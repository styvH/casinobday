<div>
<style>
/* Masquer la scrollbar native et afficher une barre rouge au scroll */
.classement-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #b91c1c transparent;
}
.classement-scrollbar::-webkit-scrollbar {
    width: 8px;
    background: transparent;
}
.classement-scrollbar::-webkit-scrollbar-thumb {
    background: #b91c1c;
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s;
}
.classement-scrollbar:hover::-webkit-scrollbar-thumb,
.classement-scrollbar:active::-webkit-scrollbar-thumb,
.classement-scrollbar:focus::-webkit-scrollbar-thumb {
    opacity: 1;
}
</style>
@php /* Les anciennes valeurs fictives ont été remplacées par des données réelles */ @endphp

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-900 via-black to-red-700 text-white">
@php /* Le composant Livewire fournit $leaderboard: top 50 users triés par solde */ @endphp
    <!-- Bouton mobile afficher classement -->
    <button id="toggleClassementMobile" class="md:hidden fixed bottom-4 right-4 z-50 bg-red-700 hover:bg-red-800 text-white font-semibold px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
        🏆 <span>Classement</span>
    </button>
    <!-- Classement flottant (masqué sur mobile jusqu'au clic) -->
    <div id="classementPanel" class="hidden md:flex fixed top-1/2 right-1/2 md:right-8 z-50 transform -translate-y-1/2 translate-x-1/2 md:translate-x-0 items-center w-11/12 md:w-auto">
    <div class="bg-black bg-opacity-80 border-l-4 border-red-800 rounded-xl md:rounded-l-xl shadow-xl w-full md:w-80 max-h-[70vh] overflow-y-auto flex flex-col classement-scrollbar" style="cursor: pointer;">
            <div class="px-6 py-4 flex items-center justify-center">
                <span class="text-xl font-bold text-red-400">🏆 Classement Top 50</span>
            </div>
            <ul class="px-6 py-2">
                @foreach(($leaderboard ?? collect()) as $index => $u)
                    @if($index < 10)
                        @php $lb_balance = ((int)($u->balance_cents ?? 0)) / 100; @endphp
                        <li class="flex items-center justify-between mb-2 p-2 rounded-lg transition
                            @if($index == 0) bg-yellow-400 bg-opacity-30 font-extrabold @elseif($index == 1) bg-gray-300 bg-opacity-30 font-bold @elseif($index == 2) bg-orange-400 bg-opacity-30 font-bold @elseif($index < 10) bg-red-900 bg-opacity-40 font-semibold text-white border border-red-700 @endif
                            @if($index < 3) shadow-lg @endif
                            @if($index < 10) ring-2 ring-red-500 @endif">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold">{{ $index+1 }}</span>
                                @if($index == 0)
                                    <span class="text-2xl">🥇</span>
                                @elseif($index == 1)
                                    <span class="text-2xl">🥈</span>
                                @elseif($index == 2)
                                    <span class="text-2xl">🥉</span>
                                @endif
                                <span>{{ $u->name }}</span>
                            </div>
                            <span class="font-mono">{{ number_format($lb_balance, 0, ',', ' ') }} €</span>
                        </li>
                    @endif
                @endforeach
            </ul>
            <details class="px-6 py-2">
                <summary class="cursor-pointer text-red-400 hover:underline">Voir le reste du classement</summary>
                <ul class="mt-2">
                    @foreach(($leaderboard ?? collect()) as $index => $u)
                        @if($index >= 10)
                            @php $lb_balance = ((int)($u->balance_cents ?? 0)) / 100; @endphp
                            <li class="flex items-center justify-between mb-1 p-1 rounded transition bg-black bg-opacity-30 text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold">{{ $index+1 }}</span>
                                    <span>{{ $u->name }}</span>
                                </div>
                                <span class="font-mono text-xs">{{ number_format($lb_balance, 0, ',', ' ') }} €</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </details>
        </div>
    </div>
    @if(($houseStats ?? null))
    <!-- Bouton mobile afficher Maison -->
    <button id="toggleMaisonMobile" class="md:hidden fixed bottom-4 left-4 z-50 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
        🏦 <span>Maison</span>
    </button>
    <!-- Panel Maison flottant (masqué sur mobile jusqu'au clic) -->
    <div id="housePanel" class="hidden md:flex fixed top-1/2 left-1/2 md:left-8 z-50 transform -translate-y-1/2 -translate-x-1/2 md:translate-x-0 items-center w-11/12 md:w-auto">
        <div class="bg-black bg-opacity-80 border-r-4 border-emerald-800 rounded-xl md:rounded-r-xl shadow-xl w-full md:w-80 max-h-[50vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 flex items-center justify-center">
                <span class="text-xl font-bold text-emerald-400">🏦 Maison</span>
            </div>
            @php
                $houseStarting = (int)(($houseStats['starting'] ?? 0) / 100);
                $houseBalance = (int)(($houseStats['balance'] ?? 0) / 100);
                $houseDelta = (int)(($houseStats['delta'] ?? 0) / 100);
            @endphp
            <ul class="px-6 pb-4 space-y-2 text-sm">
                <li class="flex justify-between"><span class="text-gray-300">Capital initial</span><span class="font-mono">{{ number_format($houseStarting, 0, ',', ' ') }} €</span></li>
                <li class="flex justify-between"><span class="text-gray-300">Solde actuel</span><span class="font-mono">{{ number_format($houseBalance, 0, ',', ' ') }} €</span></li>
                <li class="flex justify-between"><span class="text-gray-300">P/L</span>
                    <span class="font-mono {{ $houseDelta >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($houseDelta, 0, ',', ' ') }} €</span>
                </li>
            </ul>
        </div>
    </div>
    @endif
    <main class="relative w-full max-w-3xl bg-black bg-opacity-80 rounded-xl shadow-lg p-4 md:p-8 border-4 border-red-800 mt-20 md:mt-0">
        <!-- Bouton Paramètres icône seule -->
        <div class="absolute top-2 right-2 md:top-3 md:right-3 flex items-center gap-2">
            <button id="logoutConfirmBtn" aria-label="Déconnexion" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-lg bg-black/60 border border-red-700 text-red-300 hover:text-white hover:border-red-500 hover:bg-red-800/40 transition shadow focus:outline-none focus:ring-2 focus:ring-red-600/60">
                🔒
            </button>
            <button id="openSettingsBtn" aria-label="Paramètres" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-lg bg-black/60 border border-red-700 text-red-300 hover:text-white hover:border-red-500 hover:bg-red-800/40 transition shadow focus:outline-none focus:ring-2 focus:ring-red-600/60">
                ⚙️
            </button>
        </div>
        <h2 class="text-2xl md:text-4xl font-bold mb-4 md:mb-6 text-center text-red-500">Dashboard {{ $playerName }}</h2>
        <div class="flex flex-col items-center mb-6 md:mb-8">
            <div class="text-xl md:text-2xl font-semibold mb-1 md:mb-2">Solde du compte</div>
            <div class="flex items-center gap-3 mb-3 md:mb-4">
                
                <div class="text-3xl md:text-5xl font-extrabold text-red-400">{{ number_format($balance, 0, ',', ' ') }} €</div>
                <button id="openDonationBtn" class="group relative text-xs md:text-sm px-3 py-2 md:py-2 bg-gradient-to-r from-red-800 to-red-600 hover:from-red-700 hover:to-red-500 rounded-lg font-semibold shadow ring-1 ring-red-500/60 hover:ring-red-300 transition overflow-hidden">
                    <span class="relative z-10 flex items-center gap-1">
                        💝 <span>Faire un don</span>
                    </span>
                    <span class="absolute inset-0 opacity-0 group-hover:opacity-15 bg-white/10 transition"></span>
                </button>
            </div>
            <div class="grid grid-cols-3 gap-3 md:gap-6 mb-4 md:mb-6 w-full">
                <button id="btnPariesEnCours" type="button" class="bg-red-900 bg-opacity-80 rounded-lg p-2 md:p-4 text-center focus:outline-none focus:ring-2 focus:ring-red-600 hover:bg-red-800 transition">
                    <div class="text-xs md:text-lg font-bold">Paries en cours</div>
                    <div class="text-xl md:text-3xl">{{ $pariesEnCours }}</div>
                </button>
                <div class="bg-black bg-opacity-80 rounded-lg p-2 md:p-4 text-center border border-red-700">
                    <div class="text-xs md:text-lg font-bold">Total Mise</div>
                    <div class="text-xl md:text-3xl">{{ number_format($totalMise, 0, ',', ' ') }} €</div>
                </div>
                <div class="bg-red-900 bg-opacity-80 rounded-lg p-2 md:p-4 text-center">
                    <div class="text-xs md:text-lg font-bold">Gains futurs</div>
                    <div class="text-xl md:text-3xl text-green-400">{{ number_format($gainsFuturs, 0, ',', ' ') }} €</div>
                </div>
            </div>
        </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-8 mb-6 md:mb-8">
            <button id="openPariModalBtn" class="px-4 md:px-6 py-3 md:py-4 bg-red-700 hover:bg-red-900 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">🎲</span>
                Lancer un pari
            </button>
            <button id="openInscriptionPhysiqueBtn" class="px-4 md:px-6 py-3 md:py-4 bg-black hover:bg-red-800 text-white font-bold rounded-xl shadow-lg border border-red-700 transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">📝</span>
                Inscription partie physique
            </button>
            <button id="openHistoryBtn" class="px-4 md:px-6 py-3 md:py-4 bg-red-900 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">📜</span>
                Historique
            </button>
            <button id="openBlackjackBtn" class="px-4 md:px-6 py-3 md:py-4 bg-black hover:bg-red-800 text-white font-bold rounded-xl shadow-lg border border-red-700 transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">🃏</span>
                Blackjack
            </button>
        </div>
        <div class="mt-6 md:mt-8 text-center">
            <p class="text-gray-400 text-sm md:text-base">Engagez la partie et montez au classement.</p>
        </div>
    </main>
    @php
        $userBalance = (int) floor((auth()->user()->balance_cents ?? 0) / 100);

        $adminPlayersPayload = [
            'currentUserId' => auth()->id(),
            'players' => ($allPlayers ?? collect())->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
        ];

        $betEventsPayload = ($betEvents ?? collect())->map(function($e) use ($userBalance) {
            return [
                'id' => $e->id,
                'titre' => $e->title,
                'type' => $e->status,
                'miseMin' => (int) max(10000, floor($userBalance * 0.05)),
                'miseMax' => (int) max(1000000, floor($userBalance * 0.5)),
                'margin' => (float) $e->margin,
                'description' => $e->description,
                'choices' => ($e->choices ?? collect())->map(function($c){
                    return [
                        'id' => (string) $c->code,
                        'choiceId' => (int) $c->id,
                        'label' => $c->label,
                        'participants' => (int) $c->participants_count,
                        'stake' => (int) floor(((int)($c->total_bet_cents ?? 0)) / 100),
                    ];
                })->values(),
            ];
        })->values();

        $userBetsPayload = ($userActiveBets ?? collect())->map(function($b){
            return [
                'ref' => (int) $b->bet_event_id,
                'choix' => (string) optional($b->choice)->code ?? '',
                'mise' => (int) floor(($b->amount_cents ?? 0) / 100),
            ];
        })->values();
    @endphp
        <script id="adminPlayersData" type="application/json">{!! json_encode($adminPlayersPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
        <script id="betData" type="application/json">{!! json_encode(['events' => $betEventsPayload, 'activeBets' => $userBetsPayload], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
        @if(($houseStats ?? null))
        <script id="houseStatsData" type="application/json">{!! json_encode([
            'starting' => (int) ($houseStats['starting'] ?? 0),
            'balance' => (int) ($houseStats['balance'] ?? 0),
            'delta' => (int) ($houseStats['delta'] ?? 0),
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
        @endif
        @php
            $historyPayload = ($historyItems ?? collect())->map(function($h){
                return [
                    'id' => (int) ($h['id'] ?? 0),
                    'type' => (string) ($h['type'] ?? 'transaction'),
                    'icon' => (string) ($h['icon'] ?? '💰'),
                    'amount' => (float) ($h['amount'] ?? 0),
                    'desc' => (string) ($h['desc'] ?? ''),
                    'ts' => (int) ($h['ts'] ?? 0),
                ];
            })->values();
        @endphp
        <script id="historyData" type="application/json">{!! json_encode($historyPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
    <div id="playerMeta" data-balance="{{ (float)($balance ?? 0) }}" class="hidden"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('toggleClassementMobile');
    const panel = document.getElementById('classementPanel');
    if(btn && panel){
        btn.addEventListener('click', () => {
            panel.classList.toggle('hidden');
        });
    }
    const btnMaison = document.getElementById('toggleMaisonMobile');
    const maisonPanel = document.getElementById('housePanel');
    if(btnMaison && maisonPanel){
        btnMaison.addEventListener('click', () => {
            maisonPanel.classList.toggle('hidden');
        });
    }
});
</script>
<!-- Modal Liste des Paris -->
<div id="modalListeParis" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="w-11/12 md:w-3/4 lg:w-1/2 max-h-[85vh] overflow-hidden bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
            <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">🎲 Paris disponibles</h3>
            <button data-close="modalListeParis" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>
        <div class="px-5 pt-4 flex flex-wrap gap-2 text-xs md:text-sm">
            <button class="filter-pari active bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded-full" data-filter="tous">Tous</button>
            <button class="filter-pari bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full" data-filter="disponible">Disponibles</button>
            <button class="filter-pari bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full" data-filter="ferme">Fermé</button>
            <button id="openNewBetBtn" class="ml-auto bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1 rounded-full font-semibold">+ Nouveau pari</button>
        </div>
        <div class="px-5 pb-4 text-[10px] md:text-xs text-gray-400 italic">(Bonne chance)</div>
        <div class="flex-1 overflow-y-auto classement-scrollbar px-5 pb-5" id="listeParisContainer"></div>
        <div class="px-5 py-3 border-t border-red-800 flex justify-end bg-black/40">
            <button data-close="modalListeParis" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-sm font-semibold">Fermer</button>
        </div>
    </div>
</div>

    <!-- Modal Nouveau Pari -->
    <div id="modalNewBet" class="fixed inset-0 z-[72] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
      <div class="w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
            <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">Créer un pari</h3>
            <button data-close="modalNewBet" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>
        <div class="p-6 space-y-5 overflow-y-auto classement-scrollbar text-sm">
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-1">Description</label>
                <input id="newBetDesc" type="text" placeholder="Ex: Duel surprise de minuit" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" />
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-red-300 mb-1">Choix 1</label>
                    <input id="newBetChoice1" type="text" placeholder="Ex: Joueur A" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-red-300 mb-1">Choix 2</label>
                    <input id="newBetChoice2" type="text" placeholder="Ex: Joueur B" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-1">Choix 3 (facultatif)</label>
                <input id="newBetChoice3" type="text" placeholder="Ex: Égalité" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" />
            </div>
            <div id="newBetError" class="text-[11px] text-red-400 font-semibold hidden"></div>
        </div>
        <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex justify-end gap-3">
            <button data-close="modalNewBet" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Annuler</button>
            <button id="createNewBetBtn" class="px-5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-xs md:text-sm font-bold shadow ring-1 ring-emerald-500/50">Créer</button>
        </div>
      </div>
      </div>

<!-- Modal Paris En Cours -->
<div id="modalPariesEnCours" class="fixed inset-0 z-[65] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="w-11/12 md:w-3/4 lg:w-2/3 max-h-[85vh] overflow-hidden bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
            <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">🔥 Paris & Parties en cours</h3>
            <button data-close="modalPariesEnCours" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>
        <div class="px-5 pt-3 pb-2 text-[11px] text-gray-400">Vue synthétique des engagements actifs (données fictives).</div>
        <div id="listePariesEnCours" class="flex-1 overflow-y-auto classement-scrollbar px-5 pb-5 space-y-4"></div>
        <div class="px-5 py-3 border-t border-red-800 flex justify-end bg-black/40">
            <button data-close="modalPariesEnCours" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-sm font-semibold">Fermer</button>
        </div>
    </div>
 </div>

<!-- Modal Détail Pari -->
<div id="modalPariDetails" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="w-11/12 md:w-2/3 lg:w-1/3 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
            <h3 id="pariTitre" class="text-lg md:text-2xl font-bold text-red-400">Pari</h3>
            <button data-close="modalPariDetails" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>
        <div class="p-5 space-y-4 overflow-y-auto classement-scrollbar">
            <div id="pariDescription" class="text-sm text-gray-300 leading-relaxed"></div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-black/40 rounded-lg p-3 border border-red-800/40">
                    <div class="text-xs uppercase tracking-wide text-gray-400">Côte</div>
                    <div id="pariCote" class="text-xl font-bold text-red-400">1.00</div>
                </div>
                <div class="bg-black/40 rounded-lg p-3 border border-red-800/40">
                    <div class="text-xs uppercase tracking-wide text-gray-400">Type</div>
                    <div id="pariType" class="text-sm font-semibold text-red-300">-</div>
                </div>
            </div>
            <div class="bg-black/50 border border-red-800/40 rounded-xl p-3" id="pariChoices">
                <!-- choix dynamiques injectés ici -->
            </div>
            <div class="bg-black/40 rounded-xl p-4 border border-red-800/50 space-y-3">
                <label class="block text-sm font-semibold text-red-300">Votre mise (€)</label>
                <input id="pariMise" type="number" step="10000" max="{{ (int) $betMax/2 }}" value="{{ (int) $betMin }}" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2 text-sm" />
                <div class="flex justify-between text-xs text-gray-400">
                    <span>Mise min: <span id="pariMiseMin">-</span>€</span>
                    <span>Mise max: <span id="pariMiseMax">-</span>€</span>
                </div>
                <div class="bg-red-900/30 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm text-gray-300">Gain potentiel</span>
                    <span id="pariGainPotentiel" class="text-lg font-bold text-green-400">0 €</span>
                </div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-red-800 bg-black/50 flex justify-end gap-3">
            <button data-close="modalPariDetails" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-sm font-semibold">Annuler</button>
            <button id="confirmerPariBtn" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-sm font-bold shadow ring-1 ring-red-500/50">Parier</button>
        </div>
    </div>
</div>

<!-- Modal Confirmation Pari -->
<div id="modalBetConfirm" class="fixed inset-0 z-[66] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="w-11/12 md:w-[520px] bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-red-800 flex items-center gap-2">
            <span class="text-green-400">✔</span>
            <h3 class="text-lg font-semibold text-red-100">Pari enregistré</h3>
        </div>
        <div class="p-6 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-300">Événement</span><span id="betConfirmEvent" class="font-semibold text-red-200"></span></div>
            <div class="flex justify-between"><span class="text-gray-300">Choix</span><span id="betConfirmChoice" class="font-semibold text-red-200"></span></div>
            <div class="flex justify-between"><span class="text-gray-300">Côte</span><span id="betConfirmOdds" class="font-mono text-green-400"></span></div>
            <div class="flex justify-between"><span class="text-gray-300">Mise</span><span id="betConfirmStake" class="font-mono"></span></div>
            <div class="flex justify-between"><span class="text-gray-300">Gain potentiel</span><span id="betConfirmPotential" class="font-mono text-amber-300"></span></div>
        </div>
        <div class="px-6 py-4 border-t border-red-800 flex justify-end">
            <button data-close="modalBetConfirm" class="px-4 py-2 bg-red-700 hover:bg-red-800 rounded-lg">Fermer</button>
        </div>
    </div>
    </div>

<!-- Modal Inscription Partie Physique -->
<div id="modalInscriptionPhysique" class="fixed inset-0 z-[75] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
  <div class="w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[92vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">📝 Inscription Partie Physique</h3>
        <button data-close="modalInscriptionPhysique" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <div class="p-6 space-y-6 overflow-y-auto classement-scrollbar text-sm">
        <p class="text-xs text-gray-400 leading-relaxed">Configurez une partie physique: nom, joueurs participants, arbitre (betMaster) si nécessaire et mise commune.</p>

        <!-- Nom / Description -->
        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-2">Nom de la partie</label>
                <input id="partieNom" type="text" placeholder="Ex: Soirée Cash NLHE" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-2">Description (facultatif)</label>
                <input id="partieDescription" type="text" placeholder="Ex: Table amicale 4/8" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2" />
            </div>
        </div>

        <!-- Joueurs Participants -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold text-red-300">Joueurs participants</label>
                <button id="ajouterJoueurBtn" type="button" class="px-3 py-1 rounded bg-red-700/60 hover:bg-red-700 text-xs font-semibold">+ Ajouter</button>
            </div>
            <div class="relative">
                <input id="rechercheJoueur" type="text" placeholder="Rechercher pseudo ou ID..." class="w-full bg-black/50 border border-red-800 focus:ring-1 focus:ring-red-600 rounded-lg px-3 py-2 pr-10 text-xs" />
                <div id="resultatsRechercheJoueur" class="absolute z-10 mt-1 w-full bg-gray-900 border border-red-800 rounded-lg shadow-lg hidden max-h-52 overflow-y-auto text-xs"></div>
            </div>
            <div id="listeJoueursSelectionnes" class="flex flex-wrap gap-2 min-h-[2rem] p-2 bg-black/40 rounded border border-red-800/40"></div>
            <div class="text-[10px] text-gray-400">Minimum 1 joueur. Un arbitre est obligatoire au-delà de 3 joueurs.</div>
        </div>

        <!-- Arbitre / BetMaster -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <label class="block text-xs font-semibold text-red-300">BetMaster / Arbitre <span id="arbitreObligatoireBadge" class="hidden text-[10px] px-2 py-0.5 rounded bg-red-700/70">Obligatoire</span></label>
            </div>
            <div class="relative">
                <input id="rechercheArbitre" type="text" placeholder="Rechercher arbitre (pseudo ou ID)" class="w-full bg-black/50 border border-red-800 focus:ring-1 focus:ring-red-600 rounded-lg px-3 py-2 text-xs" />
                <div id="resultatsRechercheArbitre" class="absolute z-10 mt-1 w-full bg-gray-900 border border-red-800 rounded-lg shadow-lg hidden max-h-52 overflow-y-auto text-xs"></div>
            </div>
            <div id="arbitreSelectionne" class="text-xs text-gray-300"></div>
        </div>

        <!-- Mise Commune -->
        <div class="grid md:grid-cols-3 gap-5">
            <div class="md:col-span-1">
                <label for="inscriptionMise" class="block text-xs font-semibold text-red-300 mb-2">Mise commune (€)</label>
                <input id="inscriptionMise" type="number" min="1" value="1000" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2" />
            </div>
            <div class="md:col-span-2 grid grid-cols-2 gap-4 text-[11px]">
                <div class="bg-black/40 rounded-lg p-3 border border-red-800/40">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400">Mise</div>
                    <div id="inscriptionMiseAffichage" class="text-lg font-bold text-red-400">1 000 €</div>
                </div>
                <div class="bg-black/40 rounded-lg p-3 border border-red-800/40">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400">Total (si validé)</div>
                    <div id="inscriptionTotalPotentiel" class="text-lg font-bold text-emerald-400">1 000 €</div>
                </div>
            </div>
        </div>

        <div class="bg-red-900/20 border border-red-800/40 rounded-lg p-4 text-[11px] text-gray-300 leading-relaxed">
            Cette inscription est une simulation: aucun engagement financier réel n'est pris. Les données sont fictives.
        </div>
    </div>
    <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div id="messageErreurInscription" class="text-[11px] text-red-400 font-semibold hidden"></div>
        <div class="flex justify-end gap-3 w-full md:w-auto">
            <button data-close="modalInscriptionPhysique" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Annuler</button>
            <button id="confirmerInscriptionPhysiqueBtn" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs md:text-sm font-bold shadow ring-1 ring-red-500/50">Valider</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal Blackjack -->
<div id="modalBlackjack" wire:ignore class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/80 backdrop-blur-sm">
  <div class="w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[95vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">🃏 Blackjack</h3>
    <button id="blackjackCloseBtn" data-close="modalBlackjack" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <div class="flex-1 p-5 overflow-y-auto classement-scrollbar text-sm space-y-6" id="blackjackContent">
        <!-- Section mise initiale -->
        <div id="blackjackStartSection" class="space-y-4">
            <p class="text-xs text-gray-400 leading-relaxed">Saisissez votre mise pour démarrer une partie. Démo locale uniquement (aucun impact réel).</p>
            <div class="grid md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-1">
                    <label class="block text-[11px] font-semibold text-red-300 mb-1">Mise (€)</label>
                    <input 
                        id="blackjackBetInput" 
                        type="number" 
                        min="{{ (int) $betMin }}" 
                        max="{{ (int) $betMax }}" 
                        step="10000" 
                        value="{{ (int) $betMin }}" 
                        class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" 
                    />
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-2 text-[11px]">
                    @php
                        $bjMin = (int) floor($betMin);
                        $bjMax = (int) floor($betMax);
                        $bjMid = (int) floor(($bjMin + $bjMax) / 2);
                    @endphp

                    {{-- Bouton mise min --}}
                    <button type="button" 
                        data-bj-bet-quick="{{ $bjMin }}" 
                        class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">
                        MIN
                    </button>

                    {{-- Bouton mise mid --}}
                    <button type="button" 
                        data-bj-bet-quick="{{ $bjMid }}" 
                        class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">
                        MID
                    </button>

                    {{-- Bouton mise max (10k si solde > 0, sinon moitié dette si > 10k) --}}
                    <button type="button" 
                        data-bj-bet-quick="{{ $bjMax }}" 
                        class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">
                        MAX
                    </button>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" id="blackjackBet10kFixedBtn" class="mr-2 px-3 py-1 rounded bg-emerald-800/40 hover:bg-emerald-700/60 font-semibold">GO 10 000 FIXE</button>
                <button id="blackjackStartBtn" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs md:text-sm font-bold shadow ring-1 ring-red-500/50">Démarrer</button>
            </div>
        </div>
        <!-- Section jeu -->
        <div id="blackjackGameSection" class="hidden space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <div class="bg-black/40 rounded-xl p-4 border border-red-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-red-300">Croupier</h4>
                        <span id="blackjackDealerValue" class="text-xs text-gray-400">0</span>
                    </div>
                    <div id="blackjackDealerHand" class="flex flex-wrap gap-2 min-h-[52px]"></div>
                </div>
                <div class="bg-black/40 rounded-xl p-4 border border-red-800/40">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-red-300">Joueur</h4>
                        <span id="blackjackPlayerValue" class="text-xs text-gray-400">0</span>
                    </div>
                    <div id="blackjackPlayerHand" class="flex flex-wrap gap-2 min-h-[52px]"></div>
                </div>
            </div>
            <div class="bg-black/50 border border-red-800/40 rounded-xl p-4 space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <button id="blackjackHitBtn" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs md:text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">Tirer</button>
                    <button id="blackjackStandBtn" class="px-4 py-2 rounded-lg bg-black/70 border border-red-700 hover:bg-red-800 text-xs md:text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">Rester</button>
                    <button id="blackjackRestartBtn" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold hidden">Rejouer</button>
                    <label class="flex items-center gap-2 text-[11px] text-gray-400 ml-2 select-none">
                        <input id="blackjackKeepBet" type="checkbox" class="h-4 w-4 rounded border-red-700 bg-black/60 text-red-600 focus:ring-red-600" />
                        <span>Conserver mise</span>
                    </label>
                </div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-[11px]">
                    <div id="blackjackStatus" class="font-semibold text-red-300">Partie en cours...</div>
                    <div class="flex items-center gap-2">
                        <div class="bg-red-900/30 px-3 py-2 rounded-lg border border-red-800/40">
                            <span class="uppercase text-[10px] text-gray-400">Mise</span>
                            <div id="blackjackBetDisplay" class="text-sm font-bold text-red-400">0 €</div>
                        </div>
                        <div class="bg-green-900/20 px-3 py-2 rounded-lg border border-green-700/40">
                            <span class="uppercase text-[10px] text-gray-400">Gain Pot.</span>
                            <div id="blackjackWinDisplay" class="text-sm font-bold text-green-400">0 €</div>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-gray-500 italic">Blackjack paie 3:2. Démo locale.</p>
            </div>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex justify-end gap-3">
        <button data-close="modalBlackjack" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Fermer</button>
    </div>
  </div>
</div>

<!-- Modal Donation -->
<div id="modalDonation" class="fixed inset-0 z-[58] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
  <div class="w-11/12 md:w-2/3 lg:w-1/2 xl:w-2/5 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">💝 Faire un don</h3>
        <button data-close="modalDonation" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <div class="p-6 space-y-6 overflow-y-auto classement-scrollbar text-sm">
        <p class="text-xs text-gray-400 leading-relaxed">Transférez une somme fictive à un joueur. Aucune transaction réelle. Les montants sont simulés.</p>

        <div class="space-y-3">
            <label class="block text-xs font-semibold text-red-300">Destinataire</label>
            <div class="relative">
                <input id="donationSearchJoueur" type="text" placeholder="Rechercher pseudo ou ID..." class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2 text-xs pr-10" />
                <div id="donationResultatsRechercheJoueur" class="absolute z-10 mt-1 w-full bg-gray-900 border border-red-800 rounded-lg shadow-lg hidden max-h-52 overflow-y-auto text-xs"></div>
            </div>
            <div id="donationRecipientContainer" class="min-h-[2.2rem] flex items-center gap-2 p-2 bg-black/40 rounded border border-red-800/40 text-[11px] text-gray-400"></div>
        </div>

        <div class="space-y-3">
            <label class="block text-xs font-semibold text-red-300">Montant (€)</label>
            <div class="grid grid-cols-3 gap-3 items-end">
                <div class="col-span-2">
                    <input id="donationAmount" type="number" min="1" step="50" value="500" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2 text-sm" />
                </div>
                <div class="flex flex-wrap gap-2 justify-end text-[10px]">
                    <button type="button" data-don-quick="500" class="px-2 py-1 rounded bg-red-800/40 hover:bg-red-700/60">500</button>
                    <button type="button" data-don-quick="1000" class="px-2 py-1 rounded bg-red-800/40 hover:bg-red-700/60">1 000</button>
                    <button type="button" data-don-quick="2500" class="px-2 py-1 rounded bg-red-800/40 hover:bg-red-700/60">2 500</button>
                    <button type="button" data-don-quick="5000" class="px-2 py-1 rounded bg-red-800/40 hover:bg-red-700/60">5 000</button>
                </div>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-gray-400">
                <span>Frais: <span id="donationFees" class="text-gray-300">0 €</span></span>
                <span>Net reçu: <span id="donationNet" class="text-emerald-400 font-semibold">0 €</span></span>
            </div>
        </div>

        <div class="bg-red-900/20 border border-red-800/40 rounded-lg p-4 text-[11px] text-gray-300 leading-relaxed">
            Simulation d'interface. Les dons sont ajoutés à l'historique local comme transaction.
        </div>
        <div id="donationError" class="text-[11px] text-red-400 font-semibold hidden"></div>
    </div>
    <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex justify-end gap-3">
        <button data-close="modalDonation" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Annuler</button>
        <button id="confirmDonationBtn" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs md:text-sm font-bold shadow ring-1 ring-red-500/50">Envoyer</button>
    </div>
  </div>
</div>

<!-- Modal Paramètres -->
<div id="modalSettings" class="fixed inset-0 z-[59] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
  <div class="w-11/12 md:w-2/3 lg:w-1/2 xl:w-2/5 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[88vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">⚙️ Paramètres</h3>
        <button data-close="modalSettings" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <div class="p-6 space-y-6 overflow-y-auto classement-scrollbar text-sm">
        <p class="text-xs text-gray-400 leading-relaxed">Modifiez votre pseudo ou votre mot de passe. (Front-end démo uniquement.)</p>

        @if($isAdmin ?? false)
        <div class="bg-red-900/20 border border-red-800/40 rounded-lg p-4 space-y-3">
            <h4 class="text-sm font-semibold text-red-300 flex items-center gap-2">🛠️ Espace Admin</h4>
            <p class="text-[11px] text-gray-400">Accédez aux outils de gestion.</p>
            <button id="openAdminBtn" type="button" wire:click="$set('adminModalOpen', true)" class="px-3 py-2 rounded-md bg-red-700 hover:bg-red-800 text-xs font-semibold shadow ring-1 ring-red-500/40">Outils Admin</button>
        </div>
        @endif

        <div class="space-y-4">
            <label class="flex items-center gap-2 text-xs font-semibold text-red-300 select-none">
                <input id="settingsChangePseudo" type="checkbox" class="h-4 w-4 rounded border-red-700 bg-black/60 text-red-600 focus:ring-red-600" />
                <span>Changer de pseudo</span>
            </label>
            <div id="settingsPseudoFields" class="hidden bg-black/40 border border-red-800/40 rounded-lg p-4 space-y-3">
                <div>
                    <label class="block text-[11px] font-semibold text-red-300 mb-1">Nouveau pseudo</label>
                    <input id="settingsNewPseudo" type="text" placeholder="Nouveau pseudo" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2 text-sm" />
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <label class="flex items-center gap-2 text-xs font-semibold text-red-300 select-none">
                <input id="settingsChangePassword" type="checkbox" class="h-4 w-4 rounded border-red-700 bg-black/60 text-red-600 focus:ring-red-600" />
                <span>Changer le mot de passe</span>
            </label>
            <div id="settingsPasswordFields" class="hidden bg-black/40 border border-red-800/40 rounded-lg p-4 space-y-4">
                <div>
                    <label class="block text-[11px] font-semibold text-red-300 mb-1">Mot de passe actuel</label>
                    <input id="settingsCurrentPassword" type="password" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2 text-sm" />
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Nouveau mot de passe</label>
                        <input id="settingsNewPassword" type="password" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Confirmer mot de passe</label>
                        <input id="settingsConfirmPassword" type="password" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2 text-sm" />
                    </div>
                </div>
                <p class="text-[10px] text-gray-500">Longueur minimale recommandée: 6 caractères.</p>
            </div>
        </div>

        <div id="settingsError" class="text-[11px] text-red-400 font-semibold hidden"></div>
        <div id="settingsSuccess" class="text-[11px] text-emerald-400 font-semibold hidden"></div>
    </div>
    <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex justify-end gap-3">
        <button data-close="modalSettings" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Fermer</button>
        <button id="settingsSaveBtn" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs md:text-sm font-bold shadow ring-1 ring-red-500/50">Enregistrer</button>
    </div>
  </div>
</div>

<!-- Modal Déconnexion -->

@if($isAdmin ?? false)
<!-- Modal Admin -->
<div id="modalAdmin" class="fixed inset-0 z-[70] {{ $adminModalOpen ? 'flex' : 'hidden' }} items-center justify-center bg-black/75 backdrop-blur-sm">
  <div class="w-11/12 md:w-3/4 lg:w-3/5 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">🛠️ Administration</h3>
    <button data-close="modalAdmin" wire:click="$set('adminModalOpen', false)" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <div class="p-6 space-y-8 overflow-y-auto classement-scrollbar text-sm">
        <div class="space-y-4">
            <h4 class="text-sm font-semibold text-red-300 flex items-center gap-2">➕ Ajouter un joueur</h4>
            <form wire:submit.prevent="adminCreatePlayer" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Nom / Pseudo</label>
                        <input wire:model.defer="newPlayerName" type="text" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('newPlayerName') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Email</label>
                        <input wire:model.defer="newPlayerEmail" type="email" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('newPlayerEmail') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Mot de passe</label>
                        <input wire:model.defer="newPlayerPassword" type="password" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('newPlayerPassword') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Solde initial (€)</label>
                        <input wire:model.defer="newPlayerBalance" type="number" step="10000" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('newPlayerBalance') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs font-semibold ring-1 ring-red-500/50">Créer</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <h4 class="text-sm font-semibold text-red-300 flex items-center gap-2">💶 Injection monétaire</h4>
            <form wire:submit.prevent="adminInjectFunds" class="space-y-4">
                <div class="grid md:grid-cols-3 gap-4 items-start">
                    <div class="md:col-span-1">
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Montant (€)</label>
                        <input wire:model.defer="injectionAmount" type="number" step="10000" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('injectionAmount') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2" x-data wire:ignore>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Joueurs destinataires</label>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] text-gray-400">Sélectionnés</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-[10px] px-2 py-0.5 rounded bg-red-700/70 hover:bg-red-700" onclick="window.addAllInjectionSelection && window.addAllInjectionSelection()">Ajouter tout</button>
                                    <button type="button" class="text-[10px] px-2 py-0.5 rounded bg-gray-700/60 hover:bg-gray-600" onclick="window.clearInjectionSelection && window.clearInjectionSelection()">Vider</button>
                                </div>
                            </div>
                            <div id="injectionSelectedTags" class="flex flex-wrap gap-1 min-h-[2rem] p-2 bg-black/40 rounded border border-red-800/40 text-[11px]"></div>
                            <div class="relative">
                                <input id="injectionSearch" type="text" placeholder="Rechercher joueur..." class="w-full bg-black/50 border border-red-800 focus:ring-1 focus:ring-red-600 rounded-lg px-2 py-1.5 text-[11px]" autocomplete="off" />
                                <div id="injectionResults" class="absolute z-10 mt-1 w-full bg-gray-900 border border-red-800 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto text-[11px]"></div>
                            </div>
                        </div>
                        @error('injectionSelected') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs font-semibold ring-1 ring-red-500/50">Injecter</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <h4 class="text-sm font-semibold text-red-300 flex items-center gap-2">🗑️ Supprimer un joueur</h4>
            <form wire:submit.prevent="adminDeletePlayer" class="space-y-4">
                <div class="grid md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Joueur à supprimer</label>
                        <select wire:model="deletePlayerId" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600">
                            <option value="">-- Sélectionner --</option>
                            @php $currentId = auth()->id(); @endphp
                            @foreach(($allPlayers ?? collect()) as $p)
                                @if($p->id !== $currentId)
                                    <option value="{{ $p->id }}">#{{ $p->id }} - {{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('deletePlayerId') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-gray-500 mt-1">Action irréversible. Le joueur, son compte, ses transactions et sessions seront supprimés.</p>
                    </div>
                    <div class="md:col-span-1 flex justify-end">
                        <button type="submit" class="px-4 py-2 mt-5 rounded-lg bg-red-800 hover:bg-red-900 text-xs font-semibold ring-1 ring-red-600/60">Supprimer</button>
                    </div>
                </div>
            </form>
        </div>

        <div>
            @if($adminMessage)
                <div class="text-[11px] text-emerald-400 font-semibold">{{ $adminMessage }}</div>
            @endif
            @if($adminError)
                <div class="text-[11px] text-red-400 font-semibold">{{ $adminError }}</div>
            @endif
        </div>
        <div class="text-[10px] text-gray-500">Toutes les opérations sont journalisées dans les transactions.</div>
    </div>
    <div class="px-5 py-4 border-t border-red-800 bg-black/60 flex justify-end gap-3">
    <button data-close="modalAdmin" wire:click="$set('adminModalOpen', false)" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Fermer</button>
    </div>
</div>
</div>
@endif
<div id="modalLogout" class="fixed inset-0 z-[59] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="w-11/12 max-w-sm bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-xl shadow-2xl p-6 flex flex-col gap-5">
        <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-800/40 border border-red-600/40 flex items-center justify-center text-xl">🔒</div>
                <div class="flex-1">
                        <h3 class="text-lg font-bold text-red-400">Confirmer la déconnexion</h3>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">Vous allez être déconnecté. Assurez-vous d'avoir enregistré vos actions importantes.</p>
                </div>
        </div>
        <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="flex flex-col gap-3">
                @csrf
                <div class="flex justify-end gap-3">
                        <button type="button" data-close="modalLogout" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs font-semibold">Annuler</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-xs font-bold shadow ring-1 ring-red-500/50">Se déconnecter</button>
                </div>
        </form>
    </div>
</div>

<!-- Modal Historique -->
<div id="modalHistorique" class="fixed inset-0 z-[55] hidden items-center justify-center bg-black/75 backdrop-blur-sm">
    <div class="w-11/12 md:w-4/5 lg:w-2/3 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
                <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">📜 Historique</h3>
                <button data-close="modalHistorique" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>
        <div class="px-5 pt-4 pb-2 flex flex-wrap gap-2 text-[11px]" id="historyFilters">
                <button data-h-filter="tous" class="hist-filter active bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded-full">Tous</button>
                <button data-h-filter="pari" class="hist-filter bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full">Paris</button>
                <button data-h-filter="blackjack" class="hist-filter bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full">Blackjack</button>
                <button data-h-filter="transaction" class="hist-filter bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full">Transactions</button>
        </div>
    <div class="px-5 pb-3 text-[10px] md:text-xs text-gray-400 italic">Dernières activités (données réelles).</div>
        <div id="historyList" class="flex-1 overflow-y-auto classement-scrollbar px-5 pb-4 divide-y divide-red-800/30 text-sm"></div>
        <div class="px-5 py-3 border-t border-red-800 bg-black/60 flex items-center justify-between">
                <button id="historyShowMoreBtn" class="px-4 py-1.5 rounded-lg bg-black/70 border border-red-700 text-red-300 hover:bg-red-800/40 hover:text-white text-xs font-semibold">Afficher plus</button>
                <button data-close="modalHistorique" class="px-4 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs font-semibold">Fermer</button>
        </div>
    </div>
</div>

<!-- Toast Erreur Pari -->
<div id="toastBetError" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[95] hidden">
        <div class="px-4 py-3 bg-red-800 text-white rounded-lg shadow-lg border border-red-600 flex items-center gap-2">
            <span>⚠</span>
            <span id="toastBetErrorMsg" class="text-sm"></span>
        </div>
    </div>

<script>
// Gestion des modals et paris fictifs
document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('openPariModalBtn');
    const modalListe = document.getElementById('modalListeParis');
    const modalDetails = document.getElementById('modalPariDetails');
    const listeContainer = document.getElementById('listeParisContainer');
    const filterButtons = document.querySelectorAll('.filter-pari');
    const openNewBetBtn = document.getElementById('openNewBetBtn');
    const modalNewBet = document.getElementById('modalNewBet');
    const newBetDesc = document.getElementById('newBetDesc');
    const newBetChoice1 = document.getElementById('newBetChoice1');
    const newBetChoice2 = document.getElementById('newBetChoice2');
    const newBetChoice3 = document.getElementById('newBetChoice3');
    const newBetError = document.getElementById('newBetError');
    const createNewBetBtn = document.getElementById('createNewBetBtn');
    // Details refs
    const pariTitre = document.getElementById('pariTitre');
    const pariDescription = document.getElementById('pariDescription');
    const pariCote = document.getElementById('pariCote');
    const pariType = document.getElementById('pariType');
    const pariMise = document.getElementById('pariMise');
    const pariGainPotentiel = document.getElementById('pariGainPotentiel');
    const pariMiseMin = document.getElementById('pariMiseMin');
    const pariMiseMax = document.getElementById('pariMiseMax');
    const confirmerPariBtn = document.getElementById('confirmerPariBtn');
    const btnPariesEnCours = document.getElementById('btnPariesEnCours');
    const modalPariesEnCours = document.getElementById('modalPariesEnCours');
    const listePariesEnCours = document.getElementById('listePariesEnCours');
    const openInscriptionPhysiqueBtn = document.getElementById('openInscriptionPhysiqueBtn');
    const modalInscriptionPhysique = document.getElementById('modalInscriptionPhysique');
    const inscriptionMise = document.getElementById('inscriptionMise');
    const inscriptionMiseAffichage = document.getElementById('inscriptionMiseAffichage');
    const confirmerInscriptionPhysiqueBtn = document.getElementById('confirmerInscriptionPhysiqueBtn');
    const partieNom = document.getElementById('partieNom');
    const partieDescription = document.getElementById('partieDescription');
    const ajouterJoueurBtn = document.getElementById('ajouterJoueurBtn');
    const rechercheJoueur = document.getElementById('rechercheJoueur');
    const resultatsRechercheJoueur = document.getElementById('resultatsRechercheJoueur');
    const listeJoueursSelectionnes = document.getElementById('listeJoueursSelectionnes');
    const rechercheArbitre = document.getElementById('rechercheArbitre');
    const resultatsRechercheArbitre = document.getElementById('resultatsRechercheArbitre');
    const arbitreSelectionne = document.getElementById('arbitreSelectionne');
    const arbitreObligatoireBadge = document.getElementById('arbitreObligatoireBadge');
    const inscriptionTotalPotentiel = document.getElementById('inscriptionTotalPotentiel');
    const messageErreurInscription = document.getElementById('messageErreurInscription');
    // Blackjack refs
    const openBlackjackBtn = document.getElementById('openBlackjackBtn');
    const modalBlackjack = document.getElementById('modalBlackjack');
    const blackjackStartSection = document.getElementById('blackjackStartSection');
    const blackjackGameSection = document.getElementById('blackjackGameSection');
    const blackjackBetInput = document.getElementById('blackjackBetInput');
    const blackjackStartBtn = document.getElementById('blackjackStartBtn');
    const blackjackPlayerHand = document.getElementById('blackjackPlayerHand');
    const blackjackDealerHand = document.getElementById('blackjackDealerHand');
    const blackjackDealerValue = document.getElementById('blackjackDealerValue');
    const blackjackPlayerValue = document.getElementById('blackjackPlayerValue');
    const blackjackStatus = document.getElementById('blackjackStatus');
    const blackjackHitBtn = document.getElementById('blackjackHitBtn');
    const blackjackStandBtn = document.getElementById('blackjackStandBtn');
    const blackjackRestartBtn = document.getElementById('blackjackRestartBtn');
    const blackjackBetDisplay = document.getElementById('blackjackBetDisplay');
    const blackjackWinDisplay = document.getElementById('blackjackWinDisplay');
    const blackjackKeepBet = document.getElementById('blackjackKeepBet');
    // Historique refs
    const openHistoryBtn = document.getElementById('openHistoryBtn');
    const modalHistorique = document.getElementById('modalHistorique');
    const historyList = document.getElementById('historyList');
    const historyShowMoreBtn = document.getElementById('historyShowMoreBtn');
    const historyFilters = document.getElementById('historyFilters');
    // Settings refs
    const openSettingsBtn = document.getElementById('openSettingsBtn');
    const modalSettings = document.getElementById('modalSettings');
    const openAdminBtn = document.getElementById('openAdminBtn');
    const modalAdmin = document.getElementById('modalAdmin');
    const settingsChangePseudo = document.getElementById('settingsChangePseudo');
    const settingsPseudoFields = document.getElementById('settingsPseudoFields');
    const settingsNewPseudo = document.getElementById('settingsNewPseudo');
    const settingsChangePassword = document.getElementById('settingsChangePassword');
    const settingsPasswordFields = document.getElementById('settingsPasswordFields');
    const settingsCurrentPassword = document.getElementById('settingsCurrentPassword');
    const settingsNewPassword = document.getElementById('settingsNewPassword');
    const settingsConfirmPassword = document.getElementById('settingsConfirmPassword');
    const settingsSaveBtn = document.getElementById('settingsSaveBtn');
    const settingsError = document.getElementById('settingsError');
    const settingsSuccess = document.getElementById('settingsSuccess');
    // Admin injection selection UI
    function qInjectionRefs(){
        return {
            injectionSearch: document.getElementById('injectionSearch'),
            injectionResults: document.getElementById('injectionResults'),
            injectionSelectedTags: document.getElementById('injectionSelectedTags'),
        };
    }
    let { injectionSearch, injectionResults, injectionSelectedTags } = qInjectionRefs();
    const adminPlayersEl = document.getElementById('adminPlayersData');
    let __adminMeta = { currentUserId:0, players:[] };
    if(adminPlayersEl){
        try { __adminMeta = JSON.parse(adminPlayersEl.textContent || '{}'); } catch(e) { console.warn('Admin players JSON parse error', e); }
    }
    const injectionPool = Array.isArray(__adminMeta.players) ? __adminMeta.players : [];
    const currentUserId = parseInt(__adminMeta.currentUserId||0);
    function lwComp(){ const el = document.querySelector('[wire\\:id]'); if(!el || !window.Livewire) return null; return window.Livewire.find(el.getAttribute('wire:id')); }
    function getInjectionSelected(){ const c = lwComp(); return c? (c.get('injectionSelected')||[]) : []; }
    function setInjectionSelected(arr){ const c = lwComp(); if(c) c.set('injectionSelected', arr); }
    function renderInjectionSelected(){
        // Refresh refs in case DOM swapped
        ({ injectionSelectedTags } = qInjectionRefs());
        if(!injectionSelectedTags) return;
        const ids = getInjectionSelected();
        injectionSelectedTags.innerHTML = ids.map(id => {
            const u = injectionPool.find(p=>p.id==id);
            if(!u) return '';
            return `<span class="px-2 py-1 rounded bg-red-800/40 border border-red-700/50 flex items-center gap-1">
                <span>#${u.id} ${u.name}</span>
                <button type="button" data-inj-remove="${u.id}" class="text-red-300 hover:text-white font-bold">×</button>
            </span>`;
        }).join('');
    }
    window.clearInjectionSelection = function(){ setInjectionSelected([]); renderInjectionSelected(); };
    window.addAllInjectionSelection = function(){
        const allIds = injectionPool.map(u=>u.id);
        setInjectionSelected(allIds);
        renderInjectionSelected();
    };
    function openInjectionResults(){ ({ injectionResults } = qInjectionRefs()); if(injectionResults) injectionResults.classList.remove('hidden'); }
    function closeInjectionResults(){ ({ injectionResults } = qInjectionRefs()); if(injectionResults) injectionResults.classList.add('hidden'); }
    function filterInjection(q){
        q = (q||'').toLowerCase();
    const selected = new Set(getInjectionSelected());
    return injectionPool.filter(u => !selected.has(u.id) && (u.name.toLowerCase().includes(q) || (''+u.id).includes(q))).slice(0,30);
    }
    function renderInjectionResults(list){
        if(!injectionResults) return;
        if(!list.length){ injectionResults.innerHTML = '<div class="p-2 text-gray-400">Aucun résultat</div>'; return; }
        injectionResults.innerHTML = list.map(u => `<button type="button" data-inj-add="${u.id}" class="w-full text-left px-3 py-1.5 hover:bg-red-800/40 flex justify-between"><span>#${u.id} ${u.name}</span></button>`).join('');
    }
    function bindInjectionSearch(){
        ({ injectionSearch } = qInjectionRefs());
        if(!injectionSearch) return;
        injectionSearch.addEventListener('input', () => {
            const list = filterInjection(injectionSearch.value);
            renderInjectionResults(list); openInjectionResults();
        });
    }
    bindInjectionSearch();
    document.addEventListener('click', (e)=>{
        if(injectionResults && !injectionResults.contains(e.target) && injectionSearch !== e.target){ closeInjectionResults(); }
    });
    document.addEventListener('click', e => {
        const { injectionResults } = qInjectionRefs();
        const btn = e.target.closest('[data-inj-add]');
        if(btn && injectionResults && injectionResults.contains(btn)){
            const id = parseInt(btn.getAttribute('data-inj-add'));
            const arr = getInjectionSelected().slice();
            if(!arr.includes(id)) arr.push(id);
            setInjectionSelected(arr);
            const { injectionSearch } = qInjectionRefs();
            if(injectionSearch) injectionSearch.value='';
            renderInjectionSelected(); closeInjectionResults();
        }
    });
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-inj-remove]');
        if(!btn) return;
        const { injectionSelectedTags } = qInjectionRefs();
        if(injectionSelectedTags && injectionSelectedTags.contains(btn)){
            const id = parseInt(btn.getAttribute('data-inj-remove'));
            let arr = getInjectionSelected().slice();
            arr = arr.filter(x=>x!==id);
            setInjectionSelected(arr); renderInjectionSelected();
        }
    });
    document.addEventListener('livewire:load', () => {
        // Initial render + ensure bindings after Livewire boot
        renderInjectionSelected();
        bindInjectionSearch();
    });
    document.addEventListener('livewire:update', () => {
        // Livewire DOM diff occurred: refresh refs and re-bind input
        ({ injectionSearch, injectionResults, injectionSelectedTags } = qInjectionRefs());
        bindInjectionSearch();
        renderInjectionSelected();
    });
    // Écoute les événements navigateur de confirmation/erreur
    window.addEventListener('bet-placed', (e) => {
        try {
            const det = e.detail || {};
            const evId = parseInt(det.eventId);
            const choiceId = parseInt(det.choiceId);
            const stake = parseFloat(det.amount||0);
            const odds = parseFloat(det.odds||0);
            const potential = parseFloat(det.potential||0);
            // UI: fermer le modal de détails
            const modalDetails = document.getElementById('modalPariDetails');
            if(modalDetails) closeModal(modalDetails);
            // UI: mettre à jour la liste des paris actifs locale (optimiste)
            if(!Number.isNaN(evId)){
                const choixCode = det.choiceCode ? String(det.choiceCode) : String(choiceId);
                pariesActifs.unshift({ ref: evId, choix: choixCode, mise: Math.floor(stake) });
            }
            // Remplir le modal de confirmation
            const elEvent = document.getElementById('betConfirmEvent');
            const elChoice = document.getElementById('betConfirmChoice');
            const elOdds = document.getElementById('betConfirmOdds');
            const elStake = document.getElementById('betConfirmStake');
            const elPotential = document.getElementById('betConfirmPotential');
            // Trouver libellés depuis la sélection courante si dispo
            let evTitle = pariSelectionne?.titre || `#${evId}`;
            let choiceLabel = (pariSelectionne?.choices||[]).find(c=>parseInt(c.choiceId)===choiceId || String(c.id)===(det.choiceCode?String(det.choiceCode):''))?.label || `Choix ${det.choiceCode ?? choiceId}`;
            elEvent && (elEvent.textContent = evTitle);
            elChoice && (elChoice.textContent = choiceLabel);
            elOdds && (elOdds.textContent = isFinite(odds) ? odds.toFixed(2) : '—');
            const fmt = (n)=> (typeof n==='number' && isFinite(n)) ? n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €' : '—';
            elStake && (elStake.textContent = fmt(stake));
            elPotential && (elPotential.textContent = fmt(potential));
            // Ouvrir le modal
            const modal = document.getElementById('modalBetConfirm');
            if(modal){ openModal(modal); }
        } catch(err){ console.warn('bet-placed handler error', err); }
    });
    window.addEventListener('bet-error', (e) => {
        const msg = (e.detail && e.detail.message) ? String(e.detail.message) : 'Erreur pendant le pari';
        const toast = document.getElementById('toastBetError');
        const el = document.getElementById('toastBetErrorMsg');
        if(el) el.textContent = msg;
        if(toast){
            toast.classList.remove('hidden');
            setTimeout(()=> toast.classList.add('hidden'), 2500);
        }
    });
    // Logout refs
    const logoutConfirmBtn = document.getElementById('logoutConfirmBtn');
    const modalLogout = document.getElementById('modalLogout');

    let pariSelectionne = null;

    // Charger les événements de pari et les paris actifs depuis le serveur
    const betDataEl = document.getElementById('betData');
    const __betData = betDataEl ? JSON.parse(betDataEl.textContent || '{}') : { events: [], activeBets: [] };
    const paris = Array.isArray(__betData.events) ? __betData.events : [];

    function formatEuros(n){
        return new Intl.NumberFormat('fr-FR',{minimumFractionDigits:0, maximumFractionDigits:2}).format(n) + ' €';
    }

    function badgeType(t){
        const map = { disponible:['Disponible','bg-green-700/50 text-green-300'], ferme:['Fermé','bg-red-700/40 text-red-300'], cloture:['Clôturé','bg-gray-700/40 text-gray-300'] };
        return map[t] || ['Autre','bg-gray-600/40 text-gray-300'];
    }

    function computeOdds(pari){
        const commission = 0.10;
        const totalParticipants = Math.max(1, pari.choices.reduce((s,c)=> s + (parseInt(c.participants)||0), 0));
        const totalStake = Math.max(1, pari.choices.reduce((s,c)=> s + (parseInt(c.stake)||0), 0));
        const odds = {};
        pari.choices.forEach(c => {
            const participantsChoice = Math.max(1, parseInt(c.participants)||0);
            const stakeChoice = Math.max(1, parseInt(c.stake)||0);
            let raw = (totalStake / stakeChoice) * (totalParticipants / participantsChoice) * (1 - commission);
            if (raw < 1.20) raw = 1.20; if (raw > 50) raw = 50;
            odds[c.id] = raw;
        });
        return odds;
    }

    function renderParis(filter='tous'){
        const items = paris.filter(p => filter==='tous' || p.type===filter);
        if(!items.length){
            listeContainer.innerHTML = '<div class="text-center text-gray-400 py-10 text-sm">Aucun pari pour ce filtre.</div>';
            return;
        }
        listeContainer.innerHTML = items.map(p => {
            const [label, cls] = badgeType(p.type);
            const nbChoices = p.choices.length;
            const preview = p.choices.slice(0,2).map(c=>c.label).join(' / ') + (nbChoices>2?' ...':'');
            const already = hasActiveBetOn(p.id);
            const outerCls = `group border border-red-800/40 ${already?'opacity-70':''} hover:border-red-500/70 transition rounded-xl p-4 mb-3 bg-black/40 hover:bg-black/60 ${already?'cursor-not-allowed':'cursor-pointer'}`;
            return `<div class="${outerCls}" data-pari-id="${p.id}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="font-semibold text-red-300 group-hover:text-red-200">${p.titre}</div>
                    <div class="text-[11px] mt-1 text-gray-400 leading-snug">${p.description.substring(0,70)}...</div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full ${cls}">${label}</span>
                        <span class="text-xs bg-red-800/40 text-red-300 px-2 py-0.5 rounded-full">${nbChoices} choix</span>
                        <span class="text-[10px] text-gray-400">${preview}</span>
                    </div>
                </div>
                <div class="text-right text-[10px] text-gray-400 space-y-1">
                    <div>Min <span class="text-gray-200">${p.miseMin}€</span></div>
                    <div>Max <span class="text-gray-200">${p.miseMax}€</span></div>
                    <div class="pt-1">Odds dyn.</div>
                    ${already?'<div class="text-red-400 font-semibold mt-2">Déjà parié</div>':''}
                </div>
            </div>
        </div>`;
        }).join('');
    }

    function openModal(el){ el.classList.remove('hidden'); el.classList.add('flex'); }
    function closeModal(el){ el.classList.add('hidden'); el.classList.remove('flex'); }

    openBtn && openBtn.addEventListener('click', () => { renderParis(); openModal(modalListe); });
    openNewBetBtn && openNewBetBtn.addEventListener('click', () => {
        if(newBetError){ newBetError.classList.add('hidden'); newBetError.textContent=''; }
        if(newBetDesc) newBetDesc.value='';
        if(newBetChoice1) newBetChoice1.value='';
        if(newBetChoice2) newBetChoice2.value='';
        if(newBetChoice3) newBetChoice3.value='';
        openModal(modalNewBet);
    });

    // Données fictives des paris en cours (référencent éventuellement un pari de la liste + choix)
    const pariesActifs = Array.isArray(__betData.activeBets) ? __betData.activeBets : [];
    function hasActiveBetOn(eventId){ return pariesActifs.some(b=>parseInt(b.ref)===parseInt(eventId)); }

    function renderPariesEnCours(){
        if(!pariesActifs.length){
            listePariesEnCours.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">Aucun pari en cours.</div>';
            return;
        }
        listePariesEnCours.innerHTML = pariesActifs.map(pa => {
            const pari = paris.find(p=>parseInt(p.id)===parseInt(pa.ref));
            if(!pari) return '';
            const oddsMap = computeOdds(pari);
            // pa.choix peut être code (A/B/C) ou id numérique
            let choixCode = String(pa.choix);
            if(/^[0-9]+$/.test(choixCode)){
                const found = (pari.choices||[]).find(c=>parseInt(c.choiceId)===parseInt(choixCode));
                if(found) choixCode = String(found.id);
            }
            const choiceObj = (pari.choices||[]).find(c=>String(c.id)===choixCode);
            const cote = oddsMap[choixCode] || 0;
            const gainPot = pa.mise * cote;
            return `<div class=\"border border-red-800/40 rounded-xl p-4 bg-black/40 hover:border-red-500/70 transition\">\n                <div class=\"flex flex-col md:flex-row md:items-center md:justify-between gap-3\">\n                    <div class=\"flex-1\">\n                        <div class=\"text-red-300 font-semibold\">${pari.titre}</div>\n                        <div class=\"text-[11px] text-gray-400 mt-0.5\">Choix: <span class=\"text-gray-200\">${choiceObj?choiceObj.label:pa.choix}</span></div>\n                        <div class=\"mt-1 flex flex-wrap gap-2 text-[10px]\">\n                            <span class=\"px-2 py-0.5 rounded-full bg-red-800/40 text-red-200\">Côte ${cote.toFixed(2)}</span>\n                            <span class=\"px-2 py-0.5 rounded-full bg-gray-700/40 text-gray-200\">Participants ${pari.choices.reduce((s,c)=>s+c.participants,0)}</span>\n                        </div>\n                    </div>\n                    <div class=\"grid grid-cols-2 gap-3 md:text-right text-sm font-mono\">\n                        <div class=\"bg-black/50 rounded-lg p-2 border border-red-900/40\">\n                            <div class=\"text-[10px] uppercase text-gray-400 tracking-wide\">Mise</div>\n                            <div class=\"text-red-400 font-bold\">${pa.mise.toLocaleString('fr-FR')} €</div>\n                        </div>\n                        <div class=\"bg-black/50 rounded-lg p-2 border border-red-900/40\">\n                            <div class=\"text-[10px] uppercase text-gray-400 tracking-wide\">Gain Pot.</div>\n                            <div class=\"text-green-400 font-bold\">${gainPot.toFixed(2).toLocaleString('fr-FR')} €</div>\n                        </div>\n                    </div>\n                </div>\n            </div>`;
        }).join('');
    }

    btnPariesEnCours && btnPariesEnCours.addEventListener('click', () => {
        renderPariesEnCours();
        openModal(modalPariesEnCours);
    });

    // Inscription partie physique
    function updateInscriptionAffichage(){
        if(!inscriptionMise || !inscriptionMiseAffichage) return;
        const v = parseFloat(inscriptionMise.value)||0;
        inscriptionMiseAffichage.textContent = v.toLocaleString('fr-FR') + ' €';
        updateTotalPotentiel();
    }
    function updateTotalPotentiel(){
        if(!inscriptionTotalPotentiel) return;
        const mise = parseFloat(inscriptionMise.value)||0;
        const joueurs = joueursSelectionnes.length;
        inscriptionTotalPotentiel.textContent = (mise * joueurs).toLocaleString('fr-FR') + ' €';
    }

    // Données fictives pour recherche joueurs et arbitres
    const poolUtilisateurs = Array.from({length: 40}).map((_,i)=>({id:i+1,pseudo:'Joueur'+(i+1)}));
    let joueursSelectionnes = [];
    let arbitre = null;
    // Blackjack state
    let bjDeck = [];
    let bjPlayer = [];
    let bjDealer = [];
    let bjBet = 0;
    // bjFinished true = aucune main en cours ou main terminée => fermeture autorisée
    let bjFinished = true;
    const blackjackCloseBtn = document.getElementById('blackjackCloseBtn');
    // Historique state (real data from backend)
    const HISTORY_PAGE_SIZE = 10;
    let historyVisibleCount = HISTORY_PAGE_SIZE;
    let historyCurrentFilter = 'tous';
    const historyDataEl = document.getElementById('historyData');
    const __history = historyDataEl ? JSON.parse(historyDataEl.textContent || '[]') : [];

    function formatDate(ts){
        const d = new Date(ts);
        return d.toLocaleString('fr-FR', {hour12:false});
    }
    function formatAmount(a){
        const sign = a>0?'+':'';
        return sign + a.toLocaleString('fr-FR') + ' €';
    }
    function historyFiltered(){
        if(historyCurrentFilter==='tous') return __history;
        return __history.filter(h=>h.type===historyCurrentFilter);
    }
    function renderHistory(){
        if(!historyList) return;
        const data = historyFiltered();
        const slice = data.slice(0, historyVisibleCount);
        if(!slice.length){
            historyList.innerHTML = '<div class="py-10 text-center text-gray-500 text-sm">Aucun élément.</div>';
        } else {
            historyList.innerHTML = slice.map(h=>{
                const amtCls = h.amount>0? 'text-green-400':'text-red-400';
                return `<div class="py-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-800/30 border border-red-700/40 flex items-center justify-center text-base">${h.icon}</div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1">
                            <div class="font-medium text-red-200">${h.desc}</div>
                            <div class="text-[11px] text-gray-500">${formatDate(h.ts)}</div>
                        </div>
                        <div class="mt-1 text-xs uppercase tracking-wide text-gray-500">${h.type}</div>
                    </div>
                    <div class="text-sm font-mono ${amtCls}">${formatAmount(h.amount)}</div>
                </div>`;
            }).join('');
        }
        // ShowMore visibility
        if(historyShowMoreBtn){
            if(historyVisibleCount >= data.length){
                historyShowMoreBtn.classList.add('hidden');
            } else {
                historyShowMoreBtn.classList.remove('hidden');
            }
        }
    }
    function resetHistory(){
        historyVisibleCount = HISTORY_PAGE_SIZE;
        renderHistory();
    }
    openHistoryBtn && openHistoryBtn.addEventListener('click', () => {
        historyCurrentFilter = 'tous';
        document.querySelectorAll('.hist-filter').forEach(f=>{
            f.classList.remove('active','bg-red-700','text-white');
            f.classList.add('bg-gray-800','text-red-300');
        });
        const first = document.querySelector('[data-h-filter="tous"]');
        if(first){ first.classList.add('active','bg-red-700','text-white'); first.classList.remove('bg-gray-800','text-red-300'); }
        resetHistory();
        openModal(modalHistorique);
    });
    historyFilters && historyFilters.addEventListener('click', (e)=>{
        const btn = e.target.closest('[data-h-filter]');
        if(!btn) return;
        historyCurrentFilter = btn.getAttribute('data-h-filter');
        document.querySelectorAll('.hist-filter').forEach(f=>{
            f.classList.remove('active','bg-red-700','text-white');
            f.classList.add('bg-gray-800','text-red-300');
        });
        btn.classList.add('active','bg-red-700','text-white');
        btn.classList.remove('bg-gray-800','text-red-300');
        resetHistory();
    });
    historyShowMoreBtn && historyShowMoreBtn.addEventListener('click', () => {
        historyVisibleCount += HISTORY_PAGE_SIZE;
        renderHistory();
    });

    // Settings modal logic
    function clearSettingsMessages(){
        settingsError && settingsError.classList.add('hidden');
        settingsSuccess && settingsSuccess.classList.add('hidden');
    }
    function setSettingsError(msg){ if(!settingsError) return; settingsError.textContent = msg; settingsError.classList.remove('hidden'); settingsSuccess&&settingsSuccess.classList.add('hidden'); }
    function setSettingsSuccess(msg){ if(!settingsSuccess) return; settingsSuccess.textContent = msg; settingsSuccess.classList.remove('hidden'); settingsError&&settingsError.classList.add('hidden'); }

    function toggleSettingsFields(){
        if(settingsPseudoFields){ settingsPseudoFields.classList.toggle('hidden', !settingsChangePseudo.checked); }
        if(settingsPasswordFields){ settingsPasswordFields.classList.toggle('hidden', !settingsChangePassword.checked); }
    }
    settingsChangePseudo && settingsChangePseudo.addEventListener('change', toggleSettingsFields);
    settingsChangePassword && settingsChangePassword.addEventListener('change', toggleSettingsFields);

    openSettingsBtn && openSettingsBtn.addEventListener('click', () => {
        // reset form
        clearSettingsMessages();
        if(settingsChangePseudo) settingsChangePseudo.checked = false;
        if(settingsChangePassword) settingsChangePassword.checked = false;
        if(settingsNewPseudo) settingsNewPseudo.value='';
        if(settingsCurrentPassword) settingsCurrentPassword.value='';
        if(settingsNewPassword) settingsNewPassword.value='';
        if(settingsConfirmPassword) settingsConfirmPassword.value='';
        toggleSettingsFields();
        openModal(modalSettings);
    });
    openAdminBtn && openAdminBtn.addEventListener('click', () => {
        openModal(modalAdmin);
    });

    settingsSaveBtn && settingsSaveBtn.addEventListener('click', () => {
        clearSettingsMessages();
        const changePseudo = settingsChangePseudo && settingsChangePseudo.checked;
        const changePwd = settingsChangePassword && settingsChangePassword.checked;
        if(!changePseudo && !changePwd){ setSettingsError('Sélectionnez une option à modifier'); return; }
        if(changePseudo){
            const pseudo = (settingsNewPseudo?.value||'').trim();
            if(!pseudo){ setSettingsError('Nouveau pseudo requis'); return; }
            if(pseudo.length < 3){ setSettingsError('Pseudo trop court'); return; }
        }
        if(changePwd){
            const cur = settingsCurrentPassword?.value||'';
            const np = settingsNewPassword?.value||'';
            const cf = settingsConfirmPassword?.value||'';
            if(!cur){ setSettingsError('Mot de passe actuel requis'); return; }
            if(np.length < 6){ setSettingsError('Nouveau mot de passe trop court'); return; }
            if(np !== cf){ setSettingsError('Confirmation différente'); return; }
        }
        // Simulate save (front only)
        setTimeout(()=>{
            setSettingsSuccess('Paramètres mis à jour (simulation)');
        },400);
    });

    // Logout modal
    logoutConfirmBtn && logoutConfirmBtn.addEventListener('click', () => {
        openModal(modalLogout);
    });

    // Donation modal logic
    const modalDonation = document.getElementById('modalDonation');
    const openDonationBtn = document.getElementById('openDonationBtn');
    const donationSearchJoueur = document.getElementById('donationSearchJoueur');
    const donationResultatsRechercheJoueur = document.getElementById('donationResultatsRechercheJoueur');
    const donationRecipientContainer = document.getElementById('donationRecipientContainer');
    const donationAmount = document.getElementById('donationAmount');
    const donationFees = document.getElementById('donationFees');
    const donationNet = document.getElementById('donationNet');
    const confirmDonationBtn = document.getElementById('confirmDonationBtn');
    const donationError = document.getElementById('donationError');
    let donationRecipient = null;

    openDonationBtn && openDonationBtn.addEventListener('click', () => {
        donationRecipient = null;
        donationRecipientContainer.innerHTML = '<span class="text-[11px] text-gray-500">Aucun destinataire sélectionné</span>';
        donationSearchJoueur.value='';
        donationAmount.value = '500';
        updateDonationComputed();
        openModal(modalDonation);
    });

    function updateDonationComputed(){
        const val = parseFloat(donationAmount.value)||0;
        const fees = Math.round(val * 0.02); // 2% frais fictifs
        const net = Math.max(0, val - fees);
        donationFees.textContent = fees.toLocaleString('fr-FR') + ' €';
        donationNet.textContent = net.toLocaleString('fr-FR') + ' €';
    }
    donationAmount && donationAmount.addEventListener('input', updateDonationComputed);
    document.querySelectorAll('[data-don-quick]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            donationAmount.value = btn.getAttribute('data-don-quick');
            updateDonationComputed();
        });
    });

    function searchDonationUsers(term){
        const t = term.toLowerCase();
        return poolUtilisateurs.filter(u=> u.pseudo.toLowerCase().includes(t) || (''+u.id).includes(t)).slice(0,8);
    }
    function renderDonationSearch(){
        const term = donationSearchJoueur.value.trim();
        if(!term){ donationResultatsRechercheJoueur.classList.add('hidden'); donationResultatsRechercheJoueur.innerHTML=''; return; }
        const res = searchDonationUsers(term);
        if(!res.length){ donationResultatsRechercheJoueur.innerHTML='<div class="px-3 py-2 text-gray-500">Aucun résultat</div>'; donationResultatsRechercheJoueur.classList.remove('hidden'); return; }
        donationResultatsRechercheJoueur.innerHTML = res.map(r=>`<button type="button" data-don-select="${r.id}" class="w-full text-left px-3 py-2 hover:bg-red-800/40 flex justify-between items-center"> <span>${r.pseudo}</span><span class='text-[10px] text-gray-400'>#${r.id}</span></button>`).join('');
        donationResultatsRechercheJoueur.classList.remove('hidden');
        donationResultatsRechercheJoueur.querySelectorAll('[data-don-select]').forEach(b=>{
            b.addEventListener('click', ()=>{
                const id = parseInt(b.getAttribute('data-don-select'));
                const user = poolUtilisateurs.find(u=>u.id===id);
                donationRecipient = user;
                donationRecipientContainer.innerHTML = `<div class='flex items-center gap-2 bg-red-800/30 border border-red-700/50 px-3 py-2 rounded'>
                    <span class='text-red-200 font-semibold text-xs'>${user.pseudo}</span>
                    <button type='button' id='donationRemoveRecipient' class='text-red-300 hover:text-white font-bold'>×</button>
                </div>`;
                document.getElementById('donationRemoveRecipient').addEventListener('click', ()=>{
                    donationRecipient = null; donationRecipientContainer.innerHTML='<span class="text-[11px] text-gray-500">Aucun destinataire sélectionné</span>';
                });
                donationResultatsRechercheJoueur.classList.add('hidden');
                donationSearchJoueur.value='';
            });
        });
    }
    donationSearchJoueur && donationSearchJoueur.addEventListener('input', renderDonationSearch);

    function setDonationError(msg){
        if(!donationError) return; if(!msg){ donationError.classList.add('hidden'); donationError.textContent=''; return; }
        donationError.textContent = msg; donationError.classList.remove('hidden');
    }
    confirmDonationBtn && confirmDonationBtn.addEventListener('click', () => {
        setDonationError('');
        const amount = parseFloat(donationAmount.value)||0;
        if(!donationRecipient){ return setDonationError('Sélectionnez un destinataire'); }
        if(amount <= 0){ return setDonationError('Montant invalide'); }
        alert('(Démo) Don envoyé à '+donationRecipient.pseudo+' de '+amount.toLocaleString('fr-FR')+' €');
        closeModal(modalDonation);
    });

    function bjCreateDeck(){
        const suits = ['♠','♥','♦','♣'];
        const ranks = ['A','2','3','4','5','6','7','8','9','10','J','Q','K'];
        const deck = [];
        suits.forEach(s => ranks.forEach(r => deck.push({suit:s, rank:r})));
        // shuffle
        for(let i=deck.length-1;i>0;i--){
            const j = Math.floor(Math.random()* (i+1));
            [deck[i], deck[j]] = [deck[j], deck[i]];
        }
        return deck;
    }
    function bjCardValue(card){
        if(card.rank==='A') return 11;
        if(['K','Q','J'].includes(card.rank)) return 10;
        return parseInt(card.rank,10);
    }
    function bjHandValue(hand){
        let total = 0; let aces = 0;
        hand.forEach(c=>{ total += bjCardValue(c); if(c.rank==='A') aces++; });
        while(total>21 && aces>0){ total -=10; aces--; }
        return total;
    }
    function bjDeal(){ return bjDeck.shift(); }
    function bjRenderHands(revealDealer=false){
        blackjackPlayerHand.innerHTML = bjPlayer.map(c=> bjRenderCard(c)).join('');
        if(!revealDealer){
            const first = bjDealer[0];
            blackjackDealerHand.innerHTML = bjRenderCard(first) + bjHiddenCard();
        } else {
            blackjackDealerHand.innerHTML = bjDealer.map(c=> bjRenderCard(c)).join('');
        }
        blackjackPlayerValue.textContent = bjHandValue(bjPlayer);
        blackjackDealerValue.textContent = revealDealer? bjHandValue(bjDealer) : bjCardValue(bjDealer[0]);
    }
    function bjRenderCard(card){
        const isRed = ['♥','♦'].includes(card.suit);
        return `<div class="w-10 h-14 rounded-md border border-red-700/40 bg-black/60 flex flex-col items-center justify-center text-xs font-semibold ${isRed?'text-red-300':'text-gray-200'} shadow">
            <span>${card.rank}</span><span class="text-[10px]">${card.suit}</span>
        </div>`;
    }
    function bjHiddenCard(){
        return `<div class="w-10 h-14 rounded-md border border-red-800/60 bg-red-900/60 flex items-center justify-center text-[10px] text-red-200 animate-pulse">? ?</div>`;
    }
    function bjStart(){
        bjDeck = bjCreateDeck();
        bjPlayer = [bjDeal(), bjDeal()];
        bjDealer = [bjDeal(), bjDeal()];
        bjFinished = false;
        blackjackStatus.textContent = 'Partie en cours...';
        blackjackHitBtn.disabled = false;
        blackjackStandBtn.disabled = false;
        blackjackRestartBtn.classList.add('hidden');
        // Verrouiller fermeture
        if(blackjackCloseBtn){
            blackjackCloseBtn.classList.add('opacity-40','cursor-not-allowed','pointer-events-none');
            blackjackCloseBtn.setAttribute('aria-disabled','true');
        }
        bjRenderHands(false);
        bjCheckImmediate();
    }
    function bjCheckImmediate(){
        const pv = bjHandValue(bjPlayer);
        const dv = bjHandValue(bjDealer);
        if(pv===21 || dv===21){
            bjFinish();
        }
    }
    function bjFinish(){
        bjFinished = true;
        blackjackHitBtn.disabled = true;
        blackjackStandBtn.disabled = true;
        blackjackRestartBtn.classList.remove('hidden');
        // Déverrouiller fermeture
        if(blackjackCloseBtn){
            blackjackCloseBtn.classList.remove('opacity-40','cursor-not-allowed','pointer-events-none');
            blackjackCloseBtn.removeAttribute('aria-disabled');
        }
        bjRenderHands(true);
        const pv = bjHandValue(bjPlayer);
        const dv = bjHandValue(bjDealer);
        let outcome = '';
        let win = 0;
        const playerBJ = (pv===21 && bjPlayer.length===2);
        const dealerBJ = (dv===21 && bjDealer.length===2);
        if(playerBJ && dealerBJ){ outcome='Push (Blackjack)'; win = bjBet; }
        else if(playerBJ){ outcome='Blackjack !'; win = bjBet * 2.5; }
        else if(dealerBJ){ outcome='Croupier Blackjack'; win = 0; }
        else if(pv>21){ outcome='Bust joueur'; win = 0; }
        else if(dv>21){ outcome='Croupier bust'; win = bjBet*2; }
        else if(pv>dv){ outcome='Victoire joueur'; win = bjBet*2; }
        else if(pv<dv){ outcome='Défaite'; win = 0; }
        else { outcome='Push'; win = bjBet; }
        blackjackStatus.textContent = outcome + ' | Total Joueur '+pv+' / Croupier '+dv;
        blackjackWinDisplay.textContent = win.toLocaleString('fr-FR') + ' €';

        // Créditer le gain côté serveur (Livewire)
        try {
            if (win > 0 && window.Livewire) {
                const comp = lwComp();
                if (comp && typeof comp.call === 'function') {
                    comp.call('onBlackjackWon', parseFloat(win));
                }
            }
        } catch(e) { console.warn('Livewire credit error', e); }
    }
    function bjDealerPlay(){
        while(bjHandValue(bjDealer) < 17){
            bjDealer.push(bjDeal());
        }
    }
    // Events Blackjack
    // Dynamic limits based on current balance
    function getPlayerBalance(){
        const el = document.getElementById('playerMeta');
        if(!el) return 0;
        const v = parseFloat(el.getAttribute('data-balance')||'0');
        return isNaN(v)?0:v;
    }
    function bjComputeLimits(balance){
        const min = balance > 10000 ? balance * 0.10 : 10000;
        const max = balance >= 0 ? Math.max(10000, balance) : Math.max(10000, Math.abs(balance)/2);
        return {min, max};
    }
    function bjApplyLimits(){
        const bal = getPlayerBalance();
        const lim = bjComputeLimits(bal);
        if(blackjackBetInput){
            blackjackBetInput.setAttribute('min', String(Math.floor(lim.min)));
            blackjackBetInput.setAttribute('max', String(Math.floor(lim.max)));
        }
        return lim;
    }
    openBlackjackBtn && openBlackjackBtn.addEventListener('click', () => {
        blackjackStartSection.classList.remove('hidden');
        blackjackGameSection.classList.add('hidden');
        openModal(modalBlackjack);
    bjApplyLimits();
    });
    document.querySelectorAll('[data-bj-bet-quick]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const v = btn.getAttribute('data-bj-bet-quick');
            const lim = bjApplyLimits();
            let nv = parseFloat(v)||0;
            if(nv < lim.min) nv = lim.min;
            if(nv > lim.max) nv = lim.max;
            blackjackBetInput.value = String(Math.floor(nv));
        });
    });

    // Démarrage rapide avec mise fixe 10k (ignore les limites de solde)
    const bj10kBtn = document.getElementById('blackjackBet10kFixedBtn');
    if(bj10kBtn){
        bj10kBtn.addEventListener('click', () => {
            const fixed = 10000;
            // Débiter côté serveur via Livewire, sans contrôle client
            try {
                if (window.Livewire) {
                    const comp = lwComp();
                    if (comp && typeof comp.call === 'function') {
                        comp.call('onBlackjackBet10kFixed');
                    }
                }
            } catch(e) { console.warn('Livewire bet 10k error', e); }

            // Forcer la valeur de mise en UI et démarrer la partie
            bjBet = fixed;
            if (blackjackBetInput) {
                blackjackBetInput.value = String(fixed);
            }
            blackjackBetDisplay.textContent = fixed.toLocaleString('fr-FR') + ' €';
            blackjackWinDisplay.textContent = '0 €';
            blackjackStartSection.classList.add('hidden');
            blackjackGameSection.classList.remove('hidden');
            bjStart();
        });
    }
    blackjackStartBtn && blackjackStartBtn.addEventListener('click', () => {
    const lim = bjApplyLimits();
    const v = parseFloat(blackjackBetInput.value)||0;
    if(v < lim.min || v > lim.max){
            blackjackBetInput.classList.add('ring','ring-red-600');
            setTimeout(()=>blackjackBetInput.classList.remove('ring','ring-red-600'),800);
            return;
        }
        // Débiter immédiatement la mise côté serveur (Livewire)
        try {
            if (window.Livewire) {
                const comp = lwComp();
                if (comp && typeof comp.call === 'function') {
                    comp.call('onBlackjackBetPlaced', parseFloat(v));
                }
            }
        } catch(e) { console.warn('Livewire bet debit error', e); }
        bjBet = v;
        blackjackBetDisplay.textContent = v.toLocaleString('fr-FR') + ' €';
        blackjackWinDisplay.textContent = '0 €';
        blackjackStartSection.classList.add('hidden');
        blackjackGameSection.classList.remove('hidden');
        bjStart();
    });
    blackjackHitBtn && blackjackHitBtn.addEventListener('click', () => {
        if(bjFinished) return;
        bjPlayer.push(bjDeal());
        bjRenderHands(false);
        if(bjHandValue(bjPlayer) >= 21){
            bjFinish();
        }
    });
    blackjackStandBtn && blackjackStandBtn.addEventListener('click', () => {
        if(bjFinished) return;
        bjDealerPlay();
        bjFinish();
    });

    function getPlayerBalance() {
        const el = document.getElementById('playerMeta');
        if (!el) return 0;
        const v = parseFloat(el.getAttribute('data-balance') || '0');
        return isNaN(v) ? 0 : v;
    }

    function debitMise(mise) {
        const solde = getPlayerBalance();

        // Cas spécial : pari fixe de 10k, autorisé même si solde < mise
        if (mise === 10000) {
            try {
                if (window.Livewire) {
                    const comp = lwComp();
                    if (comp && typeof comp.call === 'function') {
                        comp.call('onBlackjackBet10kFixed');
                        return true;
                    }
                }
            } catch (e) {
                console.warn('Erreur débit mise 10k fixed', e);
                return false;
            }
            return false;
        }

        // Cas normal : vérifier solde
        if (mise > solde) {
            console.warn('Solde insuffisant');
            return false;
        }

        try {
            if (window.Livewire) {
                const comp = lwComp();
                if (comp && typeof comp.call === 'function') {
                    comp.call('onBlackjackBetPlaced', parseFloat(mise));
                    return true;
                }
            }
        } catch (e) {
            console.warn('Erreur débit mise', e);
            return false;
        }

        return false;
    }

    blackjackRestartBtn && blackjackRestartBtn.addEventListener('click', () => {
        if (blackjackKeepBet && blackjackKeepBet.checked) {
            if (debitMise(bjBet)) {
                // Ok → on relance
                blackjackStartSection.classList.add('hidden');
                blackjackGameSection.classList.remove('hidden');
                blackjackBetDisplay.textContent = bjBet.toLocaleString('fr-FR') + ' €';
                blackjackWinDisplay.textContent = '0 €';
                bjStart();
            } else {
                // Échec → retour saisie mise
                bjFinished = true;
                blackjackGameSection.classList.add('hidden');
                blackjackStartSection.classList.remove('hidden');
                blackjackBetInput.value = bjBet.toString();
                blackjackBetInput.focus();
            }
        } else {
            bjFinished = true;
            blackjackGameSection.classList.add('hidden');
            blackjackStartSection.classList.remove('hidden');
            blackjackBetInput.value = bjBet.toString();
            blackjackBetInput.focus();
        }
    });

    function renderJoueursSelectionnes(){
        if(!listeJoueursSelectionnes) return;
        listeJoueursSelectionnes.innerHTML = joueursSelectionnes.map(j=>`
           <span class="flex items-center gap-1 bg-red-800/30 border border-red-700/50 px-2 py-1 rounded text-[11px]">
             ${j.pseudo}
             <button type="button" data-remove-joueur="${j.id}" class="text-red-300 hover:text-white font-bold ml-1">×</button>
           </span>
        `).join('');
        updateArbitreRequirement();
        updateTotalPotentiel();
    }

    function updateArbitreRequirement(){
        if(!arbitreObligatoireBadge) return;
        if(joueursSelectionnes.length > 3){
            arbitreObligatoireBadge.classList.remove('hidden');
        } else {
            arbitreObligatoireBadge.classList.add('hidden');
        }
    }

    function filtrerUtilisateurs(terme){
        const t = terme.toLowerCase();
        return poolUtilisateurs.filter(u=> u.pseudo.toLowerCase().includes(t) || (''+u.id).includes(t)).slice(0,8);
    }

    function afficherResultatsRecherche(inputEl, resultsEl, onSelect){
        if(!inputEl || !resultsEl) return;
        const terme = inputEl.value.trim();
        if(!terme){ resultsEl.classList.add('hidden'); resultsEl.innerHTML=''; return; }
        const matches = filtrerUtilisateurs(terme);
        if(!matches.length){ resultsEl.innerHTML='<div class="px-3 py-2 text-gray-500">Aucun résultat</div>'; resultsEl.classList.remove('hidden'); return; }
        resultsEl.innerHTML = matches.map(m=>`<button type="button" data-select-id="${m.id}" class="w-full text-left px-3 py-2 hover:bg-red-800/40 flex justify-between items-center"> <span>${m.pseudo}</span><span class="text-[10px] text-gray-400">#${m.id}</span></button>`).join('');
        resultsEl.classList.remove('hidden');
        resultsEl.querySelectorAll('[data-select-id]').forEach(btn=>{
            btn.addEventListener('click',()=>{
                const id=parseInt(btn.getAttribute('data-select-id'));
                const user=poolUtilisateurs.find(u=>u.id===id);
                if(user) onSelect(user);
                resultsEl.classList.add('hidden');
            });
        })
    }

    rechercheJoueur && rechercheJoueur.addEventListener('input', ()=>{
        afficherResultatsRecherche(rechercheJoueur, resultatsRechercheJoueur, (user)=>{
            if(!joueursSelectionnes.some(j=>j.id===user.id)){
                joueursSelectionnes.push(user);
                renderJoueursSelectionnes();
            }
            rechercheJoueur.value='';
        });
    });

    rechercheArbitre && rechercheArbitre.addEventListener('input', ()=>{
        afficherResultatsRecherche(rechercheArbitre, resultatsRechercheArbitre, (user)=>{
            arbitre = user;
            arbitreSelectionne.innerHTML = `<div class=\"flex items-center gap-2 bg-emerald-800/20 border border-emerald-600/40 px-3 py-2 rounded\"><span class=\"text-emerald-300 font-semibold\">${user.pseudo}</span><button type=\"button\" id=\"retirerArbitreBtn\" class=\"text-emerald-300 hover:text-white font-bold\">×</button></div>`;
            rechercheArbitre.value='';
            resultatsRechercheArbitre.classList.add('hidden');
            document.getElementById('retirerArbitreBtn').addEventListener('click',()=>{ arbitre=null; arbitreSelectionne.innerHTML=''; });
        });
    });

    ajouterJoueurBtn && ajouterJoueurBtn.addEventListener('click', ()=>{
        // Ajoute un joueur fictif aléatoire différent si possible
        const restants = poolUtilisateurs.filter(u=> !joueursSelectionnes.some(j=>j.id===u.id));
        if(restants.length){
            const pick = restants[Math.floor(Math.random()*restants.length)];
            joueursSelectionnes.push(pick);
            renderJoueursSelectionnes();
        }
    });

    listeJoueursSelectionnes && listeJoueursSelectionnes.addEventListener('click', (e)=>{
        const btn = e.target.closest('[data-remove-joueur]');
        if(btn){
            const id = parseInt(btn.getAttribute('data-remove-joueur'));
            joueursSelectionnes = joueursSelectionnes.filter(j=>j.id!==id);
            renderJoueursSelectionnes();
        }
    });

    function validerInscriptionPartie(){
        messageErreurInscription.classList.add('hidden');
        const nom = (partieNom?.value||'').trim();
        if(!nom){ return afficherErreur('Nom de la partie requis'); }
        if(joueursSelectionnes.length===0){ return afficherErreur('Au moins un joueur'); }
        if(joueursSelectionnes.length>3 && !arbitre){ return afficherErreur('Arbitre obligatoire (>3 joueurs)'); }
        const mise = parseFloat(inscriptionMise.value)||0;
        if(mise<=0){ return afficherErreur('Mise commune invalide'); }
        const payload = {
            nom,
            description: (partieDescription?.value||'').trim(),
            joueurs: joueursSelectionnes,
            arbitre: arbitre,
            mise,
            totalPotentiel: mise * joueursSelectionnes.length
        };
        alert('(Démo) Partie créée:\n'+JSON.stringify(payload,null,2));
        closeModal(modalInscriptionPhysique);
    }
    function afficherErreur(msg){
        messageErreurInscription.textContent = msg;
        messageErreurInscription.classList.remove('hidden');
    }

    confirmerInscriptionPhysiqueBtn && confirmerInscriptionPhysiqueBtn.addEventListener('click', validerInscriptionPartie);
    inscriptionMise && inscriptionMise.addEventListener('input', updateInscriptionAffichage);
    openInscriptionPhysiqueBtn && openInscriptionPhysiqueBtn.addEventListener('click', () => {
        updateInscriptionAffichage();
        openModal(modalInscriptionPhysique);
    });
    confirmerInscriptionPhysiqueBtn && confirmerInscriptionPhysiqueBtn.addEventListener('click', () => {
        const v = parseFloat(inscriptionMise.value)||0;
        alert('(Démo) Proposition enregistrée: ' + v.toLocaleString('fr-FR') + ' €');
        closeModal(modalInscriptionPhysique);
    });

    filterButtons.forEach(b => {
        b.addEventListener('click', () => {
            filterButtons.forEach(x=>x.classList.remove('active','bg-red-700','text-white'));
            filterButtons.forEach(x=>x.classList.add('bg-gray-800','text-red-300'));
            b.classList.add('active','bg-red-700','text-white');
            b.classList.remove('bg-gray-800','text-red-300');
            renderParis(b.getAttribute('data-filter'));
        });
    });

    // Délégation click paris
    listeContainer.addEventListener('click', (e) => {
        const card = e.target.closest('[data-pari-id]');
        if(!card) return;
        const id = parseInt(card.getAttribute('data-pari-id'));
        // Empêcher la sélection si le pari est fermé
        const pMeta = paris.find(p=>p.id===id);
        if(!pMeta) return;
        if ((pMeta.type && pMeta.type === 'ferme') || (pMeta.status && pMeta.status === 'ferme')) { return; }
        if (hasActiveBetOn(id)) { return; }
        pariSelectionne = pMeta;
        if(!pariSelectionne) return;
        const [label, cls] = badgeType(pariSelectionne.type);
        pariTitre.textContent = pariSelectionne.titre;
        pariDescription.innerHTML = `<p class=\"mb-2\">${pariSelectionne.description}</p><span class=\"text-xs px-2 py-1 rounded-full ${cls}\">${label}</span>`;
    pariType.textContent = label;
    renderChoices(pariSelectionne);
    const firstChoice = pariSelectionne.choices[0];
    pariSelectionne.selectedChoice = firstChoice.id;
    updateDisplayedOdds();
    // Dynamic limits per balance
    const bal = parseFloat(playerMeta ? (playerMeta.getAttribute('data-balance')||'0') : '0');
    const dynMin = Math.floor(Math.max(bal * 0.05, 10000));
    const dynMax = Math.floor(Math.max(1000000, bal * 0.50));
    // pariMise.setAttribute('min', String(dynMin));
    pariMise.setAttribute('max', String(dynMax));
    pariMise.value = String(dynMin);
    pariMiseMin.textContent = dynMin;
    pariMiseMax.textContent = dynMax;
        updateGain();
        closeModal(modalListe);
        openModal(modalDetails);
    });

    function updateGain(){
        if(!pariSelectionne) return;
        let mise = parseFloat(pariMise.value)||0;
    const minAttr = parseFloat(pariMise.getAttribute('min')||'0');
    const maxAttr = parseFloat(pariMise.getAttribute('max')||'0');
    if(mise < minAttr) mise = minAttr;
    if(mise > maxAttr) mise = maxAttr;
        pariMise.value = mise;
        const oddsMap = computeOdds(pariSelectionne);
        const currentOdds = oddsMap[pariSelectionne.selectedChoice] || 0;
        pariGainPotentiel.textContent = formatEuros(mise * currentOdds);
    }
    pariMise && pariMise.addEventListener('input', updateGain);

    function renderChoices(pari){
        const container = document.getElementById('pariChoices');
        if(!container) return;
        const odds = computeOdds(pari);
        container.innerHTML = pari.choices.map(c => {
            const active = pari.selectedChoice === c.id;
            const o = odds[c.id] || 0;
            const total = Math.max(1, pari.choices.reduce((s,x)=>s+x.participants,0));
            return `<button type=\"button\" data-choice=\"${c.id}\" class=\"w-full text-left px-3 py-2 rounded-lg border ${active?'border-red-500 bg-red-800/30':'border-red-800/40 bg-black/40 hover:border-red-500'} transition group mb-1\">\n                <div class=\"flex justify-between items-center\">\n                    <span class=\"font-semibold text-sm text-red-200\">${c.label}</span>\n                    <span class=\"text-xs font-mono text-green-400\">${o.toFixed(2)}</span>\n                </div>\n                <div class=\"flex justify-between mt-1 text-[10px] text-gray-400\">\n                    <span>${c.participants} part.</span>\n                    <span>${(c.participants/total*100).toFixed(1)}%</span>\n                </div>\n            </button>`;
        }).join('');
    }

    function updateDisplayedOdds(){
        if(!pariSelectionne) return; const oddsMap = computeOdds(pariSelectionne); pariCote.textContent = (oddsMap[pariSelectionne.selectedChoice]||0).toFixed(2);
    }

    document.addEventListener('click', (e) => {
        if(!pariSelectionne) return;
        const btn = e.target.closest('[data-choice]');
        if(btn){
            pariSelectionne.selectedChoice = btn.getAttribute('data-choice');
            renderChoices(pariSelectionne);
            updateDisplayedOdds();
            updateGain();
        }
    });

    confirmerPariBtn && confirmerPariBtn.addEventListener('click', () => {
        if(!pariSelectionne) return;
        const mise = parseFloat(pariMise.value)||0;
        const oddsMap = computeOdds(pariSelectionne);
        const currentOdds = oddsMap[pariSelectionne.selectedChoice] || 0;
        // Trouver la choiceId numérique pour l'appel serveur
        const choice = (pariSelectionne.choices||[]).find(c=>String(c.id)===String(pariSelectionne.selectedChoice));
        const choiceId = choice ? parseInt(choice.choiceId) : NaN;
        if(Number.isNaN(choiceId)){
            const evt = new CustomEvent('bet-error', { detail: { message: 'Choix invalide' } });
            window.dispatchEvent(evt);
            return;
        }
        // Empêcher un doublon de pari sur le même événement
        if (pariesActifs.some(b=>parseInt(b.ref)===parseInt(pariSelectionne.id))) { return; }
        try {
            const comp = lwComp();
            if (comp && typeof comp.call === 'function') {
                comp.call('placeBet', parseInt(pariSelectionne.id), choiceId, parseFloat(mise));
            }
        } catch(e) { console.warn('Livewire placeBet error', e); }
    });

    // Fermeture modals générique
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetId = btn.getAttribute('data-close');
            if(targetId === 'modalBlackjack' && !bjFinished){
                // Indication visuelle
                if(blackjackStatus){
                    const prev = blackjackStatus.textContent;
                    blackjackStatus.textContent = 'Terminez la main avant de fermer';
                    blackjackStatus.classList.add('text-yellow-300');
                    setTimeout(()=>{ blackjackStatus.textContent = prev; blackjackStatus.classList.remove('text-yellow-300'); }, 1500);
                }
                e.preventDefault();
                return;
            }
            const target = document.getElementById(targetId);
            if(target) closeModal(target);
        });
    });
    // Création nouveau pari
    function setNewBetError(msg){ if(!newBetError) return; if(!msg){ newBetError.classList.add('hidden'); newBetError.textContent=''; return; } newBetError.textContent = msg; newBetError.classList.remove('hidden'); }
    createNewBetBtn && createNewBetBtn.addEventListener('click', () => {
        const desc = (newBetDesc?.value||'').trim();
        const c1 = (newBetChoice1?.value||'').trim();
        const c2 = (newBetChoice2?.value||'').trim();
        const c3 = (newBetChoice3?.value||'').trim();
        if(!desc || !c1 || !c2){ setNewBetError('Description et deux choix minimum requis'); return; }
        setNewBetError('');
        try {
            if (window.Livewire) {
                const comp = lwComp();
                if (comp && typeof comp.call === 'function') {
                    comp.call('createBetEvent', desc, c1, c2, c3 || null);
                }
            }
        } catch(e) { console.warn('Livewire createBetEvent error', e); }
        // MAJ optimiste: ajouter à la liste locale
        const newId = Math.max(0, ...paris.map(p=>parseInt(p.id)||0)) + 1;
        const item = { id: newId, titre: desc, type: 'disponible', miseMin: 1000, miseMax: 100000, margin: 0.90, description: desc, choices: [ { id:'A', label:c1, participants:0 }, { id:'B', label:c2, participants:0 } ] };
        if(c3){ item.choices.push({ id:'C', label:c3, participants:0 }); }
        paris.unshift(item);
        renderParis();
        closeModal(modalNewBet);
        // Ouvrir le listing des paris si pas déjà
        openModal(modalListe);
    });
    window.addEventListener('keydown', e => {
    if(e.key === 'Escape') { closeModal(modalDetails); closeModal(modalListe); closeModal(modalPariesEnCours); closeModal(modalInscriptionPhysique); if(bjFinished) closeModal(modalBlackjack); closeModal(modalHistorique); }
    });
});
</script>

<!-- Fin composant -->
</div>
