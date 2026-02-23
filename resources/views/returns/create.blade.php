@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Devolucion</h1>
                <p class="page-subtitle">Registra devoluciones con trazabilidad</p>
            </div>
        </div>
    </div>

    <form action="{{ route('returns.store') }}" method="POST" class="mt-6 panel">
        @csrf
        <div class="panel-body">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="field-label">ID Venta</label>
                    <input name="sale_id" value="{{ old('sale_id', $sale?->id) }}" class="input input-bordered w-full" required>
                    @error('sale_id')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Motivo</label>
                    <input name="reason" value="{{ old('reason') }}" class="input input-bordered w-full">
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-sm font-semibold">Items</h2>
                <div class="mt-3 space-y-2">
                    @if ($sale)
                        @foreach ($sale->items as $item)
                            @php
                                $returnedQty = (float) ($returnedByProduct->get($item->product_id) ?? 0);
                                $availableQty = max(0, (float) $item->quantity - $returnedQty);
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="flex-1 text-sm">
                                    <div>{{ $item->product_name }}</div>
                                    <div class="text-xs text-base-content/60">
                                        Vendido: {{ number_format((float) $item->quantity, 3) }} |
                                        Devuelto: {{ number_format($returnedQty, 3) }} |
                                        Disponible: {{ number_format($availableQty, 3) }}
                                    </div>
                                </div>
                                <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}">
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    max="{{ $availableQty }}"
                                    name="items[{{ $loop->index }}][quantity]"
                                    value="{{ old("items.{$loop->index}.quantity", 0) }}"
                                    class="input input-bordered input-sm w-24"
                                    @disabled($availableQty <= 0)
                                >
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-base-content/60">Ingresa el ID de la venta y luego selecciona los items manualmente.</p>
                    @endif
                </div>
                @error('items')
                    <p class="text-xs text-error mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex gap-2">
                <button class="btn btn-danger">Registrar devolucion</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </div>
    </form>
@endsection
