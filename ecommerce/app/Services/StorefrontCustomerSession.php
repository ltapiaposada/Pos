<?php

namespace App\Services;

class StorefrontCustomerSession
{
    /** @param array<string, mixed> $customer */
    public function put(int $companyId, array $customer): void
    {
        session([$this->key($companyId) => [
            'external_customer_uuid' => $customer['external_customer_uuid'],
            'name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'] ?? null,
            'address' => $customer['address'] ?? null,
        ]]);
    }

    /** @return array<string, mixed>|null */
    public function get(int $companyId): ?array
    {
        $customer = session($this->key($companyId));

        return is_array($customer) && ! empty($customer['external_customer_uuid']) ? $customer : null;
    }

    public function forget(int $companyId): void
    {
        session()->forget($this->key($companyId));
    }

    private function key(int $companyId): string
    {
        return 'ecommerce.contact.'.$companyId;
    }
}
