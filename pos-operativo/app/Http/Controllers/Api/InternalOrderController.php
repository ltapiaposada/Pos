<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Services\EcommerceEventPublisher;
use App\Services\EcommerceOrderService;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InternalOrderController extends Controller
{
    public function store(Request $request, EcommerceOrderService $orders, EcommerceEventPublisher $events): JsonResponse
    {
        $data = $request->validate([
            'external_order_uuid' => ['required', 'uuid'],
            'tenant_uuid' => ['required', 'uuid'],
            'customer' => ['required', 'array'],
            'customer.external_customer_uuid' => ['required', 'uuid'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:32'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = Sale::withoutGlobalScopes()->where('external_order_uuid', $data['external_order_uuid'])->first();
        if ($existing) {
            return $this->saleResponse($existing);
        }

        $company = Company::withoutGlobalScopes()->where('tenant_uuid', $data['tenant_uuid'])->firstOrFail();
        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 402, 'El servicio POS no está habilitado o su suscripción no está pagada.');
        $request->attributes->set('public_company_id', $company->id);
        $request->attributes->set('public_company', $company);

        $sale = DB::transaction(function () use ($data, $company, $orders): Sale {
            $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $company->id, 'external_customer_uuid' => $data['customer']['external_customer_uuid']],
                [
                    'name' => $data['customer']['name'],
                    'email' => $data['customer']['email'] ?? null,
                    'phone' => $data['customer']['phone'] ?? null,
                    'address' => $data['customer']['address'] ?? null,
                    'is_active' => true,
                ]
            );
            $customer->fill([
                'name' => $data['customer']['name'],
                'email' => $data['customer']['email'] ?? $customer->email,
                'phone' => $data['customer']['phone'] ?? $customer->phone,
                'address' => $data['customer']['address'] ?? $customer->address,
            ])->save();

            $integrationUser = User::withoutGlobalScopes()->where('company_id', $company->id)->orderBy('id')->firstOrFail();

            return $orders->createOrder(
                cartItems: $data['items'],
                customerId: $customer->id,
                userId: $integrationUser->id,
                paymentMethod: $data['payment_method'] ?? 'transfer',
                paymentReference: $data['payment_reference'] ?? null,
                deliveryAddress: $data['delivery_address'] ?? null,
                couponCode: $data['coupon_code'] ?? null,
                customerNote: $data['customer_note'] ?? null,
                externalOrderUuid: $data['external_order_uuid'],
            );
        });

        $eventUuid = $this->recordEvent('pos.order.created', ['external_order_uuid' => $sale->external_order_uuid, 'status' => $sale->status]);
        $events->publish($eventUuid);

        return $this->saleResponse($sale, 201);
    }

    public function updateStatus(Request $request, string $externalOrderUuid, EcommerceEventPublisher $events): JsonResponse
    {
        $data = $request->validate(['status' => ['required', 'in:pending,processing,shipped,delivered,cancelled']]);
        $sale = Sale::withoutGlobalScopes()->where('external_order_uuid', $externalOrderUuid)->firstOrFail();
        $company = Company::withoutGlobalScopes()->findOrFail($sale->company_id);
        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 402, 'El servicio POS no está habilitado o su suscripción no está pagada.');
        $sale->update(['status' => $data['status']]);

        $eventUuid = $this->recordEvent('pos.order.status_changed', ['external_order_uuid' => $sale->external_order_uuid, 'status' => $sale->status]);
        $events->publish($eventUuid);

        return $this->saleResponse($sale);
    }

    private function recordEvent(string $type, array $payload): string
    {
        $eventUuid = (string) Str::uuid();

        DB::table('integration_outbox_events')->insert([
            'event_uuid' => $eventUuid,
            'event_type' => $type,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $eventUuid;
    }

    private function saleResponse(Sale $sale, int $status = 200): JsonResponse
    {
        return response()->json([
            'external_order_uuid' => $sale->external_order_uuid,
            'pos_sale_id' => $sale->id,
            'status' => $sale->status,
            'total' => $sale->total,
            'currency' => $sale->currency,
        ], $status);
    }
}
