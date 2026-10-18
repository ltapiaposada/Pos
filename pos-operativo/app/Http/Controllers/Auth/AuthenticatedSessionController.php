<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CompanyKickoffService;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly SubscriptionAccessService $subscriptionAccessService,
        private readonly CompanyKickoffService $companyKickoffService,
    ) {}

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

        // Los compradores ya no inician sesión en POS: su perfil y sus
        // pedidos pertenecen a la aplicación e-commerce.
        if ($user->hasRole('customer')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'El acceso de compradores fue trasladado a la aplicación e-commerce.');
        }

        if ($user->company) {
            $this->companyKickoffService->runIfPending($user->company);
        }

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

        return redirect()->intended(route('dashboard', absolute: false));
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
