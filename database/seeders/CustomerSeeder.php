<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->updateOrCreate(
            ['document' => 'CF'],
            [
                'name' => 'Cliente Mostrador',
                'email' => null,
                'phone' => null,
                'address' => null,
                'contact_type' => Customer::TYPE_PERSON,
                'is_active' => true,
            ]
        );

        Customer::query()->updateOrCreate(
            ['document' => 'NIT-123456'],
            [
                'name' => 'Empresa Demo',
                'email' => 'facturas@demo.com',
                'phone' => '555-0303',
                'address' => 'Zona Industrial',
                'contact_type' => Customer::TYPE_COMPANY,
                'is_active' => true,
            ]
        );

        Customer::query()->updateOrCreate(
            ['document' => 'NIT-PROV-001'],
            [
                'name' => 'Proveedor Base',
                'email' => 'compras@proveedorbase.com',
                'phone' => '555-0404',
                'address' => 'Parque Industrial',
                'contact_type' => Customer::TYPE_SUPPLIER,
                'is_active' => true,
            ]
        );
    }
}
