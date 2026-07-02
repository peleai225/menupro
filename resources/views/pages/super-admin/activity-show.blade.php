<x-layouts.admin-super :title="'Activité: ' . $log->action">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('super-admin.activity') }}" style="color:var(--sa-muted-fg);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Détail de l'activité</h1>
            </div>
            <p style="color:var(--sa-muted-fg);">{{ $log->action }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Details -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Informations</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Action</label>
                        <p class="font-medium" style="color:var(--sa-fg);">{{ $log->action }}</p>
                    </div>
                    @if($log->description)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">Description</label>
                            <p style="color:var(--sa-fg);">{{ $log->description }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm" style="color:var(--sa-muted-fg);">Date</label>
                        <p class="font-medium" style="color:var(--sa-fg);">{{ $log->created_at->format('d/m/Y à H:i:s') }}</p>
                    </div>
                    @if($log->ip_address)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">Adresse IP</label>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $log->ip_address }}</p>
                        </div>
                    @endif
                    @if($log->user_agent)
                        <div>
                            <label class="text-sm" style="color:var(--sa-muted-fg);">User Agent</label>
                            <p class="text-sm" style="color:var(--sa-fg);">{{ $log->user_agent }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($log->metadata)
                <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Métadonnées</h2>
                    <pre class="text-sm bg-neutral-900/50 p-4 rounded-lg overflow-x-auto" style="color:var(--sa-muted-fg);">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            @if($log->user)
                <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Utilisateur</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Nom</p>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $log->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Email</p>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $log->user->email }}</p>
                        </div>
                        <a href="{{ route('super-admin.utilisateurs.show', $log->user) }}" class="block mt-4 btn btn-neutral btn-sm text-center">
                            Voir le profil
                        </a>
                    </div>
                </div>
            @endif

            <!-- Restaurant Info -->
            @if($log->restaurant)
                <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Restaurant</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Nom</p>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $log->restaurant->name }}</p>
                        </div>
                        <a href="{{ route('super-admin.restaurants.show', $log->restaurant) }}" class="block mt-4 btn btn-neutral btn-sm text-center">
                            Voir le restaurant
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('super-admin.activity') }}" class="block w-full btn btn-neutral btn-sm text-center">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
