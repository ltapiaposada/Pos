@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Empresas</h1>
                <p class="page-subtitle">Administración global de empresas, sucursales y suscripciones.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('system.companies.create') }}" class="btn btn-primary">Nueva empresa</a>
            </div>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Usuarios</th>
                            <th>Sucursales</th>
                            <th>Suscripción</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $company->name }}</div>
                                    <div class="text-muted small">{{ $company->email ?: 'Sin correo' }}</div>
                                </td>
                                <td>{{ $company->companyType?->name ?? 'Sin tipo' }}</td>
                                <td>{{ \App\Models\Company::statusOptions()[$company->status] ?? $company->status }}</td>
                                <td>{{ $company->users_count }}</td>
                                <td>{{ $company->branches_count }}</td>
                                <td>
                                    @php($subscription = $company->effectiveSubscription ?? $company->latestSubscription)
                                    @if ($subscription)
                                        <div class="fw-semibold">{{ \App\Models\CompanySubscription::billingPeriodOptions()[$subscription->billing_period] ?? $subscription->billing_period }}</div>
                                        <div class="text-muted small">{{ $subscription->end_date?->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-muted">Sin suscripción</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('system.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">Gestionar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay empresas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
@endsection
