<?php

namespace Tests\Feature;

use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryLot;
use App\Models\InventorySerial;
use App\Models\Product;
use App\Models\ProductVariantAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtendedProductTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_units_are_selected_from_controlled_options(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('<select name="unit"', false)
            ->assertSee('value="g"', false)
            ->assertSee('Gramos (g)')
            ->assertSee('value="libra"', false)
            ->assertSee('Libras (lb)');

        foreach (['g', 'libra'] as $index => $unit) {
            $this->actingAs($admin)
                ->post(route('products.store'), [
                    'name' => "Producto unidad {$unit}",
                    'sku' => "UNIT-{$index}",
                    'unit' => $unit,
                    'product_type' => Product::TYPE_SIMPLE,
                    'cost_price' => 1,
                    'sale_price' => 2,
                    'is_active' => 1,
                    'is_visible_ecommerce' => 0,
                ])
                ->assertRedirect(route('products.index'));
        }

        $this->assertDatabaseHas('products', ['sku' => 'UNIT-0', 'unit' => 'g']);
        $this->assertDatabaseHas('products', ['sku' => 'UNIT-1', 'unit' => 'libra']);

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Producto unidad libre',
                'sku' => 'UNIT-INVALID',
                'unit' => 'cualquier texto',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 1,
                'sale_price' => 2,
                'is_active' => 1,
                'is_visible_ecommerce' => 0,
            ])
            ->assertSessionHasErrors('unit');
    }

    public function test_kit_component_field_is_an_autocomplete_by_name_sku_or_barcode(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('class="input input-bordered w-full component-search"', false)
            ->assertSee('placeholder="Buscar por nombre, SKU o codigo"', false)
            ->assertSee("candidate.barcode", false)
            ->assertSee("componentInput.value = candidate.id;", false)
            ->assertSee('id="modifier-validation-error"', false)
            ->assertSee('createRow();', false)
            ->assertSee('createModifierGroup();', false)
            ->assertSee('id="add-variant-attribute"', false)
            ->assertSee('id="open-create-variant-attribute"', false)
            ->assertSee('id="variant-attribute-modal"', false)
            ->assertSee('id="variant-attribute-name"', false)
            ->assertSee('Selecciona atributo')
            ->assertSee('variant-attributes-preview', false);
    }

    public function test_create_product_form_saves_each_non_kit_product_type(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('value="'.Product::TYPE_SIMPLE.'"', false)
            ->assertSee('value="'.Product::TYPE_SERVICE.'"', false)
            ->assertSee('value="'.Product::TYPE_DIGITAL.'"', false)
            ->assertSee('value="'.Product::TYPE_SERIALIZED.'"', false)
            ->assertSee('value="'.Product::TYPE_BATCH.'"', false)
            ->assertSee('value="'.Product::TYPE_VARIANT.'"', false);

        foreach ([
            Product::TYPE_SIMPLE => ['unit' => 'unit'],
            Product::TYPE_SERVICE => ['unit' => 'service'],
            Product::TYPE_DIGITAL => ['unit' => 'license', 'delivery_instructions' => 'Clave: DEMO-123'],
            Product::TYPE_SERIALIZED => ['unit' => 'unit'],
            Product::TYPE_BATCH => ['unit' => 'unit'],
        ] as $type => $overrides) {
            $this->actingAs($admin)
                ->post(route('products.store'), array_merge([
                    'name' => "Producto {$type}",
                    'sku' => "CREATE-{$type}",
                    'product_type' => $type,
                    'cost_price' => 1,
                    'sale_price' => 2,
                    'is_active' => 1,
                    'is_visible_ecommerce' => 0,
                ], $overrides))
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('products.index'));

            $this->assertDatabaseHas('products', array_merge([
                'sku' => "CREATE-{$type}",
                'product_type' => $type,
            ], array_intersect_key($overrides, ['unit' => true, 'delivery_instructions' => true])));
        }

        $parent = Product::query()->where('sku', 'CREATE-simple')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Variante guardada',
                'sku' => 'CREATE-variant',
                'unit' => 'unit',
                'product_type' => Product::TYPE_VARIANT,
                'parent_product_id' => $parent->id,
                'cost_price' => 1,
                'sale_price' => 2,
                'is_active' => 1,
                'is_visible_ecommerce' => 0,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'CREATE-variant',
            'product_type' => Product::TYPE_VARIANT,
            'parent_product_id' => $parent->id,
        ]);
    }

    public function test_variant_product_creation_saves_parent_template_and_new_variant_children(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Camisa',
                'sku' => 'CAMISA',
                'unit' => 'unit',
                'product_type' => Product::TYPE_VARIANT,
                'cost_price' => 10,
                'sale_price' => 20,
                'is_active' => 1,
                'is_visible_ecommerce' => 1,
                'variant_attribute_definitions' => [
                    [
                        'name' => 'Talla',
                        'values' => ['L'],
                    ],
                    [
                        'name' => 'Color',
                        'values' => ['Rojo', 'Amarillo'],
                    ],
                ],
                'variants' => [
                    [
                        'attributes' => [
                            'Talla' => 'L',
                            'Color' => 'Rojo',
                        ],
                        'sku' => 'CAMISA-L-ROJO',
                        'barcode' => 'CAM-L-R',
                        'unit' => 'unit',
                        'cost_price' => 11,
                        'sale_price' => 22,
                        'is_active' => 1,
                        'is_visible_ecommerce' => 1,
                    ],
                    [
                        'attributes' => [
                            'Talla' => 'L',
                            'Color' => 'Amarillo',
                        ],
                        'sku' => 'CAMISA-L-AMARILLO',
                        'barcode' => 'CAM-L-A',
                        'unit' => 'unit',
                        'cost_price' => 12,
                        'sale_price' => 24,
                        'is_active' => 1,
                        'is_visible_ecommerce' => 1,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $parent = Product::query()->where('sku', 'CAMISA')->firstOrFail();
        $redVariant = Product::query()->where('sku', 'CAMISA-L-ROJO')->firstOrFail();

        $this->assertSame(Product::TYPE_SIMPLE, $parent->product_type);
        $this->assertNull($parent->parent_product_id);
        $this->assertSame(Product::TYPE_VARIANT, $redVariant->product_type);
        $this->assertSame($parent->id, $redVariant->parent_product_id);
        $this->assertSame(['Talla' => 'L', 'Color' => 'Rojo'], $redVariant->variant_attributes);

        $size = ProductVariantAttribute::query()->where('name', 'Talla')->firstOrFail();
        $color = ProductVariantAttribute::query()->where('name', 'Color')->firstOrFail();
        $this->assertDatabaseHas('product_variant_attribute_values', [
            'product_variant_attribute_id' => $size->id,
            'value' => 'L',
        ]);
        $this->assertDatabaseHas('product_variant_attribute_values', [
            'product_variant_attribute_id' => $color->id,
            'value' => 'Rojo',
        ]);
        $this->assertDatabaseHas('product_variant_attribute_values', [
            'product_variant_attribute_id' => $color->id,
            'value' => 'Amarillo',
        ]);
    }

    public function test_variant_attribute_modal_persists_attribute_catalog_immediately(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('products.variant-attributes.store'), [
                'name' => 'Talla',
                'values' => ['L', 'M', 'S'],
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Talla')
            ->assertJsonPath('values.0', 'L');

        $attribute = ProductVariantAttribute::query()->where('name', 'Talla')->firstOrFail();

        $this->assertDatabaseHas('product_variant_attribute_values', [
            'product_variant_attribute_id' => $attribute->id,
            'value' => 'M',
        ]);

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('<option value="'.$attribute->id.'">Talla</option>', false);
    }

    public function test_pos_returns_variant_parent_as_selector_with_available_children(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();
        $parent = Product::query()->create([
            'name' => 'Camisa',
            'sku' => 'POS-CAMISA',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 10,
            'sale_price' => 20,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);
        $variant = Product::query()->create([
            'name' => 'Camisa - Talla L - Color Rojo',
            'sku' => 'POS-CAMISA-L-ROJO',
            'unit' => 'unit',
            'product_type' => Product::TYPE_VARIANT,
            'parent_product_id' => $parent->id,
            'variant_attributes' => ['Talla' => 'L', 'Color' => 'Rojo'],
            'cost_price' => 11,
            'sale_price' => 22,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);
        Inventory::query()->create([
            'branch_id' => $admin->branch_id,
            'product_id' => $variant->id,
            'stock' => 5,
            'min_stock' => 0,
        ]);

        $payload = $this->actingAs($admin)
            ->getJson(route('pos.products', [
                'branch_id' => $admin->branch_id,
                'q' => 'POS-CAMISA',
            ]))
            ->assertOk()
            ->json();

        $product = collect($payload)->firstWhere('id', $parent->id);
        $this->assertNotNull($product);
        $this->assertTrue($product['has_variants']);
        $this->assertSame($variant->id, $product['variants'][0]['id']);
        $this->assertSame(['Talla' => 'L', 'Color' => 'Rojo'], $product['variants'][0]['attributes']);
    }

    public function test_kit_product_and_its_component_are_saved(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();
        $component = Product::query()->create([
            'name' => 'Harina por libra',
            'sku' => 'HARINA-LB',
            'unit' => 'libra',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 10,
            'sale_price' => 15,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Producto kit',
                'sku' => 'KIT-GUARDADO',
                'unit' => 'unit',
                'product_type' => Product::TYPE_KIT,
                'cost_price' => 5,
                'sale_price' => 20,
                'is_active' => 1,
                'is_visible_ecommerce' => 0,
                'kit_items' => [[
                    'component_product_id' => $component->id,
                    'quantity' => 250,
                    'component_unit' => 'g',
                    'component_unit_factor' => 0.002,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $kit = Product::query()->where('sku', 'KIT-GUARDADO')->firstOrFail();

        $this->assertDatabaseHas('product_kit_items', [
            'kit_product_id' => $kit->id,
            'component_product_id' => $component->id,
            'quantity' => 250,
            'component_unit' => 'g',
            'component_unit_factor' => 0.002,
        ]);
    }

    public function test_kit_can_use_component_groups_instead_of_direct_components(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();
        $component = Product::query()->create([
            'name' => 'Proteina',
            'sku' => 'GROUP-COMPONENT',
            'unit' => 'g',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Kit con grupos',
                'sku' => 'KIT-GROUPS',
                'unit' => 'unit',
                'product_type' => Product::TYPE_KIT,
                'uses_component_groups' => 1,
                'cost_price' => 5,
                'sale_price' => 20,
                'is_active' => 1,
                'is_visible_ecommerce' => 0,
                'modifier_groups' => [[
                    'name' => 'Proteina',
                    'selection_type' => 'single',
                    'is_required' => 1,
                    'min_select' => 1,
                    'max_select' => 1,
                    'options' => [[
                        'product_id' => $component->id,
                        'label' => 'Proteina',
                        'inventory_quantity' => 250,
                        'inventory_unit' => 'g',
                        'inventory_unit_factor' => 1,
                        'price_delta' => 0,
                        'is_default' => 1,
                        'is_active' => 1,
                    ]],
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $kit = Product::query()->where('sku', 'KIT-GROUPS')->firstOrFail();
        $this->assertTrue($kit->uses_component_groups);
        $this->assertDatabaseMissing('product_kit_items', ['kit_product_id' => $kit->id]);
        $this->assertDatabaseHas('product_modifier_groups', [
            'product_id' => $kit->id,
            'name' => 'Proteina',
        ]);
        $this->assertDatabaseHas('product_modifier_options', [
            'product_id' => $component->id,
            'inventory_quantity' => 250,
        ]);
    }

    public function test_service_and_digital_products_can_be_sold_without_inventory(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');
        $this->actingAs($user);
        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 0,
            'status' => 'open',
        ]);

        $service = Product::create([
            'name' => 'Instalacion',
            'sku' => 'SRV-001',
            'unit' => 'service',
            'product_type' => Product::TYPE_SERVICE,
            'cost_price' => 0,
            'sale_price' => 25,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);
        $digital = Product::create([
            'name' => 'Licencia digital',
            'sku' => 'DIG-001',
            'unit' => 'license',
            'product_type' => Product::TYPE_DIGITAL,
            'delivery_instructions' => 'Clave: DEMO-123',
            'cost_price' => 0,
            'sale_price' => 15,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        $response = $this->post(route('pos.checkout'), [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                ['product_id' => $service->id, 'quantity' => 1, 'unit_price' => 25],
                ['product_id' => $digital->id, 'quantity' => 1, 'unit_price' => 15],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 40],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('inventories', ['product_id' => $service->id]);
        $this->assertDatabaseMissing('inventories', ['product_id' => $digital->id]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $digital->id,
            'delivery_instructions' => 'Clave: DEMO-123',
        ]);
    }

    public function test_serialized_purchase_and_sale_assign_serials(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();
        $this->actingAs($admin);
        $product = Product::create([
            'name' => 'Telefono serializado',
            'sku' => 'SER-001',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SERIALIZED,
            'cost_price' => 100,
            'sale_price' => 150,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->post(route('purchases.store'), [
            'branch_id' => $admin->branch_id,
            'supplier_name' => 'Proveedor',
            'payment_method' => 'cash',
            'paid_total' => 1000,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_cost' => 100,
                'serial_numbers' => ['IMEI-001', 'IMEI-002'],
            ]],
        ])->assertRedirect();

        $this->assertEquals(2, InventorySerial::where('product_id', $product->id)->count());

        $cashier = User::where('email', 'cashier@pos.test')->firstOrFail();
        CashRegisterSession::create([
            'branch_id' => $cashier->branch_id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_amount' => 0,
            'status' => 'open',
        ]);
        $this->actingAs($cashier);

        $this->post(route('pos.checkout'), [
            'branch_id' => $cashier->branch_id,
            'customer_id' => Customer::where('document', 'CF')->value('id'),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 150]],
            'payments' => [['method' => 'cash', 'amount' => 150]],
        ])->assertRedirect();

        $this->assertEquals(1, InventorySerial::where('product_id', $product->id)->where('status', InventorySerial::STATUS_SOLD)->count());
        $this->assertEquals(1.0, (float) Inventory::where('product_id', $product->id)->where('branch_id', $cashier->branch_id)->value('stock'));
    }

    public function test_batch_sale_consumes_earliest_expiring_lot(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@pos.test')->firstOrFail();
        $this->actingAs($admin);
        $product = Product::create([
            'name' => 'Medicamento por lote',
            'sku' => 'LOT-001',
            'unit' => 'unit',
            'product_type' => Product::TYPE_BATCH,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        foreach ([['LATE', '2027-12-31'], ['EARLY', '2026-12-31']] as [$lot, $expiry]) {
            $this->post(route('purchases.store'), [
                'branch_id' => $admin->branch_id,
                'supplier_name' => 'Proveedor',
                'payment_method' => 'cash',
                'paid_total' => 100,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_cost' => 5,
                    'lot_number' => $lot,
                    'expires_at' => $expiry,
                ]],
            ])->assertRedirect();
        }

        $cashier = User::where('email', 'cashier@pos.test')->firstOrFail();
        CashRegisterSession::create([
            'branch_id' => $cashier->branch_id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_amount' => 0,
            'status' => 'open',
        ]);
        $this->actingAs($cashier);
        $this->post(route('pos.checkout'), [
            'branch_id' => $cashier->branch_id,
            'customer_id' => Customer::where('document', 'CF')->value('id'),
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10]],
            'payments' => [['method' => 'cash', 'amount' => 20]],
        ])->assertRedirect();

        $this->assertEquals(3.0, (float) InventoryLot::where('product_id', $product->id)->where('lot_number', 'EARLY')->value('remaining_quantity'));
        $this->assertEquals(5.0, (float) InventoryLot::where('product_id', $product->id)->where('lot_number', 'LATE')->value('remaining_quantity'));
    }
}
