<x-layouts.admin-super title="Paramètres">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-neutral-900">Paramètres système</h1>
            <p class="text-neutral-500 mt-1">Configuration générale de la plateforme MenuPro.</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <ul class="list-disc list-inside text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabs -->
        <div x-data="{ tab: 'general' }" class="space-y-6">
            <div class="flex gap-1 overflow-x-auto border-b border-neutral-200 pb-px scrollbar-thin scrollbar-thumb-neutral-300">
                <button @click="tab = 'general'" 
                        :class="tab === 'general' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Général
                </button>
                <button @click="tab = 'payment'" 
                        :class="tab === 'payment' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Paiements
                </button>
                <button @click="tab = 'email'" 
                        :class="tab === 'email' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Emails
                </button>
                <button @click="tab = 'security'" 
                        :class="tab === 'security' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Sécurité
                </button>
                <button @click="tab = 'appearance'" 
                        :class="tab === 'appearance' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Apparence
                </button>
                <button @click="tab = 'commando'"
                        :class="tab === 'commando' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    Commando
                </button>
                <button @click="tab = 'whatsapp'"
                        :class="tab === 'whatsapp' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-3 sm:px-4 py-3 border-b-2 font-medium transition-colors whitespace-nowrap text-sm sm:text-base">
                    WhatsApp
                </button>
            </div>

            <!-- General Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'general'" class="space-y-6">
                @csrf
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations plateforme</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom de la plateforme <span class="text-red-600">*</span></label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'MenuPro') }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MenuPro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">URL de base <span class="text-red-600">*</span></label>
                            <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url'] ?? 'http://127.0.0.1:8000') }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://menupro.ci">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email de contact <span class="text-red-600">*</span></label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? 'contact@menupro.ci') }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="contact@menupro.ci">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone de contact</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+225 07 00 00 00 00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Réseaux sociaux</h2>
                    <p class="text-sm text-neutral-500 mb-5">Ces liens seront affichés sur la page Contact et dans le footer du site public.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
                                Facebook
                            </label>
                            <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://facebook.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                Instagram
                            </label>
                            <input type="url" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://instagram.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                                Twitter / X
                            </label>
                            <input type="url" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://twitter.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                LinkedIn
                            </label>
                            <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://linkedin.com/company/menupro">
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Options</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Mode maintenance</span>
                                <p class="text-sm text-neutral-500">Désactiver temporairement l'accès public</p>
                            </div>
                            <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Inscriptions ouvertes</span>
                                <p class="text-sm text-neutral-500">Autoriser les nouvelles inscriptions</p>
                            </div>
                            <input type="checkbox" name="registrations_open" value="1" {{ $settings['registrations_open'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <!-- Payment Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'payment'" x-cloak class="space-y-6">
                @csrf
                <!-- Hidden fields to preserve existing values -->
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? 'http://127.0.0.1:8000' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? 'contact@menupro.ci' }}">
                <input type="hidden" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">
                <input type="hidden" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}">
                <input type="hidden" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}">
                <input type="hidden" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}">
                <input type="hidden" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}">
                
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        Wave (Checkout + Payout)
                    </h2>
                    <p class="text-sm text-neutral-500 mb-5">
                        Configuration globale Wave pour les paiements clients (Checkout) et les retraits vers les restaurants (Payout API).
                        <a href="https://docs.wave.com/payout/#payout-api" target="_blank" class="text-primary-600 hover:underline">Documentation</a>
                    </p>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">API Key Wave</label>
                            <input type="password" name="wave_api_key" value="{{ old('wave_api_key', $settings['wave_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="wave_ci_prod_...">
                            <p class="text-xs text-neutral-500 mt-1">
                                Clé API Wave Business (portail développeur). Utilisée pour toutes les requêtes Checkout et Payout.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Signing Secret Wave</label>
                            <input type="password" name="wave_signing_secret" value="{{ old('wave_signing_secret', $settings['wave_signing_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="wave_ci_AKS_...">
                            <p class="text-xs text-neutral-500 mt-1">
                                Secret de signature HMAC pour les requêtes sortantes et la vérification des webhooks (header <code class="bg-neutral-200 px-1 rounded">Wave-Signature</code>).
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Commission MenuPro sur paiements Wave</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="wave_commission_rate" value="{{ old('wave_commission_rate', ($settings['wave_commission_rate'] ?? 0.02)) }}" min="0" max="1" step="0.001" class="w-32 h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <span class="text-sm text-neutral-500">0.02 = 2 %</span>
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">
                                Taux de commission prélevé par MenuPro sur chaque paiement Wave avant de créditer le wallet virtuel du restaurant.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        GeniusPay
                    </h2>
                    <p class="text-sm text-neutral-500 mb-5">Wave, Orange Money, MTN Money. Utilisé pour les <strong>abonnements</strong> et les <strong>commandes clients</strong> (quand Lygos n'est pas configuré par le restaurant). <a href="https://pay.genius.ci/docs/api" target="_blank" class="text-primary-600 hover:underline">Documentation</a></p>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">API Key</label>
                            <input type="password" name="geniuspay_api_key" value="{{ old('geniuspay_api_key', $settings['geniuspay_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="pk_sandbox_... ou pk_live_...">
                            <p class="text-xs text-neutral-500 mt-1">Clé publique GeniusPay pour les paiements d'abonnements</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">API Secret</label>
                            <input type="password" name="geniuspay_api_secret" value="{{ old('geniuspay_api_secret', $settings['geniuspay_api_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="sk_sandbox_... ou sk_live_...">
                            <p class="text-xs text-neutral-500 mt-1">Clé secrète GeniusPay</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Webhook Secret</label>
                            <input type="password" name="geniuspay_webhook_secret" value="{{ old('geniuspay_webhook_secret', $settings['geniuspay_webhook_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="whsec_sandbox_... ou whsec_live_...">
                            <p class="text-xs text-neutral-500 mt-1">URL webhook : <code class="bg-neutral-200 px-1 rounded">{{ url('/webhooks/geniuspay') }}</code></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Mode</label>
                            <select name="geniuspay_mode" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="sandbox" {{ ($settings['geniuspay_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (test)</option>
                                <option value="live" {{ ($settings['geniuspay_mode'] ?? 'sandbox') === 'live' ? 'selected' : '' }}>Production</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Lygos <span class="text-neutral-500 text-sm font-normal">(legacy - abonnements)</span>
                    </h2>
                    <div class="space-y-5">
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Activer Lygos</span>
                                <p class="text-sm text-neutral-500">Utiliser Lygos pour les abonnements (fallback si GeniusPay non configuré)</p>
                            </div>
                            <input type="checkbox" name="lygos_enabled" value="1" {{ ($settings['lygos_enabled'] ?? true) ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">API Key</label>
                            <input type="password" name="lygos_api_key" value="{{ old('lygos_api_key', $settings['lygos_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="sk_live_...">
                            <p class="text-xs text-neutral-500 mt-1">Clé API Lygos (par restaurant, pour les commandes)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Webhook Secret (optionnel)</label>
                            <input type="password" name="lygos_webhook_secret" value="{{ old('lygos_webhook_secret', $settings['lygos_webhook_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="whsec_...">
                            <p class="text-xs text-neutral-500 mt-1">Secret pour vérifier les webhooks (optionnel)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Mode</label>
                            <select name="lygos_mode" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="test" {{ ($settings['lygos_mode'] ?? 'live') === 'test' ? 'selected' : '' }}>Test</option>
                                <option value="live" {{ ($settings['lygos_mode'] ?? 'live') === 'live' ? 'selected' : '' }}>Production</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        MenuPro Hub (Paiement direct)
                    </h2>
                    <p class="text-sm text-neutral-500 mb-5">Paiement direct sur les comptes marchands Wave, Orange Money, MTN des restaurants. Le webhook reçoit les SMS de confirmation de paiement via la passerelle SMS.</p>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Webhook Secret</label>
                            <input type="password" name="menupo_hub_webhook_secret" value="{{ old('menupo_hub_webhook_secret', $settings['menupo_hub_webhook_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Secret pour signer les requêtes">
                            <p class="text-xs text-neutral-500 mt-1">Secret pour vérifier la signature des webhooks (header X-Webhook-Signature ou X-Signature). Si vide, la vérification est désactivée. Laisser vide pour conserver la valeur actuelle.</p>
                            <p class="text-xs text-neutral-500 mt-1">URL webhook : <code class="bg-neutral-200 px-1 rounded">{{ url('/webhooks/menupo-hub/verify-payment') }}</code></p>
                        </div>
                    </div>
                </div>


                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        FusionPay (MoneyFusion)
                    </h2>
                    <p class="text-sm text-neutral-500 mb-5">Paiements Mobile Money (Wave, Orange, MTN) et transferts vers les restaurants. <a href="https://docs.moneyfusion.net/fr" target="_blank" class="text-primary-600 hover:underline">Documentation</a></p>
                    <div class="space-y-5">
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Activer FusionPay</span>
                                <p class="text-sm text-neutral-500">Paiements clients et transferts vers les wallets restaurants</p>
                            </div>
                            <input type="checkbox" name="fusionpay_enabled" value="1" {{ ($settings['fusionpay_enabled'] ?? false) ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">URL API (pay-in)</label>
                            <input type="url" name="fusionpay_api_url" value="{{ old('fusionpay_api_url', $settings['fusionpay_api_url'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://... (depuis votre tableau de bord)">
                            <p class="text-xs text-neutral-500 mt-1">Lien API généré dans Money Fusion > API de paiement</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Clé privée (pay-out)</label>
                            <input type="password" name="fusionpay_private_key" value="{{ old('fusionpay_private_key', $settings['fusionpay_private_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Clé API pour les retraits">
                            <p class="text-xs text-neutral-500 mt-1">Clé API pour l'API de retrait (transferts vers restaurants)</p>
                            <p class="text-xs text-neutral-500 mt-1">Webhooks : <code class="bg-neutral-200 px-1 rounded">{{ url('/webhooks/fusionpay/payment') }}</code> et <code class="bg-neutral-200 px-1 rounded">{{ url('/webhooks/fusionpay/payout') }}</code></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6 xl:col-span-2">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        Geoapify (Géocodage)
                    </h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">API Key Geoapify</label>
                            <input type="text" name="geoapify_api_key" value="{{ old('geoapify_api_key', $settings['geoapify_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Votre clé API Geoapify">
                            <p class="text-xs text-neutral-500 mt-1">
                                Clé API pour l'autocomplétion d'adresses. 
                                <a href="https://www.geoapify.com/get-started-with-maps-api" target="_blank" class="text-primary-600 hover:underline">Obtenir une clé gratuite (3000 requêtes/jour)</a>
                            </p>
                            <p class="text-xs text-neutral-500 mt-1">Si non configuré, le système utilisera Photon (gratuit, sans clé API) comme alternative.</p>
                        </div>
                    </div>
                </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <!-- Email Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'email'" x-cloak class="space-y-6">
                @csrf
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Configuration SMTP</h2>
                    <p class="text-sm text-neutral-500 mb-6">Configurez les paramètres SMTP pour l'envoi d'emails. Ces paramètres remplaceront ceux du fichier .env.</p>
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Serveur SMTP <span class="text-red-600">*</span></label>
                                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="smtp.gmail.com">
                                <p class="text-xs text-neutral-500 mt-1">Ex: smtp.gmail.com, smtp.mailgun.org</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Port <span class="text-red-600">*</span></label>
                                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? 587 }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="587">
                                <p class="text-xs text-neutral-500 mt-1">587 (TLS) ou 465 (SSL)</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Chiffrement</label>
                            <select name="smtp_encryption" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (recommandé)</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ empty($settings['smtp_encryption'] ?? '') ? 'selected' : '' }}>Aucun</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nom d'utilisateur <span class="text-red-600">*</span></label>
                                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="votre-email@gmail.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Mot de passe <span class="text-red-600">*</span></label>
                                <input type="password" name="smtp_password" value="" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Laissez vide pour ne pas modifier">
                                <p class="text-xs text-neutral-500 mt-1">Laissez vide pour conserver le mot de passe actuel</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email expéditeur <span class="text-red-600">*</span></label>
                                <input type="email" name="smtp_from_address" value="{{ $settings['smtp_from_address'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="noreply@menupro.ci">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nom expéditeur <span class="text-red-600">*</span></label>
                                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MenuPro">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="testEmail()" class="btn btn-ghost">Tester l'envoi</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <script>
                function testEmail() {
                    if (confirm('Voulez-vous envoyer un email de test à votre adresse ?')) {
                        // TODO: Implémenter l'envoi d'email de test
                        alert('Fonctionnalité de test à venir. Enregistrez d\'abord la configuration.');
                    }
                }
            </script>

            <!-- Security Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'security'" x-cloak class="space-y-6">
                @csrf
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Sécurité</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Double authentification obligatoire</span>
                                <p class="text-sm text-neutral-500">Forcer le 2FA pour les admins</p>
                            </div>
                            <input type="checkbox" name="require_2fa" value="1" {{ $settings['require_2fa'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Log des connexions</span>
                                <p class="text-sm text-neutral-500">Enregistrer toutes les connexions</p>
                            </div>
                            <input type="checkbox" name="log_logins" value="1" {{ $settings['log_logins'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <!-- Appearance Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" enctype="multipart/form-data" x-show="tab === 'appearance'" x-cloak class="space-y-6">
                @csrf
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Logo et Favicon</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Logo</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['logo'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                                    <img src="{{ asset('storage/' . ltrim($settings['logo'], '/')) }}" alt="Logo" class="h-16 w-auto object-contain bg-white p-2 rounded-lg">
                                @endif
                                <input type="file" name="logo" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-neutral-900 hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">Format recommandé: PNG, SVG. Taille max: 2MB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Favicon</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['favicon'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['favicon']))
                                    <img src="{{ asset('storage/' . ltrim($settings['favicon'], '/')) }}" alt="Favicon" class="h-8 w-8 object-contain bg-white p-1 rounded">
                                @endif
                                <input type="file" name="favicon" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-neutral-900 hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">Format recommandé: ICO, PNG. Taille max: 512KB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Image Hero (Page d'accueil)</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['hero_image'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['hero_image']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['hero_image']) }}" alt="Image Hero" class="h-32 w-auto object-contain bg-white p-2 rounded-lg">
                                @endif
                                <input type="file" name="hero_image" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-neutral-900 hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">Image affichée à la place du mockup de téléphone. Format recommandé: PNG, JPG, WebP. Taille max: 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Hidden fields to preserve social network values from General tab -->
                <input type="hidden" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}">
                <input type="hidden" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}">
                <input type="hidden" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}">
                <input type="hidden" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}">
                <input type="hidden" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Footer</h2>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Texte du footer</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '© ' . date('Y') . ' MenuPro. Tous droits réservés.') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="© 2026 MenuPro. Tous droits réservés.">
                        <p class="text-xs text-neutral-500 mt-1">Texte affiché en bas de page sur le site public</p>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Vidéos page d'accueil</h2>
                    <p class="text-sm text-neutral-500 mb-5">Vidéos tutoriels affichées sur la page d'accueil pour expliquer MenuPro. Collez le lien YouTube (ex: https://youtube.com/watch?v=xxx ou https://youtu.be/xxx). Laisser l'URL vide pour ne pas afficher une vidéo.</p>
                    @php
                        $homeVideos = $settings['home_videos'] ?? [];
                        $maxSlots = 5;
                        $videoSlots = array_pad($homeVideos, $maxSlots, ['title' => '', 'url' => '', 'description' => '']);
                    @endphp
                    <div class="space-y-6">
                        @foreach($videoSlots as $i => $video)
                        <div class="p-4 bg-neutral-100/50 rounded-xl border border-neutral-300">
                            <h3 class="text-sm font-medium text-neutral-700 mb-4">Vidéo {{ $i + 1 }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-neutral-500 mb-1">Titre</label>
                                    <input type="text" name="home_videos[{{ $i }}][title]" value="{{ old("home_videos.{$i}.title", $video['title'] ?? '') }}" class="w-full h-10 px-3 bg-neutral-100 border border-neutral-300 rounded-lg text-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Comment fonctionne MenuPro ?">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-neutral-500 mb-1">Lien de la vidéo <span class="text-primary-600">*</span></label>
                                    <input type="url" name="home_videos[{{ $i }}][url]" value="{{ old("home_videos.{$i}.url", $video['url'] ?? '') }}" class="w-full h-10 px-3 bg-neutral-100 border border-neutral-300 rounded-lg text-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://www.youtube.com/watch?v=xxx ou https://youtu.be/xxx">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-neutral-500 mb-1">Description (optionnel)</label>
                                    <textarea name="home_videos[{{ $i }}][description]" rows="2" class="w-full px-3 py-2 bg-neutral-100 border border-neutral-300 rounded-lg text-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Courte description de la vidéo">{{ old("home_videos.{$i}.description", $video['description'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <!-- Commando - Commissions -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'commando'" x-cloak class="space-y-6">
                @csrf
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? 'http://127.0.0.1:8000' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? 'contact@menupro.ci' }}">
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Commissions agents Commando</h2>
                    <p class="text-sm text-neutral-500 mb-5">Montant crédité à l'agent quand un restaurant inscrit via son lien de parrainage paie son premier abonnement.</p>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Commission au premier paiement (FCFA)</label>
                            <input type="number" name="commando_commission_fcfa_first_payment" value="{{ old('commando_commission_fcfa_first_payment', ($settings['commando_commission_cents_first_payment'] ?? 500000) / 100) }}" min="0" step="1" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="5000">
                            <p class="text-xs text-neutral-500 mt-1">Montant crédité à l'agent quand un restaurant parrainé paie son 1er abonnement.</p>
                        </div>
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Commission uniquement au 1er paiement</span>
                                <p class="text-sm text-neutral-500">Si coché, l'agent ne reçoit qu'une seule commission par restaurant parrainé (au premier abonnement payé). Sinon, une commission à chaque paiement.</p>
                            </div>
                            <input type="checkbox" name="commando_commission_only_first_payment" value="1" {{ ($settings['commando_commission_only_first_payment'] ?? true) ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
            <!-- WhatsApp Business API -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'whatsapp'" x-cloak class="space-y-6">
                @csrf
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? 'http://127.0.0.1:8000' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? 'contact@menupro.ci' }}">
                <div class="bg-white border border-neutral-200 shadow-sm rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-1">WhatsApp Business API</h2>
                    <p class="text-sm text-neutral-500 mb-5">Notifications automatiques aux clients et restaurateurs via WhatsApp (confirmation de commande, changement de statut, etc.).</p>

                    <div class="space-y-5">
                        <!-- Activer WhatsApp -->
                        <label class="flex items-center justify-between p-4 bg-neutral-100/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Activer les notifications WhatsApp</span>
                                <p class="text-sm text-neutral-500">Les messages seront envoyés automatiquement lors des changements de statut de commande.</p>
                            </div>
                            <input type="checkbox" name="whatsapp_enabled" value="1" {{ ($settings['whatsapp_enabled'] ?? false) ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-200">
                        </label>

                        <!-- Phone Number ID -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Phone Number ID</label>
                            <input type="text" name="whatsapp_phone_id" value="{{ old('whatsapp_phone_id', $settings['whatsapp_phone_id'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="123456789012345">
                            <p class="text-xs text-neutral-500 mt-1">L'identifiant du numéro de téléphone dans votre compte Meta Business. Se trouve dans <strong>Meta Business Suite &gt; WhatsApp &gt; Paramètres du compte</strong>.</p>
                        </div>

                        <!-- Access Token -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Token d'accès permanent</label>
                            <input type="password" name="whatsapp_api_key" value="{{ old('whatsapp_api_key', $settings['whatsapp_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-100 border border-neutral-300 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="EAAxxxxxxx...">
                            <p class="text-xs text-neutral-500 mt-1">Token permanent généré dans <strong>Meta for Developers &gt; Votre App &gt; WhatsApp &gt; Configuration</strong>. Ne partagez jamais ce token.</p>
                        </div>
                    </div>
                </div>

                <!-- Guide de configuration -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                    <h3 class="font-semibold text-amber-900 mb-3">Comment configurer WhatsApp Business API ?</h3>
                    <ol class="text-sm text-amber-800 space-y-2 list-decimal list-inside">
                        <li>Créez une app sur <strong>developers.facebook.com</strong> (type : Business)</li>
                        <li>Ajoutez le produit <strong>WhatsApp</strong> à votre app</li>
                        <li>Dans <strong>WhatsApp &gt; Configuration</strong>, copiez le <strong>Phone Number ID</strong></li>
                        <li>Générez un <strong>token d'accès permanent</strong> (System User dans Business Settings)</li>
                        <li>Collez les valeurs ci-dessus et activez les notifications</li>
                    </ol>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin-super>

