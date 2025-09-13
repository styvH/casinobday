<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\HouseAccount;

/**
 * @property-read UserAccount|null $account
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transaction> $transactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GameSession> $gameSessions
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function account()
    {
        return $this->hasOne(UserAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    /**
     * Crédite le joueur d'un gain (en euros) et journalise une transaction.
     */
    public function win(float $amount): void
    {
        $amountCents = (int) round($amount * 100);
        if ($amountCents <= 0) {
            return;
        }

        DB::transaction(function () use ($amountCents) {
            // Verrouiller/charger le compte, créer si inexistant
            $account = $this->account()->lockForUpdate()->first();
            if (!$account) {
                $account = $this->account()->create(['balance_cents' => 0]);
            }

            $account->balance_cents += $amountCents;
            $account->save();

            // Journaliser
            $this->transactions()->create([
                'type' => 'blackjack_win',
                'amount_cents' => $amountCents,
                'balance_after_cents' => $account->balance_cents,
                'meta' => ['source' => 'blackjack'],
            ]);

            // House pays out when player wins in blackjack
            $house = HouseAccount::singleton();
            $house->debit('blackjack_payout_out', $amountCents, [
                'user_id' => $this->id,
            ]);
        });
    }

    /**
     * Débite une mise (en euros) du solde du joueur et journalise une transaction.
     */
    public function bet(float $amount): void
    {
        $amountCents = (int) round($amount * 100);
        if ($amountCents <= 0) {
            return;
        }

        DB::transaction(function () use ($amountCents) {
            $account = $this->account()->lockForUpdate()->first();
            if (!$account) {
                $account = $this->account()->create(['balance_cents' => 0]);
            }

            $account->balance_cents -= $amountCents;
            $account->save();

            $this->transactions()->create([
                'type' => 'blackjack_bet',
                'amount_cents' => -$amountCents,
                'balance_after_cents' => $account->balance_cents,
                'meta' => ['source' => 'blackjack'],
            ]);

            // House collects stake in blackjack immediately
            $house = HouseAccount::singleton();
            $house->credit('blackjack_bet_in', $amountCents, [
                'user_id' => $this->id,
            ]);
        });
    }
}
