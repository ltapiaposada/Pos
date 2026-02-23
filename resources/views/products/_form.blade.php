@csrf
@php
    $selectedType = old('product_type', $product->product_type ?? \App\Models\Product::TYPE_SIMPLE);
    $rawKitItems = old(
        'kit_items',
        ($product->exists ? $product->kitItems->map(fn ($item) => [
            'component_product_id' => $item->component_product_id,
            'quantity' => $item->quantity,
        ])->toArray() : [])
    );
    $kitItems = collect($rawKitItems)
        ->filter(fn ($item) => ! empty($item['component_product_id']) || ! empty($item['quantity']))
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
                        <label class="field-label">Subir imagen (Cloudinary)</label>
                        <input id="image_file" name="image_file" type="file" accept="image/*" class="hidden">
                        <label for="image_file" id="image-upload-card" class="upload-dropzone">
                            <div class="upload-dropzone__icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-dropzone__title">Seleccionar imagen</div>
                            <div class="upload-dropzone__subtitle">Haz clic para subir PNG/JPG/WebP (max. 5MB)</div>
                        </label>
                        <span id="image-file-name" class="upload-file-name ms-2">Ningun archivo seleccionado</span>
                        <p id="image-file-help" class="text-xs text-base-content/60 mt-1">PNG/JPG/WebP hasta 5MB. Se sube a Cloudinary al guardar.</p>
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
        <select name="is_active" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_active', $product->is_active ?? true))>Si</option>
            <option value="0" @selected(old('is_active', $product->is_active ?? true) === false)>No</option>
        </select>
    </div>
    <div>
        <label class="field-label">Visible en e-commerce</label>
        <select name="is_visible_ecommerce" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_visible_ecommerce', $product->is_visible_ecommerce ?? true))>Si</option>
            <option value="0" @selected(old('is_visible_ecommerce', $product->is_visible_ecommerce ?? true) === false)>No</option>
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Descripcion</label>
        <input name="description" value="{{ old('description', $product->description ?? '') }}" class="input input-bordered w-full">
    </div>
</div>

<div id="kit-fields" class="mt-6 hidden">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold">Componentes del kit</h3>
        <button type="button" class="btn btn-outline btn-xs" id="add-kit-item">Agregar componente</button>
    </div>
    <div id="kit-items-wrapper" class="mt-3 space-y-2"></div>
    @error('kit_items')
        <p class="text-xs text-error mt-2">{{ $message }}</p>
    @enderror
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancelar</a>
</div>

<template id="kit-item-template">
    <div class="grid grid-cols-1 gap-2 items-end sm:grid-cols-12 kit-item-row">
        <div class="sm:col-span-8">
            <label class="field-label">Componente</label>
            <select class="select select-bordered w-full component-input">
                <option value="">Selecciona un producto</option>
                @foreach ($kitComponentCandidates as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->sku }})</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-3">
            <label class="field-label">Cantidad</label>
            <input type="number" min="0.001" step="0.001" class="input input-bordered w-full quantity-input" value="1">
        </div>
        <div class="sm:col-span-1">
            <button type="button" class="btn btn-outline-danger btn-xs remove-kit-item">X</button>
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

        function toggleSections() {
            const type = typeSelect.value;
            variantFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_VARIANT }}');
            kitFields.classList.toggle('hidden', type !== '{{ \App\Models\Product::TYPE_KIT }}');
        }

        function updateInputNames() {
            const rows = wrapper.querySelectorAll('.kit-item-row');
            rows.forEach((row, index) => {
                row.querySelector('.component-input').name = `kit_items[${index}][component_product_id]`;
                row.querySelector('.quantity-input').name = `kit_items[${index}][quantity]`;
            });
        }

        function createRow(item = null) {
            const node = template.content.firstElementChild.cloneNode(true);
            const componentInput = node.querySelector('.component-input');
            const quantityInput = node.querySelector('.quantity-input');
            const removeBtn = node.querySelector('.remove-kit-item');

            if (item) {
                componentInput.value = item.component_product_id ?? '';
                quantityInput.value = item.quantity ?? '1';
            }

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
        } else {
            createRow();
        }

        toggleSections();
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
                help.textContent = 'PNG/JPG/WebP hasta 5MB. Se sube a Cloudinary al guardar.';
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



