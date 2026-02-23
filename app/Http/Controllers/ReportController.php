<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get();
        $users = User::query()->orderBy('name')->get();

        $dateFrom = $request->get('from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('to', now()->toDateString());
        $branchId = $request->get('branch_id');
        $userId = $request->get('user_id');

        $salesBase = Sale::query()
            ->whereDate('sold_at', '>=', $dateFrom)
            ->whereDate('sold_at', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        $salesByDay = $salesBase->clone()
            ->select(DB::raw('DATE(sold_at) as day'), DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $salesByCashier = $salesBase->clone()
            ->select('user_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $salesByProduct = SaleItem::query()
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo, $branchId, $userId) {
                $q->whereDate('sold_at', '>=', $dateFrom)
                    ->whereDate('sold_at', '<=', $dateTo)
                    ->when($branchId, fn ($b) => $b->where('branch_id', $branchId))
                    ->when($userId, fn ($u) => $u->where('user_id', $userId));
            })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(line_total) as total'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $salesByPayment = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->whereDate('sales.sold_at', '>=', $dateFrom)
            ->whereDate('sales.sold_at', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->when($userId, fn ($q) => $q->where('sales.user_id', $userId))
            ->select('payments.method', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.method')
            ->get();

        return view('reports.index', compact(
            'branches',
            'users',
            'dateFrom',
            'dateTo',
            'branchId',
            'userId',
            'salesByDay',
            'salesByCashier',
            'salesByProduct',
            'salesByPayment'
        ));
    }
}
