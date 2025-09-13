<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopTenGrantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'enabled',
        'amount_cents',
        'interval_minutes',
        'started_at',
        'stopped_at',
        'last_dispatched_at',
        'created_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'last_dispatched_at' => 'datetime',
    ];

    public static function getOrCreate(): self
    {
        return static::first() ?? static::create([
            'enabled' => false,
            'amount_cents' => 100000000, // 1,000,000 € by default
            'interval_minutes' => 30,
        ]);
    }
}
