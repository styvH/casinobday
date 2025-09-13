<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAccount;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\BetEvent;
use App\Models\BetChoice;
use App\Models\Bet;
use App\Models\HouseAccount;
use App\Models\RewardCycle;
use App\Models\TopTenGrantSetting;
use App\Models\RewardConfig;
use App\Services\RewardService;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Password;

class DashboardJoueur extends Component
{
    public bool $adminModalOpen = false;
    public float $balance = 0.0;
    public int $pariesEnCours = 0;
    public float $totalMise = 0.0;
    public float $gainsFuturs = 0.0;
    public float $betMin = 0.0;
    public float $betMax = 0.0;

    // Admin flags & data
    public bool $isAdmin = false;

    // Admin: create player form
    public string $newPlayerName = '';
    public string $newPlayerEmail = '';
    public string $newPlayerPassword = '';
    public float $newPlayerBalance = 1000.0; // euros

    public string $playerName;

    // Admin: injection form
    public float $injectionAmount = 0.0; // euros
    public array $injectionSelected = []; // user ids

    public string $adminMessage = '';
    public string $adminError = '';

    // Player: donation form
    public ?int $donationRecipientId = null;
    public float $donationAmount = 10000.0;
    public string $donationMessage = '';
    public string $donationError = '';

    // Admin: delete player
    public ?int $deletePlayerId = null;

    // Admin: reward cycles controls
    public int $cycleIntervalMinutes = 60;
    public int $cycleRepeatCount = 1;
    public bool $top10GrantEnabled = false;
    public int $top10IntervalMinutes = 30;
    public int $top3PercentBp = 100; // basis points (1%=100)
    public float $top10AmountEuros = 1000000.0; // default 1,000,000

    protected $listeners = [
        'blackjackWon' => 'onBlackjackWon',
    'blackjackBetPlaced' => 'onBlackjackBetPlaced',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if(!$user instanceof User){
            return; // Non authentifié
        }
	$this->isAdmin = (bool)$user->is_admin;
    $this->playerName = $user->name;
    // Lire le solde réel du compte (en euros via l'accessor) sans créer par défaut
    $account = $user->account()->first();
    $this->balance = $account?->balance ?? 0.0;

    // Blackjack bet limits based on balance
    $min = $this->balance > 10000 ? $this->balance * 0.10 + 1 : 10000;
    $max = $this->balance >= 0 ? max(10000, $this->balance) : max(10000, abs($this->balance)/2);
    $this->betMin = (float) floor($min);
    $this->betMax = (float) floor($max);

        // Sessions de jeu actives
    $activeSessions = $user->gameSessions()->where('status','active');
    // Paris ouverts
    $openBets = $user->bets()->where('status','open');
    $this->pariesEnCours = (clone $activeSessions)->count() + (clone $openBets)->count();
    $this->totalMise = ((clone $activeSessions)->sum('stake_cents') + (clone $openBets)->sum('amount_cents')) / 100;
    $this->gainsFuturs = ((clone $activeSessions)->sum('potential_win_cents') + (clone $openBets)->sum('potential_win_cents')) / 100;
    }

