<x-layouts.admin-super :title="'Activité: ' . $log->action">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('super-admin.activity') }}" class="text-neutral-500 hover:text-neutral-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-neutral-900">Détail de l'activité</h1>
            </div>
            <p class="text-neutral-500">{{ $log->action }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Details -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-neutral-500">Action</label>
                        <p class="text-neutral-900 font-medium">{{ $log->action }}</p>
                    </div>
                    @if($log->description)
                        <div>
                            <label class="text-sm text-neutral-500">Description</label>
                            <p class="text-neutral-900">{{ $log->description }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm text-neutral-500">Date</label>
                        <p class="text-neutral-900 font-medium">{{ $log->created_at->format('d/m/Y à H:i:s') }}</p>
                    </div>
                    @if($log->ip_address)
                        <div>
                            <label class="text-sm text-neutral-500">Adresse IP</label>
                            <p class="text-neutral-900 font-medium">{{ $log->ip_address }}</p>
                        </div>
                    @endif
                    @if($log->user_agent)
                        <div>
                            <label class="text-sm text-neutral-500">User Agent</label>
                            <p class="text-neutral-900 text-sm">{{ $log->user_agent }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($log->metadata)
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Métadonnées</h2>
                    <pre class="text-sm text-neutral-600 bg-neutral-900/50 p-4 rounded-lg overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            @if($log->user)
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Utilisateur</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-neutral-500">Nom</p>
                            <p class="text-neutral-900 font-medium">{{ $log->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">Email</p>
                            <p class="text-neutral-900 font-medium">{{ $log->user->email }}</p>
                        </div>
                        <a href="{{ route('super-admin.utilisateurs.show', $log->user) }}" class="block mt-4 btn btn-neutral btn-sm text-center">
                            Voir le profil
                        </a>
                    </div>
                </div>
            @endif

            <!-- Restaurant Info -->
            @if($log->restaurant)
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Restaurant</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-neutral-500">Nom</p>
                            <p class="text-neutral-900 font-medium">{{ $log->restaurant->name }}</p>
                        </div>
                        <a href="{{ route('super-admin.restaurants.show', $log->restaurant) }}" class="block mt-4 btn btn-neutral btn-sm text-center">
                            Voir le restaurant
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('super-admin.activity') }}" class="block w-full btn btn-neutral btn-sm text-center">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>

