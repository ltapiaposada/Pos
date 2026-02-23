<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::query()->orderBy('name')->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(BranchRequest $request)
    {
        Branch::query()->create($request->validated());

        return redirect()->route('branches.index')->with('status', 'Sucursal creada.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', 'Sucursal actualizada.');
    }
}
