<?php

namespace App\Http\Controllers;

use App\Models\RestaurantOrder;
use App\Models\Sale;
use App\Services\AccountingPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcommerceOrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->get('q', '');
        $status = (string) $request->get('status', '');
        $isRestaurantService = \App\Support\CompanyContext::isRestaurantService($request->user()?->company);

        $restaurantOrders = $isRestaurantService
            ? RestaurantOrder::query()
                ->with(['customer', 'branch', 'table'])
                ->where(function ($query) {
                    $query->where('notes', 'like', 'Origen: Pedido web restaurante%')
                        ->orWhereIn('order_type', [
                            RestaurantOrder::TYPE_DELIVERY,
                            RestaurantOrder::TYPE_TAKEAWAY,
                        ]);
                })
                ->whereNull('sale_id')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhere('notes', 'like', "%{$search}%");
                    });
                })
                ->when($status !== '' && array_key_exists($status, RestaurantOrder::statusOptions()), fn ($query) => $query->where('status', $status))
                ->orderByDesc('opened_at')
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : collect();

        $query = Sale::query()
            ->with(['customer', 'branch', 'payments'])
            ->where('order_source', $isRestaurantService ? Sale::SOURCE_RESTAURANT : Sale::SOURCE_ECOMMERCE)
            ->orderByDesc('sold_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        if ($status !== '' && array_key_exists($status, $this->saleStatusOptions())) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('ecommerce_admin.orders.index', [
            'orders' => $orders,
            'restaurantOrders' => $restaurantOrders,
            'statusOptions' => $this->filterStatusOptions($request),
            'statusLabels' => $this->statusLabels(),
            'saleStatusOptions' => $this->saleStatusOptions(),
            'restaurantStatusOptions' => RestaurantOrder::statusOptions(),
            'isRestaurantService' => $isRestaurantService,
        ]);
    }

    public function show(Request $request, Sale $sale): View
    {
        abort_unless($sale->order_source === $this->serviceSaleSource($request), 404);

        $sale->load(['items', 'payments', 'customer', 'branch', 'user']);

        return view('ecommerce_admin.orders.show', [
            'order' => $sale,
            'statusOptions' => $this->saleStatusOptions(),
        ]);
    }

    public function updateStatus(Request $request, Sale $sale): RedirectResponse
    {
        abort_unless($sale->order_source === $this->serviceSaleSource($request), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->saleStatusOptions()))],
        ]);

        $sale->update([
            'status' => $data['status'],
            'paid_total' => $this->resolvedPaidTotalForStatus($sale, $data['status']),
        ]);

        return redirect()->route('ecommerce-admin.orders.show', $sale)->with('status', 'Estado del pedido actualizado.');
    }

    public function convertToInvoice(Request $request, Sale $sale, AccountingPostingService $postingService): RedirectResponse
    {
        abort_unless($sale->order_source === $this->serviceSaleSource($request), 404);

        if (! $this->canInvoiceSale($sale)) {
            return redirect()
                ->route('ecommerce-admin.orders.show', $sale)
                ->withErrors(['order' => 'Valida el pago o completa la entrega antes de registrar la factura de este pedido.']);
        }

        DB::transaction(function () use ($request, $sale, $postingService) {
            $sale->refresh();

            $updates = [];
            if (! $sale->invoiced_at) {
                $updates['invoiced_at'] = now();
                $updates['invoiced_by_user_id'] = $request->user()->id;
            }

            if (! $sale->accounted_at) {
                $payments = $sale->payments()
                    ->orderBy('id')
                    ->get(['method', 'amount'])
                    ->map(fn ($payment) => [
                        'method' => (string) $payment->method,
                        'amount' => (float) $payment->amount,
                    ]);

                $postingService->postSale($sale, $payments, $request->user()->id);

                $updates['accounted_at'] = now();
                $updates['accounted_by_user_id'] = $request->user()->id;
            }

            if (! empty($updates)) {
                $sale->update($updates);
            }
        });

        return redirect()->route('ecommerce-admin.orders.index')->with('status', 'Pedido facturado y contabilizado correctamente.');
    }

    private function statusLabels(): array
    {
        return $this->saleStatusOptions() + RestaurantOrder::statusOptions();
    }

    private function filterStatusOptions(Request $request): array
    {
        return \App\Support\CompanyContext::isRestaurantService($request->user()?->company)
            ? RestaurantOrder::statusOptions()
            : $this->saleStatusOptions();
    }

    private function saleStatusOptions(): array
    {
        return [
            Sale::STATUS_PAID => 'Pagada',
            Sale::STATUS_PENDING => 'Recibido / por validar',
            Sale::STATUS_PROCESSING => 'Confirmado',
            Sale::STATUS_SHIPPED => 'Despachado',
            Sale::STATUS_DELIVERED => 'Entregado',
            Sale::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    private function resolvedPaidTotalForStatus(Sale $sale, string $status): float
    {
        $method = (string) $sale->payments()->orderBy('id')->value('method');

        if (in_array($method, ['transfer', 'qr'], true) && in_array($status, [Sale::STATUS_PAID, Sale::STATUS_PROCESSING, Sale::STATUS_SHIPPED, Sale::STATUS_DELIVERED], true)) {
            return (float) $sale->total;
        }

        if ($method === 'contraentrega' && in_array($status, [Sale::STATUS_PAID, Sale::STATUS_DELIVERED], true)) {
            return (float) $sale->total;
        }

        return (float) $sale->paid_total;
    }

    private function canInvoiceSale(Sale $sale): bool
    {
        $method = (string) $sale->payments()->orderBy('id')->value('method');

        if (in_array($method, ['transfer', 'qr'], true)) {
            return in_array($sale->status, [Sale::STATUS_PAID, Sale::STATUS_PROCESSING, Sale::STATUS_SHIPPED, Sale::STATUS_DELIVERED], true);
        }

        if ($method === 'contraentrega') {
            return in_array($sale->status, [Sale::STATUS_PAID, Sale::STATUS_DELIVERED], true);
        }

        return false;
    }

    private function serviceSaleSource(Request $request): string
    {
        return \App\Support\CompanyContext::isRestaurantService($request->user()?->company)
            ? Sale::SOURCE_RESTAURANT
            : Sale::SOURCE_ECOMMERCE;
    }
}
