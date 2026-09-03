<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CashRegisterSession;
use App\Models\MedicalOrder;
use App\Models\Sale;
use App\Services\InventoryService;
use App\Services\SaleService;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            ->with(['tax:id,rate', 'kitItems.componentProduct', 'modifierGroups.options.product'])
            ->whereIn('id', $oldProductIds)
            ->get(['id', 'name', 'sku', 'tax_id', 'product_type'])
            ->keyBy('id');
        $availableStockByProduct = app(InventoryService::class)->availableStockForProducts(
            $oldProducts->values(),
            (int) $branchId
        );

        $oldPosState = [
            'branch_id' => (string) ($request->session()->getOldInput('branch_id') ?? $branchId),
            'customer_id' => $request->session()->getOldInput('customer_id', $request->query('customer_id')),
            'medical_order_id' => $request->session()->getOldInput('medical_order_id', $request->query('medical_order_id')),
            'global_discount' => (float) ($request->session()->getOldInput('global_discount', 0)),
            'items' => collect($oldItems)->map(function (array $item) use ($oldProducts) {
                $productId = (int) ($item['product_id'] ?? 0);
                $product = $oldProducts->get($productId);

                return [
                    'product_id' => $productId,
                    'name' => $product?->name ?? "Producto #{$productId}",
                    'sku' => $product?->sku ?? 'N/A',
                    'product_type' => $product?->product_type ?? Product::TYPE_SIMPLE,
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'available_stock' => (float) ($availableStockByProduct[$productId] ?? 0),
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

        $supportsMedicalOrders = CompanyContext::isOpticService($request->user()?->company);
        $medicalOrders = collect();

        if ($supportsMedicalOrders && Schema::hasTable('medical_orders')) {
            $medicalOrders = MedicalOrder::query()
                ->with('customer:id,name,document')
                ->where('status', MedicalOrder::STATUS_ACTIVE)
                ->latest('ordered_at')
                ->get();
        }

        return view('pos.index', compact('branches', 'branchId', 'customers', 'requiresCashSession', 'oldPosState', 'medicalOrders', 'supportsMedicalOrders'));
    }

    public function products(Request $request)
    {
        $branchId = (int) $request->query('branch_id', $request->user()?->branch_id);
        $query = Product::query()->where('is_active', true)->with([
            'tax:id,rate',
            'kitItems.componentProduct',
            'modifierGroups.options.product',
            'variants.tax:id,rate',
        ]);
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($variantQuery) use ($search) {
                        $variantQuery->where('is_active', true)
                            ->where(function ($variantSearch) use ($search) {
                                $variantSearch->where('sku', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                    });
            })->orderByRaw(
                'CASE WHEN barcode = ? THEN 0 WHEN sku = ? THEN 1 ELSE 2 END',
                [$search, $search]
            );
        }

        $products = $query->orderBy('name')->limit(60)->get([
            'id', 'name', 'sku', 'barcode', 'sale_price', 'tax_id', 'product_type', 'uses_component_groups', 'parent_product_id', 'variant_attributes',
        ]);
        $products = $products->whereNull('parent_product_id')->values();
        $stockProducts = $products
            ->flatMap(fn (Product $product) => $product->variants->isNotEmpty() ? $product->variants : [$product])
            ->values();

        $availableByProduct = app(InventoryService::class)->availableStockForProducts($stockProducts, $branchId);

        $products = $products->filter(function (Product $product) use ($availableByProduct) {
            if ($product->variants->isNotEmpty()) {
                return $product->variants
                    ->where('is_active', true)
                    ->contains(fn (Product $variant) => (float) ($availableByProduct[$variant->id] ?? 0) > 0);
            }

            return (float) ($availableByProduct[$product->id] ?? 0) > 0;
        })->take(20)->map(function (Product $product) use ($availableByProduct) {
            $variantOptions = $product->variants
                ->where('is_active', true)
                ->filter(fn (Product $variant) => (float) ($availableByProduct[$variant->id] ?? 0) > 0)
                ->values();
            $displayProduct = $variantOptions->first() ?? $product;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'sale_price' => (float) $displayProduct->sale_price,
                'tax_rate' => (float) ($displayProduct->tax?->rate ?? $product->tax?->rate ?? 0),
                'available_stock' => $variantOptions->isNotEmpty()
                    ? (float) $variantOptions->sum(fn (Product $variant) => (float) ($availableByProduct[$variant->id] ?? 0))
                    : (float) ($availableByProduct[$product->id] ?? 0),
                'tracks_inventory' => $displayProduct->tracksInventory(),
                'product_type' => $product->product_type,
                'uses_component_groups' => $product->uses_component_groups,
                'has_variants' => $variantOptions->isNotEmpty(),
                'variants' => $variantOptions->map(fn (Product $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'sale_price' => (float) $variant->sale_price,
                    'tax_rate' => (float) ($variant->tax?->rate ?? $product->tax?->rate ?? 0),
                    'available_stock' => (float) ($availableByProduct[$variant->id] ?? 0),
                    'tracks_inventory' => $variant->tracksInventory(),
                    'product_type' => $variant->product_type,
                    'attributes' => $variant->variant_attributes ?? [],
                ])->values(),
                'modifier_groups' => $product->modifierGroups->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'selection_type' => $group->selection_type,
                    'is_required' => $group->is_required,
                    'min_select' => $group->min_select,
                    'max_select' => $group->max_select,
                    'options' => $group->options
                        ->where('is_active', true)
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'price_delta' => (float) $option->price_delta,
                            'is_default' => $option->is_default,
                        ])->values(),
                ])->values(),
            ];
        })->values();

        return response()->json($products);
    }

    public function resolveProduct(Request $request)
    {
        $barcode = trim((string) $request->query('barcode', ''));
        $branchId = (int) $request->query('branch_id', $request->user()?->branch_id);
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

        $availableStock = app(InventoryService::class)->availableStockForProduct($product, $branchId);
        if ($product->tracksInventory() && $availableStock <= 0) {
            return response()->json([
                'message' => 'El producto no tiene stock disponible en esta sucursal.',
            ], 422);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'sale_price' => (float) $product->sale_price,
            'tax_rate' => (float) ($product->tax?->rate ?? 0),
            'available_stock' => $availableStock,
            'tracks_inventory' => $product->tracksInventory(),
            'product_type' => $product->product_type,
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
                'medical_order_id' => $request->input('medical_order_id'),
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
        $sale->load(['items.serials', 'items.lots.lot', 'payments', 'customer', 'user', 'branch', 'medicalOrder']);

        return view('sales.show', compact('sale'));
    }

    public function ticket(Sale $sale)
    {
        $sale->load(['items.serials', 'items.lots.lot', 'payments', 'customer', 'user', 'branch', 'medicalOrder']);

        return view('sales.ticket', compact('sale'));
    }

    public function invoices(Request $request)
    {
        $query = Sale::query()
            ->with(['branch', 'customer', 'user', 'medicalOrder'])
            ->where(function ($builder) {
                $builder->whereIn('order_source', [Sale::SOURCE_POS, Sale::SOURCE_RESTAURANT])
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
