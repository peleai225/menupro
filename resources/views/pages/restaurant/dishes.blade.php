<x-layouts.admin-restaurant title="Plats">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Plats</h1>
            <p class="text-neutral-500 mt-1">Gérez votre menu et vos plats.</p>
        </div>
        <a href="{{ route('restaurant.plats.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouveau plat
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Rechercher un plat..." class="w-full h-10 pl-10 pr-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
            <select class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes les catégories</option>
                <option value="1">Entrées</option>
                <option value="2">Plats principaux</option>
                <option value="3">Desserts</option>
            </select>
            <select class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>
    </div>

    <!-- Dishes Table -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[500px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Plat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach([
                        ['id' => 1, 'name' => 'Poulet braisé', 'category' => 'Grillades', 'price' => '4 500', 'status' => 'active', 'image' => null],
                        ['id' => 2, 'name' => 'Attiéké poisson', 'category' => 'Plats principaux', 'price' => '3 500', 'status' => 'active', 'image' => null],
                        ['id' => 3, 'name' => 'Alloco garni', 'category' => 'Accompagnements', 'price' => '2 000', 'status' => 'active', 'image' => null],
                        ['id' => 4, 'name' => 'Salade composée', 'category' => 'Entrées', 'price' => '1 500', 'status' => 'inactive', 'image' => null],
                        ['id' => 5, 'name' => 'Jus de bissap', 'category' => 'Boissons', 'price' => '500', 'status' => 'active', 'image' => null],
                    ] as $dish)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-neutral-200 rounded-xl flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-neutral-900">{{ $dish['name'] }}</p>
                                    <p class="text-sm text-neutral-500">#{{ $dish['id'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-neutral">{{ $dish['category'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-neutral-900">{{ $dish['price'] }} F</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($dish['status'] === 'active')
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-neutral">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 hover:bg-neutral-100 rounded-lg text-neutral-400 hover:text-neutral-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button class="p-2 hover:bg-red-50 rounded-lg text-neutral-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin-restaurant>

