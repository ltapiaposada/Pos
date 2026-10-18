<?php

namespace App\Http\Controllers;

use App\Mail\StorefrontPasswordResetEmail;
use App\Services\PosApiClient;
use App\Services\StorefrontCart;
use App\Services\StorefrontContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontCustomerPasswordController extends Controller
{
    public function requestForm(Request $request, StorefrontContext $context, StorefrontCart $cart): View
    {
        $context = $context->fromRequest($request);

        return view('storefront.auth.forgot-password', $context + ['cartCount' => $cart->count($context['company']->id)]);
    }

    public function sendLink(Request $request, StorefrontContext $context, PosApiClient $pos): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $data = $request->validate(['email' => ['required', 'email']]);

        try {
            $customer = $pos->findContact($context['company']->tenant_uuid, $data['email']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['email' => 'No fue posible validar el contacto en este momento.']);
        }

        // Nunca confirmamos a terceros si una dirección tiene cuenta en esta tienda.
        if (! $customer) {
            return back()->with('status', 'Si el contacto existe en esta tienda, recibirá las instrucciones para restablecer su contraseña.');
        }

        $token = Str::random(64);
        DB::table('ecommerce_customer_password_resets')->updateOrInsert(
            ['company_id' => $context['company']->id, 'email' => $customer['email']],
            [
                'external_customer_uuid' => $customer['external_customer_uuid'],
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $url = route('password.reset', ['token' => $token, 'email' => $customer['email']]);
        try {
            Mail::to($customer['email'], $customer['name'])
                ->send(new StorefrontPasswordResetEmail($customer, $context['company']->name, $url));
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['email' => 'No fue posible enviar el correo de recuperación. Verifica la configuración de envío.']);
        }

        return back()->with('status', 'Enviamos un enlace para restablecer tu contraseña. Revisa tu correo.');
    }

    public function resetForm(Request $request, string $token, StorefrontContext $context, StorefrontCart $cart): View
    {
        $context = $context->fromRequest($request);

        return view('storefront.auth.reset-password', $context + [
            'token' => $token,
            'email' => $request->string('email')->toString(),
            'cartCount' => $cart->count($context['company']->id),
        ]);
    }

    public function reset(Request $request, StorefrontContext $context, PosApiClient $pos): RedirectResponse
    {
        $context = $context->fromRequest($request);
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $reset = DB::table('ecommerce_customer_password_resets')
            ->where('company_id', $context['company']->id)
            ->where('email', $data['email'])
            ->first();
        if (! $reset || ! Hash::check($data['token'], $reset->token) || now()->subHour()->gt($reset->created_at)) {
            throw ValidationException::withMessages(['email' => ['El enlace de recuperación no es válido o venció.']]);
        }

        try {
            $pos->updateContact($context['company']->tenant_uuid, $reset->external_customer_uuid, ['password' => $data['password']]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'No fue posible actualizar la contraseña. Inténtalo nuevamente.']);
        }

        DB::table('ecommerce_customer_password_resets')
            ->where('company_id', $context['company']->id)
            ->where('email', $data['email'])
            ->delete();

        return redirect()->route('shop.login')->with('status', 'Contraseña actualizada. Ya puedes ingresar.');
    }
}
