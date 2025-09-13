<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-900 via-black to-red-700 text-white">
    <div class="w-full max-w-md bg-black/75 border border-red-800 rounded-2xl shadow-xl p-8">
        <h1 class="text-3xl font-bold mb-6 text-center text-red-400">Mot de passe oublié</h1>
        @if($status)
            <div class="mb-4 text-green-400 text-sm">{{ $status }}</div>
        @endif
        <form wire:submit.prevent="sendLink" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-1">Email</label>
                <input wire:model.defer="email" type="email" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 rounded-lg px-4 py-2 text-sm" placeholder="you@example.com" />
                @error('email')<div class="text-[11px] text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="w-full py-2.5 bg-red-700 hover:bg-red-800 rounded-lg font-semibold shadow ring-1 ring-red-600/50 transition">Envoyer le lien</button>
        </form>
        <div class="mt-4 text-center text-xs text-gray-400">
            <a href="{{ route('login') }}" class="hover:text-white">Retour connexion</a>
        </div>
    </div>
    </div>
