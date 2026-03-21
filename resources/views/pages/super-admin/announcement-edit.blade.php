<x-layouts.admin-super title="Modifier l'annonce">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('super-admin.announcements.index') }}" class="p-2 hover:bg-neutral-200 rounded-lg text-neutral-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Modifier l'annonce</h1>
            <p class="text-neutral-500 mt-1">{{ $announcement->title }}</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('super-admin.announcements.update', $announcement) }}" method="POST" class="max-w-3xl">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Type -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                <label class="block text-sm font-medium text-neutral-700 mb-4">Type d'annonce</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach([
                        'info' => ['label' => 'Information', 'color' => 'blue', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'success' => ['label' => 'Succès', 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'warning' => ['label' => 'Attention', 'color' => 'yellow', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                        'danger' => ['label' => 'Urgent', 'color' => 'red', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ] as $value => $type)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" {{ old('type', $announcement->type) === $value ? 'checked' : '' }}>
                            <div class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-neutral-300 peer-checked:border-{{ $type['color'] }}-500 peer-checked:bg-{{ $type['color'] }}-500/10 hover:border-neutral-500 transition-colors">
                                <svg class="w-6 h-6 text-{{ $type['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $type['icon'] }}"/>
                                </svg>
                                <span class="text-sm font-medium text-neutral-900">{{ $type['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Titre <span class="text-red-600">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                           class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Contenu <span class="text-red-600">*</span></label>
                    <textarea name="content" rows="5" required
                              class="w-full px-4 py-3 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('content', $announcement->content) }}</textarea>
                </div>
            </div>

            <!-- Target -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                <label class="block text-sm font-medium text-neutral-700 mb-4">Destinataires</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach([
                        'all' => ['label' => 'Tous', 'desc' => 'Tous les restaurants'],
                        'active' => ['label' => 'Actifs', 'desc' => 'Abonnement actif'],
                        'trial' => ['label' => 'Essai', 'desc' => 'Période d\'essai'],
                        'expired' => ['label' => 'Expirés', 'desc' => 'Abonnement expiré'],
                    ] as $value => $target)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="target" value="{{ $value }}" class="peer sr-only" {{ old('target', $announcement->target) === $value ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-neutral-300 peer-checked:border-primary-500 peer-checked:bg-primary-500/10 hover:border-neutral-500 transition-colors">
                                <span class="block text-sm font-medium text-neutral-900">{{ $target['label'] }}</span>
                                <span class="block text-xs text-neutral-500 mt-1">{{ $target['desc'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Scheduling -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                <h3 class="text-sm font-medium text-neutral-700 mb-4">Planification</h3>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm text-neutral-500 mb-2">Date de début</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}"
                               class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm text-neutral-500 mb-2">Date de fin</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}"
                               class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-medium text-neutral-700 mb-4">Options</h3>
                
                <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                    <div>
                        <span class="font-medium text-neutral-900">Activer l'annonce</span>
                        <p class="text-sm text-neutral-500">L'annonce sera visible</p>
                    </div>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }} 
                           class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                </label>

                <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                    <div>
                        <span class="font-medium text-neutral-900">Afficher sur le dashboard</span>
                        <p class="text-sm text-neutral-500">L'annonce apparaîtra sur le dashboard</p>
                    </div>
                    <input type="checkbox" name="show_on_dashboard" value="1" {{ old('show_on_dashboard', $announcement->show_on_dashboard) ? 'checked' : '' }} 
                           class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                </label>

                <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                    <div>
                        <span class="font-medium text-neutral-900">Peut être fermée</span>
                        <p class="text-sm text-neutral-500">Les utilisateurs peuvent fermer l'annonce</p>
                    </div>
                    <input type="checkbox" name="is_dismissible" value="1" {{ old('is_dismissible', $announcement->is_dismissible) ? 'checked' : '' }} 
                           class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('super-admin.announcements.index') }}" class="btn btn-ghost text-neutral-500">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin-super>
