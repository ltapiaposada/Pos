<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEcommerceAccess
{
    public function __construct(private readonly SubscriptionAccessService $subscriptionAccessService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $company = $user->company;

        if (! $company || ! $this->subscriptionAccessService->canUseService($company, 'ecommerce')) {
            abort(403, 'El servicio de e-commerce no está habilitado o su suscripción no está pagada.');
        }

        return $next($request);
    }
}
