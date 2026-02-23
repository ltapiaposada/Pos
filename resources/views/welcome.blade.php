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
            $logoUrl = null;
        @endphp
        <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-2">
                <section class="card border border-base-200/70 bg-base-100/85 shadow-[0_28px_50px_-34px_rgba(15,23,42,0.75)] backdrop-blur page-enter">
                    <div class="card-body p-7 sm:p-9">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 ring-1 ring-primary/20">
                                <x-application-logo :logo-url="$logoUrl" class="h-8 w-8 object-contain" />
                            </div>
                        </div>
                        <span class="chip">Punto de Venta</span>
                        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-base-content sm:text-4xl">
                            Opera tu negocio con un panel moderno y rapido.
                        </h1>
                        <p class="mt-4 text-sm leading-relaxed text-base-content/70">
                            Controla ventas, caja, inventario, clientes y reportes desde una sola interfaz moderna y rapida.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesion</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-success">Crear cuenta</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 page-enter">
                    <article class="panel">
                        <div class="panel-body">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/60">Ventas y caja</h2>
                            <p class="mt-2 text-sm text-base-content/70">Flujo de venta agil, multiples metodos de pago y control de arqueo diario.</p>
                        </div>
                    </article>
                    <article class="panel">
                        <div class="panel-body">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/60">Inventario inteligente</h2>
                            <p class="mt-2 text-sm text-base-content/70">Visualiza stock por sucursal, ajusta existencias y revisa movimientos en tiempo real.</p>
                        </div>
                    </article>
                    <article class="panel">
                        <div class="panel-body">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/60">Reportes ejecutivos</h2>
                            <p class="mt-2 text-sm text-base-content/70">Consulta ventas por fecha, cajero, producto y metodo de pago para tomar decisiones.</p>
                        </div>
                    </article>
                </section>
            </div>
        </main>
    </body>
</html>
