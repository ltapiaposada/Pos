@extends('layouts.admin')

@section('content')
    @php
        $subscription = $company->effectiveSubscription ?? $company->latestSubscription;
    @endphp

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Gestionar empresa</h1>
                <p class="page-subtitle">{{ $company->name }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('system.companies.update', $company) }}" method="POST" class="mt-6">
        @method('PUT')
        @include('system.companies._form')
    </form>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-5">
            <div class="panel">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Suscripción vigente</h2>
                </div>
                <div class="panel-body">
                    <form action="{{ route('system.companies.subscriptions.store', $company) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action_mode" id="subscription-action-mode" value="update_current">
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan_type" class="form-select" required>
                                @foreach ($companyTypes as $type)
                                    <option value="{{ $type->slug }}" @selected(old('plan_type', $subscription?->plan_type ?? $company->companyType?->slug ?? 'pos') === $type->slug)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Período</label>
                            <select name="billing_period" class="form-select" required>
                                @foreach (\App\Models\CompanySubscription::billingPeriodOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('billing_period', $subscription?->billing_period ?? \App\Models\CompanySubscription::PERIOD_YEARLY) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Inicio</label>
                            <input type="date" name="start_date" value="{{ old('start_date', optional($subscription?->start_date)->toDateString() ?? now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fin</label>
                            <input type="date" name="end_date" value="{{ old('end_date', optional($subscription?->end_date)->toDateString() ?? now()->addYear()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select" required>
                                @foreach (\App\Models\CompanySubscription::statusOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $subscription?->status ?? \App\Models\CompanySubscription::STATUS_ACTIVE) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado de pago</label>
                            <select name="payment_status" class="form-select" required>
                                @foreach (\App\Models\CompanySubscription::paymentStatusOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_status', $subscription?->payment_status ?? \App\Models\CompanySubscription::PAYMENT_STATUS_PAID) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Último pago</label>
                            <input type="date" name="last_payment_date" value="{{ old('last_payment_date', optional($subscription?->last_payment_date)->toDateString() ?? now()->toDateString()) }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Próximo pago</label>
                            <input type="date" name="next_payment_date" value="{{ old('next_payment_date', optional($subscription?->next_payment_date)->toDateString() ?? optional($subscription?->end_date)->toDateString() ?? now()->addYear()->toDateString()) }}" class="form-control">
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" onclick="document.getElementById('subscription-action-mode').value='update_current'">
                                Actualizar suscripción actual
                            </button>
                            <button type="submit" class="btn btn-outline-primary" onclick="document.getElementById('subscription-action-mode').value='create_new'">
                                Crear nueva suscripción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="panel">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Historial reciente</h2>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Período</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($company->subscriptions as $historyItem)
                                    <tr @class(['table-active' => $subscription && $historyItem->id === $subscription->id])>
                                        <td>{{ $historyItem->plan_type }}</td>
                                        <td>{{ \App\Models\CompanySubscription::billingPeriodOptions()[$historyItem->billing_period] ?? $historyItem->billing_period }}</td>
                                        <td>{{ $historyItem->start_date?->format('d/m/Y') }}</td>
                                        <td>{{ $historyItem->end_date?->format('d/m/Y') }}</td>
                                        <td>{{ \App\Models\CompanySubscription::statusOptions()[$historyItem->status] ?? $historyItem->status }}</td>
                                        <td>{{ \App\Models\CompanySubscription::paymentStatusOptions()[$historyItem->payment_status] ?? $historyItem->payment_status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Sin suscripciones registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
