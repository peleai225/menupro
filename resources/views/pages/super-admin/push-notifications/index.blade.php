<x-layouts.admin-super title="Notifications Push">
    <div class="space-y-6 max-w-3xl">

        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Statut Firebase --}}
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-5">
            <h2 class="font-semibold text-neutral-900 mb-3">Statut Firebase FCM</h2>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $stats['fcm_configured'] ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }}">
                    @if($stats['fcm_configured'])
                        <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-sm font-medium text-emerald-700">Firebase configuré — envoi push actif</span>
                    @else
                        <span class="w-3 h-3 bg-amber-400 rounded-full"></span>
                        <div>
                            <p class="text-sm font-medium text-amber-700">Firebase non configuré</p>
                            <p class="text-xs text-amber-600 mt-0.5">
                                Ajoutez la clé serveur dans
                                <a href="{{ route('super-admin.settings') }}#delivery" class="underline">Paramètres → Livraison & Cartes</a>
                            </p>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-neutral-50 border border-neutral-200">
                    <span class="text-2xl font-bold text-primary-600">{{ $stats['drivers_with_token'] }}</span>
                    <span class="text-sm text-neutral-600">livreurs avec token push</span>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-neutral-50 border border-neutral-200">
                    <span class="text-2xl font-bold text-emerald-600">{{ $stats['drivers_online'] }}</span>
                    <span class="text-sm text-neutral-600">livreurs en ligne</span>
                </div>
            </div>
        </div>

        {{-- Formulaire d'envoi --}}
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
            <h2 class="font-semibold text-neutral-900 mb-5">Envoyer une notification push</h2>

            <form method="POST" action="{{ route('super-admin.push.send') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Audience *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" x-data="{ audience: 'all_drivers' }">
                        <label class="relative flex cursor-pointer rounded-xl border p-4 gap-3 transition-colors"
                               :class="audience === 'all_drivers' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 bg-white hover:border-neutral-300'">
                            <input type="radio" name="audience" value="all_drivers" x-model="audience" class="sr-only">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900">Tous les livreurs</p>
                                <p class="text-xs text-neutral-500">{{ $stats['drivers_with_token'] }} tokens</p>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-xl border p-4 gap-3 transition-colors"
                               :class="audience === 'online_drivers' ? 'border-emerald-500 bg-emerald-50' : 'border-neutral-200 bg-white hover:border-neutral-300'">
                            <input type="radio" name="audience" value="online_drivers" x-model="audience" class="sr-only">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M12 12a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900">Livreurs en ligne</p>
                                <p class="text-xs text-neutral-500">{{ $stats['drivers_online'] }} actifs</p>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-xl border p-4 gap-3 transition-colors"
                               :class="audience === 'all' ? 'border-indigo-500 bg-indigo-50' : 'border-neutral-200 bg-white hover:border-neutral-300'">
                            <input type="radio" name="audience" value="all" x-model="audience" class="sr-only">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900">Tous (y compris inactifs)</p>
                                <p class="text-xs text-neutral-500">Broadcast total</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Titre *</label>
                    <input type="text" name="title" required maxlength="100" placeholder="ex: Nouvelle zone disponible !"
                           class="w-full h-11 px-4 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Message *</label>
                    <textarea name="body" required maxlength="500" rows="3" placeholder="Contenu de la notification..."
                              class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Clé data (optionnel)</label>
                        <input type="text" name="data_key" maxlength="50" placeholder="ex: type"
                               class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Valeur data (optionnel)</label>
                        <input type="text" name="data_val" maxlength="255" placeholder="ex: new_delivery"
                               class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-neutral-500">Les notifications push arrivent en temps réel sur l'app des livreurs.</p>
                    <button type="submit" {{ !$stats['fcm_configured'] ? 'disabled' : '' }}
                            class="px-6 h-11 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin-super>
