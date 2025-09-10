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
@php /* Les anciennes valeurs fictives ont été remplacées par des propriétés Livewire dynamiques */ @endphp

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-900 via-black to-red-700 text-white">
@php
    // Données fictives pour le classement
    $classementJoueurs = [];
    for ($i = 1; $i <= 50; $i++) {
        $classementJoueurs[] = [
            'pseudo' => 'Joueur' . $i,
            'points' => rand(1000, 20000)
        ];
    }
    // Tri décroissant par points
    usort($classementJoueurs, function($a, $b) {
        return $b['points'] <=> $a['points'];
    });
@endphp

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
                @foreach($classementJoueurs as $index => $joueur)
                    @if($index < 10)
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
                                <span>{{ $joueur['pseudo'] }}</span>
                            </div>
                            <span class="font-mono">{{ number_format($joueur['points'], 0, ',', ' ') }} pts</span>
                        </li>
                    @endif
                @endforeach
            </ul>
            <details class="px-6 py-2">
                <summary class="cursor-pointer text-red-400 hover:underline">Voir le reste du classement</summary>
                <ul class="mt-2">
                    @foreach($classementJoueurs as $index => $joueur)
                        @if($index >= 10)
                            <li class="flex items-center justify-between mb-1 p-1 rounded transition bg-black bg-opacity-30 text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold">{{ $index+1 }}</span>
                                    <span>{{ $joueur['pseudo'] }}</span>
                                </div>
                                <span class="font-mono text-xs">{{ number_format($joueur['points'], 0, ',', ' ') }} pts</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </details>
        </div>
    </div>
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
        <h2 class="text-2xl md:text-4xl font-bold mb-4 md:mb-6 text-center text-red-500">Dashboard Joueur</h2>
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
            $adminPlayersPayload = [
                'currentUserId' => auth()->id(),
                'players' => ($allPlayers ?? collect())->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
            ];
        @endphp
        <script id="adminPlayersData" type="application/json">{!! json_encode($adminPlayersPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('toggleClassementMobile');
    const panel = document.getElementById('classementPanel');
    if(btn && panel){
        btn.addEventListener('click', () => {
            panel.classList.toggle('hidden');
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
            <button class="filter-pari bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full" data-filter="annonce">Annoncés</button>
            <button class="filter-pari bg-gray-800 hover:bg-gray-700 text-red-300 px-3 py-1 rounded-full" data-filter="en_cours">En cours</button>
        </div>
        <div class="px-5 pb-4 text-[10px] md:text-xs text-gray-400 italic">(Données fictives pour test)</div>
        <div class="flex-1 overflow-y-auto classement-scrollbar px-5 pb-5" id="listeParisContainer"></div>
        <div class="px-5 py-3 border-t border-red-800 flex justify-end bg-black/40">
            <button data-close="modalListeParis" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-sm font-semibold">Fermer</button>
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
                <input id="pariMise" type="number" min="1000" max="100000" value="1000" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-4 py-2 text-sm" />
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
<div id="modalBlackjack" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/80 backdrop-blur-sm">
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
                    <input id="blackjackBetInput" type="number" min="100" step="50" value="500" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 focus:outline-none rounded-lg px-3 py-2" />
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-2 text-[11px]">
                    <button type="button" data-bj-bet-quick="500" class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">500</button>
                    <button type="button" data-bj-bet-quick="1000" class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">1 000</button>
                    <button type="button" data-bj-bet-quick="2500" class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">2 500</button>
                    <button type="button" data-bj-bet-quick="5000" class="px-3 py-1 rounded bg-red-800/40 hover:bg-red-700/60">5 000</button>
                </div>
            </div>
            <div class="flex justify-end">
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
            <button id="openAdminBtn" type="button" class="px-3 py-2 rounded-md bg-red-700 hover:bg-red-800 text-xs font-semibold shadow ring-1 ring-red-500/40">Outils Admin</button>
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
<div id="modalAdmin" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/75 backdrop-blur-sm">
  <div class="w-11/12 md:w-3/4 lg:w-3/5 xl:w-1/2 bg-gradient-to-b from-gray-900 to-black border border-red-700 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-red-800">
        <h3 class="text-lg md:text-2xl font-bold text-red-400 flex items-center gap-2">🛠️ Administration</h3>
        <button data-close="modalAdmin" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
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
                        <input wire:model.defer="newPlayerBalance" type="number" step="0.01" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
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
                        <input wire:model.defer="injectionAmount" type="number" step="0.01" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600" />
                        @error('injectionAmount') <span class="text-[10px] text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Portée</label>
                        <select wire:model="injectionScope" class="w-full bg-black/60 border border-red-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-600">
                            <option value="all">Tous les joueurs</option>
                            <option value="selected">Liste sélectionnée</option>
                        </select>
                    </div>
                    <div class="md:col-span-1" x-data>
                        <label class="block text-[11px] font-semibold text-red-300 mb-1">Joueurs (si sélection)</label>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-400">Sélectionnés</span>
                                <button type="button" class="text-[10px] px-2 py-0.5 rounded bg-gray-700/60 hover:bg-gray-600" onclick="window.clearInjectionSelection && window.clearInjectionSelection()">Vider</button>
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
        <button data-close="modalAdmin" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs md:text-sm font-semibold">Fermer</button>
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
        <div class="px-5 pb-3 text-[10px] md:text-xs text-gray-400 italic">Dernières activités simulées (front uniquement).</div>
        <div id="historyList" class="flex-1 overflow-y-auto classement-scrollbar px-5 pb-4 divide-y divide-red-800/30 text-sm"></div>
        <div class="px-5 py-3 border-t border-red-800 bg-black/60 flex items-center justify-between">
                <button id="historyShowMoreBtn" class="px-4 py-1.5 rounded-lg bg-black/70 border border-red-700 text-red-300 hover:bg-red-800/40 hover:text-white text-xs font-semibold">Afficher plus</button>
                <button data-close="modalHistorique" class="px-4 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-xs font-semibold">Fermer</button>
        </div>
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
    const injectionScopeSelect = document.querySelector('[wire\\:model="injectionScope"]');
    const injectionSearch = document.getElementById('injectionSearch');
    const injectionResults = document.getElementById('injectionResults');
    const injectionSelectedTags = document.getElementById('injectionSelectedTags');
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
    function openInjectionResults(){ if(injectionResults) injectionResults.classList.remove('hidden'); }
    function closeInjectionResults(){ if(injectionResults) injectionResults.classList.add('hidden'); }
    function filterInjection(q){
        q = (q||'').toLowerCase();
    const selected = new Set(getInjectionSelected());
    return injectionPool.filter(u => u.id !== currentUserId && !selected.has(u.id) && (u.name.toLowerCase().includes(q) || (''+u.id).includes(q))).slice(0,30);
    }
    function renderInjectionResults(list){
        if(!injectionResults) return;
        if(!list.length){ injectionResults.innerHTML = '<div class="p-2 text-gray-400">Aucun résultat</div>'; return; }
        injectionResults.innerHTML = list.map(u => `<button type="button" data-inj-add="${u.id}" class="w-full text-left px-3 py-1.5 hover:bg-red-800/40 flex justify-between"><span>#${u.id} ${u.name}</span></button>`).join('');
    }
    injectionSearch && injectionSearch.addEventListener('input', () => {
        const list = filterInjection(injectionSearch.value);
        renderInjectionResults(list); openInjectionResults();
    });
    document.addEventListener('click', (e)=>{
        if(injectionResults && !injectionResults.contains(e.target) && injectionSearch !== e.target){ closeInjectionResults(); }
    });
    injectionResults && injectionResults.addEventListener('click', e => {
        const btn = e.target.closest('[data-inj-add]'); if(!btn) return;
        const id = parseInt(btn.getAttribute('data-inj-add'));
        const arr = getInjectionSelected().slice();
        if(!arr.includes(id)) arr.push(id);
        setInjectionSelected(arr);
        injectionSearch.value=''; renderInjectionSelected(); closeInjectionResults();
    });
    injectionSelectedTags && injectionSelectedTags.addEventListener('click', e => {
        const btn = e.target.closest('[data-inj-remove]'); if(!btn) return;
        const id = parseInt(btn.getAttribute('data-inj-remove'));
        let arr = getInjectionSelected().slice();
        arr = arr.filter(x=>x!==id);
        setInjectionSelected(arr); renderInjectionSelected();
    });
    document.addEventListener('livewire:load', renderInjectionSelected);
    // Logout refs
    const logoutConfirmBtn = document.getElementById('logoutConfirmBtn');
    const modalLogout = document.getElementById('modalLogout');

    let pariSelectionne = null;

    // Données fictives multi-issues (pari mutuel simplifié) : chaque pari a 2 ou 3 choix.
    // margin (<1) simule commission => cote = (1/share)*margin
    const paris = [
    { id:1, titre:'Duel Poker Express', type:'disponible', miseMin:1000, miseMax:100000, margin:0.92, description:'Heads-up rapide. Sélectionnez le vainqueur.', choices:[
            { id:'A', label:'Joueur A', participants:42 },
            { id:'B', label:'Joueur B', participants:58 },
        ]},
    { id:2, titre:'Tournoi Mini 6 joueurs', type:'annonce', miseMin:1000, miseMax:100000, margin:0.90, description:'Pariez sur le style de victoire.', choices:[
            { id:'ch', label:'Chip leader conserve', participants:20 },
            { id:'up', label:'Remontada', participants:31 },
            { id:'ko', label:'KO rapide final', participants:9 },
        ]},
    { id:3, titre:'Blackjack Série 5 mains', type:'en_cours', miseMin:1000, miseMax:100000, margin:0.94, description:'Résultat cumul des 5 prochaines mains.', choices:[
            { id:'p', label:'Player domine', participants:70 },
            { id:'d', label:'Dealer domine', participants:55 },
            { id:'eq', label:'Équilibré', participants:15 },
        ]},
    { id:4, titre:'Roulette Numéro chaud', type:'disponible', miseMin:1000, miseMax:100000, margin:0.88, description:'Le numéro chaud ressort-il ?', choices:[
            { id:'oui', label:'Oui', participants:33 },
            { id:'non', label:'Non', participants:47 },
        ]},
    { id:5, titre:'Sit & Go 3 joueurs', type:'annonce', miseMin:1000, miseMax:100000, margin:0.90, description:'Qui remporte la table ?', choices:[
            { id:'p1', label:'Seat 1', participants:10 },
            { id:'p2', label:'Seat 2', participants:14 },
            { id:'p3', label:'Seat 3', participants:6 },
        ]},
    { id:6, titre:'Duel High Stakes', type:'disponible', miseMin:1000, miseMax:100000, margin:0.93, description:'Match high stakes intense.', choices:[
            { id:'A', label:'Pro A', participants:8 },
            { id:'B', label:'Pro B', participants:12 },
        ]},
    { id:7, titre:'Tournoi 12 joueurs', type:'annonce', miseMin:1000, miseMax:100000, margin:0.90, description:'Style de fin probable.', choices:[
            { id:'hu', label:'Heads-Up long', participants:18 },
            { id:'burst', label:'Eliminations rapides', participants:22 },
            { id:'mid', label:'Rythme stable', participants:11 },
        ]},
    { id:8, titre:'Challenge Gains x2', type:'en_cours', miseMin:1000, miseMax:100000, margin:0.91, description:'Atteindra-t-on le double ?', choices:[
            { id:'dbl', label:'Oui doublé', participants:41 },
            { id:'no', label:'Non', participants:37 },
        ]},
    ];

    function formatEuros(n){
        return new Intl.NumberFormat('fr-FR',{minimumFractionDigits:0, maximumFractionDigits:2}).format(n) + ' €';
    }

    function badgeType(t){
        const map = { disponible:['Disponible','bg-green-700/50 text-green-300'], annonce:['Annoncé','bg-yellow-700/40 text-yellow-300'], en_cours:['En cours','bg-blue-700/40 text-blue-300'] };
        return map[t] || ['Autre','bg-gray-600/40 text-gray-300'];
    }

    function computeOdds(pari){
        const total = Math.max(1, pari.choices.reduce((s,c)=> s + c.participants, 0));
        const odds = {};
        pari.choices.forEach(c => {
            const share = c.participants / total;
            const raw = share === 0 ? 0 : (1 / share) * pari.margin;
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
            return `<div class=\"group border border-red-800/40 hover:border-red-500/70 transition rounded-xl p-4 mb-3 bg-black/40 hover:bg-black/60 cursor-pointer" data-pari-id=\"${p.id}\">
                <div class=\"flex items-start justify-between gap-3\">
                    <div class=\"flex-1\">
                        <div class=\"font-semibold text-red-300 group-hover:text-red-200\">${p.titre}</div>
                        <div class=\"text-[11px] mt-1 text-gray-400 leading-snug\">${p.description.substring(0,70)}...</div>
                        <div class=\"mt-2 flex flex-wrap items-center gap-2\">
                            <span class=\"text-xs px-2 py-0.5 rounded-full ${cls}\">${label}</span>
                            <span class=\"text-xs bg-red-800/40 text-red-300 px-2 py-0.5 rounded-full\">${nbChoices} choix</span>
                            <span class=\"text-[10px] text-gray-400\">${preview}</span>
                        </div>
                    </div>
                    <div class=\"text-right text-[10px] text-gray-400 space-y-1\">
                        <div>Min <span class=\"text-gray-200\">${p.miseMin}€</span></div>
                        <div>Max <span class=\"text-gray-200\">${p.miseMax}€</span></div>
                        <div class=\"pt-1\">Odds dyn.</div>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function openModal(el){ el.classList.remove('hidden'); el.classList.add('flex'); }
    function closeModal(el){ el.classList.add('hidden'); el.classList.remove('flex'); }

    openBtn && openBtn.addEventListener('click', () => { renderParis(); openModal(modalListe); });

    // Données fictives des paris en cours (référencent éventuellement un pari de la liste + choix)
    const pariesActifs = [
        { ref:1, choix:'A', mise:2500 },
        { ref:3, choix:'p', mise:1500 },
        { ref:4, choix:'non', mise:3000 },
        { ref:6, choix:'B', mise:5000 },
    ];

    function renderPariesEnCours(){
        if(!pariesActifs.length){
            listePariesEnCours.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">Aucun pari en cours.</div>';
            return;
        }
        listePariesEnCours.innerHTML = pariesActifs.map(pa => {
            const pari = paris.find(p=>p.id===pa.ref);
            if(!pari) return '';
            const oddsMap = computeOdds(pari);
            const choiceObj = pari.choices.find(c=>c.id===pa.choix);
            const cote = oddsMap[pa.choix] || 0;
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
    // Historique state
    const HISTORY_PAGE_SIZE = 10;
    let historyVisibleCount = HISTORY_PAGE_SIZE;
    let historyCurrentFilter = 'tous';
    // Mock history dataset (newest first)
    const mockHistory = (()=>{
        const types = ['pari','blackjack','transaction'];
        const icons = { pari:'🎲', blackjack:'🃏', transaction:'💰' };
        const arr = [];
        const now = Date.now();
        for(let i=0;i<36;i++){
            const type = types[i%types.length];
            let amount = 0; let desc='';
            if(type==='pari'){
                amount = (Math.random()>0.5?1:-1) * (500+Math.floor(Math.random()*4500));
                desc = 'Pari '+(amount>0?'gagné':'perdu')+' sur événement #'+(100+i);
            } else if(type==='blackjack'){
                amount = (Math.random()>0.55?1:-1) * (200+Math.floor(Math.random()*3000));
                desc = 'Blackjack '+(amount>0?'victoire':'défaite');
            } else {
                amount = (Math.random()>0.5?1:-1) * (100+Math.floor(Math.random()*2000));
                desc = amount>0? 'Crédit bonus': 'Achat jetons';
            }
            arr.push({
                id:i+1,
                type,
                icon: icons[type],
                amount,
                desc,
                ts: now - i* 1000*60* (5+Math.random()*20)
            });
        }
        return arr; // Already newest first by construction
    })();

    function formatDate(ts){
        const d = new Date(ts);
        return d.toLocaleString('fr-FR', {hour12:false});
    }
    function formatAmount(a){
        const sign = a>0?'+':'';
        return sign + a.toLocaleString('fr-FR') + ' €';
    }
    function historyFiltered(){
        if(historyCurrentFilter==='tous') return mockHistory;
        return mockHistory.filter(h=>h.type===historyCurrentFilter);
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
                    <button type='button' id='donationRemoveRecipient' class='text-red-300 hover:text-white font-bold text-xs'>×</button>
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
        // Append to history (front only)
        mockHistory.unshift({
            id: mockHistory.length+1,
            type: 'transaction',
            icon: '💝',
            amount: -amount,
            desc: 'Don à '+donationRecipient.pseudo,
            ts: Date.now()
        });
        // Reset filter to show added item if already open
        resetHistory();
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
    }
    function bjDealerPlay(){
        while(bjHandValue(bjDealer) < 17){
            bjDealer.push(bjDeal());
        }
    }
    // Events Blackjack
    openBlackjackBtn && openBlackjackBtn.addEventListener('click', () => {
        blackjackStartSection.classList.remove('hidden');
        blackjackGameSection.classList.add('hidden');
        openModal(modalBlackjack);
    });
    document.querySelectorAll('[data-bj-bet-quick]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const v = btn.getAttribute('data-bj-bet-quick');
            blackjackBetInput.value = v;
        });
    });
    blackjackStartBtn && blackjackStartBtn.addEventListener('click', () => {
        const v = parseFloat(blackjackBetInput.value)||0;
        if(v < 100){
            blackjackBetInput.classList.add('ring','ring-red-600');
            setTimeout(()=>blackjackBetInput.classList.remove('ring','ring-red-600'),800);
            return;
        }
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
    blackjackRestartBtn && blackjackRestartBtn.addEventListener('click', () => {
        if(blackjackKeepBet && blackjackKeepBet.checked){
            // Relancer immédiatement avec même mise
            blackjackStartSection.classList.add('hidden');
            blackjackGameSection.classList.remove('hidden');
            blackjackBetDisplay.textContent = bjBet.toLocaleString('fr-FR') + ' €';
            blackjackWinDisplay.textContent = '0 €';
            bjStart();
        } else {
            // Revenir à l'écran de mise pour ressaisir un montant
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
        pariSelectionne = paris.find(p=>p.id===id);
        if(!pariSelectionne) return;
        const [label, cls] = badgeType(pariSelectionne.type);
        pariTitre.textContent = pariSelectionne.titre;
        pariDescription.innerHTML = `<p class=\"mb-2\">${pariSelectionne.description}</p><span class=\"text-xs px-2 py-1 rounded-full ${cls}\">${label}</span>`;
    pariType.textContent = label;
    renderChoices(pariSelectionne);
    const firstChoice = pariSelectionne.choices[0];
    pariSelectionne.selectedChoice = firstChoice.id;
    updateDisplayedOdds();
        pariMise.value = pariSelectionne.miseMin;
        pariMiseMin.textContent = pariSelectionne.miseMin;
        pariMiseMax.textContent = pariSelectionne.miseMax;
        updateGain();
        closeModal(modalListe);
        openModal(modalDetails);
    });

    function updateGain(){
        if(!pariSelectionne) return;
        let mise = parseFloat(pariMise.value)||0;
        if(mise < pariSelectionne.miseMin) mise = pariSelectionne.miseMin;
        if(mise > pariSelectionne.miseMax) mise = pariSelectionne.miseMax;
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

    confirmerPariBtn.addEventListener('click', () => {
        if(!pariSelectionne) return;
        const mise = parseFloat(pariMise.value)||0;
        const oddsMap = computeOdds(pariSelectionne);
        const currentOdds = oddsMap[pariSelectionne.selectedChoice] || 0;
        alert(`(Démo) Pari confirmé: ${pariSelectionne.titre}\nChoix: ${pariSelectionne.selectedChoice}\nCôte: ${currentOdds.toFixed(2)}\nMise: ${mise} €\nGain potentiel: ${pariGainPotentiel.textContent}`);
        const choice = pariSelectionne.choices.find(c=>c.id===pariSelectionne.selectedChoice);
        if(choice){ choice.participants += 1; }
        closeModal(modalDetails);
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
    window.addEventListener('keydown', e => {
    if(e.key === 'Escape') { closeModal(modalDetails); closeModal(modalListe); closeModal(modalPariesEnCours); closeModal(modalInscriptionPhysique); if(bjFinished) closeModal(modalBlackjack); closeModal(modalHistorique); }
    });
});
</script>

<!-- Fin composant -->
</div>
