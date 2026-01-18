<x-layouts.admin-restaurant title="Catégories d'ingrédients">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Catégories d'ingrédients</h1>
            <p class="text-neutral-500 mt-1">Organisez vos ingrédients par catégories.</p>
        </div>
        <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajouter une catégorie
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
            <div class="card p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg" style="background-color: {{ $category->color ?? '#6b7280' }}">
                            {{ strtoupper(substr($category->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-neutral-900">{{ $category->name }}</h3>
                            <p class="text-sm text-neutral-500">{{ $category->ingredients_count }} ingrédient(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->color ?? '#6b7280' }}')" 
                                class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                                title="Modifier">
                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('restaurant.stock.categories-ingredients.destroy', $category) }}" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Les ingrédients seront déplacés vers « Sans catégorie ».')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="card p-12 text-center">
                    <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <p class="text-neutral-500 mb-4">Aucune catégorie créée</p>
                    <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="btn btn-primary">
                        Créer votre première catégorie
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('addCategoryModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h2 class="text-2xl font-bold text-neutral-900 mb-6">Nouvelle catégorie</h2>
                <form method="POST" action="{{ route('restaurant.stock.categories-ingredients.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom de la catégorie</label>
                            <input type="text" name="name" required 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Couleur</label>
                            <input type="color" name="color" value="#6b7280" 
                                   class="w-full h-12 rounded-lg cursor-pointer">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="submit" class="btn btn-primary flex-1">Créer</button>
                        <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="btn btn-outline flex-1">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('editCategoryModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h2 class="text-2xl font-bold text-neutral-900 mb-6">Modifier la catégorie</h2>
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom de la catégorie</label>
                            <input type="text" name="name" id="editCategoryName" required 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Couleur</label>
                            <input type="color" name="color" id="editCategoryColor" 
                                   class="w-full h-12 rounded-lg cursor-pointer">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="submit" class="btn btn-primary flex-1">Enregistrer</button>
                        <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="btn btn-outline flex-1">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editCategory(id, name, color) {
                document.getElementById('editCategoryName').value = name;
                document.getElementById('editCategoryColor').value = color;
                document.getElementById('editCategoryForm').action = '{{ route('restaurant.stock.categories-ingredients.update', ':id') }}'.replace(':id', id);
                document.getElementById('editCategoryModal').classList.remove('hidden');
            }
        </script>
    @endpush
</x-layouts.admin-restaurant>
