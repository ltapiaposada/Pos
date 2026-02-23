@extends('layouts.admin')

@section('content')
    <div x-data="purchaseApp()" x-init="init()" class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="page-header">
                <div class="page-header-row">
                    <div>
                        <h1 class="page-title">Nueva compra</h1>
                        <p class="page-subtitle">Ingresa productos comprados y costos</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Items de compra</h2>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <select x-model.number="selectedProductId" class="select select-bordered w-full">
                            <option value="">Selecciona un producto</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="product.id" x-text="`${product.name} (${product.sku})`"></option>
                            </template>
                        </select>
                        <button type="button" class="btn btn-outline w-full sm:w-auto" @click="addSelectedProduct">Agregar</button>
                    </div>

                    <template x-if="items.length === 0">
                        <p class="text-sm text-base-content/60 mt-3">Sin productos en la compra.</p>
                    </template>

                    <template x-for="(item, index) in items" :key="item.product_id">
                        <div class="surface-muted mt-3 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-semibold" x-text="item.name"></div>
                                    <div class="text-xs text-base-content/60" x-text="item.sku"></div>
                                </div>
                                <button type="button" class="btn btn-danger btn-xs" @click="removeItem(index)">Quitar</button>
                            </div>
                            <div class="mt-2 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                                <div>
                                    <label class="text-base-content/60">Cantidad</label>
                                    <input type="number" min="0.001" step="0.001" x-model.number="item.quantity" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                                <div>
                                    <label class="text-base-content/60">Costo unitario</label>
                                    <input type="number" min="0" step="0.01" x-model.number="item.unit_cost" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                                <div>
                                    <label class="text-base-content/60">Imp %</label>
                                    <input type="number" min="0" step="0.01" x-model.number="item.tax_rate" class="input input-bordered input-sm sm:input-xs w-full">
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="text-base-content/60">Subtotal item</span>
                                <span class="font-semibold" x-text="`$${(toAmount(item.quantity) * toAmount(item.unit_cost)).toFixed(2)}`"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <form method="POST" action="{{ route('purchases.store') }}" class="panel">
                @csrf
                <input type="hidden" name="contact_id" :value="selectedContactId">
                <input type="hidden" name="items" :value="itemsPayload">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Datos de compra</h2>

                    <div class="mt-3 space-y-2">
                        <div>
                            <label class="field-label">Sucursal</label>
                            <select name="branch_id" x-model="branchId" class="select select-bordered w-full" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Contacto proveedor</label>
                            <div class="relative" @click.outside="showContactDropdown = false">
                                <input
                                    type="text"
                                    x-model="contactSearch"
                                    @focus="showContactDropdown = true; filterContacts()"
                                    @input="showContactDropdown = true; filterContacts()"
                                    @keydown.escape="showContactDropdown = false"
                                    placeholder="Buscar por nombre o identificacion"
                                    class="input input-bordered w-full"
                                    autocomplete="off"
                                >
                                <div
                                    x-show="showContactDropdown"
                                    x-cloak
                                    class="absolute z-40 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow-lg max-h-60 overflow-y-auto"
                                    style="display: none;"
                                >
                                    <button
                                        type="button"
                                        @click="clearContact"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-base-200"
                                    >
                                        Sin contacto
                                    </button>
                                    <template x-for="contact in filteredContacts" :key="contact.id">
                                        <button
                                            type="button"
                                            @click="selectContact(contact)"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-base-200"
                                        >
                                            <span class="font-medium" x-text="contact.name"></span>
                                            <span class="ml-2 text-xs text-base-content/60" x-text="contact.document ? `ID ${contact.document}` : 'Sin ID'"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredContacts.length === 0 && contactSearch.trim().length > 0" class="px-3 py-2 text-xs text-base-content/60">
                                        Sin coincidencias
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Proveedor</label>
                            <input name="supplier_name" x-model="supplierName" value="{{ old('supplier_name') }}" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="field-label">Documento proveedor</label>
                            <input name="supplier_document" x-model="supplierDocument" value="{{ old('supplier_document') }}" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="field-label">Factura proveedor</label>
                            <input name="invoice_number" value="{{ old('invoice_number') }}" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="field-label">Forma de pago</label>
                            <select name="payment_method" x-model="paymentMethod" class="select select-bordered w-full" required>
                                <option value="credit">Crédito</option>
                                <option value="cash">Efectivo</option>
                                <option value="transfer">Transferencia</option>
                            </select>
                            <p class="mt-1 text-xs text-base-content/60">Usa Crédito para dejar saldo pendiente al proveedor.</p>
                        </div>
                        <div>
                            <label class="field-label">Valor pagado</label>
                            <input name="paid_total" type="number" min="0" step="0.01" x-model.number="paidTotal" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="field-label">Notas</label>
                            <input name="notes" value="{{ old('notes') }}" class="input input-bordered w-full">
                        </div>
                    </div>

                    <div class="mt-4 border-t border-base-200 pt-3 text-sm space-y-2">
                        <div class="flex justify-between"><span>Subtotal</span><span>$<span x-text="subtotal.toFixed(2)"></span></span></div>
                        <div class="flex justify-between"><span>Impuestos</span><span>$<span x-text="taxTotal.toFixed(2)"></span></span></div>
                        <div class="flex justify-between font-semibold"><span>Total</span><span>$<span x-text="total.toFixed(2)"></span></span></div>
                        <div class="flex justify-between"><span>Saldo</span><span>$<span x-text="balanceTotal.toFixed(2)"></span></span></div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4 w-full">Registrar compra</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function purchaseApp() {
            return {
                branchId: '{{ $branchId }}',
                products: @js($productCatalog),
                contacts: @js($contacts),
                selectedContactId: @js(old('contact_id')),
                supplierName: @js(old('supplier_name', '')),
                supplierDocument: @js(old('supplier_document', '')),
                contactSearch: '',
                filteredContacts: [],
                showContactDropdown: false,
                selectedProductId: '',
                items: [],
                paymentMethod: 'credit',
                paidTotal: 0,
                init() {
                    if (this.selectedContactId) {
                        const selected = this.contacts.find(contact => String(contact.id) === String(this.selectedContactId));
                        if (selected) {
                            this.contactSearch = this.contactLabel(selected);
                        }
                    }
                    if (!this.contactSearch && this.supplierName) {
                        this.contactSearch = this.supplierName;
                    }
                    this.filterContacts();
                },
                toAmount(value) {
                    const number = Number(value);
                    return Number.isFinite(number) ? number : 0;
                },
                contactLabel(contact) {
                    return contact.document ? `${contact.name} - ${contact.document}` : contact.name;
                },
                filterContacts() {
                    const term = this.contactSearch.trim().toLowerCase();
                    if (term.length === 0) {
                        this.filteredContacts = this.contacts.slice(0, 10);
                        return;
                    }
                    this.filteredContacts = this.contacts
                        .filter(contact => {
                            const name = String(contact.name || '').toLowerCase();
                            const document = String(contact.document || '').toLowerCase();
                            return name.includes(term) || document.includes(term);
                        })
                        .slice(0, 10);
                },
                selectContact(contact) {
                    this.selectedContactId = contact.id;
                    this.supplierName = contact.name || '';
                    this.supplierDocument = contact.document || '';
                    this.contactSearch = this.contactLabel(contact);
                    this.showContactDropdown = false;
                },
                clearContact() {
                    this.selectedContactId = '';
                    this.supplierName = '';
                    this.supplierDocument = '';
                    this.contactSearch = '';
                    this.filterContacts();
                    this.showContactDropdown = false;
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (this.toAmount(item.quantity) * this.toAmount(item.unit_cost)), 0);
                },
                get taxTotal() {
                    return this.items.reduce((sum, item) => {
                        const base = this.toAmount(item.quantity) * this.toAmount(item.unit_cost);
                        return sum + (base * (this.toAmount(item.tax_rate) / 100));
                    }, 0);
                },
                get total() {
                    return this.subtotal + this.taxTotal;
                },
                get balanceTotal() {
                    return Math.max(0, this.total - this.toAmount(this.paidTotal));
                },
                get itemsPayload() {
                    return JSON.stringify(this.items.map(item => ({
                        product_id: item.product_id,
                        quantity: this.toAmount(item.quantity),
                        unit_cost: this.toAmount(item.unit_cost),
                    })));
                },
                addSelectedProduct() {
                    const product = this.products.find(p => Number(p.id) === Number(this.selectedProductId));
                    if (!product) {
                        return;
                    }

                    const existing = this.items.find(i => Number(i.product_id) === Number(product.id));
                    if (existing) {
                        existing.quantity += 1;
                        return;
                    }

                    this.items.push({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        quantity: 1,
                        unit_cost: this.toAmount(product.cost_price),
                        tax_rate: this.toAmount(product.tax_rate),
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
            };
        }
    </script>
@endsection
