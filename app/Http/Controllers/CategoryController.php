<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->with('parent')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::query()->orderBy('name')->get();

        return view('categories.create', compact('parents'));
    }

    public function store(CategoryRequest $request)
    {
        Category::query()->create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        $parents = Category::query()
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoría eliminada.');
    }
}
