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
        $this->pariesEnCours = (clone $activeSessions)->count();
        $this->totalMise = (clone $activeSessions)->sum('stake_cents') / 100;
        $this->gainsFuturs = (clone $activeSessions)->sum('potential_win_cents') / 100;
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
        return view('livewire.dashboard-joueur', [
            'allPlayers' => $allPlayers,
            'balance' => $this->balance,
            'betMin' => $this->betMin,
            'betMax' => $this->betMax,
            'leaderboard' => $leaderboard,
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
        if ($current) {
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
}
