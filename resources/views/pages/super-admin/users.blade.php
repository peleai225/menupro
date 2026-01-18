<x-layouts.admin-super title="Utilisateurs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Utilisateurs</h1>
            <p class="text-neutral-400 mt-1">Gérez tous les utilisateurs de la plateforme.</p>
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
        <div class="mb-6 p-4 bg-secondary-500/20 border border-secondary-500/30 rounded-xl text-secondary-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Total</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Super Admins</p>
            <p class="text-2xl font-bold text-accent-400">{{ number_format($stats['super_admins']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Admins Restaurant</p>
            <p class="text-2xl font-bold text-primary-400">{{ number_format($stats['restaurant_admins']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Employés</p>
            <p class="text-2xl font-bold text-neutral-300">{{ number_format($stats['employees']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Rechercher un utilisateur..." 
                           class="w-full h-10 pl-10 pr-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <select name="role" class="h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="restaurant_admin" {{ request('role') === 'restaurant_admin' ? 'selected' : '' }}>Admin Restaurant</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employé</option>
            </select>
            <select name="status" class="h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
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
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-700/50 border-b border-neutral-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-400 uppercase">Utilisateur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-400 uppercase">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-400 uppercase">Restaurant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-400 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-400 uppercase">Inscription</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-neutral-700/30">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $user->name }}</p>
                                        <p class="text-sm text-neutral-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $roleColors = [
                                        'super_admin' => 'bg-accent-500/20 text-accent-400',
                                        'restaurant_admin' => 'bg-primary-500/20 text-primary-400',
                                        'employee' => 'bg-neutral-600 text-neutral-300',
                                    ];
                                    $roleLabels = [
                                        'super_admin' => 'Super Admin',
                                        'restaurant_admin' => 'Admin Restaurant',
                                        'employee' => 'Employé',
                                    ];
                                @endphp
                                <span class="badge {{ $roleColors[$user->role->value] ?? 'bg-neutral-600 text-neutral-300' }}">
                                    {{ $roleLabels[$user->role->value] ?? $user->role->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-neutral-400">
                                {{ $user->restaurant?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="badge bg-secondary-500/20 text-secondary-400">Actif</span>
                                @else
                                    <span class="badge bg-red-500/20 text-red-400">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-400">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('super-admin.utilisateurs.show', $user) }}" class="text-primary-400 hover:text-primary-300 font-medium">
                                    Voir →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-neutral-500">Aucun utilisateur trouvé</p>
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
            <div class="relative w-full max-w-md bg-neutral-800 border border-neutral-700 rounded-2xl shadow-xl">
                <div class="p-6 border-b border-neutral-700">
                    <h2 class="text-xl font-bold text-white">Nouvel administrateur</h2>
                </div>
                <form method="POST" action="{{ route('super-admin.utilisateurs.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Nom complet *</label>
                        <input type="text" name="name" required class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Email *</label>
                        <input type="email" name="email" required class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Téléphone</label>
                        <input type="tel" name="phone" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Mot de passe *</label>
                        <input type="password" name="password" required minlength="8" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <input type="hidden" name="role" value="super_admin">
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="flex-1 h-10 px-4 bg-neutral-700 text-white rounded-lg font-medium hover:bg-neutral-600 transition-colors">
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
