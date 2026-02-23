@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Configuracion del negocio</h1>
                <p class="page-subtitle">Datos fiscales, logo y preferencias</p>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="mt-6 panel" enctype="multipart/form-data">
        <div class="panel-body">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="field-label">Logo</label>
                    <div class="mt-2 grid gap-4 lg:grid-cols-[220px,1fr] lg:items-start">
                        <div class="rounded-xl border border-base-200 bg-base-100 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Vista previa</div>
                            <div class="mt-2 flex h-28 items-center gap-3 rounded-xl border border-dashed border-base-200 bg-base-200/40 px-3">
                                <img
                                    src="{{ $business['logo_url'] ?? asset('images/product-placeholder.svg') }}"
                                    alt="{{ empty($business['logo_url']) ? 'Sin logo' : 'Logo' }}"
                                    class="h-16 w-16 rounded-lg border border-base-300 object-cover bg-base-100"
                                    id="logo-preview"
                                >
                                <div class="min-w-0">
                                    <div class="text-xs text-base-content/60">Nombre de empresa</div>
                                    <div class="truncate text-sm font-semibold" id="company-name-preview">{{ old('name', $business['name'] ?? config('app.name', 'Punto de venta')) }}</div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="upload-logo">
                                    Subir logo
                                </button>
                                <span class="text-xs text-base-content/60" id="upload-status"></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border-2 border-dashed border-base-300 bg-base-200/40 px-4 py-4 text-sm text-base-content/70 hover:border-primary/60">
                                <div>
                                    <div class="font-semibold text-base-content">Arrastra el logo aqui</div>
                                    <div class="text-xs text-base-content/60">o haz clic para seleccionar (PNG/JPG, max 2MB)</div>
                                </div>
                                <span class="badge badge-outline">Elegir archivo</span>
                                <input type="file" name="logo" accept="image/*" class="hidden" id="logo-input">
                            </label>
                            <div>
                            <label class="field-label">URL del logo (opcional)</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <input name="logo_url" value="{{ old('logo_url', $business['logo_url'] ?? '') }}" class="input input-bordered w-full sm:flex-1" placeholder="https://...">
                                    <button type="button" class="btn btn-outline btn-sm" id="clear-logo">Limpiar</button>
                                </div>
                                @error('logo_url')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-base-content/60">Sube primero y luego guarda para registrar la URL.</p>
                            </div>
                        </div>
                    </div>
                    @error('logo')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">QR de pago</label>
                    <div class="mt-2 grid gap-4 lg:grid-cols-[220px,1fr] lg:items-start">
                        <div class="rounded-xl border border-base-200 bg-base-100 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Vista previa</div>
                            <div class="mt-2 flex h-28 items-center justify-center rounded-xl border border-dashed border-base-200 bg-base-200/40 px-3">
                                <img
                                    src="{{ $business['payment_qr_url'] ?? asset('images/payment-qr-sample.svg') }}"
                                    alt="{{ empty($business['payment_qr_url']) ? 'Sin QR' : 'QR de pago' }}"
                                    class="h-20 w-20 rounded-lg border border-base-300 object-cover bg-base-100"
                                    id="qr-preview"
                                >
                            </div>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="upload-qr">
                                    Subir QR
                                </button>
                                <span class="text-xs text-base-content/60" id="qr-upload-status"></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border-2 border-dashed border-base-300 bg-base-200/40 px-4 py-4 text-sm text-base-content/70 hover:border-primary/60">
                                <div>
                                    <div class="font-semibold text-base-content">Arrastra el QR aqui</div>
                                    <div class="text-xs text-base-content/60">o haz clic para seleccionar (PNG/JPG, max 2MB)</div>
                                </div>
                                <span class="badge badge-outline">Elegir archivo</span>
                                <input type="file" name="payment_qr" accept="image/*" class="hidden" id="qr-input">
                            </label>
                            <div>
                                <label class="field-label">URL del QR (opcional)</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <input name="payment_qr_url" value="{{ old('payment_qr_url', $business['payment_qr_url'] ?? '') }}" class="input input-bordered w-full sm:flex-1" placeholder="https://...">
                                    <button type="button" class="btn btn-outline btn-sm" id="clear-qr">Limpiar</button>
                                </div>
                                @error('payment_qr_url')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-base-content/60">Sube primero y luego guarda para registrar la URL.</p>
                            </div>
                        </div>
                    </div>
                    @error('payment_qr')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">Nombre del negocio</label>
                    <input name="name" value="{{ old('name', $business['name'] ?? '') }}" class="input input-bordered w-full" required>
                    @error('name')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">NIT</label>
                    <input name="nit" value="{{ old('nit', $business['nit'] ?? '') }}" class="input input-bordered w-full">
                </div>
                <div>
                    <label class="field-label">Telefono</label>
                    <input name="phone" value="{{ old('phone', $business['phone'] ?? '') }}" class="input input-bordered w-full">
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">Direccion</label>
                    <input name="address" value="{{ old('address', $business['address'] ?? '') }}" class="input input-bordered w-full">
                </div>
                <div>
                    <label class="field-label">Moneda</label>
                    <input name="currency" value="{{ old('currency', $business['currency'] ?? 'USD') }}" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="field-label">Impuesto por defecto</label>
                    <select name="default_tax_id" class="select select-bordered w-full">
                        <option value="">Sin impuesto</option>
                        @foreach ($taxes as $tax)
                            <option value="{{ $tax->id }}" @selected(old('default_tax_id', $business['default_tax_id'] ?? null) == $tax->id)>{{ $tax->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" class="checkbox checkbox-primary" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $business['allow_negative_stock'] ?? false))>
                        Permitir stock negativo
                    </label>
                </div>
            </div>

            <div class="mt-6 flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const logoInput = document.getElementById('logo-input');
            const logoPreview = document.getElementById('logo-preview');
            const clearBtn = document.getElementById('clear-logo');
            const logoUrlInput = document.querySelector('input[name="logo_url"]');
            const uploadBtn = document.getElementById('upload-logo');
            const statusEl = document.getElementById('upload-status');
            const qrInput = document.getElementById('qr-input');
            const qrPreview = document.getElementById('qr-preview');
            const clearQrBtn = document.getElementById('clear-qr');
            const qrUrlInput = document.querySelector('input[name="payment_qr_url"]');
            const uploadQrBtn = document.getElementById('upload-qr');
            const qrStatusEl = document.getElementById('qr-upload-status');
            const companyNameInput = document.querySelector('input[name=\"name\"]');
            const companyNamePreview = document.getElementById('company-name-preview');
            const placeholderLogo = '{{ asset('images/product-placeholder.svg') }}';
            const placeholderQr = '{{ asset('images/payment-qr-sample.svg') }}';

            function bindPreview(input, preview, altText) {
                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.alt = altText;
                    };
                    reader.readAsDataURL(file);
                });
            }

            bindPreview(logoInput, logoPreview, 'Logo');
            bindPreview(qrInput, qrPreview, 'QR de pago');

            if (clearBtn && logoUrlInput && logoInput && logoPreview) {
                clearBtn.addEventListener('click', function () {
                    logoUrlInput.value = '';
                    logoInput.value = '';
                    logoPreview.src = placeholderLogo;
                    logoPreview.alt = 'Sin logo';
                });
            }

            if (clearQrBtn && qrUrlInput && qrInput && qrPreview) {
                clearQrBtn.addEventListener('click', function () {
                    qrUrlInput.value = '';
                    qrInput.value = '';
                    qrPreview.src = placeholderQr;
                    qrPreview.alt = 'Sin QR';
                });
            }

            if (companyNameInput && companyNamePreview) {
                companyNameInput.addEventListener('input', function () {
                    companyNamePreview.textContent = companyNameInput.value.trim() || '{{ config('app.name', 'Punto de venta') }}';
                });
            }

            async function bindUpload(button, fileInput, urlInput, preview, statusNode, fieldName, endpoint, successLabel, altText) {
                if (!button || !fileInput || !urlInput || !preview || !statusNode) {
                    return;
                }

                button.addEventListener('click', async function () {
                    const file = fileInput.files && fileInput.files[0];
                    if (!file) {
                        statusNode.textContent = 'Selecciona una imagen primero.';
                        return;
                    }
                    statusNode.textContent = 'Subiendo...';
                    const formData = new FormData();
                    formData.append(fieldName, file);
                    try {
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: formData,
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            statusNode.textContent = data.message || 'Error al subir.';
                            return;
                        }
                        urlInput.value = data.url;
                        preview.src = data.url;
                        preview.alt = altText;
                        statusNode.textContent = successLabel;
                    } catch (e) {
                        statusNode.textContent = 'Error de red al subir.';
                    }
                });
            }

            bindUpload(
                uploadBtn,
                logoInput,
                logoUrlInput,
                logoPreview,
                statusEl,
                'logo',
                '{{ route('settings.logo-upload') }}',
                'Logo subido.',
                'Logo'
            );

            bindUpload(
                uploadQrBtn,
                qrInput,
                qrUrlInput,
                qrPreview,
                qrStatusEl,
                'qr',
                '{{ route('settings.qr-upload') }}',
                'QR subido.',
                'QR de pago'
            );
        })();
    </script>
@endsection
