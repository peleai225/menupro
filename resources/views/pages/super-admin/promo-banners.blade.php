<x-layouts.admin-super title="Bannières promo">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Bannières promotionnelles</h1>
            <p class="text-neutral-500 mt-1">Images publicitaires affichées sur les menus dans l'application client.</p>
        </div>
        <a href="{{ route('super-admin.promo-banners.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvelle bannière
        </a>
    </div>

    <!-- Flash -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">{{ session('error') }}</div>
    @endif

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $banner)
            <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm {{ !$banner->is_active ? 'opacity-60' : '' }}">
                <!-- Image -->
                <div class="relative aspect-[16/7] bg-neutral-100">
                    <img src="{{ Storage::disk('public')->url($banner->image_path) }}"
                         alt="{{ $banner->title }}"
                         class="w-full h-full object-cover">

                    <!-- Scope badge -->
                    <div class="absolute top-2 left-2">
                        @if($banner->restaurant_id)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 border border-violet-200 backdrop-blur-sm">
                                {{ Str::limit($banner->restaurant?->name, 20) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200 backdrop-blur-sm">
                                Global (tous les restaurants)
                            </span>
                        @endif
                    </div>

                    <!-- Active indicator -->
                    <div class="absolute top-2 right-2">
                        @if($banner->is_active)
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 block shadow-sm ring-2 ring-white"></span>
                        @else
                            <span class="w-2.5 h-2.5 rounded-full bg-neutral-400 block shadow-sm ring-2 ring-white"></span>
                        @endif
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4">
                    <div class="mb-3">
                        @if($banner->title)
                            <p class="font-semibold text-neutral-900 text-sm">{{ $banner->title }}</p>
                        @endif
                        @if($banner->subtitle)
                            <p class="text-xs text-neutral-500 mt-0.5">{{ $banner->subtitle }}</p>
                        @endif
                    </div>

                    <!-- Meta badges -->
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @if($banner->link_type !== 'none')
                            @php
                                $linkLabels = ['dish' => 'Plat', 'promo_code' => 'Promo', 'url' => 'URL'];
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                Lien: {{ $linkLabels[$banner->link_type] ?? $banner->link_type }}
                            </span>
                        @endif
                        @if($banner->starts_at || $banner->ends_at)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                Planifiée
                            </span>
                        @endif
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600 border border-neutral-200">
                            #{{ $banner->sort_order }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-3 border-t border-neutral-100">
                        <form action="{{ route('super-admin.promo-banners.toggle', $banner) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-xs py-1.5 rounded-lg border transition-colors
                                {{ $banner->is_active
                                    ? 'border-neutral-200 text-neutral-600 hover:bg-neutral-50'
                                    : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                {{ $banner->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>

                        <a href="{{ route('super-admin.promo-banners.edit', $banner) }}"
                           class="p-2 hover:bg-neutral-50 rounded-lg text-neutral-600 hover:text-neutral-900 transition-colors" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        <form action="{{ route('super-admin.promo-banners.destroy', $banner) }}" method="POST"
                              class="inline" onsubmit="return confirm('Supprimer cette bannière ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 hover:bg-red-50 rounded-lg text-neutral-600 hover:text-red-700 transition-colors" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border border-neutral-200 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucune bannière</h3>
                <p class="text-neutral-500 mb-6">Créez votre première bannière promotionnelle.</p>
                <a href="{{ route('super-admin.promo-banners.create') }}" class="btn btn-primary">Créer une bannière</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($banners->hasPages())
        <div class="mt-6">{{ $banners->links() }}</div>
    @endif
</x-layouts.admin-super>
