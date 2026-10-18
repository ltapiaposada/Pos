<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingPeriodClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_date',
        'to_date',
        'entry_date',
        'description',
        'net_income',
        'journal_entry_id',
        'user_id',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'entry_date' => 'date',
        'net_income' => 'float',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
