@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $routeName = request()->route()?->getName();
    $labels = [
        'dashboard' => 'Inicio',
        'pos' => 'Punto de venta',
        'sales' => 'Facturas',
        'cash-register' => 'Caja',
        'returns' => 'Devoluciones',
        'purchases' => 'Compras',
        'products' => 'Productos',
        'categories' => 'Categorias',
        'customers' => 'Contactos',
        'branches' => 'Sucursales',
        'inventory' => 'Inventario',
        'reports' => 'Reportes',
        'settings' => 'Configuracion',
        'security' => 'Seguridad',
        'users' => 'Usuarios',
        'roles' => 'Roles y permisos',
        'accounting' => 'Contabilidad',
        'accounts' => 'Plan de cuentas',
        'entries' => 'Libro diario',
        'movements' => 'Movimientos',
        'expenses' => 'Gastos',
        'receivables' => 'Cuentas por cobrar',
        'payables' => 'Cuentas por pagar',
        'opening-balances' => 'Saldos iniciales',
        'trial-balance' => 'Balance de prueba',
        'income-statement' => 'Estado de resultados',
        'balance-sheet' => 'Balance general',
        'close-period' => 'Cierre de periodo',
        'create' => 'Nuevo',
        'edit' => 'Editar',
        'show' => 'Detalle',
        'form' => 'Formulario',
        'ticket' => 'Ticket',
    ];

    $crumbs = [];
    if ($routeName) {
        $parts = explode('.', $routeName);
        $accumulated = [];

        foreach ($parts as $index => $part) {
            $accumulated[] = $part;
            $candidateRoute = implode('.', $accumulated);
            $isLast = $index === count($parts) - 1;

            $url = null;
            if (! $isLast) {
                if (Route::has($candidateRoute)) {
                    $url = route($candidateRoute);
                } elseif (Route::has($candidateRoute.'.index')) {
                    $url = route($candidateRoute.'.index');
                } elseif ($index === 0 && Route::has($part.'.index')) {
                    $url = route($part.'.index');
                }
            }

            $crumbs[] = [
                'label' => $labels[$part] ?? Str::title(str_replace('-', ' ', $part)),
                'url' => $url,
                'active' => $isLast,
            ];
        }
    }
@endphp

@if (! empty($crumbs))
    <nav aria-label="Miga de pan" class="mb-3">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <ol class="flex flex-wrap items-center gap-2 text-base-content/70">
            @foreach ($crumbs as $crumb)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)
                        <span class="text-base-content/40" aria-hidden="true">›</span>
                    @endif

                    @if ($crumb['url'] && ! $crumb['active'])
                        <a
                            href="{{ $crumb['url'] }}"
                            class="rounded-full border border-base-300 bg-base-100 px-3 py-1 font-medium transition hover:border-primary/40 hover:bg-base-200"
                        >
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        <span
                            class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 font-semibold text-base-content {{ $crumb['active'] ? 'pointer-events-none opacity-80' : '' }}"
                            @if ($crumb['active']) aria-disabled="true" @endif
                        >
                            {{ $crumb['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
            </ol>
        </div>
    </nav>
@endif
