<x-layouts.admin-super title="Utilisateurs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Utilisateurs</h1>
            <p class="mt-1" style="color:var(--sa-muted-fg);">Gérez tous les utilisateurs de la plateforme.</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvel admin
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="border rounded-xl p-4 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Total</p>
            <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="border rounded-xl p-4 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Super Admins</p>
            <p class="text-2xl font-bold text-accent-600">{{ number_format($stats['super_admins']) }}</p>
        </div>
        <div class="border rounded-xl p-4 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Admins Restaurant</p>
            <p class="text-2xl font-bold text-primary-600">{{ number_format($stats['restaurant_admins']) }}</p>
        </div>
        <div class="border rounded-xl p-4 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Employés</p>
            <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['employees']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="border rounded-xl p-4 mb-6 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Rechercher un utilisateur..."
                           class="w-full h-10 pl-10 pr-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                           style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
            </div>
            <select name="role" class="h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                <option value="">Tous les rôles</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="restaurant_admin" {{ request('role') === 'restaurant_admin' ? 'selected' : '' }}>Admin Restaurant</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employé</option>
            </select>
            <select name="status" class="h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                <option value="">Tous statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Users Table -->
    <div class="border rounded-xl overflow-hidden shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead style="background:var(--sa-muted);border-bottom:1px solid var(--sa-border);">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Utilisateur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Inscription</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color:var(--sa-fg);">{{ $user->name }}</p>
                                        <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $roleColors = [
                                        'super_admin' => 'bg-accent-500/20 text-accent-400',
                                        'restaurant_admin' => 'bg-primary-500/20 text-primary-400',
                                        'employee' => 'bg-neutral-600 text-neutral-300',
                                        'commando_agent' => 'bg-orange-500/20 text-orange-400',
                                        'commercial' => 'bg-orange-500/20 text-orange-400',
                                        'technician' => 'bg-cyan-500/20 text-cyan-400',
                                        'team_leader' => 'bg-emerald-500/20 text-emerald-400',
                                        'customer' => 'bg-green-500/20 text-green-400',
                                        'delivery_driver' => 'bg-yellow-500/20 text-yellow-400',
                                    ];
                                    $roleLabels = [
                                        'super_admin' => 'Super Admin',
                                        'restaurant_admin' => 'Admin Restaurant',
                                        'employee' => 'Employé',
                                        'commando_agent' => 'Agent Commando',
                                        'commercial' => 'Commercial',
                                        'technician' => 'Technicien',
                                        'team_leader' => 'Team Leader',
                                        'customer' => 'Client',
                                        'delivery_driver' => 'Livreur',
                                    ];
                                    $roleValue = $user->role?->value ?? 'unknown';
                                @endphp
                                <span class="badge {{ $roleColors[$roleValue] ?? 'bg-neutral-600 text-neutral-300' }}">
                                    {{ $roleLabels[$roleValue] ?? $roleValue }}
                                </span>
                            </td>
                            <td class="px-6 py-4" style="color:var(--sa-muted-fg);">
                                {{ $user->restaurant?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="badge bg-secondary-500/20 text-secondary-400">Actif</span>
                                @else
                                    <span class="badge bg-red-500/20 text-red-400">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4" style="color:var(--sa-muted-fg);">
                                {{ $user->created_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('super-admin.utilisateurs.show', $user) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                        Voir →
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('super-admin.utilisateurs.destroy', $user) }}"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est définitive.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs font-medium text-red-600 hover:text-red-700">
                                                Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p style="color:var(--sa-muted-fg);">Aucun utilisateur trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif

    <!-- Add User Modal -->
    <div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('addUserModal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-md border rounded-2xl shadow-xl" style="background:var(--sa-card);border-color:var(--sa-border);">
                <div class="p-6 border-b" style="border-color:var(--sa-border);">
                    <h2 class="text-xl font-bold" style="color:var(--sa-fg);">Nouvel administrateur</h2>
                </div>
                <form method="POST" action="{{ route('super-admin.utilisateurs.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Nom complet *</label>
                        <input type="text" name="name" required class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Email *</label>
                        <input type="email" name="email" required class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Téléphone</label>
                        <input type="tel" name="phone" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Mot de passe *</label>
                        <input type="password" name="password" required minlength="8" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <input type="hidden" name="role" value="super_admin">
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="flex-1 h-10 px-4 rounded-lg font-medium transition-colors" style="background:var(--sa-muted);color:var(--sa-fg);">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 h-10 px-4 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
