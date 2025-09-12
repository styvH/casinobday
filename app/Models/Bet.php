<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','bet_event_id','bet_choice_id','amount_cents','odds','status','potential_win_cents','reference'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(BetEvent::class, 'bet_event_id');
    }

    public function choice()
    {
        return $this->belongsTo(BetChoice::class, 'bet_choice_id');
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100.0;
    }

    public function getPotentialWinAttribute(): float
    {
        return $this->potential_win_cents / 100.0;
    }
}
