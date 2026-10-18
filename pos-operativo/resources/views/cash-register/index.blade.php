@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Caja</h1>
                <p class="page-subtitle">Apertura, movimientos y cierre</p>
            </div>
        </div>
    </div>
    @if ($errors->has('cash_register'))
        <div class="alert alert-error mt-4">{{ $errors->first('cash_register') }}</div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="panel lg:col-span-2">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Sesion actual</h2>
                @if ($session)
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                        <div>
                            <div class="text-xs text-base-content/60">Sucursal</div>
                            <div class="font-semibold">{{ $session->branch->name }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Apertura</div>
                            <div class="font-semibold">{{ $session->opened_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Fondo inicial</div>
                            <div class="font-semibold">${{ number_format($session->opening_amount, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Entradas de efectivo</div>
                            <div class="font-semibold text-success">${{ number_format($summary['cash_in'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Salidas de efectivo</div>
                            <div class="font-semibold text-error">${{ number_format($summary['cash_out'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Efectivo esperado</div>
                            <div class="font-semibold">${{ number_format($summary['expected_amount'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Estado</div>
                            <div class="font-semibold uppercase">{{ $session->status }}</div>
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-base-content/60">No hay caja abierta.</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Abrir caja</h2>
                    <form action="{{ route('cash-register.open') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <label class="field-label">Sucursal</label>
                        <select name="branch_id" class="select select-bordered w-full">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <label class="field-label">Monto inicial</label>
                        <input name="opening_amount" type="number" step="0.01" min="0" class="input input-bordered w-full" placeholder="Monto inicial">
                        <button class="btn btn-success w-full">Abrir</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Movimiento</h2>
                    <form action="{{ route('cash-register.movement') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                        <label class="field-label">Tipo</label>
                        <select name="type" class="select select-bordered w-full">
                            <option value="IN">Entrada</option>
                            <option value="OUT">Salida</option>
                        </select>
                        <label class="field-label">Clase de movimiento</label>
                        <select name="category" class="select select-bordered w-full">
                            <option value="cash_float">Fondo o cambio</option>
                            <option value="cash_withdrawal">Retiro / consignación</option>
                            <option value="manual_adjustment">Ajuste manual</option>
                        </select>
                        <label class="field-label">Monto</label>
                        <input name="amount" type="number" step="0.01" min="0.01" class="input input-bordered w-full" placeholder="Monto">
                        <label class="field-label">Motivo</label>
                        <input name="reason" class="input input-bordered w-full" placeholder="Motivo">
                        <button class="btn btn-primary w-full">Registrar</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Cerrar caja</h2>
                    @if ($session)
                        <p class="mt-2 text-xs text-base-content/60">Efectivo esperado: ${{ number_format($summary['expected_amount'], 2) }}</p>
                    @endif
                    <form action="{{ route('cash-register.close') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                        <label class="field-label">Monto contado</label>
                        <input name="closing_amount" type="number" step="0.01" min="0" class="input input-bordered w-full" placeholder="Monto contado">
                        <label class="field-label">Motivo de diferencia</label>
                        <textarea name="difference_reason" class="textarea textarea-bordered w-full" rows="2" placeholder="Obligatorio si hay sobrante o faltante"></textarea>
                        <button class="btn btn-danger w-full">Cerrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($session)
        <div class="mt-6 panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Movimientos de la sesión</h2>
                @if ($summary['movements']->isEmpty())
                    <p class="mt-3 text-sm text-base-content/60">Aún no hay movimientos registrados.</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="table table-sm">
                            <thead><tr><th>Hora</th><th>Tipo</th><th>Clase</th><th>Motivo</th><th>Usuario</th><th class="text-right">Valor</th></tr></thead>
                            <tbody>
                                @foreach ($summary['movements'] as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('H:i') }}</td>
                                        <td><span class="badge {{ $movement->type === 'IN' ? 'badge-success' : 'badge-error' }}">{{ $movement->type === 'IN' ? 'Entrada' : 'Salida' }}</span></td>
                                        <td>{{ match($movement->category) {
                                            'sale_cash' => 'Venta en efectivo',
                                            'cash_collection' => 'Abono de cartera',
                                            'cash_collection_reversal' => 'Anulación de abono',
                                            'refund_cash' => 'Reembolso',
                                            'cash_expense' => 'Gasto',
                                            'payable_cash' => 'Pago a proveedor',
                                            'payable_cash_reversal' => 'Anulación pago a proveedor',
                                            'cash_withdrawal' => 'Retiro / consignación',
                                            'cash_float' => 'Fondo o cambio',
                                            'manual_adjustment' => 'Ajuste manual',
                                            default => 'Movimiento anterior',
                                        } }}</td>
                                        <td>{{ $movement->reason }}</td>
                                        <td>{{ $movement->user?->name ?? 'Sistema' }}</td>
                                        <td class="text-right {{ $movement->type === 'IN' ? 'text-success' : 'text-error' }}">{{ $movement->type === 'IN' ? '+' : '-' }}${{ number_format($movement->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($closedSessions->isNotEmpty())
        <div class="mt-6 panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Últimos cierres</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th>Fecha</th><th>Esperado</th><th>Contado</th><th>Diferencia</th><th>Motivo</th></tr></thead>
                        <tbody>@foreach ($closedSessions as $closedSession)<tr>
                            <td>{{ $closedSession->closed_at?->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format($closedSession->expected_amount, 2) }}</td>
                            <td>${{ number_format($closedSession->closing_amount, 2) }}</td>
                            <td class="{{ (float) $closedSession->difference === 0.0 ? '' : 'text-error' }}">${{ number_format($closedSession->difference, 2) }}</td>
                            <td>{{ $closedSession->difference_reason ?: '—' }}</td>
                        </tr>@endforeach</tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
