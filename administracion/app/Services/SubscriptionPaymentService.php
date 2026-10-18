<?php

namespace App\Services;

use App\Models\Subscription;

/** Mantiene el estado de pago como un resultado de los cobros registrados. */
class SubscriptionPaymentService
{
    public function reconcile(Subscription $subscription): void
    {
        $subscription->loadMissing(['plan', 'payments']);
        $payments = $subscription->payments->where('status', '!=', 'void');
        $paidTotal = (float) $payments->where('status', 'paid')->sum('amount');
        $requiredAmount = (float) ($subscription->plan?->price ?? 0);

        $paymentStatus = match (true) {
            $paidTotal > 0 && ($requiredAmount <= 0 || $paidTotal >= $requiredAmount) => 'paid',
            $payments->contains('status', 'overdue') => 'overdue',
            default => 'pending',
        };

        if ($subscription->payment_status !== $paymentStatus) {
            $subscription->updateQuietly(['payment_status' => $paymentStatus]);
        }
    }
}
