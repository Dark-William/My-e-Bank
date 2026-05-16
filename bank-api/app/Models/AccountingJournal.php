<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingJournal extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'type', 'amount', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
