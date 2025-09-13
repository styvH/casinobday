<?php

namespace App\Services;

use App\Models\RewardCycle;
use App\Models\TopTenGrantSetting;
use App\Models\User;
use App\Models\HouseAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RewardService
{
    /**
     * Compute current leaderboard (top N) ordered by balance desc.
     * Returns array of [user_id => balance_cents].
     */
    public static function topByBalance(int $limit = 50): array
    {
        $rows = User::query()
            ->leftJoin('user_accounts', 'user_accounts.user_id', '=', 'users.id')
            ->select('users.id', DB::raw('COALESCE(user_accounts.balance_cents,0) as balance_cents'))
            ->orderByDesc('balance_cents')
            ->limit($limit)
            ->get();
        $out = [];
        foreach ($rows as $r) { $out[(int)$r->id] = (int)$r->balance_cents; }
        return $out;
    }

    /**
     * Dispatch 10% of house balance to Top 3 as 5%/3%/2% of house balance, debiting house and crediting users.
     * Returns detail array.
     */
    public static function dispatchTop3Bonus(): array
    {
        return DB::transaction(function(){
            $house = HouseAccount::singleton();
            // Calculate base on current house balance at time of run
            $houseBalance = (int) $house->balance_cents;
            if ($houseBalance <= 0) {
                return ['ok' => false, 'reason' => 'house_empty'];
            }

            // Compute amounts: 5%, 3%, 2% of house balance
            $amt1 = (int) floor($houseBalance * 0.05);
            $amt2 = (int) floor($houseBalance * 0.03);
            $amt3 = (int) floor($houseBalance * 0.02);
            $total = $amt1 + $amt2 + $amt3;
            if ($total <= 0) { return ['ok' => false, 'reason' => 'tiny_amount']; }

            // Find top 3 users by balance
            $top = array_keys(self::topByBalance(3));
            if (count($top) < 3) { return ['ok' => false, 'reason' => 'not_enough_players']; }

            // Debit house once then distribute credits, with memo lines per user
            $house->debit('top3_bonus_total_out', $total, ['cycle' => true]);

            $amounts = [$amt1, $amt2, $amt3];
            $i = 0; $details = [];
            foreach ($top as $userId) {
                $u = User::find($userId);
                if (!$u) continue;
                $amountCents = $amounts[$i] ?? 0; $i++;
                if ($amountCents <= 0) continue;

                // Credit user account and add transaction row
                $acc = $u->account()->lockForUpdate()->first();
                if (!$acc) { $acc = $u->account()->create(['balance_cents' => 0]); }
                $acc->balance_cents += $amountCents;
                $acc->save();
                $u->transactions()->create([
                    'type' => 'top3_bonus',
                    'amount_cents' => $amountCents,
                    'balance_after_cents' => $acc->balance_cents,
                    'meta' => [
                        'source' => 'reward_cycle',
                        'rank' => $i,
                    ],
                ]);
                $details[] = ['user_id' => $u->id, 'amount_cents' => $amountCents];
            }

            return ['ok' => true, 'total' => $total, 'details' => $details];
        });
    }

    /**
     * Process one tick of a reward cycle: if active and next_run_at <= now, pay top3 and schedule next.
     */
    public static function processRewardCycles(): void
    {
        $now = now();
        $cycles = RewardCycle::whereIn('status', ['pending','active'])
            ->orderBy('id')
            ->lockForUpdate()->get();

        foreach ($cycles as $c) {
            if ($c->status === 'pending') {
                $c->status = 'active';
                $c->started_at = $now;
                // if next_run_at missing, schedule first run after interval
                if (!$c->next_run_at) {
                    $mins = max(1, (int) $c->interval_minutes);
                    $c->next_run_at = $now->clone()->addMinutes($mins);
                }
                $c->save();
            }
            if ($c->status !== 'active') { continue; }
            if ($c->next_run_at && $c->next_run_at->greaterThan($now)) { continue; }

            // Run a payout
            $res = self::dispatchTop3Bonus();

            // Update cycle counters
            $c->repeat_remaining = max(0, (int) $c->repeat_remaining - 1);
            if ($c->repeat_remaining <= 0) {
                $c->status = 'completed';
                $c->completed_at = $now;
                $c->next_run_at = null;
            } else {
                $mins = max(1, (int) $c->interval_minutes);
                $c->next_run_at = $now->clone()->addMinutes($mins);
            }
            $c->save();
        }
    }

    /**
     * If enabled, every interval distribute a fixed amount to top 10 equally.
     */
    public static function processTopTenGrant(): void
    {
        $setting = TopTenGrantSetting::first();
        if (!$setting || !$setting->enabled) { return; }

        $now = now();
        $last = $setting->last_dispatched_at;
        $interval = max(1, (int) $setting->interval_minutes);
        // Throttle: if we have a last dispatch, wait full interval
        if ($last && $last->diffInMinutes($now) < $interval) { return; }
        // If never dispatched yet, wait until started_at + interval
        if (!$last) {
            $start = $setting->started_at ?? $now;
            if ($start->diffInMinutes($now) < $interval) { return; }
        }

        DB::transaction(function () use ($setting, $now) {
            $house = HouseAccount::singleton();
            $amountTotal = (int) $setting->amount_cents; // to distribute per user (100k) or total?
            // Clarification: "la maison envoie 100k à tous les membres du top 10" => 100k each.
            $topIds = array_keys(self::topByBalance(10));
            if (count($topIds) < 1) return;

            foreach ($topIds as $uid) {
                $u = User::find($uid); if (!$u) continue;
                // Debit house and credit each user
                $house->debit('top10_grant_out', $amountTotal, ['user_id' => $uid]);
                $acc = $u->account()->lockForUpdate()->first();
                if (!$acc) { $acc = $u->account()->create(['balance_cents' => 0]); }
                $acc->balance_cents += $amountTotal;
                $acc->save();
                $u->transactions()->create([
                    'type' => 'top10_grant',
                    'amount_cents' => $amountTotal,
                    'balance_after_cents' => $acc->balance_cents,
                    'meta' => ['source' => 'top10_schedule'],
                ]);
            }

            $setting->last_dispatched_at = $now;
            $setting->save();
        });
    }
}
