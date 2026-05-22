<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly SubscriptionAccessService $subscriptionAccessService)
    {
    }

    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $request->session()->regenerateToken();

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget(['active_subscription_id', 'active_subscription_plan_type']);

        $user = $request->user();

        if (! $user->hasRole('customer') && $this->subscriptionAccessService->hasMultiplePaidActivePlanContexts($user->company)) {
            return redirect()->route('subscription-context.index');
        }

        $singleSubscription = $this->subscriptionAccessService->paidActiveSubscriptionsForCompany($user->company)
            ->unique('plan_type')
            ->values()
            ->first();

        if ($singleSubscription) {
            $request->session()->put('active_subscription_id', $singleSubscription->id);
            $request->session()->put('active_subscription_plan_type', $singleSubscription->plan_type);
        }

        $homeRoute = $user->hasRole('customer')
            ? route('shop.index', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()->intended($homeRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
