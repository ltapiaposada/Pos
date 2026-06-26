<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalRecord extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'created_by_user_id',
        'examined_at',
        'reason_for_consultation',
        'medical_history',
        'ocular_history',
        'examination',
        'diagnosis',
        'treatment_plan',
        'observations',
        'professional_name',
        'professional_license',
    ];

    protected $casts = [
        'examined_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function medicalOrders()
    {
        return $this->hasMany(MedicalOrder::class);
    }
}
