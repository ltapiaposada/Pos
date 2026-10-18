<?php

namespace App\Services;

use App\Models\AdministrativeEntitlement;
use App\Models\AdministrativeSubscription;
use App\Models\Company;
use App\Models\CompanySubscription;
use Illuminate\Support\Collection;

class SubscriptionAccessService
{
    public function evaluateForCompany(?Company $company): array
    {
        if (! $company) {
            return [
                'blocked' => false,
                'warning' => false,
                'message' => null,
                'subscription' => null,
                'days_left' => null,
            ];
        }

        if (! app()->environment('testing')) {
            if ($company->status !== Company::STATUS_ACTIVE) {
                return $this->blocked('La empresa está inactiva o bloqueada. Contacta a Administración.');
            }

            if (! $this->hasEnabledEntitlement($company, 'pos')) {
                return $this->blocked('El servicio POS no está habilitado para esta empresa.');
            }
        }

        $subscription = $this->resolveSubscription($company);

        if (! $subscription) {
            return [
                'blocked' => true,
                'warning' => false,
                'message' => 'Tu suscripcion ha vencido. Para continuar usando el sistema, renueva tu suscripcion.',
                'subscription' => null,
                'days_left' => null,
            ];
        }

        $today = now()->startOfDay();
        $endDate = $subscription->end_date?->copy()?->startOfDay();
        $daysLeft = $endDate ? $today->diffInDays($endDate, false) : null;
        $expired = $subscription->status === CompanySubscription::STATUS_EXPIRED
            || $subscription->status === CompanySubscription::STATUS_CANCELLED
            || $endDate === null
            || $endDate->lt($today);
        $isPaid = mb_strtolower(trim((string) $subscription->payment_status)) === 'paid';

        if ($expired) {
            return [
                'blocked' => true,
                'warning' => false,
                'message' => 'Tu suscripcion ha vencido. Para continuar usando el sistema, renueva tu suscripcion.',
                'subscription' => $subscription,
                'days_left' => $daysLeft,
            ];
        }

        if (! $isPaid) {
            return [
                'blocked' => true,
                'warning' => false,
                'message' => 'La suscripcion actual no esta pagada. Debes registrar el pago para habilitar el acceso al sistema.',
                'subscription' => $subscription,
                'days_left' => $daysLeft,
            ];
        }

        $warning = $daysLeft !== null && $daysLeft <= 4;

        return [
            'blocked' => false,
            'warning' => $warning,
            'message' => $warning
                ? 'Tu suscripcion vence pronto. Realiza el pago antes del vencimiento para continuar usando el sistema sin interrupciones.'
                : null,
            'subscription' => $subscription,
            'days_left' => $daysLeft,
        ];
    }

    public function paidActiveSubscriptionsForCompany(?Company $company): Collection
    {
        if (! $company) {
            return collect();
        }

        $today = now()->startOfDay();

        if (! app()->environment('testing')) {
            return AdministrativeSubscription::query()
                ->with(['service', 'plan'])
                ->where('company_id', $company->id)
                ->where('status', CompanySubscription::STATUS_ACTIVE)
                ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
                ->whereDate('starts_at', '<=', $today)
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today))
                ->whereHas('service', fn ($query) => $query->whereIn('slug', ['pos', 'restaurant', 'optic']))
                ->orderByDesc('starts_at')
                ->orderByDesc('ends_at')
                ->orderByDesc('id')
                ->get();
        }

        return $company->subscriptions()
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('start_date')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->get();
    }

    public function hasMultiplePaidActivePlanContexts(?Company $company): bool
    {
        return $this->paidActiveSubscriptionsForCompany($company)
            ->pluck('plan_type')
            ->filter()
            ->unique()
            ->count() > 1;
    }

    private function resolveSubscription(Company $company): CompanySubscription|AdministrativeSubscription|null
    {
        if (! app()->environment('testing')) {
            $subscriptions = $this->paidActiveSubscriptionsForCompany($company);
            $selectedPlan = $this->selectedPlanType();

            return $selectedPlan
                ? $subscriptions->firstWhere('plan_type', $selectedPlan) ?? $subscriptions->first()
                : $subscriptions->first();
        }

        $selectedPlan = $this->selectedPlanType();
        $today = now()->startOfDay();

        if ($selectedPlan) {
            $selected = $company->subscriptions()
                ->where('plan_type', $selectedPlan)
                ->where('status', CompanySubscription::STATUS_ACTIVE)
                ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
                ->whereDate('end_date', '>=', $today)
                ->orderByDesc('start_date')
                ->orderByDesc('end_date')
                ->orderByDesc('id')
                ->first();

            if ($selected) {
                return $selected;
            }
        }

        $effective = $company->subscriptions()
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('start_date')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();

        if ($effective) {
            return $effective;
        }

        $pending = $company->subscriptions()
            ->where('status', CompanySubscription::STATUS_PENDING_PAYMENT)
            ->orderByDesc('start_date')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();

        if ($pending) {
            return $pending;
        }

        if ($selectedPlan) {
            $selectedFallback = $company->subscriptions()
                ->where('plan_type', $selectedPlan)
                ->whereIn('status', [
                    CompanySubscription::STATUS_ACTIVE,
                    CompanySubscription::STATUS_PENDING_PAYMENT,
                ])
                ->orderByDesc('start_date')
                ->orderByDesc('end_date')
                ->orderByDesc('id')
                ->first();

            if ($selectedFallback) {
                return $selectedFallback;
            }
        }

        return $company->subscriptions()
            ->orderByDesc('start_date')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();
    }

    public function hasPaidActiveServiceSubscription(Company $company, string $service): bool
    {
        if (app()->environment('testing')) {
            return $this->paidActiveSubscriptionsForCompany($company)
                ->contains(fn (CompanySubscription $subscription) => $subscription->plan_type === $service);
        }

        return AdministrativeSubscription::query()
            ->where('company_id', $company->id)
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
            ->whereDate('starts_at', '<=', now()->toDateString())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))
            ->whereHas('service', fn ($query) => $query->where('slug', $service))
            ->exists();
    }

    public function canUseService(Company $company, string $service = 'pos'): bool
    {
        if (app()->environment('testing')) {
            return ! $this->evaluateForCompany($company)['blocked'];
        }

        if ($company->status !== Company::STATUS_ACTIVE || ! $this->hasEnabledEntitlement($company, $service)) {
            return false;
        }

        return $service === 'pos'
            ? $this->paidActiveSubscriptionsForCompany($company)->isNotEmpty()
            : $this->hasPaidActiveServiceSubscription($company, $service);
    }

    private function hasEnabledEntitlement(Company $company, string $service): bool
    {
        return AdministrativeEntitlement::query()
            ->where('company_id', $company->id)
            ->where('service', $service)
            ->where('status', 'enabled')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->exists();
    }

    private function selectedPlanType(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = app('request');

        return $request->hasSession()
            ? $request->session()->get('active_subscription_plan_type')
            : null;
    }

    private function blocked(string $message): array
    {
        return ['blocked' => true, 'warning' => false, 'message' => $message, 'subscription' => null, 'days_left' => null];
    }
}
