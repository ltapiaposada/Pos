@csrf
@php
    $hasVariantChildren = $product->exists && $product->relationLoaded('variants') && $product->variants->isNotEmpty();
    $selectedType = old('product_type', $hasVariantChildren ? \App\Models\Product::TYPE_VARIANT : ($product->product_type ?? \App\Models\Product::TYPE_SIMPLE));
    $rawKitItems = old(
        'kit_items',
        ($product->exists ? $product->kitItems->map(fn ($item) => [
            'component_product_id' => $item->component_product_id,
            'quantity' => $item->quantity,
            'component_unit' => $item->component_unit,
            'component_unit_factor' => $item->component_unit_factor,
        ])->toArray() : [])
    );
    $kitItems = collect($rawKitItems)
        ->filter(fn ($item) => ! empty($item['component_product_id']) || ! empty($item['quantity']))
        ->values()
        ->all();
    $rawModifierGroups = old(
        'modifier_groups',
        ($product->exists ? $product->modifierGroups->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'selection_type' => $group->selection_type,
            'is_required' => $group->is_required,
            'min_select' => $group->min_select,
            'max_select' => $group->max_select,
            'options' => $group->options->map(fn ($option) => [
                'id' => $option->id,
                'product_id' => $option->product_id,
                'inventory_quantity' => $option->inventory_quantity,
                'inventory_unit' => $option->inventory_unit,
                'inventory_unit_factor' => $option->inventory_unit_factor,
                'label' => $option->label,
                'price_delta' => $option->price_delta,
                'is_default' => $option->is_default,
                'is_active' => $option->is_active,
            ])->toArray(),
        ])->toArray() : [])
    );
    $modifierGroups = collect($rawModifierGroups)
        ->filter(fn ($group) => ! empty($group['name']) || ! empty($group['options']))
        ->values()
        ->all();
    $rawVariants = old(
        'variants',
        ($hasVariantChildren ? $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'attributes' => $variant->variant_attributes ?? [],
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
            'unit' => $variant->unit,
            'cost_price' => $variant->cost_price,
            'sale_price' => $variant->sale_price,
            'is_active' => $variant->is_active,
            'is_visible_ecommerce' => $variant->is_visible_ecommerce,
        ])->toArray() : [])
    );
    $variants = collect($rawVariants)
        ->filter(fn ($variant) => ! empty($variant['name']) || ! empty($variant['sku']) || collect($variant['attributes'] ?? [])->filter()->isNotEmpty())
        ->values()
        ->all();
    $variantCatalogOptions = collect($variantAttributes ?? [])
        ->map(fn ($attribute) => [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'values' => $attribute->values->pluck('value')->values()->all(),
        ])
        ->values()
        ->all();
    $rawVariantAttributeDefinitions = old('variant_attribute_definitions');
    if ($rawVariantAttributeDefinitions === null) {
        $rawVariantAttributeDefinitions = collect($variants)
            ->flatMap(fn ($variant) => collect($variant['attributes'] ?? [])->map(fn ($value, $name) => [
                'name' => $name,
                'value' => $value,
            ]))
            ->groupBy('name')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'values' => $items->pluck('value')->filter()->unique()->values()->all(),
            ])
            ->values()
            ->all();
    }
    $variantAttributeDefinitions = collect($rawVariantAttributeDefinitions)
        ->map(fn ($attribute) => [
            'name' => trim((string) ($attribute['name'] ?? '')),
            'values' => collect($attribute['values'] ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ])
        ->filter(fn ($attribute) => $attribute['name'] !== '' && ! empty($attribute['values']))
        ->values()
        ->all();
    $currentImage = old('image_url', $product->image_url ?? null);
    $usesComponentGroups = (string) old(
        'uses_component_groups',
        ($product->product_type ?? null) === \App\Models\Product::TYPE_KIT && ($product->uses_component_groups ?? false) ? '1' : '0'
    );
    $unitOptions = \App\Models\Product::unitOptions();
    $kitComponentOptions = $kitComponentCandidates->map(fn ($candidate) => [
        'id' => $candidate->id,
        'name' => $candidate->name,
        'sku' => $candidate->sku,
        'barcode' => $candidate->barcode,
        'unit' => $candidate->unit,
    ])->values()->all();
@endphp

@if ($errors->any())
    <div class="alert alert-error mb-5">
        <div>
            <p class="font-semibold">No se pudo guardar el producto:</p>
            <ul class="mt-1 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="form-grid">
    <div class="sm:col-span-2">
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">SKU</label>
        <input name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="input input-bordered w-full" required>
        @error('sku')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Codigo de barras</label>
        <div class="flex gap-2">
            <input id="product-barcode" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" class="input input-bordered w-full">
            <button id="open-product-barcode-scanner" type="button" class="btn btn-outline shrink-0">
                <i class="fa-solid fa-camera" aria-hidden="true"></i>
                Camara
            </button>
            <button id="open-product-remote-scanner" type="button" class="btn btn-outline shrink-0">
                <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
                Celular
            </button>
        </div>
        <p id="product-barcode-feedback" class="mt-1 text-xs text-base-content/60"></p>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Imagen del producto</label>
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
            <div class="grid gap-4 sm:grid-cols-[180px_1fr] sm:items-start">
                <div>
                    <img
                        id="product-image-preview"
                        src="{{ $currentImage ?: asset('images/product-placeholder.svg') }}"
                        alt="Imagen de producto"
                        style="width: 160px; height: 160px; object-fit: cover; border-radius: 12px; border: 1px solid #cbd5e1; background: #fff;"
                    >
                    <p class="text-[11px] text-base-content/60 mt-2">Vista previa</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <style>
                            .upload-dropzone {
                                display: block;
                                border: 2px dashed #93c5fd;
                                border-radius: 14px;
                                padding: 1rem;
                                background: linear-gradient(145deg, #f8fbff 0%, #eef6ff 100%);
                                cursor: pointer;
                                transition: all .2s ease;
                                text-align: center;
                            }
                            .upload-dropzone:hover {
                                border-color: #2563eb;
                                box-shadow: 0 10px 24px rgba(37, 99, 235, 0.14);
                                transform: translateY(-1px);
                            }
                            .upload-dropzone.is-selected {
                                border-color: #16a34a;
                                background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%);
                                box-shadow: 0 10px 24px rgba(22, 163, 74, 0.15);
                            }
                            .upload-dropzone__icon {
                                width: 44px;
                                height: 44px;
                                border-radius: 999px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                background: rgba(37, 99, 235, 0.12);
                                color: #1d4ed8;
                                margin: 0 auto .6rem;
                                font-size: 1.1rem;
                            }
                            .upload-dropzone__title {
                                font-weight: 700;
                                color: #0f172a;
                                margin-bottom: .2rem;
                            }
                            .upload-dropzone__subtitle {
                                font-size: .78rem;
                                color: #475569;
                            }
                            .upload-file-name {
                                font-size: .82rem;
                                color: #0f172a;
                                font-weight: 600;
                                margin-top: .4rem;
                            }
                        </style>
                        <label class="field-label">Subir imagen (Cloudflare R2)</label>
                        <input id="image_file" name="image_file" type="file" accept="image/*" class="hidden">
                        <label for="image_file" id="image-upload-card" class="upload-dropzone">
                            <div class="upload-dropzone__icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-dropzone__title">Seleccionar imagen</div>
                            <div class="upload-dropzone__subtitle">Haz clic para subir PNG/JPG/WebP (max. 5MB)</div>
                        </label>
                        <span id="image-file-name" class="upload-file-name ms-2">Ningun archivo seleccionado</span>
                        <p id="image-file-help" class="text-xs text-base-content/60 mt-1">PNG/JPG/WebP hasta 5MB. Se sube a Cloudflare R2 al guardar.</p>
                        @error('image_file')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label">URL manual (opcional)</label>
                        <input id="image_url" name="image_url" value="{{ old('image_url', $product->image_url ?? '') }}" class="input input-bordered w-full" placeholder="{{ asset('images/products/cafe.svg') }}">
                        <p class="text-xs text-base-content/60 mt-1">Si subes archivo, la URL manual se reemplaza automáticamente.</p>
                        @error('image_url')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <label class="field-label">Categoria</label>
        <select name="category_id" class="select select-bordered w-full">
            <option value="">Sin categoria</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Impuesto</label>
        <select name="tax_id" class="select select-bordered w-full">
            <option value="">Sin impuesto</option>
            @foreach ($taxes as $tax)
                <option value="{{ $tax->id }}" @selected(old('tax_id', $product->tax_id ?? null) == $tax->id)>{{ $tax->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Unidad</label>
        <select name="unit" class="select select-bordered w-full" required>
            @foreach ($unitOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('unit', $product->unit ?? 'unit') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('unit')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Tipo de producto</label>
        <select name="product_type" id="product_type" class="select select-bordered w-full" required>
            <option value="{{ \App\Models\Product::TYPE_SIMPLE }}" @selected($selectedType === \App\Models\Product::TYPE_SIMPLE)>Simple</option>
            <option value="{{ \App\Models\Product::TYPE_KIT }}" @selected($selectedType === \App\Models\Product::TYPE_KIT)>Kit</option>
            <option value="{{ \App\Models\Product::TYPE_VARIANT }}" @selected($selectedType === \App\Models\Product::TYPE_VARIANT)>Variante</option>
            <option value="{{ \App\Models\Product::TYPE_SERVICE }}" @selected($selectedType === \App\Models\Product::TYPE_SERVICE)>Servicio (sin inventario)</option>
            <option value="{{ \App\Models\Product::TYPE_DIGITAL }}" @selected($selectedType === \App\Models\Product::TYPE_DIGITAL)>Digital (sin inventario)</option>
            <option value="{{ \App\Models\Product::TYPE_SERIALIZED }}" @selected($selectedType === \App\Models\Product::TYPE_SERIALIZED)>Serializado</option>
            <option value="{{ \App\Models\Product::TYPE_BATCH }}" @selected($selectedType === \App\Models\Product::TYPE_BATCH)>Lote y vencimiento</option>
        </select>
        @error('product_type')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div id="variant-fields" class="sm:col-span-2 hidden" data-attribute-store-url="{{ route('products.variant-attributes.store') }}">
        <div class="rounded-xl border border-base-300 bg-base-100 p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold">Variantes del producto</h3>
                    <p class="mt-1 text-xs text-base-content/60">Agrega atributos y valores. La tabla se genera combinando los valores seleccionados.</p>
                </div>
                <button type="button" class="btn btn-outline btn-xs" id="open-create-variant-attribute">Crear atributo</button>
            </div>
            <div class="mt-3 max-w-md">
                <label class="field-label">Atributo existente</label>
                <select id="variant-attribute-select" class="select select-bordered w-full">
                    <option value="">Selecciona atributo</option>
                    @foreach ($variantCatalogOptions as $attribute)
                        <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline btn-sm mt-2" id="add-variant-attribute">Agregar</button>
            </div>
            <div id="variant-attributes-wrapper" class="mt-3 space-y-3"></div>
            <div class="mt-4 overflow-x-auto">
                <table class="table table-zebra w-full text-sm">
                    <thead>
                        <tr>
                            <th>SKU variante</th>
                            <th>Nombre generado</th>
                            <th>Codigo de barras</th>
                            <th>Costo</th>
                            <th>Precio</th>
                            <th>Activa</th>
                            <th>E-commerce</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody id="variant-rows-wrapper"></tbody>
                </table>
            </div>
            <p id="variant-validation-error" class="mt-2 hidden text-xs text-error"></p>
            @error('variants')
                <p class="mt-2 text-xs text-error">{{ $message }}</p>
            @enderror
            @if ($errors->has('variants.*'))
                <p class="mt-2 text-xs text-error">{{ $errors->first('variants.*') }}</p>
            @endif
        </div>
    </div>
    <div>
        <label class="field-label">Costo</label>
        <input name="cost_price" type="number" step="0.01" value="{{ old('cost_price', $product->cost_price ?? 0) }}" class="input input-bordered w-full" required>
    </div>
    <div>
        <label class="field-label">Precio venta</label>
        <input name="sale_price" type="number" step="0.01" value="{{ old('sale_price', $product->sale_price ?? 0) }}" class="input input-bordered w-full" required>
    </div>
    <div>
        <label class="field-label">Activo</label>
        @php($activeValue = (string) old('is_active', isset($product) ? (int) $product->is_active : 1))
        <select name="is_active" class="select select-bordered w-full" required>
            <option value="1" @selected($activeValue === '1')>Si</option>
            <option value="0" @selected($activeValue === '0')>No</option>
        </select>
        @error('is_active')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Visible en e-commerce</label>
        @php($visibleEcommerceValue = (string) old('is_visible_ecommerce', isset($product) ? (int) $product->is_visible_ecommerce : 1))
        <select name="is_visible_ecommerce" class="select select-bordered w-full" required>
            <option value="1" @selected($visibleEcommerceValue === '1')>Si</option>
            <option value="0" @selected($visibleEcommerceValue === '0')>No</option>
        </select>
        @error('is_visible_ecommerce')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Descripcion</label>
        <input name="description" value="{{ old('description', $product->description ?? '') }}" class="input input-bordered w-full">
    </div>
    <div id="digital-fields" class="sm:col-span-2">
        <label class="field-label">Instrucciones de entrega digital</label>
        <textarea name="delivery_instructions" rows="4" class="textarea textarea-bordered w-full" placeholder="Enlace, licencia o instrucciones que deben entregarse al cliente.">{{ old('delivery_instructions', $product->delivery_instructions ?? '') }}</textarea>
        <p class="mt-1 text-xs text-base-content/60">Solo se usa para productos digitales.</p>
    </div>
</div>

<div
    id="product-remote-scanner-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4"
    data-session-url="{{ route('pos.scanner.session') }}"
    data-poll-base-url="{{ url('pos/scanner/session') }}"
    data-csrf-token="{{ csrf_token() }}"
>
    <div class="w-full max-w-lg rounded-xl bg-base-100 p-4 shadow-xl">
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Celular como lector</h2>
                <p class="text-xs text-base-content/60">Cada nuevo escaneo reemplaza el codigo anterior.</p>
            </div>
            <button id="close-product-remote-scanner" type="button" class="btn btn-outline btn-sm">Cerrar</button>
        </div>
        <input id="product-remote-scanner-url" type="text" class="input input-bordered w-full text-xs" readonly value="Generando enlace...">
        <div class="mt-3 flex flex-wrap gap-2">
            <button id="copy-product-remote-scanner-url" type="button" class="btn btn-outline btn-sm">Copiar enlace</button>
            <a id="launch-product-remote-scanner" class="btn btn-primary btn-sm" href="#" target="_blank" rel="noopener">Abrir escaner</a>
        </div>
        <p id="product-remote-scanner-status" class="mt-2 text-xs text-base-content/60">Preparando lector. Puedes escanear varias veces hasta dejar el codigo correcto.</p>
    </div>
</div>

<div id="product-barcode-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-md rounded-xl bg-base-100 p-4 shadow-xl">
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Escanear codigo de barras</h2>
                <p class="text-xs text-base-content/60">Usa la camara trasera y centra toda la barra.</p>
            </div>
            <button id="close-product-barcode-scanner" type="button" class="btn btn-outline btn-sm">Cerrar</button>
        </div>
        <video id="product-barcode-preview" autoplay muted playsinline class="h-64 w-full rounded-xl bg-black object-cover"></video>
        <p id="product-barcode-status" class="mt-2 text-xs text-base-content/60">Camara detenida.</p>
    </div>
</div>

<div id="variant-attribute-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-lg rounded-xl bg-base-100 p-4 shadow-xl">
        <div class="mb-3 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Crear atributo</h2>
                <p class="text-xs text-base-content/60">Define el atributo y los valores que podras combinar en la tabla.</p>
            </div>
            <button id="close-variant-attribute-modal" type="button" class="btn btn-outline btn-sm">Cerrar</button>
        </div>
        <div>
            <label class="field-label">Nombre del atributo</label>
            <input id="variant-attribute-name" type="text" class="input input-bordered w-full" placeholder="Talla, Color, Tela">
        </div>
        <div class="mt-3">
            <div class="flex items-center justify-between gap-3">
                <label class="field-label mb-0">Valores</label>
                <button id="add-variant-attribute-value" type="button" class="btn btn-outline btn-xs">Agregar valor</button>
            </div>
            <div id="variant-attribute-values-wrapper" class="mt-2 space-y-2"></div>
        </div>
        <p id="variant-attribute-modal-error" class="mt-2 hidden text-xs text-error"></p>
        <div class="mt-4 flex justify-end gap-2">
            <button id="cancel-variant-attribute-modal" type="button" class="btn btn-outline btn-sm">Cancelar</button>
            <button id="save-variant-attribute" type="button" class="btn btn-primary btn-sm">Guardar atributo</button>
        </div>
    </div>
</div>

<div id="kit-mode-fields" class="mt-6 hidden">
    <label class="field-label">¿El kit maneja grupos de componentes?</label>
    <select id="uses_component_groups" name="uses_component_groups" class="select select-bordered w-full max-w-md">
        <option value="0" @selected($usesComponentGroups === '0')>No, usar componentes directos</option>
        <option value="1" @selected($usesComponentGroups === '1')>Sí, usar grupos de componentes</option>
    </select>
    <p class="mt-1 text-xs text-base-content/60">Los dos modos son exclusivos para evitar descontar el inventario dos veces.</p>
    @error('uses_component_groups')
        <p class="mt-1 text-xs text-error">{{ $message }}</p>
    @enderror
</div>

<div id="kit-fields" class="mt-6 hidden">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold">Receta o componentes del producto</h3>
            <p class="text-xs text-base-content/60 mt-1">Define cuanta materia prima consume cada unidad vendida. Ejemplo: 250 g con factor 0.001 descuenta 0.250 de un stock base en kg.</p>
        </div>
        <button type="button" class="btn btn-outline btn-xs" id="add-kit-item">Agregar componente</button>
    </div>
    <div id="kit-items-wrapper" class="mt-3 space-y-2"></div>
    @error('kit_items')
        <p class="text-xs text-error mt-2">{{ $message }}</p>
    @enderror
    @if ($errors->has('kit_items.*'))
        <p class="text-xs text-error mt-2">{{ $errors->first('kit_items.*') }}</p>
    @endif
</div>

<div id="modifier-fields" class="mt-6 hidden">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold">Componentes y elecciones del cliente</h3>
            <p class="text-xs text-base-content/60 mt-1">Define proteínas, acompañamientos o ingredientes que se pueden quitar/agregar.</p>
        </div>
        <button type="button" class="btn btn-outline btn-xs" id="add-modifier-group">Agregar grupo</button>
    </div>
    <div id="modifier-groups-wrapper" class="mt-3 space-y-4"></div>
    <p id="modifier-validation-error" class="mt-2 hidden text-xs text-error"></p>
    @error('modifier_groups')
        <p class="mt-2 text-xs text-error">{{ $message }}</p>
    @enderror
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancelar</a>
</div>

<template id="variant-attribute-template">
    <div class="rounded-xl border border-base-200 p-3 variant-attribute-row">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm font-semibold variant-attribute-name-display"></div>
                <div class="mt-2 flex flex-wrap gap-1 variant-attribute-values-display"></div>
                <div class="variant-attribute-definition-inputs"></div>
                <input type="hidden" class="variant-attribute-name">
                <input type="hidden" class="variant-attribute-values">
            </div>
            <button type="button" class="btn btn-outline-danger btn-xs remove-variant-attribute">Quitar</button>
        </div>
    </div>
</template>

<template id="variant-row-template">
    <tr class="variant-row">
        <td>
            <input type="hidden" class="variant-id-input">
            <input type="hidden" class="variant-name-input">
            <input type="hidden" class="variant-unit-input">
            <input type="text" class="input input-bordered input-sm w-32 variant-sku-input">
        </td>
        <td>
            <div class="min-w-56 text-sm font-medium variant-name-preview"></div>
            <div class="mt-1 text-[11px] text-base-content/60 variant-attributes-preview"></div>
        </td>
        <td>
            <input type="text" class="input input-bordered input-sm w-36 variant-barcode-input">
        </td>
        <td>
            <input type="number" min="0" step="0.01" class="input input-bordered input-sm w-28 variant-cost-input">
        </td>
        <td>
            <input type="number" min="0" step="0.01" class="input input-bordered input-sm w-28 variant-price-input">
        </td>
        <td>
            <select class="select select-bordered select-sm w-24 variant-active-input">
                <option value="1">Si</option>
                <option value="0">No</option>
            </select>
        </td>
        <td>
            <select class="select select-bordered select-sm w-24 variant-visible-input">
                <option value="1">Si</option>
                <option value="0">No</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-outline-danger btn-xs remove-variant-row">Quitar</button>
        </td>
    </tr>
</template>

<template id="kit-item-template">
    <div class="grid grid-cols-1 gap-2 items-end sm:grid-cols-12 kit-item-row">
        <div class="sm:col-span-5">
            <label class="field-label">Componente</label>
            <div class="relative component-autocomplete">
                <input type="hidden" class="component-input">
                <input
                    type="text"
                    class="input input-bordered w-full component-search"
                    placeholder="Buscar por nombre, SKU o codigo"
                    autocomplete="off"
                >
                <p class="mt-1 hidden text-xs text-error component-selection-error">Selecciona un producto de la lista.</p>
                <div class="component-results absolute z-40 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-base-300 bg-base-100 shadow-lg"></div>
            </div>
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Consumo visible</label>
            <input type="number" min="0.001" step="0.001" class="input input-bordered w-full quantity-input" value="1">
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Unidad visible</label>
            <select class="select select-bordered w-full component-unit-input">
                <option value="">Usar unidad del componente</option>
                @foreach ($unitOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Factor a stock</label>
            <input type="number" min="0.000001" step="0.000001" class="input input-bordered w-full component-factor-input" value="1">
            <p class="mt-1 text-[11px] text-base-content/60 factor-help">Automatico cuando las unidades son compatibles.</p>
        </div>
        <div class="sm:col-span-1">
            <button type="button" class="btn btn-outline-danger btn-xs remove-kit-item">X</button>
        </div>
    </div>
</template>

<template id="modifier-group-template">
    <div class="rounded-2xl border border-base-300 bg-base-100 p-4 modifier-group-row">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="field-label">Nombre del grupo</label>
                <input type="text" class="input input-bordered w-full modifier-group-name" placeholder="Proteína, Ingredientes removibles, Acompañantes">
            </div>
            <div>
                <label class="field-label">Tipo</label>
                <select class="select select-bordered w-full modifier-group-type">
                    <option value="single">Elegir una opción</option>
                    <option value="multiple">Elegir varias opciones</option>
                    <option value="remove">Quitar ingredientes</option>
                </select>
            </div>
            <div>
                <label class="field-label">Obligatorio</label>
                <select class="select select-bordered w-full modifier-group-required">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
            <div>
                <label class="field-label">Mínimo</label>
                <input type="number" min="0" class="input input-bordered w-full modifier-group-min" value="0">
            </div>
            <div>
                <label class="field-label">Máximo</label>
                <input type="number" min="0" class="input input-bordered w-full modifier-group-max" value="1">
            </div>
            <div class="flex items-end justify-end xl:col-span-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-modifier-group">Eliminar grupo</button>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium">Opciones</h4>
                <button type="button" class="btn btn-outline btn-xs add-modifier-option">Agregar opción</button>
            </div>
            <div class="modifier-options-wrapper mt-3 space-y-3"></div>
        </div>
    </div>
</template>

<template id="modifier-option-template">
    <div class="grid gap-3 rounded-xl border border-base-200 p-3 modifier-option-row md:grid-cols-2 xl:grid-cols-8">
        <div class="xl:col-span-2">
            <label class="field-label">Producto relacionado</label>
            <select class="select select-bordered w-full modifier-option-product">
                <option value="">Selecciona un producto</option>
                @foreach ($kitComponentCandidates as $candidate)
                    <option value="{{ $candidate->id }}" data-unit="{{ $candidate->unit }}">{{ $candidate->name }} ({{ $candidate->sku }})</option>
                @endforeach
            </select>
        </div>
        <div class="xl:col-span-2">
            <label class="field-label">Etiqueta visible</label>
            <input type="text" class="input input-bordered w-full modifier-option-label" placeholder="Carne, Cerdo, Sin cebolla">
        </div>
        <div>
            <label class="field-label">Consumo</label>
            <input type="number" min="0.001" step="0.001" class="input input-bordered w-full modifier-option-quantity" value="1">
        </div>
        <div>
            <label class="field-label">Unidad visible</label>
            <select class="select select-bordered w-full modifier-option-unit">
                <option value="">Usar unidad del producto</option>
                @foreach ($unitOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Factor a stock</label>
            <input type="number" min="0.000001" step="0.000001" class="input input-bordered w-full modifier-option-factor" value="1">
            <p class="mt-1 text-[11px] text-base-content/60 factor-help">Automatico cuando las unidades son compatibles.</p>
        </div>
        <div>
            <label class="field-label">Extra</label>
            <input type="number" min="0" step="0.01" class="input input-bordered w-full modifier-option-price" value="0">
        </div>
        <div>
            <label class="field-label">Por defecto</label>
            <select class="select select-bordered w-full modifier-option-default">
                <option value="0">No</option>
                <option value="1">Sí</option>
            </select>
        </div>
        <div>
            <label class="field-label">Activa</label>
            <select class="select select-bordered w-full modifier-option-active">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="flex items-end justify-end">
            <button type="button" class="btn btn-outline-danger btn-xs remove-modifier-option">Quitar</button>
        </div>
    </div>
</template>

<script>
    (function () {
        const typeSelect = document.getElementById('product_type');
        const variantFields = document.getElementById('variant-fields');
        const kitModeFields = document.getElementById('kit-mode-fields');
        const kitFields = document.getElementById('kit-fields');
        const usesComponentGroupsSelect = document.getElementById('uses_component_groups');
        const modifierFields = document.getElementById('modifier-fields');
        const digitalFields = document.getElementById('digital-fields');
        const wrapper = document.getElementById('kit-items-wrapper');
        const addBtn = document.getElementById('add-kit-item');
        const template = document.getElementById('kit-item-template');
        const initialItems = @json($kitItems);
        const kitComponentCandidates = @json($kitComponentOptions);
        const modifierGroupsWrapper = document.getElementById('modifier-groups-wrapper');
        const addModifierGroupBtn = document.getElementById('add-modifier-group');
        const modifierGroupTemplate = document.getElementById('modifier-group-template');
        const modifierOptionTemplate = document.getElementById('modifier-option-template');
        const modifierValidationError = document.getElementById('modifier-validation-error');
        const initialModifierGroups = @json($modifierGroups);
        const variantAttributesWrapper = document.getElementById('variant-attributes-wrapper');
        const addVariantAttributeBtn = document.getElementById('add-variant-attribute');
        const openCreateVariantAttributeBtn = document.getElementById('open-create-variant-attribute');
        const variantAttributeSelect = document.getElementById('variant-attribute-select');
        const variantAttributeModal = document.getElementById('variant-attribute-modal');
        const closeVariantAttributeModalBtn = document.getElementById('close-variant-attribute-modal');
        const cancelVariantAttributeModalBtn = document.getElementById('cancel-variant-attribute-modal');
        const saveVariantAttributeBtn = document.getElementById('save-variant-attribute');
        const addVariantAttributeValueBtn = document.getElementById('add-variant-attribute-value');
        const variantAttributeNameInput = document.getElementById('variant-attribute-name');
        const variantAttributeValuesWrapper = document.getElementById('variant-attribute-values-wrapper');
        const variantAttributeModalError = document.getElementById('variant-attribute-modal-error');
        const variantAttributeTemplate = document.getElementById('variant-attribute-template');
        const variantRowsWrapper = document.getElementById('variant-rows-wrapper');
        const variantRowTemplate = document.getElementById('variant-row-template');
        const variantValidationError = document.getElementById('variant-validation-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const variantAttributeStoreUrl = variantFields?.dataset.attributeStoreUrl || '';
        const initialVariants = @json($variants);
        const initialVariantAttributeDefinitions = @json($variantAttributeDefinitions);
        let variantAttributeCatalog = @json($variantCatalogOptions);
        let variantStateByKey = new Map();
        let removedVariantKeys = new Set();
        const gramsPerUnit = {
            g: 1,
            kg: 1000,
            libra: 500,
        };

        function automaticUnitFactor(fromUnit, toUnit) {
            if (!fromUnit || !toUnit) {
                return null;
            }
            if (fromUnit === toUnit) {
                return 1;
            }
            if (!(fromUnit in gramsPerUnit) || !(toUnit in gramsPerUnit)) {
                return null;
            }

            return gramsPerUnit[fromUnit] / gramsPerUnit[toUnit];
        }

        function syncFactorInput(unitInput, productInput, factorInput) {
            const selected = productInput.tagName === 'SELECT'
                ? productInput.options[productInput.selectedIndex]
                : productInput;
            const stockUnit = selected?.dataset.unit || '';
            const visibleUnit = unitInput.value || stockUnit;
            const automaticFactor = automaticUnitFactor(visibleUnit, stockUnit);
            const help = factorInput.parentElement.querySelector('.factor-help');

            if (automaticFactor !== null) {
                factorInput.value = automaticFactor.toFixed(6).replace(/0+$/, '').replace(/\.$/, '');
                factorInput.readOnly = true;
                if (help) {
                    help.textContent = `Automatico: 1 ${visibleUnit} = ${factorInput.value} ${stockUnit}.`;
                }
                return;
            }

            factorInput.readOnly = false;
            if (help) {
                help.textContent = 'Indica manualmente cuanto equivale en la unidad de inventario.';
            }
        }

        function updateVariantInputNames() {
            if (! variantRowsWrapper) {
                return;
            }

            const isVariantTemplate = typeSelect.value === '{{ \App\Models\Product::TYPE_VARIANT }}';
            variantRowsWrapper.querySelectorAll('.variant-row').forEach((row, index) => {
                row.querySelector('.variant-id-input').name = isVariantTemplate ? `variants[${index}][id]` : '';
                row.querySelector('.variant-name-input').name = isVariantTemplate ? `variants[${index}][name]` : '';
                row.querySelector('.variant-sku-input').name = isVariantTemplate ? `variants[${index}][sku]` : '';
                row.querySelector('.variant-barcode-input').name = isVariantTemplate ? `variants[${index}][barcode]` : '';
                row.querySelector('.variant-cost-input').name = isVariantTemplate ? `variants[${index}][cost_price]` : '';
                row.querySelector('.variant-price-input').name = isVariantTemplate ? `variants[${index}][sale_price]` : '';
                row.querySelector('.variant-unit-input').name = isVariantTemplate ? `variants[${index}][unit]` : '';
                row.querySelector('.variant-active-input').name = isVariantTemplate ? `variants[${index}][is_active]` : '';
                row.querySelector('.variant-visible-input').name = isVariantTemplate ? `variants[${index}][is_visible_ecommerce]` : '';

                const attributes = row._variantAttributes || {};
                row.querySelectorAll('.variant-attribute-hidden').forEach(input => input.remove());
                Object.entries(attributes).forEach(([attribute, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.className = 'variant-attribute-hidden';
                    input.name = isVariantTemplate ? `variants[${index}][attributes][${attribute}]` : '';
                    input.value = value;
                    row.appendChild(input);
                });
            });
        }

        function normalizeVariantAttributes(attributes) {
            return Object.fromEntries(
                Object.entries(attributes || {})
                    .map(([attribute, value]) => [String(attribute).trim(), String(value).trim()])
                    .filter(([attribute, value]) => attribute !== '' && value !== '')
            );
        }

        function variantKey(attributes) {
            return Object.entries(normalizeVariantAttributes(attributes))
                .map(([attribute, value]) => `${attribute}:${value}`)
                .join('|');
        }

        function variantNameFromAttributes(attributes) {
            const productName = document.querySelector('[name="name"]')?.value.trim() || 'Producto';
            const parts = Object.entries(normalizeVariantAttributes(attributes))
                .map(([attribute, value]) => `${attribute.toLowerCase()} ${value}`);

            return [productName, ...parts].join(' ');
        }

        function readVariantStateFromRow(row) {
            const key = row.dataset.variantKey || variantKey(row._variantAttributes || {});
            if (! key) {
                return;
            }

            variantStateByKey.set(key, {
                id: row.querySelector('.variant-id-input').value,
                sku: row.querySelector('.variant-sku-input').value,
                barcode: row.querySelector('.variant-barcode-input').value,
                cost_price: row.querySelector('.variant-cost-input').value,
                sale_price: row.querySelector('.variant-price-input').value,
                unit: row.querySelector('.variant-unit-input').value,
                is_active: row.querySelector('.variant-active-input').value,
                is_visible_ecommerce: row.querySelector('.variant-visible-input').value,
                name: row.querySelector('.variant-name-input').value,
                attributes: row._variantAttributes || {},
            });
        }

        function readCurrentVariantState() {
            variantRowsWrapper?.querySelectorAll('.variant-row').forEach(readVariantStateFromRow);
        }

        function selectedVariantAttributes() {
            if (! variantAttributesWrapper) {
                return [];
            }

            return Array.from(variantAttributesWrapper.querySelectorAll('.variant-attribute-row'))
                .map(row => ({
                    name: row.querySelector('.variant-attribute-name').value.trim(),
                    values: JSON.parse(row.querySelector('.variant-attribute-values').value || '[]'),
                }))
                .filter(attribute => attribute.name !== '' && attribute.values.length > 0);
        }

        function combinationsForAttributes(attributes) {
            if (attributes.length === 0) {
                return [];
            }

            return attributes.reduce((combinations, attribute) => {
                const next = [];
                combinations.forEach(combination => {
                    attribute.values.forEach(value => {
                        next.push({ ...combination, [attribute.name]: value });
                    });
                });
                return next;
            }, [{}]);
        }

        function nextVariantSku(index) {
            const parentSku = document.querySelector('[name="sku"]')?.value.trim() || 'VAR';
            return `${parentSku}-${index + 1}`;
        }

        function updateVariantAttributeDefinitionNames() {
            if (! variantAttributesWrapper) {
                return;
            }

            const isVariantTemplate = typeSelect.value === '{{ \App\Models\Product::TYPE_VARIANT }}';
            variantAttributesWrapper.querySelectorAll('.variant-attribute-row').forEach((row, index) => {
                const inputsWrapper = row.querySelector('.variant-attribute-definition-inputs');
                const name = row.querySelector('.variant-attribute-name').value.trim();
                const values = JSON.parse(row.querySelector('.variant-attribute-values').value || '[]');
                inputsWrapper.innerHTML = '';

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = isVariantTemplate ? `variant_attribute_definitions[${index}][name]` : '';
                nameInput.value = name;
                inputsWrapper.appendChild(nameInput);

                values.forEach((value, valueIndex) => {
                    const valueInput = document.createElement('input');
                    valueInput.type = 'hidden';
                    valueInput.name = isVariantTemplate ? `variant_attribute_definitions[${index}][values][${valueIndex}]` : '';
                    valueInput.value = value;
                    inputsWrapper.appendChild(valueInput);
                });
            });
        }

        function createVariantAttributeRow(attribute = null) {
            if (! variantAttributesWrapper || ! variantAttributeTemplate) {
                return;
            }

            const name = String(attribute?.name || '').trim();
            const values = Array.from(new Set((Array.isArray(attribute?.values) ? attribute.values : [])
                .map(value => String(value).trim())
                .filter(Boolean)));
            if (name === '' || values.length === 0) {
                return;
            }
            const existingNames = selectedVariantAttributes().map(item => item.name.toLowerCase());
            if (existingNames.includes(name.toLowerCase())) {
                if (variantValidationError) {
                    variantValidationError.textContent = 'Ese atributo ya fue agregado.';
                    variantValidationError.classList.remove('hidden');
                }
                return;
            }

            const row = variantAttributeTemplate.content.firstElementChild.cloneNode(true);
            row.querySelector('.variant-attribute-name').value = name;
            row.querySelector('.variant-attribute-values').value = JSON.stringify(values);
            row.querySelector('.variant-attribute-name-display').textContent = name;
            row.querySelector('.variant-attribute-values-display').innerHTML = '';
            values.forEach(value => {
                const chip = document.createElement('span');
                chip.className = 'badge badge-outline';
                chip.textContent = value;
                row.querySelector('.variant-attribute-values-display').appendChild(chip);
            });
            row.querySelector('.remove-variant-attribute').addEventListener('click', function () {
                readCurrentVariantState();
                row.remove();
                renderVariantRows();
            });

            variantAttributesWrapper.appendChild(row);
            renderVariantRows();
        }

        function createModalValueRow(value = '') {
            if (! variantAttributeValuesWrapper) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 variant-attribute-value-row';
            row.innerHTML = `
                <input type="text" class="input input-bordered input-sm w-full variant-attribute-value-input" placeholder="L, M, S">
                <button type="button" class="btn btn-outline-danger btn-xs remove-variant-attribute-value">Quitar</button>
            `;
            row.querySelector('.variant-attribute-value-input').value = value;
            row.querySelector('.remove-variant-attribute-value').addEventListener('click', function () {
                row.remove();
                if (variantAttributeValuesWrapper.querySelectorAll('.variant-attribute-value-row').length === 0) {
                    createModalValueRow();
                }
            });
            variantAttributeValuesWrapper.appendChild(row);
        }

        function resetVariantAttributeModal() {
            if (! variantAttributeNameInput || ! variantAttributeValuesWrapper) {
                return;
            }

            variantAttributeNameInput.value = '';
            variantAttributeValuesWrapper.innerHTML = '';
            createModalValueRow();
            if (variantAttributeModalError) {
                variantAttributeModalError.textContent = '';
                variantAttributeModalError.classList.add('hidden');
            }
        }

        function openVariantAttributeModal() {
            if (! variantAttributeModal) {
                return;
            }

            resetVariantAttributeModal();
            variantAttributeModal.classList.remove('hidden');
            variantAttributeModal.classList.add('flex');
            variantAttributeNameInput?.focus();
        }

        function closeVariantAttributeModal() {
            if (! variantAttributeModal) {
                return;
            }

            variantAttributeModal.classList.add('hidden');
            variantAttributeModal.classList.remove('flex');
        }

        function readModalAttribute() {
            const name = variantAttributeNameInput?.value.trim() || '';
            const values = Array.from(variantAttributeValuesWrapper?.querySelectorAll('.variant-attribute-value-input') || [])
                .map(input => input.value.trim())
                .filter(Boolean)
                .filter((value, index, list) => list.findIndex(item => item.toLowerCase() === value.toLowerCase()) === index);

            return { name, values };
        }

        function showVariantAttributeModalError(message) {
            if (! variantAttributeModalError) {
                return;
            }

            variantAttributeModalError.textContent = message;
            variantAttributeModalError.classList.remove('hidden');
        }

        function addAttributeToSelect(attribute) {
            if (! variantAttributeSelect) {
                return;
            }

            const existing = variantAttributeCatalog.find(item => item.name.toLowerCase() === attribute.name.toLowerCase());
            if (existing) {
                existing.values = Array.from(new Set([...existing.values, ...attribute.values]));
                return;
            }

            const catalogAttribute = {
                id: `new-${Date.now()}`,
                name: attribute.name,
                values: attribute.values,
            };
            variantAttributeCatalog.push(catalogAttribute);

            const option = document.createElement('option');
            option.value = catalogAttribute.id;
            option.textContent = catalogAttribute.name;
            variantAttributeSelect.appendChild(option);
        }

        function selectedCatalogAttribute() {
            if (! variantAttributeSelect || variantAttributeSelect.value === '') {
                return null;
            }

            return variantAttributeCatalog.find(attribute => String(attribute.id) === String(variantAttributeSelect.value)) || null;
        }

        async function persistVariantAttribute(attribute) {
            const response = await fetch(variantAttributeStoreUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(attribute),
            });

            if (! response.ok) {
                const payload = await response.json().catch(() => ({}));
                const errors = payload.errors || {};
                const firstError = Object.values(errors).flat()[0] || payload.message || 'No se pudo guardar el atributo.';
                throw new Error(firstError);
            }

            return response.json();
        }

        function renderVariantRows() {
            if (! variantRowsWrapper || ! variantRowTemplate) {
                return;
            }

            readCurrentVariantState();
            const combinations = combinationsForAttributes(selectedVariantAttributes());
            variantRowsWrapper.innerHTML = '';

            combinations.forEach((attributes, index) => {
                const key = variantKey(attributes);
                if (removedVariantKeys.has(key)) {
                    return;
                }
                const saved = variantStateByKey.get(key) || {};
                const row = variantRowTemplate.content.firstElementChild.cloneNode(true);
                const generatedName = saved.name || variantNameFromAttributes(attributes);
                row.dataset.variantKey = key;
                row._variantAttributes = attributes;

                row.querySelector('.variant-id-input').value = saved.id ?? '';
                row.querySelector('.variant-name-input').value = generatedName;
                row.querySelector('.variant-name-preview').textContent = generatedName;
                row.querySelector('.variant-attributes-preview').textContent = Object.entries(attributes)
                    .map(([attribute, value]) => `${attribute}: ${value}`)
                    .join(' | ');
                row.querySelector('.variant-sku-input').value = saved.sku || nextVariantSku(index);
                row.querySelector('.variant-barcode-input').value = saved.barcode ?? '';
                row.querySelector('.variant-cost-input').value = saved.cost_price ?? document.querySelector('[name="cost_price"]')?.value ?? '0';
                row.querySelector('.variant-price-input').value = saved.sale_price ?? document.querySelector('[name="sale_price"]')?.value ?? '0';
                row.querySelector('.variant-unit-input').value = saved.unit ?? document.querySelector('[name="unit"]')?.value ?? 'unit';
                row.querySelector('.variant-active-input').value = saved.is_active === '0' ? '0' : '1';
                row.querySelector('.variant-visible-input').value = saved.is_visible_ecommerce === '0' ? '0' : '1';

                row.querySelectorAll('input, select').forEach(input => {
                    input.addEventListener('input', () => readVariantStateFromRow(row));
                    input.addEventListener('change', () => readVariantStateFromRow(row));
                });

                row.querySelector('.remove-variant-row').addEventListener('click', function () {
                    readVariantStateFromRow(row);
                    removedVariantKeys.add(key);
                    variantStateByKey.delete(key);
                    row.remove();
                    updateVariantInputNames();
                });

                variantRowsWrapper.appendChild(row);
            });

            updateVariantInputNames();
            updateVariantAttributeDefinitionNames();
        }

        function initializeVariantGenerator() {
            const attributesByName = {};
            initialVariants.forEach(variant => {
                const attributes = normalizeVariantAttributes(variant.attributes || {});
                const key = variantKey(attributes);
                if (key) {
                    variantStateByKey.set(key, {
                        id: variant.id ?? '',
                        sku: variant.sku ?? '',
                        barcode: variant.barcode ?? '',
                        cost_price: variant.cost_price ?? '',
                        sale_price: variant.sale_price ?? '',
                        unit: variant.unit ?? 'unit',
                        is_active: variant.is_active === false || String(variant.is_active) === '0' ? '0' : '1',
                        is_visible_ecommerce: variant.is_visible_ecommerce === false || String(variant.is_visible_ecommerce) === '0' ? '0' : '1',
                        name: variant.name ?? '',
                        attributes,
                    });
                }

                Object.entries(attributes).forEach(([attribute, value]) => {
                    attributesByName[attribute] = attributesByName[attribute] || [];
                    if (! attributesByName[attribute].includes(value)) {
                        attributesByName[attribute].push(value);
                    }
                });
            });

            const attributesToPaint = initialVariantAttributeDefinitions.length
                ? initialVariantAttributeDefinitions
                : Object.entries(attributesByName).map(([name, values]) => ({ name, values }));

            attributesToPaint.forEach(({ name, values }) => {
                createVariantAttributeRow({ name, values });
            });

            renderVariantRows();
        }

        function toggleSections() {
            const type = typeSelect.value;
            const isKit = type === '{{ \App\Models\Product::TYPE_KIT }}';
            if (! isKit) {
                usesComponentGroupsSelect.value = '0';
            }
            const usesGroups = isKit && usesComponentGroupsSelect.value === '1';
            variantFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_VARIANT }}');
            kitModeFields.classList.toggle('hidden', ! isKit);
            kitFields.classList.toggle('hidden', ! isKit || usesGroups);
            modifierFields.classList.toggle('hidden', ! usesGroups);
            digitalFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_DIGITAL }}');

            if (isKit && ! usesGroups && wrapper.querySelectorAll('.kit-item-row').length === 0) {
                createRow();
            }
            if (usesGroups && modifierGroupsWrapper.querySelectorAll('.modifier-group-row').length === 0) {
                createModifierGroup();
            }
            updateVariantInputNames();
            updateVariantAttributeDefinitionNames();
            updateInputNames();
            updateModifierInputNames();
        }

        function updateInputNames() {
            const isKit = typeSelect.value === '{{ \App\Models\Product::TYPE_KIT }}'
                && usesComponentGroupsSelect.value !== '1';
            const rows = wrapper.querySelectorAll('.kit-item-row');
            rows.forEach((row, index) => {
                const componentInput = row.querySelector('.component-input');
                const quantityInput = row.querySelector('.quantity-input');
                const componentUnitInput = row.querySelector('.component-unit-input');
                const componentFactorInput = row.querySelector('.component-factor-input');

                if (isKit) {
                    componentInput.name = `kit_items[${index}][component_product_id]`;
                    quantityInput.name = `kit_items[${index}][quantity]`;
                    componentUnitInput.name = `kit_items[${index}][component_unit]`;
                    componentFactorInput.name = `kit_items[${index}][component_unit_factor]`;
                } else {
                    componentInput.name = '';
                    quantityInput.name = '';
                    componentUnitInput.name = '';
                    componentFactorInput.name = '';
                }
            });
        }

        function createRow(item = null) {
            const node = template.content.firstElementChild.cloneNode(true);
            const componentInput = node.querySelector('.component-input');
            const componentSearch = node.querySelector('.component-search');
            const componentResults = node.querySelector('.component-results');
            const componentSelectionError = node.querySelector('.component-selection-error');
            const quantityInput = node.querySelector('.quantity-input');
            const componentUnitInput = node.querySelector('.component-unit-input');
            const componentFactorInput = node.querySelector('.component-factor-input');
            const removeBtn = node.querySelector('.remove-kit-item');

            if (item) {
                componentInput.value = item.component_product_id ?? '';
                quantityInput.value = item.quantity ?? '1';
                componentUnitInput.value = item.component_unit ?? '';
                componentFactorInput.value = item.component_unit_factor ?? '1';

                const selectedCandidate = kitComponentCandidates.find(candidate => String(candidate.id) === String(componentInput.value));
                if (selectedCandidate) {
                    componentSearch.value = `${selectedCandidate.name} (${selectedCandidate.sku})`;
                    componentInput.dataset.unit = selectedCandidate.unit || '';
                }
            }

            function selectComponent(candidate) {
                componentInput.value = candidate.id;
                componentInput.dataset.unit = candidate.unit || '';
                componentSearch.value = `${candidate.name} (${candidate.sku})`;
                componentSearch.setCustomValidity('');
                componentSelectionError.classList.add('hidden');
                componentResults.classList.add('hidden');

                if (componentUnitInput.value.trim() === '' && candidate.unit) {
                    componentUnitInput.value = candidate.unit;
                }
                syncFactorInput(componentUnitInput, componentInput, componentFactorInput);
            }

            function renderComponentResults() {
                const term = componentSearch.value.trim().toLowerCase();
                const matches = kitComponentCandidates
                    .filter(candidate => {
                        if (term === '') {
                            return true;
                        }

                        return [candidate.name, candidate.sku, candidate.barcode]
                            .some(value => String(value || '').toLowerCase().includes(term));
                    })
                    .slice(0, 12);

                componentResults.innerHTML = '';
                matches.forEach(candidate => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-base-200';
                    button.innerHTML = `<span class="font-medium"></span><span class="ml-2 text-xs text-base-content/60"></span>`;
                    button.querySelector('.font-medium').textContent = candidate.name;
                    button.querySelector('.text-xs').textContent = [
                        candidate.sku ? `SKU ${candidate.sku}` : '',
                        candidate.barcode ? `Codigo ${candidate.barcode}` : '',
                    ].filter(Boolean).join(' - ');
                    button.addEventListener('mousedown', event => {
                        event.preventDefault();
                        selectComponent(candidate);
                    });
                    componentResults.appendChild(button);
                });

                if (matches.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'px-3 py-2 text-xs text-base-content/60';
                    empty.textContent = 'Sin coincidencias';
                    componentResults.appendChild(empty);
                }

                componentResults.classList.remove('hidden');
            }

            componentSearch.addEventListener('focus', renderComponentResults);
            componentSearch.addEventListener('input', () => {
                componentInput.value = '';
                componentInput.dataset.unit = '';
                componentSearch.setCustomValidity('');
                componentSelectionError.classList.add('hidden');
                renderComponentResults();
            });
            componentSearch.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    componentResults.classList.add('hidden');
                }
            });
            componentSearch.addEventListener('blur', () => {
                window.setTimeout(() => componentResults.classList.add('hidden'), 100);
            });
            componentUnitInput.addEventListener('change', () => syncFactorInput(componentUnitInput, componentInput, componentFactorInput));
            syncFactorInput(componentUnitInput, componentInput, componentFactorInput);

            removeBtn.addEventListener('click', function () {
                node.remove();
                updateInputNames();
            });

            wrapper.appendChild(node);
            updateInputNames();
        }

        addBtn.addEventListener('click', function () {
            createRow();
        });

        if (addVariantAttributeBtn) {
            addVariantAttributeBtn.addEventListener('click', function () {
                const attribute = selectedCatalogAttribute();
                variantAttributeSelect?.setCustomValidity('');
                if (! attribute) {
                    variantAttributeSelect?.setCustomValidity('Selecciona atributo.');
                    variantAttributeSelect?.reportValidity();
                    return;
                }

                createVariantAttributeRow(attribute);
                if (variantAttributeSelect) {
                    variantAttributeSelect.value = '';
                }
            });
        }

        openCreateVariantAttributeBtn?.addEventListener('click', openVariantAttributeModal);
        closeVariantAttributeModalBtn?.addEventListener('click', closeVariantAttributeModal);
        cancelVariantAttributeModalBtn?.addEventListener('click', closeVariantAttributeModal);
        addVariantAttributeValueBtn?.addEventListener('click', function () {
            createModalValueRow();
        });
        saveVariantAttributeBtn?.addEventListener('click', async function () {
            const attribute = readModalAttribute();
            if (attribute.name === '') {
                showVariantAttributeModalError('Escribe el nombre del atributo.');
                variantAttributeNameInput?.focus();
                return;
            }
            if (attribute.values.length === 0) {
                showVariantAttributeModalError('Agrega al menos un valor para el atributo.');
                variantAttributeValuesWrapper?.querySelector('.variant-attribute-value-input')?.focus();
                return;
            }

            saveVariantAttributeBtn.disabled = true;
            try {
                const savedAttribute = await persistVariantAttribute(attribute);
                addAttributeToSelect(savedAttribute);
                createVariantAttributeRow(savedAttribute);
                closeVariantAttributeModal();
            } catch (error) {
                showVariantAttributeModalError(error.message || 'No se pudo guardar el atributo.');
            } finally {
                saveVariantAttributeBtn.disabled = false;
            }
        });

        typeSelect.addEventListener('change', toggleSections);
        usesComponentGroupsSelect.addEventListener('change', toggleSections);

        typeSelect.closest('form').addEventListener('submit', event => {
            if (typeSelect.value === '{{ \App\Models\Product::TYPE_VARIANT }}') {
                const rows = Array.from(variantRowsWrapper.querySelectorAll('.variant-row'));
                let firstInvalidField = null;
                let message = '';

                rows.forEach(row => {
                    const nameInput = row.querySelector('.variant-name-input');
                    const skuInput = row.querySelector('.variant-sku-input');

                    [nameInput, skuInput].forEach(input => input.setCustomValidity(''));

                    if (skuInput.value.trim() === '' && firstInvalidField === null) {
                        skuInput.setCustomValidity('Cada variante debe tener SKU.');
                        firstInvalidField = skuInput;
                        message = 'Cada variante debe tener SKU.';
                    }
                });

                if (rows.length === 0 && firstInvalidField === null) {
                    const firstAttributeInput = variantAttributeSelect;
                    firstInvalidField = firstAttributeInput;
                    if (firstInvalidField) {
                        firstInvalidField.setCustomValidity('Agrega atributos y valores para generar variantes.');
                    }
                    message = 'Agrega atributos con valores para generar variantes.';
                }

                if (firstInvalidField) {
                    event.preventDefault();
                    if (variantValidationError) {
                        variantValidationError.textContent = message;
                        variantValidationError.classList.remove('hidden');
                    }
                    firstInvalidField.reportValidity();
                    firstInvalidField.focus();
                    return;
                }

                if (variantValidationError) {
                    variantValidationError.textContent = '';
                    variantValidationError.classList.add('hidden');
                }
                return;
            }

            if (typeSelect.value !== '{{ \App\Models\Product::TYPE_KIT }}') {
                return;
            }

            if (usesComponentGroupsSelect.value === '1') {
                const groupRows = Array.from(modifierGroupsWrapper.querySelectorAll('.modifier-group-row'));
                let firstInvalidField = null;
                let message = '';

                groupRows.forEach(groupRow => {
                    const nameInput = groupRow.querySelector('.modifier-group-name');
                    const typeInput = groupRow.querySelector('.modifier-group-type');
                    const optionRows = Array.from(groupRow.querySelectorAll('.modifier-option-row'));
                    const validOptions = optionRows.filter(optionRow => {
                        const productInput = optionRow.querySelector('.modifier-option-product');
                        const labelInput = optionRow.querySelector('.modifier-option-label');
                        return productInput.value !== '' || labelInput.value.trim() !== '';
                    });

                    nameInput.setCustomValidity('');
                    optionRows.forEach(optionRow => {
                        optionRow.querySelector('.modifier-option-product').setCustomValidity('');
                        optionRow.querySelector('.modifier-option-label').setCustomValidity('');
                        optionRow.querySelector('.modifier-option-quantity').setCustomValidity('');
                    });

                    if (nameInput.value.trim() === '' && firstInvalidField === null) {
                        nameInput.setCustomValidity('Escribe el nombre del grupo.');
                        firstInvalidField = nameInput;
                        message = 'Cada grupo debe tener nombre.';
                    }

                    if (validOptions.length === 0 && firstInvalidField === null) {
                        const labelInput = optionRows[0]?.querySelector('.modifier-option-label');
                        if (labelInput) {
                            labelInput.setCustomValidity('Agrega al menos una opcion al grupo.');
                            firstInvalidField = labelInput;
                            message = 'Cada grupo debe tener al menos una opcion.';
                        }
                    }

                    if (['single', 'multiple'].includes(typeInput.value)) {
                        validOptions.forEach(optionRow => {
                            const productInput = optionRow.querySelector('.modifier-option-product');
                            const quantityInput = optionRow.querySelector('.modifier-option-quantity');

                            if (productInput.value === '' && firstInvalidField === null) {
                                productInput.setCustomValidity('Selecciona el producto que se descuenta del inventario.');
                                firstInvalidField = productInput;
                                message = 'Las opciones de seleccion deben apuntar a un producto existente.';
                            }
                            if (parseFloat(quantityInput.value || '0') <= 0 && firstInvalidField === null) {
                                quantityInput.setCustomValidity('Indica la cantidad consumida.');
                                firstInvalidField = quantityInput;
                                message = 'Indica la cantidad consumida por cada opcion.';
                            }
                        });
                    }
                });

                if (firstInvalidField) {
                    event.preventDefault();
                    if (modifierValidationError) {
                        modifierValidationError.textContent = message;
                        modifierValidationError.classList.remove('hidden');
                    }
                    firstInvalidField.reportValidity();
                    firstInvalidField.focus();
                    return;
                }

                if (modifierValidationError) {
                    modifierValidationError.textContent = '';
                    modifierValidationError.classList.add('hidden');
                }
                return;
            }

            let firstInvalidSearch = null;
            wrapper.querySelectorAll('.kit-item-row').forEach(row => {
                const componentInput = row.querySelector('.component-input');
                const componentSearch = row.querySelector('.component-search');
                const componentSelectionError = row.querySelector('.component-selection-error');
                const hasSelection = componentInput.value !== '';

                componentSearch.setCustomValidity(hasSelection ? '' : 'Selecciona un producto de la lista.');
                componentSelectionError.classList.toggle('hidden', hasSelection);

                if (! hasSelection && firstInvalidSearch === null) {
                    firstInvalidSearch = componentSearch;
                }
            });

            if (firstInvalidSearch) {
                event.preventDefault();
                firstInvalidSearch.reportValidity();
                firstInvalidSearch.focus();
            }
        });

        initializeVariantGenerator();

        if (initialItems.length > 0) {
            initialItems.forEach(item => createRow(item));
        } else if (typeSelect.value === '{{ \App\Models\Product::TYPE_KIT }}') {
            createRow();
        }

        toggleSections();

        function updateModifierInputNames() {
            if (! modifierGroupsWrapper) {
                return;
            }

            const groups = modifierGroupsWrapper.querySelectorAll('.modifier-group-row');
            const usesGroups = typeSelect.value === '{{ \App\Models\Product::TYPE_KIT }}'
                && usesComponentGroupsSelect.value === '1';
            groups.forEach((groupRow, groupIndex) => {
                const nameInput = groupRow.querySelector('.modifier-group-name');
                const typeInput = groupRow.querySelector('.modifier-group-type');
                const requiredInput = groupRow.querySelector('.modifier-group-required');
                const minInput = groupRow.querySelector('.modifier-group-min');
                const maxInput = groupRow.querySelector('.modifier-group-max');
                const hiddenIdInput = groupRow.querySelector('.modifier-group-id');
                hiddenIdInput.name = usesGroups ? `modifier_groups[${groupIndex}][id]` : '';
                nameInput.name = usesGroups ? `modifier_groups[${groupIndex}][name]` : '';
                typeInput.name = usesGroups ? `modifier_groups[${groupIndex}][selection_type]` : '';
                requiredInput.name = usesGroups ? `modifier_groups[${groupIndex}][is_required]` : '';
                minInput.name = usesGroups ? `modifier_groups[${groupIndex}][min_select]` : '';
                maxInput.name = usesGroups ? `modifier_groups[${groupIndex}][max_select]` : '';

                const optionRows = groupRow.querySelectorAll('.modifier-option-row');
                optionRows.forEach((optionRow, optionIndex) => {
                    optionRow.querySelector('.modifier-option-id').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][id]` : '';
                    optionRow.querySelector('.modifier-option-product').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][product_id]` : '';
                    optionRow.querySelector('.modifier-option-quantity').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_quantity]` : '';
                    optionRow.querySelector('.modifier-option-unit').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_unit]` : '';
                    optionRow.querySelector('.modifier-option-factor').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_unit_factor]` : '';
                    optionRow.querySelector('.modifier-option-label').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][label]` : '';
                    optionRow.querySelector('.modifier-option-price').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][price_delta]` : '';
                    optionRow.querySelector('.modifier-option-default').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][is_default]` : '';
                    optionRow.querySelector('.modifier-option-active').name = usesGroups ? `modifier_groups[${groupIndex}][options][${optionIndex}][is_active]` : '';
                });
            });
        }

        function syncGroupConstraints(groupRow) {
            const typeInput = groupRow.querySelector('.modifier-group-type');
            const requiredInput = groupRow.querySelector('.modifier-group-required');
            const minInput = groupRow.querySelector('.modifier-group-min');
            const maxInput = groupRow.querySelector('.modifier-group-max');
            const defaultInputs = groupRow.querySelectorAll('.modifier-option-default');
            const priceInputs = groupRow.querySelectorAll('.modifier-option-price');

            if (typeInput.value === 'single') {
                maxInput.value = '1';
                maxInput.setAttribute('readonly', 'readonly');
                if (requiredInput.value === '1') {
                    minInput.value = '1';
                } else if (parseInt(minInput.value || '0', 10) > 1) {
                    minInput.value = '0';
                }
            } else {
                maxInput.removeAttribute('readonly');
            }

            if (typeInput.value === 'remove') {
                defaultInputs.forEach(input => {
                    input.value = '1';
                    input.setAttribute('readonly', 'readonly');
                });
                priceInputs.forEach(input => {
                    input.value = '0';
                    input.setAttribute('readonly', 'readonly');
                });
            } else {
                defaultInputs.forEach(input => input.removeAttribute('readonly'));
                priceInputs.forEach(input => input.removeAttribute('readonly'));
            }
        }

        function bindOptionRow(optionRow, groupRow) {
            const productInput = optionRow.querySelector('.modifier-option-product');
            const labelInput = optionRow.querySelector('.modifier-option-label');
            const unitInput = optionRow.querySelector('.modifier-option-unit');
            const factorInput = optionRow.querySelector('.modifier-option-factor');
            const removeBtn = optionRow.querySelector('.remove-modifier-option');

            productInput.addEventListener('change', function () {
                if (labelInput.value.trim() !== '') {
                    const selected = productInput.options[productInput.selectedIndex];
                    if (unitInput.value.trim() === '' && selected && selected.dataset.unit) {
                        unitInput.value = selected.dataset.unit;
                    }
                    syncFactorInput(unitInput, productInput, factorInput);
                    return;
                }
                const selected = productInput.options[productInput.selectedIndex];
                if (!selected || !selected.textContent) {
                    return;
                }
                const label = selected.textContent.replace(/\s*\([^)]*\)\s*$/, '').trim();
                labelInput.value = label;
                if (unitInput.value.trim() === '' && selected.dataset.unit) {
                    unitInput.value = selected.dataset.unit;
                }
                syncFactorInput(unitInput, productInput, factorInput);
            });
            unitInput.addEventListener('change', () => syncFactorInput(unitInput, productInput, factorInput));
            syncFactorInput(unitInput, productInput, factorInput);

            removeBtn.addEventListener('click', function () {
                optionRow.remove();
                updateModifierInputNames();
            });

            syncGroupConstraints(groupRow);
        }

        function createOptionRow(groupRow, option = null) {
            const optionRow = modifierOptionTemplate.content.firstElementChild.cloneNode(true);
            const hiddenIdInput = document.createElement('input');
            hiddenIdInput.type = 'hidden';
            hiddenIdInput.className = 'modifier-option-id';
            optionRow.prepend(hiddenIdInput);

            if (option) {
                hiddenIdInput.value = option.id ?? '';
                optionRow.querySelector('.modifier-option-product').value = option.product_id ?? '';
                optionRow.querySelector('.modifier-option-quantity').value = option.inventory_quantity ?? '1';
                optionRow.querySelector('.modifier-option-unit').value = option.inventory_unit ?? '';
                optionRow.querySelector('.modifier-option-factor').value = option.inventory_unit_factor ?? '1';
                optionRow.querySelector('.modifier-option-label').value = option.label ?? '';
                optionRow.querySelector('.modifier-option-price').value = option.price_delta ?? '0';
                optionRow.querySelector('.modifier-option-default').value = option.is_default ? '1' : '0';
                optionRow.querySelector('.modifier-option-active').value = option.is_active === false ? '0' : '1';
            }

            groupRow.querySelector('.modifier-options-wrapper').appendChild(optionRow);
            bindOptionRow(optionRow, groupRow);
            updateModifierInputNames();
        }

        function createModifierGroup(group = null) {
            if (! modifierGroupsWrapper || ! modifierGroupTemplate || ! modifierOptionTemplate) {
                return;
            }

            const groupRow = modifierGroupTemplate.content.firstElementChild.cloneNode(true);
            const hiddenIdInput = document.createElement('input');
            hiddenIdInput.type = 'hidden';
            hiddenIdInput.className = 'modifier-group-id';
            groupRow.prepend(hiddenIdInput);

            if (group) {
                hiddenIdInput.value = group.id ?? '';
                groupRow.querySelector('.modifier-group-name').value = group.name ?? '';
                groupRow.querySelector('.modifier-group-type').value = group.selection_type ?? 'single';
                groupRow.querySelector('.modifier-group-required').value = group.is_required ? '1' : '0';
                groupRow.querySelector('.modifier-group-min').value = group.min_select ?? 0;
                groupRow.querySelector('.modifier-group-max').value = group.max_select ?? 1;
            }

            groupRow.querySelector('.remove-modifier-group').addEventListener('click', function () {
                groupRow.remove();
                updateModifierInputNames();
            });

            groupRow.querySelector('.add-modifier-option').addEventListener('click', function () {
                createOptionRow(groupRow);
            });

            groupRow.querySelector('.modifier-group-type').addEventListener('change', function () {
                syncGroupConstraints(groupRow);
            });
            groupRow.querySelector('.modifier-group-required').addEventListener('change', function () {
                syncGroupConstraints(groupRow);
            });

            modifierGroupsWrapper.appendChild(groupRow);

            if (group && Array.isArray(group.options) && group.options.length > 0) {
                group.options.forEach(option => createOptionRow(groupRow, option));
            } else {
                createOptionRow(groupRow);
            }

            syncGroupConstraints(groupRow);
            updateModifierInputNames();
        }

        if (addModifierGroupBtn && modifierGroupsWrapper && modifierGroupTemplate && modifierOptionTemplate) {
            addModifierGroupBtn.addEventListener('click', function () {
                createModifierGroup();
            });

            if (initialModifierGroups.length > 0) {
                initialModifierGroups.forEach(group => createModifierGroup(group));
            }
        }
    })();
