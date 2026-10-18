<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\CheckoutOrder;
use App\Services\PosApiClient;
use App\Services\StorefrontCart;
use App\Services\StorefrontCheckout;
use App\Services\StorefrontContext;
use App\Services\StorefrontCustomerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontCartController extends Controller
{
    public function cart(Request $request, StorefrontContext $context, StorefrontCart $cart): View
    {
        $context = $context->fromRequest($request);

        return view('storefront.cart', $context + [
            'cartItems' => $cart->items($context['company']->id),
            'summary' => $cart->summary($context['company']->id, $context['storefront']),
            'cartCount' => $cart->count($context['company']->id),
        ]);
    }

    public function add(Request $request, StorefrontContext $context, StorefrontCart $cart): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'numeric', 'min:1', 'max:999'],
            'variant_id' => ['nullable', 'integer'],
        ]);
        $context = $context->fromRequest($request);
        $product = CatalogProduct::query()
            ->where('company_id', $context['company']->id)
            ->whereKey($data['product_id'])
            ->firstOrFail();
        $cart->add($product, $data['quantity'] ?? 1, $data['variant_id'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Producto agregado al carrito.',
                'cart_count' => $cart->count($context['company']->id),
            ]);
        }

        return back()->with('status', 'Producto agregado al carrito.');
    }

    public function update(Request $request, string $lineKey, StorefrontContext $context, StorefrontCart $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'numeric', 'min:1', 'max:999']]);
        $context = $context->fromRequest($request);
        $cart->update($context['company']->id, $lineKey, $data['quantity']);

        return back()->with('status', 'Cantidad actualizada.');
    }

    public function remove(Request $request, string $lineKey, StorefrontContext $context, StorefrontCart $cart): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $cart->remove($context['company']->id, $lineKey);

        return back()->with('status', 'Producto retirado del carrito.');
    }

    public function checkout(Request $request, StorefrontContext $context, StorefrontCart $cart): View|RedirectResponse
    {
        $context = $context->fromRequest($request);
        if (! $this->customer($context['company']->id)) {
            return redirect()->guest(route('shop.login'))
                ->with('status', 'Inicia sesión o crea tu cuenta para guardar este pedido.');
        }
        if ($cart->items($context['company']->id)->isEmpty()) {
            return redirect()->route('shop.cart')->withErrors(['cart' => 'Agrega un producto antes de finalizar la compra.']);
        }

        return view('storefront.checkout', $context + [
            'cartItems' => $cart->items($context['company']->id),
            'summary' => $cart->summary($context['company']->id, $context['storefront']),
            'cartCount' => $cart->count($context['company']->id),
            'customer' => $this->customer($context['company']->id),
        ]);
    }

    public function placeOrder(Request $request, StorefrontContext $context, StorefrontCart $cart, StorefrontCheckout $checkout, PosApiClient $pos, StorefrontCustomerSession $session): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:transfer,contraentrega,qr,card,cash'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $context = $context->fromRequest($request);
        $customer = $this->customer($context['company']->id);
        if (! $customer) {
            return redirect()->guest(route('shop.login'));
        }

        $data['name'] = $customer['name'];
        $data['email'] = $customer['email'];
        $data['phone'] = $data['phone'] ?? $customer['phone'];
        $data['address'] = $data['address'] ?? $customer['address'];
        try {
            $customer = $pos->updateContact($context['company']->tenant_uuid, $customer['external_customer_uuid'], [
                'phone' => $data['phone'],
                'address' => $data['address'],
            ]);
            $session->put($context['company']->id, $customer);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['checkout' => 'No fue posible actualizar los datos del contacto.']);
        }

        try {
            $order = $checkout->submit($context['company'], $data, $cart);
        } catch (ConnectionException $exception) {
            report($exception);

            return back()->withInput()->withErrors(['checkout' => 'El POS no está respondiendo en este momento. Tu carrito sigue guardado; espera unos segundos e inténtalo nuevamente.']);
        } catch (RequestException $exception) {
            report($exception);
            $message = $exception->response?->json('message');

            return back()->withInput()->withErrors(['checkout' => is_string($message) ? $message : 'El POS rechazó el pedido. Verifica la disponibilidad e inténtalo nuevamente.']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['checkout' => 'No fue posible registrar el pedido. Inténtalo nuevamente.']);
        }

        return redirect()->route('shop.orders.show', $order->external_order_uuid)
            ->with('status', 'Recibimos tu pedido. Puedes consultar su estado aquí.');
    }

    public function orders(Request $request, StorefrontContext $context, StorefrontCart $cart): View|RedirectResponse
    {
        $context = $context->fromRequest($request);
        if (! $this->customer($context['company']->id)) {
            return redirect()->guest(route('shop.login'));
        }
        $orders = CheckoutOrder::query()
            ->where('company_id', $context['company']->id)
            ->where('external_customer_uuid', $cart->customerUuid($context['company']->id))
            ->latest()
            ->paginate(12);

        return view('storefront.orders.index', $context + compact('orders') + [
            'cartCount' => $cart->count($context['company']->id),
        ]);
    }

    public function orderShow(Request $request, string $externalOrderUuid, StorefrontContext $context, StorefrontCart $cart): View|RedirectResponse
    {
        $context = $context->fromRequest($request);
        if (! $this->customer($context['company']->id)) {
            return redirect()->guest(route('shop.login'));
        }
        $order = CheckoutOrder::query()
            ->where('company_id', $context['company']->id)
            ->where('external_customer_uuid', $cart->customerUuid($context['company']->id))
            ->where('external_order_uuid', $externalOrderUuid)
            ->firstOrFail();

        return view('storefront.orders.show', $context + compact('order') + [
            'cartCount' => $cart->count($context['company']->id),
        ]);
    }

    /** @return array<string, mixed>|null */
    private function customer(int $companyId): ?array
    {
        return app(StorefrontCustomerSession::class)->get($companyId);
    }
}
