@extends('layouts.admin')

@section('content')
    @php
        $initialPosState = $oldPosState ?? [
            'branch_id' => null,
            'customer_id' => null,
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
                            placeholder="Buscar producto (Ctrl+K)"
                            class="input input-bordered h-10 w-full min-w-0 flex-1"
                        >
                        <button @click="fetchProducts" type="button" class="btn btn-outline h-10 px-5 sm:w-auto">
                            Buscar
                        </button>
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
                                    <input type="number" step="0.001" min="0.001" x-model.number="item.quantity" class="input input-bordered input-sm sm:input-xs w-full">
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
                <input type="hidden" name="global_discount" :value="globalDiscount">
                <input type="hidden" name="items" :value="itemsPayload">
                <input type="hidden" name="payments" :value="paymentsPayload">

                <div class="panel-body">
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

    <script>
        const oldPosState = @js($initialPosState);

        function posApp() {
            return {
                branchId: oldPosState.branch_id || '{{ $branchId }}',
                requiresCashSession: @js($requiresCashSession),
                showCashModal: false,
                openBranchId: oldPosState.branch_id || '{{ $branchId }}',
                customerId: oldPosState.customer_id || '',
                customerSearch: '',
                customers: @js($customers->map(fn ($customer) => ['id' => $customer->id, 'name' => $customer->name, 'document' => $customer->document])->values()->all()),
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
                get itemsPayload() {
                    return JSON.stringify(this.cart.map(item => ({
                        product_id: item.product_id,
                        quantity: this.toAmount(item.quantity),
                        unit_price: this.toAmount(item.unit_price),
                        discount_type: this.toAmount(item.discount_percent) > 0 ? 'percent' : null,
                        discount_value: this.toAmount(item.discount_percent) > 0 ? this.toAmount(item.discount_percent) : 0,
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
                },
                clearCustomer() {
                    this.customerId = null;
                    this.customerSearch = '';
                    this.filterCustomers();
                    this.showCustomerDropdown = false;
                },
                async fetchProducts() {
                    const params = new URLSearchParams({ q: this.search });
                    const response = await fetch(`{{ route('pos.products') }}?${params.toString()}`);
                    this.products = await response.json();
                },
                changeBranch() {
                    window.location = `{{ route('pos.index') }}?branch_id=${this.branchId}`;
                },
                addToCart(product) {
                    const existing = this.cart.find(item => item.product_id === product.id);
                    if (existing) {
                        existing.quantity += 1;
                        return;
                    }
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        quantity: 1,
                        unit_price: parseFloat(product.sale_price),
                        tax_rate: this.toAmount(product.tax_rate),
                        discount_percent: 0,
                    });
                },
                removeItem(index) {
                    this.cart.splice(index, 1);
                },
                resetCheckoutState() {
                    this.customerId = null;
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
