<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Punto de venta') }}</title>
        @vite(['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'])
        <style>
            .user-menu-item:hover,
            .user-menu-item:focus {
                background-color: rgba(13, 110, 253, 0.14) !important;
                color: inherit !important;
            }
        </style>
    </head>
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary app-soft-bg is-boot-loading">
        @php
            $business = \App\Models\Setting::getValue('business', []);
            $logoUrl = $business['logo_url'] ?? null;
            $businessName = $business['name'] ?? config('app.name', 'Punto de venta');
            $user = Auth::user();
            $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
            $lowStockQuery = \App\Models\Inventory::query()
                ->with(['product:id,name,sku', 'branch:id,name'])
                ->where('min_stock', '>', 0)
                ->whereColumn('stock', '<=', 'min_stock');
            $lowStockTotal = (clone $lowStockQuery)->count();
            $lowStockItems = $lowStockQuery
                ->orderByRaw('(min_stock - stock) desc')
                ->limit(10)
                ->get();
            $pendingEcommerceOrders = \App\Models\Sale::query()
                ->where('order_source', \App\Models\Sale::SOURCE_ECOMMERCE)
                ->whereNull('invoiced_at')
                ->whereNotIn('status', [
                    \App\Models\Sale::STATUS_SHIPPED,
                    \App\Models\Sale::STATUS_CANCELLED,
                ])
                ->count();
        @endphp

        <div id="route-loader" class="route-loader" aria-hidden="true">
            <div class="route-loader__backdrop"></div>
            <div class="route-loader__card" role="status" aria-live="polite" aria-label="Cargando contenido">
                <div class="route-loader__orbit"></div>
                <div class="route-loader__pulse"></div>
                <div class="route-loader__text">Cargando...</div>
            </div>
        </div>

        <div class="app-wrapper">
            <nav class="app-header navbar navbar-expand bg-body">
                <div class="container-fluid">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                                <i class="fa-solid fa-bars"></i>
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center gap-2">
                        <li class="nav-item d-none d-md-block">
                            <span class="badge text-bg-light border">Panel administrativo</span>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link position-relative px-2" data-bs-toggle="dropdown" aria-label="Alertas de inventario">
                                <i class="fa-regular fa-bell"></i>
                                @if ($lowStockTotal > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                                        {{ $lowStockTotal > 99 ? '99+' : $lowStockTotal }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0 shadow-sm" style="min-width: 22rem;">
                                <div class="dropdown-header d-flex items-center justify-between px-3 py-2 text-xs">
                                    <span class="font-semibold">Stock minimo</span>
                                    <span class="badge {{ $lowStockTotal > 0 ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $lowStockTotal }}</span>
                                </div>
                                <div class="max-h-80 overflow-auto">
                                    @forelse ($lowStockItems as $item)
                                        <a
                                            href="{{ route('inventory.index', ['branch_id' => $item->branch_id, 'q' => $item->product->name]) }}"
                                            class="dropdown-item px-3 py-2"
                                        >
                                            <div class="flex items-center justify-between gap-2 text-sm">
                                                <span class="font-semibold">{{ $item->product->name }}</span>
                                                <span class="badge text-bg-danger">
                                                    {{ number_format($item->stock, 3) }} / {{ number_format($item->min_stock, 3) }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-base-content/60">{{ $item->branch->name }} · SKU {{ $item->product->sku }}</div>
                                        </a>
                                    @empty
                                        <div class="px-3 py-3 text-sm text-base-content/60">Sin productos en minimo.</div>
                                    @endforelse
                                </div>
                                <div class="border-top p-2">
                                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm w-100">Ver inventario</a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="theme-icon-btn" id="theme-toggle" title="Cambiar tema" aria-label="Cambiar tema">
                                <i id="theme-icon-sun" class="fa-regular fa-sun d-none"></i>
                                <i id="theme-icon-moon" class="fa-regular fa-moon"></i>
                            </button>
                        </li>
                        <li class="nav-item dropdown user-menu d-flex align-items-center position-relative">
                            <button type="button" class="nav-link dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent" data-user-menu-toggle aria-expanded="false">
                                <span class="d-none d-md-inline">{{ $user->name }}</span>
                                <span class="avatar-circle">{{ $initial }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" data-user-menu style="top: calc(100% + 0.35rem); right: 0; left: auto;">
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item user-menu-item d-flex align-items-center gap-2">
                                        <i class="fa-regular fa-user"></i>
                                        <span>Mi perfil</span>
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item user-menu-item d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-right-from-bracket"></i>
                                            <span>Salir</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
                <div class="sidebar-brand">
                    <a href="{{ route('dashboard') }}" class="brand-link">
                        <x-application-logo :logo-url="$logoUrl" :size="34" class="brand-image opacity-90 shadow-sm" />
                        <span class="brand-text fw-light">{{ $businessName }}</span>
                    </a>
                </div>

                <div class="sidebar-wrapper">
                    <nav class="mt-2">
                        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                            @php
                                $salesMenuOpen = request()->routeIs('pos.*') || request()->routeIs('sales.*') || request()->routeIs('cash-register.*') || request()->routeIs('returns.*') || request()->routeIs('purchases.*');
                                $catalogMenuOpen = request()->is('products*') || request()->is('categories*') || request()->is('customers*') || request()->is('branches*');
                                $opsMenuOpen = request()->is('inventory*') || request()->routeIs('reports.*');
                                $securityMenuOpen = request()->is('security/users*') || request()->is('security/roles*') || request()->is('settings*');
                                $accountingMenuOpen = request()->is('accounting/accounts*') || request()->is('accounting/expenses*') || request()->is('accounting/receivables*') || request()->is('accounting/payables*') || request()->is('accounting/opening-balances*') || request()->is('accounting/entries*') || request()->is('accounting/income-statement*') || request()->is('accounting/close-period*');
                                $ecommerceMenuOpen = request()->is('ecommerce/orders*');
                            @endphp

                            <li class="nav-header">INICIO</li>
                            <li class="nav-item">
                                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-house"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>

                            @canany(['create_sale', 'open_cash_register', 'process_return', 'manage_purchases'])
                                <li class="nav-header">FLUJO DIARIO</li>
                                <li class="nav-item has-treeview {{ $salesMenuOpen ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $salesMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-cart-shopping"></i>
                                        <p>
                                            Ventas y caja
                                            <i class="nav-arrow fa-solid fa-angle-right right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('create_sale')
                                            <li class="nav-item">
                                                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Punto de venta</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Facturas</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('open_cash_register')
                                            <li class="nav-item">
                                                <a href="{{ route('cash-register.index') }}" class="nav-link {{ request()->routeIs('cash-register.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Caja</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('process_return')
                                            <li class="nav-item">
                                                <a href="{{ route('returns.create') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Devoluciones</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_purchases')
                                            <li class="nav-item">
                                                <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Compras</p>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany

                            @can('manage_ecommerce_orders')
                                <li class="nav-header">E-COMMERCE</li>
                                <li class="nav-item">
                                    <a href="{{ route('ecommerce-admin.orders.index') }}" class="nav-link {{ $ecommerceMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-truck-fast"></i>
                                        <p>
                                            Pedidos web
                                            @if ($pendingEcommerceOrders > 0)
                                                <span class="badge text-bg-danger ms-2">{{ $pendingEcommerceOrders > 99 ? '99+' : $pendingEcommerceOrders }}</span>
                                            @endif
                                        </p>
                                    </a>
                                </li>
                            @endcan

                            @canany(['manage_products', 'manage_categories', 'manage_customers', 'manage_branches'])
                                <li class="nav-header">GESTION COMERCIAL</li>
                                <li class="nav-item has-treeview {{ $catalogMenuOpen ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $catalogMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-boxes-stacked"></i>
                                        <p>
                                            Catalogos
                                            <i class="nav-arrow fa-solid fa-angle-right right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('manage_products')
                                            <li class="nav-item">
                                                <a href="{{ route('products.index') }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Productos</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_categories')
                                            <li class="nav-item">
                                                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Categorias</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_customers')
                                            <li class="nav-item">
                                                <a href="{{ route('customers.index') }}" class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Contactos</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_branches')
                                            <li class="nav-item">
                                                <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('branches*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Sucursales</p>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany

                            @canany(['manage_inventory', 'view_reports'])
                                <li class="nav-header">CONTROL</li>
                                <li class="nav-item has-treeview {{ $opsMenuOpen ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $opsMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-chart-line"></i>
                                        <p>
                                            Operacion y analitica
                                            <i class="nav-arrow fa-solid fa-angle-right right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('manage_inventory')
                                            <li class="nav-item">
                                                <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->is('inventory*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>
                                                        Inventario
                                                        @if ($lowStockTotal > 0)
                                                            <span class="badge text-bg-danger ms-2">{{ $lowStockTotal > 99 ? '99+' : $lowStockTotal }}</span>
                                                        @endif
                                                    </p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('view_reports')
                                            <li class="nav-item">
                                                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Reportes</p>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany

                            @can('manage_accounting')
                                <li class="nav-header">FINANZAS</li>
                                <li class="nav-item has-treeview {{ $accountingMenuOpen ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $accountingMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-book"></i>
                                        <p>
                                            Contabilidad
                                            <i class="nav-arrow fa-solid fa-angle-right right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.accounts.index') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/accounts*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Plan de cuentas</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.entries.index') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/entries*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Libro diario</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.expenses.create') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/expenses*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Registrar gasto</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.receivables.index') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/receivables*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Cuentas por cobrar</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.payables.index') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/payables*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Cuentas por pagar</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.opening-balances.form') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/opening-balances*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Saldos iniciales</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.income-statement') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/income-statement*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Estado de resultados</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('accounting.close-period.form') }}" data-full-reload="true" class="nav-link {{ request()->is('accounting/close-period*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-regular fa-circle"></i>
                                                <p>Cierre de periodo</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan

                            @canany(['manage_users', 'manage_settings'])
                                <li class="nav-header">ADMINISTRACION</li>
                                <li class="nav-item has-treeview {{ $securityMenuOpen ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $securityMenuOpen ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-users-gear"></i>
                                        <p>
                                            Seguridad y sistema
                                            <i class="nav-arrow fa-solid fa-angle-right right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('manage_users')
                                            <li class="nav-item">
                                                <a href="{{ route('security.users.index') }}" class="nav-link {{ request()->is('security/users*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Usuarios</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('security.roles.index') }}" class="nav-link {{ request()->is('security/roles*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Roles y permisos</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_settings')
                                            <li class="nav-item">
                                                <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                                                    <i class="nav-icon fa-regular fa-circle"></i>
                                                    <p>Configuracion</p>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                        </ul>
                    </nav>
                </div>
            </aside>

            <main class="app-main">
                <div class="app-content pt-3">
                    <div class="container-fluid" id="app-main-content">
                        @php
                            $currentRouteName = request()->route()?->getName();
                            $principalRoutes = [
                                'dashboard',
                                'pos.index',
                                'sales.index',
                                'cash-register.index',
                                'inventory.index',
                                'reports.index',
                                'purchases.index',
                                'accounting.accounts.index',
                                'accounting.entries.index',
                                'accounting.expenses.create',
                                'accounting.receivables.index',
                                'accounting.payables.index',
                                'accounting.opening-balances.form',
                                'accounting.income-statement',
                                'accounting.close-period.form',
                                'security.users.index',
                                'security.roles.index',
                                'settings.edit',
                                'products.index',
                                'categories.index',
                                'customers.index',
                                'branches.index',
                                'ecommerce-admin.orders.index',
                            ];
                            $isPrincipalView = in_array($currentRouteName, $principalRoutes, true);
                        @endphp
                        @if (! $isPrincipalView)
                            @include('layouts.partials.breadcrumbs')
                        @endif
                        @if (session('status'))
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    if (typeof window.showCenterAlert === 'function') {
                                        window.showCenterAlert(@json(session('status')), { type: 'success' });
                                    }
                                }, { once: true });
                            </script>
                        @endif
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

        <script>
            (function () {
                const body = document.body;
                if (!body) {
                    return;
                }

                function ensureMobileSidebarCollapsed() {
                    if (window.innerWidth < 992) {
                        body.classList.add('sidebar-collapse');
                        body.classList.remove('sidebar-open');

                        // Reapply after AdminLTE DOM updates to prevent reopening.
                        window.requestAnimationFrame(() => {
                            body.classList.add('sidebar-collapse');
                            body.classList.remove('sidebar-open');
                        });
                        setTimeout(() => {
                            body.classList.add('sidebar-collapse');
                            body.classList.remove('sidebar-open');
                        }, 60);
                    }
                }

                window.ensureMobileSidebarCollapsed = ensureMobileSidebarCollapsed;

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    ensureMobileSidebarCollapsed();
                } else {
                    document.addEventListener('DOMContentLoaded', ensureMobileSidebarCollapsed, { once: true });
                }

                window.addEventListener('resize', ensureMobileSidebarCollapsed);
            })();
        </script>
        <script>
            (function () {
                const toggle = document.querySelector('[data-user-menu-toggle]');
                const menu = document.querySelector('[data-user-menu]');

                if (!toggle || !menu) {
                    return;
                }

                function closeMenu() {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const isOpen = menu.classList.contains('show');
                    if (isOpen) {
                        closeMenu();
                    } else {
                        menu.classList.add('show');
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });

                menu.addEventListener('click', function (event) {
                    const link = event.target.closest('a[href]');
                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    closeMenu();
                    window.location.assign(link.href);
                });
            })();
        </script>
        <script>
            (function () {
                const body = document.body;
                if (!body) {
                    return;
                }

                function stopBootLoading() {
                    window.setTimeout(() => {
                        body.classList.remove('is-boot-loading');
                    }, 220);
                }

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    stopBootLoading();
                } else {
                    document.addEventListener('DOMContentLoaded', stopBootLoading, { once: true });
                }
            })();
        </script>
        <script>
            (function () {
                const key = 'pos-admin-theme';
                const root = document.documentElement;
                const body = document.body;
                const btn = document.getElementById('theme-toggle');
                const sun = document.getElementById('theme-icon-sun');
                const moon = document.getElementById('theme-icon-moon');

                function setTheme(theme) {
                    root.setAttribute('data-bs-theme', theme);
                    body.setAttribute('data-theme', theme === 'dark' ? 'posdark' : 'pos');
                    if (theme === 'dark') {
                        body.classList.add('dark-mode');
                        sun.classList.remove('d-none');
                        moon.classList.add('d-none');
                    } else {
                        body.classList.remove('dark-mode');
                        sun.classList.add('d-none');
                        moon.classList.remove('d-none');
                    }
                }

                const saved = localStorage.getItem(key) || 'light';
                setTheme(saved);

                if (btn) {
                    btn.addEventListener('click', function () {
                        const next = body.classList.contains('dark-mode') ? 'light' : 'dark';
                        localStorage.setItem(key, next);
                        setTheme(next);
                    });
                }
            })();
        </script>
        <script>
            (function () {
                const enableSoftNavigation = false;
                if (!enableSoftNavigation) {
                    return;
                }

                const content = document.getElementById('app-main-content');
                const loader = document.getElementById('route-loader');
                const sidebar = document.querySelector('.sidebar-wrapper');
                if (!content) {
                    return;
                }

                let currentRequest = null;
                let activeNavigationId = 0;

                function setLoadingState(loading) {
                    document.body.classList.toggle('is-navigating', loading);
                    if (loader) {
                        loader.setAttribute('aria-hidden', loading ? 'false' : 'true');
                    }
                    content.style.opacity = loading ? '0.65' : '1';
                    content.style.transition = 'opacity 140ms ease';
                }

                function shouldInterceptLink(link, event) {
                    if (!link || event.defaultPrevented) return false;
                    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
                    if (link.target && link.target !== '_self') return false;
                    if (link.hasAttribute('download') || link.dataset.fullReload === 'true') return false;
                    if (link.matches('[data-lte-toggle], [data-bs-toggle]')) return false;
                    if (!link.closest('.sidebar-wrapper')) return false;

                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;

                    const url = new URL(link.href, window.location.origin);
                    if (url.origin !== window.location.origin) return false;
                    if (/\/(create|edit)(\/|$)/.test(url.pathname)) return false;
                    if (/^\/(pos|sales|cash-register|purchases|returns)(\/|$)/.test(url.pathname)) return false;

                    return true;
                }

                function setupSidebarTreeToggle() {
                    const sidebarRoot = document.querySelector('.sidebar-wrapper');
                    if (!sidebarRoot || sidebarRoot.dataset.treeToggleBound === '1') {
                        return;
                    }

                    sidebarRoot.dataset.treeToggleBound = '1';
                    sidebarRoot.addEventListener('click', (event) => {
                        const toggleLink = event.target.closest('li.nav-item.has-treeview > a.nav-link');
                        if (!toggleLink) {
                            return;
                        }

                        const href = toggleLink.getAttribute('href') || '';
                        if (href !== '#') {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        if (typeof event.stopImmediatePropagation === 'function') {
                            event.stopImmediatePropagation();
                        }

                        const treeItem = toggleLink.closest('li.nav-item.has-treeview');
                        if (!treeItem) {
                            return;
                        }

                        treeItem.classList.toggle('menu-open');
                        toggleLink.classList.toggle('active', treeItem.classList.contains('menu-open'));
                    }, true);
                }

                function executeScripts(scope) {
                    const scripts = scope.querySelectorAll('script');
                    scripts.forEach((oldScript) => {
                        const script = document.createElement('script');
                        Array.from(oldScript.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
                        script.textContent = oldScript.textContent;
                        oldScript.replaceWith(script);
                    });
                }

                function updateSidebarActiveState(targetUrlString) {
                    const sidebar = document.querySelector('.sidebar-wrapper');
                    if (!sidebar) {
                        return;
                    }

                    const targetUrl = new URL(targetUrlString, window.location.origin);
                    const targetPath = targetUrl.pathname.replace(/\/+$/, '') || '/';

                    const links = Array.from(sidebar.querySelectorAll('a.nav-link[href]'))
                        .filter((link) => {
                            const href = link.getAttribute('href') || '';
                            return href && href !== '#' && !href.startsWith('javascript:');
                        });

                    links.forEach((link) => link.classList.remove('active'));
                    sidebar.querySelectorAll('li.nav-item').forEach((li) => li.classList.remove('menu-open'));

                    let bestLink = null;
                    let bestLength = -1;

                    links.forEach((link) => {
                        const url = new URL(link.href, window.location.origin);
                        if (url.origin !== window.location.origin) {
                            return;
                        }

                        const path = url.pathname.replace(/\/+$/, '') || '/';
                        const exact = path === targetPath;
                        const isPrefix = targetPath.startsWith(path + '/');
                        if (!exact && !isPrefix) {
                            return;
                        }

                        if (path.length > bestLength) {
                            bestLink = link;
                            bestLength = path.length;
                        }
                    });

                    if (!bestLink) {
                        return;
                    }

                    applySidebarActiveFromLink(bestLink, sidebar);
                }

                function applySidebarActiveFromLink(link, sidebar = null) {
                    const root = sidebar || document.querySelector('.sidebar-wrapper');
                    if (!root || !link) {
                        return;
                    }

                    root.querySelectorAll('a.nav-link').forEach((item) => item.classList.remove('active'));
                    root.querySelectorAll('li.nav-item').forEach((li) => li.classList.remove('menu-open'));

                    link.classList.add('active');

                    let node = link.closest('li.nav-item');
                    while (node) {
                        const parentTree = node.closest('li.nav-item.has-treeview');
                        if (!parentTree) {
                            break;
                        }

                        parentTree.classList.add('menu-open');
                        const parentLink = parentTree.querySelector(':scope > a.nav-link');
                        if (parentLink) {
                            parentLink.classList.add('active');
                        }

                        node = parentTree.parentElement?.closest('li.nav-item') || null;
                    }
                }

                function normalizeMenuHref(value) {
                    if (!value) return '';
                    const url = new URL(value, window.location.origin);
                    return `${url.pathname.replace(/\/+$/, '') || '/'}${url.search}`;
                }

                function updateSidebarActiveByExactUrl(targetUrlString) {
                    const sidebar = document.querySelector('.sidebar-wrapper');
                    if (!sidebar) {
                        return;
                    }

                    const targetUrl = new URL(targetUrlString, window.location.origin);
                    const targetPath = targetUrl.pathname.replace(/\/+$/, '') || '/';
                    const targetKey = `${targetPath}${targetUrl.search}`;

                    const links = Array.from(sidebar.querySelectorAll('a.nav-link[href]'))
                        .filter((link) => {
                            const href = link.getAttribute('href') || '';
                            return href && href !== '#';
                        });

                    const exact = links.find((link) => normalizeMenuHref(link.href) === targetKey);
                    if (exact) {
                        applySidebarActiveFromLink(exact, sidebar);
                        return;
                    }

                    const byPath = links.find((link) => {
                        const url = new URL(link.href, window.location.origin);
                        const path = url.pathname.replace(/\/+$/, '') || '/';
                        return path === targetPath;
                    });

                    if (byPath) {
                        applySidebarActiveFromLink(byPath, sidebar);
                    } else {
                        updateSidebarActiveState(targetUrlString);
                    }
                }

                function stabilizeSidebarActive(targetUrlString) {
                    updateSidebarActiveByExactUrl(targetUrlString);
                    window.requestAnimationFrame(() => {
                        updateSidebarActiveByExactUrl(targetUrlString);
                    });
                    window.setTimeout(() => {
                        updateSidebarActiveByExactUrl(targetUrlString);
                    }, 120);
                }

                async function navigate(url, pushState = true) {
                    activeNavigationId += 1;
                    const navigationId = activeNavigationId;
                    setLoadingState(true);

                    if (currentRequest) {
                        currentRequest.abort();
                    }
                    currentRequest = new AbortController();

                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            cache: 'no-store',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            signal: currentRequest.signal,
                        });

                        if (response.redirected) {
                            window.location.assign(response.url);
                            return;
                        }

                        if (!response.ok) {
                            window.location.assign(url);
                            return;
                        }

                        const html = await response.text();
                        if (navigationId !== activeNavigationId) {
                            return;
                        }
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextContent = doc.getElementById('app-main-content');
                        const nextSidebar = doc.querySelector('.sidebar-wrapper');
                        if (!nextContent) {
                            window.location.assign(url);
                            return;
                        }

                        content.innerHTML = nextContent.innerHTML;
                        if (sidebar && nextSidebar) {
                            sidebar.innerHTML = nextSidebar.innerHTML;
                        }
                        executeScripts(content);

                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(content);
                        }

                        if (pushState && window.location.href !== url) {
                            window.history.pushState({}, '', url);
                        }

                        if (doc.title) {
                            document.title = doc.title;
                        }

                        setupSidebarTreeToggle();
                        updateSidebarActiveByExactUrl(url);
                        window.scrollTo({ top: 0, behavior: 'auto' });
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            window.location.assign(url);
                        }
                    } finally {
                        if (navigationId === activeNavigationId) {
                            setLoadingState(false);
                        }
                    }
                }

                document.addEventListener('click', (event) => {
                    const link = event.target.closest('a[href]');
                    if (!shouldInterceptLink(link, event)) return;

                    const targetUrl = new URL(link.href, window.location.origin).toString();
                    const currentUrl = new URL(window.location.href);
                    const target = new URL(targetUrl);
                    if (target.pathname === currentUrl.pathname && target.search === currentUrl.search) return;

                    event.preventDefault();
                    navigate(targetUrl, true);
                });

                setupSidebarTreeToggle();
                updateSidebarActiveByExactUrl(window.location.href);

            })();
        </script>
    </body>
</html>



