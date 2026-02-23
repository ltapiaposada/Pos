<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'status',
        'user_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

