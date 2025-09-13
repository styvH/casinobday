<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'status', // pending|active|completed|canceled
        'interval_minutes',
        'repeat_total',
        'repeat_remaining',
        'next_run_at',
        'started_at',
        'completed_at',
        'canceled_at',
        'created_by',
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];
}
