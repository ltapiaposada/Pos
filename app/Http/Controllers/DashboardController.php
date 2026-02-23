<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->branch_id;
        $today = now()->startOfDay();

        $salesQuery = Sale::query()->where('sold_at', '>=', $today);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $salesTotal = (float) $salesQuery->sum('total');
        $salesCount = (int) $salesQuery->count();
        $avgTicket = $salesCount > 0 ? $salesTotal / $salesCount : 0;

        $lowStock = Inventory::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $recentSales = Sale::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('sold_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('salesTotal', 'salesCount', 'avgTicket', 'lowStock', 'recentSales'));
    }
}
