<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanySubscriptionRequest;
use App\Models\Company;
use App\Models\CompanySubscription;
use Illuminate\Support\Facades\DB;

class CompanySubscriptionController extends Controller
{
    public function store(CompanySubscriptionRequest $request, Company $company)
    {
        $data = $request->validated();
        $actionMode = $data['action_mode'] ?? 'update_current';

        DB::transaction(function () use ($company, $data, $actionMode): void {
            $currentSubscription = CompanySubscription::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->whereIn('status', [
                    CompanySubscription::STATUS_ACTIVE,
                    CompanySubscription::STATUS_PENDING_PAYMENT,
                ])
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first()
                ?? CompanySubscription::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->orderByDesc('start_date')
                    ->orderByDesc('id')
                    ->first();

            $payload = [
                'company_id' => $company->id,
                'plan_type' => $data['plan_type'],
                'billing_period' => $data['billing_period'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'last_payment_date' => $data['last_payment_date'] ?? $data['start_date'],
                'next_payment_date' => $data['next_payment_date'] ?? $data['end_date'],
            ];

            if (
                $actionMode !== 'create_new'
                && $currentSubscription
                && $currentSubscription->billing_period === $data['billing_period']
                && $currentSubscription->plan_type === $data['plan_type']
            ) {
                $currentSubscription->update($payload);

                CompanySubscription::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('id', '!=', $currentSubscription->id)
                    ->whereIn('status', [
                        CompanySubscription::STATUS_ACTIVE,
                        CompanySubscription::STATUS_PENDING_PAYMENT,
                    ])
                    ->where('plan_type', $currentSubscription->plan_type)
                    ->update(['status' => CompanySubscription::STATUS_CANCELLED]);

                return;
            }

            CompanySubscription::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->whereIn('status', [
                    CompanySubscription::STATUS_ACTIVE,
                    CompanySubscription::STATUS_PENDING_PAYMENT,
                ])
                ->where('plan_type', $data['plan_type'])
                ->update(['status' => CompanySubscription::STATUS_CANCELLED]);

            CompanySubscription::withoutGlobalScopes()->create($payload);
        });

        return redirect()->route('system.companies.edit', $company)->with('status', 'Suscripción actualizada.');
    }
}
