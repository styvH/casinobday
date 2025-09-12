<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'status', 'margin', 'min_bet_cents', 'max_bet_cents'
    ];

    public function choices()
    {
        return $this->hasMany(BetChoice::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function minBet(): float { return $this->min_bet_cents / 100.0; }
    public function maxBet(): float { return $this->max_bet_cents / 100.0; }
}
