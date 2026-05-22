@csrf
@php
    $selectedType = old('product_type', $product->product_type ?? \App\Models\Product::TYPE_SIMPLE);
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
    $currentImage = old('image_url', $product->image_url ?? null);
@endphp

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
        <input name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" class="input input-bordered w-full">
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
        <input name="unit" value="{{ old('unit', $product->unit ?? 'unit') }}" class="input input-bordered w-full" required>
    </div>
    <div>
        <label class="field-label">Tipo de producto</label>
        <select name="product_type" id="product_type" class="select select-bordered w-full" required>
            <option value="{{ \App\Models\Product::TYPE_SIMPLE }}" @selected($selectedType === \App\Models\Product::TYPE_SIMPLE)>Simple</option>
            <option value="{{ \App\Models\Product::TYPE_KIT }}" @selected($selectedType === \App\Models\Product::TYPE_KIT)>Kit</option>
            <option value="{{ \App\Models\Product::TYPE_VARIANT }}" @selected($selectedType === \App\Models\Product::TYPE_VARIANT)>Variante</option>
        </select>
        @error('product_type')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div id="variant-fields">
        <label class="field-label">Producto base (solo variantes)</label>
        <select name="parent_product_id" class="select select-bordered w-full">
            <option value="">Selecciona un producto base</option>
            @foreach ($parentCandidates as $candidate)
                <option value="{{ $candidate->id }}" @selected((string) old('parent_product_id', $product->parent_product_id ?? '') === (string) $candidate->id)>
                    {{ $candidate->name }} ({{ $candidate->sku }})
                </option>
            @endforeach
        </select>
        @error('parent_product_id')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
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
</div>

<div id="modifier-fields" class="mt-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold">Componentes y elecciones del cliente</h3>
            <p class="text-xs text-base-content/60 mt-1">Define proteínas, acompañamientos o ingredientes que se pueden quitar/agregar.</p>
        </div>
        <button type="button" class="btn btn-outline btn-xs" id="add-modifier-group">Agregar grupo</button>
    </div>
    <div id="modifier-groups-wrapper" class="mt-3 space-y-4"></div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancelar</a>
</div>

