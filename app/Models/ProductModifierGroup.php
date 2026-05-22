<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModifierGroup extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const TYPE_SINGLE = 'single';
    public const TYPE_MULTIPLE = 'multiple';
    public const TYPE_REMOVE = 'remove';

    protected $fillable = [
        'company_id',
        'product_id',
        'name',
        'selection_type',
        'is_required',
        'min_select',
        'max_select',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_select' => 'integer',
        'max_select' => 'integer',
        'display_order' => 'integer',
    ];

    public static function selectionTypeOptions(): array
    {
        return [
            self::TYPE_SINGLE => 'Elegir una opción',
            self::TYPE_MULTIPLE => 'Elegir varias opciones',
            self::TYPE_REMOVE => 'Quitar ingredientes',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->hasMany(ProductModifierOption::class)->orderBy('display_order')->orderBy('id');
    }
}
