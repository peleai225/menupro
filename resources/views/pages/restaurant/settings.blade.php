<x-layouts.admin-restaurant title="Paramètres">
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-neutral-900">Paramètres</h1>
            <p class="text-neutral-500 mt-1">Configurez votre restaurant et votre profil.</p>
        </div>

        <!-- Tabs -->
        <div x-data="{ tab: 'restaurant' }" class="space-y-6">
            <div class="flex gap-2 border-b border-neutral-200">
                <button @click="tab = 'restaurant'" 
                        :class="tab === 'restaurant' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Restaurant
                </button>
                <button @click="tab = 'profile'" 
                        :class="tab === 'profile' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Profil
                </button>
                <button @click="tab = 'notifications'" 
                        :class="tab === 'notifications' ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                    Notifications
                </button>
            </div>

            <!-- Restaurant Settings -->
            <div x-show="tab === 'restaurant'" class="space-y-6">
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations du restaurant</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom du restaurant</label>
                            <input type="text" value="Le Délice" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Description</label>
                            <textarea rows="3" class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">Restaurant spécialisé dans la cuisine ivoirienne traditionnelle.</textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone</label>
                                <input type="tel" value="+225 07 00 00 00" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email</label>
                                <input type="email" value="contact@ledelice.ci" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse</label>
                            <input type="text" value="Cocody Angré, 8ème tranche" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Images</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Logo</label>
                            <div class="w-32 h-32 bg-neutral-100 rounded-2xl border-2 border-dashed border-neutral-300 flex items-center justify-center">
                                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Bannière</label>
                            <div class="w-full h-32 bg-neutral-100 rounded-2xl border-2 border-dashed border-neutral-300 flex items-center justify-center">
                                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </div>

            <!-- Profile Settings -->
            <div x-show="tab === 'profile'" x-cloak class="space-y-6">
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations personnelles</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom complet</label>
                            <input type="text" value="Koffi Adjoumani" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Email</label>
                            <input type="email" value="koffi@ledelice.ci" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone</label>
                            <input type="tel" value="+225 07 00 00 00" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Changer le mot de passe</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Mot de passe actuel</label>
                            <input type="password" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nouveau mot de passe</label>
                            <input type="password" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Confirmer le mot de passe</label>
                            <input type="password" class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="btn btn-primary">Enregistrer</button>
                </div>
            </div>

            <!-- Notifications Settings -->
            <div x-show="tab === 'notifications'" x-cloak class="space-y-6">
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Préférences de notification</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Nouvelles commandes</span>
                                <p class="text-sm text-neutral-500">Recevoir une notification pour chaque nouvelle commande</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Rappels d'abonnement</span>
                                <p class="text-sm text-neutral-500">Recevoir un rappel avant l'expiration de l'abonnement</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                        </label>
                        <label class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Emails marketing</span>
                                <p class="text-sm text-neutral-500">Recevoir des conseils et actualités MenuPro</p>
                            </div>
                            <input type="checkbox" class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

