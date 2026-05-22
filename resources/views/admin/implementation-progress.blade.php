@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Progreso multiempresa</h1>
                <p class="page-subtitle">Seguimiento del proceso de conversion del POS a plataforma multiempresa.</p>
            </div>
            <div class="page-actions">
                <span class="chip">{{ collect($steps)->where('completed', true)->count() }}/{{ count($steps) }} completados</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-7">
            <div class="panel h-100">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Checklist de implementacion</h2>
                    <span class="chip">Estado actual</span>
                </div>
                <div class="panel-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach ($steps as $step)
                            <div class="d-flex align-items-start gap-3 rounded-4 border px-3 py-3 {{ $step['completed'] ? 'border-success-subtle bg-success-subtle' : 'border-secondary-subtle bg-body' }}">
                                <div class="fs-5 lh-1 mt-1">{{ $step['completed'] ? '☑' : '☐' }}</div>
                                <div>
                                    <div class="fw-semibold">{{ $step['label'] }}</div>
                                    <div class="text-muted small">{{ $step['completed'] ? 'Completado en la fase actual.' : 'Pendiente en siguientes fases.' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="panel h-100">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Hallazgos de la auditoria</h2>
                    <span class="chip">Base existente</span>
                </div>
                <div class="panel-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach ($findings as $finding)
                            <div class="rounded-4 border border-secondary-subtle bg-body px-3 py-3">
                                <div class="text-sm text-base-content/80">{{ $finding }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
