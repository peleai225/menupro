<x-layouts.admin-restaurant title="QR Code">
    @php
        $publicUrl = route('r.menu', ['slug' => $restaurant->slug]);
    @endphp

    <div class="max-w-5xl mx-auto" x-data="{
        size: 250,
        copied: false,
        numberOfTables: {{ $restaurant->number_of_tables ?? 0 }},
        fromTable: 1,
        toTable: {{ $restaurant->number_of_tables ?? 1 }},
        tablesConfigured: {{ $restaurant->number_of_tables ? 'true' : 'false' }},
        showTableConfig: {{ $restaurant->number_of_tables ? 'false' : 'true' }},
    }">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">QR Code de votre établissement</h1>
            <p class="text-neutral-500 mt-2">Téléchargez votre QR code unique ou générez des QR codes individuels pour chaque table.</p>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-8">
            {{-- ==================== LEFT COLUMN ==================== --}}
            <div class="space-y-6">
                <!-- QR Code Preview -->
                <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6 sm:p-8">
                    <div class="text-center">
                        <h2 class="text-lg font-semibold text-neutral-800 mb-6">QR Code général</h2>

                        <div class="inline-block bg-white p-6 rounded-2xl border-2 border-dashed border-neutral-200 mb-6">
                            <img
                                :src="'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data={{ urlencode($publicUrl) }}'"
                                alt="QR Code {{ $restaurant->name }}"
                                class="mx-auto"
                                :style="'width: ' + size + 'px; height: ' + size + 'px;'"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-neutral-700 mb-3">Taille du QR Code</label>
                            <div class="flex justify-center gap-2 flex-wrap">
                                <button @click="size = 150" :class="size === 150 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">Petit</button>
                                <button @click="size = 250" :class="size === 250 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">Moyen</button>
                                <button @click="size = 400" :class="size === 400 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">Grand</button>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a :href="'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&format=png&data={{ urlencode($publicUrl) }}'"
                               download="{{ Str::slug($restaurant->name) }}-qrcode.png"
                               class="btn btn-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PNG
                            </a>
                            <a :href="'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&format=svg&data={{ urlencode($publicUrl) }}'"
                               download="{{ Str::slug($restaurant->name) }}-qrcode.svg"
                               class="btn btn-outline">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                SVG
                            </a>
                        </div>
                    </div>
                </div>

                <!-- URL & Share -->
                <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
                    <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Lien de votre menu
                    </h3>
                    <div class="flex gap-2">
                        <input type="text" value="{{ $publicUrl }}" readonly class="input flex-1 bg-neutral-50 text-sm font-mono" id="public-url">
                        <button @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="btn btn-secondary whitespace-nowrap" :class="copied ? 'bg-secondary-500 text-white' : ''">
                            <span x-text="copied ? 'Copié !' : 'Copier'"></span>
                        </button>
                    </div>
                    <div class="flex items-center gap-4 mt-3">
                        <a href="{{ $publicUrl }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 text-sm">
                            Voir mon menu
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode('Consultez notre menu : ' . $publicUrl) }}" target="_blank" class="inline-flex items-center gap-1 text-green-600 hover:text-green-700 text-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.34 0-4.508-.768-6.258-2.066l-.438-.338-2.652.889.889-2.652-.338-.438A9.964 9.964 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                            Partager sur WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            {{-- ==================== RIGHT COLUMN ==================== --}}
            <div class="space-y-6">
                {{-- ===== QR CODES PAR TABLE ===== --}}
                <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl border-2 border-orange-200 p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-primary-500 text-white rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900">QR Codes par table</h3>
                            <p class="text-sm text-neutral-600">Un QR code unique pour chaque table</p>
                        </div>
                    </div>

                    {{-- Step 1: Configure number of tables --}}
                    <div class="mt-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span class="font-semibold text-neutral-800 text-sm">Nombre de tables</span>
                        </div>

                        <form method="POST" action="{{ route('restaurant.qrcode.update-tables') }}" class="flex items-end gap-3">
                            @csrf
                            <div class="flex-1">
                                <input type="number"
                                       name="number_of_tables"
                                       x-model="numberOfTables"
                                       min="1" max="200"
                                       placeholder="Ex: 15"
                                       class="input w-full text-center text-lg font-bold"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-primary whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Valider
                            </button>
                        </form>

                        @if($restaurant->number_of_tables)
                            <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                {{ $restaurant->number_of_tables }} table(s) configurée(s)
                            </p>
                        @endif
                    </div>

                    {{-- Step 2: Select range & download --}}
                    <div class="mt-6" x-show="numberOfTables > 0" x-transition>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span class="font-semibold text-neutral-800 text-sm">Télécharger le PDF</span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-orange-100">
                            {{-- Range selector --}}
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-sm text-neutral-600">De la table</span>
                                <input type="number" x-model="fromTable" min="1" :max="toTable" class="input w-16 text-center text-sm py-1.5">
                                <span class="text-sm text-neutral-600">à</span>
                                <input type="number" x-model="toTable" :min="fromTable" :max="numberOfTables || 200" class="input w-16 text-center text-sm py-1.5">
                                <button @click="fromTable = 1; toTable = numberOfTables || 1" class="text-xs text-primary-600 hover:text-primary-700 underline ml-1">Toutes</button>
                            </div>

                            {{-- Download button --}}
                            <a :href="'{{ route('restaurant.qrcode.download-tables') }}?from_table=' + fromTable + '&to_table=' + toTable"
                               class="btn btn-primary w-full justify-center text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span x-text="fromTable == 1 && toTable == numberOfTables ? 'Télécharger toutes les tables (PDF)' : 'Télécharger tables ' + fromTable + ' à ' + toTable + ' (PDF)'"></span>
                            </a>

                            <p class="text-xs text-neutral-500 mt-3 text-center">
                                Format A4 portrait — 8 QR codes par page, prêts à imprimer et découper
                            </p>
                        </div>
                    </div>

                    {{-- Step 3: Social Media Card --}}
                    <div class="mt-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                            </span>
                            <span class="font-semibold text-neutral-800 text-sm">Format réseaux sociaux</span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-blue-100">
                            <p class="text-sm text-neutral-600 mb-3">
                                Image optimisée pour <strong>Facebook</strong> et <strong>Instagram</strong> avec votre QR code, le nom de votre restaurant et un appel à l'action.
                            </p>

                            <a href="{{ route('restaurant.qrcode.download-social') }}"
                               class="btn bg-blue-600 hover:bg-blue-700 text-white w-full justify-center text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Télécharger carte Facebook (1200×630)
                            </a>

                            <p class="text-xs text-neutral-400 mt-2 text-center">
                                Format 1200×630px — Idéal pour posts Facebook, couverture et partage
                            </p>
                        </div>
                    </div>

                    {{-- Preview grid of tables --}}
                    @if($restaurant->number_of_tables)
                        <div class="mt-6">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-6 h-6 bg-neutral-700 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </span>
                                <span class="font-semibold text-neutral-800 text-sm">Aperçu des tables</span>
                            </div>
                            <div class="grid grid-cols-5 sm:grid-cols-8 gap-2">
                                @for($i = 1; $i <= min($restaurant->number_of_tables, 40); $i++)
                                    <a href="{{ route('restaurant.qrcode.preview-table', $i) }}"
                                       target="_blank"
                                       class="aspect-square bg-white border border-orange-200 rounded-lg flex flex-col items-center justify-center hover:bg-orange-50 hover:border-primary-400 transition-colors group cursor-pointer"
                                       title="Aperçu table {{ $i }}">
                                        <svg class="w-4 h-4 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                        <span class="text-xs font-bold text-neutral-700 group-hover:text-primary-600">{{ $i }}</span>
                                    </a>
                                @endfor
                                @if($restaurant->number_of_tables > 40)
                                    <div class="aspect-square bg-neutral-50 border border-neutral-200 rounded-lg flex items-center justify-center">
                                        <span class="text-xs text-neutral-500 font-medium">+{{ $restaurant->number_of_tables - 40 }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="text-xs text-neutral-500 mt-2">Cliquez sur une table pour voir l'aperçu du QR code</p>
                        </div>
                    @endif
                </div>

                {{-- Tips --}}
                <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
                    <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Conseils
                    </h3>
                    <ul class="space-y-3 text-sm text-neutral-700">
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-orange-100 text-primary-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">1</span>
                            <span><strong>Plastifiez</strong> les QR codes pour une durée de vie plus longue.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-orange-100 text-primary-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">2</span>
                            <span><strong>Numéro de table :</strong> Le serveur voit automatiquement de quelle table vient la commande.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-orange-100 text-primary-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">3</span>
                            <span><strong>Collez</strong> le QR code sur la table ou utilisez un support chevalet.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-orange-100 text-primary-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">4</span>
                            <span><strong>Vitrine :</strong> Utilisez le QR code général à l'entrée du restaurant.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin-restaurant>
