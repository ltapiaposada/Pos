<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_adjustment_requires_permission(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->first();
        $this->actingAs($user);

        $response = $this->post(route('inventory.adjust'), [
            'branch_id' => $user->branch_id,
            'product_id' => 1,
            'type' => 'IN',
            'quantity' => 5,
        ]);

        $response->assertForbidden();
    }
}
