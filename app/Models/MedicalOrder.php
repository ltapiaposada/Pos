<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalOrder extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_USED = 'used';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'customer_id',
        'clinical_record_id',
        'created_by_user_id',
        'ordered_at',
        'status',
        'prescription_details',
        'usage_instructions',
        'observations',
        'professional_name',
        'professional_license',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function clinicalRecord()
    {
        return $this->belongsTo(ClinicalRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_USED => 'Usada',
            self::STATUS_CANCELLED => 'Anulada',
        ];
    }
}
