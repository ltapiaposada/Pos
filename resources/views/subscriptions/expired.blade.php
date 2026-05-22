@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Suscripcion vencida</h1>
                <p class="page-subtitle">El acceso operativo esta bloqueado hasta renovar la suscripcion de la empresa.</p>
            </div>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <div class="alert alert-danger mb-3" role="alert">
                {{ $subscriptionAccess['message'] }}
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-3">Empresa</dt>
                <dd class="col-sm-9">{{ $company?->name ?? 'Sin empresa' }}</dd>

                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">{{ $subscriptionAccess['subscription']?->status ?? 'Sin suscripcion' }}</dd>

                <dt class="col-sm-3">Vencimiento</dt>
                <dd class="col-sm-9">{{ optional($subscriptionAccess['subscription']?->end_date)->format('d/m/Y') ?? 'No definido' }}</dd>
            </dl>
        </div>
    </div>
@endsection
