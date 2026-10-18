@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $patient->name }}</h1>
                <p class="page-subtitle">Ficha del paciente y trazabilidad clinica</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('optometry.records.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">Nueva historia</a>
                <a href="{{ route('optometry.orders.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline btn-sm">Nueva orden</a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3 mt-6">
        <div class="panel">
            <div class="panel-body text-sm space-y-2">
                <h2 class="font-semibold">Datos generales</h2>
                <div><span class="text-base-content/60">Documento:</span> {{ $patient->document ?? '-' }}</div>
                <div><span class="text-base-content/60">Email:</span> {{ $patient->email ?? '-' }}</div>
                <div><span class="text-base-content/60">Telefono:</span> {{ $patient->phone ?? '-' }}</div>
                <div><span class="text-base-content/60">Nacimiento:</span> {{ optional($patient->optometryProfile?->birth_date)->format('d/m/Y') ?? '-' }}</div>
                <div><span class="text-base-content/60">Genero:</span> {{ $patient->optometryProfile?->gender ?? '-' }}</div>
                <div><span class="text-base-content/60">Ocupacion:</span> {{ $patient->optometryProfile?->occupation ?? '-' }}</div>
            </div>
        </div>

        <div class="panel lg:col-span-2">
            <div class="panel-body">
                <h2 class="font-semibold">Antecedentes</h2>
                <div class="mt-3 grid gap-3 md:grid-cols-2 text-sm">
                    <div class="surface-muted rounded-2xl p-4">
                        <p class="font-semibold">Sistemicos</p>
                        <p class="mt-2 whitespace-pre-line text-base-content/70">{{ $patient->optometryProfile?->systemic_history ?: 'Sin registro.' }}</p>
                    </div>
                    <div class="surface-muted rounded-2xl p-4">
                        <p class="font-semibold">Oculares</p>
                        <p class="mt-2 whitespace-pre-line text-base-content/70">{{ $patient->optometryProfile?->ocular_history ?: 'Sin registro.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 mt-6">
        <div class="panel">
            <div class="panel-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Historias clinicas</h2>
                    <a href="{{ route('optometry.records.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline btn-xs">Agregar</a>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse ($patient->clinicalRecords as $record)
                        <article class="surface-muted rounded-2xl p-4">
                            <p class="text-sm font-semibold">{{ $record->examined_at->format('d/m/Y H:i') }}</p>
                            <p class="mt-1 text-sm text-base-content/70 line-clamp-2">{{ $record->reason_for_consultation }}</p>
                            <div class="mt-3">
                                <a href="{{ route('optometry.records.show', $record) }}" class="btn btn-outline btn-xs">Abrir</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-base-content/60">Sin historias clinicas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Ordenes medicas</h2>
                    <a href="{{ route('optometry.orders.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline btn-xs">Agregar</a>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse ($patient->medicalOrders as $order)
                        <article class="surface-muted rounded-2xl p-4">
                            <p class="text-sm font-semibold">Orden #{{ $order->id }} · {{ $order->ordered_at->format('d/m/Y H:i') }}</p>
                            <p class="mt-1 text-xs text-base-content/60">Estado: {{ \App\Models\MedicalOrder::statusOptions()[$order->status] ?? $order->status }}</p>
                            <div class="mt-3">
                                <a href="{{ route('optometry.orders.show', $order) }}" class="btn btn-outline btn-xs">Abrir</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-base-content/60">Sin ordenes medicas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
