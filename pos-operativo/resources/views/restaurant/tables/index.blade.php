@extends('layouts.admin')

@section('content')
    @php
        $tableCollection = $tables->getCollection();
        $statusMeta = [
            \App\Models\RestaurantTable::STATUS_AVAILABLE => ['label' => 'Disponible', 'badge' => 'badge-success', 'ring' => 'restaurant-table--available', 'icon' => 'fa-circle-check'],
            \App\Models\RestaurantTable::STATUS_OCCUPIED => ['label' => 'Ocupada', 'badge' => 'badge-warning', 'ring' => 'restaurant-table--occupied', 'icon' => 'fa-utensils'],
            \App\Models\RestaurantTable::STATUS_RESERVED => ['label' => 'Reservada', 'badge' => 'badge-info', 'ring' => 'restaurant-table--reserved', 'icon' => 'fa-calendar-check'],
            \App\Models\RestaurantTable::STATUS_CLEANING => ['label' => 'En limpieza', 'badge' => 'badge-secondary', 'ring' => 'restaurant-table--cleaning', 'icon' => 'fa-soap'],
            \App\Models\RestaurantTable::STATUS_INACTIVE => ['label' => 'Inactiva', 'badge' => 'badge-danger', 'ring' => 'restaurant-table--inactive', 'icon' => 'fa-ban'],
        ];
        $totals = [
            'all' => $tableCollection->count(),
            'available' => $tableCollection->where('status', \App\Models\RestaurantTable::STATUS_AVAILABLE)->count(),
            'occupied' => $tableCollection->where('status', \App\Models\RestaurantTable::STATUS_OCCUPIED)->count(),
            'reserved' => $tableCollection->where('status', \App\Models\RestaurantTable::STATUS_RESERVED)->count(),
            'cleaning' => $tableCollection->where('status', \App\Models\RestaurantTable::STATUS_CLEANING)->count(),
            'inactive' => $tableCollection->where('status', \App\Models\RestaurantTable::STATUS_INACTIVE)->count(),
            'active' => $tableCollection->where('is_active', true)->count(),
        ];
    @endphp

    <div class="page-header restaurant-floor-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Mesas del restaurante</h1>
                <p class="page-subtitle">Configuración visual del salón, zonas y operación de cada mesa.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('restaurant.index') }}" class="btn btn-outline btn-sm">Ir al salón</a>
            </div>
        </div>

        <div class="restaurant-floor-kpis">
            <article class="restaurant-floor-kpi">
                <span class="restaurant-floor-kpi__label">Total</span>
                <span class="restaurant-floor-kpi__value">{{ $totals['all'] }}</span>
                <span class="restaurant-floor-kpi__hint">Mesas cargadas en esta vista</span>
            </article>
            <article class="restaurant-floor-kpi">
                <span class="restaurant-floor-kpi__label">Disponibles</span>
                <span class="restaurant-floor-kpi__value text-success">{{ $totals['available'] }}</span>
                <span class="restaurant-floor-kpi__hint">Listas para recibir clientes</span>
            </article>
            <article class="restaurant-floor-kpi">
                <span class="restaurant-floor-kpi__label">Ocupadas</span>
                <span class="restaurant-floor-kpi__value text-warning">{{ $totals['occupied'] }}</span>
                <span class="restaurant-floor-kpi__hint">Con servicio en curso</span>
            </article>
            <article class="restaurant-floor-kpi">
                <span class="restaurant-floor-kpi__label">Activas</span>
                <span class="restaurant-floor-kpi__value">{{ $totals['active'] }}</span>
                <span class="restaurant-floor-kpi__hint">Mesas habilitadas operativamente</span>
            </article>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-4">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mt-4">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-4">
            <div class="panel restaurant-side-panel">
                <div class="panel-body">
                    <div class="restaurant-side-panel__heading">
                        <div>
                            <h2 class="text-sm font-semibold">Nueva mesa</h2>
                            <p class="text-xs text-base-content/60">Crea una mesa con ubicación, capacidad y estado inicial.</p>
                        </div>
                        <span class="chip">Setup</span>
                    </div>

                    <form method="POST" action="{{ route('restaurant.tables.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="field-label">Sucursal</label>
                            <select name="branch_id" class="select select-bordered w-full">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branchFilter === $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="field-label">Nombre visible</label>
                                <input name="name" class="input input-bordered w-full" placeholder="Terraza 1">
                            </div>
                            <div>
                                <label class="field-label">Número corto</label>
                                <input name="number" class="input input-bordered w-full" placeholder="T-01">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="field-label">Capacidad</label>
                                <input name="capacity" type="number" min="1" value="4" class="input input-bordered w-full">
                            </div>
                            <div>
                                <label class="field-label">Estado inicial</label>
                                <select name="status" class="select select-bordered w-full">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="field-label">Ubicación / zona</label>
                            <input name="location" class="input input-bordered w-full" placeholder="Salón principal, terraza, VIP...">
                        </div>

                        <div>
                            <label class="field-label">Disponibilidad</label>
                            <select name="is_active" class="select select-bordered w-full">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>

                        <button class="btn btn-primary w-full">Crear mesa</button>
                    </form>
                </div>
            </div>

            <div class="panel restaurant-side-panel">
                <div class="panel-body">
                    <div class="restaurant-side-panel__heading">
                        <div>
                            <h2 class="text-sm font-semibold">Lectura rápida del salón</h2>
                            <p class="text-xs text-base-content/60">Un resumen útil para anfitrión, supervisor o caja.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ([
                            ['label' => 'Reservadas', 'count' => $totals['reserved'], 'tone' => 'restaurant-mini-stat--reserved'],
                            ['label' => 'En limpieza', 'count' => $totals['cleaning'], 'tone' => 'restaurant-mini-stat--cleaning'],
                            ['label' => 'Inactivas', 'count' => $totals['inactive'], 'tone' => 'restaurant-mini-stat--inactive'],
                        ] as $miniStat)
                            <div class="restaurant-mini-stat {{ $miniStat['tone'] }}">
                                <span>{{ $miniStat['label'] }}</span>
                                <strong>{{ $miniStat['count'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 xl:col-span-8">
            <div class="panel">
                <div class="panel-body">
                    <div class="restaurant-toolbar">
                        <div>
                            <h2 class="text-sm font-semibold">Mapa operativo de mesas</h2>
                            <p class="text-xs text-base-content/60">Vista tipo floor manager para ubicar rápido estado, zona y pedido activo.</p>
                        </div>

                        <form method="GET" class="restaurant-toolbar__filters">
                            <select name="branch_id" class="select select-bordered select-sm" onchange="this.form.submit()">
                                <option value="">Todas las sucursales</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branchFilter === $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>

                            <select name="status" class="select select-bordered select-sm" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <input
                                type="search"
                                name="q"
                                value="{{ $search }}"
                                class="input input-bordered input-sm"
                                placeholder="Buscar mesa, número o zona"
                            >
                            <button class="btn btn-outline btn-sm">Filtrar</button>

                            @if ($branchFilter || $statusFilter || $search)
                                <a href="{{ route('restaurant.tables.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
                            @endif
                        </form>
                    </div>

                    <div class="restaurant-status-pills">
                        @foreach ($statusOptions as $value => $label)
                            @php
                                $pillCount = $tableCollection->where('status', $value)->count();
                                $meta = $statusMeta[$value];
                            @endphp
                            <span class="restaurant-status-pill {{ $meta['ring'] }}">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                {{ $label }}
                                <strong>{{ $pillCount }}</strong>
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                        @forelse ($tables as $table)
                            @php
                                $meta = $statusMeta[$table->status] ?? $statusMeta[\App\Models\RestaurantTable::STATUS_INACTIVE];
                                $order = $table->activeOrder;
                            @endphp

                            <article class="restaurant-table-card {{ $meta['ring'] }}">
                                <div class="restaurant-table-card__top">
                                    <div>
                                        <div class="restaurant-table-card__eyebrow">
                                            <span>{{ $table->branch?->name ?? 'Sucursal' }}</span>
                                            <span>Mesa {{ $table->number }}</span>
                                        </div>
                                        <h3 class="restaurant-table-card__title">{{ $table->name }}</h3>
                                    </div>
                                    <span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                                </div>

                                <div class="restaurant-table-card__layout">
                                    <div class="restaurant-table-card__avatar">
                                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                                    </div>
                                    <div class="restaurant-table-card__metrics">
                                        <span><i class="fa-solid fa-users"></i> {{ $table->capacity }} puestos</span>
                                        <span><i class="fa-solid fa-location-dot"></i> {{ $table->location ?: 'Zona sin definir' }}</span>
                                        <span><i class="fa-solid fa-toggle-{{ $table->is_active ? 'on' : 'off' }}"></i> {{ $table->is_active ? 'Mesa activa' : 'Mesa inactiva' }}</span>
                                    </div>
                                </div>

                                @if ($order)
                                    <div class="restaurant-table-order">
                                        <div>
                                            <p class="restaurant-table-order__title">Pedido #{{ $order->order_number }}</p>
                                            <p class="restaurant-table-order__meta">
                                                {{ \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status }}
                                                @if ($order->customer)
                                                    · {{ $order->customer->name }}
                                                @endif
                                            </p>
                                        </div>
                                        <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-primary btn-xs">Abrir pedido</a>
                                    </div>
                                @else
                                    <div class="restaurant-table-order restaurant-table-order--empty">
                                        <p class="restaurant-table-order__meta">Sin pedido activo. Lista para operación o reasignación.</p>
                                    </div>
                                @endif

                                <details class="restaurant-table-editor">
                                    <summary>
                                        <span>Editar configuración</span>
                                        <i class="fa-solid fa-sliders"></i>
                                    </summary>

                                    <form method="POST" action="{{ route('restaurant.tables.update', $table) }}" class="mt-4 space-y-3">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="field-label">Sucursal</label>
                                            <select name="branch_id" class="select select-bordered w-full">
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}" @selected($table->branch_id === $branch->id)>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="field-label">Nombre</label>
                                                <input name="name" value="{{ $table->name }}" class="input input-bordered w-full">
                                            </div>
                                            <div>
                                                <label class="field-label">Número</label>
                                                <input name="number" value="{{ $table->number }}" class="input input-bordered w-full">
                                            </div>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="field-label">Capacidad</label>
                                                <input name="capacity" type="number" min="1" value="{{ $table->capacity }}" class="input input-bordered w-full">
                                            </div>
                                            <div>
                                                <label class="field-label">Estado</label>
                                                <select name="status" class="select select-bordered w-full">
                                                    @foreach ($statusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($table->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="field-label">Ubicación</label>
                                                <input name="location" value="{{ $table->location }}" class="input input-bordered w-full">
                                            </div>
                                            <div>
                                                <label class="field-label">Disponibilidad</label>
                                                <select name="is_active" class="select select-bordered w-full">
                                                    <option value="1" @selected($table->is_active)>Activa</option>
                                                    <option value="0" @selected(! $table->is_active)>Inactiva</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button class="btn btn-outline btn-sm w-full">Guardar cambios</button>
                                    </form>
                                </details>
                            </article>
                        @empty
                            <div class="restaurant-empty-state md:col-span-2 2xl:col-span-3">
                                <div class="restaurant-empty-state__icon">
                                    <i class="fa-solid fa-table-cells-large"></i>
                                </div>
                                <h3>No hay mesas registradas</h3>
                                <p>Crea la primera mesa para empezar a organizar salón, terraza o zonas VIP.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                {{ $tables->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
