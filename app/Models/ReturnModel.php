<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyThroughBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;
    use BelongsToCompanyThroughBranch;

    protected $table = 'returns';

    protected $fillable = [
        'company_id',
        'sale_id',
        'branch_id',
        'user_id',
        'reason',
        'total',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
