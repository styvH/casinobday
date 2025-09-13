<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HouseAccount extends Model
{
    use HasFactory;

    protected $fillable = ['starting_balance_cents','balance_cents'];

    public function transactions()
    {
        return $this->hasMany(HouseTransaction::class);
    }

    public static function singleton(): self
    {
        return DB::transaction(function(){
            $acc = self::lockForUpdate()->first();
            if(!$acc){
                $acc = self::create([
                    'starting_balance_cents' => 100000000000, // 1,000,000,000 €
                    'balance_cents' => 100000000000,
                ]);
            }
            return $acc;
        });
    }

    public function credit(string $type, int $amountCents, array $meta = []): void
    {
        if ($amountCents <= 0) return;
        DB::transaction(function () use ($type, $amountCents, $meta) {
            $this->lockForUpdate();
            $this->balance_cents += $amountCents;
            $this->save();
            $this->transactions()->create([
                'type' => $type,
                'amount_cents' => $amountCents,
                'balance_after_cents' => $this->balance_cents,
                'meta' => $meta,
            ]);
        });
    }

    public function debit(string $type, int $amountCents, array $meta = []): void
    {
        if ($amountCents <= 0) return;
        DB::transaction(function () use ($type, $amountCents, $meta) {
            $this->lockForUpdate();
            $this->balance_cents -= $amountCents;
            $this->save();
            $this->transactions()->create([
                'type' => $type,
                'amount_cents' => -$amountCents,
                'balance_after_cents' => $this->balance_cents,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Record a non-cash event in the house ledger without changing cash balance.
     */
    public function memo(string $type, int $amountCents, array $meta = []): void
    {
        DB::transaction(function () use ($type, $amountCents, $meta) {
            $this->lockForUpdate();
            $this->transactions()->create([
                'type' => $type,
                'amount_cents' => $amountCents,
                'balance_after_cents' => $this->balance_cents,
                'meta' => $meta,
            ]);
        });
    }
}
