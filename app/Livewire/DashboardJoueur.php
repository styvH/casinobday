<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAccount;
use App\Models\GameSession;
use App\Models\User;

class DashboardJoueur extends Component
{
    public float $balance = 0.0;
    public int $pariesEnCours = 0;
    public float $totalMise = 0.0;
    public float $gainsFuturs = 0.0;

    public function mount(): void
    {
        $user = Auth::user();
        if(!$user instanceof User){
            return; // Non authentifié
        }
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
        return view('livewire.dashboard-joueur');
    }
}
