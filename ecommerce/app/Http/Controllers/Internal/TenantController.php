<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeTenant;
use App\Models\Storefront;
use App\Services\AdministrativeAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function provision(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            // This value is exclusively the public storefront host, e.g. empresa.tudominio.com.
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = AdministrativeTenant::where('tenant_uuid', $data['tenant_uuid'])->firstOrFail();
        abort_unless(app(AdministrativeAccessService::class)->canUseStorefront($tenant), 422, 'POS y e-commerce deben estar habilitados y pagados antes de aprovisionar la tienda.');

        if (! empty($data['domain'])) {
            Storefront::firstOrCreate(
                ['domain' => mb_strtolower($data['domain'])],
                ['company_id' => $tenant->id, 'status' => 'enabled']
            );
        }

        return response()->json(['tenant_uuid' => $tenant->tenant_uuid], 201);
    }

    public function updateStorefront(Request $request, string $tenantUuid): JsonResponse
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:enabled,suspended'],
            'flat_shipping' => ['nullable', 'numeric', 'min:0'],
            'coupons' => ['nullable', 'array'],
        ]);
        $tenant = AdministrativeTenant::where('tenant_uuid', $tenantUuid)->firstOrFail();
        $storefront = Storefront::updateOrCreate(
            ['company_id' => $tenant->id],
            [
                'domain' => mb_strtolower($data['domain']),
                'status' => $data['status'],
                'flat_shipping' => $data['flat_shipping'] ?? 0,
                'coupons' => $data['coupons'] ?? [],
            ]
        );

        return response()->json(['tenant_uuid' => $tenant->tenant_uuid, 'domain' => $storefront->domain, 'status' => $storefront->status]);
    }
}
