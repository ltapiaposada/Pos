<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/** @var App\Models\User $user */
$user = App\Models\User::query()->where('email', 'restaurant@pos.test')->firstOrFail();
$companyId = $user->company_id;
$branchId = $user->branch_id;

$category = App\Models\Category::query()->updateOrCreate(
    [
        'company_id' => $companyId,
        'name' => 'Menu restaurante',
    ],
    [
        'description' => 'Productos para operacion del modulo restaurante',
        'is_active' => true,
    ]
);

$taxId = App\Models\Tax::query()->where('company_id', $companyId)->orderBy('id')->value('id');

$products = [
    [
        'sku' => 'REST-BURG-001',
        'name' => 'Hamburguesa clasica',
        'description' => 'Hamburguesa para practicas del modulo restaurante',
        'unit' => 'plato',
        'cost_price' => 9000,
        'sale_price' => 18000,
        'stock' => 30,
        'modifier_groups' => [
            [
                'name' => 'Proteina',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_SINGLE,
                'is_required' => true,
                'min_select' => 1,
                'max_select' => 1,
                'options' => [
                    ['label' => 'Carne', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Pollo crispy', 'price_delta' => 2000, 'is_default' => false],
                    ['label' => 'Mixta', 'price_delta' => 3000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Extras',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_MULTIPLE,
                'is_required' => false,
                'min_select' => 0,
                'max_select' => 3,
                'options' => [
                    ['label' => 'Queso cheddar', 'price_delta' => 1500, 'is_default' => false],
                    ['label' => 'Tocineta', 'price_delta' => 2500, 'is_default' => false],
                    ['label' => 'Huevo frito', 'price_delta' => 2000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Quitar ingredientes',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_REMOVE,
                'is_required' => false,
                'min_select' => 0,
                'max_select' => 0,
                'options' => [
                    ['label' => 'Cebolla', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Tomate', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Lechuga', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Salsa especial', 'price_delta' => 0, 'is_default' => true],
                ],
            ],
        ],
    ],
    [
        'sku' => 'REST-ALM-001',
        'name' => 'Almuerzo corriente',
        'description' => 'Almuerzo configurable para practicar pedidos por mesa',
        'unit' => 'plato',
        'cost_price' => 8000,
        'sale_price' => 16000,
        'stock' => 40,
        'modifier_groups' => [
            [
                'name' => 'Proteina',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_SINGLE,
                'is_required' => true,
                'min_select' => 1,
                'max_select' => 1,
                'options' => [
                    ['label' => 'Carne', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Cerdo', 'price_delta' => 0, 'is_default' => false],
                    ['label' => 'Pechuga', 'price_delta' => 1500, 'is_default' => false],
                    ['label' => 'Pescado', 'price_delta' => 3000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Acompanantes',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_MULTIPLE,
                'is_required' => true,
                'min_select' => 1,
                'max_select' => 3,
                'options' => [
                    ['label' => 'Arroz', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Ensalada', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Papa frita', 'price_delta' => 1000, 'is_default' => false],
                    ['label' => 'Frijol', 'price_delta' => 0, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Quitar ingredientes',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_REMOVE,
                'is_required' => false,
                'min_select' => 0,
                'max_select' => 0,
                'options' => [
                    ['label' => 'Cebolla', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Ensalada', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Aji', 'price_delta' => 0, 'is_default' => true],
                ],
            ],
        ],
    ],
    [
        'sku' => 'REST-POL-001',
        'name' => 'Pollo apanado con papas',
        'description' => 'Plato fuerte para practicas restaurante',
        'unit' => 'plato',
        'cost_price' => 9500,
        'sale_price' => 19500,
        'stock' => 25,
        'modifier_groups' => [],
    ],
    [
        'sku' => 'REST-PAP-001',
        'name' => 'Papas a la francesa',
        'description' => 'Acompanante o entrada',
        'unit' => 'porcion',
        'cost_price' => 2500,
        'sale_price' => 7000,
        'stock' => 50,
        'modifier_groups' => [
            [
                'name' => 'Salsas',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_MULTIPLE,
                'is_required' => false,
                'min_select' => 0,
                'max_select' => 2,
                'options' => [
                    ['label' => 'Salsa de tomate', 'price_delta' => 0, 'is_default' => false],
                    ['label' => 'Mayonesa', 'price_delta' => 0, 'is_default' => false],
                    ['label' => 'Queso cheddar', 'price_delta' => 1200, 'is_default' => false],
                ],
            ],
        ],
    ],
    [
        'sku' => 'REST-LIM-001',
        'name' => 'Limonada natural',
        'description' => 'Bebida fria para practicas restaurante',
        'unit' => 'vaso',
        'cost_price' => 1800,
        'sale_price' => 5500,
        'stock' => 60,
        'modifier_groups' => [
            [
                'name' => 'Azucar',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_SINGLE,
                'is_required' => true,
                'min_select' => 1,
                'max_select' => 1,
                'options' => [
                    ['label' => 'Normal', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Sin azucar', 'price_delta' => 0, 'is_default' => false],
                    ['label' => 'Endulzante', 'price_delta' => 500, 'is_default' => false],
                ],
            ],
        ],
    ],
    [
        'sku' => 'REST-JUG-001',
        'name' => 'Jugo del dia',
        'description' => 'Bebida fresca para practicas restaurante',
        'unit' => 'vaso',
        'cost_price' => 2000,
        'sale_price' => 6000,
        'stock' => 45,
        'modifier_groups' => [
            [
                'name' => 'Sabor',
                'selection_type' => App\Models\ProductModifierGroup::TYPE_SINGLE,
                'is_required' => true,
                'min_select' => 1,
                'max_select' => 1,
                'options' => [
                    ['label' => 'Mora', 'price_delta' => 0, 'is_default' => true],
                    ['label' => 'Maracuya', 'price_delta' => 0, 'is_default' => false],
                    ['label' => 'Mango', 'price_delta' => 0, 'is_default' => false],
                ],
            ],
        ],
    ],
];

$created = [];

foreach ($products as $data) {
    $product = App\Models\Product::query()->updateOrCreate(
        [
            'company_id' => $companyId,
            'sku' => $data['sku'],
        ],
        [
            'category_id' => $category->id,
            'tax_id' => $taxId,
            'name' => $data['name'],
            'description' => $data['description'],
            'unit' => $data['unit'],
            'product_type' => App\Models\Product::TYPE_SIMPLE,
            'cost_price' => $data['cost_price'],
            'sale_price' => $data['sale_price'],
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]
    );

    App\Models\Inventory::query()->updateOrCreate(
        [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
        ],
        [
            'stock' => $data['stock'],
            'min_stock' => 5,
        ]
    );

    $product->modifierGroups()->delete();

    foreach ($data['modifier_groups'] as $groupIndex => $groupData) {
        $group = $product->modifierGroups()->create([
            'company_id' => $companyId,
            'name' => $groupData['name'],
            'selection_type' => $groupData['selection_type'],
            'is_required' => $groupData['is_required'],
            'min_select' => $groupData['min_select'],
            'max_select' => $groupData['max_select'],
            'display_order' => $groupIndex + 1,
        ]);

        foreach ($groupData['options'] as $optionIndex => $optionData) {
            $group->options()->create([
                'company_id' => $companyId,
                'label' => $optionData['label'],
                'price_delta' => $optionData['price_delta'],
                'is_default' => $optionData['is_default'],
                'is_active' => true,
                'display_order' => $optionIndex + 1,
            ]);
        }
    }

    $created[] = [
        'sku' => $product->sku,
        'name' => $product->name,
        'price' => (float) $product->sale_price,
        'stock' => $data['stock'],
        'modifier_groups' => count($data['modifier_groups']),
    ];
}

echo json_encode($created, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
