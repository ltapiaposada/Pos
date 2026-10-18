<?php

namespace App\Services;

use App\Models\AdministrativeTenant;
use App\Models\Storefront;
use Illuminate\Http\Request;

/** Contexto de una tienda pública, aislado por dominio y compañía. */
class StorefrontContext
{
    /** @return array{company: AdministrativeTenant, storefront: Storefront, preview: bool} */
    public function fromRequest(Request $request): array
    {
        $storefront = Storefront::query()
            ->where('domain', mb_strtolower($request->getHost()))
            ->where('status', 'enabled')
            ->firstOrFail();

        return $this->forCompany($storefront->company_id, false);
    }

    /** @return array{company: AdministrativeTenant, storefront: Storefront, preview: bool} */
    public function preview(int $companyId): array
    {
        return $this->forCompany($companyId, true);
    }

    /** @return array{company: AdministrativeTenant, storefront: Storefront, preview: bool} */
    private function forCompany(int $companyId, bool $preview): array
    {
        $company = AdministrativeTenant::query()
            ->whereKey($companyId)
            ->where('status', 'active')
            ->firstOrFail();

        abort_unless(
            app(AdministrativeAccessService::class)->canUseStorefront($company),
            402,
            'La tienda no está habilitada o su suscripción no está pagada.'
        );

        $storefront = Storefront::query()->where('company_id', $company->id)->first();
        abort_unless($storefront && ($preview || $storefront->status === 'enabled'), 404);

        return compact('company', 'storefront', 'preview');
    }
}
