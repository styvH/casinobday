<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','description','created_by','betmaster_id','status','stake_cents','commission_bp','pot_cents','winner_id','state'
    ];

    protected $casts = [
        'state' => 'array',
    ];

    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function betmaster(){ return $this->belongsTo(User::class, 'betmaster_id'); }
    public function winner(){ return $this->belongsTo(User::class, 'winner_id'); }
    public function participants(){ return $this->hasMany(PhysicalGameParticipant::class); }
}
