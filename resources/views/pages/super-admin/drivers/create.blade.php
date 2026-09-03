<x-layouts.admin-super title="Ajouter un livreur">
    <div class="space-y-6 max-w-2xl">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm" style="color:var(--sa-muted-fg);">
            <a href="{{ route('super-admin.drivers.index') }}" class="hover:underline" style="color:var(--sa-muted-fg);">Livreurs</a>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span style="color:var(--sa-fg);">Ajouter un livreur</span>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border p-4" style="background:rgba(220,38,38,0.05);border-color:rgba(220,38,38,0.20);">
                <p class="text-sm font-semibold mb-2" style="color:var(--sa-danger);">Corrigez les erreurs :</p>
                <ul class="space-y-0.5 text-sm" style="color:var(--sa-danger);">
                    @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.drivers.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- ══ Informations personnelles ══ --}}
            <div class="rounded-2xl border p-6 space-y-4" style="border-color:var(--sa-border);background:var(--sa-card);">
                <h2 class="text-sm font-bold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Informations personnelles</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nom --}}
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Nom complet <span style="color:var(--sa-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Jean Kouassi"
                               required
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none focus:ring-2"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>

                    {{-- Téléphone --}}
                    <div>
                        <label for="phone" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Téléphone <span style="color:var(--sa-danger);">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                               placeholder="07 00 00 00 00"
                               required
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Email <span class="font-normal text-xs" style="color:var(--sa-muted-fg);">(optionnel)</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="livreur@exemple.com"
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Mot de passe <span style="color:var(--sa-danger);">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                               placeholder="Minimum 6 caractères"
                               required
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>

                    {{-- CNI --}}
                    <div>
                        <label for="cni_number" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Numéro CNI <span class="font-normal text-xs" style="color:var(--sa-muted-fg);">(optionnel)</span>
                        </label>
                        <input type="text" id="cni_number" name="cni_number" value="{{ old('cni_number') }}"
                               placeholder="CI-ABJ-XX-2024-XXXXX"
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                </div>
            </div>

            {{-- ══ Zone & Véhicule ══ --}}
            <div class="rounded-2xl border p-6 space-y-4" style="border-color:var(--sa-border);background:var(--sa-card);">
                <h2 class="text-sm font-bold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Zone & Véhicule</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Ville --}}
                    <div>
                        <label for="city" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Ville <span style="color:var(--sa-danger);">*</span>
                        </label>
                        @if($cities->count())
                            <select id="city" name="city" required
                                    class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none appearance-none"
                                    style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                                <option value="">Sélectionner une ville</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                   placeholder="Abidjan, Bouaké…"
                                   required
                                   class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                                   style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                        @endif
                    </div>

                    {{-- Zone --}}
                    <div>
                        <label for="zone" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Zone / Quartier <span class="font-normal text-xs" style="color:var(--sa-muted-fg);">(optionnel)</span>
                        </label>
                        <input type="text" id="zone" name="zone" value="{{ old('zone') }}"
                               placeholder="Cocody, Yopougon…"
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>

                    {{-- Type véhicule --}}
                    <div>
                        <label for="vehicle_type" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Type de véhicule <span style="color:var(--sa-danger);">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['moto' => '🏍️ Moto', 'velo' => '🚲 Vélo', 'voiture' => '🚗 Voiture'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="vehicle_type" value="{{ $val }}"
                                       {{ old('vehicle_type', 'moto') === $val ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-xs font-semibold transition-all
                                            peer-checked:border-[color:var(--sa-primary)] peer-checked:bg-[rgba(194,98,31,0.06)]"
                                     style="border-color:var(--sa-border);color:var(--sa-fg);">
                                    <span class="text-xl">{{ explode(' ', $label)[0] }}</span>
                                    <span>{{ explode(' ', $label)[1] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Plaque --}}
                    <div>
                        <label for="vehicle_plate" class="block text-sm font-semibold mb-1.5" style="color:var(--sa-fg);">
                            Plaque d'immatriculation <span class="font-normal text-xs" style="color:var(--sa-muted-fg);">(optionnel)</span>
                        </label>
                        <input type="text" id="vehicle_plate" name="vehicle_plate" value="{{ old('vehicle_plate') }}"
                               placeholder="AB 1234 CI"
                               class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                               style="background:var(--sa-surface);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                </div>
            </div>

            {{-- ══ Documents (optionnels côté admin) ══ --}}
            <div class="rounded-2xl border p-6 space-y-4" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Documents</h2>
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">Optionnels</span>
                </div>
                <p class="text-xs" style="color:var(--sa-muted-fg);">Le livreur pourra fournir ses documents via l'application mobile après l'inscription.</p>

                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['name' => 'photo',          'label' => 'Photo de profil'],
                        ['name' => 'cni_photo',       'label' => 'Photo CNI'],
                        ['name' => 'license_photo',   'label' => 'Photo permis'],
                        ['name' => 'vehicle_photo',   'label' => 'Photo véhicule'],
                    ] as $doc)
                    <div>
                        <label for="{{ $doc['name'] }}" class="block text-xs font-semibold mb-1.5" style="color:var(--sa-fg);">{{ $doc['label'] }}</label>
                        <label for="{{ $doc['name'] }}" class="flex flex-col items-center justify-center gap-1.5 h-24 rounded-xl border-2 border-dashed cursor-pointer transition-colors"
                               style="border-color:var(--sa-border);"
                               onmouseover="this.style.borderColor='var(--sa-primary)'"
                               onmouseout="this.style.borderColor='var(--sa-border)'">
                            <svg class="w-5 h-5" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs" style="color:var(--sa-muted-fg);" id="{{ $doc['name'] }}_label">JPG / PNG · max 5 Mo</span>
                        </label>
                        <input type="file" id="{{ $doc['name'] }}" name="{{ $doc['name'] }}" accept="image/jpeg,image/png,image/webp"
                               class="hidden"
                               onchange="document.getElementById('{{ $doc['name'] }}_label').textContent = this.files[0]?.name ?? 'JPG / PNG · max 5 Mo'">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ══ Statut initial ══ --}}
            <div class="rounded-2xl border p-5" style="border-color:var(--sa-border);background:var(--sa-card);">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="approve_now" value="1"
                           {{ old('approve_now') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border"
                           style="accent-color:var(--sa-primary);border-color:var(--sa-border);">
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--sa-fg);">Approuver directement</p>
                        <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Le livreur sera actif dès la création. Sinon, le dossier passera en attente de validation.</p>
                    </div>
                </label>
            </div>

            {{-- ══ Actions ══ --}}
            <div class="flex items-center justify-between gap-3 pt-2">
                <a href="{{ route('super-admin.drivers.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold border transition-colors"
                   style="border-color:var(--sa-border);color:var(--sa-muted-fg);background:var(--sa-card);"
                   onmouseover="this.style.color='var(--sa-fg)'" onmouseout="this.style.color='var(--sa-muted-fg)'">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                        style="background:var(--sa-primary);color:var(--sa-primary-fg);"
                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Créer le livreur
                </button>
            </div>

        </form>
    </div>
</x-layouts.admin-super>
