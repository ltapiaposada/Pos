<?php

namespace App\Http\Controllers;

use App\Services\PosApiClient;
use App\Services\StorefrontCart;
use App\Services\StorefrontContext;
use App\Services\StorefrontCustomerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontCustomerAuthController extends Controller
{
    public function create(Request $request, StorefrontContext $context, StorefrontCart $cart): View
    {
        $context = $context->fromRequest($request);

        return view('storefront.auth.register', $context + ['cartCount' => $cart->count($context['company']->id)]);
    }

    public function store(Request $request, StorefrontContext $context, PosApiClient $pos, StorefrontCustomerSession $session): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $customer = $pos->registerContact($context['company']->tenant_uuid, $data);
        } catch (\Illuminate\Http\Client\RequestException $exception) {
            if ($exception->response->status() === 409) {
                return redirect()->route('shop.password.request', ['email' => $data['email']])
                    ->with('status', 'Este contacto ya existe. Restablece su contraseña para ingresar.');
            }

            report($exception);

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'No fue posible validar el contacto con el POS. Inténtalo nuevamente.']);
        }

        $session->put($context['company']->id, $customer);
        $request->session()->regenerate();

        return redirect()->intended(route('storefront.index'))
            ->with('status', 'Tu contacto fue creado. Ya puedes finalizar tus compras y consultar pedidos.');
    }

    public function login(Request $request, StorefrontContext $context, StorefrontCart $cart): View
    {
        $context = $context->fromRequest($request);

        return view('storefront.auth.login', $context + ['cartCount' => $cart->count($context['company']->id)]);
    }

    public function authenticate(Request $request, StorefrontContext $context, PosApiClient $pos, StorefrontCustomerSession $session): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        try {
            $customer = $pos->loginContact($context['company']->tenant_uuid, $data['email'], $data['password']);
        } catch (\Illuminate\Http\Client\RequestException) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Las credenciales no son válidas para esta tienda.']);
        }

        $session->put($context['company']->id, $customer);
        $request->session()->regenerate();

        return redirect()->intended(route('storefront.index'));
    }

    public function destroy(Request $request, StorefrontContext $context, StorefrontCustomerSession $session): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $session->forget($context['company']->id);
        $request->session()->regenerateToken();

        return redirect()->route('storefront.index');
    }
}
