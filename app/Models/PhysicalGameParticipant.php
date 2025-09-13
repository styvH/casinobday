<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalGameParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_game_id','user_id','status','confirmed','picked_winner_id','picked_canceled'
    ];

    public function game(){ return $this->belongsTo(PhysicalGame::class, 'physical_game_id'); }
    public function user(){ return $this->belongsTo(User::class); }
    public function pickedWinner(){ return $this->belongsTo(User::class, 'picked_winner_id'); }
}
