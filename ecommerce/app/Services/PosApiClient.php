<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PosApiClient
{
    public function createOrder(array $payload): array
    {
        return $this->request()
            ->withHeader('Idempotency-Key', $payload['external_order_uuid'] ?? '')
            // El comando de pedido es idempotente, por lo que un reintento ante
            // una caída transitoria no puede crear una venta duplicada en el POS.
            ->retry(2, 500, fn (\Throwable $exception) => $exception instanceof ConnectionException)
            ->post('/api/v1/orders', $payload)
            ->throw()
            ->json();
    }

    public function catalog(string $tenantUuid): array
    {
        return $this->request()->get("/api/v1/tenants/{$tenantUuid}/catalog")->throw()->json();
    }

    public function availability(string $tenantUuid): array
    {
        return $this->request()->get("/api/v1/tenants/{$tenantUuid}/availability")->throw()->json();
    }

    /** @param array<string, mixed> $contact */
    public function registerContact(string $tenantUuid, array $contact): array
    {
        return $this->request()->post("/api/v1/tenants/{$tenantUuid}/contacts", $contact)->throw()->json('customer');
    }

    /** @return array<string, mixed> */
    public function loginContact(string $tenantUuid, string $email, string $password): array
    {
        return $this->request()->post("/api/v1/tenants/{$tenantUuid}/contacts/login", compact('email', 'password'))->throw()->json('customer');
    }

    /** @return array<string, mixed>|null */
    public function findContact(string $tenantUuid, string $email): ?array
    {
        $response = $this->request()->post("/api/v1/tenants/{$tenantUuid}/contacts/lookup", compact('email'));

        return $response->status() === 404 ? null : $response->throw()->json('customer');
    }

    /** @param array<string, mixed> $changes */
    public function updateContact(string $tenantUuid, string $externalCustomerUuid, array $changes): array
    {
        return $this->request()
            ->patch("/api/v1/tenants/{$tenantUuid}/contacts/{$externalCustomerUuid}", $changes)
            ->throw()
            ->json('customer');
    }

    public function request(): PendingRequest
    {
        return Http::baseUrl(config('integration.pos_url'))
            ->acceptJson()
            ->asJson()
            ->withHeader('X-Internal-Token', config('integration.internal_token'))
            ->connectTimeout(5)
            ->timeout(30);
    }
}
