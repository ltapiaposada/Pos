<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantOrderStatusRequest;
use App\Http\Requests\RestaurantOrderStoreRequest;
use App\Models\Branch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantTable;
use App\Services\RestaurantOrderService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $branchId = (int) ($request->get('branch_id', $request->user()->branch_id ?? $branches->first()?->id));
        $status = (string) $request->get('status', '');

        $tables = RestaurantTable::query()
            ->with(['activeOrder.customer'])
            ->where('branch_id', $branchId)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->get();

        $activeOrders = RestaurantOrder::query()
            ->with(['table', 'customer'])
            ->where('branch_id', $branchId)
            ->whereIn('status', RestaurantOrder::activeStatuses())
            ->orderByDesc('opened_at')
            ->limit(10)
            ->get();

        $historyOrders = RestaurantOrder::query()
            ->with(['table', 'customer', 'sale'])
            ->where('branch_id', $branchId)
            ->whereIn('status', [RestaurantOrder::STATUS_CLOSED, RestaurantOrder::STATUS_CANCELLED])
            ->orderByDesc('closed_at')
            ->limit(10)
            ->get();

        return view('restaurant.index', [
            'branches' => $branches,
            'branchId' => $branchId,
            'statusFilter' => $status,
            'tableStatuses' => RestaurantTable::statusOptions(),
            'tables' => $tables,
            'activeOrders' => $activeOrders,
            'historyOrders' => $historyOrders,
            'orderTypes' => RestaurantOrder::typeOptions(),
        ]);
    }

    public function storeOrder(RestaurantOrderStoreRequest $request, RestaurantOrderService $service)
    {
        $order = $service->createOrder($request->validated(), $request->user()->id);

        return redirect()->route('restaurant.orders.show', $order)
            ->with('status', 'Pedido listo para editar.');
    }

    public function updateOrderStatus(RestaurantOrderStatusRequest $request, RestaurantOrder $order, RestaurantOrderService $service)
    {
        $service->updateOrderStatus($order, $request->validated()['status']);

        return redirect()->route('restaurant.orders.show', $order)
            ->with('status', 'Estado del pedido actualizado.');
    }
}
