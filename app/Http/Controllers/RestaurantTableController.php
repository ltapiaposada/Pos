<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantTableRequest;
use App\Models\Branch;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class RestaurantTableController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);

        $query = RestaurantTable::query()
            ->with(['branch', 'activeOrder.customer'])
            ->when($request->filled('branch_id'), fn ($builder) => $builder->where('branch_id', (int) $request->integer('branch_id')))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', (string) $request->string('status')))
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = trim((string) $request->string('q'));

                $builder->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderBy('branch_id')
            ->orderBy('number')
            ->orderBy('name');

        $tables = $query
            ->paginate(20);

        return view('restaurant.tables.index', [
            'branches' => $branches,
            'tables' => $tables,
            'statusOptions' => RestaurantTable::statusOptions(),
            'branchFilter' => $request->integer('branch_id') ?: null,
            'statusFilter' => $request->string('status')->toString(),
            'search' => trim((string) $request->string('q')),
        ]);
    }

    public function store(RestaurantTableRequest $request)
    {
        RestaurantTable::query()->create($request->validated());

        return redirect()->route('restaurant.tables.index')->with('status', 'Mesa creada.');
    }

    public function update(RestaurantTableRequest $request, RestaurantTable $table)
    {
        $table->update($request->validated());

        return redirect()->route('restaurant.tables.index')->with('status', 'Mesa actualizada.');
    }
}
