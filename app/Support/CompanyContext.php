<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanySubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyContext
{
    public const SERVICE_POS = 'pos';
    public const SERVICE_RESTAURANT = 'restaurant';
    public const SERVICE_OPTIC = 'optic';

    public static function user()
    {
        return Auth::user();
    }

    public static function isSystemAdmin(): bool
    {
        $user = static::user();

        return (bool) ($user && method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin());
    }

    public static function authenticatedCompanyId(): ?int
    {
        $user = static::user();

        if (! $user || ! $user->company_id) {
            return null;
        }

        return (int) $user->company_id;
    }

    public static function publicCompanyId(?Request $request = null): ?int
    {
        $request ??= request();

        if (! $request) {
            return null;
        }

        $companyId = $request->attributes->get('public_company_id');

        return is_numeric($companyId) ? (int) $companyId : null;
    }

    public static function publicCompany(?Request $request = null): ?Company
    {
        $request ??= request();

        if (! $request) {
            return null;
        }

        $company = $request->attributes->get('public_company');

        return $company instanceof Company ? $company : null;
    }

    public static function resolvePublicCompanyFromRequest(?Request $request = null): ?Company
    {
        $request ??= request();

        if (! $request) {
            return null;
        }

        $resolved = static::publicCompany($request);
        if ($resolved) {
            return $resolved;
        }

        $host = trim(mb_strtolower($request->getHost()));
        if ($host === '') {
            return null;
        }

        return Company::withoutGlobalScopes()
            ->where('status', Company::STATUS_ACTIVE)
            ->where('domain', $host)
            ->first();
    }

    public static function activeSubscriptionPlanType(?Company $company = null): ?string
    {
        $user = static::user();
        if (! $user) {
            return null;
        }

        $session = request()?->session();
        $selectedPlan = $session?->get('active_subscription_plan_type');
        if ($selectedPlan) {
            return (string) $selectedPlan;
        }

        $company ??= $user->company;
        if (! $company) {
            return null;
        }

        $subscription = $company->subscriptions()
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
            ->orderByDesc('start_date')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();

        return $subscription?->plan_type ?? $company->companyType?->slug;
    }

    public static function activeService(?Company $company = null): ?string
    {
        $service = static::activeSubscriptionPlanType($company);

        if (! is_string($service) || trim($service) === '') {
            return null;
        }

        return mb_strtolower(trim($service));
    }

    public static function isRestaurantService(?Company $company = null): bool
    {
        return static::activeService($company) === static::SERVICE_RESTAURANT;
    }

    public static function isPosService(?Company $company = null): bool
    {
        return static::activeService($company) === static::SERVICE_POS;
    }

    public static function isOpticService(?Company $company = null): bool
    {
        return static::activeService($company) === static::SERVICE_OPTIC;
    }

    public static function supportsClassicPos(?Company $company = null): bool
    {
        return in_array(static::activeService($company), [
            static::SERVICE_POS,
            static::SERVICE_OPTIC,
        ], true);
    }

    public static function companyIdForScopedReads(): ?int
    {
        if (static::isSystemAdmin()) {
            return null;
        }

        $publicCompanyId = static::publicCompanyId();
        if ($publicCompanyId !== null) {
            return $publicCompanyId;
        }

        $companyId = static::authenticatedCompanyId();
        if ($companyId !== null) {
            return $companyId;
        }

        if (app()->runningInConsole()) {
            return null;
        }

        return static::defaultCompanyId();
    }

    public static function companyIdForWrites(): ?int
    {
        if (static::isSystemAdmin()) {
            return null;
        }

        $publicCompanyId = static::publicCompanyId();
        if ($publicCompanyId !== null) {
            return $publicCompanyId;
        }

        return static::authenticatedCompanyId() ?? static::defaultCompanyId();
    }

    public static function defaultCompanyId(): ?int
    {
        return Company::query()->orderBy('id')->value('id');
    }

    public static function defaultBranchId(): ?int
    {
        return Branch::query()->orderBy('id')->value('id');
    }

    public static function branchBelongsToCompany(?int $branchId, ?int $companyId = null): bool
    {
        if (! $branchId) {
            return true;
        }

        $companyId ??= static::authenticatedCompanyId();

        if ($companyId === null) {
            return static::isSystemAdmin() || Branch::query()->whereKey($branchId)->exists();
        }

        return Branch::query()
            ->whereKey($branchId)
            ->where('company_id', $companyId)
            ->exists();
    }
}
