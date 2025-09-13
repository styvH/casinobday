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
    // Admin: delete player
    public ?int $deletePlayerId = null;

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
            ->orderByRaw("FIELD(status, 'disponible','annonce','en_cours','ferme')")
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

        return view('livewire.dashboard-joueur', [
            'allPlayers' => $allPlayers,
            'balance' => $this->balance,
            'betMin' => $this->betMin,
            'betMax' => $this->betMax,
            'leaderboard' => $leaderboard,
            'betEvents' => $betEvents,
            'userActiveBets' => $userActiveBets,
            'houseStats' => $houseStats,
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
        $this->adminMessage = 'Joueur créé avec succès.';
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
            foreach ($players as $player) {
                $account = $player->account;
                if (!$account) {
                    $account = $player->account()->create(['balance_cents' => 0]);
                }

                $newBalance = $account->balance_cents + $amountCents;

                $account->balance_cents = $newBalance;
                $account->save();

                $player->transactions()->create([
                    'type' => $amountCents > 0 ? 'admin_injection' : 'admin_withdrawal',
                    'amount_cents' => $amountCents,
                    'balance_after_cents' => $account->balance_cents,
                    'meta' => ['reason' => 'admin_adjustment'],
                ]);
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
        if(!$user instanceof User){ return; }

        $event = BetEvent::with('choices')->find($eventId);
        if(!$event){ throw ValidationException::withMessages(['bet' => 'Événement introuvable']); }
        if(!in_array($event->status, ['disponible','en_cours','annonce'])){
            throw ValidationException::withMessages(['bet' => 'Événement fermé']);
        }
        $choice = $event->choices->firstWhere('id', $choiceId);
        if(!$choice){ throw ValidationException::withMessages(['bet' => 'Choix invalide']); }

        $amountCents = (int) round($amount * 100);
        if ($amountCents <= 0) {
            throw ValidationException::withMessages(['amount' => 'Montant invalide']);
        }
        // Dynamic limits based on player's balance: min=max(5% solde,10k), max=max(1M,50% solde)
        $accCheck = $user->account()->first();
        $balance = $accCheck ? ($accCheck->balance_cents / 100.0) : 0.0;
        $dynMin = (int) round(max($balance * 0.05, 10000) * 100);
        $dynMax = (int) round(max(1000000, $balance * 0.50) * 100);
        if ($amountCents < $dynMin || $amountCents > $dynMax) {
            throw ValidationException::withMessages(['amount' => 'Hors limites dynamiques']);
        }

        // Prevent multiple open bets by the same user on the same event
        $exists = Bet::where('user_id',$user->id)->where('bet_event_id',$event->id)->where('status','open')->exists();
        if ($exists) {
            throw ValidationException::withMessages(['bet' => 'Pari déjà placé sur cet événement']);
        }

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

            // House commission: (1 - margin) of the stake
            $commission = (int) round($amountCents * (1 - (float) $event->margin));
            if ($commission > 0) {
                $house = HouseAccount::singleton();
                $house->credit('bet_commission_in', $commission, [
                    'bet_event_id' => $event->id,
                    'bet_id' => $bet->id,
                    'user_id' => $user->id,
                ]);
            }

            // Update component computed fields
            $this->pariesEnCours += 1;
            $this->totalMise += $amountCents / 100.0;
            $this->gainsFuturs += $potential / 100.0;
        });

        // Refresh balance
        $acc = $user->account()->first();
        $this->balance = $acc?->balance ?? 0.0;
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
