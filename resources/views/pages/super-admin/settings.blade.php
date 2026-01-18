<x-layouts.admin-super title="Paramètres">
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Paramètres système</h1>
            <p class="text-neutral-400 mt-1">Configuration générale de la plateforme MenuPro.</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-secondary-500/20 border border-secondary-500/30 rounded-xl text-secondary-400">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                <ul class="list-disc list-inside text-red-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabs -->
        <div x-data="{ tab: 'general' }" class="space-y-6">
            <div class="flex gap-2 border-b border-neutral-700">
                <button @click="tab = 'general'" 
                        :class="tab === 'general' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-300'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Général
                </button>
                <button @click="tab = 'payment'" 
                        :class="tab === 'payment' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-300'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Paiements
                </button>
                <button @click="tab = 'email'" 
                        :class="tab === 'email' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-300'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Emails
                </button>
                <button @click="tab = 'security'" 
                        :class="tab === 'security' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-300'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Sécurité
                </button>
                <button @click="tab = 'appearance'" 
                        :class="tab === 'appearance' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-500 hover:text-neutral-300'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Apparence
                </button>
            </div>

            <!-- General Settings -->
            <form method="POST" action="{{ route('super-admin.settings.update') }}" x-show="tab === 'general'" class="space-y-6">
                @csrf
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Informations plateforme</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Nom de la plateforme <span class="text-red-400">*</span></label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'MenuPro') }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MenuPro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">URL de base <span class="text-red-400">*</span></label>
                            <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url'] ?? 'http://127.0.0.1:8000') }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://menupro.ci">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Email de contact <span class="text-red-400">*</span></label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? 'contact@menupro.ci') }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="contact@menupro.ci">
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Options</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-neutral-700/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-white">Mode maintenance</span>
                                <p class="text-sm text-neutral-400">Désactiver temporairement l'accès public</p>
                            </div>
                            <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-700/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-white">Inscriptions ouvertes</span>
                                <p class="text-sm text-neutral-400">Autoriser les nouvelles inscriptions</p>
                            </div>
                            <input type="checkbox" name="registrations_open" value="1" {{ $settings['registrations_open'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
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
                
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Configuration Lygos</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">API Key</label>
                            <input type="password" name="lygos_api_key" value="{{ old('lygos_api_key', $settings['lygos_api_key'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="sk_live_...">
                            <p class="text-xs text-neutral-400 mt-1">Votre clé API Lygos pour les paiements d'abonnements</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Webhook Secret (optionnel)</label>
                            <input type="password" name="lygos_webhook_secret" value="{{ old('lygos_webhook_secret', $settings['lygos_webhook_secret'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="whsec_...">
                            <p class="text-xs text-neutral-400 mt-1">Secret pour vérifier les webhooks (optionnel)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Mode</label>
                            <select name="lygos_mode" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="test" {{ ($settings['lygos_mode'] ?? 'live') === 'test' ? 'selected' : '' }}>Test</option>
                                <option value="live" {{ ($settings['lygos_mode'] ?? 'live') === 'live' ? 'selected' : '' }}>Production</option>
                            </select>
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
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Configuration SMTP</h2>
                    <p class="text-sm text-neutral-400 mb-6">Configurez les paramètres SMTP pour l'envoi d'emails. Ces paramètres remplaceront ceux du fichier .env.</p>
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Serveur SMTP <span class="text-red-400">*</span></label>
                                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="smtp.gmail.com">
                                <p class="text-xs text-neutral-400 mt-1">Ex: smtp.gmail.com, smtp.mailgun.org</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Port <span class="text-red-400">*</span></label>
                                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? 587 }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="587">
                                <p class="text-xs text-neutral-400 mt-1">587 (TLS) ou 465 (SSL)</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Chiffrement</label>
                            <select name="smtp_encryption" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (recommandé)</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ empty($settings['smtp_encryption'] ?? '') ? 'selected' : '' }}>Aucun</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Nom d'utilisateur <span class="text-red-400">*</span></label>
                                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="votre-email@gmail.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Mot de passe <span class="text-red-400">*</span></label>
                                <input type="password" name="smtp_password" value="" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Laissez vide pour ne pas modifier">
                                <p class="text-xs text-neutral-400 mt-1">Laissez vide pour conserver le mot de passe actuel</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Email expéditeur <span class="text-red-400">*</span></label>
                                <input type="email" name="smtp_from_address" value="{{ $settings['smtp_from_address'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="noreply@menupro.ci">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Nom expéditeur <span class="text-red-400">*</span></label>
                                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? '' }}" required class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MenuPro">
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
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Sécurité</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-neutral-700/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-white">Double authentification obligatoire</span>
                                <p class="text-sm text-neutral-400">Forcer le 2FA pour les admins</p>
                            </div>
                            <input type="checkbox" name="require_2fa" value="1" {{ $settings['require_2fa'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-700/50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-white">Log des connexions</span>
                                <p class="text-sm text-neutral-400">Enregistrer toutes les connexions</p>
                            </div>
                            <input type="checkbox" name="log_logins" value="1" {{ $settings['log_logins'] ? 'checked' : '' }} class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
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
                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Logo et Favicon</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Logo</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['logo'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                                    <img src="{{ asset('storage/' . ltrim($settings['logo'], '/')) }}" alt="Logo" class="h-16 w-auto object-contain bg-white p-2 rounded-lg">
                                @endif
                                <input type="file" name="logo" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-400 mt-1">Format recommandé: PNG, SVG. Taille max: 2MB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Favicon</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['favicon'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['favicon']))
                                    <img src="{{ asset('storage/' . ltrim($settings['favicon'], '/')) }}" alt="Favicon" class="h-8 w-8 object-contain bg-white p-1 rounded">
                                @endif
                                <input type="file" name="favicon" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-400 mt-1">Format recommandé: ICO, PNG. Taille max: 512KB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Image Hero (Page d'accueil)</label>
                            <div class="flex items-center gap-4">
                                @if(!empty($settings['hero_image'] ?? ''))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['hero_image']) }}" alt="Image Hero" class="h-32 w-auto object-contain bg-white p-2 rounded-lg">
                                @endif
                                <input type="file" name="hero_image" accept="image/*" class="flex-1 h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <p class="text-xs text-neutral-400 mt-1">Image affichée à la place du mockup de téléphone. Format recommandé: PNG, JPG, WebP. Taille max: 5MB</p>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Réseaux sociaux</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Facebook</label>
                            <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://facebook.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Twitter</label>
                            <input type="url" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://twitter.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Instagram</label>
                            <input type="url" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://instagram.com/menupro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">LinkedIn</label>
                            <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://linkedin.com/company/menupro">
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Footer</h2>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Texte du footer</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '© ' . date('Y') . ' MenuPro. Tous droits réservés.') }}" class="w-full h-12 px-4 bg-neutral-700 border border-neutral-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="© 2026 MenuPro. Tous droits réservés.">
                        <p class="text-xs text-neutral-400 mt-1">Texte affiché en bas de page sur le site public</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin-super>

