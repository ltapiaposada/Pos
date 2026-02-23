<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\AccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcommerceOrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::query()
            ->with(['customer', 'branch', 'payments'])
            ->where('order_source', Sale::SOURCE_ECOMMERCE)
            ->orderByDesc('sold_at')
            ->orderByDesc('id');

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('ecommerce_admin.orders.index', [
            'orders' => $orders,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function show(Sale $sale): View
    {
        abort_unless($sale->order_source === Sale::SOURCE_ECOMMERCE, 404);

        $sale->load(['items', 'payments', 'customer', 'branch', 'user']);

        return view('ecommerce_admin.orders.show', [
            'order' => $sale,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function updateStatus(Request $request, Sale $sale): RedirectResponse
    {
        abort_unless($sale->order_source === Sale::SOURCE_ECOMMERCE, 404);

        $data = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $sale->update([
            'status' => $data['status'],
        ]);

        return redirect()->route('ecommerce-admin.orders.show', $sale)->with('status', 'Estado del pedido actualizado.');
    }

    public function convertToInvoice(Request $request, Sale $sale, AccountingPostingService $postingService): RedirectResponse
    {
        abort_unless($sale->order_source === Sale::SOURCE_ECOMMERCE, 404);

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

    private function statusOptions(): array
    {
        return [
            Sale::STATUS_PENDING => 'Pendiente',
            Sale::STATUS_PROCESSING => 'Procesando',
            Sale::STATUS_SHIPPED => 'Enviado',
            Sale::STATUS_DELIVERED => 'Entregado',
            Sale::STATUS_CANCELLED => 'Cancelado',
        ];
    }
}

