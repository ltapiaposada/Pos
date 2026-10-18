<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Customer extends Model
{
    use BelongsToCompany;
    use HasFactory, SoftDeletes;

    public const TYPE_PERSON = 'person';

    public const TYPE_COMPANY = 'company';

    public const TYPE_SUPPLIER = 'supplier';

    protected $fillable = [
        'company_id',
        'external_customer_uuid',
        'user_id',
        'name',
        'document',
        'email',
        'phone',
        'address',
        'password',
        'contact_type',
        'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public static function supportsContactType(): bool
    {
        static $hasContactType = null;

        if ($hasContactType === null) {
            $hasContactType = Schema::hasColumn('customers', 'contact_type');
        }

        return $hasContactType;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function optometryProfile()
    {
        return $this->hasOne(OptometryPatientProfile::class);
    }

    public function clinicalRecords()
    {
        return $this->hasMany(ClinicalRecord::class);
    }

    public function medicalOrders()
    {
        return $this->hasMany(MedicalOrder::class);
    }
}
