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
    public float $balance = 0.0;
    public int $pariesEnCours = 0;
    public float $totalMise = 0.0;
    public float $gainsFuturs = 0.0;

    // Admin flags & data
    public bool $isAdmin = false;

    // Admin: create player form
    public string $newPlayerName = '';
    public string $newPlayerEmail = '';
    public string $newPlayerPassword = '';
    public float $newPlayerBalance = 1000.0; // euros

    // Admin: injection form
    public float $injectionAmount = 0.0; // euros
    public string $injectionScope = 'all'; // 'all' or 'selected'
    public array $injectionSelected = []; // user ids

    public string $adminMessage = '';
    public string $adminError = '';

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
    }

    public function adminInjectFunds(): void
    {
        $this->resetAdminMessages();
        $this->ensureAdmin();
        $data = $this->validate([
            'injectionAmount' => 'required|numeric|min:0.01|max:1000000',
            'injectionScope' => 'required|in:all,selected',
            'injectionSelected' => 'array',
            'injectionSelected.*' => 'integer|exists:users,id',
        ]);
        $amountCents = (int) round($data['injectionAmount'] * 100);
        if($amountCents <= 0){
            $this->adminError = 'Montant invalide.';
            return;
        }
        // Détermination de la collection de joueurs concernés
        $query = User::query();
        if($data['injectionScope'] === 'selected'){
            if(empty($data['injectionSelected'])){
                $this->adminError = 'Sélectionnez au moins un joueur.';
                return;
            }
            $query->whereIn('id', $data['injectionSelected']);
        }
        $players = $query->get();
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
    }

    protected function resetAdminMessages(): void
    {
        $this->adminMessage = '';
        $this->adminError = '';
    }
}
