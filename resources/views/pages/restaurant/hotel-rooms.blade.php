<x-layouts.admin-restaurant title="Chambres hôtel">

    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">Chambres & QR codes hôtel</h1>
                <p class="text-neutral-500 mt-1">Chaque chambre obtient un QR code unique. La commande arrive avec le nom de la chambre.</p>
            </div>
            @if($rooms->isNotEmpty())
                <a href="{{ route('restaurant.rooms.download-pdf') }}"
                   class="btn btn-primary whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Télécharger tous les QR (PDF)
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-8">

            {{-- Ajouter une chambre --}}
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
                <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-violet-500 text-white rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    Ajouter une chambre
                </h2>

                <form x-data="{ adding: false, newName: '' }"
                      @submit.prevent="
                          if (!newName.trim()) return;
                          adding = true;
                          fetch('{{ route('restaurant.rooms.store') }}', {
                              method: 'POST',
                              headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'},
                              body: JSON.stringify({name: newName})
                          }).then(r => r.json()).then(d => {
                              adding = false;
                              if (d.success) { newName = ''; window.location.reload(); }
                              else { alert(d.message || 'Erreur'); }
                          }).catch(() => { adding = false; alert('Erreur réseau'); })
                      "
                      class="flex gap-3">
                    @csrf
                    <input type="text"
                           x-model="newName"
                           required
                           maxlength="100"
                           class="input flex-1"
                           placeholder="Ex: Chambre 101, Suite Royale, R1...">
                    <button type="submit" :disabled="adding" class="btn btn-primary whitespace-nowrap flex items-center gap-2">
                        <svg x-show="adding" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="adding ? '...' : 'Ajouter'"></span>
                    </button>
                </form>

                <p class="text-xs text-neutral-400 mt-3">
                    Exemples : <span class="font-mono">R1</span>, <span class="font-mono">Chambre 12</span>, <span class="font-mono">Suite Présidentielle</span>
                </p>

                @if($rooms->isNotEmpty())
                    <div class="mt-6 p-4 bg-violet-50 rounded-xl border border-violet-100">
                        <p class="text-sm font-semibold text-violet-800 mb-1">Comment ça marche</p>
                        <p class="text-xs text-violet-700">
                            Placez le QR code dans chaque chambre. Quand le client scanne, sa commande arrive à l'écran cuisine avec le nom de la chambre (ex : <strong>Chambre 101</strong>).
                        </p>
                    </div>
                @endif
            </div>

            {{-- Liste des chambres --}}
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
                <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    {{ $rooms->count() }} chambre(s) configurée(s)
                </h2>

                @if($rooms->isEmpty())
                    <div class="text-center py-10 text-neutral-400">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <p class="text-sm">Aucune chambre. Ajoutez-en une ci-contre.</p>
                    </div>
                @else
                    <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                        @foreach($rooms as $room)
                            <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-xl group"
                                 x-data="{ editing: false, name: '{{ addslashes($room->name) }}' }">

                                <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>

                                <template x-if="!editing">
                                    <span class="flex-1 font-medium text-neutral-800 text-sm">{{ $room->name }}</span>
                                </template>
                                <template x-if="editing">
                                    <form method="POST" action="{{ route('restaurant.rooms.update', $room) }}" class="flex-1 flex gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" x-model="name" required maxlength="100"
                                               class="input flex-1 py-1.5 text-sm">
                                        <button type="submit" class="btn btn-primary text-xs py-1.5 px-3">OK</button>
                                        <button type="button" @click="editing=false; name='{{ addslashes($room->name) }}'"
                                                class="btn btn-secondary text-xs py-1.5 px-3">✕</button>
                                    </form>
                                </template>

                                <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" x-show="!editing">
                                    <button @click="editing=true"
                                            class="w-7 h-7 rounded-lg bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-600 transition-colors"
                                            title="Renommer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('restaurant.rooms.destroy', $room) }}"
                                          onsubmit="return confirm('Supprimer {{ addslashes($room->name) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 flex items-center justify-center text-red-600 transition-colors"
                                                title="Supprimer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Info plan --}}
        <div class="mt-6 bg-violet-50 border border-violet-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 bg-violet-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-violet-900">Fonctionnalité Hôtel — Plan Pro / Business</p>
                <p class="text-xs text-violet-700 mt-0.5">Les commandes provenant de chambres affichent le nom de la chambre sur l'écran cuisine et dans l'historique des commandes.</p>
            </div>
        </div>
    </div>

</x-layouts.admin-restaurant>
