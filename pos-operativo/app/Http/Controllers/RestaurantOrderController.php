<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantOrderCheckoutRequest;
use App\Http\Requests\RestaurantOrderUpdateRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\RestaurantOrder;
use App\Models\RestaurantTable;
use App\Services\InventoryService;
use App\Services\RestaurantOrderService;
use App\Services\SaleService;
use Illuminate\Http\Request;

class RestaurantOrderController extends Controller
{
    public function show(RestaurantOrder $order)
    {
        $order->load(['table', 'customer', 'items.product.kitItems.componentProduct', 'items.product.modifierGroups.options', 'items.selections', 'sale']);
        $availableStockByProduct = app(InventoryService::class)->availableStockForProducts(
            $order->items->pluck('product')->filter()->values(),
            $order->branch_id
        );

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'document']);

        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $tables = RestaurantTable::query()
            ->where('branch_id', $order->branch_id)
            ->where(function ($query) use ($order) {
                $query->where('is_active', true)
                    ->orWhere('id', $order->restaurant_table_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'status']);

        return view('restaurant.orders.show', [
            'order' => $order,
            'customers' => $customers,
            'branches' => $branches,
            'tables' => $tables,
            'orderTypes' => RestaurantOrder::typeOptions(),
            'availableStockByProduct' => $availableStockByProduct,
        ]);
    }

    public function products(Request $request)
    {
        $branchId = (int) $request->query('branch_id', $request->user()?->branch_id);
        $query = Product::query()->where('is_active', true)->with(['tax:id,rate', 'kitItems.componentProduct', 'modifierGroups.options']);

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->limit(80)->get([
            'id',
            'name',
            'sku',
            'barcode',
            'sale_price',
            'tax_id',
            'product_type',
            'uses_component_groups',
        ]);

        $availableByProduct = app(InventoryService::class)->availableStockForProducts($products, $branchId);

        $products = $products->filter(function (Product $product) use ($availableByProduct) {
            return (float) ($availableByProduct[$product->id] ?? 0) > 0;
        })->take(25)->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'sale_price' => (float) $product->sale_price,
            'tax_rate' => (float) ($product->tax?->rate ?? 0),
            'available_stock' => (float) ($availableByProduct[$product->id] ?? 0),
            'uses_component_groups' => (bool) $product->uses_component_groups,
            'modifier_groups' => $product->modifierGroups
                ->filter(fn ($group) => $group->options->where('is_active', true)->isNotEmpty())
                ->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'selection_type' => $group->selection_type,
                    'is_required' => (bool) $group->is_required,
                    'min_select' => (int) $group->min_select,
                    'max_select' => (int) $group->max_select,
                    'options' => $group->options
                        ->where('is_active', true)
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'product_id' => $option->product_id,
                            'label' => $option->label,
                            'price_delta' => (float) $option->price_delta,
                            'inventory_quantity' => (float) ($option->inventory_quantity ?? 0),
                            'inventory_unit' => $option->inventory_unit,
                            'inventory_unit_factor' => (float) ($option->inventory_unit_factor ?? 1),
                            'is_default' => (bool) $option->is_default,
                        ])->values()->all(),
                ])->values()->all(),
        ])->values();

        return response()->json($products);
    }

    public function update(RestaurantOrderUpdateRequest $request, RestaurantOrder $order, RestaurantOrderService $service)
    {
        $service->updateOrder($order, $request->validated());

        return redirect()->route('restaurant.orders.show', $order)
            ->with('status', 'Pedido actualizado.');
    }

    public function sendToKitchen(RestaurantOrder $order, RestaurantOrderService $service)
    {
        $service->sendToKitchen($order);

        return redirect()->route('restaurant.orders.show', $order)
            ->with('status', 'Pedido enviado a cocina.');
    }

    public function convertToSale(
        RestaurantOrderCheckoutRequest $request,
        RestaurantOrder $order,
        RestaurantOrderService $restaurantOrderService,
        SaleService $saleService
    ) {
        $sale = $restaurantOrderService->convertToSale(
            $order->load('items'),
            $request->validated(),
            $request->user()->id,
            $saleService
        );

        return redirect()->route('sales.ticket', $sale)->with('status', 'Pedido convertido en venta y mesa liberada.');
    }

    public function close(RestaurantOrder $order, RestaurantOrderService $service)
    {
        $service->closeOrder($order);

        return redirect()->route('restaurant.index', ['branch_id' => $order->branch_id])
            ->with('status', 'Pedido cerrado y mesa liberada.');
    }
}