    public function render()
    {
        $allPlayers = collect();
        if($this->isAdmin){
            $allPlayers = User::orderBy('name')->select('id','name')->get();
        }
        // Liste des destinataires possibles pour don (tous sauf l'utilisateur courant)
        $donationPlayers = collect();
        $current = Auth::user();
        if ($current instanceof User) {
            $donationPlayers = User::where('id', '!=', $current->id)
                ->orderBy('name')
                ->select('id','name')
                ->get();
        }
        // Classement des joueurs par solde (balance_cents) décroissant
        $leaderboard = User::query()
            ->leftJoin('user_accounts', 'user_accounts.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('COALESCE(user_accounts.balance_cents, 0) as balance_cents'))
            ->orderByDesc('balance_cents')
            ->orderBy('users.name')
            ->limit(50)
            ->get();
        // Bet events and user's active bets
        // Load events and aggregate total bet amounts per choice for odds
        $betEvents = BetEvent::with(['choices' => function($q){ $q->orderBy('id'); }])
            ->whereIn('status', ['disponible','ferme'])
            ->orderByRaw("FIELD(status, 'disponible','ferme')")
            ->orderBy('id')
            ->limit(100)
            ->get();
        // Map choice_id => total amount (open bets)
        $choiceSums = Bet::query()
            ->select('bet_choice_id', DB::raw('SUM(amount_cents) as sum_amount'))
            ->where('status','open')
            ->groupBy('bet_choice_id')
            ->pluck('sum_amount','bet_choice_id');
        // Attach sums to choices for UI consumption
        foreach ($betEvents as $ev) {
            foreach ($ev->choices as $ch) {
                $ch->total_bet_cents = (int) ($choiceSums[$ch->id] ?? 0);
            }
        }
    $user = Auth::user();
    $userActiveBets = collect();
        if ($user) {
            $userActiveBets = Bet::with(['event','choice'])
                ->where('user_id', $user->id)
                ->where('status','open')
                ->latest()->limit(50)->get();
        }
        // House stats
        $house = HouseAccount::first();
        $houseStats = null;
        if ($house) {
            $houseStats = [
                'starting' => (int) $house->starting_balance_cents,
                'balance' => (int) $house->balance_cents,
                'delta' => (int) ($house->balance_cents - $house->starting_balance_cents),
            ];
        }

        // Build DB-backed history for the current user (transactions => unified timeline)
        $historyItems = collect();
        if ($user) {
            $txs = \App\Models\Transaction::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(200)
                ->get();

            // Collect referenced bet events / choices from transaction meta for nicer labels
            $eventIds = [];
            $choiceIds = [];
            foreach ($txs as $t) {
                $meta = is_array($t->meta) ? $t->meta : [];
                if (!empty($meta['bet_event_id'])) { $eventIds[] = (int) $meta['bet_event_id']; }
                if (!empty($meta['bet_choice_id'])) { $choiceIds[] = (int) $meta['bet_choice_id']; }
            }
            $eventsById = $eventIds ? BetEvent::whereIn('id', array_unique($eventIds))->get()->keyBy('id') : collect();
            $choicesById = $choiceIds ? BetChoice::whereIn('id', array_unique($choiceIds))->get()->keyBy('id') : collect();

            $historyItems = $txs->map(function($t) use ($eventsById, $choicesById) {
                $type = (string) $t->type;
                $meta = is_array($t->meta) ? $t->meta : [];
                $desc = '';
                $category = 'transaction';
                $icon = '💰';

                // Resolve bet event / choice labels if present
                $evTitle = null; $choiceLabel = null; $choiceCode = null;
                if (!empty($meta['bet_event_id'])) {
                    $ev = $eventsById->get((int) $meta['bet_event_id']);
                    if ($ev) { $evTitle = $ev->title ?? ('Événement #'.$ev->id); }
                }
                if (!empty($meta['bet_choice_id'])) {
                    $ch = $choicesById->get((int) $meta['bet_choice_id']);
                    if ($ch) { $choiceLabel = $ch->label ?? null; $choiceCode = $ch->code ?? null; }
                }

                // Categorize and describe
                if (str_starts_with($type, 'blackjack_')) {
                    $category = 'blackjack';
                    $icon = '🃏';
                    if ($type === 'blackjack_bet') { $desc = 'Mise Blackjack'; }
                    elseif ($type === 'blackjack_win') { $desc = 'Gain Blackjack'; }
                    elseif ($type === 'blackjack_loss') { $desc = 'Perte Blackjack'; }
                    else { $desc = ucfirst(str_replace('_',' ',$type)); }
                } elseif (in_array($type, ['bet_place','bet_win','bet_loss'], true)) {
                    $category = 'pari';
                    $icon = '🎲';
                    if ($type === 'bet_place') {
                        $desc = 'Mise sur '.($evTitle ?? 'événement').($choiceLabel ? ' — '.$choiceLabel : ($choiceCode ? ' — '.$choiceCode : ''));
                    } elseif ($type === 'bet_win') {
                        $desc = 'Gain pari sur '.($evTitle ?? 'événement');
                    } else {
                        $desc = 'Perte pari sur '.($evTitle ?? 'événement');
                    }
                } else {
                    // Generic transactions
                    $map = [
                        'deposit' => 'Dépôt',
                        'transfer_in' => 'Transfert entrant',
                        'transfer_out' => 'Transfert sortant',
                        'adjustment' => 'Ajustement',
                        'admin_injection' => 'Crédit administrateur',
                        'admin_withdrawal' => 'Retrait administrateur',
                        'initial_credit' => 'Crédit initial',
                    ];
                    $desc = $map[$type] ?? ucfirst(str_replace('_',' ', $type));
                }

                // created_at in ms for front formatting
                $ts = $t->created_at ? $t->created_at->valueOf() : now()->valueOf();

                return [
                    'id' => (int) $t->id,
                    'type' => $category,
                    'icon' => $icon,
                    'amount' => (float) $t->amount, // euros (signed)
                    'desc' => $desc,
                    'ts' => (int) $ts,
                ];
            });
        }

        // Active reward cycle (for countdown)
        $activeCycle = RewardCycle::where('status','active')->orderBy('id','desc')->first();
        $nextRunAt = $activeCycle?->next_run_at?->toIso8601String();
        $repeatRemaining = $activeCycle?->repeat_remaining ?? 0;

        // Top10 grant setting
        $setting = TopTenGrantSetting::first();
        $this->top10GrantEnabled = (bool) ($setting?->enabled ?? false);
        if ($setting) {
            $this->top10IntervalMinutes = (int) ($setting->interval_minutes ?? 30);
            $this->top10AmountEuros = ((int)($setting->amount_cents ?? 100000000)) / 100.0;
        }
        // RewardConfig for Top-3 percent
        $cfg = RewardConfig::first();
        if ($cfg) { $this->top3PercentBp = (int) ($cfg->top3_percent_bp ?? 100); }
        // Compute next run for Top10 countdown when enabled (always in the future)
        $top10Next = null;
        if ($setting && $setting->enabled) {
            $interval = max(1, (int) $setting->interval_minutes);
            $base = $setting->last_dispatched_at ?: ($setting->started_at ?: now());
            $next = $base->copy()->addMinutes($interval);
            $nowTs = now();
            if ($next->lte($nowTs)) {
                // ensure next is strictly in the future by adding the required number of intervals
                $minutesDiff = $base->diffInMinutes($nowTs);
                $steps = (int) floor($minutesDiff / $interval) + 1;
                $next = $base->copy()->addMinutes($interval * max(1, $steps));
            }
            $top10Next = $next->toIso8601String();
        }

    $rewardActive = (bool) $activeCycle;

    return view('livewire.dashboard-joueur', [
            'allPlayers' => $allPlayers,
            'balance' => $this->balance,
            'betMin' => $this->betMin,
            'betMax' => $this->betMax,
            'leaderboard' => $leaderboard,
            'betEvents' => $betEvents,
            'userActiveBets' => $userActiveBets,
            'houseStats' => $houseStats,
            'historyItems' => $historyItems,
            'donationPlayers' => $donationPlayers,
            'rewardNextRunAt' => $nextRunAt,
            'rewardRepeatsLeft' => $repeatRemaining,
            'rewardActive' => $rewardActive,
            'top10NextRunAt' => $top10Next,
        ]);
    }

    protected function ensureAdmin(): ?User
    {
        $user = Auth::user();
        if(!$user || !$user->is_admin){
            throw ValidationException::withMessages(['unauthorized' => 'Action non autorisée']);
        }
        return $user;
    }

    public function adminCreatePlayer(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $data = $this->validate([
            'newPlayerName' => 'required|string|min:2|max:60',
            'newPlayerEmail' => 'required|email|max:190|unique:users,email',
            'newPlayerPassword' => 'required|string|min:6|max:100',
            'newPlayerBalance' => 'required|numeric|min:0|max:1000000',
        ]);
        DB::transaction(function() use ($data) {
            $user = User::create([
                'name' => $data['newPlayerName'],
                'email' => $data['newPlayerEmail'],
                'password' => Hash::make($data['newPlayerPassword']),
            ]);
            $balanceCents = (int) round($data['newPlayerBalance'] * 100);
            $user->account()->create(['balance_cents' => $balanceCents]);
            // Transaction initiale
            $user->transactions()->create([
                'type' => 'initial_credit',
                'amount_cents' => $balanceCents,
                'balance_after_cents' => $balanceCents,
                'meta' => ['source' => 'admin_create'],
            ]);
        });
        try {
            $createdUser = User::where('email', $data['newPlayerEmail'])->first();
            if ($createdUser) {
                Mail::to($createdUser->email)->send(new WelcomeMail($createdUser));
        // Envoyer un lien de définition de mot de passe (réinitialisation)
        Password::sendResetLink(['email' => $createdUser->email]);
            }
        } catch (\Throwable $e) {
            // ne pas interrompre le flux admin si le mail échoue
        }
    $this->adminMessage = 'Joueur créé avec succès. Un email d\'invitation à définir le mot de passe a été envoyé.';
        $this->reset(['newPlayerName','newPlayerEmail','newPlayerPassword']);
        $this->newPlayerBalance = 1000.0;
    $this->adminModalOpen = true;
    }

    public function adminInjectFunds(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();

        $data = $this->validate([
            'injectionAmount'    => 'required|numeric',
            'injectionSelected'  => 'array|min:1',
            'injectionSelected.*'=> 'integer|exists:users,id',
        ]);

        $amountCents = (int) round($data['injectionAmount'] * 100);
        if ($amountCents === 0) {
            $this->adminError = 'Montant invalide (0 interdit).';
            return;
        }

        $players = User::whereIn('id', $data['injectionSelected'])->get();
        if ($players->isEmpty()) {
            $this->adminError = 'Aucun joueur ciblé.';
            return;
        }

        DB::transaction(function () use ($players, $amountCents) {
            $house = HouseAccount::singleton();
            foreach ($players as $player) {
                $account = $player->account;
                if (!$account) {
                    $account = $player->account()->create(['balance_cents' => 0]);
                }

                if ($amountCents > 0) {
                    // Injection: move funds from house to player
                    $house->debit('admin_injection_out', $amountCents, ['to_user_id' => $player->id]);
                    $account->balance_cents += $amountCents;
                    $account->save();
                    $player->transactions()->create([
                        'type' => 'admin_injection',
                        'amount_cents' => $amountCents,
                        'balance_after_cents' => $account->balance_cents,
                        'meta' => ['reason' => 'admin_adjustment'],
                    ]);
                } else {
                    // Retrait: move funds from player to house
                    $withdraw = abs($amountCents);
                    if ($account->balance_cents < $withdraw) {
                        throw new \RuntimeException('Solde insuffisant pour retrait sur un joueur.');
                    }
                    $account->balance_cents -= $withdraw;
                    $account->save();
                    $player->transactions()->create([
                        'type' => 'admin_withdrawal',
                        'amount_cents' => -$withdraw,
                        'balance_after_cents' => $account->balance_cents,
                        'meta' => ['reason' => 'admin_adjustment'],
                    ]);
                    $house->credit('admin_withdrawal_in', $withdraw, ['from_user_id' => $player->id]);
                }
            }
        });

        // 🔄 rafraîchir le solde de l’admin connecté
        $current = Auth::user();
        if ($current instanceof User) {
            $account = $current->account()->first();
            $this->balance = $account?->balance ?? 0.0;
        }

        $this->adminMessage = ($amountCents > 0 ? 'Injection' : 'Retrait') .
                            ' réalisé(e) sur ' . $players->count() . ' joueur(s).';
        $this->injectionAmount = 0.0;
        $this->injectionSelected = [];
        $this->adminModalOpen = true;
    }

    // --- Reward cycles controls ---
    public function startRewardCycle(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $interval = max(1, (int) $this->cycleIntervalMinutes);
        $repeat = max(1, (int) $this->cycleRepeatCount);

        // Prevent concurrent cycle
        $exists = RewardCycle::whereIn('status',['pending','active'])->exists();
        if ($exists) {
            $this->adminError = 'Un cycle est déjà en cours.';
            $this->adminModalOpen = true;
            return;
        }

        RewardCycle::create([
            'status' => 'pending',
            'interval_minutes' => $interval,
            'repeat_total' => $repeat,
            'repeat_remaining' => $repeat,
            // first run should occur after the selected interval
            'next_run_at' => now()->addMinutes($interval),
            'created_by' => Auth::id(),
        ]);
        $this->adminMessage = 'Cycle de récompense démarré.';
        $this->adminModalOpen = true;
    }

    public function cancelAllRewardCycles(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $now = now();
        $count = RewardCycle::whereIn('status',['pending','active'])->update([
            'status' => 'canceled',
            'canceled_at' => $now,
            'next_run_at' => null,
        ]);
        $this->adminMessage = $count.' cycle(s) annulé(s).';
        $this->adminModalOpen = true;
    }

    public function updateActiveRewardCycle(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $interval = max(1, (int) $this->cycleIntervalMinutes);
        $repeat = max(1, (int) $this->cycleRepeatCount);
        $cycle = RewardCycle::whereIn('status',[ 'pending','active' ])->orderBy('id','desc')->first();
        if (!$cycle) {
            $this->adminError = 'Aucun cycle en cours.';
            $this->adminModalOpen = true;
            return;
        }
        $cycle->interval_minutes = $interval;
        // Reset remaining to not exceed total; if increasing total, adjust remaining proportionally to keep number of runs
        $cycle->repeat_total = $repeat;
        $cycle->repeat_remaining = min($cycle->repeat_remaining, $repeat);
        // Reschedule next payout from now using new interval
        $cycle->next_run_at = now()->addMinutes($interval);
        $cycle->save();
        $this->adminMessage = 'Cycle mis à jour et reprogrammé.';
        $this->adminModalOpen = true;
    }

    public function toggleTop3Cycle(bool $enable): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        if ($enable) {
            // Start if none active/pending
            $exists = RewardCycle::whereIn('status',[ 'pending','active' ])->exists();
            if ($exists) {
                $this->adminError = 'Un cycle est déjà en cours.';
                $this->adminModalOpen = true;
                return;
            }
            $interval = max(1, (int) $this->cycleIntervalMinutes);
            $repeat = max(1, (int) $this->cycleRepeatCount);
            RewardCycle::create([
                'status' => 'pending',
                'interval_minutes' => $interval,
                'repeat_total' => $repeat,
                'repeat_remaining' => $repeat,
                'next_run_at' => now()->addMinutes($interval),
                'created_by' => Auth::id(),
            ]);
            $this->adminMessage = 'Cycle Top-3 activé.';
        } else {
            // Stop: cancel all active/pending
            $now = now();
            $count = RewardCycle::whereIn('status',[ 'pending','active' ])->update([
                'status' => 'canceled',
                'canceled_at' => $now,
                'next_run_at' => null,
            ]);
            $this->adminMessage = ($count > 0) ? 'Cycle Top-3 stoppé.' : 'Aucun cycle à stopper.';
        }
        $this->adminModalOpen = true;
    }

