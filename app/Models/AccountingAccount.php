<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'nature',
        'parent_account_id',
        'level',
        'is_postable',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_postable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    public const NATURE_DEBIT = 'debit';
    public const NATURE_CREDIT = 'credit';

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function parent()
    {
        return $this->belongsTo(AccountingAccount::class, 'parent_account_id');
    }

    public function children()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_account_id');
    }
}
