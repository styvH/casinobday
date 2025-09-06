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
            <div class="text-3xl md:text-5xl font-extrabold text-red-400 mb-3 md:mb-4">{{ number_format($solde, 0, ',', ' ') }} €</div>
            <div class="grid grid-cols-3 gap-3 md:gap-6 mb-4 md:mb-6 w-full">
                <div class="bg-red-900 bg-opacity-80 rounded-lg p-2 md:p-4 text-center">
                    <div class="text-xs md:text-lg font-bold">Paries en cours</div>
                    <div class="text-xl md:text-3xl">{{ $pariesEnCours }}</div>
                </div>
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
            <button class="px-4 md:px-6 py-3 md:py-4 bg-red-700 hover:bg-red-900 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center text-sm md:text-base">
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
</div>

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
