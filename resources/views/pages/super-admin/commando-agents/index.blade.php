<x-layouts.admin-super title="Agents Commando">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Agents Commando</h1>
            <p class="text-neutral-500 mt-1">Inscriptions et vérification des agents.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'pending_review']) }}"
           class="bg-white border border-neutral-200 shadow-sm rounded-xl p-4 hover:border-amber-500/50 transition-colors">
            <p class="text-sm text-neutral-500">En attente</p>
            <p class="text-2xl font-bold text-amber-400">{{ $counts['pending'] }}</p>
        </a>
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'valide']) }}"
           class="bg-white border border-neutral-200 shadow-sm rounded-xl p-4 hover:border-green-500/50 transition-colors">
            <p class="text-sm text-neutral-500">Valides</p>
            <p class="text-2xl font-bold text-green-400">{{ $counts['valide'] }}</p>
        </a>
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'banni']) }}"
           class="bg-white border border-neutral-200 shadow-sm rounded-xl p-4 hover:border-red-500/50 transition-colors">
            <p class="text-sm text-neutral-500">Bannis</p>
            <p class="text-2xl font-bold text-red-600">{{ $counts['banni'] }}</p>
        </a>
        <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-4">
            <p class="text-sm text-neutral-500">Total</p>
            <p class="text-2xl font-bold text-neutral-900">{{ $agents->total() }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white border border-neutral-200 shadow-sm rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <select name="status" class="h-10 px-4 bg-neutral-100 border border-neutral-300 rounded-lg text-neutral-900">
                <option value="">Tous les statuts</option>
                <option value="shadow" {{ request('status') === 'shadow' ? 'selected' : '' }}>Shadow</option>
                <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>En attente</option>
                <option value="valide" {{ request('status') === 'valide' ? 'selected' : '' }}>Valide</option>
                <option value="rejete" {{ request('status') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
                <option value="banni" {{ request('status') === 'banni' ? 'selected' : '' }}>Banni</option>
            </select>
            <input type="text" name="city" value="{{ request('city') }}" placeholder="Ville..."
                   class="h-10 px-4 bg-neutral-100 border border-neutral-300 rounded-lg text-neutral-900 placeholder-neutral-500">
            <button type="submit" class="h-10 px-6 bg-primary-500 text-neutral-900 rounded-lg font-medium hover:bg-primary-600">Filtrer</button>
        </div>
    </form>

    <div class="bg-white border border-neutral-200 shadow-sm rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-100/50 border-b border-neutral-300">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Agent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">WhatsApp</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Inscrit le</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @forelse($agents as $a)
                        <tr class="hover:bg-neutral-100/30">
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-900">{{ $a->full_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-neutral-600">{{ $a->whatsapp }}</td>
                            <td class="px-6 py-4 text-neutral-500">{{ $a->city ?? '–' }}</td>
                            <td class="px-6 py-4">
                                @php $status = $a->status_verification; @endphp
                                <span class="px-2 py-1 rounded-lg text-xs font-medium
                                    @if($status->value === 'pending_review') bg-amber-50 text-amber-700
                                    @elseif($status->value === 'valide') bg-emerald-50 text-emerald-700
                                    @elseif($status->value === 'rejete' || $status->value === 'banni') bg-red-50 text-red-700
                                    @else bg-neutral-100 text-neutral-600
                                    @endif">
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 text-sm">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('super-admin.commando.agents.show', $a) }}"
                                       class="text-primary-400 hover:text-primary-300 text-sm font-medium">Voir</a>
                                    <form method="POST" action="{{ route('super-admin.commando.agents.destroy', $a) }}" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement cet agent ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-300 text-sm font-medium">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500">Aucun agent.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agents->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200">
                {{ $agents->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
