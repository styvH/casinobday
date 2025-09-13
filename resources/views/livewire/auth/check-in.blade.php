<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-900 via-black to-red-700 text-white">
    <div class="w-full max-w-md bg-black/75 border border-red-800 rounded-2xl shadow-xl p-8">
        <h1 class="text-3xl font-bold mb-6 text-center text-red-400">Check-In</h1>
        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-1">Email</label>
                <input wire:model.defer="email" type="email" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 rounded-lg px-4 py-2 text-sm" placeholder="you@example.com" />
                @error('email')<div class="text-[11px] text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-red-300 mb-1">Mot de passe</label>
                <input wire:model.defer="password" type="password" class="w-full bg-black/60 border border-red-700 focus:ring-2 focus:ring-red-600 rounded-lg px-4 py-2 text-sm" placeholder="••••••" />
                @error('password')<div class="text-[11px] text-red-400 mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 select-none">
                    <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-red-700 bg-black/60 text-red-600 focus:ring-red-600" />
                    <span>Se souvenir</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-gray-300 hover:text-white">Mot de passe oublié ?</a>
            </div>
            <button type="submit" class="w-full py-2.5 bg-red-700 hover:bg-red-800 rounded-lg font-semibold shadow ring-1 ring-red-600/50 transition">Se connecter</button>
        </form>
    </div>
</div>
