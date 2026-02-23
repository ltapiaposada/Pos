<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_PERSON = 'person';
    public const TYPE_COMPANY = 'company';
    public const TYPE_SUPPLIER = 'supplier';

    protected $fillable = [
        'user_id',
        'name',
        'document',
        'email',
        'phone',
        'address',
        'contact_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
