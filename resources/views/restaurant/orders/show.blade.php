@extends('layouts.admin')

@section('content')
    @php
        $initialItems = $order->items->map(function ($item) {
            $includedDelta = (float) $item->selections->where('selection_action', 'include')->sum('price_delta');

            return [
                'product_id' => $item->product_id,
                'name' => $item->product?->name ?? 'Producto',
                'sku' => $item->product?->sku ?? '',
                'quantity' => (float) $item->quantity,
                'base_unit_price' => (float) $item->unit_price - $includedDelta,
                'unit_price' => (float) $item->unit_price,
                'available_stock' => (float) ($availableStockByProduct[$item->product_id] ?? 0),
                'tax_rate' => (float) ($item->product?->tax?->rate ?? 0),
                'notes' => $item->notes,
                'kitchen_status' => $item->kitchen_status,
                'modifier_groups' => $item->product?->modifierGroups->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'selection_type' => $group->selection_type,
                    'is_required' => (bool) $group->is_required,
                    'min_select' => (int) $group->min_select,
                    'max_select' => (int) $group->max_select,
                    'options' => $group->options->where('is_active', true)->map(fn ($option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'price_delta' => (float) $option->price_delta,
                        'inventory_quantity' => (float) ($option->inventory_quantity ?? 0),
                        'inventory_unit' => $option->inventory_unit,
                        'inventory_unit_factor' => (float) ($option->inventory_unit_factor ?? 1),
                        'product_id' => $option->product_id,
                        'is_default' => (bool) $option->is_default,
                    ])->values()->all(),
                ])->values()->all() ?? [],
                'saved_modifier_selections' => $item->selections->map(fn ($selection) => [
                    'group_id' => $selection->product_modifier_group_id,
                    'option_id' => $selection->product_modifier_option_id,
                    'action' => $selection->selection_action,
                ])->values()->all(),
            ];
        })->values()->all();
    @endphp

    <div x-data="restaurantOrderApp()" x-init="init()" class="restaurant-workspace grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <div class="page-header restaurant-module-header">
                <div class="page-header-row">
                    <div>
                        <h1 class="page-title">Pedido #{{ $order->order_number }}</h1>
                        <p class="page-subtitle">
                            {{ $order->table?->name ?? ($orderTypes[$order->order_type] ?? $order->order_type) }}
                            · Estado {{ \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status }}
                        </p>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('restaurant.index', ['branch_id' => $order->branch_id]) }}" class="btn btn-outline btn-sm">Volver</a>
                    </div>
                </div>

                <div class="restaurant-header-chips">
                    <span class="chip">Sucursal {{ $order->branch?->name ?? 'Actual' }}</span>
                    <span class="chip">{{ $orderTypes[$order->order_type] ?? $order->order_type }}</span>
                    <span class="chip">Total actual ${{ number_format((float) $order->total, 2) }}</span>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('restaurant.orders.update', $order) }}" method="POST" x-ref="orderForm" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="items" :value="itemsPayload">

                <div class="panel restaurant-module-panel">
                    <div class="panel-body">
                        <div class="restaurant-form-panel__header">
                            <div>
                                <h2 class="text-sm font-semibold">Datos del servicio</h2>
                                <p class="text-xs text-base-content/60">Sucursal, mesa, cliente y contexto general del pedido.</p>
                            </div>
                        </div>

                        <div class="restaurant-order-meta-grid">
                            <div>
                                <label class="field-label">Sucursal</label>
                                <select name="branch_id" x-model="branchId" class="select select-bordered w-full" @change="handleOrderTypeChange()">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="relative mt-2" @click.outside="showCustomerDropdown = false">
                                    <input
                                        type="text"
                                        x-model="customerSearch"
                                        @click="showCustomerDropdown = true; filterCustomers()"
                                        @focus="showCustomerDropdown = true; filterCustomers()"
                                        @input="filterCustomers()"
                                        @keydown.escape="showCustomerDropdown = false"
                                        placeholder="Busca por nombre o identificacion"
                                        class="input input-bordered w-full"
                                        autocomplete="off"
                                    >
                                    <button
                                        type="button"
                                        x-show="customerId"
                                        x-cloak
                                        @click="clearCustomer()"
                                        class="btn btn-ghost btn-xs absolute right-2 top-2"
                                    >
                                        Limpiar
                                    </button>
                                    <div
                                        x-show="showCustomerDropdown"
                                        x-cloak
                                        class="absolute z-40 mt-1 w-full max-h-60 overflow-y-auto rounded-xl border border-base-300 bg-base-100 shadow-lg"
                                        style="display: none;"
                                    >
                                        <button
                                            type="button"
                                            @click="clearCustomer()"
                                            class="w-full border-b border-base-200 px-3 py-2 text-left text-sm hover:bg-base-200"
                                        >
                                            Cliente mostrador
                                        </button>
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
                            <div>
                                <label class="field-label">Tipo de pedido</label>
                                <select name="order_type" x-model="orderType" class="select select-bordered w-full" @change="handleOrderTypeChange()">
                                    @foreach ($orderTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="orderType === 'dine_in'">
                                <label class="field-label">Mesa</label>
                                <select name="restaurant_table_id" x-model="tableId" class="select select-bordered w-full">
                                    <option value="">Selecciona una mesa</option>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->id }}">{{ $table->name }} · Mesa {{ $table->number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Cliente</label>
                                <select name="customer_id" x-model="customerId" class="hidden">
                                    <option value="">Cliente mostrador</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->document ? ' · '.$customer->document : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="field-label">Notas generales</label>
                                <textarea name="notes" x-model="orderNotes" class="textarea textarea-bordered w-full" rows="3" placeholder="Observaciones del pedido"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel restaurant-module-panel">
                    <div class="panel-body">
                        <div class="restaurant-order-toolbar">
                            <div>
                                <h2 class="text-sm font-semibold">Productos</h2>
                                <p class="text-xs text-base-content/60">Agrega productos y guarda cambios antes de enviar a cocina o facturar.</p>
                            </div>
                            <div class="restaurant-order-toolbar__actions">
                                <input x-model="search" @input.debounce.250ms="fetchProducts" placeholder="Buscar producto" class="input input-bordered input-sm w-52">
                                <button type="button" @click="fetchProducts" class="btn btn-outline btn-sm">Buscar</button>
                            </div>
                        </div>

                        <div class="restaurant-order-products-grid">
                            <template x-for="product in products" :key="product.id">
                                <button type="button" @click="addToCart(product)" class="restaurant-order-product-card">
                                    <div class="restaurant-order-product-card__body">
                                        <p class="restaurant-order-product-card__title" x-text="product.name"></p>
                                        <p class="restaurant-order-product-card__meta" x-text="product.sku || product.barcode || 'Sin codigo'"></p>
                                        <p class="restaurant-order-product-card__meta">Disponible: <span x-text="toAmount(product.available_stock).toFixed(3)"></span></p>
                                        <p class="restaurant-order-product-card__price">$<span x-text="toAmount(product.sale_price).toFixed(2)"></span></p>
                                        <p x-show="(product.modifier_groups || []).length > 0" class="restaurant-order-product-card__hint">Configurable por componentes</p>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <div class="mt-6 space-y-3">
                            <template x-if="cart.length === 0">
                                <div class="restaurant-empty-inline">Aun no hay productos en el pedido.</div>
                            </template>

                            <template x-for="(item, index) in cart" :key="`${item.product_id}-${index}`">
                                <div class="restaurant-order-line">
                                    <div class="restaurant-order-line__top">
                                        <div>
                                            <p class="text-sm font-semibold" x-text="item.name"></p>
                                            <p class="text-xs text-base-content/60" x-text="item.sku || 'Sin SKU'"></p>
                                            <p class="mt-1 text-xs text-base-content/60">Cocina: <span x-text="kitchenLabel(item.kitchen_status)"></span></p>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-xs" @click="removeItem(index)" :disabled="!editable">Quitar</button>
                                    </div>

                                    <div class="restaurant-order-line__grid">
                                        <div>
                                            <label class="text-xs text-base-content/60">Cantidad</label>
                                            <input type="number" step="0.001" min="0.001" x-model.number="item.quantity" @input="sanitizeItemQuantity(item)" class="input input-bordered input-sm w-full" :disabled="!editable">
                                        </div>
                                        <div>
                                            <label class="text-xs text-base-content/60">Precio base</label>
                                            <input type="number" step="0.01" min="0" x-model.number="item.base_unit_price" class="input input-bordered input-sm w-full" :disabled="!editable">
                                        </div>
                                        <div>
                                            <label class="text-xs text-base-content/60">Subtotal</label>
                                            <div class="input input-bordered input-sm flex items-center">$<span x-text="lineSubtotal(item).toFixed(2)"></span></div>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-[11px] text-base-content/60">Disponible: <span x-text="toAmount(item.available_stock).toFixed(3)"></span></p>

                                    <div class="mt-4 space-y-3" x-show="(item.modifier_groups || []).length > 0">
                                        <template x-for="group in item.modifier_groups" :key="`${item.product_id}-${group.id}`">
                                            <div class="restaurant-order-modifier">
                                                <p class="text-sm font-medium" x-text="group.name"></p>
                                                <p class="text-[11px] text-base-content/60" x-text="groupHelp(group)"></p>
                                                <div class="mt-3 space-y-2">
                                                    <template x-for="option in group.options" :key="option.id">
                                                        <label class="flex items-center justify-between gap-3 rounded-lg border border-base-200 px-3 py-2 text-sm">
                                                            <span class="flex items-center gap-2">
                                                                <template x-if="group.selection_type === 'single'">
                                                                    <input type="radio" :name="`modifier-${item.product_id}-${group.id}`" :checked="isOptionSelected(group, option)" @change="toggleGroupOption(group, option, true)" :disabled="!editable">
                                                                </template>
                                                                <template x-if="group.selection_type !== 'single'">
                                                                    <input type="checkbox" :checked="isOptionSelected(group, option)" @change="toggleGroupOption(group, option, $event.target.checked)" :disabled="!editable">
                                                                </template>
                                                                <span x-text="option.label"></span>
                                                            </span>
                                                            <span class="text-xs text-base-content/60" x-show="toAmount(option.price_delta) > 0">+$<span x-text="toAmount(option.price_delta).toFixed(2)"></span></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="mt-3">
                                        <label class="text-xs text-base-content/60">Nota por producto</label>
                                        <textarea x-model="item.notes" class="textarea textarea-bordered w-full" rows="2" :disabled="!editable" placeholder="Termino medio, sin hielo, etc."></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="restaurant-order-footer">
                            <div class="restaurant-order-totals">
                                <div class="flex justify-between"><span>Subtotal</span><span>$<span x-text="subtotal.toFixed(2)"></span></span></div>
                                <div class="mt-2 flex justify-between"><span>Impuestos</span><span>$<span x-text="taxTotal.toFixed(2)"></span></span></div>
                                <div class="mt-2 flex justify-between font-semibold"><span>Total</span><span>$<span x-text="total.toFixed(2)"></span></span></div>
                            </div>
                            <div class="flex items-end justify-end">
                                <button class="btn btn-primary w-full md:w-auto" :disabled="!editable || cart.length === 0">Guardar pedido</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-6 xl:col-span-4">
            <div class="panel restaurant-module-panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Acciones</h2>
                    <div class="mt-4 grid gap-3">
                        @if ($order->status === \App\Models\RestaurantOrder::STATUS_OPEN)
                            <form method="POST" action="{{ route('restaurant.orders.send-to-kitchen', $order) }}">
                                @csrf
                                <button class="btn btn-outline w-full">Enviar a cocina</button>
                            </form>
                        @endif

                        @if (in_array($order->status, [
                            \App\Models\RestaurantOrder::STATUS_READY,
                            \App\Models\RestaurantOrder::STATUS_IN_PREPARATION,
                            \App\Models\RestaurantOrder::STATUS_SENT_TO_KITCHEN,
                        ], true))
                            <form method="POST" action="{{ route('restaurant.orders.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ \App\Models\RestaurantOrder::STATUS_DELIVERED }}">
                                <button class="btn btn-outline w-full">Marcar como entregado</button>
                            </form>
                        @endif

                        @if (! $order->sale_id)
                            <form method="POST" action="{{ route('restaurant.orders.convert-to-sale', $order) }}" x-ref="checkoutForm" class="restaurant-side-stack">
                                @csrf
                                <input type="hidden" name="customer_id" :value="customerId">
                                <input type="hidden" name="payments" :value="paymentsPayload">
                                <div class="space-y-3">
                                    <div>
                                        <label class="field-label">Efectivo</label>
                                        <input type="number" step="0.01" min="0" x-model.number="paymentCash" class="input input-bordered w-full">
                                    </div>
                                    <div>
                                        <label class="field-label">Tarjeta</label>
                                        <input type="number" step="0.01" min="0" x-model.number="paymentCard" class="input input-bordered w-full">
                                    </div>
                                    <div>
                                        <label class="field-label">Transferencia</label>
                                        <input type="number" step="0.01" min="0" x-model.number="paymentTransfer" class="input input-bordered w-full">
                                    </div>
                                    <div>
                                        <label class="field-label">Credito</label>
                                        <input type="number" step="0.01" min="0" x-model.number="paymentCredit" class="input input-bordered w-full">
                                    </div>
                                    <div class="restaurant-order-totals text-sm">
                                        <div class="flex justify-between"><span>Total cubierto</span><span>$<span x-text="coveredTotal.toFixed(2)"></span></span></div>
                                        <div class="mt-2 flex justify-between"><span>Saldo</span><span>$<span x-text="pendingTotal.toFixed(2)"></span></span></div>
                                        <div class="mt-2 flex justify-between"><span>Cambio</span><span>$<span x-text="changeTotal.toFixed(2)"></span></span></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-full" @click.prevent="submitCheckout">Convertir en venta y cerrar</button>
                                </div>
                            </form>
                        @else
                            <div class="restaurant-summary-card text-sm">
                                <p class="font-semibold">Venta generada</p>
                                <p class="mt-1 text-base-content/60">Factura #{{ $order->sale->sale_number }}</p>
                                <a href="{{ route('sales.show', $order->sale) }}" class="btn btn-outline btn-sm mt-3 w-full">Ver venta</a>
                            </div>
                            @if ($order->status !== \App\Models\RestaurantOrder::STATUS_CLOSED)
                                <form method="POST" action="{{ route('restaurant.orders.close', $order) }}">
                                    @csrf
                                    <button class="btn btn-outline w-full">Cerrar pedido</button>
                                </form>
                            @endif
                        @endif

                        @if (! in_array($order->status, [\App\Models\RestaurantOrder::STATUS_CLOSED, \App\Models\RestaurantOrder::STATUS_CANCELLED], true))
                            <form method="POST" action="{{ route('restaurant.orders.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ \App\Models\RestaurantOrder::STATUS_CANCELLED }}">
                                <button class="btn btn-danger w-full">Cancelar pedido</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="panel restaurant-module-panel">
                <div class="panel-body text-sm">
                    <h2 class="text-sm font-semibold">Resumen</h2>
                    <div class="restaurant-summary-card mt-4 space-y-2">
                        <div class="flex justify-between"><span>Mesa</span><span>{{ $order->table?->name ?? 'Sin mesa' }}</span></div>
                        <div class="flex justify-between"><span>Cliente</span><span>{{ $order->customer?->name ?? 'Cliente mostrador' }}</span></div>
                        <div class="flex justify-between"><span>Abierto</span><span>{{ optional($order->opened_at)->format('d/m/Y H:i') }}</span></div>
                        <div class="flex justify-between"><span>Total actual</span><span>$<span x-text="total.toFixed(2)"></span></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function restaurantOrderApp() {
            return {
                branchId: @js((string) $order->branch_id),
                orderType: @js($order->order_type),
                tableId: @js($order->restaurant_table_id ? (string) $order->restaurant_table_id : ''),
                customerId: @js($order->customer_id ? (string) $order->customer_id : ''),
                customerSearch: '',
                customers: @js($customers->map(fn ($customer) => ['id' => $customer->id, 'name' => $customer->name, 'document' => $customer->document])->values()->all()),
                filteredCustomers: [],
                showCustomerDropdown: false,
                minCustomerChars: 1,
                orderNotes: @js($order->notes),
                search: '',
                products: [],
                cart: @js($initialItems),
                paymentCash: 0,
                paymentCard: 0,
                paymentTransfer: 0,
                paymentCredit: 0,
                editable: @js($order->status === \App\Models\RestaurantOrder::STATUS_OPEN),
                init() {
                    this.cart = (this.cart || []).map(item => this.normalizeCartItem(item));
                    if (this.customerId) {
                        const selected = this.customers.find(customer => String(customer.id) === String(this.customerId));
                        if (selected) {
                            this.customerSearch = selected.name;
                        }
                    }
                    this.filterCustomers();
                    this.fetchProducts();
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
                    this.customerId = String(customer.id);
                    this.customerSearch = customer.name;
                    this.showCustomerDropdown = false;
                },
                clearCustomer() {
                    this.customerId = '';
                    this.customerSearch = '';
                    this.filterCustomers();
                    this.showCustomerDropdown = false;
                },
                async fetchProducts() {
                    const params = new URLSearchParams({ q: this.search || '', branch_id: this.branchId || '' });
                    const response = await fetch(`{{ route('restaurant.products') }}?${params.toString()}`, {
                        headers: { Accept: 'application/json' },
                    });
                    this.products = await response.json();
                },
                toAmount(value) {
                    const amount = parseFloat(value || 0);
                    return Number.isFinite(amount) ? amount : 0;
                },
                normalizeCartItem(item) {
                    const normalized = {
                        ...item,
                        base_unit_price: this.toAmount(item.base_unit_price ?? item.unit_price ?? 0),
                        modifier_groups: (item.modifier_groups || []).map(group => ({
                            ...group,
                            options: (group.options || []).map(option => ({
                                ...option,
                                selected: this.resolveOptionSelected(group, option, item.saved_modifier_selections || []),
                            })),
                        })),
                    };

                    normalized.unit_price = this.computeUnitPrice(normalized);

                    return normalized;
                },
                resolveOptionSelected(group, option, savedSelections = []) {
                    const saved = savedSelections.find(selection => String(selection.group_id) === String(group.id) && String(selection.option_id) === String(option.id));
                    if (group.selection_type === 'remove') {
                        return !saved;
                    }
                    if (saved) {
                        return true;
                    }
                    return !!option.is_default;
                },
                selectedModifierPayload(item) {
                    const payload = [];
                    (item.modifier_groups || []).forEach(group => {
                        (group.options || []).forEach(option => {
                            if (group.selection_type === 'remove') {
                                if (!option.selected) {
                                    payload.push({ group_id: group.id, option_id: option.id, action: 'remove' });
                                }
                                return;
                            }

                            if (option.selected) {
                                payload.push({ group_id: group.id, option_id: option.id, action: 'include' });
                            }
                        });
                    });

                    return payload;
                },
                selectedModifierDelta(item) {
                    return this.selectedModifierPayload(item).reduce((carry, selection) => {
                        if (selection.action !== 'include') {
                            return carry;
                        }

                        const group = (item.modifier_groups || []).find(entry => String(entry.id) === String(selection.group_id));
                        const option = (group?.options || []).find(entry => String(entry.id) === String(selection.option_id));

                        return carry + this.toAmount(option?.price_delta || 0);
                    }, 0);
                },
                computeUnitPrice(item) {
                    return this.toAmount(item.base_unit_price) + this.selectedModifierDelta(item);
                },
                lineSubtotal(item) {
                    item.unit_price = this.computeUnitPrice(item);
                    return this.toAmount(item.quantity) * this.toAmount(item.unit_price);
                },
                get subtotal() {
                    return this.cart.reduce((carry, item) => carry + this.lineSubtotal(item), 0);
                },
                get taxTotal() {
                    return this.cart.reduce((carry, item) => carry + (this.lineSubtotal(item) * (this.toAmount(item.tax_rate) / 100)), 0);
                },
                get total() {
                    return this.subtotal + this.taxTotal;
                },
                addToCart(product) {
                    if (!this.editable) {
                        return;
                    }

                    const usedQuantity = this.cart
                        .filter(item => String(item.product_id) === String(product.id))
                        .reduce((carry, item) => carry + this.toAmount(item.quantity), 0);
                    if (this.toAmount(product.available_stock) > 0 && (usedQuantity + 1) > this.toAmount(product.available_stock)) {
                        alert('No puedes vender una cantidad superior al stock disponible.');
                        return;
                    }

                    const item = this.normalizeCartItem({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        quantity: 1,
                        base_unit_price: this.toAmount(product.sale_price),
                        unit_price: this.toAmount(product.sale_price),
                        tax_rate: this.toAmount(product.tax_rate),
                        notes: '',
                        kitchen_status: 'pending',
                        available_stock: this.toAmount(product.available_stock),
                        modifier_groups: product.modifier_groups || [],
                        saved_modifier_selections: [],
                    });

                    this.cart.push(item);
                },
                sanitizeItemQuantity(item) {
                    const quantity = Math.max(0.001, this.toAmount(item.quantity));
                    const available = this.toAmount(item.available_stock);
                    if (available > 0 && quantity > available) {
                        item.quantity = available;
                        return;
                    }
                    item.quantity = quantity;
                },
                hasCartStockOverflow() {
                    const totals = {};
                    for (const item of this.cart) {
                        const key = String(item.product_id);
                        totals[key] = (totals[key] || 0) + this.toAmount(item.quantity);
                        const available = this.toAmount(item.available_stock);
                        if (available > 0 && totals[key] > available) {
                            return true;
                        }
                    }

                    return false;
                },
                removeItem(index) {
                    this.cart.splice(index, 1);
                },
                groupHelp(group) {
                    if (group.selection_type === 'remove') {
                        return 'Desmarca lo que el cliente no quiere.';
                    }

                    if (group.selection_type === 'single') {
                        return group.is_required ? 'Selecciona una opcion obligatoria.' : 'Selecciona una opcion si aplica.';
                    }

                    const max = this.toAmount(group.max_select);
                    return max > 0 ? `Selecciona hasta ${max} opciones.` : 'Selecciona varias opciones.';
                },
                isOptionSelected(group, option) {
                    return !!option.selected;
                },
                toggleGroupOption(group, option, checked) {
                    if (group.selection_type === 'single') {
                        group.options.forEach(entry => {
                            entry.selected = String(entry.id) === String(option.id) ? checked : false;
                        });
                        return;
                    }

                    option.selected = !!checked;
                },
                kitchenLabel(status) {
                    const labels = {
                        pending: 'Pendiente',
                        in_preparation: 'En preparacion',
                        ready: 'Listo',
                        delivered: 'Entregado',
                    };

                    return labels[status] || status;
                },
                handleOrderTypeChange() {
                    if (this.orderType !== 'dine_in') {
                        this.tableId = '';
                    }
                },
                get itemsPayload() {
                    return JSON.stringify(this.cart.map(item => ({
                        product_id: item.product_id,
                        quantity: this.toAmount(item.quantity),
                        unit_price: this.toAmount(item.base_unit_price),
                        notes: item.notes || null,
                        modifier_selections: this.selectedModifierPayload(item),
                    })));
                },
                get paymentsPayload() {
                    const payments = [];
                    if (this.toAmount(this.paymentCash) > 0) payments.push({ method: 'cash', amount: this.toAmount(this.paymentCash) });
                    if (this.toAmount(this.paymentCard) > 0) payments.push({ method: 'card', amount: this.toAmount(this.paymentCard) });
                    if (this.toAmount(this.paymentTransfer) > 0) payments.push({ method: 'transfer', amount: this.toAmount(this.paymentTransfer) });
                    if (this.toAmount(this.paymentCredit) > 0) payments.push({ method: 'credit', amount: this.toAmount(this.paymentCredit) });
                    return JSON.stringify(payments);
                },
                get coveredTotal() {
                    return this.toAmount(this.paymentCash) + this.toAmount(this.paymentCard) + this.toAmount(this.paymentTransfer) + this.toAmount(this.paymentCredit);
                },
                get pendingTotal() {
                    return Math.max(0, this.total - this.coveredTotal);
                },
                get changeTotal() {
                    const nonCredit = this.toAmount(this.paymentCash) + this.toAmount(this.paymentCard) + this.toAmount(this.paymentTransfer);
                    return Math.max(0, nonCredit - this.total);
                },
                submitCheckout() {
                    const payments = JSON.parse(this.paymentsPayload);
                    if (this.hasCartStockOverflow()) {
                        alert('Hay productos con cantidad superior al stock disponible.');
                        return;
                    }
                    if (payments.length === 0) {
                        alert('Debes registrar al menos un pago.');
                        return;
                    }
                    if (this.coveredTotal + 0.0001 < this.total) {
                        alert('El pago mas credito es insuficiente.');
                        return;
                    }
                    if (this.coveredTotal > this.total + 0.0001) {
                        alert('El pago mas credito no puede superar el total.');
                        return;
                    }
                    this.$refs.checkoutForm.submit();
                },
            };
        }
    </script>
@endsection
