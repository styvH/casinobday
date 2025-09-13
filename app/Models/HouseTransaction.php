<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['house_account_id','type','amount_cents','balance_after_cents','meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function house()
    {
        return $this->belongsTo(HouseAccount::class, 'house_account_id');
    }
}
