<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Punto de venta') }} - Tienda</title>
    @vite(['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'])
    <style>
        .shop-nav .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .shop-main {
            min-height: calc(100vh - 72px);
        }
        .shop-nav .btn-outline-secondary,
        .shop-nav .btn-outline-primary {
            color: #0f172a;
        }
        .shop-nav .btn-outline-secondary:hover,
        .shop-nav .btn-outline-secondary:focus,
        .shop-nav .btn-outline-primary:hover,
        .shop-nav .btn-outline-primary:focus {
            color: #0f172a;
            background: #e2e8f0;
            border-color: #94a3b8;
        }
        .shop-cart-count {
            min-width: 1.25rem;
            text-align: center;
            font-weight: 700;
        }
        .shop-register-btn {
            font-weight: 700;
            padding-inline: .9rem;
            white-space: nowrap;
        }
        .shop-theme-toggle {
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease;
        }
        .shop-theme-toggle:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }
        body.dark-mode .shop-theme-toggle {
            background: rgba(15, 23, 42, 0.9);
            border-color: #475569;
            color: #e2e8f0;
        }
        body.dark-mode .shop-theme-toggle:hover {
            background: rgba(30, 41, 59, 0.95);
            border-color: #64748b;
        }
        body.dark-mode {
            background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
            color: #e2e8f0;
        }
        body.dark-mode .shop-main {
            background: transparent;
        }
        body.dark-mode .shop-nav {
            background: rgba(15, 23, 42, 0.94) !important;
            border-bottom-color: rgba(71, 85, 105, 0.55) !important;
        }
        body.dark-mode .shop-nav .navbar-brand {
            color: #e2e8f0;
        }
        body.dark-mode .shop-nav .btn-outline-secondary,
        body.dark-mode .shop-nav .btn-outline-primary {
            color: #e2e8f0;
            border-color: #475569;
            background: rgba(15, 23, 42, 0.7);
        }
        body.dark-mode .shop-nav .btn-outline-secondary:hover,
        body.dark-mode .shop-nav .btn-outline-secondary:focus,
        body.dark-mode .shop-nav .btn-outline-primary:hover,
        body.dark-mode .shop-nav .btn-outline-primary:focus {
            color: #f8fafc;
            background: rgba(30, 41, 59, 0.95);
            border-color: #64748b;
        }
        body.dark-mode .shop-nav .btn-primary {
            background: #2563eb;
            border-color: #1d4ed8;
        }
        body.dark-mode .shop-nav .btn-primary:hover,
        body.dark-mode .shop-nav .btn-primary:focus {
            background: #1d4ed8;
            border-color: #1e40af;
        }
        body.dark-mode .pagination .page-link {
            color: #cbd5e1;
            background-color: rgba(15, 23, 42, 0.78);
            border-color: rgba(71, 85, 105, 0.55);
        }
        body.dark-mode .pagination .page-link:hover {
            color: #f8fafc;
            background-color: rgba(30, 41, 59, 0.95);
            border-color: rgba(100, 116, 139, 0.7);
        }
        body.dark-mode .pagination .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #1d4ed8;
            color: #fff;
        }
        body.dark-mode .text-muted,
        body.dark-mode .text-secondary {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="bg-body-tertiary" data-theme="pos">
    @php
        $business = \App\Models\Setting::getValue('business', []);
        $businessName = $business['name'] ?? config('app.name', 'Tienda');
    @endphp
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shop-nav">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('shop.index') }}">{{ $businessName }}</a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="shop-theme-toggle" id="shop-theme-toggle" title="Cambiar tema" aria-label="Cambiar tema">
                    <i id="shop-theme-icon-sun" class="fa-regular fa-sun d-none"></i>
                    <i id="shop-theme-icon-moon" class="fa-regular fa-moon"></i>
                </button>
                <a href="{{ route('shop.cart') }}" class="btn btn-outline-secondary btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 7H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10" cy="19" r="1.5" fill="currentColor"/>
                        <circle cx="17" cy="19" r="1.5" fill="currentColor"/>
                    </svg>
                    <span class="shop-cart-count">{{ $cartCount ?? 0 }}</span>
                </a>
                @auth
                    @if (auth()->user()->hasRole('customer'))
                        <a href="{{ route('shop.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-box"></i>
                            <span>Mis pedidos</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Panel admin</span>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Salir</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-regular fa-user"></i>
                        <span>Iniciar</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm shop-register-btn">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Registrate</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4 shop-main">
        @if (session('status'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof window.showCenterAlert === 'function') {
                        window.showCenterAlert(@json(session('status')), { type: 'success' });
                    }
                }, { once: true });
            </script>
        @endif

        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof window.showCenterAlert === 'function') {
                        window.showCenterAlert(@json(implode(' | ', $errors->all())), { type: 'error', duration: 4200 });
                    }
                }, { once: true });
            </script>
        @endif

        @yield('content')
    </main>
    <script>
        (function () {
            const key = 'pos-shop-theme';
            const root = document.documentElement;
            const body = document.body;
            const btn = document.getElementById('shop-theme-toggle');
            const sun = document.getElementById('shop-theme-icon-sun');
            const moon = document.getElementById('shop-theme-icon-moon');

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
</body>
</html>