</script>
@vite('resources/js/product-barcode-scanner.js')
<script>
    (function () {
        const fileInput = document.getElementById('image_file');
        const preview = document.getElementById('product-image-preview');
        const help = document.getElementById('image-file-help');
        const fileName = document.getElementById('image-file-name');
        const uploadCard = document.getElementById('image-upload-card');
        const urlInput = document.getElementById('image_url');

        if (!fileInput || !preview) {
            return;
        }

        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

            if (!file) {
                help.textContent = 'PNG/JPG/WebP hasta 5MB. Se sube a Cloudflare R2 al guardar.';
                if (fileName) {
                    fileName.textContent = 'Ningun archivo seleccionado';
                }
                if (uploadCard) {
                    uploadCard.classList.remove('is-selected');
                }
                if (urlInput && urlInput.value.trim() !== '') {
                    preview.src = urlInput.value.trim();
                }
                return;
            }

            help.textContent = `Archivo seleccionado: ${file.name}`;
            if (fileName) {
                fileName.textContent = file.name;
            }
            if (uploadCard) {
                uploadCard.classList.add('is-selected');
            }
            const reader = new FileReader();
            reader.onload = function (event) {
                if (event.target && typeof event.target.result === 'string') {
                    preview.src = event.target.result;
                }
            };
            reader.readAsDataURL(file);
        });

        if (urlInput) {
            urlInput.addEventListener('blur', function () {
                if (fileInput.files && fileInput.files.length > 0) {
                    return;
                }
                const value = urlInput.value.trim();
                if (value !== '') {
                    preview.src = value;
                }
            });
        }
    })();
</script>



