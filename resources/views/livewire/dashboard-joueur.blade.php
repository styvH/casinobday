@php
    $solde = 12500; // fictif
    $pariesEnCours = 3;
    $totalMise = 4500;
    $gainsFuturs = 9800;
@endphp

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-900 via-black to-red-700 text-white">
    <main class="w-full max-w-3xl bg-black bg-opacity-80 rounded-xl shadow-lg p-8 border-4 border-red-800">
        <h2 class="text-4xl font-bold mb-6 text-center text-red-500">Dashboard Joueur</h2>
        <div class="flex flex-col items-center mb-8">
            <div class="text-2xl font-semibold mb-2">Solde du compte</div>
            <div class="text-5xl font-extrabold text-red-400 mb-4">{{ number_format($solde, 0, ',', ' ') }} €</div>
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div class="bg-red-900 bg-opacity-80 rounded-lg p-4 text-center">
                    <div class="text-lg font-bold">Paries en cours</div>
                    <div class="text-3xl">{{ $pariesEnCours }}</div>
                </div>
                <div class="bg-black bg-opacity-80 rounded-lg p-4 text-center border border-red-700">
                    <div class="text-lg font-bold">Total Mise</div>
                    <div class="text-3xl">{{ number_format($totalMise, 0, ',', ' ') }} €</div>
                </div>
                <div class="bg-red-900 bg-opacity-80 rounded-lg p-4 text-center">
                    <div class="text-lg font-bold">Gains futurs</div>
                    <div class="text-3xl text-green-400">{{ number_format($gainsFuturs, 0, ',', ' ') }} €</div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <button class="px-6 py-4 bg-red-700 hover:bg-red-900 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center">
                <span class="text-2xl mb-2">🎲</span>
                Lancer un pari avec un autre joueur
            </button>
            <button class="px-6 py-4 bg-black hover:bg-red-800 text-white font-bold rounded-xl shadow-lg border border-red-700 transition flex flex-col items-center">
                <span class="text-2xl mb-2">📝</span>
                Demander l'inscription à une partie physique
            </button>
            <button class="px-6 py-4 bg-red-900 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg transition flex flex-col items-center">
                <span class="text-2xl mb-2">📜</span>
                Historique des transactions / paris / parties
            </button>
        </div>
        <div class="mt-8 text-center">
            <p class="text-gray-400">Engagez la partie et montez au classement.</p>
        </div>
    </main>
</div>
