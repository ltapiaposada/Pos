@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Historia clinica #{{ $record->id }}</h1>
                <p class="page-subtitle">{{ $record->customer?->name }} · {{ $record->examined_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('optometry.records.print', $record) }}" class="btn btn-outline btn-sm" onclick="window.open(this.href, 'clinical_record_print', 'width=1024,height=768,scrollbars=yes,resizable=yes'); return false;">Imprimir</a>
                <a href="{{ route('optometry.orders.create', ['clinical_record_id' => $record->id]) }}" class="btn btn-primary btn-sm">Generar orden</a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 mt-6">
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Motivo de consulta</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->reason_for_consultation }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Diagnostico</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->diagnosis ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Antecedentes medicos</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->medical_history ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Antecedentes oculares</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->ocular_history ?: 'Sin registro.' }}</p></div></div>
        <div class="panel lg:col-span-2"><div class="panel-body"><h2 class="font-semibold">Examen</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->examination ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Conducta o plan</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->treatment_plan ?: 'Sin registro.' }}</p></div></div>
        <div class="panel"><div class="panel-body"><h2 class="font-semibold">Observaciones</h2><p class="mt-3 whitespace-pre-line text-sm">{{ $record->observations ?: 'Sin registro.' }}</p></div></div>
    </div>
@endsection
