<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];
        $now = now();
        $productIds = Product::query()->pluck('id');
        $branchIds = Branch::query()->pluck('id');

        foreach ($branchIds as $branchId) {
            foreach ($productIds as $productId) {
                $rows[] = [
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'stock' => 100,
                    'min_stock' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($rows as $row) {
            Inventory::query()->updateOrCreate(
                [
                    'branch_id' => $row['branch_id'],
                    'product_id' => $row['product_id'],
                ],
                [
                    'stock' => $row['stock'],
                    'min_stock' => $row['min_stock'],
                ]
            );
        }
    }
}
