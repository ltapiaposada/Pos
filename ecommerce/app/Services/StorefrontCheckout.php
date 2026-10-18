<?php

namespace App\Services;

use App\Models\AdministrativeTenant;
use App\Models\CheckoutOrder;
use Illuminate\Support\Str;

class StorefrontCheckout
{
    public function __construct(private readonly PosApiClient $pos) {}

    /** @param array<string, mixed> $data */
    public function submit(AdministrativeTenant $company, array $data, StorefrontCart $cart): CheckoutOrder
    {
        $items = $cart->items($company->id);
        abort_if($items->isEmpty(), 422, 'El carrito está vacío.');

        $externalOrderUuid = (string) Str::uuid();
        $payload = [
            'tenant_uuid' => $company->tenant_uuid,
            'external_order_uuid' => $externalOrderUuid,
            'customer' => [
                'external_customer_uuid' => $cart->customerUuid($company->id),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ],
            'items' => $items->map(fn (array $item) => [
                'product_id' => $item['pos_product_id'],
                'quantity' => $item['quantity'],
            ])->values()->all(),
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'delivery_address' => $data['address'] ?? null,
            'coupon_code' => $data['coupon_code'] ?? null,
            'customer_note' => $data['customer_note'] ?? null,
        ];

        $order = CheckoutOrder::create([
            'company_id' => $company->id,
            'external_order_uuid' => $externalOrderUuid,
            'external_customer_uuid' => $payload['customer']['external_customer_uuid'],
            'status' => 'pending_pos',
            'currency' => $items->first()['currency'] ?? 'COP',
            'request_payload' => $payload,
        ]);

        try {
            $response = $this->pos->createOrder($payload);
            $order->update([
                'pos_sale_id' => $response['pos_sale_id'] ?? null,
                'status' => $response['status'] ?? 'pending',
                'total' => $response['total'] ?? null,
                'currency' => $response['currency'] ?? $order->currency,
                'pos_response' => $response,
            ]);
            $cart->clear($company->id);
        } catch (\Throwable $exception) {
            report($exception);
            $order->update(['status' => 'rejected']);
            throw $exception;
        }

        return $order->fresh();
    }
}
