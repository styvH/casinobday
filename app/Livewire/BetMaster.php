<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\BetEvent;
use App\Models\BetChoice;
use App\Models\Bet;
use App\Models\User;
use App\Models\HouseAccount;
use App\Models\PhysicalGame;
use App\Models\PhysicalGameParticipant;

class BetMaster extends Component
{
    public string $password = '';
    public bool $unlocked = false;
    public string $flashMessage = '';
    public string $flashError = '';

    // For UI interactions
    public ?int $winnerChoiceId = null;
    public string $statusFilter = 'all'; // all | disponible | annonce | en_cours | ferme | cloture

    // Confirm modal state
    public bool $confirmModalOpen = false;
    public ?int $confirmEventId = null;
    public ?int $confirmChoiceId = null;
    public ?string $confirmEventTitle = null;
    public ?string $confirmChoiceLabel = null;
    public ?string $confirmChoiceCode = null; // A/B/C
    public ?string $confirmSelectCode = null; // user's selection in dropdown

    protected function isAdmin(): bool
    {
        $u = Auth::user();
        return $u instanceof User && (bool) $u->is_admin;
    }

    protected function sessionKey(): string
    {
        $uid = Auth::id() ?: 'guest';
        return 'betmaster_unlocked_until_'.$uid;
    }

    public function mount(): void
    {
        $this->unlocked = $this->checkUnlocked();
    }

    protected function checkUnlocked(): bool
    {
        if ($this->isAdmin()) return true;
        $ts = Session::get($this->sessionKey());
        if (!$ts) return false;
        return now()->timestamp < (int) $ts;
    }

    public function unlock(): void
    {
        $this->flashMessage = '';
        $this->flashError = '';
        if ($this->isAdmin()) {
            $this->unlocked = true;
            return;
        }
        if ($this->password !== 'BetMasterPass10$') {
            $this->flashError = 'Mot de passe invalide';
            $this->unlocked = false;
            return;
        }
        $until = now()->addMinutes(30)->timestamp;
        Session::put($this->sessionKey(), $until);
        $this->unlocked = true;
        $this->password = '';
        $this->flashMessage = "Accès déverrouillé pour 30 minutes.";
    }

    protected function ensureUnlocked(): void
    {
        if (!$this->checkUnlocked()) {
            throw ValidationException::withMessages(['locked' => 'Accès verrouillé']);
        }
    }

    public function toggleEventStatus(int $eventId): void
    {
        $this->ensureUnlocked();
        $event = BetEvent::find($eventId);
        if (!$event) return;
        if ($event->status === 'cloture') { // already closed
            $this->flashError = "Événement déjà clôturé";
            return;
        }
        $new = $event->status === 'ferme' ? 'disponible' : 'ferme';
        $event->status = $new;
        $event->save();
        $this->flashMessage = "Statut de l'événement #{$event->id} => {$new}.";
    }

    public function openConfirmModal(int $eventId, int $choiceId): void
    {
        $this->ensureUnlocked();
        $this->flashError = '';
        $this->flashMessage = '';
        $event = BetEvent::with('choices')->find($eventId);
        if(!$event){ return; }
        if ($event->status === 'cloture') {
            $this->flashError = 'Événement déjà clôturé';
            return;
        }
        $choice = $event->choices->firstWhere('id', $choiceId);
        if(!$choice){
            $this->flashError = 'Choix invalide';
            return;
        }
        $this->confirmEventId = $event->id;
        $this->confirmChoiceId = $choice->id;
        $this->confirmEventTitle = $event->title;
        $this->confirmChoiceLabel = $choice->label;
        $this->confirmChoiceCode = $choice->code; // expected A/B/C
        $this->confirmSelectCode = null;
        $this->confirmModalOpen = true;
    }

    public function cancelConfirmModal(): void
    {
        $this->confirmModalOpen = false;
        $this->confirmEventId = null;
        $this->confirmChoiceId = null;
        $this->confirmEventTitle = null;
        $this->confirmChoiceLabel = null;
        $this->confirmChoiceCode = null;
        $this->confirmSelectCode = null;
    }

    public function confirmSettlement(): void
    {
        $this->ensureUnlocked();
        if(!$this->confirmEventId || !$this->confirmChoiceId || !$this->confirmChoiceCode){
            $this->flashError = 'Données de confirmation manquantes';
            return;
        }
        if (strtoupper($this->confirmSelectCode ?? '') !== strtoupper($this->confirmChoiceCode ?? '')) {
            $this->flashError = 'Le code sélectionné ne correspond pas';
            return;
        }
        $eventId = $this->confirmEventId;
        $choiceId = $this->confirmChoiceId;
        $this->cancelConfirmModal();
        $this->settleEvent($eventId, $choiceId);
    }

