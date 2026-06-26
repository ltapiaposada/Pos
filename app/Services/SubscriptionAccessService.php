<?php

namespace App\Services;

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

    private function resolveSubscription(Company $company): ?CompanySubscription
    {
        $selectedPlan = request()?->session()?->get('active_subscription_plan_type');
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
}
