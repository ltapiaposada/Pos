<?php

namespace App\Http\Controllers;

use App\Http\Requests\EcommerceCheckoutRequest;
use App\Models\CompanySubscription;
use App\Models\Customer;
use App\Models\Product;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItemSelection;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Services\EcommerceOrderService;
use App\Services\ProductModifierSelectionService;
use App\Services\RestaurantOrderService;
use App\Support\CompanyContext;
use App\Support\StorefrontCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->publicCompanyId($request);
        $isRestaurantCatalog = $this->isRestaurantCatalog($request);
        $search = (string) $request->get('q', '');
        $normalizedSearch = mb_strtolower(trim($search));
        $page = max(1, (int) $request->integer('page', 1));
        $version = (int) Cache::get(StorefrontCache::PRODUCTS_VERSION_KEY, 1);
        $cacheKey = 'storefront:products:company:'.($companyId ?? 'default').':restaurant:'.(int) $isRestaurantCatalog.':v'.$version.':q:'.md5($normalizedSearch).':p:'.$page;
        $ttl = now()->addMinutes((int) config('pos.cache.storefront_products_ttl_minutes', 15));

        $products = Cache::remember($cacheKey, $ttl, function () use ($search) {
            $with = [
                'category',
                'tax',
                'kitItems.componentProduct:id,name',
                'modifierGroups.options' => function ($query) {
                    $query->where('is_active', true)
                        ->with('product:id,name,unit');
                },
                'variants' => function ($query) {
                    $query->where('is_active', true)
                        ->where('is_visible_ecommerce', true)
                        ->orderBy('name');
                },
            ];

            return Product::query()
                ->with($with)
                ->where('is_active', true)
                ->where('product_type', '!=', Product::TYPE_VARIANT)
                ->where(function ($query) {
                    $query->where('is_visible_ecommerce', true)
                        ->orWhereHas('variants', function ($variantQuery) {
                            $variantQuery->where('is_active', true)
                                ->where('is_visible_ecommerce', true);
                        });
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->orWhereHas('variants', function ($variantQuery) use ($search) {
                                $variantQuery->where('is_active', true)
                                    ->where('is_visible_ecommerce', true)
                                    ->where(function ($inner) use ($search) {
                                        $inner->where('name', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%")
                                            ->orWhere('barcode', 'like', "%{$search}%");
                                    });
                            });
                    });
                })
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString();
        });
        $products->appends(['q' => $search]);

        return view('ecommerce.index', [
            'products' => $products,
            'search' => $search,
            'cartCount' => $this->cartCount($request),
            'isRestaurantCatalog' => $isRestaurantCatalog,
        ]);
    }

    public function cart(Request $request): View
    {
        $items = $this->resolveCartItems($request);

        return view('ecommerce.cart', [
            'cartItems' => $items,
            'summary' => $this->cartSummary($items),
            'business' => Setting::getValue('business', []),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $companyId = $this->publicCompanyId($request);
        $isRestaurantCatalog = $this->isRestaurantCatalog($request);
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->when($companyId !== null, fn ($builder) => $builder->where('company_id', $companyId))
                    ->where('is_active', true)
                    ->where('is_visible_ecommerce', true)),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'modifier_groups' => ['nullable', 'array'],
        ]);

        $product = Product::query()
            ->with([
                'modifierGroups.options' => function ($query) {
                    $query->where('is_active', true)->with('product:id,name,unit');
                },
                'parentProduct.modifierGroups.options' => function ($query) {
                    $query->where('is_active', true)->with('product:id,name,unit');
                },
            ])
            ->whereKey((int) $data['product_id'])
            ->where('is_active', true)
            ->where('is_visible_ecommerce', true)
            ->firstOrFail();

        $quantity = (int) ($data['quantity'] ?? 1);
        $normalizedSelections = [];
        $modifierProduct = $product->modifierGroups->isNotEmpty()
            ? $product
            : ($product->parentProduct?->modifierGroups->isNotEmpty() ? $product->parentProduct : $product);

        if ($modifierProduct->modifierGroups->isNotEmpty()) {
            $normalizedSelections = app(ProductModifierSelectionService::class)
                ->normalizeStorefrontInput($modifierProduct, (array) ($data['modifier_groups'] ?? []));
        }

        $lineKey = $this->cartLineKey($product->id, $normalizedSelections);
        $cart = $this->cartData($request);
        $existing = $cart[$lineKey] ?? [
            'product_id' => $product->id,
            'quantity' => 0,
            'modifier_selections' => $normalizedSelections,
        ];

        $existing['quantity'] = min(999, (int) ($existing['quantity'] ?? 0) + $quantity);
        $existing['product_id'] = $product->id;
        $existing['modifier_selections'] = $normalizedSelections;

        $cart[$lineKey] = $existing;
        $this->storeCart($request, $cart);

        return redirect()->back()->with('status', 'Producto agregado al carrito.');
    }

    public function updateCartItem(Request $request, string $lineKey): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->cartData($request);
        abort_unless(isset($cart[$lineKey]), 404);

        $cart[$lineKey]['quantity'] = (int) $data['quantity'];
        $this->storeCart($request, $cart);

        return redirect()->route('shop.cart')->with('status', 'Cantidad actualizada.');
    }

    public function removeCartItem(Request $request, string $lineKey): RedirectResponse
    {
        $cart = $this->cartData($request);
        abort_unless(isset($cart[$lineKey]), 404);

        unset($cart[$lineKey]);
        $this->storeCart($request, $cart);

        return redirect()->route('shop.cart')->with('status', 'Producto eliminado del carrito.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $items = $this->resolveCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('shop.index')->withErrors([
                'cart' => 'Debes agregar productos al carrito antes de pagar.',
            ]);
        }

        return view('ecommerce.checkout', [
            'cartItems' => $items,
            'summary' => $this->cartSummary($items),
            'customer' => $this->resolveCustomer($request),
            'business' => Setting::getValue('business', []),
            'cartCount' => $this->cartCount($request),
            'isRestaurantCatalog' => $this->isRestaurantCatalog($request),
        ]);
    }

    public function placeOrder(
        EcommerceCheckoutRequest $request,
        EcommerceOrderService $orderService,
        RestaurantOrderService $restaurantOrderService
    ): RedirectResponse
    {
        $items = $this->resolveCartItems($request);
        if ($items->isEmpty()) {
            return redirect()->route('shop.index')->withErrors([
                'cart' => 'Tu carrito esta vacio.',
            ]);
        }

        $customer = $this->resolveCustomer($request);
        $customer->update([
            'phone' => $request->input('phone') ?: $customer->phone,
            'address' => $request->input('address'),
            'email' => $request->user()->email,
            'is_active' => true,
        ]);

        if ($this->isRestaurantCatalog($request)) {
            $order = $this->createRestaurantStorefrontOrder($request, $items, $customer, $restaurantOrderService);

            $request->session()->forget($this->cartSessionKey($request));
            $request->session()->forget('shop.cart');

            return redirect()->route('shop.orders.show', $order->id)
                ->with('status', 'Pedido recibido correctamente. El restaurante lo revisara antes de enviarlo a cocina.');
        }

        $order = $orderService->createOrder(
            cartItems: $items->map(function (array $item) {
                return [
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'display_name' => $item['display_name'],
                    'inventory_components' => collect($item['modifier_selections'] ?? [])
                        ->filter(fn (array $selection) => ! empty($selection['product_id']) && (float) ($selection['stock_quantity'] ?? 0) !== 0.0)
                        ->map(fn (array $selection) => [
                            'product_id' => (int) $selection['product_id'],
                            'stock_quantity' => (float) $selection['stock_quantity'],
                            'selection_action' => $selection['selection_action'] ?? RestaurantOrderItemSelection::ACTION_INCLUDE,
                            'label' => $selection['option_label'] ?? null,
                        ])->values()->all(),
                ];
            })->values()->all(),
            customerId: $customer->id,
            userId: $request->user()->id,
            paymentMethod: $request->input('payment_method', 'transfer'),
            paymentReference: $request->input('payment_reference'),
            deliveryAddress: $request->input('address'),
            couponCode: $request->input('coupon_code'),
            customerNote: $request->input('customer_note')
        );

        $request->session()->forget($this->cartSessionKey($request));
        $request->session()->forget('shop.cart');

        return redirect()->route('shop.orders.show', $order->id)->with('status', 'Pedido recibido correctamente. Validaremos el pago o la modalidad de entrega antes de confirmarlo.');
    }

    public function orders(Request $request): View
    {
        $customer = $this->resolveCustomer($request);
        $companyId = $this->publicCompanyId($request);

        if ($this->isRestaurantCatalog($request)) {
            $orders = RestaurantOrder::query()
                ->with(['items.product', 'sale'])
                ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
                ->where('customer_id', $customer->id)
                ->whereIn('order_type', [RestaurantOrder::TYPE_TAKEAWAY, RestaurantOrder::TYPE_DELIVERY])
                ->orderByDesc('opened_at')
                ->orderByDesc('id')
                ->paginate(10);

            return view('ecommerce.restaurant_orders.index', [
                'orders' => $orders,
                'cartCount' => $this->cartCount($request),
            ]);
        }

        $orders = Sale::query()
            ->with(['items', 'payments'])
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->where('order_source', Sale::SOURCE_ECOMMERCE)
            ->where('customer_id', $customer->id)
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('ecommerce.orders.index', [
            'orders' => $orders,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function orderShow(Request $request, int $sale): View
    {
        $customer = $this->resolveCustomer($request);
        $companyId = $this->publicCompanyId($request);

        if ($this->isRestaurantCatalog($request)) {
            $order = RestaurantOrder::query()
                ->with(['items.product', 'items.selections', 'sale'])
                ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
                ->findOrFail($sale);

            abort_unless((int) $order->customer_id === (int) $customer->id, 403);

            return view('ecommerce.restaurant_orders.show', [
                'order' => $order,
                'cartCount' => $this->cartCount($request),
            ]);
        }

        $sale = Sale::query()
            ->with(['items.serials', 'items.lots.lot', 'payments', 'branch'])
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->findOrFail($sale);

        abort_unless((int) $sale->customer_id === (int) $customer->id && $sale->order_source === Sale::SOURCE_ECOMMERCE, 403);

        return view('ecommerce.orders.show', [
            'order' => $sale,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();
        $companyId = $this->publicCompanyId($request) ?? $user->company_id;

        return Customer::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $companyId,
            ],
            [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]
        );
    }

    private function cartData(Request $request): array
    {
        $cart = $request->session()->get($this->cartSessionKey($request));
        if (! is_array($cart)) {
            $cart = $request->session()->get('shop.cart', []);
        }

        if (! is_array($cart)) {
            return [];
        }

        $normalizedCart = [];

        foreach ($cart as $lineKey => $line) {
            if (! is_array($line)) {
                $legacyProductId = (int) $lineKey;
                $normalizedCart[(string) $legacyProductId] = [
                    'product_id' => $legacyProductId,
                    'quantity' => max(1, (int) $line),
                    'modifier_selections' => [],
                ];

                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $modifierSelections = collect($line['modifier_selections'] ?? [])
                ->filter(fn ($selection) => is_array($selection))
                ->values()
                ->all();

            $normalizedLineKey = is_string($lineKey) && $lineKey !== ''
                ? $lineKey
                : $this->cartLineKey($productId, $modifierSelections);

            $normalizedCart[$normalizedLineKey] = [
                'product_id' => $productId,
                'quantity' => max(1, (int) ($line['quantity'] ?? 1)),
                'modifier_selections' => $modifierSelections,
            ];
        }

        return $normalizedCart;
    }

    private function storeCart(Request $request, array $cart): void
    {
        $request->session()->put($this->cartSessionKey($request), $cart);
    }

    private function cartCount(Request $request): int
    {
        return (int) collect($this->cartData($request))->sum('quantity');
    }

    private function cartSessionKey(Request $request): string
    {
        $companyId = $this->publicCompanyId($request);

        return 'shop.cart.'.($companyId ?? 'default');
    }

    private function cartLineKey(int $productId, array $modifierSelections): string
    {
        $fingerprint = collect($modifierSelections)
            ->map(fn (array $selection) => [
                'group_id' => (int) ($selection['product_modifier_group_id'] ?? $selection['group_id'] ?? 0),
                'option_id' => (int) ($selection['product_modifier_option_id'] ?? $selection['option_id'] ?? 0),
                'action' => (string) ($selection['selection_action'] ?? $selection['action'] ?? RestaurantOrderItemSelection::ACTION_INCLUDE),
            ])
            ->sortBy([
                ['group_id', 'asc'],
                ['option_id', 'asc'],
                ['action', 'asc'],
            ])
            ->values()
            ->all();

        return sha1(json_encode([
            'product_id' => $productId,
            'modifier_selections' => $fingerprint,
        ]));
    }

    private function resolveCartItems(Request $request): Collection
    {
        $cart = $this->cartData($request);
        if (empty($cart)) {
            return collect();
        }

        $products = Product::query()
            ->with('tax')
            ->whereIn('id', collect($cart)->pluck('product_id')->all())
            ->where('is_active', true)
            ->where('is_visible_ecommerce', true)
            ->get()
            ->keyBy('id');

        $modifierService = app(ProductModifierSelectionService::class);
        $items = collect();

        foreach ($cart as $lineKey => $line) {
            $product = $products->get((int) $line['product_id']);
            if (! $product) {
                continue;
            }

            $quantity = (int) ($line['quantity'] ?? 1);
            $modifierSelections = collect($line['modifier_selections'] ?? [])
                ->filter(fn ($selection) => is_array($selection))
                ->values()
                ->all();
            $modifierDelta = $modifierService->priceDelta($modifierSelections);
            $unitPrice = round((float) $product->sale_price + $modifierDelta, 2);
            $subtotal = round($unitPrice * $quantity, 2);
            $taxRate = (float) ($product->tax?->rate ?? 0);
            $tax = round($subtotal * ($taxRate / 100), 2);

            $items->push([
                'line_key' => $lineKey,
                'product' => $product,
                'display_name' => $modifierService->displayName($product, $modifierSelections),
                'selection_summary' => $modifierService->summaryLines($modifierSelections),
                'modifier_selections' => $modifierSelections,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => round($subtotal + $tax, 2),
            ]);
        }

        return $items;
    }

    private function cartSummary(Collection $items): array
    {
        $subtotal = round((float) $items->sum('subtotal'), 2);
        $tax = round((float) $items->sum('tax'), 2);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
        ];
    }

    private function publicCompanyId(Request $request): ?int
    {
        return CompanyContext::publicCompanyId($request);
    }

    private function createRestaurantStorefrontOrder(
        Request $request,
        Collection $items,
        Customer $customer,
        RestaurantOrderService $restaurantOrderService
    ): RestaurantOrder {
        $branchId = $this->resolveStorefrontBranchId();
        $ownerUserId = $this->resolveRestaurantOrderOwnerUserId($request, $branchId);
        $orderType = $request->input('fulfillment_type') === RestaurantOrder::TYPE_TAKEAWAY
            ? RestaurantOrder::TYPE_TAKEAWAY
            : RestaurantOrder::TYPE_DELIVERY;

        $payload = [
            'branch_id' => $branchId,
            'restaurant_table_id' => null,
            'customer_id' => $customer->id,
            'order_type' => $orderType,
            'notes' => $this->composeRestaurantStorefrontNote($request, $orderType),
        ];

        $order = $restaurantOrderService->createOrder($payload, $ownerUserId);

        $restaurantOrderService->updateOrder($order, [
            ...$payload,
            'items' => $items->map(function (array $item) {
                return [
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $item['product']->sale_price,
                    'notes' => null,
                    'modifier_selections' => collect($item['modifier_selections'] ?? [])
                        ->map(fn (array $selection) => [
                            'group_id' => (int) ($selection['product_modifier_group_id'] ?? 0),
                            'option_id' => (int) ($selection['product_modifier_option_id'] ?? 0),
                            'action' => $selection['selection_action'] ?? RestaurantOrderItemSelection::ACTION_INCLUDE,
                        ])->values()->all(),
                ];
            })->values()->all(),
        ]);

        return $order->fresh();
    }

    private function resolveStorefrontBranchId(): int
    {
        $branchId = \App\Models\Branch::query()->orderBy('id')->value('id');
        abort_unless($branchId, 422, 'No hay una sucursal configurada para procesar pedidos.');

        return (int) $branchId;
    }

    private function resolveRestaurantOrderOwnerUserId(Request $request, int $branchId): int
    {
        $companyId = $this->publicCompanyId($request);
        $staffUserId = User::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->orderBy('id')
            ->value('id');

        if ($staffUserId) {
            return (int) $staffUserId;
        }

        $fallbackUserId = User::query()
            ->where('company_id', $companyId)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->orderBy('id')
            ->value('id');

        if ($fallbackUserId) {
            return (int) $fallbackUserId;
        }

        return (int) $request->user()->id;
    }

    private function composeRestaurantStorefrontNote(Request $request, string $orderType): string
    {
        $lines = [
            'Origen: Pedido web restaurante',
            'Tipo de pedido: '.($orderType === RestaurantOrder::TYPE_TAKEAWAY ? 'Para llevar' : 'Domicilio'),
            'Metodo de pago solicitado: '.$this->paymentMethodLabel((string) $request->input('payment_method', 'transfer')),
        ];

        if ($reference = trim((string) $request->input('payment_reference'))) {
            $lines[] = 'Referencia de pago: '.$reference;
        }

        if ($orderType === RestaurantOrder::TYPE_DELIVERY && $address = trim((string) $request->input('address'))) {
            $lines[] = 'Direccion: '.$address;
        }

        if ($phone = trim((string) $request->input('phone'))) {
            $lines[] = 'Telefono: '.$phone;
        }

        if ($note = trim((string) $request->input('customer_note'))) {
            $lines[] = 'Nota del cliente: '.$note;
        }

        return implode(PHP_EOL, $lines);
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'transfer' => 'Transferencia por validar',
            'qr' => 'QR por validar',
            'contraentrega' => 'Contraentrega',
            default => 'Pago manual',
        };
    }

    private function isRestaurantCatalog(Request $request): bool
    {
        $company = CompanyContext::publicCompany($request);
        if (! $company) {
            return false;
        }

        if ($company->companyType?->slug === 'restaurant') {
            return true;
        }

        return $company->subscriptions()
            ->where('plan_type', 'restaurant')
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->where('payment_status', CompanySubscription::PAYMENT_STATUS_PAID)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();
    }
}
