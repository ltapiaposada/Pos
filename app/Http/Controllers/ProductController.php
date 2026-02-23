<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Services\CloudinaryService;
use App\Support\StorefrontCache;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'tax', 'parentProduct']);

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::query()->orderBy('name')->get();
        $taxes = Tax::query()->where('is_active', true)->orderBy('name')->get();
        $parentCandidates = Product::query()
            ->where('product_type', '!=', Product::TYPE_VARIANT)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
        $kitComponentCandidates = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('products.create', compact('categories', 'taxes', 'parentCandidates', 'kitComponentCandidates'));
    }

    public function store(ProductRequest $request, CloudinaryService $cloudinary)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        unset($payload['kit_items']);
        unset($payload['image_file']);

        if (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
            $payload['parent_product_id'] = null;
        }

        if ($request->hasFile('image_file')) {
            try {
                $payload['image_url'] = $cloudinary->uploadImage($request->file('image_file'));
            } catch (\Throwable $e) {
                report($e);
                return back()
                    ->withErrors(['image_file' => config('app.debug') ? $e->getMessage() : 'No se pudo subir la imagen a Cloudinary.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($payload, $kitItems): void {
            $product = Product::query()->create($payload);
            $this->syncKitItems($product, $kitItems);
        });
        $this->bumpStorefrontProductsCacheVersion();

        return redirect()->route('products.index')->with('status', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        $product->load('kitItems');

        $categories = Category::query()->orderBy('name')->get();
        $taxes = Tax::query()->where('is_active', true)->orderBy('name')->get();
        $parentCandidates = Product::query()
            ->where('product_type', '!=', Product::TYPE_VARIANT)
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
        $kitComponentCandidates = Product::query()
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('products.edit', compact('product', 'categories', 'taxes', 'parentCandidates', 'kitComponentCandidates'));
    }

    public function update(ProductRequest $request, Product $product, CloudinaryService $cloudinary)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        unset($payload['kit_items']);
        unset($payload['image_file']);

        if (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
            $payload['parent_product_id'] = null;
        }

        if ($request->hasFile('image_file')) {
            try {
                $payload['image_url'] = $cloudinary->uploadImage($request->file('image_file'));
            } catch (\Throwable $e) {
                report($e);
                return back()
                    ->withErrors(['image_file' => config('app.debug') ? $e->getMessage() : 'No se pudo subir la imagen a Cloudinary.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($product, $payload, $kitItems): void {
            $product->update($payload);
            $this->syncKitItems($product, $kitItems);
        });
        $this->bumpStorefrontProductsCacheVersion();

        return redirect()->route('products.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        $this->bumpStorefrontProductsCacheVersion();

        return redirect()->route('products.index')->with('status', 'Producto eliminado.');
    }

    protected function syncKitItems(Product $product, Collection $kitItems): void
    {
        if ($product->product_type !== Product::TYPE_KIT) {
            $product->kitItems()->delete();

            return;
        }

        $normalized = $kitItems
            ->map(function (array $item): array {
                return [
                    'component_product_id' => (int) $item['component_product_id'],
                    'quantity' => (float) $item['quantity'],
                ];
            })
            ->filter(fn (array $item): bool => $item['component_product_id'] > 0 && $item['quantity'] > 0)
            ->values()
            ->all();

        $product->kitItems()->delete();
        $product->kitItems()->createMany($normalized);
    }

    private function bumpStorefrontProductsCacheVersion(): void
    {
        StorefrontCache::bumpProductsVersion();
    }
}
