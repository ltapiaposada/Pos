<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-base-content">Seleccionar acceso</h1>
        <p class="mt-1 text-sm text-base-content/60">
            {{ $company?->name ?? 'Empresa' }} tiene más de una suscripción activa. Elige con qué operación deseas entrar.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('subscription-context.store') }}" class="space-y-4">
        @csrf

        <div class="grid gap-3">
            @foreach ($subscriptions as $subscription)
                <label class="rounded-2xl border border-base-300/70 bg-base-100/80 p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <input
                            type="radio"
                            name="subscription_id"
                            value="{{ $subscription->id }}"
                            class="mt-1"
                            @checked(old('subscription_id', $loop->first ? $subscription->id : null) == $subscription->id)
                        >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold text-base-content">
                                    {{ \App\Models\CompanySubscription::billingPeriodOptions()[$subscription->billing_period] ?? $subscription->billing_period }}
                                    ·
                                    {{ $subscription->plan_type === 'restaurant' ? 'Restaurante' : ($subscription->plan_type === 'pos' ? 'POS' : ucfirst($subscription->plan_type)) }}
                                </div>
                                <span class="badge badge-success">Pagada</span>
                            </div>
                            <p class="mt-1 text-sm text-base-content/60">
                                Vigente del {{ $subscription->start_date?->format('d/m/Y') }} al {{ $subscription->end_date?->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="btn btn-primary">Entrar con esta suscripción</button>
        </div>
    </form>
</x-guest-layout>
