<x-layouts.public title="Contact">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 py-20 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 pattern-dots opacity-30"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500/10 border border-primary-500/20 rounded-full text-primary-400 text-sm font-medium mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Nous sommes là pour vous
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                    Contactez-nous
                </h1>
                <p class="text-xl text-neutral-300">
                    Une question ? Un projet ? Notre équipe vous répond sous 24h.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 lg:py-24 bg-neutral-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-12">
                <!-- Contact Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-neutral-900 mb-6">Informations</h2>
                        <p class="text-neutral-600">
                            Notre équipe est disponible du lundi au vendredi, de 9h à 18h pour répondre à toutes vos questions.
                        </p>
                    </div>

                    <!-- Contact Cards -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-900">Email</h3>
                                    <a href="mailto:{{ \App\Models\SystemSetting::get('contact_email', 'contact@menupro.com') }}" class="text-primary-600 hover:text-primary-700">
                                        {{ \App\Models\SystemSetting::get('contact_email', 'contact@menupro.com') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-900">Téléphone</h3>
                                    <a href="tel:{{ \App\Models\SystemSetting::get('contact_phone', '+225 07 00 00 00 00') }}" class="text-neutral-600 hover:text-primary-600">
                                        {{ \App\Models\SystemSetting::get('contact_phone', '+225 07 00 00 00 00') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-900">Horaires</h3>
                                    <p class="text-neutral-600">Lun - Ven : 9h - 18h</p>
                                    <p class="text-neutral-500 text-sm">Réponse sous 24h</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    @php
                        $socialFacebook = \App\Models\SystemSetting::get('social_facebook', '');
                        $socialTwitter = \App\Models\SystemSetting::get('social_twitter', '');
                        $socialInstagram = \App\Models\SystemSetting::get('social_instagram', '');
                        $socialLinkedin = \App\Models\SystemSetting::get('social_linkedin', '');
                    @endphp
                    @if($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin)
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-4">Suivez-nous</h3>
                            <div class="flex items-center gap-3">
                                @if($socialFacebook)
                                    <a href="{{ $socialFacebook }}" target="_blank" class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center text-neutral-600 hover:bg-primary-500 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
                                    </a>
                                @endif
                                @if($socialTwitter)
                                    <a href="{{ $socialTwitter }}" target="_blank" class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center text-neutral-600 hover:bg-primary-500 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                                    </a>
                                @endif
                                @if($socialInstagram)
                                    <a href="{{ $socialInstagram }}" target="_blank" class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center text-neutral-600 hover:bg-primary-500 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                @endif
                                @if($socialLinkedin)
                                    <a href="{{ $socialLinkedin }}" target="_blank" class="w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center text-neutral-600 hover:bg-primary-500 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8">
                        <h2 class="text-2xl font-bold text-neutral-900 mb-2">Envoyez-nous un message</h2>
                        <p class="text-neutral-600 mb-8">Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.</p>

                        @if(session('success'))
                            <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-800 flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 flex items-center gap-3">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                            @csrf

                            <!-- Type de demande -->
                            <div>
                                <label class="label">Type de demande</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach([
                                        'general' => ['label' => 'Question', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'support' => ['label' => 'Support', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
                                        'partnership' => ['label' => 'Partenariat', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                                        'demo' => ['label' => 'Démo', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z']
                                    ] as $value => $type)
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" {{ old('type', 'general') === $value ? 'checked' : '' }}>
                                            <div class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-neutral-200 peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-neutral-300 transition-colors">
                                                <svg class="w-6 h-6 text-neutral-400 peer-checked:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $type['icon'] }}"/>
                                                </svg>
                                                <span class="text-sm font-medium text-neutral-700">{{ $type['label'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nom et Email -->
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="label">Nom complet</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                                           class="input @error('name') input-error @enderror" 
                                           placeholder="Jean Dupont">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="label">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                                           class="input @error('email') input-error @enderror" 
                                           placeholder="jean@exemple.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sujet -->
                            <div>
                                <label for="subject" class="label">Sujet</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" 
                                       class="input @error('subject') input-error @enderror" 
                                       placeholder="Comment puis-je vous aider ?">
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message" class="label">Message</label>
                                <textarea id="message" name="message" rows="5" 
                                          class="input @error('message') input-error @enderror resize-none" 
                                          placeholder="Décrivez votre demande en détail...">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm text-neutral-500">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Vos données sont protégées
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Prêt à digitaliser votre restaurant ?</h2>
            <p class="text-xl text-primary-100 mb-8">Créez votre compte gratuitement et commencez à recevoir des commandes en ligne.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="btn bg-white text-primary-600 hover:bg-neutral-100">
                    Créer mon restaurant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('pricing') }}" class="btn btn-outline border-white text-white hover:bg-white/10">
                    Voir les tarifs
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
