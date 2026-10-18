@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Orden medica #{{ $order->id }}</h1>
                <p class="page-subtitle">{{ $order->customer?->name }} · {{ $statusOptions[$order->status] ?? $order->status }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('optometry.orders.print', $order) }}" class="btn btn-outline btn-sm" onclick="window.open(this.href, 'medical_order_print', 'width=1024,height=768,scrollbars=yes,resizable=yes'); return false;">Imprimir</a>
                @if ($order->status === \App\Models\MedicalOrder::STATUS_ACTIVE)
                    <a href="{{ route('pos.index', ['customer_id' => $order->customer_id, 'medical_order_id' => $order->id]) }}" class="btn btn-primary btn-sm">Usar en venta</a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 mt-6">
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Formula o indicaciones</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $order->prescription_details }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Instrucciones de uso</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $order->usage_instructions ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Observaciones</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $order->observations ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body text-sm space-y-2"><h2 class="font-semibold">Trazabilidad</h2><div><span class="text-base-content/60">Fecha:</span> {{ $order->ordered_at->format('d/m/Y H:i') }}</div><div><span class="text-base-content/60">Historia:</span> {{ $order->clinicalRecord ? '#'.$order->clinicalRecord->id : 'Sin relacion' }}</div><div><span class="text-base-content/60">Venta:</span> {{ $order->sale ? '#'.$order->sale->sale_number : 'Sin usar' }}</div></div></div>
    </div>
@endsection
