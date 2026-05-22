<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/** @var App\Models\User $user */
$user = App\Models\User::query()->where('email', 'restaurant@pos.test')->firstOrFail();
$branchId = $user->branch_id;
$companyId = $user->company_id;

$customerId = App\Models\Customer::query()->where('document', 'CF')->value('id')
    ?? App\Models\Customer::query()->value('id');

if (! $customerId) {
    throw new RuntimeException('No existe un cliente para asignar a los pedidos demo.');
}

App\Models\CashRegisterSession::query()->updateOrCreate(
    [
        'branch_id' => $branchId,
        'user_id' => $user->id,
        'status' => 'open',
    ],
    [
        'opened_at' => now(),
        'opening_amount' => 100,
    ]
);

$products = App\Models\Product::query()
    ->where('is_active', true)
    ->orderBy('id')
    ->limit(6)
    ->get();

if ($products->count() < 3) {
    throw new RuntimeException('No hay suficientes productos activos para crear pedidos demo.');
}

$service = app(App\Services\RestaurantOrderService::class);

$tables = collect([
    ['number' => 'R-10', 'name' => 'Ventana 10', 'location' => 'Ventana'],
    ['number' => 'R-11', 'name' => 'Centro 11', 'location' => 'Salón central'],
    ['number' => 'R-12', 'name' => 'Terraza 12', 'location' => 'Terraza'],
])->map(function (array $tableData) use ($branchId, $companyId) {
    return App\Models\RestaurantTable::query()->updateOrCreate(
        [
            'branch_id' => $branchId,
            'number' => $tableData['number'],
        ],
        [
            'company_id' => $companyId,
            'name' => $tableData['name'],
            'capacity' => 4,
            'status' => App\Models\RestaurantTable::STATUS_AVAILABLE,
            'location' => $tableData['location'],
            'is_active' => true,
        ]
    );
});

$definitions = [
    [
        'label' => 'mesa abierta',
        'table' => $tables[0],
        'order_type' => App\Models\RestaurantOrder::TYPE_DINE_IN,
        'status' => App\Models\RestaurantOrder::STATUS_OPEN,
        'items' => [
            ['product' => $products[0], 'qty' => 2, 'notes' => 'Sin hielo'],
            ['product' => $products[1], 'qty' => 1, 'notes' => 'Punto medio'],
        ],
    ],
    [
        'label' => 'mesa en cocina',
        'table' => $tables[1],
        'order_type' => App\Models\RestaurantOrder::TYPE_DINE_IN,
        'status' => App\Models\RestaurantOrder::STATUS_SENT_TO_KITCHEN,
        'items' => [
            ['product' => $products[2], 'qty' => 1, 'notes' => 'Extra salsa'],
            ['product' => $products[3], 'qty' => 2, 'notes' => 'Compartir'],
        ],
    ],
    [
        'label' => 'mesa lista',
        'table' => $tables[2],
        'order_type' => App\Models\RestaurantOrder::TYPE_DINE_IN,
        'status' => App\Models\RestaurantOrder::STATUS_READY,
        'items' => [
            ['product' => $products[4], 'qty' => 1, 'notes' => 'Sin cebolla'],
        ],
    ],
    [
        'label' => 'para llevar',
        'table' => null,
        'order_type' => App\Models\RestaurantOrder::TYPE_TAKEAWAY,
        'status' => App\Models\RestaurantOrder::STATUS_OPEN,
        'items' => [
            ['product' => $products[5] ?? $products[0], 'qty' => 1, 'notes' => 'Empaque aparte'],
        ],
    ],
];

$summary = [];

foreach ($definitions as $definition) {
    $tableId = $definition['table']?->id;

    $existingOrder = App\Models\RestaurantOrder::query()
        ->where('branch_id', $branchId)
        ->where('order_type', $definition['order_type'])
        ->when($tableId, fn ($query) => $query->where('restaurant_table_id', $tableId), fn ($query) => $query->whereNull('restaurant_table_id'))
        ->whereIn('status', App\Models\RestaurantOrder::activeStatuses())
        ->latest('id')
        ->first();

    $order = $existingOrder ?: $service->createOrder([
        'branch_id' => $branchId,
        'restaurant_table_id' => $tableId,
        'customer_id' => $customerId,
        'order_type' => $definition['order_type'],
        'notes' => 'Pedido demo '.$definition['label'],
    ], $user->id);

    $service->updateOrder($order->fresh(), [
        'branch_id' => $branchId,
        'restaurant_table_id' => $tableId,
        'customer_id' => $customerId,
        'order_type' => $definition['order_type'],
        'notes' => 'Pedido demo '.$definition['label'],
        'items' => collect($definition['items'])->map(function (array $item) {
            /** @var App\Models\Product $product */
            $product = $item['product'];

            return [
                'product_id' => $product->id,
                'quantity' => $item['qty'],
                'unit_price' => (float) $product->sale_price,
                'notes' => $item['notes'],
                'modifier_selections' => [],
            ];
        })->all(),
    ]);

    $order = $order->fresh(['items']);

    if ($definition['status'] === App\Models\RestaurantOrder::STATUS_SENT_TO_KITCHEN
        || $definition['status'] === App\Models\RestaurantOrder::STATUS_READY) {
        if ($order->status === App\Models\RestaurantOrder::STATUS_OPEN) {
            $service->sendToKitchen($order);
        }
    }

    if ($definition['status'] === App\Models\RestaurantOrder::STATUS_READY) {
        foreach ($order->fresh()->items as $item) {
            $service->updateKitchenItemStatus($item, App\Models\RestaurantOrderItem::STATUS_READY);
        }
    }

    $order = $order->fresh(['table', 'items']);

    $summary[] = [
        'pedido' => '#'.$order->order_number,
        'tipo' => $order->order_type,
        'mesa' => $order->table?->number,
        'estado' => $order->status,
        'items' => $order->items->count(),
        'total' => (float) $order->total,
    ];
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
