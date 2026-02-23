<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryAdjustmentRequest;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get();
        $branchId = $request->get('branch_id', $branches->first()?->id);

        $query = Inventory::query()->with(['product', 'branch']);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($search = $request->get('q')) {
            $query->whereHas('product', function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $inventories = $query->orderBy('product_id')->paginate(15)->withQueryString();
        $products = Product::query()->orderBy('name')->get();
        $movements = InventoryMovement::query()
            ->with(['product', 'branch', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('inventory.index', compact('inventories', 'products', 'branches', 'branchId', 'movements'));
    }

    public function adjust(InventoryAdjustmentRequest $request, InventoryService $inventoryService)
    {
        $inventoryService->adjust([
            'branch_id' => $request->integer('branch_id'),
            'product_id' => $request->integer('product_id'),
            'user_id' => $request->user()->id,
            'type' => $request->string('type')->value(),
            'quantity' => (float) $request->input('quantity'),
            'min_stock' => $request->input('min_stock'),
            'cost_price' => $request->input('cost_price'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('inventory.index')->with('status', 'Inventario ajustado.');
    }
}
