<x-layouts.admin-super title="Annonces">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Annonces</h1>
            <p class="text-neutral-400 mt-1">Communiquez avec tous les restaurants de la plateforme.</p>
        </div>
        <a href="{{ route('super-admin.announcements.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvelle annonce
        </a>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-500/20 border border-secondary-500/30 rounded-xl text-secondary-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mb-6 p-4 bg-yellow-500/20 border border-yellow-500/30 rounded-xl text-yellow-400">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-5">
            <p class="text-sm text-neutral-400">Total</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-5">
            <p class="text-sm text-neutral-400">Actives</p>
            <p class="text-2xl font-bold text-secondary-400 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-5">
            <p class="text-sm text-neutral-400">Programmées</p>
            <p class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['scheduled'] }}</p>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            @php
                $typeColors = [
                    'info' => 'border-blue-500/30 bg-blue-500/10',
                    'warning' => 'border-yellow-500/30 bg-yellow-500/10',
                    'success' => 'border-secondary-500/30 bg-secondary-500/10',
                    'danger' => 'border-red-500/30 bg-red-500/10',
                ];
                $typeTextColors = [
                    'info' => 'text-blue-400',
                    'warning' => 'text-yellow-400',
                    'success' => 'text-secondary-400',
                    'danger' => 'text-red-400',
                ];
            @endphp
            <div class="bg-neutral-800/50 border {{ $typeColors[$announcement->type] }} rounded-2xl p-6 {{ !$announcement->is_active ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <!-- Type Icon -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $typeColors[$announcement->type] }}">
                            <svg class="w-6 h-6 {{ $typeTextColors[$announcement->type] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $announcement->type_icon }}"/>
                            </svg>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $announcement->title }}</h3>
                                @if(!$announcement->is_active)
                                    <span class="px-2 py-0.5 bg-neutral-600 text-neutral-300 rounded text-xs">Inactive</span>
                                @endif
                                @if($announcement->starts_at && $announcement->starts_at->isFuture())
                                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs">Programmée</span>
                                @endif
                            </div>
                            <p class="text-neutral-300 mb-4">{{ Str::limit($announcement->content, 200) }}</p>
                            
                            <!-- Meta -->
                            <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Cible: {{ ucfirst($announcement->target) }}
                                </span>
                                @if($announcement->starts_at)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Début: {{ $announcement->starts_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                                @if($announcement->ends_at)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Fin: {{ $announcement->ends_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                                @if($announcement->email_sent_at)
                                    <span class="flex items-center gap-1 text-secondary-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Emails envoyés
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Par: {{ $announcement->creator?->name ?? 'Système' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        @if(!$announcement->email_sent_at && $announcement->is_active)
                            <form action="{{ route('super-admin.announcements.send-emails', $announcement) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 hover:bg-neutral-700 rounded-lg text-neutral-400 hover:text-blue-400 transition-colors" title="Envoyer par email">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('super-admin.announcements.edit', $announcement) }}" class="p-2 hover:bg-neutral-700 rounded-lg text-neutral-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('super-admin.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette annonce ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 hover:bg-red-500/20 rounded-lg text-neutral-400 hover:text-red-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Aucune annonce</h3>
                <p class="text-neutral-400 mb-6">Créez votre première annonce pour communiquer avec les restaurants.</p>
                <a href="{{ route('super-admin.announcements.create') }}" class="btn btn-primary">
                    Créer une annonce
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($announcements->hasPages())
        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
    @endif
</x-layouts.admin-super>
