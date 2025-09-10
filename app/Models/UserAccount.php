<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','balance_cents'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBalanceAttribute(): float
    {
        return $this->balance_cents / 100.0;
    }
}
