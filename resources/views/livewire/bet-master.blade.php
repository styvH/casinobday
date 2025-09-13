<div class="max-w-5xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-red-600">Gestion des Paris</h1>

    @if(!$isUnlocked)
        <div class="border border-red-700 rounded p-4 bg-black text-gray-100 shadow-sm shadow-red-900/30">
            <p class="mb-2">Entrer le mot de passe BetMaster pour déverrouiller 30 minutes.</p>
            <div class="flex gap-2 items-center">
                <input type="password" wire:model.defer="password" class="border border-red-600 bg-neutral-900 text-white p-2 rounded w-64 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-600" placeholder="Mot de passe" />
                <button wire:click="unlock" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded shadow">
                    Déverrouiller
                </button>
            </div>
            @if($flashError)
                <p class="text-red-400 mt-2">{{ $flashError }}</p>
            @endif
            @if($flashMessage)
                <p class="text-green-500 mt-2">{{ $flashMessage }}</p>
            @endif
        </div>
    @else
        <div class="border-l-4 border-red-700 rounded p-3 bg-neutral-900 text-gray-100 mb-4">
            <p class="mb-3">Accès BetMaster actif @if(!$isAdmin) pendant une durée limitée @endif.</p>
            <div class="flex flex-wrap items-center gap-2">
                <label class="text-sm text-gray-300">Filtrer par statut</label>
                <select wire:model.live="statusFilter" class="bg-black text-white border border-red-700 rounded px-2 py-1">
                    <option value="all">Tous</option>
                    <option value="disponible">Disponible</option>
                    <option value="ferme">Fermé</option>
                    <option value="cloture">Clôturé</option>
                </select>
            </div>
            @if($flashError)
                <p class="text-red-400 mt-2">{{ $flashError }}</p>
            @endif
            @if($flashMessage)
                <p class="text-green-500 mt-2">{{ $flashMessage }}</p>
            @endif
        </div>

        <div class="space-y-4">
            @if(isset($pgames) && $pgames->count())
                <div class="border border-emerald-800 rounded p-3 bg-neutral-900 text-gray-100">
                    <div class="text-emerald-300 font-semibold mb-2">🧩 Parties physiques (Admin)</div>
                    <div class="space-y-2">
                        @foreach($pgames as $g)
                            <div class="p-3 rounded border {{ $g->status==='active' ? 'border-emerald-700' : 'border-neutral-700' }} bg-black/40">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-emerald-200">#{{ $g->id }} - {{ $g->name }}</div>
                                        <div class="text-xs text-gray-400">Statut: {{ $g->status }} | Mise {{ number_format($g->stake_cents/100,0,',',' ') }} € | Pot {{ number_format($g->pot_cents/100,0,',',' ') }} €</div>
                                        <div class="text-[11px] text-gray-400 mt-1">Participants: {{ $g->participants->pluck('user.name')->filter()->join(', ') }}</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="settlePhysicalGame({{ $g->id }}, null, true)" class="px-3 py-1 rounded border border-red-700 text-red-200 bg-black hover:bg-neutral-950">Annuler</button>
                                    </div>
                                </div>
                                @if($g->status==='active')
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($g->participants as $p)
                                            <button wire:click="settlePhysicalGame({{ $g->id }}, {{ $p->user_id }}, false)" class="px-2 py-1 rounded bg-emerald-700 hover:bg-emerald-800 text-white text-xs">Gagnant: {{ optional($p->user)->name ?? ('#'.$p->user_id) }}</button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @forelse($events as $event)
                @php
                    $isFerme = $event->status === 'ferme';
                    $isCloture = $event->status === 'cloture';
                    $statusChip = match($event->status) {
                        'disponible' => 'bg-black text-white border border-red-600',
                        'annonce' => 'bg-red-700 text-white',
                        'en_cours' => 'bg-black text-red-400 border border-red-700',
                        'ferme' => 'bg-red-800 text-white',
                        'cloture' => 'bg-neutral-700 text-gray-200 border border-neutral-600',
                        default => 'bg-neutral-800 text-gray-200',
                    };
                @endphp
                <div class="border border-red-800 rounded p-3 bg-neutral-900 text-gray-100 shadow-sm shadow-red-900/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">#{{ $event->id }} - {{ $event->title }}</div>
                            <div class="text-sm text-gray-300">Statut:
                                <span class="px-2 py-0.5 rounded font-mono {{ $statusChip }}">{{ $event->status }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @if($isFerme)
                                <button wire:click="toggleEventStatus({{ $event->id }})" class="px-3 py-1 rounded border border-red-700 text-red-200 bg-black hover:bg-neutral-950 disabled:opacity-50 disabled:cursor-not-allowed" {{ $isCloture ? 'disabled' : '' }}>
                                    Ouvrir
                                </button>
                            @else
                                <button wire:click="toggleEventStatus({{ $event->id }})" class="px-3 py-1 rounded bg-red-700 hover:bg-red-800 text-white disabled:opacity-50 disabled:cursor-not-allowed" {{ $isCloture ? 'disabled' : '' }}>
                                    Fermer
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach($event->choices as $ch)
                            <div class="p-3 rounded text-white {{ $loop->odd ? 'bg-neutral-900 border border-red-800' : 'bg-red-900 border border-red-950' }}">
                                <div class="font-medium">{{ $ch->label }} <span class="text-xs text-red-300">({{ $ch->code }})</span></div>
                                <div class="text-xs text-gray-300">Participants: {{ $ch->participants_count }}</div>
                                <div class="mt-2">
                                    <button wire:click="openConfirmModal({{ $event->id }}, {{ $ch->id }})" class="text-sm bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed" {{ $event->status === 'cloture' ? 'disabled' : '' }}>
                                        Valider gagnant
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p>Aucun événement de pari.</p>
            @endforelse
        </div>
        
        @if($confirmModalOpen)
            <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
                <div class="w-full max-w-md bg-neutral-900 border border-red-800 rounded-lg shadow-xl p-4 text-gray-100">
                    <h2 class="text-lg font-semibold text-red-500 mb-2">Confirmer le gagnant</h2>
                    <p class="text-sm text-gray-300">Événement: <span class="font-medium">{{ $confirmEventTitle }}</span></p>
                    <p class="text-sm text-gray-300 mb-3">Choix sélectionné: <span class="font-medium">{{ $confirmChoiceLabel }}</span> <span class="text-xs text-red-300">({{ $confirmChoiceCode }})</span></p>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-300 mb-1">Sélectionner le code du choix</label>
                        <select wire:model.live="confirmSelectCode" class="w-full bg-black text-white border border-red-700 rounded px-2 py-2">
                            <option value="">Sélectionner A, B ou C</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                        @if($confirmSelectCode && strtoupper($confirmSelectCode) !== strtoupper($confirmChoiceCode))
                            <p class="text-xs text-red-400 mt-1">Le code saisi ne correspond pas au choix affiché.</p>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2">
                        <button wire:click="cancelConfirmModal" class="px-3 py-2 rounded border border-neutral-600 text-gray-200">Annuler</button>
                        <button wire:click="confirmSettlement" class="px-3 py-2 rounded bg-red-700 hover:bg-red-800 text-white disabled:opacity-50" {{ ($confirmSelectCode && strtoupper($confirmSelectCode) === strtoupper($confirmChoiceCode)) ? '' : 'disabled' }}>
                            Valider
                        </button>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($clotureEvents) && $clotureEvents->count() > 0 && $statusFilter !== 'cloture')
            <details class="mt-6">
                <summary class="cursor-pointer select-none px-3 py-2 bg-black text-white border border-neutral-700 rounded">
                    Voir les événements clôturés ({{ $clotureEvents->count() }})
                </summary>
                <div class="mt-3 space-y-4">
                    @foreach($clotureEvents as $event)
                        @php
                            $statusChip = 'bg-neutral-700 text-gray-200 border border-neutral-600';
                        @endphp
                        <div class="border border-neutral-800 rounded p-3 bg-neutral-900 text-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold">#{{ $event->id }} - {{ $event->title }}</div>
                                    <div class="text-sm text-gray-300">Statut:
                                        <span class="px-2 py-0.5 rounded font-mono {{ $statusChip }}">{{ $event->status }}</span>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-400">Clôturé</div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                                @foreach($event->choices as $ch)
                                    <div class="p-3 rounded text-white bg-neutral-900 border border-neutral-800">
                                        <div class="font-medium">{{ $ch->label }} <span class="text-xs text-gray-400">({{ $ch->code }})</span></div>
                                        <div class="text-xs text-gray-400">Participants: {{ $ch->participants_count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    @endif
</div>
