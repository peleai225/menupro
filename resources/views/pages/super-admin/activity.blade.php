<x-layouts.admin-super title="Activité">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Journal d'activité</h1>
            <p class="text-neutral-500 mt-1">Historique de toutes les actions sur la plateforme.</p>
        </div>
        <a href="{{ route('super-admin.activity.export') }}" class="btn btn-outline border-neutral-200 text-neutral-700 hover:bg-neutral-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white border border-neutral-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <select name="restaurant" class="w-full h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les restaurants</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" {{ request('restaurant') == $restaurant->id ? 'selected' : '' }}>
                            {{ $restaurant->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <select name="action" class="w-full h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="Du">
            </div>
            <div>
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="Au">
            </div>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Activity Log -->
    <div class="bg-white border border-neutral-200 rounded-xl shadow-sm">
        <div class="divide-y divide-neutral-100">
            @forelse($activities as $log)
                @php
                    $actionColors = [
                        'login' => 'bg-blue-500/20 text-blue-400',
                        'logout' => 'bg-neutral-500/20 text-neutral-400',
                        'create' => 'bg-secondary-500/20 text-secondary-400',
                        'update' => 'bg-primary-500/20 text-primary-400',
                        'delete' => 'bg-red-500/20 text-red-400',
                        'order' => 'bg-accent-500/20 text-accent-400',
                        'payment' => 'bg-green-500/20 text-green-400',
                    ];
                    $actionIcons = [
                        'login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                        'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                        'create' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                        'update' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                        'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                        'order' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        'payment' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                    ];
                    $colorClass = $actionColors[$log->action] ?? $actionColors[explode('_', $log->action)[0]] ?? 'bg-neutral-500/20 text-neutral-400';
                    $iconPath = $actionIcons[$log->action] ?? $actionIcons[explode('_', $log->action)[0]] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                @endphp
                <div class="p-4 hover:bg-neutral-50 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 {{ $colorClass }} rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <p class="text-neutral-900 font-medium">{{ $log->user?->name ?? 'Système' }}</p>
                                    @if($log->restaurant)
                                        <span class="text-neutral-500">•</span>
                                        <span class="text-neutral-600 text-sm">{{ $log->restaurant->name }}</span>
                                    @endif
                                </div>
                                <span class="text-sm text-neutral-500 whitespace-nowrap">{{ $log->created_at->locale('fr')->diffForHumans() }}</span>
                            </div>
                            <p class="text-neutral-600 text-sm mt-1">
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                @if($log->description)
                                    : {{ $log->description }}
                                @endif
                            </p>
                            @if($log->ip_address)
                                <p class="text-neutral-500 text-xs mt-1">IP: {{ $log->ip_address }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-neutral-500">Aucune activité trouvée</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($activities->hasPages())
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    @endif
</x-layouts.admin-super>
