<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected static function booted(): void
    {
        $invalidate = static function (): void {
            StorefrontCache::bumpProductsVersion();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'parent_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
