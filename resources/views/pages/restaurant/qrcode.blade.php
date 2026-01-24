<x-layouts.admin-restaurant title="QR Code">
    @php
        $publicUrl = route('r.menu', ['slug' => $restaurant->slug]);
    @endphp
    
    <div class="max-w-4xl mx-auto" x-data="{ size: 250, copied: false }">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">QR Code de votre établissement</h1>
            <p class="text-neutral-500 mt-2">Téléchargez et partagez votre QR code pour permettre à vos clients d'accéder à votre menu.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- QR Code Preview -->
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6 sm:p-8">
                <div class="text-center">
                    <h2 class="text-lg font-semibold text-neutral-800 mb-6">Aperçu du QR Code</h2>
                    
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
                            Télécharger PNG
                        </a>
                        <a :href="'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&format=svg&data={{ urlencode($publicUrl) }}'" 
                           download="{{ Str::slug($restaurant->name) }}-qrcode.svg"
                           class="btn btn-outline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Télécharger SVG
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info & URL -->
            <div class="space-y-6">
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
                    <a href="{{ $publicUrl }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 text-sm mt-3">
                        Voir mon menu en ligne
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div class="bg-gradient-to-br from-primary-50 to-orange-50 rounded-2xl border border-primary-100 p-6">
                    <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Conseils d'utilisation
                    </h3>
                    <ul class="space-y-3 text-sm text-neutral-700">
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">1</span>
                            <span><strong>Tables :</strong> Imprimez et plastifiez le QR code pour chaque table.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">2</span>
                            <span><strong>Vitrine :</strong> Affichez-le à l'entrée pour les passants.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">3</span>
                            <span><strong>Cartes de visite :</strong> Ajoutez le QR code sur vos cartes.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold">4</span>
                            <span><strong>Réseaux sociaux :</strong> Partagez sur WhatsApp, Facebook, Instagram.</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
                    <h3 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimer un modèle
                    </h3>
                    <p class="text-neutral-600 text-sm mb-4">Imprimez un modèle prêt à l'emploi avec votre QR code et le nom de votre établissement.</p>
                    <button onclick="printQRCode()" class="btn btn-outline w-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimer le modèle
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function printQRCode() {
            const w = window.open('', '', 'width=600,height=700');
            w.document.write(`
                <html>
                <head>
                    <title>QR Code - {{ $restaurant->name }}</title>
                    <style>
                        body { margin: 0; padding: 40px; font-family: system-ui, sans-serif; text-align: center; }
                        .container { max-width: 400px; margin: 0 auto; border: 2px solid #e5e7eb; border-radius: 16px; padding: 40px; }
                        h1 { font-size: 28px; font-weight: bold; margin: 0 0 8px; color: #1c1917; }
                        .subtitle { font-size: 16px; color: #78716c; margin: 0 0 30px; }
                        .url { font-size: 12px; color: #a8a29e; word-break: break-all; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>{{ $restaurant->name }}</h1>
                        <p class="subtitle">Scannez pour voir notre menu</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($publicUrl) }}" width="250" height="250" style="margin: 0 auto 30px; display: block;">
                        <p class="url">{{ $publicUrl }}</p>
                    </div>
                </body>
                </html>
            `);
            w.document.close();
            setTimeout(() => { w.print(); w.close(); }, 500);
        }
    </script>
    @endpush
</x-layouts.admin-restaurant>

