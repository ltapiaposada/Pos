<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantKitchenStatusRequest;
use App\Models\Branch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Services\RestaurantOrderService;
use Illuminate\Http\Request;

class RestaurantKitchenController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $branchId = (int) ($request->get('branch_id', $request->user()->branch_id ?? $branches->first()?->id));
        $status = (string) $request->get('status', '');

        $orders = RestaurantOrder::query()
            ->with(['table', 'customer', 'items.product', 'items.selections'])
            ->where('branch_id', $branchId)
            ->whereIn('status', [
                RestaurantOrder::STATUS_SENT_TO_KITCHEN,
                RestaurantOrder::STATUS_IN_PREPARATION,
                RestaurantOrder::STATUS_READY,
                RestaurantOrder::STATUS_DELIVERED,
            ])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('opened_at')
            ->get();

        return view('restaurant.kitchen.index', [
            'branches' => $branches,
            'branchId' => $branchId,
            'statusFilter' => $status,
            'orders' => $orders,
            'statusOptions' => RestaurantOrder::statusOptions(),
            'itemStatusOptions' => RestaurantOrderItem::kitchenStatusOptions(),
        ]);
    }

    public function updateItemStatus(
        RestaurantKitchenStatusRequest $request,
        RestaurantOrderItem $item,
        RestaurantOrderService $service
    ) {
        $service->updateKitchenItemStatus($item, $request->validated()['kitchen_status']);

        return redirect()->route('restaurant.kitchen.index')
            ->with('status', 'Estado de cocina actualizado.');
    }
}
