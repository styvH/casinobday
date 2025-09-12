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
        // Assure qu'un compte existe (création si inexistant)
        $account = $user->account; // relation chargée paresseusement
        if(!$account){
            $account = $user->account()->create(['balance_cents' => 1000000]); // 10 000 € par défaut
        }
        $this->balance = $account->balance_cents / 100;

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
        return view('livewire.dashboard-joueur', [
            'allPlayers' => $allPlayers,
            'balance' => $this->balance,
            'betMin' => $this->betMin,
            'betMax' => $this->betMax,
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
            'injectionAmount' => 'required|numeric|min:0.01|max:1000000',
            'injectionSelected' => 'array|min:1',
            'injectionSelected.*' => 'integer|exists:users,id',
        ]);
        $amountCents = (int) round($data['injectionAmount'] * 100);
        if($amountCents <= 0){
            $this->adminError = 'Montant invalide.';
            return;
        }
        $players = User::whereIn('id', $data['injectionSelected'])->get();
        if($players->isEmpty()){
            $this->adminError = 'Aucun joueur ciblé.';
            return;
        }
        DB::transaction(function() use ($players, $amountCents) {
            foreach($players as $player){
                $account = $player->account;
                if(!$account){
                    $account = $player->account()->create(['balance_cents' => 0]);
                }
                $account->balance_cents += $amountCents;
                $account->save();
                $player->transactions()->create([
                    'type' => 'admin_injection',
                    'amount_cents' => $amountCents,
                    'balance_after_cents' => $account->balance_cents,
                    'meta' => ['reason' => 'injection_globale'],
                ]);
            }
        });
        $this->adminMessage = 'Injection réalisée sur '. $players->count() .' joueur(s).';
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
        $this->balance = $account ? ($account->balance_cents / 100) : 0.0;
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

        $this->balance = $account ? ($account->balance_cents / 100) : 0.0;
    }
}
