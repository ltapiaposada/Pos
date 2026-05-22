<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanySubscription;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionContextController extends Controller
{
    public function __construct(private readonly SubscriptionAccessService $subscriptionAccessService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $company = $user?->company;
        $subscriptions = $this->subscriptionAccessService
            ->paidActiveSubscriptionsForCompany($company)
            ->unique('plan_type')
            ->values();

        if ($subscriptions->count() <= 1) {
            $selected = $subscriptions->first();
            if ($selected) {
                $request->session()->put('active_subscription_id', $selected->id);
                $request->session()->put('active_subscription_plan_type', $selected->plan_type);
            }

            return redirect()->route('dashboard');
        }

        return view('auth.subscription-context', [
            'company' => $company,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user?->company;

        $validated = $request->validate([
            'subscription_id' => ['required', 'integer'],
        ]);

        $subscription = $this->subscriptionAccessService
            ->paidActiveSubscriptionsForCompany($company)
            ->firstWhere('id', (int) $validated['subscription_id']);

        if (! $subscription) {
            return back()->withErrors([
                'subscription_id' => 'Debes seleccionar una suscripción activa y pagada.',
            ]);
        }

        $request->session()->put('active_subscription_id', $subscription->id);
        $request->session()->put('active_subscription_plan_type', $subscription->plan_type);

        return redirect()->route('dashboard');
    }
}
