<x-layouts.admin-super title="Intégrations Jeko">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Intégrations Jeko</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Gérez les demandes d'intégration Jeko des restaurants.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-5">

        {{-- Total --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <p class="text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Total</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <p class="text-3xl font-bold" style="color:var(--sa-warning);">{{ number_format($stats['pending']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">En attente</p>
        </div>

        {{-- Approved --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <p class="text-3xl font-bold" style="color:var(--sa-primary);">{{ number_format($stats['approved']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Approuvés</p>
        </div>

        {{-- Integrated --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <p class="text-3xl font-bold" style="color:var(--sa-success);">{{ number_format($stats['integrated']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Intégrés</p>
        </div>

        {{-- Rejected --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <p class="text-3xl font-bold" style="color:var(--sa-danger);">{{ number_format($stats['rejected']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Rejetés</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background:rgba(61,158,98,0.10);border-color:rgba(61,158,98,0.30);color:var(--sa-success);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background:rgba(220,38,38,0.10);border-color:rgba(220,38,38,0.30);color:var(--sa-danger);">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-4 rounded-2xl border p-4 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <form method="GET" action="{{ route('super-admin.jeko.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="mb-1 block text-xs font-medium" style="color:var(--sa-muted-fg);">Recherche</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Restaurant, email..."
                       class="w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2"
                       style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
            </div>
            <div class="min-w-[160px]">
                <label class="mb-1 block text-xs font-medium" style="color:var(--sa-muted-fg);">Statut</label>
                <select name="status"
                        class="w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2"
                        style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                    <option value="">Tous</option>
                    @foreach(\App\Enums\JekoSubMerchantStatus::cases() as $statusCase)
                        <option value="{{ $statusCase->value }}" @selected(request('status') === $statusCase->value)>
                            {{ $statusCase->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="inline-flex h-10 items-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm"
                    style="background:var(--sa-primary);color:#fff;">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('super-admin.jeko.index') }}"
                   class="inline-flex h-10 items-center gap-2 rounded-lg border px-4 text-sm font-medium"
                   style="border-color:var(--sa-border);color:var(--sa-muted-fg);">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border shadow-sm overflow-hidden" style="border-color:var(--sa-border);background:var(--sa-card);">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);">Raison sociale</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);">Opérateur</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);">Soumis le</th>
                        <th class="px-4 py-3 text-right font-semibold" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subMerchants as $sub)
                        @php
                            $color = match($sub->status->color()) {
                                'yellow' => ['bg' => 'rgba(217,119,6,0.10)', 'fg' => 'var(--sa-warning)', 'border' => 'rgba(217,119,6,0.25)'],
                                'blue'   => ['bg' => 'rgba(59,130,246,0.10)', 'fg' => '#3b82f6', 'border' => 'rgba(59,130,246,0.25)'],
                                'green'  => ['bg' => 'rgba(61,158,98,0.10)', 'fg' => 'var(--sa-success)', 'border' => 'rgba(61,158,98,0.25)'],
                                'red'    => ['bg' => 'rgba(220,38,38,0.10)', 'fg' => 'var(--sa-danger)', 'border' => 'rgba(220,38,38,0.25)'],
                                default  => ['bg' => 'transparent', 'fg' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'],
                            };
                        @endphp
                        <tr class="border-b transition hover:opacity-90" style="border-color:var(--sa-border);">
                            <td class="px-4 py-3">
                                <a href="{{ route('super-admin.jeko.show', $sub) }}"
                                   class="font-medium hover:underline" style="color:var(--sa-fg);">
                                    {{ $sub->restaurant?->name ?? '—' }}
                                </a>
                                @if($sub->restaurant?->owner)
                                    <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">{{ $sub->restaurant->owner->email }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $sub->legal_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                      style="background:{{ $color['bg'] }};color:{{ $color['fg'] }};border-color:{{ $color['border'] }};">
                                    {{ $sub->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm" style="color:var(--sa-muted-fg);">
                                {{ $sub->mobile_money_operator?->value ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm" style="color:var(--sa-muted-fg);">
                                {{ $sub->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('super-admin.jeko.show', $sub) }}"
                                       class="inline-flex h-8 items-center gap-1 rounded-lg border px-3 text-xs font-medium"
                                       style="border-color:var(--sa-border);color:var(--sa-fg);">
                                        Détail
                                    </a>
                                    @if($sub->isPending())
                                        {{-- Approve button --}}
                                        <form method="POST" action="{{ route('super-admin.jeko.approve', $sub) }}"
                                              onsubmit="return confirm('Approuver cette demande ?')">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center gap-1 rounded-lg px-3 text-xs font-medium shadow-sm"
                                                    style="background:var(--sa-success);color:#fff;">
                                                Approuver
                                            </button>
                                        </form>

                                        {{-- Reject button (opens modal) --}}
                                        <button type="button"
                                                onclick="openRejectModal({{ $sub->id }})"
                                                class="inline-flex h-8 items-center gap-1 rounded-lg px-3 text-xs font-medium shadow-sm"
                                                style="background:var(--sa-danger);color:#fff;">
                                            Rejeter
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm" style="color:var(--sa-muted-fg);">
                                Aucune demande trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subMerchants->hasPages())
            <div class="border-t px-4 py-3" style="border-color:var(--sa-border);">
                {{ $subMerchants->links() }}
            </div>
        @endif
    </div>

    {{-- Reject Modals --}}
    @foreach($subMerchants as $sub)
        @if($sub->isPending())
            <div id="reject-modal-{{ $sub->id }}"
                 class="fixed inset-0 z-50 hidden items-center justify-center"
                 style="background:rgba(0,0,0,0.5);">
                <div class="w-full max-w-md rounded-2xl border p-6 shadow-xl"
                     style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="mb-1 text-lg font-semibold" style="color:var(--sa-fg);">Rejeter la demande</h2>
                    <p class="mb-4 text-sm" style="color:var(--sa-muted-fg);">
                        Restaurant : <strong>{{ $sub->restaurant?->name ?? $sub->legal_name }}</strong>
                    </p>
                    <form method="POST" action="{{ route('super-admin.jeko.reject', $sub) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium" style="color:var(--sa-fg);">
                                Motif du rejet <span style="color:var(--sa-danger);">*</span>
                            </label>
                            <textarea name="rejected_reason"
                                      rows="4"
                                      maxlength="1000"
                                      required
                                      placeholder="Expliquez la raison du rejet..."
                                      class="w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2"
                                      style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);resize:vertical;"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    onclick="closeRejectModal({{ $sub->id }})"
                                    class="inline-flex h-9 items-center gap-2 rounded-lg border px-4 text-sm font-medium"
                                    style="border-color:var(--sa-border);color:var(--sa-muted-fg);">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm"
                                    style="background:var(--sa-danger);color:#fff;">
                                Confirmer le rejet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        function openRejectModal(id) {
            const modal = document.getElementById('reject-modal-' + id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeRejectModal(id) {
            const modal = document.getElementById('reject-modal-' + id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
        // Close modals on backdrop click
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                e.target.classList.add('hidden');
                e.target.classList.remove('flex');
            }
        });
    </script>

</x-layouts.admin-super>
