<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CashRegisterSession;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get();
        $branchId = $request->get('branch_id', $request->user()->branch_id ?? $branches->first()?->id);
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->when(
                Customer::supportsContactType(),
                fn ($query) => $query->whereIn('contact_type', [Customer::TYPE_PERSON, Customer::TYPE_COMPANY])
            )
            ->get();
        $openSession = CashRegisterSession::query()
            ->where('user_id', $request->user()->id)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();
        $requiresCashSession = $openSession === null;
        $oldItems = $this->normalizeOldInputArray($request->session()->getOldInput('items', []));
        $oldProductIds = collect($oldItems)->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $oldProducts = Product::query()
            ->with('tax:id,rate')
            ->whereIn('id', $oldProductIds)
            ->get(['id', 'name', 'sku', 'tax_id'])
            ->keyBy('id');

        $oldPosState = [
            'branch_id' => (string) ($request->session()->getOldInput('branch_id') ?? $branchId),
            'customer_id' => $request->session()->getOldInput('customer_id'),
            'global_discount' => (float) ($request->session()->getOldInput('global_discount', 0)),
            'items' => collect($oldItems)->map(function (array $item) use ($oldProducts) {
                $productId = (int) ($item['product_id'] ?? 0);
                $product = $oldProducts->get($productId);

                return [
                    'product_id' => $productId,
                    'name' => $product?->name ?? "Producto #{$productId}",
                    'sku' => $product?->sku ?? 'N/A',
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'tax_rate' => (float) ($product?->tax?->rate ?? 0),
                    'discount_percent' => (($item['discount_type'] ?? null) === 'percent')
                        ? (float) ($item['discount_value'] ?? 0)
                        : 0,
                ];
            })->values()->all(),
            'payments' => collect($this->normalizeOldInputArray($request->session()->getOldInput('payments', [])))
                ->map(function (array $payment) {
                    return [
                        'method' => (string) ($payment['method'] ?? ''),
                        'amount' => (float) ($payment['amount'] ?? 0),
                    ];
                })->values()->all(),
        ];

        return view('pos.index', compact('branches', 'branchId', 'customers', 'requiresCashSession', 'oldPosState'));
    }

    public function products(Request $request)
    {
        $query = Product::query()->where('is_active', true)->with('tax:id,rate');
        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->limit(20)->get([
            'id', 'name', 'sku', 'barcode', 'sale_price', 'tax_id',
        ])->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'sale_price' => (float) $product->sale_price,
                'tax_rate' => (float) ($product->tax?->rate ?? 0),
            ];
        })->values();

        return response()->json($products);
    }

    public function resolveProduct(Request $request)
    {
        $barcode = trim((string) $request->query('barcode', ''));
        if ($barcode === '') {
            return response()->json([
                'message' => 'Debes enviar un codigo de barras o SKU.',
            ], 422);
        }

        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($builder) use ($barcode) {
                $builder->where('barcode', $barcode)
                    ->orWhere('sku', $barcode);
            })
            ->with('tax:id,rate')
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'No se encontro un producto con ese codigo.',
            ], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'sale_price' => (float) $product->sale_price,
            'tax_rate' => (float) ($product->tax?->rate ?? 0),
        ]);
    }

    public function createScannerSession(Request $request)
    {
        $token = Str::lower(Str::random(40));
        $expiresAt = now()->addHours(2);
        $session = [
            'user_id' => $request->user()->id,
            'expires_at' => $expiresAt->timestamp,
        ];

        Cache::put($this->scannerSessionKey($token), $session, $expiresAt);
        Cache::put($this->scannerEventsKey($token), [], $expiresAt);

        return response()->json([
            'token' => $token,
            'remote_url' => route('pos.scanner.remote', ['token' => $token]),
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function pollScannerSession(Request $request, string $token)
    {
        $session = $this->getScannerSession($token);
        if (!$session) {
            return response()->json([
                'message' => 'Sesion de escaneo expirada o invalida.',
            ], 404);
        }

        if ((int) ($session['user_id'] ?? 0) !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'No puedes leer esta sesion de escaneo.',
            ], 403);
        }

        $events = Cache::get($this->scannerEventsKey($token), []);
        Cache::put($this->scannerEventsKey($token), [], $this->scannerExpiryFromSession($session));

        return response()->json([
            'events' => is_array($events) ? $events : [],
        ]);
    }

    public function remoteScanner(string $token)
    {
        $session = $this->getScannerSession($token);
        abort_if(!$session, 404);

        return view('pos.remote-scanner', [
            'token' => $token,
        ]);
    }

    public function receiveRemoteScan(Request $request, string $token)
    {
        $session = $this->getScannerSession($token);
        if (!$session) {
            return response()->json([
                'message' => 'Sesion de escaneo expirada o invalida.',
            ], 404);
        }

        $barcode = trim((string) $request->input('barcode', ''));
        if ($barcode === '') {
            return response()->json([
                'message' => 'Debes enviar el codigo de barras.',
            ], 422);
        }

        $events = Cache::get($this->scannerEventsKey($token), []);
        if (!is_array($events)) {
            $events = [];
        }

        $events[] = [
            'barcode' => $barcode,
            'scanned_at' => now()->toIso8601String(),
        ];

        Cache::put(
            $this->scannerEventsKey($token),
            array_slice($events, -30),
            $this->scannerExpiryFromSession($session)
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    public function checkout(SaleRequest $request, SaleService $saleService)
    {
        $this->validateDiscounts($request);

        try {
            $sale = $saleService->createSale([
                'branch_id' => $request->integer('branch_id'),
                'customer_id' => $request->input('customer_id'),
                'items' => $request->input('items'),
                'global_discount' => $request->input('global_discount', 0),
                'payments' => $request->input('payments'),
            ], $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'sale' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('sales.ticket', $sale)->with('status', 'Venta registrada.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'payments', 'customer', 'user', 'branch']);

        return view('sales.show', compact('sale'));
    }

    public function ticket(Sale $sale)
    {
        $sale->load(['items', 'payments', 'customer', 'user', 'branch']);

        return view('sales.ticket', compact('sale'));
    }

    public function invoices(Request $request)
    {
        $query = Sale::query()
            ->with(['branch', 'customer', 'user'])
            ->where(function ($builder) {
                $builder->where('order_source', Sale::SOURCE_POS)
                    ->orWhereNotNull('invoiced_at');
            })
            ->orderByDesc('sold_at')
            ->orderByDesc('id');

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('branch', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->paginate(20)->withQueryString();
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);

        return view('sales.index', compact('sales', 'branches'));
    }

    private function normalizeOldInputArray(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    private function validateDiscounts(SaleRequest $request): void
    {
        $threshold = (float) config('pos.high_discount_threshold_percent', 10);
        $items = $request->input('items', []);

        foreach ($items as $item) {
            $type = $item['discount_type'] ?? null;
            $value = (float) ($item['discount_value'] ?? 0);
            if ($type === 'percent' && $value > $threshold && !$request->user()->can('apply_high_discount')) {
                throw ValidationException::withMessages([
                    'items' => 'No tienes permiso para aplicar descuentos altos.',
                ]);
            }
        }
    }

    private function scannerSessionKey(string $token): string
    {
        return "pos_scanner_session:{$token}";
    }

    private function scannerEventsKey(string $token): string
    {
        return "pos_scanner_events:{$token}";
    }

    private function getScannerSession(string $token): ?array
    {
        $session = Cache::get($this->scannerSessionKey($token));
        if (!is_array($session)) {
            return null;
        }

        $expiresAt = (int) ($session['expires_at'] ?? 0);
        if ($expiresAt <= now()->timestamp) {
            Cache::forget($this->scannerSessionKey($token));
            Cache::forget($this->scannerEventsKey($token));

            return null;
        }

        return $session;
    }

    private function scannerExpiryFromSession(array $session): \DateTimeInterface
    {
        $expiresAt = (int) ($session['expires_at'] ?? now()->addHour()->timestamp);

        return now()->setTimestamp(max(now()->timestamp + 60, $expiresAt));
    }
}