    public function toggleTopTenGrant(bool $enable): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $setting = TopTenGrantSetting::getOrCreate();
        // Ensure interval reflects current admin choice
        $setting->interval_minutes = max(1, (int) $this->top10IntervalMinutes ?: 30);
    // Set amount to 1,000,000 € per user
    $setting->amount_cents = 100000000;
        $setting->enabled = $enable;
        if ($enable) {
            $setting->started_at = now();
            $setting->last_dispatched_at = null; // restart timer cleanly
            $setting->stopped_at = null;
        }
        else { $setting->stopped_at = now(); }
        $setting->save();
        $this->top10GrantEnabled = $setting->enabled;
    $this->adminMessage = $enable ? 'Top10: 1M/30min activé.' : 'Top10: arrêt programmé.';
        $this->adminModalOpen = true;
    }

    public function updateTopTenGrantSettings(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $setting = TopTenGrantSetting::getOrCreate();
        $setting->interval_minutes = max(1, (int) $this->top10IntervalMinutes ?: 30);
        // Persist custom amount (€ -> cents)
        $setting->amount_cents = max(0, (int) round(($this->top10AmountEuros ?? 0) * 100));
        // Reset schedule from now if currently enabled, to restart the timer
        if ($setting->enabled) {
            $setting->started_at = now();
            $setting->last_dispatched_at = null;
        }
        $setting->save();
        $this->adminMessage = 'Paramètres Top10 mis à jour.';
        $this->adminModalOpen = true;
    }

    public function updateTop3Percent(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $data = $this->validate([
            'top3PercentBp' => 'required|integer|min:0|max:10000',
        ]);
        $cfg = RewardConfig::singleton();
        $cfg->top3_percent_bp = (int) $data['top3PercentBp'];
        $cfg->save();
        $this->adminMessage = 'Pourcentage Top-3 mis à jour.';
        $this->adminModalOpen = true;
    }


    public function adminDeletePlayer(): void
    {
        $this->resetAdminMessages();
        $admin = $this->ensureAdmin();
        $data = $this->validate([
            'deletePlayerId' => 'required|integer|exists:users,id'
        ]);
        if($data['deletePlayerId'] === $admin->id){
            $this->adminError = 'Vous ne pouvez pas vous supprimer.';
            return;
        }
        $target = User::find($data['deletePlayerId']);
        if(!$target){
            $this->adminError = 'Joueur introuvable.';
            return;
        }
        DB::transaction(function() use ($target){
            // Suppression logique: supprimer sessions, transactions, compte puis user
            $target->gameSessions()->delete();
            $target->transactions()->delete();
            $target->account()?->delete();
            $target->delete();
        });
        $this->adminMessage = 'Joueur supprimé.';
        $this->deletePlayerId = null;
    $this->adminModalOpen = true;
    }

    protected function resetAdminMessages(): void
    {
        $this->adminMessage = '';
        $this->adminError = '';
    }

    /**
     * Livewire listener: ajoute le gain au solde du joueur courant.
     */
    public function onBlackjackWon(float $amount): void
    {
        $user = Auth::user();
        if(!$user instanceof User){ return; }

        // Sécurité minimale
        if ($amount <= 0) { return; }

        $user->win($amount);

        // Rafraîchir solde affiché
    $account = $user->account()->first();
    $this->balance = $account?->balance ?? 0.0;
    }

    /**
     * Livewire listener: débite la mise du joueur courant.
     */
    public function onBlackjackBetPlaced(float $amount): void
    {
        $user = Auth::user();
        if(!$user instanceof User){ return; }
        if ($amount <= 0) { return; }

        // Server-side limit enforcement
        $account = $user->account()->first();
        $balance = $account ? ($account->balance_cents / 100) : 0.0;
    $min = $balance > 10000 ? $balance * 0.10 : 10000;
    $max = $balance >= 0 ? max(10000, $balance) : max(10000, abs($balance)/2);
        if ($amount < $min || $amount > $max) {
            // reject silently for now (UI already enforces). Could emit event/error if needed.
            return;
        }

    $user->bet($amount);

    // Recharger le compte pour obtenir le solde à jour
    $account = $user->account()->first();
    $this->balance = $account?->balance ?? 0.0;
    }

    /**
     * Place une mise FIXE de 10k € au Blackjack, en ignorant les limites liées au solde.
     * Utilisé pour autoriser un pari spécial de 10k "peu importe son solde".
     */
    public function onBlackjackBet10kFixed(): void
    {
        $user = Auth::user();
        if(!$user instanceof User){ return; }

        // Montant fixe (en euros)
        $amount = 10000.0;

        // Débite directement sans contrôle de min/max
        $user->bet($amount);

        // Rafraîchir le solde affiché
        $account = $user->account()->first();
        $this->balance = $account?->balance ?? 0.0;
    }

    /**
     * Place a bet on an event choice; amount in euros.
     */
    public function placeBet(int $eventId, int $choiceId, float $amount): void
    {
        $user = Auth::user();
        if(!$user instanceof User){
            $this->dispatch('bet-error', message: 'Veuillez vous connecter.');
            return;
        }

        $event = BetEvent::with('choices')->find($eventId);
        if(!$event){
            $this->dispatch('bet-error', message: 'Événement introuvable');
            return;
        }
        // Seuls les événements au statut "disponible" acceptent de nouveaux paris
        if($event->status !== 'disponible'){
            $this->dispatch('bet-error', message: "Cet événement n'est pas disponible pour parier.");
            return;
        }
        $choice = $event->choices->firstWhere('id', $choiceId);
        if(!$choice){
            $this->dispatch('bet-error', message: 'Choix invalide');
            return;
        }

        $amountCents = (int) round($amount * 100);
        if ($amountCents <= 0) {
            $this->dispatch('bet-error', message: 'Montant invalide');
            return;
        }
        // Dynamic limits based on player's balance: min=max(5% solde,10k), max=max(1M,50% solde)
        $accCheck = $user->account()->first();
        $balance = $accCheck ? ($accCheck->balance_cents / 100.0) : 0.0;
        $dynMin = (int) round(max($balance * 0.05, 10000) * 100);
        $dynMax = (int) round(max(1000000, $balance * 0.50) * 100);
        if ($amountCents < $dynMin || $amountCents > $dynMax) {
            $this->dispatch('bet-error', message: 'Montant hors limites dynamiques');
            return;
        }

        // Prevent multiple open bets by the same user on the same event
        $exists = Bet::where('user_id',$user->id)->where('bet_event_id',$event->id)->where('status','open')->exists();
        if ($exists) {
            $this->dispatch('bet-error', message: 'Vous avez déjà un pari ouvert sur cet événement');
            return;
        }

    try {
    DB::transaction(function () use ($user, $event, $choice, $amountCents) {
            // Combined odds formula with commission 10% (fixed)
            $commission = 0.10;
            $totalParticipants = max(1, (int) $event->choices->sum('participants_count'));
            $participantsChoice = max(1, (int) $choice->participants_count);
            // Use current open bets sums as total_mises
            $choiceSums = Bet::query()
                ->select('bet_choice_id', DB::raw('SUM(amount_cents) as sum_amount'))
                ->where('status','open')
                ->whereIn('bet_choice_id', $event->choices->pluck('id'))
                ->groupBy('bet_choice_id')
                ->pluck('sum_amount','bet_choice_id');
            $totalMises = max(1, (int) $choiceSums->sum());
            $miseSurChoix = max(1, (int) ($choiceSums[$choice->id] ?? 0));
            // odds = (total_mises/mise_sur_choix) * (total_joueurs/joueurs_sur_choix) * (1-commission)
            $odds = ($totalMises / $miseSurChoix) * ($totalParticipants / $participantsChoice) * (1.0 - $commission);
            // bounds
            if ($odds < 1.20) { $odds = 1.20; }
            if ($odds > 50.0) { $odds = 50.0; }
            $potential = (int) round($amountCents * $odds);

            // Debit balance via transaction and create Bet
            $account = $user->account()->lockForUpdate()->first();
            if(!$account){ $account = $user->account()->create(['balance_cents' => 0]); }
            $account->balance_cents -= $amountCents;
            $account->save();
            $user->transactions()->create([
                'type' => 'bet_place',
                'amount_cents' => -$amountCents,
                'balance_after_cents' => $account->balance_cents,
                'reference' => 'BET-'.now()->format('YmdHis').'-'.$event->id,
                'meta' => [
                    'bet_event_id' => $event->id,
                    'bet_choice_id' => $choice->id,
                ],
            ]);

            $bet = Bet::create([
                'user_id' => $user->id,
                'bet_event_id' => $event->id,
                'bet_choice_id' => $choice->id,
                'amount_cents' => $amountCents,
                'odds' => $odds,
                'status' => 'open',
                'potential_win_cents' => $potential,
                'reference' => 'B'.$user->id.'E'.$event->id.'C'.$choice->id.'T'.time(),
            ]);

            // Increment participants on the chosen choice
            $choice->increment('participants_count');

            // House collects the full stake at placement; payouts will be debited at settlement
            $house = HouseAccount::singleton();
            $house->credit('bet_stake_in', $amountCents, [
                'bet_event_id' => $event->id,
                'bet_id' => $bet->id,
                'user_id' => $user->id,
            ]);

            // Recalculate dynamic odds for ALL open bets of this event (continuous future gain)
            $event->load('choices');
            // Recompute stake sums per choice (includes the newly created bet)
            $choiceIds = $event->choices->pluck('id');
            $sums = Bet::query()
                ->select('bet_choice_id', DB::raw('SUM(amount_cents) as sum_amount'))
                ->where('status','open')
                ->whereIn('bet_choice_id', $choiceIds)
                ->groupBy('bet_choice_id')
                ->pluck('sum_amount','bet_choice_id');
            // Participants per choice come from choices table
            $totalParticipants = max(1, (int) $event->choices->sum('participants_count'));
            $commission = 0.10;
            // Build odds map per choice id
            $oddsMap = [];
            foreach ($event->choices as $c) {
                $miseChoix = max(1, (int) ($sums[$c->id] ?? 0));
                $totalMises = max(1, (int) $sums->sum());
                $joueursChoix = max(1, (int) $c->participants_count);
                $oddsVal = ($totalMises / $miseChoix) * ($totalParticipants / $joueursChoix) * (1.0 - $commission);
                if ($oddsVal < 1.20) $oddsVal = 1.20; if ($oddsVal > 50.0) $oddsVal = 50.0;
                $oddsMap[$c->id] = $oddsVal;
            }
            // Update potential_win_cents for all open bets on this event
            $openBetsForEvent = Bet::where('bet_event_id', $event->id)->where('status','open')->lockForUpdate()->get();
            foreach ($openBetsForEvent as $ob) {
                $o = (float) ($oddsMap[$ob->bet_choice_id] ?? 1.20);
                $ob->potential_win_cents = (int) round($ob->amount_cents * $o);
                // Optionally keep latest indicative odds on bet (not used for payout)
                $ob->odds = $o;
                $ob->save();
            }

            // Update component computed fields (will be refreshed again after TX)
            $this->pariesEnCours += 1;
            $this->totalMise += $amountCents / 100.0;
            // Don't trust previous $potential; we've just recalculated potentials for all bets
            // We'll recompute gainsFuturs after transaction for accuracy

            // Rafraîchir le bet après recalcul pour envoyer les valeurs mises à jour
            $bet->refresh();
            $this->dispatch(
                'bet-placed',
                betId: $bet->id,
                eventId: $event->id,
                choiceId: $choice->id,
                choiceCode: $choice->code,
                amount: $amountCents / 100.0,
                odds: (float) $bet->odds,
                potential: (int) $bet->potential_win_cents / 100.0,
            );
        });
        } catch (\Throwable $e) {
            $this->dispatch('bet-error', message: "Erreur serveur pendant l'enregistrement du pari");
            return;
        }

        // Refresh balance and dynamic future gains (sum of open bets updated potentials)
        $acc = $user->account()->first();
        $this->balance = $acc?->balance ?? 0.0;
        $this->gainsFuturs = (float) (Bet::where('user_id', $user->id)->where('status','open')->sum('potential_win_cents') / 100);
    }

    /**
     * Transfert d'argent vers un autre joueur (don).
     * Montant en euros. Min = 10 000 €, Max = solde courant. Pas de don à soi-même.
     */
    public function donate(int $recipientId, float $amount, string $message = ''): void
    {
        $donor = Auth::user();
        if(!$donor instanceof User){
            $this->dispatch('donation-error', message: 'Veuillez vous connecter.');
            return;
        }

        if ($recipientId === $donor->id) {
            $this->dispatch('donation-error', message: 'Impossible de vous envoyer un don.');
            return;
        }

        $recipient = User::find($recipientId);
        if(!$recipient){
            $this->dispatch('donation-error', message: 'Destinataire introuvable.');
            return;
        }

        // Règles
        $amount = (float) $amount;
        $amountCents = (int) round($amount * 100);
        $minCents = 10000 * 100; // 10 000 €
        if ($amountCents < $minCents) {
            $this->dispatch('donation-error', message: 'Montant minimum 10 000 €.');
            return;
        }

        try {
            DB::transaction(function () use ($donor, $recipient, $amountCents, $message) {
                // Charger et verrouiller comptes
                $donorAccount = $donor->account()->lockForUpdate()->first();
                if (!$donorAccount) { $donorAccount = $donor->account()->create(['balance_cents' => 0]); }
                $recipientAccount = $recipient->account()->lockForUpdate()->first();
                if (!$recipientAccount) { $recipientAccount = $recipient->account()->create(['balance_cents' => 0]); }

                if ($donorAccount->balance_cents < $amountCents) {
                    throw new \RuntimeException('Solde insuffisant.');
                }

                // Débit donateur
                $donorAccount->balance_cents -= $amountCents;
                $donorAccount->save();
                $donor->transactions()->create([
                    'type' => 'transfer_out',
                    'amount_cents' => -$amountCents,
                    'balance_after_cents' => $donorAccount->balance_cents,
                    'meta' => [
                        'to_user_id' => $recipient->id,
                        'to_user_name' => $recipient->name,
                        'message' => $message,
                    ],
                ]);

                // Crédit destinataire
                $recipientAccount->balance_cents += $amountCents;
                $recipientAccount->save();
                $recipient->transactions()->create([
                    'type' => 'transfer_in',
                    'amount_cents' => $amountCents,
                    'balance_after_cents' => $recipientAccount->balance_cents,
                    'meta' => [
                        'from_user_id' => $donor->id,
                        'from_user_name' => $donor->name,
                        'message' => $message,
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            $this->dispatch('donation-error', message: $e->getMessage() ?: 'Erreur lors du transfert.');
            return;
        }

        // Rafraîchir solde et infos
        $acc = $donor->account()->first();
        $this->balance = $acc?->balance ?? 0.0;

        $this->dispatch('donation-success', amount: $amount, recipientId: $recipient->id, recipientName: $recipient->name);
    }

    /**
     * Create a new bet event from UI quick form.
     */
    public function createBetEvent(string $description, string $choice1, string $choice2, ?string $choice3 = null): void
    {
        $user = Auth::user();
        if(!$user instanceof User){ return; }

        $description = trim($description);
        $choice1 = trim($choice1);
        $choice2 = trim($choice2);
        $choice3 = $choice3 !== null ? trim($choice3) : null;

        if ($description === '' || $choice1 === '' || $choice2 === '') {
            throw ValidationException::withMessages(['create' => 'Description et deux choix minimum requis.']);
        }

        DB::transaction(function () use ($description, $choice1, $choice2, $choice3) {
            $event = BetEvent::create([
                'title' => $description,
                'description' => $description,
                'status' => 'disponible',
                'margin' => 0.90,
                'min_bet_cents' => 1000000,  // 10 000 €
                'max_bet_cents' => 10000000, // 100 000 €
            ]);

            $choices = [
                ['code' => 'A', 'label' => $choice1],
                ['code' => 'B', 'label' => $choice2],
            ];
            if ($choice3) { $choices[] = ['code' => 'C', 'label' => $choice3]; }

            foreach ($choices as $c) {
                BetChoice::create([
                    'bet_event_id' => $event->id,
                    'code' => $c['code'],
                    'label' => $c['label'],
                    'participants_count' => 0,
                ]);
            }
        });
    }
}
