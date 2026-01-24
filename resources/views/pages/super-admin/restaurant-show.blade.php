<x-layouts.admin-super title="{{ $restaurant->name }}">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('super-admin.restaurants.index') }}" class="p-2 hover:bg-neutral-800 rounded-lg">
            <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                @if($restaurant->logo_path)
                    <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-10 h-10 rounded-xl object-cover">
                @endif
                <h1 class="text-2xl font-bold text-white">{{ $restaurant->name }}</h1>
                @php
                    $statusColors = [
                        'active' => 'bg-secondary-500/20 text-secondary-400',
                        'pending' => 'bg-yellow-500/20 text-yellow-400',
                        'suspended' => 'bg-red-500/20 text-red-400',
                        'expired' => 'bg-neutral-500/20 text-neutral-400',
                    ];
                    $statusLabels = [
                        'active' => 'Actif',
                        'pending' => 'En attente',
                        'suspended' => 'Suspendu',
                        'expired' => 'Expiré',
                    ];
                @endphp
                <span class="badge {{ $statusColors[$restaurant->status->value] ?? 'bg-neutral-500/20 text-neutral-400' }}">
                    {{ $statusLabels[$restaurant->status->value] ?? $restaurant->status->value }}
                </span>
            </div>
            <p class="text-neutral-400 mt-1">Inscrit le {{ $restaurant->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($restaurant->status->value === 'pending')
                <form method="POST" action="{{ route('super-admin.restaurants.approve', $restaurant) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Valider
                    </button>
                </form>
            @elseif($restaurant->status->value === 'active')
                <button onclick="document.getElementById('suspendModal').classList.remove('hidden')" class="btn btn-ghost text-yellow-400 hover:bg-yellow-500/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Suspendre
                </button>
            @elseif($restaurant->status->value === 'suspended')
                <form method="POST" action="{{ route('super-admin.restaurants.reactivate', $restaurant) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réactiver
                    </button>
                </form>
            @endif
            <a href="{{ route('r.menu', $restaurant->slug) }}" target="_blank" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Voir le site
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-500/20 border border-secondary-500/30 rounded-xl text-secondary-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
                    <p class="text-sm text-neutral-400">Commandes</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['orders_count'] ?? 0) }}</p>
                </div>
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
                    <p class="text-sm text-neutral-400">CA Total</p>
                    <p class="text-2xl font-bold text-white">
                        @if(($stats['total_revenue'] ?? 0) >= 1000000)
                            {{ number_format(($stats['total_revenue'] ?? 0) / 1000000, 1) }}M F
                        @else
                            {{ number_format(($stats['total_revenue'] ?? 0) / 1000, 0) }}K F
                        @endif
                    </p>
                </div>
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
                    <p class="text-sm text-neutral-400">Plats</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['dishes_count'] ?? 0) }}</p>
                </div>
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
                    <p class="text-sm text-neutral-400">Catégories</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['categories_count'] ?? 0) }}</p>
                </div>
            </div>

            <!-- Restaurant Info -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Informations</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-neutral-400">Email</p>
                        <p class="text-white">{{ $restaurant->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-400">Téléphone</p>
                        <p class="text-white">{{ $restaurant->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-400">Adresse</p>
                        <p class="text-white">{{ $restaurant->address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-400">Ville</p>
                        <p class="text-white">{{ $restaurant->city ?? '-' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-neutral-400">Description</p>
                        <p class="text-white">{{ $restaurant->description ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            @if(isset($stats['recent_orders']) && $stats['recent_orders']->isNotEmpty())
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl">
                    <div class="p-6 border-b border-neutral-700">
                        <h2 class="text-lg font-semibold text-white">Commandes récentes</h2>
                    </div>
                    <div class="divide-y divide-neutral-700">
                        @foreach($stats['recent_orders'] as $order)
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <span class="text-white font-medium">{{ $order->order_number }}</span>
                                    <span class="text-neutral-400 text-sm ml-2">{{ $order->customer_name }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold text-white">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                                    <span class="text-neutral-500 text-sm block">{{ $order->created_at->locale('fr')->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- RCCM Verification -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Vérification RCCM</h2>
                    @if($restaurant->is_verified)
                        <span class="badge bg-blue-500/20 text-blue-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Vérifié
                        </span>
                    @else
                        <span class="badge bg-neutral-500/20 text-neutral-400">Non vérifié</span>
                    @endif
                </div>

                <div class="space-y-4 text-sm">
                    <!-- Company Name -->
                    <div>
                        <p class="text-neutral-400">Nom entreprise</p>
                        <p class="text-white">{{ $restaurant->company_name ?? '-' }}</p>
                    </div>

                    <!-- RCCM Number -->
                    <div>
                        <p class="text-neutral-400">Numéro RCCM</p>
                        <p class="text-white font-mono">{{ $restaurant->rccm ?? '-' }}</p>
                    </div>

                    <!-- RCCM Document -->
                    <div>
                        <p class="text-neutral-400 mb-2">Document RCCM</p>
                        @if($restaurant->rccm_document_path)
                            @php
                                $documentUrl = Storage::url($restaurant->rccm_document_path);
                                $extension = pathinfo($restaurant->rccm_document_path, PATHINFO_EXTENSION);
                                $isPdf = strtolower($extension) === 'pdf';
                            @endphp
                            <div class="flex flex-col gap-2">
                                @if($isPdf)
                                    <a href="{{ $documentUrl }}" target="_blank" class="flex items-center gap-2 p-3 bg-neutral-700/50 border border-neutral-600 rounded-lg hover:bg-neutral-700 transition-colors">
                                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <span class="text-white font-medium">Document PDF</span>
                                            <span class="block text-xs text-neutral-400">Cliquez pour ouvrir</span>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ $documentUrl }}" target="_blank" class="block">
                                        <img src="{{ $documentUrl }}" alt="Document RCCM" class="w-full rounded-lg border border-neutral-600 hover:opacity-80 transition-opacity">
                                    </a>
                                @endif
                                <a href="{{ $documentUrl }}" download class="btn btn-outline btn-sm border-neutral-600 text-neutral-300 hover:bg-neutral-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        @else
                            <p class="text-neutral-500 italic">Aucun document fourni</p>
                        @endif
                    </div>
                </div>

                @if($restaurant->is_verified)
                    <div class="mt-4 pt-4 border-t border-neutral-700">
                        <p class="text-xs text-neutral-400 mb-2">
                            Vérifié le {{ $restaurant->verified_at->format('d/m/Y à H:i') }}
                            @if($restaurant->verifiedBy)
                                par {{ $restaurant->verifiedBy->name }}
                            @endif
                        </p>
                        <form method="POST" action="{{ route('super-admin.restaurants.unverify', $restaurant) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir retirer la vérification ?')">
                            @csrf
                            <button type="submit" class="btn btn-ghost w-full text-neutral-400 hover:text-white hover:bg-neutral-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Retirer la vérification
                            </button>
                        </form>
                    </div>
                @elseif($restaurant->rccm && $restaurant->rccm_document_path)
                    <div class="mt-4 pt-4 border-t border-neutral-700">
                        <p class="text-xs text-neutral-400 mb-3">Après vérification du document, vous pouvez marquer ce restaurant comme vérifié.</p>
                        <form method="POST" action="{{ route('super-admin.restaurants.verify', $restaurant) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-full">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Marquer comme vérifié
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Owner Info -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Propriétaire</h2>
                @if($restaurant->owner)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($restaurant->owner->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $restaurant->owner->name }}</p>
                            <p class="text-sm text-neutral-400">Administrateur</p>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3 text-neutral-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $restaurant->owner->email }}
                        </div>
                        @if($restaurant->owner->phone)
                            <div class="flex items-center gap-3 text-neutral-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $restaurant->owner->phone }}
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-neutral-500">Aucun propriétaire assigné</p>
                @endif
            </div>

            <!-- Subscription -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Abonnement</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-400">Plan</span>
                        <span class="font-medium text-white">{{ $restaurant->currentPlan?->name ?? 'Aucun' }}</span>
                    </div>
                    @if($restaurant->currentPlan)
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400">Montant</span>
                            <span class="font-medium text-white">{{ number_format($restaurant->currentPlan->price, 0, ',', ' ') }} F/mois</span>
                        </div>
                    @endif
                    @if($restaurant->subscription_ends_at)
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-400">Expire le</span>
                            <span class="font-medium text-white">{{ $restaurant->subscription_ends_at->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Change Plan -->
                @if($plans->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-neutral-700">
                        <form method="POST" action="{{ route('super-admin.restaurants.update', $restaurant) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <select name="current_plan_id" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Aucun plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ $restaurant->current_plan_id == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} ({{ number_format($plan->price, 0, ',', ' ') }} F)
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline w-full border-neutral-600 text-neutral-300 hover:bg-neutral-700">
                                Changer le plan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Danger Zone -->
            <div class="bg-neutral-800/50 border border-red-500/30 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-red-400 mb-4">Zone dangereuse</h2>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('super-admin.restaurants.impersonate', $restaurant) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline w-full border-neutral-600 text-neutral-300 hover:bg-neutral-700">
                            Connexion en tant que
                        </button>
                    </form>
                    <form method="POST" action="{{ route('super-admin.restaurants.destroy', $restaurant) }}" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce restaurant ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost w-full text-red-400 hover:bg-red-500/10">
                            Supprimer le restaurant
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div id="suspendModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('suspendModal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-md bg-neutral-800 border border-neutral-700 rounded-2xl shadow-xl">
                <div class="p-6 border-b border-neutral-700">
                    <h2 class="text-xl font-bold text-white">Suspendre le restaurant</h2>
                </div>
                <form method="POST" action="{{ route('super-admin.restaurants.suspend', $restaurant) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Raison de la suspension *</label>
                        <textarea name="reason" required rows="4" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Expliquez pourquoi ce restaurant est suspendu..."></textarea>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('suspendModal').classList.add('hidden')" class="flex-1 h-10 px-4 bg-neutral-700 text-white rounded-lg font-medium hover:bg-neutral-600 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 h-10 px-4 bg-yellow-500 text-white rounded-lg font-medium hover:bg-yellow-600 transition-colors">
                            Suspendre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
