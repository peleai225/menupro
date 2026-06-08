<x-layouts.public title="Supports QR Code" description="Commandez des QR codes physiques pour vos tables : supports rigides ou autocollants plastifiés. Livrés à votre restaurant.">

    <section class="relative py-16 sm:py-20 lg:py-24 bg-gradient-to-b from-white via-orange-50/30 to-white overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle,rgba(249,115,22,0.12)_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_80%)] opacity-50"></div>
        <div class="absolute top-10 right-10 w-64 h-64 bg-primary-200/40 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-72 h-72 bg-amber-200/40 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="qrSupportsPricing()">

            {{-- Section header --}}
            <div class="max-w-3xl mx-auto text-center mb-10 sm:mb-14">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-gradient-to-r from-primary-100 to-orange-100 text-primary-700 rounded-full text-xs font-bold uppercase tracking-wider mb-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/></svg>
                    Livré à votre restaurant
                </div>

                <h1 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight mb-5">
                    Vos QR codes,
                    <span class="relative inline-block">
                        <span class="text-gradient">imprimés et prêts à servir.</span>
                        <svg class="absolute -bottom-2 left-0 w-full h-2.5 text-primary-400" viewBox="0 0 200 8" preserveAspectRatio="none">
                            <path d="M0,4 Q50,0 100,4 T200,4" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h1>

                <p class="text-neutral-600 text-base sm:text-lg leading-relaxed">
                    On vous imprime des QR codes de qualité pour chaque table. Deux formats au choix : <strong class="text-neutral-900">support rigide</strong> posé sur la table, ou <strong class="text-neutral-900">autocollant</strong> collé directement.
                </p>
            </div>

            {{-- 2 Format cards --}}
            <div class="grid md:grid-cols-2 gap-5 sm:gap-6 mb-10 sm:mb-12">
                {{-- FORMAT 1 : Support rigide --}}
                <button type="button"
                        @click="format = 'support'"
                        :class="format === 'support' ? 'border-primary-500 bg-white shadow-2xl shadow-primary-200/40 ring-2 ring-primary-100' : 'border-neutral-200 bg-white/60 hover:border-primary-300 hover:bg-white'"
                        class="relative text-left border-2 rounded-3xl p-6 sm:p-7 transition-all duration-300 focus:outline-none">

                    <div class="flex items-start gap-4 mb-5">
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-primary-500 via-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 transform rotate-[-4deg]">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" viewBox="0 0 48 48" fill="none">
                                    <path d="M8 40 L24 8 L40 40 Z" stroke="currentColor" stroke-width="2.5" fill="white" fill-opacity="0.15" stroke-linejoin="round"/>
                                    <rect x="14" y="18" width="20" height="14" rx="1.5" fill="white"/>
                                    <rect x="16" y="20" width="3" height="3" fill="currentColor"/>
                                    <rect x="20" y="20" width="2" height="2" fill="currentColor"/>
                                    <rect x="29" y="20" width="3" height="3" fill="currentColor"/>
                                    <rect x="16" y="29" width="3" height="3" fill="currentColor"/>
                                    <rect x="29" y="29" width="3" height="3" fill="currentColor"/>
                                </svg>
                            </div>
                            <div x-show="format === 'support'" x-transition class="absolute -top-2 -right-2 w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center ring-4 ring-white shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-primary-600 mb-1">Format 1 &middot; Le plus populaire</div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold text-neutral-900 leading-tight mb-1">Support rigide sur table</h3>
                            <p class="text-sm text-neutral-600 leading-snug">Chevalet triangulaire, rigide, posé directement sur la table. Visible des deux côtés.</p>
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2 mb-4 pb-4 border-b border-neutral-100">
                        <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-primary-500 to-orange-500 bg-clip-text text-transparent">1 500</span>
                        <span class="text-sm font-semibold text-neutral-500">FCFA / unité</span>
                    </div>

                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>PVC rigide, anti-tâches et lavable</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Double face : client voit le QR des deux côtés</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Se déplace facilement (changement de table)</span>
                        </li>
                    </ul>
                </button>

                {{-- FORMAT 2 : Autocollant --}}
                <button type="button"
                        @click="format = 'sticker'"
                        :class="format === 'sticker' ? 'border-primary-500 bg-white shadow-2xl shadow-primary-200/40 ring-2 ring-primary-100' : 'border-neutral-200 bg-white/60 hover:border-primary-300 hover:bg-white'"
                        class="relative text-left border-2 rounded-3xl p-6 sm:p-7 transition-all duration-300 focus:outline-none">

                    <div class="flex items-start gap-4 mb-5">
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 transform rotate-[4deg] relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-6 h-6 bg-white/90 [clip-path:polygon(100%_0,100%_100%,0_0)] rounded-bl"></div>
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white relative" viewBox="0 0 48 48" fill="none">
                                    <rect x="10" y="10" width="28" height="28" rx="4" fill="white"/>
                                    <rect x="13" y="13" width="4" height="4" fill="currentColor"/>
                                    <rect x="31" y="13" width="4" height="4" fill="currentColor"/>
                                    <rect x="13" y="30" width="4" height="4" fill="currentColor"/>
                                    <rect x="31" y="31" width="4" height="3" fill="currentColor"/>
                                </svg>
                            </div>
                            <div x-show="format === 'sticker'" x-transition class="absolute -top-2 -right-2 w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center ring-4 ring-white shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Format 2 &middot; Économique</div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold text-neutral-900 leading-tight mb-1">Autocollant plastifié</h3>
                            <p class="text-sm text-neutral-600 leading-snug">Étiquette adhésive à coller directement sur la table, le menu ou la vitrine.</p>
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2 mb-4 pb-4 border-b border-neutral-100">
                        <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent">300</span>
                        <span class="text-sm font-semibold text-neutral-500">FCFA / unité</span>
                    </div>

                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Adhésif fort, se colle sans bulles</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Vernis protecteur, résiste à l'eau</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Idéal pour tables fixes, bar, vitrine</span>
                        </li>
                    </ul>
                </button>
            </div>

            {{-- Configurator + Price card --}}
            <div class="grid lg:grid-cols-5 gap-6 lg:gap-8 items-start">
                <div class="lg:col-span-3 bg-white rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/40 p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <h3 class="font-display text-lg sm:text-xl font-bold text-neutral-900">Configurez votre commande</h3>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Nombre de tables / emplacements</label>
                        <div class="flex items-stretch gap-3">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>
                            <div class="flex-1 relative">
                                <input type="number" min="1" max="999" x-model.number="quantity"
                                       class="w-full h-12 sm:h-14 text-center text-2xl sm:text-3xl font-black text-neutral-900 bg-neutral-50 border-2 border-neutral-200 rounded-xl focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-neutral-400 uppercase tracking-wide pointer-events-none hidden sm:block">unités</span>
                            </div>
                            <button type="button" @click="quantity = Math.min(999, quantity + 1)" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-primary-100 hover:bg-primary-200 flex items-center justify-center text-primary-600 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Packs populaires</label>
                        <div class="grid grid-cols-4 gap-2 sm:gap-3">
                            <template x-for="preset in [10, 20, 50, 100]" :key="preset">
                                <button type="button"
                                        @click="quantity = preset"
                                        :class="quantity === preset ? 'bg-primary-500 text-white border-primary-500 shadow-md shadow-primary-500/25' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:border-primary-300 hover:bg-white'"
                                        class="relative py-3 sm:py-4 px-2 border-2 rounded-xl font-bold transition-all focus:outline-none">
                                    <div class="text-lg sm:text-xl" x-text="preset"></div>
                                    <div class="text-[10px] font-medium opacity-80">tables</div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="relative bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 text-white rounded-3xl p-6 sm:p-8 shadow-2xl overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/20 rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-orange-500/10 rounded-full blur-2xl"></div>

                        <div class="relative">
                            <div class="text-[10px] font-bold uppercase tracking-[2px] text-primary-300 mb-2">Votre commande</div>

                            <div class="flex items-baseline gap-2 mb-1">
                                <span class="text-3xl sm:text-4xl font-black" x-text="quantity"></span>
                                <span class="text-sm text-neutral-400">&times;</span>
                                <span class="text-lg font-bold" x-text="format === 'support' ? '1 500 F' : '300 F'"></span>
                            </div>
                            <div class="text-xs text-neutral-400 mb-6" x-text="format === 'support' ? 'Supports rigides sur table' : 'Autocollants plastifiés'"></div>

                            <div class="border-t border-white/10 pt-5">
                                <div class="text-xs uppercase tracking-wider text-neutral-400 mb-1">Total à payer</div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-primary-300 to-orange-300 bg-clip-text text-transparent" x-text="formatPrice(total)"></span>
                                    <span class="text-sm font-medium text-neutral-300">FCFA</span>
                                </div>
                                <div class="text-xs text-neutral-400 mt-1" x-show="quantity >= 20">
                                    <span class="inline-flex items-center gap-1 text-emerald-300 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Livraison offerte à Abidjan
                                    </span>
                                </div>
                                <div class="text-xs text-neutral-400 mt-1" x-show="quantity < 20">
                                    Livraison : 2 000 FCFA (Abidjan) &middot; Offerte dès 20 unités
                                </div>
                            </div>

                            @php
                                $qrWhatsapp = \App\Models\SystemSetting::get('contact_whatsapp', \App\Models\SystemSetting::get('contact_phone', ''));
                            @endphp

                            <button type="button"
                                    @click="showOrderForm = true; $nextTick(() => document.getElementById('qr-order-form')?.scrollIntoView({behavior: 'smooth', block: 'center'}))"
                                    x-show="!showOrderForm"
                                    class="mt-6 w-full inline-flex items-center justify-center gap-2 py-3.5 px-5 bg-gradient-to-r from-primary-500 to-orange-500 hover:from-primary-600 hover:to-orange-600 rounded-xl font-bold text-sm shadow-lg shadow-primary-500/30 transition-all hover:scale-[1.02] active:scale-100 focus:outline-none focus:ring-4 focus:ring-primary-400/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Passer commande maintenant
                            </button>

                            @if($qrWhatsapp)
                            <a :href="'https://wa.me/{{ preg_replace('/[^0-9]/', '', $qrWhatsapp) }}?text=' + encodeURIComponent('Bonjour MenuPro, je souhaite commander ' + quantity + ' ' + (format === 'support' ? 'supports rigides QR code' : 'autocollants QR code') + ' (' + formatPrice(total) + ' FCFA). Merci de me contacter.')"
                               target="_blank"
                               x-show="!showOrderForm"
                               class="mt-3 w-full inline-flex items-center justify-center gap-2 py-3 px-5 bg-white/5 hover:bg-white/10 border border-white/15 hover:border-emerald-400/50 rounded-xl font-semibold text-xs text-white transition-all">
                                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                Ou via WhatsApp
                            </a>
                            @endif

                            <div class="mt-3 flex items-center justify-center gap-4 text-[11px] text-neutral-400" x-show="!showOrderForm">
                                <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Paiement à la livraison</span>
                                <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>5-7 jours ouvrés</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order form --}}
            <div id="qr-order-form" class="mt-8 sm:mt-10" x-show="showOrderForm" x-cloak
                 x-transition:enter="transition ease-out duration-400"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">

                @if(session('qr_success'))
                    <div class="mb-6 p-5 bg-emerald-50 border-2 border-emerald-200 rounded-2xl flex items-start gap-3">
                        <svg class="w-6 h-6 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <div>
                            <div class="font-bold text-emerald-900 mb-1">Commande envoyée !</div>
                            <div class="text-sm text-emerald-800">{{ session('qr_success') }}</div>
                        </div>
                    </div>
                @endif

                @if(session('qr_error'))
                    <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <div>
                            <div class="font-bold text-red-900 mb-1">Erreur</div>
                            <div class="text-sm text-red-800">{{ session('qr_error') }}</div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-2xl">
                        <div class="font-bold text-red-900 mb-2">Veuillez corriger les erreurs suivantes :</div>
                        <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white border-2 border-neutral-200 rounded-3xl shadow-xl p-6 sm:p-10">
                    <div class="flex items-start justify-between gap-4 mb-6 pb-6 border-b border-neutral-100">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[2px] text-primary-600 mb-2">Étape finale</div>
                            <h3 class="font-display text-2xl sm:text-3xl font-bold text-neutral-900">Vos informations de livraison</h3>
                            <p class="text-sm text-neutral-600 mt-2">Notre équipe vous rappelle sous 24h pour confirmer et organiser la livraison. Aucun paiement en ligne : vous réglez à la réception.</p>
                        </div>
                        <button type="button" @click="showOrderForm = false"
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors"
                                aria-label="Fermer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mb-6 p-4 bg-gradient-to-br from-primary-50 to-orange-50 border border-primary-200/50 rounded-2xl flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-primary-700 uppercase tracking-wide mb-1">Récapitulatif</div>
                            <div class="text-sm text-neutral-900">
                                <span class="font-bold" x-text="quantity"></span>
                                <span x-text="format === 'support' ? ' supports rigides' : ' autocollants'"></span>
                                &middot;
                                <span class="font-bold text-primary-700" x-text="formatPrice(total) + ' FCFA'"></span>
                                <span class="text-neutral-500" x-show="quantity >= 20">+ livraison offerte</span>
                                <span class="text-neutral-500" x-show="quantity < 20">+ 2 000 F livraison</span>
                            </div>
                        </div>
                        <button type="button" @click="showOrderForm = false"
                                class="hidden sm:inline-flex flex-shrink-0 text-xs font-semibold text-primary-600 hover:text-primary-700 underline">
                            Modifier
                        </button>
                    </div>

                    <form action="{{ route('qr-supports.order') }}" method="POST" class="space-y-5" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="format" :value="format">
                        <input type="hidden" name="quantity" :value="quantity">

                        <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label for="qr-name" class="block text-sm font-semibold text-neutral-800 mb-2">Nom complet <span class="text-red-500">*</span></label>
                                <input id="qr-name" name="name" type="text" required maxlength="100"
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Kouamé Yao"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                            <div>
                                <label for="qr-phone" class="block text-sm font-semibold text-neutral-800 mb-2">Téléphone <span class="text-red-500">*</span></label>
                                <input id="qr-phone" name="phone" type="tel" required maxlength="30"
                                       value="{{ old('phone') }}"
                                       placeholder="+225 07 00 00 00 00"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label for="qr-email" class="block text-sm font-semibold text-neutral-800 mb-2">Email <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                                <input id="qr-email" name="email" type="email" maxlength="255"
                                       value="{{ old('email') }}"
                                       placeholder="votre@email.com"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                            <div>
                                <label for="qr-city" class="block text-sm font-semibold text-neutral-800 mb-2">Ville <span class="text-red-500">*</span></label>
                                <input id="qr-city" name="city" type="text" required maxlength="100"
                                       value="{{ old('city', 'Abidjan') }}"
                                       placeholder="Ex: Abidjan"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="qr-address" class="block text-sm font-semibold text-neutral-800 mb-2">Adresse de livraison <span class="text-neutral-400 font-normal">(commune / quartier / point de repère)</span></label>
                            <input id="qr-address" name="address" type="text" maxlength="500"
                                   value="{{ old('address') }}"
                                   placeholder="Ex: Cocody, Riviera 2, près de la pharmacie..."
                                   class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                        </div>

                        <div>
                            <label for="qr-note" class="block text-sm font-semibold text-neutral-800 mb-2">Message / précisions <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                            <textarea id="qr-note" name="note" rows="3" maxlength="1000"
                                      placeholder="Nom de votre restaurant, instructions particulières..."
                                      class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all resize-none">{{ old('note') }}</textarea>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-3">
                            <button type="submit" :disabled="submitting"
                                    class="flex-1 inline-flex items-center justify-center gap-2 py-4 px-6 bg-gradient-to-r from-primary-500 to-orange-500 hover:from-primary-600 hover:to-orange-600 disabled:opacity-70 disabled:cursor-not-allowed rounded-xl font-bold text-white shadow-lg shadow-primary-500/25 transition-all hover:scale-[1.01] active:scale-100 focus:outline-none focus:ring-4 focus:ring-primary-400/40">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                <span x-text="submitting ? 'Envoi en cours...' : 'Confirmer ma commande'"></span>
                            </button>
                            <button type="button" @click="showOrderForm = false"
                                    class="sm:w-auto px-6 py-4 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl font-semibold transition-colors">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

    @push('scripts')
    <script>
        function qrSupportsPricing() {
            return {
                format: 'support',
                quantity: 20,
                prices: { support: 1500, sticker: 300 },
                showOrderForm: @json(session()->has('qr_success') || session()->has('qr_error') || ($errors->any() && old('quantity'))),
                submitting: false,
                get unitPrice() {
                    return this.prices[this.format];
                },
                get total() {
                    return this.unitPrice * Math.max(1, Math.min(999, Number(this.quantity) || 1));
                },
                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(price));
                },
                init() {
                    const oldFormat = @json(old('format'));
                    const oldQty = @json(old('quantity'));
                    if (oldFormat && ['support', 'sticker'].includes(oldFormat)) {
                        this.format = oldFormat;
                    }
                    if (oldQty) {
                        const n = parseInt(oldQty, 10);
                        if (!isNaN(n) && n > 0) this.quantity = Math.min(999, n);
                    }
                    if (this.showOrderForm) {
                        this.$nextTick(() => {
                            document.getElementById('qr-order-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.public>
