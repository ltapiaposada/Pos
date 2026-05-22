<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_YEARLY = 'yearly';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'company_id',
        'plan_type',
        'billing_period',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'last_payment_date',
        'next_payment_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_payment_date' => 'date',
        'next_payment_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function billingPeriodOptions(): array
    {
        return [
            self::PERIOD_MONTHLY => 'Mensual',
            self::PERIOD_YEARLY => 'Anual',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_PENDING_PAYMENT => 'Pendiente de pago',
            self::STATUS_EXPIRED => 'Vencida',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_STATUS_PAID => 'Pagada',
            self::PAYMENT_STATUS_PENDING => 'Pendiente',
            self::PAYMENT_STATUS_PARTIAL => 'Pago parcial',
            self::PAYMENT_STATUS_OVERDUE => 'Vencida',
        ];
    }
}
