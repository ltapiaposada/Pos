<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Punto de venta') }}</title>

        @vite(['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-theme="pos" class="min-h-screen pos-app-bg antialiased">
        @php
            $business = \App\Models\Setting::getValue('business', []);
            $logoUrl = $business['logo_url'] ?? null;
            $businessName = $business['name'] ?? config('app.name', 'Punto de venta');
        @endphp
        <div class="min-h-screen px-4 py-10">
            <div class="mx-auto flex w-full max-w-md flex-col gap-6">
                <div class="flex items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 ring-1 ring-primary/20">
                            <x-application-logo :logo-url="$logoUrl" :size="32" class="h-8 w-8 object-contain" />
                        </div>
                        <div>
                            <div class="text-sm font-semibold tracking-tight">{{ $businessName }}</div>
                            <div class="text-xs text-base-content/55">Acceso seguro</div>
                        </div>
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="theme-toggle" title="Modo oscuro">
                        <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364-1.414 1.414M7.05 16.95l-1.414 1.414M16.95 16.95l1.414 1.414M7.05 7.05 5.636 5.636M12 7a5 5 0 100 10 5 5 0 000-10z" />
                        </svg>
                        <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                        </svg>
                    </button>
                </div>

                <div class="card border border-base-200/70 bg-base-100/90 shadow-[0_24px_45px_-34px_rgba(15,23,42,0.8)] backdrop-blur page-enter">
                    <div class="card-body p-6 sm:p-7">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function () {
                const key = 'pos-theme';
                const root = document.documentElement;
                const body = document.body;
                const btn = document.getElementById('theme-toggle');
                const sun = document.getElementById('theme-icon-sun');
                const moon = document.getElementById('theme-icon-moon');

                function setTheme(theme) {
                    root.setAttribute('data-theme', theme);
                    body.setAttribute('data-theme', theme);
                    if (theme === 'posdark') {
                        sun.classList.remove('hidden');
                        moon.classList.add('hidden');
                    } else {
                        sun.classList.add('hidden');
                        moon.classList.remove('hidden');
                    }
                }

                const saved = localStorage.getItem(key) || 'pos';
                setTheme(saved);

                if (btn) {
                    btn.addEventListener('click', function () {
                        const current = root.getAttribute('data-theme') || 'pos';
                        const next = current === 'posdark' ? 'pos' : 'posdark';
                        localStorage.setItem(key, next);
                        setTheme(next);
                    });
                }
            })();
        </script>
    </body>
</html>
