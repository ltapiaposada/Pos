<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyThroughBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    use BelongsToCompanyThroughBranch;

    protected $fillable = [
        'company_id',
        'branch_id',
        'product_id',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'stock' => 'decimal:3',
        'min_stock' => 'decimal:3',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
