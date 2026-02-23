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
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                        <div>
                            <div class="text-xs text-base-content/60">Sucursal</div>
                            <div class="font-semibold">{{ $session->branch->name }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Apertura</div>
                            <div class="font-semibold">{{ $session->opened_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Monto inicial</div>
                            <div class="font-semibold">${{ number_format($session->opening_amount, 2) }}</div>
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
                    <form action="{{ route('cash-register.close') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                        <label class="field-label">Monto contado</label>
                        <input name="closing_amount" type="number" step="0.01" min="0" class="input input-bordered w-full" placeholder="Monto contado">
                        <button class="btn btn-danger w-full">Cerrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
