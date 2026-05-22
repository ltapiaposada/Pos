<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class EnsureActiveSubscription
{
    public function __construct(private readonly SubscriptionAccessService $subscriptionAccessService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSystemAdmin()) {
            return $next($request);
        }

        $company = $user->company;

        if ($this->subscriptionAccessService->hasMultiplePaidActivePlanContexts($company)
            && ! $request->session()->has('active_subscription_plan_type')) {
            return redirect()->route('subscription-context.index');
        }

        $access = $this->subscriptionAccessService->evaluateForCompany($company);

        View::share('subscriptionAccess', $access);

        if ($access['blocked']) {
            return response()->view('subscriptions.expired', [
                'company' => $company,
                'subscriptionAccess' => $access,
            ], 402);
        }

        return $next($request);
    }
}
