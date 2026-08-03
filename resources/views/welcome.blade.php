<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MenuPro - Commandez vos plats préférés en quelques clics</title>
    <meta name="description" content="Découvrez MenuPro, l'application qui révolutionne la commande de repas en Côte d'Ivoire. Livraison rapide, paiement mobile, suivi en temps réel.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .float-animation { animation: float 6s ease-in-out infinite; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .gradient-text {
            background: linear-gradient(135deg, #F97316 0%, #EF4444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .blob {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%);
            filter: blur(40px);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-lg border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4v8.82c0 4.52-3.13 8.75-8 9.82-4.87-1.07-8-5.3-8-9.82V8.18l8-4z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span class="text-2xl font-bold text-gray-900">Menu<span class="text-orange-600">Pro</span></span>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-orange-600 font-medium transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-orange-600 font-medium transition">
                                Connexion
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-orange-600 text-white px-6 py-2 rounded-full hover:bg-orange-700 font-medium transition">
                                    S'inscrire
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <!-- Background Blobs -->
        <div class="absolute top-20 left-10 w-96 h-96 blob opacity-60"></div>
        <div class="absolute bottom-20 right-10 w-80 h-80 blob opacity-60"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-center lg:text-left fade-in-up">
                    <div class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold mb-6">
                        🎉 Nouveau : Paiement mobile avec Jeko
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-gray-900 mb-6 leading-tight">
                        Vos plats préférés
                        <span class="gradient-text block">livrés en un clic</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-8 max-w-2xl">
                        Découvrez les meilleurs restaurants d'Abidjan. Commandez en ligne, payez avec Wave, Orange Money, MTN ou Moov. Livraison ultra-rapide 🚀
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="https://mpa-five.vercel.app/" target="_blank" class="inline-flex items-center justify-center px-8 py-4 bg-orange-600 text-white text-lg font-bold rounded-full hover:bg-orange-700 transform hover:scale-105 transition shadow-xl hover:shadow-2xl">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Ouvrir l'application
                        </a>

                        <a href="#features" class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 text-lg font-bold rounded-full hover:border-orange-600 hover:text-orange-600 transition">
                            En savoir plus
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-orange-600">50+</div>
                            <div class="text-sm text-gray-600">Restaurants</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-orange-600">10k+</div>
                            <div class="text-sm text-gray-600">Commandes</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-orange-600">< 30min</div>
                            <div class="text-sm text-gray-600">Livraison</div>
                        </div>
                    </div>
                </div>

                <!-- Right Image/Phone Mockup -->
                <div class="relative float-animation hidden lg:block">
                    <div class="relative mx-auto w-80">
                        <!-- Phone Frame -->
                        <div class="relative z-10 bg-gray-900 rounded-[3rem] p-3 shadow-2xl">
                            <div class="bg-white rounded-[2.5rem] overflow-hidden">
                                <!-- Status Bar -->
                                <div class="bg-gray-50 px-6 py-2 flex items-center justify-between text-xs">
                                    <span class="font-semibold">9:41</span>
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                                    </div>
                                </div>

                                <!-- App Screenshot Placeholder -->
                                <div class="aspect-[9/16] bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center relative overflow-hidden">
                                    <!-- Simulated App Content -->
                                    <div class="absolute inset-0 bg-white">
                                        <div class="p-6">
                                            <div class="bg-gray-200 h-8 w-48 rounded mb-4"></div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="bg-gray-100 rounded-2xl h-32"></div>
                                                <div class="bg-gray-100 rounded-2xl h-32"></div>
                                                <div class="bg-gray-100 rounded-2xl h-32"></div>
                                                <div class="bg-gray-100 rounded-2xl h-32"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-24 h-24 text-white opacity-90" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4v8.82c0 4.52-3.13 8.75-8 9.82-4.87-1.07-8-5.3-8-9.82V8.18l8-4z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Elements -->
                        <div class="absolute -top-6 -right-6 bg-green-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                            ✓ Disponible
                        </div>
                        <div class="absolute -bottom-6 -left-6 bg-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg">
                            🔥 -20% aujourd'hui
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">
                    Pourquoi choisir <span class="gradient-text">MenuPro</span> ?
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Une expérience de commande simplifiée avec les meilleures fonctionnalités
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-orange-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Livraison rapide</h3>
                    <p class="text-gray-600">
                        Recevez vos commandes en moins de 30 minutes. Suivi en temps réel avec géolocalisation du livreur.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Paiement mobile</h3>
                    <p class="text-gray-600">
                        Payez avec Wave, Orange Money, MTN ou Moov Money. Paiement sécurisé via Jeko.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Meilleurs restaurants</h3>
                    <p class="text-gray-600">
                        Plus de 50 restaurants partenaires à Abidjan. Cuisine locale, internationale, fast-food et plus.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Suivi temps réel</h3>
                    <p class="text-gray-600">
                        Suivez votre commande étape par étape : préparation, en route, livraison. Notifications push.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-yellow-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Promotions exclusives</h3>
                    <p class="text-gray-600">
                        Profitez de réductions jusqu'à -30% sur vos restaurants préférés. Offres spéciales chaque jour.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-3xl p-8 hover:shadow-xl transition transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Programme fidélité</h3>
                    <p class="text-gray-600">
                        Gagnez des points à chaque commande. Échangez-les contre des réductions ou plats gratuits.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">
                    Comment ça marche ?
                </h2>
                <p class="text-xl text-gray-600">Simple comme bonjour !</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-orange-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">1</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Choisissez</h3>
                    <p class="text-gray-600">Parcourez nos restaurants et sélectionnez vos plats préférés</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-orange-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">2</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Commandez</h3>
                    <p class="text-gray-600">Ajoutez au panier et validez votre commande en quelques clics</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-orange-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">3</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Payez</h3>
                    <p class="text-gray-600">Paiement sécurisé avec votre opérateur mobile favori</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-orange-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">4</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Dégustez</h3>
                    <p class="text-gray-600">Recevez votre commande chaude en moins de 30 minutes</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-orange-600 to-red-600 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8 relative z-10">
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6">
                Prêt à commander ?
            </h2>
            <p class="text-xl text-white/90 mb-10">
                Rejoignez des milliers de clients satisfaits et découvrez une nouvelle façon de commander vos repas
            </p>

            <a href="https://mpa-five.vercel.app/" target="_blank" class="inline-flex items-center px-10 py-5 bg-white text-orange-600 text-lg font-bold rounded-full hover:bg-gray-100 transform hover:scale-105 transition shadow-2xl">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Lancer MenuPro
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>

            <p class="mt-6 text-white/80 text-sm">
                Aucune installation requise • Fonctionne sur tous les appareils • 100% gratuit
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4v8.82c0 4.52-3.13 8.75-8 9.82-4.87-1.07-8-5.3-8-9.82V8.18l8-4z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="text-2xl font-bold text-white">Menu<span class="text-orange-600">Pro</span></span>
                    </div>
                    <p class="text-sm text-gray-400">
                        La meilleure plateforme de commande de repas en Côte d'Ivoire. Rapide, simple, délicieux.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Liens rapides</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://mpa-five.vercel.app/" class="hover:text-orange-600 transition">Commander</a></li>
                        <li><a href="#features" class="hover:text-orange-600 transition">Fonctionnalités</a></li>
                        @auth
                            <li><a href="{{ url('/dashboard') }}" class="hover:text-orange-600 transition">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-orange-600 transition">Connexion restaurants</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-orange-600 transition">Centre d'aide</a></li>
                        <li><a href="#" class="hover:text-orange-600 transition">Nous contacter</a></li>
                        <li><a href="#" class="hover:text-orange-600 transition">Devenir partenaire</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Suivez-nous</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-orange-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-orange-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-orange-600 rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} MenuPro. Tous droits réservés.</p>
                <p class="mt-2">
                    <a href="#" class="hover:text-orange-600 transition">Conditions d'utilisation</a> •
                    <a href="#" class="hover:text-orange-600 transition">Politique de confidentialité</a>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
