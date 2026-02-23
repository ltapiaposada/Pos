<?php

namespace App\Http\Controllers;

use App\Http\Requests\EcommerceCheckoutRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\EcommerceOrderService;
use App\Support\StorefrontCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->get('q', '');
        $normalizedSearch = mb_strtolower(trim($search));
        $page = max(1, (int) $request->integer('page', 1));
        $version = (int) Cache::get(StorefrontCache::PRODUCTS_VERSION_KEY, 1);
        $cacheKey = 'storefront:products:v'.$version.':q:'.md5($normalizedSearch).':p:'.$page;
        $ttl = now()->addMinutes((int) config('pos.cache.storefront_products_ttl_minutes', 15));

        $products = Cache::remember($cacheKey, $ttl, function () use ($search) {
            return Product::query()
                ->with([
                    'category',
                    'tax',
                    'kitItems.componentProduct:id,name',
                    'variants' => function ($query) {
                        $query->where('is_active', true)
                            ->where('is_visible_ecommerce', true)
                            ->orderBy('name');
                    },
                ])
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
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->where('is_visible_ecommerce', true)),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->cartData($request);
        $productId = (int) $data['product_id'];
        $quantity = (int) ($data['quantity'] ?? 1);

        $cart[$productId] = min(999, (int) ($cart[$productId] ?? 0) + $quantity);
        $this->storeCart($request, $cart);

        return redirect()->back()->with('status', 'Producto agregado al carrito.');
    }

    public function updateCartItem(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->cartData($request);
        $cart[$product->id] = (int) $data['quantity'];
        $this->storeCart($request, $cart);

        return redirect()->route('shop.cart')->with('status', 'Cantidad actualizada.');
    }

    public function removeCartItem(Request $request, Product $product): RedirectResponse
    {
        $cart = $this->cartData($request);
        unset($cart[$product->id]);
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
        ]);
    }

    public function placeOrder(EcommerceCheckoutRequest $request, EcommerceOrderService $orderService): RedirectResponse
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

        $order = $orderService->createOrder(
            cartItems: $items->map(fn ($item) => [
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
            ])->values()->all(),
            customerId: $customer->id,
            userId: $request->user()->id,
            paymentMethod: $request->input('payment_method', 'card'),
            paymentReference: $request->input('payment_reference'),
            deliveryAddress: $request->input('address'),
            couponCode: $request->input('coupon_code'),
            customerNote: $request->input('customer_note')
        );

        $request->session()->forget('shop.cart');

        return redirect()->route('shop.orders.show', $order)->with('status', 'Pedido creado correctamente.');
    }

    public function orders(Request $request): View
    {
        $customer = $this->resolveCustomer($request);

        $orders = Sale::query()
            ->with(['items', 'payments'])
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

    public function orderShow(Request $request, Sale $sale): View
    {
        $customer = $this->resolveCustomer($request);

        abort_unless((int) $sale->customer_id === (int) $customer->id && $sale->order_source === Sale::SOURCE_ECOMMERCE, 403);

        $sale->load(['items', 'payments', 'branch']);

        return view('ecommerce.orders.show', [
            'order' => $sale,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();

        return Customer::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]
        );
    }

    private function cartData(Request $request): array
    {
        $cart = $request->session()->get('shop.cart', []);

        if (! is_array($cart)) {
            return [];
        }

        return collect($cart)
            ->mapWithKeys(fn ($qty, $id) => [(int) $id => max(1, (int) $qty)])
            ->all();
    }

    private function storeCart(Request $request, array $cart): void
    {
        $request->session()->put('shop.cart', $cart);
    }

    private function cartCount(Request $request): int
    {
        return (int) collect($this->cartData($request))->sum();
    }

    private function resolveCartItems(Request $request)
    {
        $cart = $this->cartData($request);
        if (empty($cart)) {
            return collect();
        }

        $products = Product::query()
            ->with('tax')
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->where('is_visible_ecommerce', true)
            ->get()
            ->keyBy('id');

        $items = collect();

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            $unitPrice = (float) $product->sale_price;
            $subtotal = $unitPrice * $quantity;
            $taxRate = (float) ($product->tax?->rate ?? 0);
            $tax = $subtotal * ($taxRate / 100);

            $items->push([
                'product' => $product,
                'quantity' => (int) $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ]);
        }

        return $items;
    }

    private function cartSummary($items): array
    {
        $subtotal = (float) $items->sum('subtotal');
        $tax = (float) $items->sum('tax');

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ];
    }
}
