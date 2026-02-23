<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => '1', 'name' => 'Activo', 'type' => 'asset', 'nature' => 'debit', 'parent' => null, 'is_postable' => false],
            ['code' => '11', 'name' => 'Disponible', 'type' => 'asset', 'nature' => 'debit', 'parent' => '1', 'is_postable' => false],
            ['code' => '1105', 'name' => 'Caja', 'type' => 'asset', 'nature' => 'debit', 'parent' => '11', 'is_postable' => true],
            ['code' => '1110', 'name' => 'Bancos', 'type' => 'asset', 'nature' => 'debit', 'parent' => '11', 'is_postable' => true],
            ['code' => '13', 'name' => 'Deudores', 'type' => 'asset', 'nature' => 'debit', 'parent' => '1', 'is_postable' => false],
            ['code' => '1305', 'name' => 'Clientes', 'type' => 'asset', 'nature' => 'debit', 'parent' => '13', 'is_postable' => true],
            ['code' => '14', 'name' => 'Inventarios', 'type' => 'asset', 'nature' => 'debit', 'parent' => '1', 'is_postable' => false],
            ['code' => '1435', 'name' => 'Mercancias no fabricadas', 'type' => 'asset', 'nature' => 'debit', 'parent' => '14', 'is_postable' => true],

            ['code' => '2', 'name' => 'Pasivo', 'type' => 'liability', 'nature' => 'credit', 'parent' => null, 'is_postable' => false],
            ['code' => '22', 'name' => 'Proveedores', 'type' => 'liability', 'nature' => 'credit', 'parent' => '2', 'is_postable' => false],
            ['code' => '2205', 'name' => 'Nacionales', 'type' => 'liability', 'nature' => 'credit', 'parent' => '22', 'is_postable' => true],
            ['code' => '24', 'name' => 'Impuestos por pagar', 'type' => 'liability', 'nature' => 'credit', 'parent' => '2', 'is_postable' => false],
            ['code' => '2408', 'name' => 'IVA por pagar', 'type' => 'liability', 'nature' => 'credit', 'parent' => '24', 'is_postable' => true],

            ['code' => '3', 'name' => 'Patrimonio', 'type' => 'equity', 'nature' => 'credit', 'parent' => null, 'is_postable' => false],
            ['code' => '31', 'name' => 'Capital social', 'type' => 'equity', 'nature' => 'credit', 'parent' => '3', 'is_postable' => true],
            ['code' => '36', 'name' => 'Resultados del ejercicio', 'type' => 'equity', 'nature' => 'credit', 'parent' => '3', 'is_postable' => false],
            ['code' => '3605', 'name' => 'Utilidad o perdida del ejercicio', 'type' => 'equity', 'nature' => 'credit', 'parent' => '36', 'is_postable' => true],

            ['code' => '4', 'name' => 'Ingresos', 'type' => 'income', 'nature' => 'credit', 'parent' => null, 'is_postable' => false],
            ['code' => '41', 'name' => 'Ingresos operacionales', 'type' => 'income', 'nature' => 'credit', 'parent' => '4', 'is_postable' => false],
            ['code' => '4135', 'name' => 'Comercio al por menor', 'type' => 'income', 'nature' => 'credit', 'parent' => '41', 'is_postable' => true],

            ['code' => '5', 'name' => 'Gastos', 'type' => 'expense', 'nature' => 'debit', 'parent' => null, 'is_postable' => false],
            ['code' => '51', 'name' => 'Administracion', 'type' => 'expense', 'nature' => 'debit', 'parent' => '5', 'is_postable' => false],
            ['code' => '5105', 'name' => 'Gastos de personal', 'type' => 'expense', 'nature' => 'debit', 'parent' => '51', 'is_postable' => true],
            ['code' => '5135', 'name' => 'Servicios', 'type' => 'expense', 'nature' => 'debit', 'parent' => '51', 'is_postable' => true],

            ['code' => '6', 'name' => 'Costo de ventas', 'type' => 'expense', 'nature' => 'debit', 'parent' => null, 'is_postable' => false],
            ['code' => '61', 'name' => 'Costo de mercancias vendidas', 'type' => 'expense', 'nature' => 'debit', 'parent' => '6', 'is_postable' => false],
            ['code' => '6135', 'name' => 'Comercio al por menor', 'type' => 'expense', 'nature' => 'debit', 'parent' => '61', 'is_postable' => true],
        ];

        foreach ($rows as $row) {
            $parentId = null;
            if ($row['parent']) {
                $parentId = AccountingAccount::query()->where('code', $row['parent'])->value('id');
            }

            AccountingAccount::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'nature' => $row['nature'],
                    'parent_account_id' => $parentId,
                    'level' => strlen($row['code']),
                    'is_postable' => $row['is_postable'],
                    'is_active' => true,
                ]
            );
        }
    }
}
