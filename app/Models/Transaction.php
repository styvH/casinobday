<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','type','amount_cents','balance_after_cents','reference','meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100.0;
    }

    public function getBalanceAfterAttribute(): float
    {
        return $this->balance_after_cents / 100.0;
    }
}
