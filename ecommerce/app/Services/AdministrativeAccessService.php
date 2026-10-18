<?php

namespace App\Services;

use App\Models\AdministrativeEntitlement;
use App\Models\AdministrativeSubscription;
use App\Models\AdministrativeTenant;

/** Lee los permisos comerciales directamente de Administración. */
class AdministrativeAccessService
{
    public function canUseStorefront(AdministrativeTenant $company): bool
    {
        // E-commerce depende de un POS vigente, pues el inventario y las ventas
        // siguen siendo propiedad del POS.
        return $this->canUseService($company, 'ecommerce')
            && $this->canUseService($company, 'pos');
    }

    public function canUseService(AdministrativeTenant $company, string $service): bool
    {
        if ($company->status !== 'active' || ! $this->hasEnabledEntitlement($company, $service)) {
            return false;
        }

        return AdministrativeSubscription::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->whereDate('starts_at', '<=', now()->toDateString())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))
            ->whereHas('service', fn ($query) => $query->where('slug', $service))
            ->exists();
    }

    private function hasEnabledEntitlement(AdministrativeTenant $company, string $service): bool
    {
        return AdministrativeEntitlement::query()
            ->where('company_id', $company->id)
            ->where('service', $service)
            ->where('status', 'enabled')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->exists();
    }
}
