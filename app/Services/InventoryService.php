<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function adjust(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::query()->firstOrCreate(
                [
                    'branch_id' => $data['branch_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'stock' => 0,
                    'min_stock' => 0,
                ]
            );

            $allowNegative = (bool) (Setting::getValue('business')['allow_negative_stock'] ?? false);
            $delta = $data['type'] === 'IN' ? $data['quantity'] : -$data['quantity'];
            $newStock = $inventory->stock + $delta;

            if ($newStock < 0 && !$allowNegative) {
                throw ValidationException::withMessages([
                    'quantity' => 'No se permite stock negativo.',
                ]);
            }

            $payload = [
                'stock' => $newStock,
            ];

            if (array_key_exists('min_stock', $data) && $data['min_stock'] !== null && $data['min_stock'] !== '') {
                $payload['min_stock'] = (float) $data['min_stock'];
            }

            $inventory->update($payload);

            InventoryMovement::query()->create([
                'branch_id' => $data['branch_id'],
                'product_id' => $data['product_id'],
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'cost_price' => $data['cost_price'] ?? 0,
                'ref_type' => $data['ref_type'] ?? 'manual',
                'ref_id' => $data['ref_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $inventory;
        });
    }
}