    public function settleEvent(int $eventId, int $winningChoiceId): void
    {
        $this->ensureUnlocked();
        $event = BetEvent::with('choices')->find($eventId);
        if (!$event) return;
        $winner = $event->choices->firstWhere('id', $winningChoiceId);
        if (!$winner) {
            $this->flashError = 'Choix gagnant invalide';
            return;
        }

        DB::transaction(function () use ($event, $winner) {
            // Lock open bets and settle
            $openBets = Bet::where('bet_event_id', $event->id)
                ->where('status', 'open')
                ->lockForUpdate()->get();
            $house = HouseAccount::singleton();

            foreach ($openBets as $bet) {
                if ($bet->bet_choice_id === $winner->id) {
                    // Winner: credit user with potential_win_cents
                    $amount = (int) $bet->potential_win_cents;
                    if ($amount > 0) {
                        // Credit player account and log transaction
                        $account = $bet->user->account()->lockForUpdate()->first();
                        if (!$account) {
                            $account = $bet->user->account()->create(['balance_cents' => 0]);
                        }
                        $account->balance_cents += $amount;
                        $account->save();
                        $bet->user->transactions()->create([
                            'type' => 'bet_win_payout',
                            'amount_cents' => $amount,
                            'balance_after_cents' => $account->balance_cents,
                            'reference' => 'BETWIN-'.$bet->id,
                            'meta' => [
                                'bet_id' => $bet->id,
                                'bet_event_id' => $event->id,
                                'bet_choice_id' => $winner->id,
                            ],
                        ]);
                        // House pays out the winnings to the player
                        $house->debit('bet_payout_out', $amount, [
                            'bet_id' => $bet->id,
                            'bet_event_id' => $event->id,
                            'user_id' => $bet->user_id,
                        ]);
                        // Optionally, record a memo for house ledger without affecting balance
                        // HouseAccount::singleton()->memo('bet_payout_memo', $amount, [
                        //     'bet_id' => $bet->id,
                        //     'bet_event_id' => $event->id,
                        //     'user_id' => $bet->user_id,
                        // ]);
                    }
                    $bet->status = 'won';
                    $bet->save();
                } else {
                    $bet->status = 'lost';
                    $bet->save();
                }
            }

            $event->status = 'cloture'; // clôturé
            $event->save();
        });

        $this->flashMessage = "Événement #{$event->id} clôturé. Gains redistribués.";
    }

    public function render()
    {
    $base = BetEvent::with(['choices' => function($q){ $q->orderBy('id'); }])
            ->orderByDesc('id');

        if ($this->statusFilter === 'all') {
            $events = (clone $base)->where('status','!=','cloture')->limit(100)->get();
        } elseif ($this->statusFilter === 'cloture') {
            $events = (clone $base)->where('status','cloture')->limit(100)->get();
        } else {
            $events = (clone $base)->where('status',$this->statusFilter)->limit(100)->get();
        }

        $clotureEvents = collect();
        if ($this->statusFilter !== 'cloture') {
            $clotureEvents = (clone $base)->where('status','cloture')->limit(100)->get();
        }

        // Physical games for admin management
        $pgames = PhysicalGame::with(['participants.user'])->orderByDesc('id')->limit(100)->get();

        return view('livewire.bet-master', [
            'events' => $events,
            'clotureEvents' => $clotureEvents,
            'isUnlocked' => $this->checkUnlocked(),
            'isAdmin' => $this->isAdmin(),
            'pgames' => $pgames,
        ]);
    }

    // Admin direct resolve/cancel for physical games
    public function settlePhysicalGame(int $gameId, ?int $winnerUserId = null, bool $cancel=false): void
    {
        if(!$this->checkUnlocked()) { $this->flashError = 'Accès verrouillé'; return; }
        $game = PhysicalGame::with('participants')->find($gameId);
        if(!$game){ $this->flashError = 'Partie introuvable'; return; }
        // Reuse DashboardJoueur logic would be ideal, but replicate minimal payout/refund here
        if($cancel){
            if($game->status !== 'active'){ $this->flashError = 'Partie non active'; return; }
            DB::transaction(function() use ($game){
                foreach ($game->participants as $p) {
                    $u = $p->user; if(!$u) continue;
                    $acc = $u->account()->lockForUpdate()->first();
                    if(!$acc){ $acc = $u->account()->create(['balance_cents'=>0]); }
                    $acc->balance_cents += (int) $game->stake_cents;
                    $acc->save();
                    $u->transactions()->create([
                        'type' => 'physical_refund',
                        'amount_cents' => (int) $game->stake_cents,
                        'balance_after_cents' => $acc->balance_cents,
                        'reference' => 'PG-'.$game->id,
                        'meta' => ['physical_game_id' => $game->id],
                    ]);
                }
                $game->status = 'canceled';
                $game->save();
            });
            $this->flashMessage = 'Partie annulée';
            return;
        }
        if(!$winnerUserId){ $this->flashError = 'Gagnant requis'; return; }
        if(!$game->participants->firstWhere('user_id', $winnerUserId)){ $this->flashError = 'Gagnant invalide'; return; }
        DB::transaction(function() use ($game, $winnerUserId){
            $winner = User::find($winnerUserId);
            if(!$winner) throw new \RuntimeException('Winner missing');
            $acc = $winner->account()->lockForUpdate()->first();
            if(!$acc){ $acc = $winner->account()->create(['balance_cents'=>0]); }
            $acc->balance_cents += (int) $game->pot_cents;
            $acc->save();
            $winner->transactions()->create([
                'type' => 'physical_win',
                'amount_cents' => (int) $game->pot_cents,
                'balance_after_cents' => $acc->balance_cents,
                'reference' => 'PG-'.$game->id,
                'meta' => ['physical_game_id' => $game->id],
            ]);
            $game->winner_id = $winner->id;
            $game->status = 'completed';
            $game->save();
        });
        $this->flashMessage = 'Partie réglée';
    }
}
