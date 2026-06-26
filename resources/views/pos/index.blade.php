@extends('layouts.admin')

@section('content')
    @php
        $initialPosState = $oldPosState ?? [
            'branch_id' => null,
            'customer_id' => null,
            'medical_order_id' => null,
            'global_discount' => 0,
            'items' => [],
            'payments' => [],
        ];
    @endphp

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="posApp()" x-init="init()" class="grid gap-4 xl:grid-cols-12">
        <div class="space-y-4 xl:col-span-7">
            <div class="page-header">
                <div class="page-header-row">
                    <div>
                        <h1 class="page-title">Punto de venta</h1>
                        <p class="page-subtitle">Venta rapida por caja</p>
                    </div>
                    <div class="page-actions w-full sm:w-auto flex-col sm:flex-row">
                        <div class="w-full sm:min-w-[16rem]">
                            <label class="field-label normal-case tracking-normal">Seleccionar sucursal</label>
                            <select
                                x-model="branchId"
                                @change="changeBranch"
                                class="select select-bordered h-10 w-full pr-10 text-sm"
                            >
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="relative w-full sm:min-w-[18rem]" @click.outside="showCustomerDropdown = false">
                            <label class="field-label normal-case tracking-normal">Seleccionar cliente</label>
                            <input
                                type="text"
                                x-model="customerSearch"
                                @click="showCustomerDropdown = true; filterCustomers()"
                                @focus="showCustomerDropdown = true; filterCustomers()"
                                @input="filterCustomers()"
                                @keydown.escape="showCustomerDropdown = false"
                                placeholder="Busca por nombre o identificacion"
                                class="input input-bordered h-10 w-full text-sm"
                                autocomplete="off"
                            >
                            <div
                                x-show="showCustomerDropdown"
                                x-cloak
                                class="absolute z-40 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow-lg max-h-60 overflow-y-auto"
                                style="display: none;"
                            >
                                <template x-for="customer in filteredCustomers" :key="customer.id">
                                    <button
                                        type="button"
                                        @click="selectCustomer(customer)"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-base-200"
                                    >
                                        <span class="font-medium" x-text="customer.name"></span>
                                        <span class="ml-2 text-xs text-base-content/60" x-text="customer.document ? `ID ${customer.document}` : 'Sin ID'"></span>
                                    </button>
                                </template>
                                <div x-show="customerSearch.trim().length >= minCustomerChars && filteredCustomers.length === 0" class="px-3 py-2 text-xs text-base-content/60">
                                    Sin coincidencias
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-1 flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                        <input
                            x-ref="searchInput"
                            x-model="search"
                            @keydown.ctrl.k.prevent="focusSearch"
                            @input.debounce.300ms="fetchProducts"
                            placeholder="Nombre, codigo de barras o codigo del producto (Ctrl+K)"
                            class="input input-bordered h-10 w-full min-w-0 flex-1"
                        >
                        <button @click="fetchProducts" type="button" class="btn btn-outline h-10 px-5 sm:w-auto">
                            Buscar
                        </button>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <button type="button" class="btn btn-outline btn-sm" @click="startCameraScanner">
                        <i class="fa-solid fa-camera mr-1" aria-hidden="true"></i>Escanear con camara
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" @click="openRemoteScannerModal">
                        <i class="fa-solid fa-mobile-screen-button mr-1" aria-hidden="true"></i>Usar celular como lector
                    </button>
                    <span class="text-xs text-base-content/60">Lector USB: escanea directamente sobre esta pantalla.</span>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 xl:hidden">
                    <button
                        type="button"
                        class="btn h-10 min-w-0 px-2 sm:px-3"
                        :class="mobileSection === 'products' ? 'btn-primary' : 'btn-outline'"
                        @click="mobileSection = 'products'"
                    >
                        <i class="fa-solid fa-box-open shrink-0" aria-hidden="true"></i>
                        <span class="min-w-0 truncate">Productos</span>
                    </button>
                    <button
                        type="button"
                        class="btn h-10 min-w-0 px-2 sm:px-3"
                        :class="mobileSection === 'cart' ? 'btn-primary' : 'btn-outline'"
                        @click="mobileSection = 'cart'"
                    >
                        <i class="fa-solid fa-cart-shopping shrink-0" aria-hidden="true"></i>
                        <span class="min-w-0 truncate">Carrito</span>
                        <span class="inline-flex shrink-0 min-w-[1.35rem] items-center justify-center rounded-full bg-base-content/15 px-1.5 py-0.5 text-[11px] font-semibold leading-none">
                            <span x-text="cartItemsCount"></span>
                        </span>
                    </button>
                </div>
            </div>

            <div class="panel" x-show="mobileSection === 'products' || !isMobileViewport" x-cloak>
                <div class="panel-body">
                    <div class="mb-3 flex items-center justify-between xl:hidden">
                        <h2 class="text-sm font-semibold">Productos</h2>
                        <button
                            type="button"
                            @click="showProducts = !showProducts"
                            class="btn btn-outline btn-xs"
                        >
                            <i class="fa-solid fa-layer-group mr-1" aria-hidden="true"></i>
                            <span x-text="showProducts ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                    <div x-show="showProducts" x-cloak class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <template x-for="product in products" :key="product.id">
                            <button type="button" @click="addToCart(product)" class="card h-full border border-base-200 hover:border-primary/60 transition">
                                <div class="card-body p-4 text-left">
                                    <div class="text-sm font-semibold" x-text="product.name"></div>
                                    <div class="text-xs text-base-content/60" x-text="product.sku"></div>
                                    <div class="mt-1 text-[11px] text-base-content/60">
                                        <span x-show="!product.uses_component_groups">Disponible: <span x-text="toAmount(product.available_stock).toFixed(3)"></span></span>
                                        <span x-show="product.uses_component_groups">Componentes configurables</span>
                                    </div>
                                    <div class="mt-2 text-sm font-semibold text-primary">$<span x-text="product.sale_price"></span></div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4 xl:col-span-5 xl:sticky xl:top-4 self-start" x-show="mobileSection === 'cart' || !isMobileViewport" x-cloak>
            <div class="panel">
                <div class="panel-body">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold">Carrito</h2>
                        <button
                            type="button"
                            class="btn btn-outline btn-xs xl:hidden"
                            @click="mobileSection = 'products'"
                        >
                            <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i>Seguir agregando
                        </button>
                    </div>
                    <template x-if="cart.length === 0">
                        <p class="mt-3 text-sm text-base-content/60">Sin productos.</p>
                    </template>
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <div class="surface-muted mt-3 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold" x-text="item.name"></div>
                                    <div class="text-xs text-base-content/60" x-text="item.sku"></div>
                                </div>
                                <button type="button" @click="removeItem(index)" class="btn btn-danger btn-xs">
                                    <i class="fa-solid fa-trash-can mr-1" aria-hidden="true"></i>Quitar
                                </button>
                            </div>
                            <div class="mt-2 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                                <div>
                                    <label class="text-base-content/60">Cantidad</label>
                                    <input type="number" :step="item.product_type === 'serialized' ? 1 : 0.001" :min="item.product_type === 'serialized' ? 1 : 0.001" x-model.number="item.quantity" @input="sanitizeItemQuantity(item)" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                                <div>
                                    <label class="text-base-content/60">Precio</label>
                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                                <div>
                                    <label class="text-base-content/60">Desc %</label>
                                    <input type="number" step="0.01" min="0" x-model.number="item.discount_percent" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                            </div>
                            <div class="mt-2 text-[11px] text-base-content/60">
                                <span x-show="!item.uses_component_groups">Disponible: <span x-text="toAmount(item.available_stock).toFixed(3)"></span></span>
                                <span x-show="item.uses_component_groups">El inventario se valida según los componentes elegidos.</span>
                            </div>
                            <div x-show="item.uses_component_groups" class="mt-3 space-y-3">
                                <template x-for="group in item.modifier_groups || []" :key="group.id">
                                    <div class="rounded-xl border border-base-200 p-3">
                                        <label class="field-label" x-text="group.name"></label>
                                        <select
                                            x-show="group.selection_type === 'single'"
                                            class="select select-bordered select-sm w-full"
                                            :value="selectedOptionForGroup(item, group.id)"
                                            @change="selectSingleGroupOption(item, group, $event.target.value)"
                                        >
                                            <option value="">Selecciona una opción</option>
                                            <template x-for="option in group.options" :key="option.id">
                                                <option :value="String(option.id)" x-text="option.price_delta > 0 ? `${option.label} (+$${Number(option.price_delta).toFixed(2)})` : option.label"></option>
                                            </template>
                                        </select>
                                        <div x-show="group.selection_type !== 'single'" class="space-y-2">
                                            <template x-for="option in group.options" :key="option.id">
                                                <label class="flex items-center gap-2 text-sm">
                                                    <input
                                                        type="checkbox"
                                                        class="checkbox checkbox-sm"
                                                        :checked="isModifierSelected(item, group.id, option.id)"
                                                        @change="toggleModifierOption(item, group, option, $event.target.checked)"
                                                    >
                                                    <span x-text="option.label"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div class="mt-4 border-t border-base-200 pt-3 text-sm space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>$<span x-text="subtotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Descuento lineas</span>
                            <span>-$<span x-text="lineDiscountTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Impuestos</span>
                            <span>$<span x-text="taxTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Descuento global</span>
                            <input type="number" step="0.01" min="0" x-model.number="globalDiscount" class="input input-bordered input-sm sm:input-xs w-28 sm:w-24">
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>Total</span>
                            <span>$<span x-text="total.toFixed(2)"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <form x-ref="checkoutForm" action="{{ route('pos.checkout') }}" method="POST" class="panel" @submit.prevent="submitSale">
                @csrf
                <input type="hidden" name="branch_id" :value="branchId">
                <input type="hidden" name="customer_id" :value="customerId">
                @if ($supportsMedicalOrders)
                    <input type="hidden" name="medical_order_id" :value="medicalOrderId">
                @endif
                <input type="hidden" name="global_discount" :value="globalDiscount">
                <input type="hidden" name="items" :value="itemsPayload">
                <input type="hidden" name="payments" :value="paymentsPayload">

                <div class="panel-body">
                    @if ($supportsMedicalOrders)
                    <div class="mb-4">
                        <label class="field-label">Orden medica</label>
                        <select x-model="medicalOrderId" class="select select-bordered w-full">
                            <option value="">Sin orden medica</option>
                            <template x-for="order in availableMedicalOrders" :key="order.id">
                                <option :value="String(order.id)" x-text="`#${order.id} · ${order.patient_name} · ${order.ordered_at}`"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-base-content/60">Solo aparecen ordenes activas del paciente seleccionado.</p>
                    </div>
                    @endif

                    <h2 class="text-sm font-semibold">Pagos</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                            <span>Efectivo</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentCash" class="input input-bordered input-sm sm:input-xs w-full sm:w-28">
                        </div>
                        <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                            <span>Tarjeta</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentCard" class="input input-bordered input-sm sm:input-xs w-full sm:w-28">
                        </div>
                        <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                            <span>Transferencia</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentTransfer" class="input input-bordered input-sm sm:input-xs w-full sm:w-28">
                        </div>
                        <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                            <span>Credito</span>
                            <input type="number" step="0.01" min="0" x-model.number="paymentCredit" class="input input-bordered input-sm sm:input-xs w-full sm:w-28">
                        </div>
                    </div>
                    <div class="mt-3 text-sm">
                        <div class="flex justify-between">
                            <span>Pagado</span>
                            <span>$<span x-text="paidTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Credito aplicado</span>
                            <span>$<span x-text="creditApplied.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total cubierto</span>
                            <span>$<span x-text="coveredTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Saldo pendiente</span>
                            <span>$<span x-text="pendingTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cambio</span>
                            <span>$<span x-text="changeTotal.toFixed(2)"></span></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4 h-11 w-full" :disabled="requiresCashSession || isSubmitting">
                        <span x-show="!isSubmitting">
                            <i class="fa-solid fa-cash-register mr-2" aria-hidden="true"></i>Cobrar
                        </span>
                        <span x-show="isSubmitting" x-cloak>
                            <i class="fa-solid fa-spinner fa-spin mr-2" aria-hidden="true"></i>Procesando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        <button
            type="button"
            class="btn btn-primary fixed bottom-4 right-4 z-40 h-12 px-4 xl:hidden"
            x-show="isMobileViewport && mobileSection === 'products'"
            x-cloak
            @click="mobileSection = 'cart'"
        >
            <i class="fa-solid fa-cart-shopping mr-2" aria-hidden="true"></i>
            Ver carrito
            <span class="ml-2 inline-flex min-w-[1.35rem] items-center justify-center rounded-full bg-base-content/15 px-1.5 py-0.5 text-[11px] font-semibold leading-none">
                <span x-text="cartItemsCount"></span>
            </span>
        </button>

    <div
        x-show="showCameraModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        style="display: none;"
    >
        <div class="w-full max-w-lg rounded-xl bg-base-100 shadow-xl">
            <div class="border-b border-base-200 px-5 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Escaner con camara</h2>
                    <p class="text-sm text-base-content/60 mt-1">Escanea desde este dispositivo.</p>
                </div>
                <button type="button" class="btn btn-outline btn-xs" @click="closeCameraScanner">Cerrar</button>
            </div>
            <div class="p-5 space-y-3">
                <video
                    x-ref="cameraPreview"
                    autoplay
                    muted
                    playsinline
                    class="w-full rounded-xl border border-base-300 bg-base-200 min-h-[200px]"
                ></video>
                <p class="text-xs text-base-content/60" x-text="cameraStatus"></p>
                <div class="flex justify-end">
                    <button type="button" class="btn btn-outline" @click="closeCameraScanner">Detener</button>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="showRemoteScannerModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        style="display: none;"
    >
        <div class="w-full max-w-xl rounded-xl bg-base-100 shadow-xl">
            <div class="border-b border-base-200 px-5 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Celular como lector</h2>
                    <p class="text-sm text-base-content/60 mt-1">Abre este enlace en el celular para escanear y enviar al POS.</p>
                </div>
                <button type="button" class="btn btn-outline btn-xs" @click="showRemoteScannerModal = false">Cerrar</button>
            </div>
            <div class="p-5 space-y-3">
                <div>
                    <label class="field-label">URL local actual</label>
                    <input type="text" class="input input-bordered w-full text-xs" :value="remoteScannerUrl || 'Generando enlace...'" readonly>
                </div>
                <div class="grid gap-2 sm:grid-cols-3">
                    <div>
                        <label class="field-label">Protocolo</label>
                        <select class="select select-bordered w-full" x-model="scannerProtocol">
                            <option value="http://">http://</option>
                            <option value="https://">https://</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">IP o host para USB</label>
                        <input
                            type="text"
                            class="input input-bordered w-full text-sm"
                            x-model.trim="scannerHostInput"
                            placeholder="Ejemplo: 192.168.42.129"
                        >
                    </div>
                    <div>
                        <label class="field-label">Puerto</label>
                        <input type="text" class="input input-bordered w-full text-sm" x-model.trim="scannerPortInput" placeholder="8000">
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <button type="button" class="btn btn-outline btn-sm w-full sm:w-auto" @click="useCurrentOriginForScanner">
                            Usar host actual
                        </button>
                    </div>
                </div>
                <div>
                    <label class="field-label">URL para celular por USB</label>
                    <input type="text" class="input input-bordered w-full text-xs" :value="remoteScannerUsbUrl || 'Completa IP/host'" readonly>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline btn-sm" @click="copyRemoteScannerUrl" :disabled="!remoteScannerUrl">
                        Copiar URL local
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" @click="copyRemoteScannerUsbUrl" :disabled="!remoteScannerUsbUrl">
                        Copiar URL USB
                    </button>
                    <a
                        class="btn btn-primary btn-sm"
                        :href="remoteScannerUsbUrl || remoteScannerUrl || '#'"
                        target="_blank"
                        rel="noopener"
                        :class="{ 'pointer-events-none opacity-50': !(remoteScannerUsbUrl || remoteScannerUrl) }"
                    >
                        Abrir escaner remoto
                    </a>
                </div>
                <p class="text-xs text-base-content/60">
                    Para USB: activa Anclaje USB en el celular, ejecuta `php artisan serve --host=0.0.0.0 --port=8000` y usa la IP del adaptador USB de tu PC.
                </p>
                <p class="text-xs text-base-content/60" x-show="lastScannerMessage" x-text="lastScannerMessage"></p>
            </div>
        </div>
    </div>

    <div
        x-show="showCashModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        style="display: none;"
    >
        <div class="w-full max-w-lg rounded-xl bg-base-100 shadow-xl">
            <div class="border-b border-base-200 px-5 py-4">
                <h2 class="text-lg font-semibold">Abre una caja para vender</h2>
                <p class="text-sm text-base-content/60 mt-1">No hay una caja abierta en esta sucursal para tu usuario.</p>
            </div>
            <form method="POST" action="{{ route('cash-register.open') }}" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="redirect_to" x-bind:value="`{{ route('pos.index') }}?branch_id=${openBranchId}`">
                @if ($errors->has('opening_amount') || $errors->has('branch_id'))
                    <div class="alert alert-error">
                        {{ $errors->first('opening_amount') ?: $errors->first('branch_id') }}
                    </div>
                @endif
                <div>
                    <label class="field-label">Sucursal</label>
                    <select name="branch_id" class="select select-bordered w-full" x-model="openBranchId" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Monto inicial</label>
                    <input name="opening_amount" type="number" min="0" step="0.01" class="input input-bordered w-full" value="0" required>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="submit" class="btn btn-primary flex-1">
                        <i class="fa-solid fa-lock-open mr-2" aria-hidden="true"></i>Abrir caja
                    </button>
                    <a href="{{ route('cash-register.index') }}" class="btn btn-outline">
                        <i class="fa-solid fa-vault mr-2" aria-hidden="true"></i>Ir a Caja
                    </a>
                </div>
            </form>
        </div>
    </div>
    </div>

    @vite('resources/js/pos-barcode-scanner.js')

    <script>
        const oldPosState = @js($initialPosState);

        function posApp() {
            return {
                branchId: oldPosState.branch_id || '{{ $branchId }}',
                requiresCashSession: @js($requiresCashSession),
                showCashModal: false,
                openBranchId: oldPosState.branch_id || '{{ $branchId }}',
                customerId: oldPosState.customer_id || '',
                medicalOrderId: oldPosState.medical_order_id || '',
                customerSearch: '',
                customers: @js($customers->map(fn ($customer) => ['id' => $customer->id, 'name' => $customer->name, 'document' => $customer->document])->values()->all()),
                medicalOrders: @js($medicalOrders->map(fn ($order) => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'patient_name' => $order->customer?->name,
                    'ordered_at' => optional($order->ordered_at)->format('d/m/Y H:i'),
                ])->values()->all()),
                filteredCustomers: [],
                showCustomerDropdown: false,
                showProducts: true,
                mobileSection: 'products',
                isMobileViewport: window.innerWidth < 1280,
                minCustomerChars: 1,
                search: '',
                products: [],
                cart: Array.isArray(oldPosState.items) ? oldPosState.items : [],
                globalDiscount: Number(oldPosState.global_discount || 0),
                paymentCash: Number((oldPosState.payments || []).find(p => p.method === 'cash')?.amount || 0),
                paymentCard: Number((oldPosState.payments || []).find(p => p.method === 'card')?.amount || 0),
                paymentTransfer: Number((oldPosState.payments || []).find(p => p.method === 'transfer')?.amount || 0),
                paymentCredit: Number((oldPosState.payments || []).find(p => p.method === 'credit')?.amount || 0),
                isSubmitting: false,
                scannerKeyboardBuffer: '',
                scannerKeyboardLastAt: 0,
                scannerPollingTimer: null,
                scannerPollingInProgress: false,
                scannerSessionToken: '',
                scannerSessionExpiresAt: '',
                scannerRequestSequence: 0,
                remoteScannerUrl: '',
                scannerProtocol: window.location.protocol === 'https:' ? 'https://' : 'http://',
                scannerHostInput: window.location.hostname || '',
                scannerPortInput: window.location.port || '8000',
                showRemoteScannerModal: false,
                showCameraModal: false,
                scannerStream: null,
                scannerDetector: null,
                scannerLoopRunning: false,
                scannerLastCode: '',
                scannerLastDetectedAt: 0,
                cameraStatus: 'Camara detenida.',
                lastScannerMessage: '',
                toAmount(value) {
                    const number = Number(value);
                    return Number.isFinite(number) ? number : 0;
                },
                get subtotal() {
                    return this.cart.reduce((sum, item) => {
                        const unitPrice = this.toAmount(item.unit_price);
                        const quantity = this.toAmount(item.quantity);
                        return sum + (unitPrice * quantity);
                    }, 0);
                },
                get cartItemsCount() {
                    return this.cart.length;
                },
                get availableMedicalOrders() {
                    if (!this.customerId) {
                        return [];
                    }

                    return this.medicalOrders.filter(order => String(order.customer_id) === String(this.customerId));
                },
                get lineDiscountTotal() {
                    return this.cart.reduce((sum, item) => {
                        const unitPrice = this.toAmount(item.unit_price);
                        const quantity = this.toAmount(item.quantity);
                        const lineSubtotal = unitPrice * quantity;
                        const discountPercent = this.toAmount(item.discount_percent);
                        const lineDiscount = lineSubtotal * (discountPercent / 100);
                        return sum + Math.max(0, Math.min(lineDiscount, lineSubtotal));
                    }, 0);
                },
                get taxTotal() {
                    return this.cart.reduce((sum, item) => {
                        const unitPrice = this.toAmount(item.unit_price);
                        const quantity = this.toAmount(item.quantity);
                        const lineSubtotal = unitPrice * quantity;
                        const discountPercent = this.toAmount(item.discount_percent);
                        const lineDiscount = Math.max(0, Math.min(lineSubtotal * (discountPercent / 100), lineSubtotal));
                        const taxableBase = lineSubtotal - lineDiscount;
                        const taxRate = this.toAmount(item.tax_rate);
                        return sum + (taxableBase * (taxRate / 100));
                    }, 0);
                },
                get total() {
                    return Math.max(
                        0,
                        this.subtotal
                        - this.lineDiscountTotal
                        - this.toAmount(this.globalDiscount)
                        + this.taxTotal
                    );
                },
                get paidTotal() {
                    return (
                        this.toAmount(this.paymentCash)
                        + this.toAmount(this.paymentCard)
                        + this.toAmount(this.paymentTransfer)
                    );
                },
                get coveredTotal() {
                    return this.paidTotal + this.creditApplied;
                },
                get enteredTotal() {
                    return this.paidTotal + this.toAmount(this.paymentCredit);
                },
                get creditApplied() {
                    const credit = this.toAmount(this.paymentCredit);
                    const remainingAfterPaid = Math.max(0, this.total - this.paidTotal);
                    return Math.min(credit, remainingAfterPaid);
                },
                get pendingTotal() {
                    return Math.max(0, this.total - this.coveredTotal);
                },
                get changeTotal() {
                    return Math.max(0, this.paidTotal - this.total);
                },
                get remoteScannerUsbUrl() {
                    const token = String(this.scannerSessionToken || '').trim();
                    if (!token) {
                        return '';
                    }

                    let host = String(this.scannerHostInput || '').trim();
                    host = host
                        .replace(/^https?:\/\//i, '')
                        .replace(/\/.*$/, '')
                        .replace(/:\d+$/, '')
                        .trim();
                    if (!host) {
                        return '';
                    }

                    let port = String(this.scannerPortInput || '').trim();
                    port = port.replace(/[^\d]/g, '');
                    const protocol = this.scannerProtocol === 'https://' ? 'https://' : 'http://';
                    const portSegment = port ? `:${port}` : '';

                    return `${protocol}${host}${portSegment}/pos/scanner/remote/${token}`;
                },
                get itemsPayload() {
                    return JSON.stringify(this.cart.map(item => ({
                        product_id: item.product_id,
                        quantity: this.toAmount(item.quantity),
                        unit_price: this.toAmount(item.unit_price),
                        discount_type: this.toAmount(item.discount_percent) > 0 ? 'percent' : null,
                        discount_value: this.toAmount(item.discount_percent) > 0 ? this.toAmount(item.discount_percent) : 0,
                        modifier_selections: item.modifier_selections || [],
                    })));
                },
                get paymentsPayload() {
                    const payments = [];
                    if (this.toAmount(this.paymentCash) > 0) payments.push({ method: 'cash', amount: this.toAmount(this.paymentCash) });
                    if (this.toAmount(this.paymentCard) > 0) payments.push({ method: 'card', amount: this.toAmount(this.paymentCard) });
                    if (this.toAmount(this.paymentTransfer) > 0) payments.push({ method: 'transfer', amount: this.toAmount(this.paymentTransfer) });
                    if (this.creditApplied > 0) payments.push({ method: 'credit', amount: this.creditApplied });
                    return JSON.stringify(payments);
                },
                init() {
                    this.fetchProducts();
                    this.openBranchId = this.branchId;
                    this.showCashModal = this.requiresCashSession;
                    this.initKeyboardScanner();
                    this.restoreRemoteScannerSession();
                    const mediaQuery = window.matchMedia('(min-width: 1280px)');
                    const syncViewportState = (event) => {
                        this.isMobileViewport = !event.matches;
                        if (!this.isMobileViewport) {
                            this.mobileSection = 'products';
                            this.showProducts = true;
                        }
                    };
                    syncViewportState(mediaQuery);
                    mediaQuery.addEventListener('change', syncViewportState);
                    window.addEventListener('message', (event) => {
                        if (event.origin !== window.location.origin) {
                            return;
                        }
                        if (event.data?.type === 'pos-sale-completed') {
                            this.resetCheckoutState();
                        }
                    });
                    if (this.customerId) {
                        const selected = this.customers.find(customer => String(customer.id) === String(this.customerId));
                        if (selected) {
                            this.customerSearch = selected.name;
                        }
                    }
                    window.addEventListener('beforeunload', () => {
                        this.stopRemoteScannerPolling();
                        this.closeCameraScanner();
                    });
                    window.addEventListener('focus', () => this.resumeRemoteScannerPolling());
                    window.addEventListener('online', () => this.resumeRemoteScannerPolling());
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.resumeRemoteScannerPolling();
                        }
                    });
                    this.filterCustomers();
                },
                focusSearch() {
                    this.$refs.searchInput.focus();
                },
                filterCustomers() {
                    const source = Array.isArray(this.customers)
                        ? this.customers
                        : Object.values(this.customers || {});
                    const term = this.customerSearch.trim().toLowerCase();
                    if (term.length === 0) {
                        this.filteredCustomers = source.slice(0, 10);
                        return;
                    }
                    this.filteredCustomers = source
                        .filter(customer => {
                            const name = String(customer.name || '').toLowerCase();
                            const document = String(customer.document || '').toLowerCase();
                            return name.includes(term) || document.includes(term);
                        })
                        .slice(0, 10);
                },
                selectCustomer(customer) {
                    this.customerId = customer.id;
                    this.customerSearch = customer.name;
                    this.showCustomerDropdown = false;
                    if (!this.availableMedicalOrders.some(order => String(order.id) === String(this.medicalOrderId))) {
                        this.medicalOrderId = '';
                    }
                },
                clearCustomer() {
                    this.customerId = null;
                    this.medicalOrderId = '';
                    this.customerSearch = '';
                    this.filterCustomers();
                    this.showCustomerDropdown = false;
                },
                initKeyboardScanner() {
                    window.addEventListener('keydown', (event) => {
                        const now = Date.now();
                        if (event.key === 'Enter') {
                            const buffer = this.scannerKeyboardBuffer.trim();
                            const elapsed = now - this.scannerKeyboardLastAt;
                            if (buffer.length >= 4 && elapsed < 120) {
                                event.preventDefault();
                                this.handleScannedBarcode(buffer, 'lector USB');
                            }
                            this.scannerKeyboardBuffer = '';
                            return;
                        }

                        if (event.key.length !== 1) {
                            return;
                        }

                        if ((now - this.scannerKeyboardLastAt) > 80) {
                            this.scannerKeyboardBuffer = '';
                        }
                        this.scannerKeyboardBuffer += event.key;
                        this.scannerKeyboardLastAt = now;
                    });
                },
                async handleScannedBarcode(rawCode, sourceLabel = 'escaner') {
                    const barcode = String(rawCode || '').trim();
                    if (barcode === '') {
                        return;
                    }

                    const requestSequence = ++this.scannerRequestSequence;
                    this.search = barcode;
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.value = barcode;
                        }
                    });
                    this.showProducts = true;
                    this.mobileSection = 'products';
                    this.lastScannerMessage = `${sourceLabel}: buscando codigo ${barcode}...`;

                    try {
                        await this.fetchProducts();
                        if (requestSequence !== this.scannerRequestSequence) {
                            return;
                        }

                        const response = await fetch(`{{ route('pos.products.resolve') }}?${new URLSearchParams({ barcode, branch_id: this.branchId || '' }).toString()}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (requestSequence !== this.scannerRequestSequence) {
                            return;
                        }
                        if (!response.ok) {
                            const payload = await response.json().catch(() => ({}));
                            if (requestSequence !== this.scannerRequestSequence) {
                                return;
                            }
                            this.lastScannerMessage = payload.message
                                ? `${sourceLabel}: ${payload.message}`
                                : `${sourceLabel}: codigo ${barcode} no encontrado.`;
                            return;
                        }
                        const product = await response.json();
                        if (requestSequence !== this.scannerRequestSequence) {
                            return;
                        }
                        this.addToCart(product);
                        this.lastScannerMessage = `${sourceLabel}: codigo ${barcode} encontrado. Agregado ${product.name}.`;
                        if (navigator.vibrate) {
                            navigator.vibrate(50);
                        }
                    } catch (error) {
                        if (requestSequence === this.scannerRequestSequence) {
                            this.lastScannerMessage = `${sourceLabel}: error leyendo codigo.`;
                        }
                    }
                },
                async createRemoteScannerSession() {
                    if (this.scannerSessionToken && this.remoteScannerUrl) {
                        return;
                    }
                    const response = await fetch(`{{ route('pos.scanner.session') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });
                    if (!response.ok) {
                        throw new Error('No fue posible crear sesion de escaneo remoto.');
                    }
                    const payload = await response.json();
                    this.scannerSessionToken = payload.token || '';
                    this.remoteScannerUrl = payload.remote_url || '';
                    this.scannerSessionExpiresAt = payload.expires_at || '';
                    this.persistRemoteScannerSession();
                },
                scannerStorageKey() {
                    return `pos:remote-scanner:{{ auth()->id() }}`;
                },
                persistRemoteScannerSession() {
                    if (!this.scannerSessionToken) {
                        return;
                    }
                    localStorage.setItem(this.scannerStorageKey(), JSON.stringify({
                        token: this.scannerSessionToken,
                        remote_url: this.remoteScannerUrl,
                        expires_at: this.scannerSessionExpiresAt,
                    }));
                },
                restoreRemoteScannerSession() {
                    try {
                        const stored = JSON.parse(localStorage.getItem(this.scannerStorageKey()) || 'null');
                        const expiresAt = Date.parse(stored?.expires_at || '');
                        if (!stored?.token || !Number.isFinite(expiresAt) || expiresAt <= Date.now()) {
                            localStorage.removeItem(this.scannerStorageKey());
                            return;
                        }
                        this.scannerSessionToken = stored.token;
                        this.remoteScannerUrl = stored.remote_url || '';
                        this.scannerSessionExpiresAt = stored.expires_at;
                        this.startRemoteScannerPolling();
                    } catch (error) {
                        localStorage.removeItem(this.scannerStorageKey());
                    }
                },
                startRemoteScannerPolling() {
                    this.stopRemoteScannerPolling();
                    if (!this.scannerSessionToken) {
                        return;
                    }
                    const poll = async () => {
                        if (!this.scannerSessionToken || this.scannerPollingInProgress) {
                            return;
                        }
                        this.scannerPollingInProgress = true;
                        try {
                            const response = await fetch(`{{ url('pos/scanner/session') }}/${this.scannerSessionToken}/poll`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (response.status === 404) {
                                this.clearRemoteScannerSession();
                                this.lastScannerMessage = 'La sesion del lector expiro. Genera un enlace nuevo.';
                                return;
                            }
                            if (!response.ok) {
                                return;
                            }
                            const payload = await response.json();
                            const events = Array.isArray(payload.events) ? payload.events : [];
                            for (const event of events) {
                                await this.handleScannedBarcode(event.barcode, 'celular remoto');
                            }
                        } catch (error) {
                            // Se reintenta al volver a programar el sondeo.
                        } finally {
                            this.scannerPollingInProgress = false;
                            if (this.scannerSessionToken) {
                                this.scannerPollingTimer = window.setTimeout(poll, 1000);
                            }
                        }
                    };
                    poll();
                },
                resumeRemoteScannerPolling() {
                    if (this.scannerSessionToken) {
                        this.startRemoteScannerPolling();
                    }
                },
                stopRemoteScannerPolling() {
                    if (this.scannerPollingTimer) {
                        clearTimeout(this.scannerPollingTimer);
                        this.scannerPollingTimer = null;
                    }
                },
                clearRemoteScannerSession() {
                    this.stopRemoteScannerPolling();
                    this.scannerSessionToken = '';
                    this.scannerSessionExpiresAt = '';
                    this.remoteScannerUrl = '';
                    localStorage.removeItem(this.scannerStorageKey());
                },
                async openRemoteScannerModal() {
                    this.showRemoteScannerModal = true;
                    try {
                        if (!this.scannerSessionToken) {
                            await this.createRemoteScannerSession();
                        }
                        this.startRemoteScannerPolling();
                    } catch (error) {
                        this.lastScannerMessage = 'No se pudo iniciar el escaner remoto.';
                    }
                },
                async copyRemoteScannerUrl() {
                    if (!this.remoteScannerUrl) {
                        return;
                    }
                    try {
                        await navigator.clipboard.writeText(this.remoteScannerUrl);
                        this.lastScannerMessage = 'Enlace copiado al portapapeles.';
                    } catch (error) {
                        this.lastScannerMessage = 'No se pudo copiar el enlace.';
                    }
                },
                async copyRemoteScannerUsbUrl() {
                    if (!this.remoteScannerUsbUrl) {
                        return;
                    }
                    try {
                        await navigator.clipboard.writeText(this.remoteScannerUsbUrl);
                        this.lastScannerMessage = 'URL USB copiada al portapapeles.';
                    } catch (error) {
                        this.lastScannerMessage = 'No se pudo copiar la URL USB.';
                    }
                },
                useCurrentOriginForScanner() {
                    this.scannerProtocol = window.location.protocol === 'https:' ? 'https://' : 'http://';
                    this.scannerHostInput = window.location.hostname || '';
                    this.scannerPortInput = window.location.port || '';
                },
                async startCameraScanner() {
                    this.showCameraModal = true;
                    this.cameraStatus = 'Iniciando camara...';
                    try {
                        if (!window.PosBarcodeCamera) {
                            throw new Error('El lector de camara no se cargo. Recarga la pagina e intenta nuevamente.');
                        }

                        await window.PosBarcodeCamera.start(
                            this.$refs.cameraPreview,
                            async barcode => {
                                await this.handleScannedBarcode(barcode, 'camara');
                                this.closeCameraScanner();
                            },
                            message => {
                                this.cameraStatus = message;
                            }
                        );
                    } catch (error) {
                        this.cameraStatus = error?.message || 'No se pudo acceder a la camara. Revisa los permisos.';
                    }
                },
                closeCameraScanner() {
                    this.showCameraModal = false;
                    window.PosBarcodeCamera?.stop();
                    this.cameraStatus = 'Camara detenida.';
                },
                async fetchProducts() {
                    const params = new URLSearchParams({ q: this.search });
                    params.set('branch_id', this.branchId || '');
                    const response = await fetch(`{{ route('pos.products') }}?${params.toString()}`);
                    this.products = await response.json();
                },
                changeBranch() {
                    window.location = `{{ route('pos.index') }}?branch_id=${this.branchId}`;
                },
                addToCart(product) {
                    const existing = this.cart.find(item => item.product_id === product.id);
                    if (existing) {
                        const nextQuantity = this.toAmount(existing.quantity) + 1;
                        if (this.toAmount(existing.available_stock) > 0 && nextQuantity > this.toAmount(existing.available_stock)) {
                            alert('No puedes vender una cantidad superior al stock disponible.');
                            return;
                        }
                        existing.quantity = nextQuantity;
                        return;
                    }
                    const defaultSelections = this.defaultModifierSelections(product.modifier_groups || []);
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        product_type: product.product_type || 'simple',
                        quantity: 1,
                        base_unit_price: parseFloat(product.sale_price),
                        unit_price: parseFloat(product.sale_price) + this.modifierPriceDelta(product.modifier_groups || [], defaultSelections),
                        tax_rate: this.toAmount(product.tax_rate),
                        discount_percent: 0,
                        available_stock: this.toAmount(product.available_stock),
                        uses_component_groups: Boolean(product.uses_component_groups),
                        modifier_groups: product.modifier_groups || [],
                        modifier_selections: defaultSelections,
                    });
                },
                modifierPriceDelta(groups, selections) {
                    return (selections || []).reduce((sum, selection) => {
                        const group = (groups || []).find(entry => String(entry.id) === String(selection.group_id));
                        const option = (group?.options || []).find(entry => String(entry.id) === String(selection.option_id));
                        return sum + this.toAmount(option?.price_delta);
                    }, 0);
                },
                refreshModifierPrice(item) {
                    item.unit_price = this.toAmount(item.base_unit_price)
                        + this.modifierPriceDelta(item.modifier_groups || [], item.modifier_selections || []);
                },
                defaultModifierSelections(groups) {
                    return groups.flatMap(group => (group.options || [])
                        .filter(option => option.is_default)
                        .map(option => ({
                            group_id: group.id,
                            option_id: option.id,
                            action: group.selection_type === 'remove' ? 'remove' : 'include',
                        })));
                },
                selectedOptionForGroup(item, groupId) {
                    const selection = (item.modifier_selections || []).find(entry => String(entry.group_id) === String(groupId));
                    return selection ? String(selection.option_id) : '';
                },
                isModifierSelected(item, groupId, optionId) {
                    return (item.modifier_selections || []).some(entry =>
                        String(entry.group_id) === String(groupId)
                        && String(entry.option_id) === String(optionId)
                    );
                },
                selectSingleGroupOption(item, group, optionId) {
                    item.modifier_selections = (item.modifier_selections || [])
                        .filter(entry => String(entry.group_id) !== String(group.id));
                    if (optionId !== '') {
                        item.modifier_selections.push({
                            group_id: group.id,
                            option_id: Number(optionId),
                            action: 'include',
                        });
                    }
                    this.refreshModifierPrice(item);
                },
                toggleModifierOption(item, group, option, checked) {
                    item.modifier_selections = (item.modifier_selections || [])
                        .filter(entry => !(
                            String(entry.group_id) === String(group.id)
                            && String(entry.option_id) === String(option.id)
                        ));
                    if (checked) {
                        item.modifier_selections.push({
                            group_id: group.id,
                            option_id: option.id,
                            action: group.selection_type === 'remove' ? 'remove' : 'include',
                        });
                    }
                    this.refreshModifierPrice(item);
                },
                sanitizeItemQuantity(item) {
                    const minimum = item.product_type === 'serialized' ? 1 : 0.001;
                    const rawQuantity = Math.max(minimum, this.toAmount(item.quantity));
                    const quantity = item.product_type === 'serialized' ? Math.floor(rawQuantity) : rawQuantity;
                    const available = this.toAmount(item.available_stock);
                    if (available > 0 && quantity > available) {
                        item.quantity = available;
                        return;
                    }
                    item.quantity = quantity;
                },
                removeItem(index) {
                    this.cart.splice(index, 1);
                },
                resetCheckoutState() {
                    this.customerId = null;
                    this.medicalOrderId = '';
                    this.customerSearch = '';
                    this.cart = [];
                    this.globalDiscount = 0;
                    this.paymentCash = 0;
                    this.paymentCard = 0;
                    this.paymentTransfer = 0;
                    this.paymentCredit = 0;
                    this.filterCustomers();
                },
                submitSale() {
                    if (this.isSubmitting) {
                        return;
                    }
                    if (this.requiresCashSession) {
                        this.showCashModal = true;
                        return;
                    }
                    if (this.cart.length === 0) {
                        alert('Agrega productos al carrito.');
                        return;
                    }
                    if (!this.customerId) {
                        alert('Debes seleccionar un cliente.');
                        return;
                    }
                    if (this.cart.some(item => this.toAmount(item.available_stock) > 0 && this.toAmount(item.quantity) > this.toAmount(item.available_stock))) {
                        alert('Hay productos con cantidad superior al stock disponible.');
                        return;
                    }
                    if (JSON.parse(this.paymentsPayload).length === 0) {
                        alert('Debes registrar al menos un pago.');
                        return;
                    }
                    if (this.enteredTotal > this.total + 0.0001) {
                        alert('La suma de pagos y credito no puede superar el total.');
                        return;
                    }
                    if (this.coveredTotal < this.total) {
                        alert('Pago mas credito insuficiente.');
                        return;
                    }
                    const popupName = 'pos_invoice_popup';
                    const popupFeatures = 'width=420,height=760,scrollbars=yes,resizable=yes';
                    const popupWindow = window.open('', popupName, popupFeatures);
                    if (popupWindow) {
                        popupWindow.focus();
                        this.$refs.checkoutForm.setAttribute('target', popupName);
                    } else {
                        this.$refs.checkoutForm.removeAttribute('target');
                    }
                    this.isSubmitting = true;
                    this.$refs.checkoutForm.submit();
                    setTimeout(() => {
                        this.isSubmitting = false;
                        this.$refs.checkoutForm.removeAttribute('target');
                    }, 2000);
                },
            };
        }
    </script>
@endsection
