<x-layouts.admin-super title="Livreur : {{ $driver->name }}">
    <div class="space-y-6 max-w-5xl">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm" style="color:var(--sa-muted-fg);">
            <a href="{{ route('super-admin.drivers.index') }}" class="hover:underline" style="color:var(--sa-muted-fg);">Livreurs</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium" style="color:var(--sa-fg);">{{ $driver->name }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Profil --}}
            <div class="rounded-2xl border shadow-sm p-6 space-y-4" style="background:var(--sa-card);border-color:var(--sa-border);">
                <div class="flex items-center gap-4">
                    @if($driver->photo_path)
                        <img src="{{ \App\Support\StorageUrl::url($driver->photo_path) }}"
                             alt="{{ $driver->name }}"
                             class="w-16 h-16 rounded-2xl object-cover border"
                             style="border-color:var(--sa-border);"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 items-center justify-center text-white font-bold text-2xl" style="display:none;">
                            {{ strtoupper(substr($driver->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-2xl">
                            {{ strtoupper(substr($driver->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="font-bold text-lg" style="color:var(--sa-fg);">{{ $driver->name }}</h2>
                        @if($driver->verification_status === 'approved' && $driver->is_active && $driver->is_available)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> En ligne
                            </span>
                        @elseif($driver->verification_status === 'approved')
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Approuvé</span>
                        @elseif($driver->verification_status === 'pending')
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">En attente</span>
                        @elseif($driver->verification_status === 'rejected')
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejeté</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:var(--sa-muted);color:var(--sa-muted-fg);">Suspendu</span>
                        @endif
                    </div>
                </div>

                <div class="text-sm" style="border-top:1px solid var(--sa-border);">
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Téléphone</span><span class="font-medium" style="color:var(--sa-fg);">{{ $driver->phone }}</span></div>
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Email</span><span class="font-medium text-xs" style="color:var(--sa-fg);">{{ $driver->email }}</span></div>
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Ville</span><span class="font-medium" style="color:var(--sa-fg);">{{ $driver->city ?? '—' }}</span></div>
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Véhicule</span><span class="font-medium" style="color:var(--sa-fg);">{{ $driver->vehicle_type ?? '—' }}</span></div>
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Plaque</span><span class="font-medium" style="color:var(--sa-fg);">{{ $driver->vehicle_plate ?? '—' }}</span></div>
                    <div class="py-2 flex justify-between" style="border-bottom:1px solid var(--sa-border);"><span style="color:var(--sa-muted-fg);">Inscrit le</span><span class="font-medium" style="color:var(--sa-fg);">{{ $driver->created_at->format('d/m/Y') }}</span></div>
                    <div class="py-2 flex justify-between"><span style="color:var(--sa-muted-fg);">Token push</span>
                        @if($driver->fcm_token)
                            <span class="px-1.5 py-0.5 text-xs rounded bg-emerald-100 text-emerald-700">Actif</span>
                        @else
                            <span class="text-xs" style="color:var(--sa-muted-fg);">Aucun</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-2 pt-2">
                    @if($driver->verification_status === 'pending')
                        <form method="POST" action="{{ route('super-admin.drivers.approve', $driver) }}">@csrf
                            <button class="w-full h-9 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700">Approuver</button>
                        </form>
                        <form method="POST" action="{{ route('super-admin.drivers.reject', $driver) }}">@csrf
                            <button class="w-full h-9 bg-red-500 text-white rounded-xl text-sm font-medium hover:bg-red-600">Rejeter</button>
                        </form>
                    @elseif($driver->is_active)
                        <form method="POST" action="{{ route('super-admin.drivers.suspend', $driver) }}">@csrf
                            <button class="w-full h-9 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Suspendre</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('super-admin.drivers.reactivate', $driver) }}">@csrf
                            <button class="w-full h-9 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700">Réactiver</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Stats + Livraisons --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl p-4 border shadow-sm text-center" style="background:var(--sa-card);border-color:var(--sa-border);">
                        <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $driver->total_deliveries ?? 0 }}</p>
                        <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Livraisons</p>
                    </div>
                    <div class="rounded-2xl p-4 border shadow-sm text-center" style="background:var(--sa-card);border-color:var(--sa-border);">
                        <p class="text-2xl font-bold text-amber-500">{{ $driver->rating ? '★ '.number_format($driver->rating,1) : '—' }}</p>
                        <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Note</p>
                    </div>
                    <div class="rounded-2xl p-4 border shadow-sm text-center" style="background:var(--sa-card);border-color:var(--sa-border);">
                        <p class="text-2xl font-bold text-primary-600">{{ number_format($driver->total_earnings_xof ?? 0) }} F</p>
                        <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Gains totaux</p>
                    </div>
                </div>

                {{-- Dernières livraisons --}}
                <div class="rounded-2xl border shadow-sm overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <div class="px-5 py-4" style="border-bottom:1px solid var(--sa-border);">
                        <h3 class="font-semibold" style="color:var(--sa-fg);">Dernières livraisons</h3>
                    </div>
                    @if($recentDeliveries->count())
                        <div>
                            @foreach($recentDeliveries as $delivery)
                            <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm" style="border-bottom:1px solid var(--sa-border);">
                                <div>
                                    <p class="font-medium" style="color:var(--sa-fg);">#{{ $delivery->order_id ?? $delivery->id }}</p>
                                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($delivery->fee_xof)
                                        <span class="text-sm font-medium" style="color:var(--sa-fg);">{{ number_format($delivery->fee_xof) }} F</span>
                                    @endif
                                    @php
                                        $statusMap = [
                                            'pending'               => ['text' => 'En attente',  'class' => 'bg-neutral-100 text-neutral-600'],
                                            'assigned'              => ['text' => 'Assigné',      'class' => 'bg-blue-100 text-blue-700'],
                                            'heading_to_restaurant' => ['text' => 'En route',     'class' => 'bg-indigo-100 text-indigo-700'],
                                            'picked_up'             => ['text' => 'Récupéré',     'class' => 'bg-orange-100 text-orange-700'],
                                            'delivering'            => ['text' => 'En livraison', 'class' => 'bg-purple-100 text-purple-700'],
                                            'delivered'             => ['text' => 'Livré',         'class' => 'bg-emerald-100 text-emerald-700'],
                                            'cancelled'             => ['text' => 'Annulé',        'class' => 'bg-red-100 text-red-700'],
                                        ];
                                        $statusKey = $delivery->status instanceof \App\Enums\DeliveryStatus
                                            ? $delivery->status->value
                                            : (string) $delivery->status;
                                        $s = $statusMap[$statusKey] ?? ['text' => $statusKey, 'class' => 'bg-neutral-100 text-neutral-600'];
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $s['class'] }}">{{ $s['text'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-10 text-center text-sm" style="color:var(--sa-muted-fg);">Aucune livraison enregistrée.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Documents du livreur --}}
        <div class="rounded-2xl border shadow-sm p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h3 class="font-bold text-base mb-5" style="color:var(--sa-fg);">Documents d'inscription</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                {{-- CNI --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--sa-muted-fg);">
                        Pièce d'identité (CNI)
                        @if($driver->cni_number)
                            <span class="ml-1 font-normal normal-case" style="color:var(--sa-fg);">· {{ $driver->cni_number }}</span>
                        @endif
                    </p>
                    @if($driver->cni_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($driver->cni_photo_path))
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($driver->cni_photo_path) }}" target="_blank" class="block group">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($driver->cni_photo_path) }}"
                                 alt="CNI {{ $driver->name }}"
                                 class="w-full h-40 object-cover rounded-xl border group-hover:opacity-90 transition-opacity"
                                 style="border-color:var(--sa-border);">
                            <p class="text-xs mt-1 text-center" style="color:var(--sa-muted-fg);">Cliquer pour agrandir</p>
                        </a>
                    @else
                        <div class="w-full h-40 rounded-xl border flex items-center justify-center" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <p class="text-xs" style="color:var(--sa-muted-fg);">Aucune photo CNI</p>
                        </div>
                    @endif
                </div>

                {{-- Permis de conduire --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--sa-muted-fg);">Permis de conduire</p>
                    @if($driver->license_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($driver->license_photo_path))
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($driver->license_photo_path) }}" target="_blank" class="block group">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($driver->license_photo_path) }}"
                                 alt="Permis {{ $driver->name }}"
                                 class="w-full h-40 object-cover rounded-xl border group-hover:opacity-90 transition-opacity"
                                 style="border-color:var(--sa-border);">
                            <p class="text-xs mt-1 text-center" style="color:var(--sa-muted-fg);">Cliquer pour agrandir</p>
                        </a>
                    @else
                        <div class="w-full h-40 rounded-xl border flex items-center justify-center" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <p class="text-xs" style="color:var(--sa-muted-fg);">Aucune photo permis</p>
                        </div>
                    @endif
                </div>

                {{-- Photo du véhicule --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--sa-muted-fg);">
                        Véhicule
                        @if($driver->vehicle_plate)
                            <span class="ml-1 font-normal normal-case" style="color:var(--sa-fg);">· {{ $driver->vehicle_plate }}</span>
                        @endif
                    </p>
                    @if($driver->vehicle_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($driver->vehicle_photo_path))
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($driver->vehicle_photo_path) }}" target="_blank" class="block group">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($driver->vehicle_photo_path) }}"
                                 alt="Véhicule {{ $driver->name }}"
                                 class="w-full h-40 object-cover rounded-xl border group-hover:opacity-90 transition-opacity"
                                 style="border-color:var(--sa-border);">
                            <p class="text-xs mt-1 text-center" style="color:var(--sa-muted-fg);">Cliquer pour agrandir</p>
                        </a>
                    @else
                        <div class="w-full h-40 rounded-xl border flex items-center justify-center" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <p class="text-xs" style="color:var(--sa-muted-fg);">Aucune photo véhicule</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Photo de profil --}}
            @if($driver->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($driver->photo_path))
            <div class="mt-5 pt-5" style="border-top:1px solid var(--sa-border);">
                <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color:var(--sa-muted-fg);">Photo de profil</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($driver->photo_path) }}"
                     alt="Photo {{ $driver->name }}"
                     class="w-24 h-24 object-cover rounded-2xl border"
                     style="border-color:var(--sa-border);">
            </div>
            @endif
        </div>

    </div>
</x-layouts.admin-super>
