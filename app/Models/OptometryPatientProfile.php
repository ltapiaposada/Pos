<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptometryPatientProfile extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'birth_date',
        'gender',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'systemic_history',
        'ocular_history',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
