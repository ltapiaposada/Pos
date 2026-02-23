<?php

namespace Tests\Feature;

use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductKitItem;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_of_kit_restocks_component_inventory(): void
    {
        $this->seed();

        $cashier = User::where('email', 'cashier@pos.test')->first();
        $supervisor = User::where('email', 'supervisor@pos.test')->first();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $cashier->branch_id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_amount' => 50,
            'status' => 'open',
        ]);

        $component = Product::create([
            'name' => 'Componente retorno kit',
            'sku' => 'COMP-RET-KIT',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 2,
            'sale_price' => 4,
            'is_active' => true,
        ]);

        $kit = Product::create([
            'name' => 'Kit retorno',
            'sku' => 'KIT-RET',
            'unit' => 'unit',
            'product_type' => Product::TYPE_KIT,
            'cost_price' => 0,
            'sale_price' => 30,
            'is_active' => true,
        ]);

        ProductKitItem::create([
            'kit_product_id' => $kit->id,
            'component_product_id' => $component->id,
            'quantity' => 3,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $cashier->branch_id, 'product_id' => $component->id],
            ['stock' => 20, 'min_stock' => 0]
        );

        $this->actingAs($cashier);
        $saleResponse = $this->post(route('pos.checkout'), [
            'branch_id' => $cashier->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => $kit->id,
                    'quantity' => 2,
                    'unit_price' => 30,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 60,
                ],
            ],
        ]);
        $saleResponse->assertRedirect();

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);

        $stockAfterSale = Inventory::where('branch_id', $cashier->branch_id)
            ->where('product_id', $component->id)
            ->value('stock');
        $this->assertEquals(14.000, (float) $stockAfterSale);

        $this->actingAs($supervisor);
        $returnResponse = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Prueba devolucion kit',
            'items' => [
                [
                    'product_id' => $kit->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $returnResponse->assertRedirect();
        $this->assertGreaterThanOrEqual(2, JournalEntry::count());

        $stockAfterReturn = Inventory::where('branch_id', $cashier->branch_id)
            ->where('product_id', $component->id)
            ->value('stock');

        $this->assertEquals(17.000, (float) $stockAfterReturn);
    }

    public function test_cannot_return_more_than_sold_across_multiple_returns(): void
    {
        $this->seed();

        $cashier = User::where('email', 'cashier@pos.test')->first();
        $supervisor = User::where('email', 'supervisor@pos.test')->first();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $cashier->branch_id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_amount' => 50,
            'status' => 'open',
        ]);

        $simple = Product::create([
            'name' => 'Simple retorno parcial',
            'sku' => 'SIMPLE-RET-PARC',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 3,
            'is_active' => true,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $cashier->branch_id, 'product_id' => $simple->id],
            ['stock' => 20, 'min_stock' => 0]
        );

        $this->actingAs($cashier);
        $saleResponse = $this->post(route('pos.checkout'), [
            'branch_id' => $cashier->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => $simple->id,
                    'quantity' => 2,
                    'unit_price' => 3,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 6,
                ],
            ],
        ]);
        $saleResponse->assertSessionHasNoErrors();
        $saleResponse->assertRedirect();
        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);

        $this->actingAs($supervisor);
        $firstReturn = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Primera parcial',
            'items' => [
                [
                    'product_id' => $simple->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $firstReturn->assertSessionHasNoErrors();
        $firstReturn->assertRedirect();

        $secondReturn = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Intento excedido',
            'items' => [
                [
                    'product_id' => $simple->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $secondReturn->assertSessionHasErrors('items');
    }
}
