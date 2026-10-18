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
    <body class="layout-top-nav bg-body-tertiary">
        @php
            $business = \App\Models\Setting::getValue('business', []);
            $logoUrl = $business['logo_url'] ?? null;
            $businessName = $business['name'] ?? config('app.name', 'Punto de venta');
            $user = Auth::user();
            $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
        @endphp

        <div class="wrapper">
            <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
                <div class="container">
                    <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2">
                        <x-application-logo :logo-url="$logoUrl" :size="34" class="brand-image opacity-90 shadow-sm" />
                        <span class="brand-text fw-semibold">{{ $businessName }}</span>
                    </a>

                    <ul class="navbar-nav ms-auto align-items-center gap-2">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Volver al panel</a>
                        </li>
                        <li class="nav-item dropdown d-flex align-items-center position-relative">
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

            <div class="content-wrapper bg-body-tertiary">
                <div class="content-header">
                    <div class="container">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>
                </div>

                <div class="content">
                    <div class="container pb-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
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
            })();
        </script>
    </body>
</html>



