<x-layouts.admin-restaurant title="Gestion du Stock">
    <div class="max-w-2xl mx-auto py-12">
        <div class="card p-8 text-center">
            <!-- Icon -->
            <div class="w-20 h-20 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-neutral-900 mb-4">Gestion du Stock</h1>
            
            <!-- Description -->
            <p class="text-neutral-600 mb-8 max-w-md mx-auto">
                La gestion du stock est une fonctionnalité avancée disponible avec nos plans Pro et Premium. 
                Suivez vos ingrédients, gérez vos fournisseurs et optimisez votre inventaire.
            </p>

            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-left">
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-xl">
                    <div class="w-8 h-8 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">Suivi des ingrédients</p>
                        <p class="text-sm text-neutral-500">Gérez tous vos ingrédients et leurs quantités</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-xl">
                    <div class="w-8 h-8 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">Alertes de stock</p>
                        <p class="text-sm text-neutral-500">Notifications quand le stock est bas</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-xl">
                    <div class="w-8 h-8 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">Gestion fournisseurs</p>
                        <p class="text-sm text-neutral-500">Gardez trace de vos fournisseurs</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-xl">
                    <div class="w-8 h-8 bg-secondary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">Historique mouvements</p>
                        <p class="text-sm text-neutral-500">Traçabilité complète des entrées/sorties</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <a href="{{ route('restaurant.subscription') }}" class="btn btn-primary inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Passer à un plan supérieur
            </a>
        </div>
    </div>
</x-layouts.admin-restaurant>

