<?php

namespace App\Http\Controllers;

use App\Models\ClientPayment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $companies = Tenant::orderBy('name')->get();
        $companyId = $request->integer('company_id');
        $payments = ClientPayment::query()->with(['company', 'subscription.service'])->when($companyId, fn ($query) => $query->where('company_id', $companyId))->latest('due_date')->latest()->get();

        return view('payments.index', compact('companies', 'companyId', 'payments'));
    }

    public function company(Tenant $company): View
    {
        $company->load(['subscriptions.service', 'payments' => fn ($query) => $query->latest('due_date')->latest()]);
        return view('payments.company', compact('company'));
    }

    public function store(Request $request, Tenant $company, SubscriptionPaymentService $payments): RedirectResponse
    {
        $data = $this->data($request, $company);
        $payment = ClientPayment::create([...$data, 'company_id' => $company->id, 'paid_at' => $data['status'] === 'paid' ? now() : null]);
        $this->syncSubscriptionPaymentStatus($payment, $payments);
        return back()->with('success', 'Cobro registrado.');
    }

    public function update(Request $request, Tenant $company, ClientPayment $payment, SubscriptionPaymentService $payments): RedirectResponse
    {
        abort_unless($payment->company_id === $company->id, 404);
        $data = $this->data($request, $company);
        $payment->update([...$data, 'paid_at' => $data['status'] === 'paid' ? ($payment->paid_at ?? now()) : null]);
        $this->syncSubscriptionPaymentStatus($payment->fresh(), $payments);
        return back()->with('success', 'Cobro actualizado.');
    }

    private function data(Request $request, Tenant $company): array
    {
        return $request->validate([
            'subscription_id' => ['nullable', Rule::exists('subscriptions', 'id')->where('company_id', $company->id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', Rule::in(['pending', 'paid', 'overdue', 'void'])],
            'due_date' => ['nullable', 'date'],
            'method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function syncSubscriptionPaymentStatus(ClientPayment $payment, SubscriptionPaymentService $payments): void
    {
        if (! $payment->subscription_id) {
            return;
        }

        $subscription = Subscription::query()
            ->with('plan')
            ->where('company_id', $payment->company_id)
            ->findOrFail($payment->subscription_id);

        $payments->reconcile($subscription);
    }
}
