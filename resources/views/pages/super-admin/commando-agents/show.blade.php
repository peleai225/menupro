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
                       class="flex-1 min-w-0 px-3 py-2 border rounded-lg text-sm" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('welcome-url').value); this.textContent='Copié !'; setTimeout(() => this.textContent='Copier', 2000)"
                        class="px-4 py-2 rounded-lg bg-amber-500 text-neutral-900 text-sm font-medium">Copier</button>
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
                       class="flex-1 min-w-0 px-3 py-2 border rounded-lg text-sm" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('welcome-url-persist').value); this.textContent='Copié !'; setTimeout(() => this.textContent='Copier', 2000)"
                        class="px-4 py-2 rounded-lg bg-primary-500 text-neutral-900 text-sm font-medium">Copier</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-red-600 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Profil</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt style="color:var(--sa-muted-fg);">Nom complet</dt>
                    <dd class="font-medium" style="color:var(--sa-fg);">{{ $agent->full_name }}</dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">WhatsApp</dt>
                    <dd style="color:var(--sa-fg);">{{ $agent->whatsapp }}</dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">Ville</dt>
                    <dd style="color:var(--sa-fg);">{{ $agent->city ?? '–' }}</dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">Badge ID</dt>
                    <dd class="font-mono" style="color:var(--sa-fg);">{{ $agent->badge_id_display }}</dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">Statut métier</dt>
                    <dd style="color:var(--sa-fg);">{{ $agent->statut_metier ?? '–' }}</dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">Statut vérification</dt>
                    <dd>
                        @php
                            $sv = $agent->status_verification;
                            $svStyle = match(true) {
                                $sv->value === 'pending_review' => 'background:rgba(217,119,6,0.15);color:var(--sa-warning);',
                                $sv->value === 'valide' => 'background:rgba(61,158,98,0.15);color:var(--sa-success);',
                                in_array($sv->value, ['rejete','banni']) => 'background:rgba(220,38,38,0.15);color:var(--sa-danger);',
                                default => 'background:rgba(107,101,96,0.15);color:var(--sa-muted-fg);',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-lg text-xs font-medium" style="{{ $svStyle }}">
                            {{ $sv->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt style="color:var(--sa-muted-fg);">Inscrit le</dt>
                    <dd style="color:var(--sa-fg);">{{ $agent->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($agent->rejection_reason)
                    <div>
                        <dt style="color:var(--sa-muted-fg);">Motif rejet</dt>
                        <dd class="text-amber-400">{{ $agent->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
            @if($agent->relationLoaded('verifyScans') && $agent->verifyScans->isNotEmpty())
                <div class="mt-4 pt-4" style="border-top:1px solid var(--sa-border);">
                    <dt style="color:var(--sa-muted-fg);" class="mb-1">Derniers scans QR</dt>
                    <dd class="text-slate-400 text-xs">
                        {{ $agent->verifyScans->count() }} scan(s) enregistré(s). Dernier : {{ $agent->verifyScans->first()->created_at->diffForHumans() }}
                    </dd>
                </div>
            @endif

            {{-- Statistiques agent --}}
            <div class="mt-4 pt-4 grid grid-cols-2 gap-3" style="border-top:1px solid var(--sa-border);">
                <div class="rounded-lg p-3 text-center" style="background:var(--sa-muted);">
                    <p class="text-xs mb-1" style="color:var(--sa-muted-fg);">Commissions reçues</p>
                    <p class="text-base font-bold text-green-400">{{ number_format($agentStats['total_commissions_fcfa'], 0, ',', ' ') }} <span class="text-xs font-normal" style="color:var(--sa-muted-fg);">FCFA</span></p>
                </div>
                <div class="rounded-lg p-3 text-center" style="background:var(--sa-muted);">
                    <p class="text-xs mb-1" style="color:var(--sa-muted-fg);">Restaurants parrainés</p>
                    <p class="text-base font-bold" style="color:var(--sa-fg);">
                        <span class="text-orange-400">{{ $agentStats['restaurants_active'] }}</span>
                        <span class="text-xs font-normal" style="color:var(--sa-muted-fg);">actifs</span>
                        / {{ $agentStats['restaurants_total'] }}
                    </p>
                </div>
                @if($agentStats['last_scan_at'])
                <div class="rounded-lg p-3 col-span-2" style="background:var(--sa-muted);">
                    <p class="text-xs mb-0.5" style="color:var(--sa-muted-fg);">Dernière activité</p>
                    <p class="text-xs" style="color:var(--sa-fg);">
                        Scan QR : {{ $agentStats['last_scan_at']->diffForHumans() }}
                        @if($agentStats['last_commission_at'])
                        &nbsp;·&nbsp; Commission : {{ $agentStats['last_commission_at']->diffForHumans() }}
                        @endif
                    </p>
                </div>
                @elseif($agentStats['last_commission_at'])
                <div class="rounded-lg p-3 col-span-2" style="background:var(--sa-muted);">
                    <p class="text-xs mb-0.5" style="color:var(--sa-muted-fg);">Dernière activité</p>
                    <p class="text-xs" style="color:var(--sa-fg);">Dernière commission : {{ $agentStats['last_commission_at']->diffForHumans() }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Wallet --}}
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Wallet</h2>
            <p class="text-2xl font-bold mb-4" style="color:var(--sa-fg);">{{ number_format($agent->balance, 0, ',', ' ') }} <span class="text-base font-normal" style="color:var(--sa-muted-fg);">FCFA</span></p>
            <form method="POST" action="{{ route('super-admin.commando.agents.commission', $agent) }}" class="flex flex-wrap gap-2 mb-4">
                @csrf
                <input type="number" name="amount" step="1" min="1" placeholder="Montant (FCFA)" required
                       class="px-3 py-2 border rounded-lg text-sm w-32"
                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                <input type="text" name="description" placeholder="Description (optionnel)"
                       class="flex-1 min-w-0 px-3 py-2 border rounded-lg text-sm"
                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-neutral-900 text-sm font-medium">Ajouter commission</button>
            </form>
            @if($pendingWithdrawals->isNotEmpty())
                <div class="mb-4">
                    <p class="text-amber-400 text-sm font-medium mb-2">Demandes de retrait en attente</p>
                    <ul class="space-y-2">
                        @foreach($pendingWithdrawals as $tx)
                            <li class="py-2 px-3 rounded-lg border text-sm" style="background:var(--sa-muted);border-color:var(--sa-border);">
                                <div class="flex items-center justify-between">
                                    <span style="color:var(--sa-fg);">{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }} FCFA – {{ $tx->created_at->format('d/m/Y H:i') }}</span>
                                    <span class="flex gap-2">
                                        <form method="POST" action="{{ route('super-admin.commando.agents.withdrawal.pay', [$agent, $tx]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300 text-xs font-medium">Marquer payé</button>
                                        </form>
                                        <form method="POST" action="{{ route('super-admin.commando.agents.withdrawal.reject', [$agent, $tx]) }}" class="inline" onsubmit="return confirm('Rejeter cette demande ?');">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-300 text-xs font-medium">Rejeter</button>
                                        </form>
                                    </span>
                                </div>
                                @if(!empty($tx->meta['payment_method']) || !empty($tx->meta['phone']))
                                    <div class="mt-1 flex gap-3 text-xs" style="color:var(--sa-muted-fg);">
                                        @if(!empty($tx->meta['payment_method']))
                                            <span class="capitalize">
                                                {{ match($tx->meta['payment_method']) {
                                                    'wave'         => 'Wave',
                                                    'orange_money' => 'Orange Money',
                                                    'mtn_money'    => 'MTN Money',
                                                    default        => $tx->meta['payment_method'],
                                                } }}
                                            </span>
                                        @endif
                                        @if(!empty($tx->meta['phone']))
                                            <span class="font-mono">{{ $tx->meta['phone'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="text-xs mb-2" style="color:var(--sa-muted-fg);">Historique (50 derniers)</p>
            <ul class="space-y-1 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->commissionTransactions as $tx)
                    <li class="flex justify-between items-center py-1.5 px-2 rounded" style="color:var(--sa-muted-fg);">
                        <span>{{ $tx->type->label() }} – {{ $tx->created_at->format('d/m H:i') }} <span style="color:var(--sa-muted-fg);">({{ $tx->status->label() }})</span></span>
                        <span class="@if($tx->amount_cents > 0) text-green-400 @endif">{{ $tx->amount_cents > 0 ? '+' : '' }}{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }} FCFA</span>
                    </li>
                @empty
                    <li class="py-2" style="color:var(--sa-muted-fg);">Aucune transaction</li>
                @endforelse
            </ul>
        </div>

        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Pièce d'identité</h2>
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
                        <img src="{{ $agent->id_document_url }}" alt="Pièce d'identité" class="rounded-lg max-h-80 object-contain" style="border:1px solid var(--sa-border);">
                    </a>
                @endif
            @else
                <p class="text-sm" style="color:var(--sa-muted-fg);">Aucun document.</p>
            @endif
        </div>
    </div>

    <div class="mt-6 grid lg:grid-cols-2 gap-6">
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Déploiements ({{ $agent->deployments->count() }})</h2>
            <ul class="space-y-2 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->deployments as $d)
                    <li class="flex justify-between items-center py-2 px-3 rounded-lg border" style="background:var(--sa-muted);border-color:var(--sa-border);">
                        <span style="color:var(--sa-fg);">{{ $d->restaurant_name }}</span>
                        <span class="px-2 py-0.5 rounded text-xs
                            @if($d->status->value === 'actif') bg-emerald-50 text-emerald-700
                            @elseif($d->status->value === 'en_attente_paiement') bg-sky-500/20 text-sky-400
                            @else bg-amber-50 text-amber-700
                            @endif">{{ $d->status->label() }}</span>
                    </li>
                @empty
                    <li style="color:var(--sa-muted-fg);">Aucun déploiement</li>
                @endforelse
            </ul>
        </div>
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Restaurants parrainés ({{ $agent->referredRestaurants->count() }})</h2>
            <ul class="space-y-2 max-h-48 overflow-y-auto text-sm">
                @forelse($agent->referredRestaurants as $r)
                    <li class="py-2 px-3 rounded-lg border" style="background:var(--sa-muted);border-color:var(--sa-border);">
                        <a href="{{ route('super-admin.restaurants.show', $r) }}" class="text-orange-400 hover:text-orange-300">{{ $r->name }}</a>
                        <span class="text-xs block" style="color:var(--sa-muted-fg);">{{ $r->created_at->format('d/m/Y') }}</span>
                    </li>
                @empty
                    <li style="color:var(--sa-muted-fg);">Aucun restaurant parrainé</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        @if($agent->status_verification->value === 'pending_review')
            <form method="POST" action="{{ route('super-admin.commando.agents.approve', $agent) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-neutral-900 font-medium">
                    Approuver
                </button>
            </form>
            <div x-data>
            <button type="button" @click="$refs.rejectModal.showModal()" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-neutral-900 font-medium">
                Rejeter
            </button>
            <dialog x-ref="rejectModal" class="rounded-xl p-6 max-w-md w-full backdrop:bg-black/60" style="background:var(--sa-card);border:1px solid var(--sa-border);">
                <form method="POST" action="{{ route('super-admin.commando.agents.reject', $agent) }}">
                    @csrf
                    <h3 class="text-lg font-semibold mb-2" style="color:var(--sa-fg);">Rejeter l'agent</h3>
                    <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Motif (optionnel)</label>
                    <textarea name="reason" rows="3" class="w-full px-4 py-2 border rounded-lg mb-4" style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Raison du rejet..."></textarea>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-lg" style="background:var(--sa-muted);color:var(--sa-fg);">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white">Rejeter</button>
                    </div>
                </form>
            </dialog>
            </div>
        @endif
        @if($agent->status_verification->value === 'valide' && !$agent->banned_at)
            <div x-data>
                <button type="button" @click="$refs.banModal.showModal()"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-neutral-900 font-medium">
                    Révoquer (bannir)
                </button>
                <dialog x-ref="banModal" class="rounded-xl p-6 max-w-md w-full backdrop:bg-black/60" style="background:var(--sa-card);border:1px solid var(--sa-border);">
                    <form method="POST" action="{{ route('super-admin.commando.agents.ban', $agent) }}">
                        @csrf
                        <h3 class="text-lg font-semibold mb-2" style="color:var(--sa-fg);">Bannir l'agent</h3>
                        <p class="text-sm mb-3" style="color:var(--sa-muted-fg);">Le QR de cet agent affichera « Agent invalide ». L'agent sera notifié.</p>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Motif du bannissement (optionnel)</label>
                        <textarea name="ban_reason" rows="3" class="w-full px-4 py-2 border rounded-lg mb-4" style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Raison du bannissement..."></textarea>
                        <div class="flex gap-2 justify-end">
                            <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-lg" style="background:var(--sa-muted);color:var(--sa-fg);">Annuler</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white">Bannir</button>
                        </div>
                    </form>
                </dialog>
            </div>
        @endif
        @if($agent->status_verification->value === 'banni')
            <form method="POST" action="{{ route('super-admin.commando.agents.unban', $agent) }}" class="inline"
                  onsubmit="return confirm('Débannir cet agent et réactiver son compte ?')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-neutral-900 font-medium">
                    Débannir
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('super-admin.commando.agents.destroy', $agent) }}" class="inline"
              onsubmit="return confirm('Supprimer définitivement cet agent ({{ addslashes($agent->full_name) }}) ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded-lg text-red-600 hover:text-red-300 font-medium" style="background:var(--sa-muted);border:1px solid rgba(220,38,38,0.30);">
                Supprimer l'agent
            </button>
        </form>
    </div>
</x-layouts.admin-super>
