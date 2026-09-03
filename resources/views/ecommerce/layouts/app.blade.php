<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Punto de venta') }} - Tienda</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,450;9..144,560;9..144,650&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --shop-ink: #15181D;
            --shop-ink-soft: #585F6B;
            --shop-paper: #FFFFFF;
            --shop-paper-soft: #F6F5F1;
            --shop-line: #E4E1D9;
            --shop-forest: #1E4A3D;
            --shop-forest-dark: #123329;
            --shop-forest-tint: #E5ECE8;
            --shop-gold: #A9781F;
            --shop-gold-tint: #F1E7D2;
            --shop-clay: #9C4B34;
            --shop-clay-tint: #F1E1DA;
            --shop-slate: #3E4F63;
            --shop-slate-tint: #E4E8ED;
            --shop-radius-s: 8px;
            --shop-radius-m: 14px;
        }

        body[data-theme="shop"] {
            background: var(--shop-paper);
            color: var(--shop-ink);
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body[data-shop-mode="dark"] {
            --shop-ink: #F4F5F2;
            --shop-ink-soft: #B9C0C5;
            --shop-paper: #1D2329;
            --shop-paper-soft: #151A1F;
            --shop-line: #3A4249;
            --shop-forest: #2F6656;
            --shop-forest-dark: #1E4A3D;
            --shop-forest-tint: #25443B;
            --shop-gold-tint: #493C24;
            --shop-clay-tint: #4A302A;
            --shop-slate-tint: #303B47;
        }

        body[data-theme="shop"] h1,
        body[data-theme="shop"] h2,
        body[data-theme="shop"] h3,
        body[data-theme="shop"] .shop-serif {
            font-family: "Fraunces", Georgia, serif;
        }

        body[data-theme="shop"] a {
            color: inherit;
            text-decoration: none;
        }

        .shop-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--shop-paper);
            border-bottom: 1px solid var(--shop-line);
        }

        .shop-header__inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .shop-brand {
            min-width: 0;
            display: inline-flex;
            align-items: center;
            gap: 11px;
        }

        .shop-brand__mark {
            width: 32px;
            height: 32px;
            flex: 0 0 auto;
        }

        .shop-brand__copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .shop-brand__eyebrow {
            color: var(--shop-ink-soft);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .shop-brand__name {
            margin-top: 1px;
            color: var(--shop-ink);
            font-family: "Fraunces", Georgia, serif;
            font-size: 18px;
            font-weight: 560;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: min(42vw, 320px);
        }

        .shop-main-nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .shop-main-nav a {
            padding: 6px 0;
            border-bottom: 1.5px solid transparent;
            color: var(--shop-ink-soft);
            font-size: 14px;
            font-weight: 500;
        }

        .shop-main-nav a:hover,
        .shop-main-nav a.active {
            color: var(--shop-ink);
            border-color: var(--shop-forest);
        }

        .shop-header-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 0 0 auto;
        }

        .shop-icon-btn {
            position: relative;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--shop-ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .shop-icon-btn:hover,
        .shop-icon-btn:focus {
            background: var(--shop-paper-soft);
            color: var(--shop-ink);
        }

        .shop-theme-icon--hidden { display: none; }

        .shop-cart-count {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 15px;
            height: 15px;
            border-radius: 999px;
            background: var(--shop-forest);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9.5px;
            font-weight: 800;
        }

        .shop-btn {
            border-radius: 6px;
            padding: 9px 18px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .shop-btn--ghost {
            border: 1px solid var(--shop-line);
            background: var(--shop-paper);
            color: var(--shop-ink);
        }

        .shop-btn--ghost:hover,
        .shop-btn--ghost:focus {
            border-color: var(--shop-ink);
            color: var(--shop-ink);
        }

        .shop-btn--solid {
            border: 1px solid var(--shop-forest);
            background: var(--shop-forest);
            color: #fff !important;
        }

        .shop-btn--solid span,
        .shop-btn--solid i {
            color: #fff !important;
        }

        .shop-btn--solid:hover,
        .shop-btn--solid:focus {
            border-color: var(--shop-forest-dark);
            background: var(--shop-forest-dark);
            color: #fff;
        }

        .shop-main {
            min-height: calc(100vh - 75px);
        }

        .shop-page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 40px 48px 64px;
        }

        .shop-page-header {
            margin-bottom: 32px;
            padding: 32px;
            border-radius: var(--shop-radius-m);
            background: var(--shop-forest);
            color: #fff;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 24px;
        }

        .shop-page-header__eyebrow { color: #B9D0C4; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .shop-page-header h1 { max-width: 16ch; margin: 8px 0 0; color: #fff; font-size: 32px; font-weight: 560; line-height: 1.12; }
        .shop-page-header p { max-width: 48ch; margin: 9px 0 0; color: #D3E0DA; font-size: 14px; line-height: 1.55; }
        .shop-surface, .shop-summary { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); background: #fff; }
        .shop-surface { padding: 22px; }
        .shop-summary { padding: 22px; background: var(--shop-paper-soft); }
        .shop-summary h2, .shop-surface h2 { margin: 0; color: var(--shop-ink); font-family: "Fraunces", Georgia, serif; font-size: 21px; font-weight: 560; }
        .shop-action { min-height: 40px; border: 0; border-radius: 6px; background: var(--shop-ink); color: #fff !important; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 9px 15px; font: inherit; font-size: 13px; font-weight: 700; text-decoration: none; }
        .shop-action:hover, .shop-action:focus { background: var(--shop-forest-dark); color: #fff; }
        .shop-action--soft { border: 1px solid var(--shop-line); background: #fff; color: var(--shop-ink) !important; }
        .shop-action--soft:hover, .shop-action--soft:focus { border-color: var(--shop-forest); background: var(--shop-forest-tint); color: var(--shop-forest-dark) !important; }
        .shop-action--danger { background: transparent; color: var(--shop-clay) !important; padding-inline: 0; }
        .shop-action--danger:hover, .shop-action--danger:focus { background: transparent; color: #74351F !important; text-decoration: underline; }
        .shop-line-item { padding: 18px 0; border-bottom: 1px solid var(--shop-line); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; }
        .shop-line-item:first-of-type { margin-top: 8px; }.shop-line-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .shop-line-item__name { color: var(--shop-ink); font-size: 14px; font-weight: 700; }.shop-line-item__meta { margin-top: 5px; color: var(--shop-ink-soft); font-size: 12.5px; line-height: 1.5; }.shop-line-item__price { color: var(--shop-ink); font-family: "Fraunces", Georgia, serif; font-size: 17px; font-weight: 560; white-space: nowrap; }
        .shop-quantity { width: 68px; min-height: 38px; border: 1px solid var(--shop-line); border-radius: 6px; background: #fff; color: var(--shop-ink); padding: 6px; text-align: center; font: inherit; }
        .shop-item-actions { margin-top: 12px; display: flex; align-items: center; gap: 10px; }
        .shop-totals { margin-top: 18px; display: grid; gap: 11px; }.shop-total-row { display: flex; justify-content: space-between; gap: 16px; color: var(--shop-ink-soft); font-size: 14px; }.shop-total-row strong { color: var(--shop-ink); }.shop-total-row--final { margin-top: 6px; padding-top: 16px; border-top: 1px solid var(--shop-line); color: var(--shop-ink); font-family: "Fraunces", Georgia, serif; font-size: 20px; }.shop-total-row--final strong { color: var(--shop-forest-dark); }
        .shop-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-top: 24px; }.shop-field--full { grid-column: 1 / -1; }.shop-field label { display: block; margin-bottom: 7px; color: var(--shop-ink); font-size: 13px; font-weight: 700; }.shop-input { width: 100%; min-height: 42px; border: 1px solid var(--shop-line); border-radius: 6px; background: #fff; color: var(--shop-ink); padding: 9px 11px; font: inherit; font-size: 14px; }.shop-input:focus { outline: 0; border-color: var(--shop-forest); box-shadow: 0 0 0 3px rgba(30, 74, 61, .12); }.shop-note { margin-top: 8px; color: var(--shop-ink-soft); font-size: 12px; line-height: 1.45; }.shop-notice { margin-top: 18px; border: 1px solid var(--shop-gold-tint); border-radius: var(--shop-radius-s); background: var(--shop-gold-tint); color: var(--shop-ink); padding: 13px 14px; font-size: 13px; line-height: 1.5; }
        .shop-order-list { display: grid; gap: 12px; }.shop-order-card { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); background: #fff; padding: 18px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: center; }.shop-order-card__number { color: var(--shop-ink); font-family: "Fraunces", Georgia, serif; font-size: 19px; font-weight: 560; }.shop-order-card__info { margin-top: 4px; color: var(--shop-ink-soft); font-size: 13px; }.shop-status { width: fit-content; border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 700; }.shop-status--pending { background: var(--shop-gold-tint); color: #76520E; }.shop-status--processing { background: var(--shop-slate-tint); color: var(--shop-slate); }.shop-status--shipped { background: var(--shop-forest-tint); color: var(--shop-forest-dark); }.shop-status--delivered { background: var(--shop-forest-tint); color: var(--shop-forest-dark); }.shop-status--cancelled { background: var(--shop-clay-tint); color: #74351F; }

        .shop-footer {
            border-top: 1px solid var(--shop-line);
            background: var(--shop-paper-soft);
        }

        .shop-footer__inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 56px 48px 30px;
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            gap: 40px;
        }

        .shop-footer__name {
            color: var(--shop-ink);
            font-family: "Fraunces", Georgia, serif;
            font-size: 19px;
            font-weight: 560;
        }

        .shop-footer__intro {
            max-width: 30ch;
            margin: 12px 0 0;
            color: var(--shop-ink-soft);
            font-size: 13px;
            line-height: 1.6;
        }

        .shop-footer__heading {
            margin: 0 0 14px;
            color: var(--shop-ink-soft);
            font-family: "Inter", system-ui, sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .shop-footer__link {
            display: block;
            width: fit-content;
            padding: 5px 0;
            color: var(--shop-ink);
            font-size: 13.5px;
        }

        .shop-footer__link:hover,
        .shop-footer__link:focus {
            color: var(--shop-forest);
        }

        .shop-footer__bottom {
            border-top: 1px solid var(--shop-line);
            padding: 20px 48px;
            color: var(--shop-ink-soft);
            font-size: 12.5px;
            text-align: center;
        }

        body[data-theme="shop"] .pagination .page-link {
            border-color: var(--shop-line);
            color: var(--shop-ink);
        }

        body[data-theme="shop"] .pagination .page-link:hover {
            border-color: var(--shop-forest);
            color: var(--shop-forest);
            background: var(--shop-forest-tint);
        }

        body[data-theme="shop"] .pagination .page-item.active .page-link {
            background: var(--shop-forest);
            border-color: var(--shop-forest);
            color: #fff;
        }

        body[data-shop-mode="dark"] .shop-surface,
        body[data-shop-mode="dark"] .shop-product,
        body[data-shop-mode="dark"] .shop-discover__tile,
        body[data-shop-mode="dark"] .shop-summary,
        body[data-shop-mode="dark"] .shop-order-card,
        body[data-shop-mode="dark"] .shop-configurator__group,
        body[data-shop-mode="dark"] .shop-modal-close,
        body[data-shop-mode="dark"] .shop-action--soft,
        body[data-shop-mode="dark"] .shop-input,
        body[data-shop-mode="dark"] .shop-quantity {
            background: var(--shop-paper);
            color: var(--shop-ink);
        }

        body[data-shop-mode="dark"] .shop-configurator__body,
        body[data-shop-mode="dark"] .product-image-modal__body {
            background: var(--shop-paper-soft);
        }

        @media (max-width: 960px) {
            .shop-main-nav {
                display: none;
            }

            .shop-header__inner {
                padding-inline: 24px;
            }

            .shop-footer__inner {
                grid-template-columns: 1fr 1fr;
                padding-inline: 24px;
            }
        }

        @media (max-width: 640px) {
            .shop-header__inner {
                padding: 14px 20px;
                gap: 12px;
            }

            .shop-brand__name {
                max-width: 34vw;
            }

            .shop-header-actions {
                gap: 4px;
            }

            .shop-header-actions .shop-btn span {
                display: none;
            }

            .shop-header-actions .shop-btn {
                width: 38px;
                height: 38px;
                padding: 0;
                border-radius: 50%;
            }

            .shop-footer__inner {
                grid-template-columns: 1fr;
                padding: 44px 20px 24px;
                gap: 28px;
            }

            .shop-footer__bottom {
                padding: 18px 20px;
            }

            .shop-page { padding: 28px 20px 44px; }.shop-page-header { align-items: flex-start; flex-direction: column; padding: 25px 22px; }.shop-page-header h1 { font-size: 28px; }.shop-surface, .shop-summary { padding: 18px; }.shop-form-grid { grid-template-columns: 1fr; }.shop-field--full { grid-column: auto; }.shop-line-item, .shop-order-card { grid-template-columns: 1fr; gap: 10px; }
        }
    </style>
</head>
<body data-theme="shop" data-shop-mode="light">
    @php
        $publicCompany = \App\Support\CompanyContext::publicCompany();
        $business = \App\Models\Setting::getValue('business', [], $publicCompany?->id);
        $businessName = $business['name'] ?? $publicCompany?->name ?? config('app.name', 'Tienda');
    @endphp

    <header class="shop-header">
        <div class="shop-header__inner">
            <a class="shop-brand" href="{{ route('shop.index') }}" aria-label="{{ $businessName }}">
                <svg class="shop-brand__mark" viewBox="0 0 32 32" aria-hidden="true">
                    <rect x="4" y="4" width="12" height="12" rx="4" fill="#1E4A3D"/>
                    <rect x="16" y="4" width="12" height="12" rx="4" fill="#A9781F"/>
                    <rect x="4" y="16" width="12" height="12" rx="4" fill="#9C4B34"/>
                    <rect x="16" y="16" width="12" height="12" rx="4" fill="#3E4F63"/>
                </svg>
                <span class="shop-brand__copy">
                    <span class="shop-brand__eyebrow">Tienda oficial</span>
                    <span class="shop-brand__name">{{ $businessName }}</span>
                </span>
            </a>

            <nav class="shop-main-nav" aria-label="Navegacion principal">
                <a href="{{ route('shop.index') }}" class="active">Catalogo</a>
                <a href="{{ route('shop.index') }}#discover">Categorias</a>
                @auth
                    @if (auth()->user()->hasRole('customer'))
                        <a href="{{ route('shop.orders.index') }}">Mis pedidos</a>
                    @endif
                @endauth
            </nav>

            <div class="shop-header-actions">
                <a href="{{ route('shop.cart') }}" class="shop-icon-btn" aria-label="Carrito">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="9" cy="21" r="1" fill="currentColor"/>
                        <circle cx="20" cy="21" r="1" fill="currentColor"/>
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="shop-cart-count">{{ $cartCount ?? 0 }}</span>
                </a>

                <button type="button" class="shop-icon-btn" id="shop-theme-toggle" aria-label="Activar modo oscuro" title="Cambiar tema">
                    <svg id="shop-theme-sun" class="shop-theme-icon--hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <svg id="shop-theme-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                @auth
                    @if (! auth()->user()->hasRole('customer'))
                        <a href="{{ route('dashboard') }}" class="shop-btn shop-btn--ghost">
                            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                            <span>Panel admin</span>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="shop-btn shop-btn--ghost">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            <span>Salir</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="shop-btn shop-btn--ghost">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <span>Iniciar sesion</span>
                    </a>
                    <a href="{{ route('register') }}" class="shop-btn shop-btn--solid">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                        <span>Registrate</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="shop-main">
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

    <footer class="shop-footer">
        <div class="shop-footer__inner">
            <div>
                <div class="shop-footer__name">{{ $businessName }}</div>
                <p class="shop-footer__intro">Explora el catalogo, configura tus productos y consulta tus pedidos desde un solo lugar.</p>
            </div>
            <div>
                <h2 class="shop-footer__heading">Comprar</h2>
                <a href="{{ route('shop.index') }}" class="shop-footer__link">Catalogo</a>
                <a href="{{ route('shop.index') }}#discover" class="shop-footer__link">Categorias</a>
                <a href="{{ route('shop.cart') }}" class="shop-footer__link">Carrito</a>
            </div>
            <div>
                <h2 class="shop-footer__heading">Tu cuenta</h2>
                @auth
                    @if (auth()->user()->hasRole('customer'))
                        <a href="{{ route('shop.orders.index') }}" class="shop-footer__link">Mis pedidos</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="shop-footer__link border-0 bg-transparent p-0">Cerrar sesion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="shop-footer__link">Iniciar sesion</a>
                    <a href="{{ route('register') }}" class="shop-footer__link">Crear cuenta</a>
                @endauth
            </div>
        </div>
        <div class="shop-footer__bottom">{{ $businessName }} · Compra y consulta tus pedidos con tranquilidad.</div>
    </footer>
    <script>
        (function () {
            const toggle = document.getElementById('shop-theme-toggle');
            const sun = document.getElementById('shop-theme-sun');
            const moon = document.getElementById('shop-theme-moon');

            if (!toggle || !sun || !moon) {
                return;
            }

            function setTheme(mode) {
                const dark = mode === 'dark';
                document.body.setAttribute('data-shop-mode', dark ? 'dark' : 'light');
                sun.classList.toggle('shop-theme-icon--hidden', !dark);
                moon.classList.toggle('shop-theme-icon--hidden', dark);
                toggle.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
            }

            setTheme(localStorage.getItem('pos-theme') === 'posdark' ? 'dark' : 'light');
            toggle.addEventListener('click', function () {
                const dark = document.body.getAttribute('data-shop-mode') === 'dark';
                localStorage.setItem('pos-theme', dark ? 'pos' : 'posdark');
                setTheme(dark ? 'light' : 'dark');
            });
        })();
    </script>
</body>
</html>
