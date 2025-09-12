<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'bet_event_id', 'code', 'label', 'participants_count'
    ];

    public function event()
    {
        return $this->belongsTo(BetEvent::class, 'bet_event_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
