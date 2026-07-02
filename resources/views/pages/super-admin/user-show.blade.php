<x-layouts.admin-super :title="'Utilisateur: ' . $user->name">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('super-admin.utilisateurs.index') }}" style="color:var(--sa-muted-fg);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $user->name }}</h1>
            </div>
            <p style="color:var(--sa-muted-fg);">{{ $user->email }}</p>
        </div>
        <div class="flex gap-2">
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('super-admin.users.toggle', $user) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn {{ $user->is_active ? 'btn-warning' : 'btn-secondary' }}">
                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Details -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Informations</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Nom</label>
                        <p class="font-medium" style="color:var(--sa-fg);">{{ $user->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Email</label>
                        <p class="font-medium" style="color:var(--sa-fg);">{{ $user->email }}</p>
                    </div>
                    @if($user->phone)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">Téléphone</label>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $user->phone }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Rôle</label>
                        <p class="font-medium" style="color:var(--sa-fg);">
                            @if($user->role->value === 'super_admin')
                                <span class="px-2 py-1 bg-accent-500/20 text-accent-400 rounded-lg text-sm">Super Admin</span>
                            @elseif($user->role->value === 'restaurant_admin')
                                <span class="px-2 py-1 bg-primary-500/20 text-primary-400 rounded-lg text-sm">Admin Restaurant</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-sm" style="background:var(--sa-muted);color:var(--sa-muted-fg);">Employé</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Statut</label>
                        <p class="font-medium" style="color:var(--sa-fg);">
                            @if($user->is_active)
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-sm">Actif</span>
                            @else
                                <span class="px-2 py-1 bg-red-50 text-red-700 rounded-lg text-sm">Inactif</span>
                            @endif
                        </p>
                    </div>
                    @if($user->restaurant)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">Restaurant</label>
                            <p class="font-medium" style="color:var(--sa-fg);">
                                <a href="{{ route('super-admin.restaurants.show', $user->restaurant) }}" class="text-primary-400 hover:text-primary-300">
                                    {{ $user->restaurant->name }}
                                </a>
                            </p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Inscrit le</label>
                        <p class="font-medium" style="color:var(--sa-fg);">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @if($user->email_verified_at)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">Email vérifié</label>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $user->email_verified_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Activité récente</h2>
                @if($user->activityLogs && $user->activityLogs->count() > 0)
                    <div class="space-y-3">
                        @foreach($user->activityLogs as $log)
                            <div class="flex items-start gap-3 p-3 rounded-lg" style="background:var(--sa-muted);">
                                <div class="flex-1">
                                    <p class="text-sm font-medium" style="color:var(--sa-fg);">{{ $log->action }}</p>
                                    @if($log->description)
                                        <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">{{ $log->description }}</p>
                                    @endif
                                    @if($log->restaurant)
                                        <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">Restaurant: {{ $log->restaurant->name }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Aucune activité enregistrée.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Actions</h2>
                <div class="space-y-2">
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('super-admin.users.reset-password', $user) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')">
                            @csrf
                            <button type="submit" class="w-full btn btn-secondary btn-sm">
                                Réinitialiser mot de passe
                            </button>
                        </form>
                        <form method="POST"
                              action="{{ route('super-admin.utilisateurs.destroy', $user) }}"
                              onsubmit="return confirm('Confirmez-vous la suppression définitive de cet utilisateur ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full btn btn-danger btn-sm">
                                Supprimer l'utilisateur
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('super-admin.utilisateurs.index') }}" class="block w-full btn btn-neutral btn-sm text-center">
                        Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Statistiques</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Activités enregistrées</p>
                        <p class="text-xl font-bold" style="color:var(--sa-fg);">{{ $user->activityLogs ? $user->activityLogs->count() : 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
