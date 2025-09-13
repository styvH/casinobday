<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'top3_percent_bp', // basis points (1% = 100)
    ];

    public static function singleton(): self
    {
        return static::first() ?? static::create([
            'top3_percent_bp' => 100, // default 1%
        ]);
    }
}
