<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnRequest;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function create(Request $request)
    {
        $saleId = $request->get('sale_id');
        $sale = $saleId ? Sale::query()->with('items')->find($saleId) : null;
        $returnedByProduct = collect();

        if ($sale) {
            $returnedByProduct = ReturnItem::query()
                ->selectRaw('product_id, SUM(quantity) as returned_qty')
                ->whereHas('returnModel', function ($query) use ($sale) {
                    $query->where('sale_id', $sale->id)
                        ->where('status', 'completed');
                })
                ->groupBy('product_id')
                ->pluck('returned_qty', 'product_id');
        }

        return view('returns.create', compact('sale', 'returnedByProduct'));
    }

    public function store(ReturnRequest $request, ReturnService $service)
    {
        $return = $service->createReturn(
            $request->integer('sale_id'),
            $request->user()->id,
            $request->input('items'),
            $request->input('reason')
        );

        return redirect()->route('returns.show', $return)->with('status', 'Devolución registrada.');
    }

    public function show(\App\Models\ReturnModel $return)
    {
        $return->load('items');

        return view('returns.show', compact('return'));
    }
}
