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
@php
    $solde = 12500; // fictif
    $pariesEnCours = 3;
    $totalMise = 4500;
    $gainsFuturs = 9800;
@endphp

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
    <main class="w-full max-w-3xl bg-black bg-opacity-80 rounded-xl shadow-lg p-4 md:p-8 border-4 border-red-800 mt-20 md:mt-0">
        <h2 class="text-2xl md:text-4xl font-bold mb-4 md:mb-6 text-center text-red-500">Dashboard Joueur</h2>
        <div class="flex flex-col items-center mb-6 md:mb-8">
            <div class="text-xl md:text-2xl font-semibold mb-1 md:mb-2">Solde du compte</div>
            <div class="flex items-center gap-3 mb-3 md:mb-4">
                <div class="text-3xl md:text-5xl font-extrabold text-red-400">{{ number_format($solde, 0, ',', ' ') }} €</div>
                <button class="text-xs md:text-sm px-3 py-2 md:py-2 bg-red-700 hover:bg-red-800 rounded-lg font-semibold shadow ring-1 ring-red-500/60 hover:ring-red-400 transition">
                    Faire un don
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 mb-6 md:mb-8">
            <button id="openPariModalBtn" class="px-4 md:px-6 py-3 md:py-4 bg-red-700 hover:bg-red-900 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">🎲</span>
                Lancer un pari
            </button>
            <button class="px-4 md:px-6 py-3 md:py-4 bg-black hover:bg-red-800 text-white font-bold rounded-xl shadow-lg border border-red-700 transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">📝</span>
                Inscription partie physique
            </button>
            <button class="px-4 md:px-6 py-3 md:py-4 bg-red-900 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center text-sm md:text-base">
                <span class="text-xl md:text-2xl mb-1 md:mb-2">📜</span>
                Historique
            </button>
        </div>
        <div class="mt-6 md:mt-8 text-center">
            <p class="text-gray-400 text-sm md:text-base">Engagez la partie et montez au classement.</p>
        </div>
    </main>
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

    openBtn.addEventListener('click', () => { renderParis(); openModal(modalListe); });

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
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.getAttribute('data-close'));
            if(target) closeModal(target);
        });
    });
    window.addEventListener('keydown', e => {
    if(e.key === 'Escape') { closeModal(modalDetails); closeModal(modalListe); closeModal(modalPariesEnCours); }
    });
});
</script>

<!-- Fin composant -->
</div>
