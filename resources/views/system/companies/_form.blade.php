@csrf

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="panel">
            <div class="panel-header">
                <h2 class="text-sm font-semibold text-base-content/80">Datos de la empresa</h2>
            </div>
            <div class="panel-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Identificacion</label>
                        <input type="text" name="identification" value="{{ old('identification', $company->identification ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dominio publico</label>
                        <input type="text" name="domain" value="{{ old('domain', $company->domain ?? '') }}" class="form-control" placeholder="mirestaurante.adanis.com">
                        <small class="text-muted">Guarda solo el host, sin protocolo ni rutas.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de empresa</label>
                        <select name="company_type_id" class="form-select" required>
                            @foreach ($companyTypes as $type)
                                <option value="{{ $type->id }}" @selected((int) old('company_type_id', $company->company_type_id ?? 0) === (int) $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select" required>
                            @foreach (['active' => 'Activa', 'inactive' => 'Inactiva', 'blocked' => 'Bloqueada'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $company->status ?? 'active') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Direccion</label>
                        <input type="text" name="address" value="{{ old('address', $company->address ?? '') }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        @if (! isset($company))
            <div class="panel mb-4">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Administrador inicial</h2>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contrasena</label>
                        <input type="password" name="admin_password" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Confirmar contrasena</label>
                        <input type="password" name="admin_password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Suscripcion inicial</h2>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label">Plan</label>
                        <input type="text" name="subscription_plan_type" value="{{ old('subscription_plan_type', 'pos') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periodo</label>
                        <select name="subscription_billing_period" class="form-select">
                            <option value="">Sin crear ahora</option>
                            <option value="monthly" @selected(old('subscription_billing_period') === 'monthly')>Mensual</option>
                            <option value="yearly" @selected(old('subscription_billing_period', 'yearly') === 'yearly')>Anual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Inicio</label>
                        <input type="date" name="subscription_start_date" value="{{ old('subscription_start_date', now()->toDateString()) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fin</label>
                        <input type="date" name="subscription_end_date" value="{{ old('subscription_end_date', now()->addYear()->toDateString()) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="subscription_status" class="form-select">
                            <option value="active" @selected(old('subscription_status', 'active') === 'active')>Activa</option>
                            <option value="pending_payment" @selected(old('subscription_status') === 'pending_payment')>Pendiente de pago</option>
                            <option value="expired" @selected(old('subscription_status') === 'expired')>Vencida</option>
                            <option value="cancelled" @selected(old('subscription_status') === 'cancelled')>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Estado de pago</label>
                        <input type="text" name="subscription_payment_status" value="{{ old('subscription_payment_status', 'paid') }}" class="form-control">
                    </div>
                </div>
            </div>
        @else
            <div class="panel">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-base-content/80">Resumen</h2>
                </div>
                <div class="panel-body">
                    <p class="mb-2"><strong>ID:</strong> {{ $company->id }}</p>
                    <p class="mb-2"><strong>Dominio:</strong> {{ $company->domain ?: 'Sin dominio configurado' }}</p>
                    <p class="mb-2"><strong>Tipo:</strong> {{ $company->companyType?->name ?? 'Sin tipo' }}</p>
                    <p class="mb-0"><strong>Estado:</strong> {{ $company->status }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-4" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('system.companies.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ isset($company) ? 'Guardar cambios' : 'Crear empresa' }}</button>
</div>
