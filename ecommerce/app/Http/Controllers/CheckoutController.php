<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\CheckoutOrder;
use App\Models\AdministrativeTenant;
use App\Models\Storefront;
use App\Services\PosApiClient;
use App\Services\AdministrativeAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request, PosApiClient $pos): JsonResponse
    {
        $data = $request->validate([
            'tenant_uuid' => ['required', 'uuid'],
            'customer' => ['required', 'array'],
            'customer.external_customer_uuid' => ['required', 'uuid'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email'],
            'customer.phone' => ['nullable', 'string', 'max:32'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant = AdministrativeTenant::where('tenant_uuid', $data['tenant_uuid'])->where('status', 'active')->firstOrFail();
        Storefront::where('company_id', $tenant->id)->where('status', 'enabled')->firstOrFail();
        abort_unless(app(AdministrativeAccessService::class)->canUseStorefront($tenant), 402, 'La tienda no está habilitada o su suscripción no está pagada.');
        $externalOrderUuid = (string) ($request->header('Idempotency-Key') ?: Str::uuid());
        abort_unless(Str::isUuid($externalOrderUuid), 422, 'Idempotency-Key debe ser UUID.');

        $order = CheckoutOrder::firstOrCreate(
            ['external_order_uuid' => $externalOrderUuid],
            [
                'company_id' => $tenant->id,
                'external_customer_uuid' => $data['customer']['external_customer_uuid'],
                'request_payload' => $data,
            ]
        );

        if ($order->status === 'pending_pos') {
            $catalogProducts = CatalogProduct::where('company_id', $tenant->id)
                ->whereIn('id', collect($data['items'])->pluck('product_id'))
                ->get()
                ->keyBy('id');
            abort_unless($catalogProducts->count() === count($data['items']), 422, 'Un producto no pertenece a esta tienda.');

            $payload = $data;
            $payload['external_order_uuid'] = $externalOrderUuid;
            $payload['items'] = collect($data['items'])->map(fn (array $item) => [
                'product_id' => $catalogProducts->get($item['product_id'])->pos_product_id,
                'quantity' => $item['quantity'],
            ])->values()->all();

            $response = $pos->createOrder($payload);
            $order->update([
                'pos_sale_id' => $response['pos_sale_id'],
                'status' => $response['status'],
                'total' => $response['total'],
                'currency' => $response['currency'],
                'pos_response' => $response,
            ]);
        }

        return response()->json($order->fresh()->only(['external_order_uuid', 'pos_sale_id', 'status', 'total', 'currency']), 201);
    }
}
