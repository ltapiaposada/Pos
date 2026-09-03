<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariantAttribute;
use App\Models\Tax;
use App\Services\ImageStorageService;
use App\Support\CompanyRules;
use App\Support\StorefrontCache;
use App\Support\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'tax', 'parentProduct']);

        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
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
        $companyId = CompanyRules::currentCompanyId();
        $categories = Category::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->get();
        $taxes = Tax::query()
            ->forCompany($companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $parentCandidates = Product::query()
            ->forCompany($companyId)
            ->where('product_type', '!=', Product::TYPE_VARIANT)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
        $kitComponentCandidates = Product::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'unit']);
        $variantAttributes = ProductVariantAttribute::query()
            ->forCompany($companyId)
            ->with(['values' => fn ($query) => $query->orderBy('value')])
            ->orderBy('name')
            ->get();

        return view('products.create', compact('categories', 'taxes', 'parentCandidates', 'kitComponentCandidates', 'variantAttributes'));
    }

    public function store(ProductRequest $request, ImageStorageService $imageStorage)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        $modifierGroups = collect($payload['modifier_groups'] ?? []);
        $variants = collect($payload['variants'] ?? []);
        $variantAttributeDefinitions = collect($payload['variant_attribute_definitions'] ?? []);
        unset($payload['variant_attribute_definitions']);
        unset($payload['variants']);
        unset($payload['kit_items']);
        unset($payload['modifier_groups']);
        unset($payload['image_file']);
        $payload['company_id'] = CompanyRules::currentCompanyId();
        $createsVariantTemplate = ($payload['product_type'] ?? null) === Product::TYPE_VARIANT
            && empty($payload['parent_product_id']);
        $payload['uses_component_groups'] = ($payload['product_type'] ?? null) === Product::TYPE_KIT
            && (bool) ($payload['uses_component_groups'] ?? false);

        if ($createsVariantTemplate) {
            $payload['product_type'] = Product::TYPE_SIMPLE;
            $payload['parent_product_id'] = null;
        } elseif (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
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

        DB::transaction(function () use ($payload, $kitItems, $modifierGroups, $variants, $variantAttributeDefinitions, $createsVariantTemplate): void {
            $product = Product::query()->create($payload);
            $this->syncKitItems($product, $product->uses_component_groups ? collect() : $kitItems);
            if ($product->uses_component_groups) {
                $this->syncModifierGroups($product, $modifierGroups);
            } elseif ($product->product_type === Product::TYPE_KIT) {
                $this->syncModifierGroups($product, collect());
            }
            if ($createsVariantTemplate) {
                $this->syncVariantAttributeCatalog($variantAttributeDefinitions);
                $this->syncVariants($product, $variants);
            }
        });
        $this->bumpStorefrontProductsCacheVersion();

        return redirect()->route('products.index')->with('status', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        $product->load(['kitItems', 'modifierGroups.options', 'variants']);

        $companyId = (int) ($product->company_id ?? CompanyRules::currentCompanyId());
        $categories = Category::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->get();
        $taxes = Tax::query()
            ->forCompany($companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $parentCandidates = Product::query()
            ->forCompany($companyId)
            ->where('product_type', '!=', Product::TYPE_VARIANT)
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
        $kitComponentCandidates = Product::query()
            ->forCompany($companyId)
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'unit']);
        $variantAttributes = ProductVariantAttribute::query()
            ->forCompany($companyId)
            ->with(['values' => fn ($query) => $query->orderBy('value')])
            ->orderBy('name')
            ->get();

        return view('products.edit', compact('product', 'categories', 'taxes', 'parentCandidates', 'kitComponentCandidates', 'variantAttributes'));
    }

    public function update(ProductRequest $request, Product $product, ImageStorageService $imageStorage)
    {
        $payload = $request->validated();
        $kitItems = collect($payload['kit_items'] ?? []);
        $modifierGroups = collect($payload['modifier_groups'] ?? []);
        $variants = collect($payload['variants'] ?? []);
        $variantAttributeDefinitions = collect($payload['variant_attribute_definitions'] ?? []);
        unset($payload['variant_attribute_definitions']);
        unset($payload['variants']);
        unset($payload['kit_items']);
        unset($payload['modifier_groups']);
        unset($payload['image_file']);
        $createsVariantTemplate = ($payload['product_type'] ?? null) === Product::TYPE_VARIANT
            && empty($payload['parent_product_id']);
        $payload['uses_component_groups'] = ($payload['product_type'] ?? null) === Product::TYPE_KIT
            && (bool) ($payload['uses_component_groups'] ?? false);

        if ($createsVariantTemplate) {
            $payload['product_type'] = Product::TYPE_SIMPLE;
            $payload['parent_product_id'] = null;
        } elseif (($payload['product_type'] ?? Product::TYPE_SIMPLE) !== Product::TYPE_VARIANT) {
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

        DB::transaction(function () use ($product, $payload, $kitItems, $modifierGroups, $variants, $variantAttributeDefinitions, $createsVariantTemplate): void {
            $product->update($payload);
            $this->syncKitItems($product, $product->uses_component_groups ? collect() : $kitItems);
            if ($product->uses_component_groups) {
                $this->syncModifierGroups($product, $modifierGroups);
            } elseif ($product->product_type === Product::TYPE_KIT) {
                $this->syncModifierGroups($product, collect());
            }
            if ($createsVariantTemplate) {
                $this->syncVariantAttributeCatalog($variantAttributeDefinitions);
                $this->syncVariants($product, $variants);
            }
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

    public function storeVariantAttribute(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'values' => ['required', 'array', 'min:1'],
            'values.*' => ['required', 'string', 'max:120'],
        ]);

        $companyId = CompanyRules::currentCompanyId();
        $name = trim((string) $validated['name']);
        $values = collect($validated['values'])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => mb_strtolower($value))
            ->values();

        $attribute = DB::transaction(function () use ($companyId, $name, $values): ProductVariantAttribute {
            $attribute = ProductVariantAttribute::query()->firstOrCreate([
                'company_id' => $companyId,
                'name' => $name,
            ]);

            $values->each(function (string $value) use ($attribute): void {
                $attribute->values()->firstOrCreate(['value' => $value]);
            });

            return $attribute->load(['values' => fn ($query) => $query->orderBy('value')]);
        });

        return response()->json([
            'id' => $attribute->id,
            'name' => $attribute->name,
            'values' => $attribute->values->pluck('value')->values(),
        ]);
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
                $stockUnit = Product::query()->whereKey($componentProductId)->value('unit');
                $factor = UnitConverter::resolveFactor(
                    $componentUnit,
                    $stockUnit,
                    (float) ($item['component_unit_factor'] ?? 1)
                );

                return [
                    'component_product_id' => $componentProductId,
                    'quantity' => (float) $item['quantity'],
                    'component_unit' => $componentUnit,
                    'component_unit_factor' => $factor,
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

                $inventoryUnit = $optionData['inventory_unit']
                    ?? Product::query()->whereKey($optionData['product_id'] ?? null)->value('unit')
                    ?? null;
                $stockUnit = Product::query()->whereKey($optionData['product_id'] ?? null)->value('unit');
                $factor = UnitConverter::resolveFactor(
                    $inventoryUnit,
                    $stockUnit,
                    (float) ($optionData['inventory_unit_factor'] ?? 1)
                );

                $option = $group->options()->updateOrCreate(
                    ['id' => $optionData['id'] ?? null],
                    [
                        'product_id' => $optionData['product_id'] ?? null,
                        'inventory_quantity' => filled($optionData['inventory_quantity'] ?? null) ? (float) $optionData['inventory_quantity'] : null,
                        'inventory_unit' => $inventoryUnit,
                        'inventory_unit_factor' => $factor,
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

    protected function syncVariants(Product $product, Collection $variants): void
    {
        $normalized = $variants
            ->filter(fn (array $variant): bool => filled($variant['sku'] ?? null) || filled($variant['name'] ?? null) || collect($variant['attributes'] ?? [])->filter()->isNotEmpty())
            ->values();

        $existingIds = [];

        foreach ($normalized as $variant) {
            $attributes = collect($variant['attributes'] ?? [])
                ->mapWithKeys(fn ($value, $key) => [
                    trim((string) $key) => trim((string) $value),
                ])
                ->filter(fn ($value, $key) => $key !== '' && $value !== '')
                ->all();

            $child = $product->variants()->updateOrCreate(
                ['id' => $variant['id'] ?? null],
                [
                    'company_id' => $product->company_id,
                    'category_id' => $product->category_id,
                    'tax_id' => $product->tax_id,
                    'name' => $this->variantName($product, $variant, $attributes),
                    'sku' => $variant['sku'],
                    'barcode' => $variant['barcode'] ?? null,
                    'image_url' => $product->image_url,
                    'description' => $product->description,
                    'delivery_instructions' => $product->delivery_instructions,
                    'unit' => $variant['unit'] ?? $product->unit,
                    'product_type' => Product::TYPE_VARIANT,
                    'uses_component_groups' => false,
                    'variant_attributes' => $attributes,
                    'cost_price' => (float) ($variant['cost_price'] ?? $product->cost_price),
                    'sale_price' => (float) ($variant['sale_price'] ?? $product->sale_price),
                    'is_active' => (bool) ($variant['is_active'] ?? true),
                    'is_visible_ecommerce' => (bool) ($variant['is_visible_ecommerce'] ?? $product->is_visible_ecommerce),
                ]
            );

            $existingIds[] = $child->id;
        }

        if ($existingIds !== []) {
            $product->variants()->whereNotIn('id', $existingIds)->update(['is_active' => false]);
        }
    }

    protected function syncVariantAttributeCatalog(Collection $definitions): void
    {
        $companyId = CompanyRules::currentCompanyId();

        $definitions
            ->map(function (array $definition): array {
                return [
                    'name' => trim((string) ($definition['name'] ?? '')),
                    'values' => collect($definition['values'] ?? [])
                        ->map(fn ($value) => trim((string) $value))
                        ->filter()
                        ->unique(fn ($value) => mb_strtolower($value))
                        ->values(),
                ];
            })
            ->filter(fn (array $definition): bool => $definition['name'] !== '' && $definition['values']->isNotEmpty())
            ->each(function (array $definition) use ($companyId): void {
                $attribute = ProductVariantAttribute::query()->firstOrCreate([
                    'company_id' => $companyId,
                    'name' => $definition['name'],
                ]);

                $definition['values']->each(function (string $value) use ($attribute): void {
                    $attribute->values()->firstOrCreate(['value' => $value]);
                });
            });
    }

    private function variantName(Product $product, array $variant, array $attributes): string
    {
        if (filled($variant['name'] ?? null)) {
            return trim((string) $variant['name']);
        }

        $parts = [];
        foreach ($attributes as $attribute => $value) {
            $parts[] = mb_strtolower((string) $attribute).' '.$value;
        }

        return trim($product->name.' - '.implode(' - ', $parts));
    }

    private function bumpStorefrontProductsCacheVersion(): void
    {
        StorefrontCache::bumpProductsVersion();
    }

}
