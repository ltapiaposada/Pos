<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Services\ImageStorageService;
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
            ->get(['id', 'name', 'sku', 'unit']);

        return view('products.create', compact('categories', 'taxes', 'parentCandidates', 'kitComponentCandidates'));
    }

    public function store(ProductRequest $request, ImageStorageService $imageStorage)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        $modifierGroups = collect($payload['modifier_groups'] ?? []);
        unset($payload['kit_items']);
        unset($payload['modifier_groups']);
        unset($payload['image_file']);

        if (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
            $payload['parent_product_id'] = null;
        }

        if ($request->hasFile('image_file')) {
            try {
                $payload['image_url'] = $imageStorage->uploadImage($request->file('image_file'));
            } catch (\Throwable $e) {
                report($e);
                return back()
                    ->withErrors(['image_file' => config('app.debug') ? $e->getMessage() : 'No se pudo subir la imagen a Cloudflare R2.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($payload, $kitItems, $modifierGroups): void {
            $product = Product::query()->create($payload);
            $this->syncKitItems($product, $kitItems);
            $this->syncModifierGroups($product, $modifierGroups);
        });
        $this->bumpStorefrontProductsCacheVersion();

        return redirect()->route('products.index')->with('status', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        $product->load(['kitItems', 'modifierGroups.options']);

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
            ->get(['id', 'name', 'sku', 'unit']);

        return view('products.edit', compact('product', 'categories', 'taxes', 'parentCandidates', 'kitComponentCandidates'));
    }

    public function update(ProductRequest $request, Product $product, ImageStorageService $imageStorage)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        $modifierGroups = collect($payload['modifier_groups'] ?? []);
        unset($payload['kit_items']);
        unset($payload['modifier_groups']);
        unset($payload['image_file']);

        if (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
            $payload['parent_product_id'] = null;
        }

        if ($request->hasFile('image_file')) {
            try {
                $payload['image_url'] = $imageStorage->uploadImage($request->file('image_file'));
            } catch (\Throwable $e) {
                report($e);
                return back()
                    ->withErrors(['image_file' => config('app.debug') ? $e->getMessage() : 'No se pudo subir la imagen a Cloudflare R2.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($product, $payload, $kitItems, $modifierGroups): void {
            $product->update($payload);
            $this->syncKitItems($product, $kitItems);
            $this->syncModifierGroups($product, $modifierGroups);
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
                $componentProductId = (int) $item['component_product_id'];
                $componentUnit = $item['component_unit']
                    ?? Product::query()->whereKey($componentProductId)->value('unit')
                    ?? null;

                return [
                    'component_product_id' => $componentProductId,
                    'quantity' => (float) $item['quantity'],
                    'component_unit' => $componentUnit,
                    'component_unit_factor' => (float) ($item['component_unit_factor'] ?? 1),
                ];
            })
            ->filter(fn (array $item): bool => $item['component_product_id'] > 0 && $item['quantity'] > 0 && $item['component_unit_factor'] > 0)
            ->values()
            ->all();

        $product->kitItems()->delete();
        $product->kitItems()->createMany($normalized);
    }

    protected function syncModifierGroups(Product $product, Collection $modifierGroups): void
    {
        $normalizedGroups = $modifierGroups
            ->filter(fn (array $group): bool => filled($group['name'] ?? null))
            ->values();

        $existingGroupIds = [];

        foreach ($normalizedGroups as $groupIndex => $groupData) {
            $group = $product->modifierGroups()->updateOrCreate(
                ['id' => $groupData['id'] ?? null],
                [
                    'name' => $groupData['name'],
                    'selection_type' => $groupData['selection_type'],
                    'is_required' => (bool) ($groupData['is_required'] ?? false),
                    'min_select' => (int) ($groupData['min_select'] ?? 0),
                    'max_select' => (int) ($groupData['max_select'] ?? (($groupData['selection_type'] ?? null) === \App\Models\ProductModifierGroup::TYPE_SINGLE ? 1 : 0)),
                    'display_order' => $groupIndex,
                ]
            );

            $existingGroupIds[] = $group->id;
            $options = collect($groupData['options'] ?? [])
                ->filter(fn (array $option): bool => filled($option['label'] ?? null) || filled($option['product_id'] ?? null))
                ->values();

            $existingOptionIds = [];

            foreach ($options as $optionIndex => $optionData) {
                $label = trim((string) ($optionData['label'] ?? ''));
                if ($label === '' && ! empty($optionData['product_id'])) {
                    $label = (string) Product::query()->whereKey($optionData['product_id'])->value('name');
                }

                $option = $group->options()->updateOrCreate(
                    ['id' => $optionData['id'] ?? null],
                    [
                        'product_id' => $optionData['product_id'] ?? null,
                        'inventory_quantity' => filled($optionData['inventory_quantity'] ?? null) ? (float) $optionData['inventory_quantity'] : null,
                        'inventory_unit' => $optionData['inventory_unit']
                            ?? Product::query()->whereKey($optionData['product_id'] ?? null)->value('unit')
                            ?? null,
                        'inventory_unit_factor' => (float) ($optionData['inventory_unit_factor'] ?? 1),
                        'label' => $label,
                        'price_delta' => (float) ($optionData['price_delta'] ?? 0),
                        'is_default' => (bool) ($optionData['is_default'] ?? false),
                        'is_active' => (bool) ($optionData['is_active'] ?? true),
                        'display_order' => $optionIndex,
                    ]
                );

                $existingOptionIds[] = $option->id;
            }

            $group->options()->whereNotIn('id', $existingOptionIds ?: [0])->delete();
        }

        $product->modifierGroups()->whereNotIn('id', $existingGroupIds ?: [0])->delete();
    }

    private function bumpStorefrontProductsCacheVersion(): void
    {
        StorefrontCache::bumpProductsVersion();
    }
}