<template id="kit-item-template">
    <div class="grid grid-cols-1 gap-2 items-end sm:grid-cols-12 kit-item-row">
        <div class="sm:col-span-5">
            <label class="field-label">Componente</label>
            <select class="select select-bordered w-full component-input">
                <option value="">Selecciona un producto</option>
                @foreach ($kitComponentCandidates as $candidate)
                    <option value="{{ $candidate->id }}" data-unit="{{ $candidate->unit }}">{{ $candidate->name }} ({{ $candidate->sku }})</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Consumo visible</label>
            <input type="number" min="0.001" step="0.001" class="input input-bordered w-full quantity-input" value="1">
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Unidad visible</label>
            <input type="text" class="input input-bordered w-full component-unit-input" placeholder="g, ml, und">
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Factor a stock</label>
            <input type="number" min="0.000001" step="0.000001" class="input input-bordered w-full component-factor-input" value="1">
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
            <input type="text" class="input input-bordered w-full modifier-option-unit" placeholder="g, ml, und">
        </div>
        <div>
            <label class="field-label">Factor a stock</label>
            <input type="number" min="0.000001" step="0.000001" class="input input-bordered w-full modifier-option-factor" value="1">
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
        const kitFields = document.getElementById('kit-fields');
        const wrapper = document.getElementById('kit-items-wrapper');
        const addBtn = document.getElementById('add-kit-item');
        const template = document.getElementById('kit-item-template');
        const initialItems = @json($kitItems);
        const modifierGroupsWrapper = document.getElementById('modifier-groups-wrapper');
        const addModifierGroupBtn = document.getElementById('add-modifier-group');
        const modifierGroupTemplate = document.getElementById('modifier-group-template');
        const modifierOptionTemplate = document.getElementById('modifier-option-template');
        const initialModifierGroups = @json($modifierGroups);

        function toggleSections() {
            const type = typeSelect.value;
            variantFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_VARIANT }}');
            kitFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_KIT }}');
            updateInputNames();
        }

        function updateInputNames() {
            const isKit = typeSelect.value === '{{ \App\Models\Product::TYPE_KIT }}';
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
            const quantityInput = node.querySelector('.quantity-input');
            const componentUnitInput = node.querySelector('.component-unit-input');
            const componentFactorInput = node.querySelector('.component-factor-input');
            const removeBtn = node.querySelector('.remove-kit-item');

            if (item) {
                componentInput.value = item.component_product_id ?? '';
                quantityInput.value = item.quantity ?? '1';
                componentUnitInput.value = item.component_unit ?? '';
                componentFactorInput.value = item.component_unit_factor ?? '1';
            }

            componentInput.addEventListener('change', function () {
                const selected = componentInput.options[componentInput.selectedIndex];
                if (!selected) {
                    return;
                }

                if (componentUnitInput.value.trim() === '' && selected.dataset.unit) {
                    componentUnitInput.value = selected.dataset.unit;
                }
            });

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

        typeSelect.addEventListener('change', toggleSections);

        if (initialItems.length > 0) {
            initialItems.forEach(item => createRow(item));
        } else if (typeSelect.value === '{{ \App\Models\Product::TYPE_KIT }}') {
            createRow();
        }

        toggleSections();

        function updateModifierInputNames() {
            const groups = modifierGroupsWrapper.querySelectorAll('.modifier-group-row');
            groups.forEach((groupRow, groupIndex) => {
                const nameInput = groupRow.querySelector('.modifier-group-name');
                const typeInput = groupRow.querySelector('.modifier-group-type');
                const requiredInput = groupRow.querySelector('.modifier-group-required');
                const minInput = groupRow.querySelector('.modifier-group-min');
                const maxInput = groupRow.querySelector('.modifier-group-max');
                const hiddenIdInput = groupRow.querySelector('.modifier-group-id');
                hiddenIdInput.name = `modifier_groups[${groupIndex}][id]`;
                nameInput.name = `modifier_groups[${groupIndex}][name]`;
                typeInput.name = `modifier_groups[${groupIndex}][selection_type]`;
                requiredInput.name = `modifier_groups[${groupIndex}][is_required]`;
                minInput.name = `modifier_groups[${groupIndex}][min_select]`;
                maxInput.name = `modifier_groups[${groupIndex}][max_select]`;

                const optionRows = groupRow.querySelectorAll('.modifier-option-row');
                optionRows.forEach((optionRow, optionIndex) => {
                    optionRow.querySelector('.modifier-option-id').name = `modifier_groups[${groupIndex}][options][${optionIndex}][id]`;
                    optionRow.querySelector('.modifier-option-product').name = `modifier_groups[${groupIndex}][options][${optionIndex}][product_id]`;
                    optionRow.querySelector('.modifier-option-quantity').name = `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_quantity]`;
                    optionRow.querySelector('.modifier-option-unit').name = `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_unit]`;
                    optionRow.querySelector('.modifier-option-factor').name = `modifier_groups[${groupIndex}][options][${optionIndex}][inventory_unit_factor]`;
                    optionRow.querySelector('.modifier-option-label').name = `modifier_groups[${groupIndex}][options][${optionIndex}][label]`;
                    optionRow.querySelector('.modifier-option-price').name = `modifier_groups[${groupIndex}][options][${optionIndex}][price_delta]`;
                    optionRow.querySelector('.modifier-option-default').name = `modifier_groups[${groupIndex}][options][${optionIndex}][is_default]`;
                    optionRow.querySelector('.modifier-option-active').name = `modifier_groups[${groupIndex}][options][${optionIndex}][is_active]`;
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
            const removeBtn = optionRow.querySelector('.remove-modifier-option');

            productInput.addEventListener('change', function () {
                if (labelInput.value.trim() !== '') {
                    const selected = productInput.options[productInput.selectedIndex];
                    if (unitInput.value.trim() === '' && selected && selected.dataset.unit) {
                        unitInput.value = selected.dataset.unit;
                    }
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
            });

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

        addModifierGroupBtn.addEventListener('click', function () {
            createModifierGroup();
        });

        if (initialModifierGroups.length > 0) {
            initialModifierGroups.forEach(group => createModifierGroup(group));
        }
    })();
</script>
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



