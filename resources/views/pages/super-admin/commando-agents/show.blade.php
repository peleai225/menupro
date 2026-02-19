<x-layouts.admin-super title="Agent – {{ $agent->full_name }}">
    <div class="mb-6">
        <a href="{{ route('super-admin.commando.agents.index') }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium inline-flex items-center gap-1">
            ← Retour à la liste
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-green-400 text-sm">
            {{ session('success') }}
            @if(session('whatsapp_sent'))
                <span class="block mt-1">Un message WhatsApp a été envoyé à l'agent.</span>
            @endif
        </div>
    @endif
    @if(session('welcome_url'))
        <div class="mb-4 p-4 bg-amber-500/20 border border-amber-500/50 rounded-xl">
            <p class="text-amber-200 text-sm font-medium mb-2">Lien pour que l'agent définisse son mot de passe :</p>
            <div class="flex gap-2 flex-wrap">
                <input type="text" readonly value="{{ session('welcome_url') }}" id="welcome-url"
                       class="flex-1 min-w-0 px-3 py-2 bg-neutral-800 border border-neutral-600 rounded-lg text-white text-sm">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('welcome-url').value); this.textContent='Copié !'; setTimeout(() => this.textContent='Copier', 2000)"
                        class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium">Copier</button>
            </div>
            <p class="text-amber-200/80 text-xs mt-2">Envoyez ce lien à l'agent par WhatsApp s'il ne l'a pas reçu automatiquement.</p>
        </div>
    @endif
    @if($agent->user && $agent->user->welcome_token)
        <div class="mb-4 p-4 bg-slate-700/50 border border-slate-600 rounded-xl">
            <p class="text-slate-300 text-sm font-medium mb-2">Lien bienvenue (agent n'a pas encore défini son mot de passe) :</p>
            @php $welcomeUrl = route('commando.welcome', ['token' => $agent->user->welcome_token]); @endphp
            <div class="flex gap-2 flex-wrap">
                <input type="text" readonly value="{{ $welcomeUrl }}" id="welcome-url-persist"
                       class="flex-1 min-w-0 px-3 py-2 bg-neutral-800 border border-neutral-600 rounded-lg text-white text-sm">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('welcome-url-persist').value); this.textContent='Copié !'; setTimeout(() => this.textContent='Copier', 2000)"
                        class="px-4 py-2 rounded-lg bg-primary-500 text-white text-sm font-medium">Copier</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Profil</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-neutral-500">Nom complet</dt>
                    <dd class="text-white font-medium">{{ $agent->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-neutral-500">WhatsApp</dt>
                    <dd class="text-white">{{ $agent->whatsapp }}</dd>
                </div>
                <div>
                    <dt class="text-neutral-500">Ville</dt>
                    <dd class="text-white">{{ $agent->city ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-neutral-500">Badge ID</dt>
                    <dd class="text-white font-mono">{{ $agent->badge_id_display }}</dd>
                </div>
                <div>
                    <dt class="text-neutral-500">Statut métier</dt>
                    <dd class="text-white">{{ $agent->statut_metier ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-neutral-500">Statut vérification</dt>
                    <dd>
                        <span class="px-2 py-1 rounded-lg text-xs font-medium
                            @if($agent->status_verification->value === 'pending_review') bg-amber-500/20 text-amber-400
                            @elseif($agent->status_verification->value === 'valide') bg-green-500/20 text-green-400
                            @elseif(in_array($agent->status_verification->value, ['rejete','banni'])) bg-red-500/20 text-red-400
                            @else bg-neutral-500/20 text-neutral-400
                            @endif">
                            {{ $agent->status_verification->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-neutral-500">Inscrit le</dt>
                    <dd class="text-white">{{ $agent->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($agent->rejection_reason)
                    <div>
                        <dt class="text-neutral-500">Motif rejet</dt>
                        <dd class="text-amber-400">{{ $agent->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
            @if($agent->relationLoaded('verifyScans') && $agent->verifyScans->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-neutral-700">
                    <dt class="text-neutral-500 mb-1">Derniers scans QR</dt>
                    <dd class="text-slate-400 text-xs">
                        {{ $agent->verifyScans->count() }} scan(s) enregistré(s). Dernier : {{ $agent->verifyScans->first()->created_at->diffForHumans() }}
                    </dd>
                </div>
            @endif
        </div>

        {{-- Wallet --}}
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Wallet</h2>
            <p class="text-2xl font-bold text-white mb-4">{{ number_format($agent->balance, 0, ',', ' ') }} <span class="text-neutral-400 text-base font-normal">FCFA</span></p>
            <form method="POST" action="{{ route('super-admin.commando.agents.commission', $agent) }}" class="flex flex-wrap gap-2 mb-4">
                @csrf
                <input type="number" name="amount" step="1" min="1" placeholder="Montant (FCFA)" required class="px-3 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white text-sm w-32">
                <input type="text" name="description" placeholder="Description (optionnel)" class="flex-1 min-w-0 px-3 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white text-sm">
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white text-sm font-medium">Ajouter commission</button>
            </form>
            @if($pendingWithdrawals->isNotEmpty())
                <div class="mb-4">
                    <p class="text-amber-400 text-sm font-medium mb-2">Demandes de retrait en attente</p>
                    <ul class="space-y-2">
                        @foreach($pendingWithdrawals as $tx)
                            <li class="flex items-center justify-between py-2 px-3 rounded-lg bg-neutral-700/50 border border-neutral-600 text-sm">
                                <span class="text-white">{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }} FCFA – {{ $tx->created_at->format('d/m/Y H:i') }}</span>
                                <span class="flex gap-2">
                                    <form method="POST" action="{{ route('super-admin.commando.agents.withdrawal.pay', [$agent, $tx]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-400 hover:text-green-300 text-xs font-medium">Marquer payé</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.commando.agents.withdrawal.reject', [$agent, $tx]) }}" class="inline" onsubmit="return confirm('Rejeter cette demande ?');">
                                        @csrf
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-medium">Rejeter</button>
                                    </form>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="text-neutral-500 text-xs mb-2">Historique (50 derniers)</p>
            <ul class="space-y-1 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->commissionTransactions as $tx)
                    <li class="flex justify-between items-center py-1.5 px-2 rounded text-neutral-300">
                        <span>{{ $tx->type->label() }} – {{ $tx->created_at->format('d/m H:i') }} <span class="text-neutral-500">({{ $tx->status->label() }})</span></span>
                        <span class="@if($tx->amount_cents > 0) text-green-400 @else text-neutral-400 @endif">{{ $tx->amount_cents > 0 ? '+' : '' }}{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }} FCFA</span>
                    </li>
                @empty
                    <li class="text-neutral-500 py-2">Aucune transaction</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Pièce d'identité</h2>
            @if($agent->id_document_path && $agent->id_document_url)
                @if(str_ends_with(strtolower($agent->id_document_path), '.pdf'))
                    <a href="{{ $agent->id_document_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 text-orange-400 hover:text-orange-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Ouvrir le PDF
                    </a>
                @else
                    <a href="{{ $agent->id_document_url }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $agent->id_document_url }}" alt="Pièce d'identité" class="rounded-lg max-h-80 object-contain border border-neutral-600">
                    </a>
                @endif
            @else
                <p class="text-neutral-500 text-sm">Aucun document.</p>
            @endif
        </div>
    </div>

    <div class="mt-6 grid lg:grid-cols-2 gap-6">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Déploiements ({{ $agent->deployments->count() }})</h2>
            <ul class="space-y-2 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->deployments as $d)
                    <li class="flex justify-between items-center py-2 px-3 rounded-lg bg-neutral-700/30 border border-neutral-600">
                        <span class="text-white">{{ $d->restaurant_name }}</span>
                        <span class="px-2 py-0.5 rounded text-xs
                            @if($d->status->value === 'actif') bg-green-500/20 text-green-400
                            @elseif($d->status->value === 'en_attente_paiement') bg-sky-500/20 text-sky-400
                            @else bg-amber-500/20 text-amber-400
                            @endif">{{ $d->status->label() }}</span>
                    </li>
                @empty
                    <li class="text-neutral-500">Aucun déploiement</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Restaurants parrainés ({{ $agent->referredRestaurants->count() }})</h2>
            <ul class="space-y-2 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->referredRestaurants as $r)
                    <li class="py-2 px-3 rounded-lg bg-neutral-700/30 border border-neutral-600">
                        <a href="{{ route('super-admin.restaurants.show', $r) }}" class="text-orange-400 hover:text-orange-300">{{ $r->name }}</a>
                        <span class="text-neutral-500 text-xs block">{{ $r->created_at->format('d/m/Y') }}</span>
                    </li>
                @empty
                    <li class="text-neutral-500">Aucun restaurant parrainé</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        @if($agent->status_verification->value === 'pending_review')
            <form method="POST" action="{{ route('super-admin.commando.agents.approve', $agent) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-medium">
                    Approuver
                </button>
            </form>
            <div x-data>
            <button type="button" @click="$refs.rejectModal.showModal()" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-medium">
                Rejeter
            </button>
            <dialog x-ref="rejectModal" class="bg-neutral-800 border border-neutral-600 rounded-xl p-6 max-w-md w-full backdrop:bg-black/60">
                <form method="POST" action="{{ route('super-admin.commando.agents.reject', $agent) }}">
                    @csrf
                    <h3 class="text-lg font-semibold text-white mb-2">Rejeter l'agent</h3>
                    <label class="block text-sm text-neutral-400 mb-2">Motif (optionnel)</label>
                    <textarea name="reason" rows="3" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white mb-4" placeholder="Raison du rejet..."></textarea>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-lg bg-neutral-600 text-white">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white">Rejeter</button>
                    </div>
                </form>
            </dialog>
            </div>
        @endif
        @if($agent->status_verification->value === 'valide' && !$agent->banned_at)
            <form method="POST" action="{{ route('super-admin.commando.agents.ban', $agent) }}" class="inline"
                  onsubmit="return confirm('Révoquer la carte de cet agent ? Le QR affichera « Agent invalide ».')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-medium">
                    Révoquer (bannir)
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('super-admin.commando.agents.destroy', $agent) }}" class="inline"
              onsubmit="return confirm('Supprimer définitivement cet agent ({{ addslashes($agent->full_name) }}) ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded-lg bg-neutral-600 hover:bg-neutral-700 text-red-400 hover:text-red-300 font-medium border border-red-500/30">
                Supprimer l'agent
            </button>
        </form>
    </div>
</x-layouts.admin-super>
