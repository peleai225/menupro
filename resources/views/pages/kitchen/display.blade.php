<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine — {{ $restaurant->name }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { user-select: none; -webkit-user-select: none; }
        .card-enter { animation: cardSlideIn 0.3s ease-out; }
        @keyframes cardSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .pulse-ring {
            animation: pulseRing 1.8s ease-out infinite;
        }
        @keyframes pulseRing {
            0%   { transform: scale(0.8); opacity: 0.8; }
            70%  { transform: scale(1.4); opacity: 0; }
            100% { transform: scale(0.8); opacity: 0; }
        }
    </style>
</head>
<body class="h-full bg-neutral-950 text-white overflow-hidden font-sans"
      x-data="kitchenDisplay()"
      x-init="init()">

    {{-- ══ HEADER ══ --}}
    <header class="h-14 px-4 flex items-center justify-between shrink-0 bg-primary-600 shadow-lg">
        <div class="flex items-center gap-3">
            @if($restaurant->logo_url)
                <img src="{{ $restaurant->logo_url }}" alt="" class="w-8 h-8 rounded-full object-cover border border-white/20">
            @endif
            <h1 class="text-base font-bold truncate max-w-[140px] sm:max-w-none">{{ $restaurant->name }}</h1>
            <span class="text-white/50 text-xs hidden sm:inline">| Cuisine</span>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Compteurs --}}
            <div class="hidden sm:flex items-center gap-2 text-xs">
                <span class="bg-white/20 px-2 py-1 rounded-lg font-bold" x-text="counts.new + ' en attente'"></span>
                <span class="bg-white/10 px-2 py-1 rounded-lg" x-text="counts.preparing + ' en prep'"></span>
                <span x-show="counts.ready > 0"
                      class="bg-success-500/90 px-2 py-1 rounded-lg font-bold"
                      x-text="counts.ready + ' prêt' + (counts.ready > 1 ? 's' : '')"></span>
            </div>
            {{-- Indicateur réseau --}}
            <div class="relative flex items-center justify-center w-6 h-6" title="Connexion">
                <span class="absolute w-3 h-3 rounded-full"
                      :class="online ? 'bg-success-500' : 'bg-error-500'"></span>
                <span x-show="online" class="absolute w-3 h-3 rounded-full bg-success-500 pulse-ring"></span>
            </div>
            {{-- Synthèse vocale --}}
            <button @click="toggleVoice()"
                    class="p-1.5 rounded-lg hover:bg-white/10 transition"
                    :class="voiceEnabled ? 'text-white' : 'text-white/30'"
                    title="Synthèse vocale">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="voiceEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    <path x-show="!voiceEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/>
                </svg>
            </button>
            {{-- Son --}}
            <button @click="toggleSound()"
                    class="p-1.5 rounded-lg hover:bg-white/10 transition"
                    :class="soundEnabled ? 'text-white' : 'text-white/30'"
                    title="Sonnerie">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="soundEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/>
                    <path x-show="!soundEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
            </button>
            <span class="text-white/40 text-xs font-mono hidden sm:inline" x-text="clock"></span>
        </div>
    </header>

    {{-- ══ 3 COLONNES ══ --}}
    <main class="h-[calc(100vh-56px)] flex overflow-hidden">

        {{-- ── Colonne 1 : Nouvelles commandes ── --}}
        <div class="flex-1 flex flex-col border-r border-neutral-800 min-w-0">
            <div class="h-10 flex items-center px-4 bg-primary-500/10 border-b border-neutral-800 shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-primary-500 mr-2 shrink-0"></span>
                <span class="text-sm font-bold text-primary-400">Nouvelles</span>
                <span class="ml-auto text-xs text-primary-400/70 font-mono" x-text="counts.new"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in newOrders" :key="order.id">
                    <div class="card-enter bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
                        {{-- Top bar --}}
                        <div class="flex items-center justify-between px-4 py-2 border-b border-neutral-800/50"
                             :class="order.status === 'paid' ? 'bg-primary-500/10' : 'bg-warning-500/10'">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base font-black text-white shrink-0" x-text="'#' + order.reference"></span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded shrink-0"
                                      :class="order.status === 'paid' ? 'bg-primary-500 text-white' : 'bg-warning-500 text-black'"
                                      x-text="order.status === 'paid' ? 'NOUVELLE' : 'CONFIRMÉE'"></span>
                                <template x-if="order.table_number">
                                    <span class="text-[11px] bg-neutral-700 text-white px-2 py-0.5 rounded-full font-bold shrink-0"
                                          x-text="'Table ' + order.table_number"></span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-neutral-400 shrink-0">
                                <span x-text="order.created_at"></span>
                                <span class="font-bold"
                                      :class="{
                                          'text-error-500': order.minutes_ago > 15,
                                          'text-warning-500': order.minutes_ago > 8,
                                          'text-neutral-400': order.minutes_ago <= 8
                                      }"
                                      x-text="order.minutes_ago + 'min'"></span>
                            </div>
                        </div>

                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-3 gap-2">
                                <span class="text-sm font-semibold text-neutral-200 truncate" x-text="order.customer_name || 'Client'"></span>
                                <span class="text-xs text-neutral-500 shrink-0" x-text="order.type"></span>
                            </div>

                            <div class="space-y-1.5 mb-3">
                                <template x-for="(item, idx) in order.items" :key="idx">
                                    <div class="flex items-start gap-2">
                                        <span class="text-sm font-black text-white min-w-[22px] shrink-0" x-text="item.quantity + 'x'"></span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm text-neutral-100 font-medium" x-text="item.name"></span>
                                            <template x-if="item.options && item.options.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    <template x-for="(opt, oi) in item.options" :key="oi">
                                                        <span class="text-[10px] bg-neutral-800 text-neutral-300 px-1.5 py-0.5 rounded"
                                                              x-text="typeof opt === 'string' ? opt : (opt.name || '')"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="item.instructions">
                                                <p class="text-[11px] text-warning-400 font-medium mt-0.5 bg-warning-500/10 px-1.5 py-0.5 rounded"
                                                   x-text="'⚠ ' + item.instructions"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="order.status === 'paid'">
                                <button @click="updateOrder(order.id, 'confirm')"
                                        class="w-full py-3 rounded-xl bg-primary-500 hover:bg-primary-400 active:scale-[0.97] text-white font-black text-sm uppercase tracking-wide transition shadow-lg shadow-primary-500/20">
                                    ✓ Confirmer la commande
                                </button>
                            </template>
                            <template x-if="order.status === 'confirmed'">
                                <button @click="updateOrder(order.id, 'prepare')"
                                        class="w-full py-3 rounded-xl bg-warning-500 hover:bg-warning-400 active:scale-[0.97] text-black font-black text-sm uppercase tracking-wide transition shadow-lg shadow-warning-500/20">
                                    🍳 Commencer la préparation
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="newOrders.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-700 text-center">
                    <svg class="w-10 h-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm">Aucune nouvelle commande</p>
                </div>
            </div>
        </div>

        {{-- ── Colonne 2 : En préparation ── --}}
        <div class="flex-1 flex flex-col border-r border-neutral-800 min-w-0">
            <div class="h-10 flex items-center px-4 bg-info-500/10 border-b border-neutral-800 shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-info-500 mr-2 shrink-0"></span>
                <span class="text-sm font-bold text-info-500">En préparation</span>
                <span class="ml-auto text-xs text-info-500/70 font-mono" x-text="counts.preparing"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in preparingOrders" :key="order.id">
                    <div class="card-enter bg-neutral-900 rounded-xl border border-info-500/30 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2 bg-info-500/10 border-b border-neutral-800/50">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base font-black text-white shrink-0" x-text="'#' + order.reference"></span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-info-500 text-white shrink-0">EN COURS</span>
                                <template x-if="order.table_number">
                                    <span class="text-[11px] bg-neutral-700 text-white px-2 py-0.5 rounded-full font-bold shrink-0"
                                          x-text="'Table ' + order.table_number"></span>
                                </template>
                            </div>
                            <span class="font-bold text-xs shrink-0"
                                  :class="{
                                      'text-error-500': order.minutes_ago > 20,
                                      'text-warning-500': order.minutes_ago > 12,
                                      'text-info-500': order.minutes_ago <= 12
                                  }"
                                  x-text="order.minutes_ago + 'min'"></span>
                        </div>

                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-3 gap-2">
                                <span class="text-sm font-semibold text-neutral-200 truncate" x-text="order.customer_name || 'Client'"></span>
                                <span class="text-xs text-neutral-500 shrink-0" x-text="order.type"></span>
                            </div>

                            <div class="space-y-1.5 mb-3">
                                <template x-for="(item, idx) in order.items" :key="idx">
                                    <div class="flex items-start gap-2">
                                        <span class="text-sm font-black text-white min-w-[22px] shrink-0" x-text="item.quantity + 'x'"></span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm text-neutral-100 font-medium" x-text="item.name"></span>
                                            <template x-if="item.options && item.options.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    <template x-for="(opt, oi) in item.options" :key="oi">
                                                        <span class="text-[10px] bg-neutral-800 text-neutral-300 px-1.5 py-0.5 rounded"
                                                              x-text="typeof opt === 'string' ? opt : (opt.name || '')"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="item.instructions">
                                                <p class="text-[11px] text-warning-400 font-medium mt-0.5 bg-warning-500/10 px-1.5 py-0.5 rounded"
                                                   x-text="'⚠ ' + item.instructions"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button @click="updateOrder(order.id, 'ready')"
                                    class="w-full py-3 rounded-xl bg-success-500 hover:bg-success-400 active:scale-[0.97] text-white font-black text-sm uppercase tracking-wide transition shadow-lg shadow-success-500/20">
                                ✅ Prêt — Servir !
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="preparingOrders.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-700 text-center">
                    <svg class="w-10 h-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm">Aucune préparation en cours</p>
                </div>
            </div>
        </div>

        {{-- ── Colonne 3 : Prêtes — En attente de service ── --}}
        <div class="flex-1 flex flex-col min-w-0">
            <div class="h-10 flex items-center px-4 bg-success-500/10 border-b border-neutral-800 shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-success-500 mr-2 shrink-0"></span>
                <span class="text-sm font-bold text-success-400">Prêtes à servir</span>
                <span class="ml-auto text-xs text-success-400/70 font-mono" x-text="counts.ready"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in readyOrders" :key="order.id">
                    <div class="card-enter bg-neutral-900 rounded-xl border border-success-500/40 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-success-500/10 border-b border-neutral-800/50">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base font-black text-white shrink-0" x-text="'#' + order.reference"></span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-success-500 text-white shrink-0">PRÊT</span>
                                <template x-if="order.table_number">
                                    <span class="text-[11px] bg-neutral-700 text-white px-2 py-0.5 rounded-full font-bold shrink-0"
                                          x-text="'Table ' + order.table_number"></span>
                                </template>
                            </div>
                            <span class="text-[11px] text-success-400 font-mono shrink-0" x-text="order.ready_at || order.created_at"></span>
                        </div>

                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-2 gap-2">
                                <span class="text-sm font-semibold text-neutral-200 truncate" x-text="order.customer_name || 'Client'"></span>
                                <span class="text-xs text-neutral-500 shrink-0" x-text="order.type"></span>
                            </div>

                            <div class="space-y-1 mb-3 text-sm text-neutral-300">
                                <template x-for="(item, idx) in order.items" :key="idx">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-white shrink-0" x-text="item.quantity + 'x'"></span>
                                        <span class="truncate" x-text="item.name"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="text-center text-xs text-success-500 font-bold py-1 bg-success-500/10 rounded-lg">
                                En attente d'un serveur
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="readyOrders.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-700 text-center">
                    <svg class="w-10 h-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <p class="text-sm">Aucune commande prête</p>
                </div>
            </div>
        </div>
    </main>

    {{-- ══ OVERLAY Nouvelle commande ══ --}}
    <div x-show="showAlert" x-cloak
         @click="showAlert = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
        <div class="rounded-3xl p-10 text-center shadow-2xl bg-primary-500 max-w-sm w-full mx-4">
            <svg class="w-16 h-16 mx-auto mb-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-3xl font-black text-white mb-2" x-text="alertTitle">Nouvelle commande !</p>
            <p class="text-base text-white/80 font-medium" x-text="alertDetail"></p>
            <p class="text-sm mt-4 text-white/50">Touchez pour fermer</p>
        </div>
    </div>

    {{-- ══ TUTORIEL (première visite) ══ --}}
    <div x-show="showTutorial" x-cloak
         class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-neutral-900 rounded-2xl max-w-md w-full p-6 text-center border border-neutral-800 shadow-2xl">
            <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center bg-primary-500">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <h2 class="text-xl font-black text-white mb-2">Bienvenue en cuisine !</h2>
            <p class="text-neutral-400 text-sm mb-5">Voici comment utiliser cet écran :</p>

            <div class="text-left space-y-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-primary-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <p class="text-sm font-bold text-white">Nouvelles commandes (gauche)</p>
                        <p class="text-xs text-neutral-400">CONFIRMER → accepter, puis COMMENCER quand vous commencez à préparer</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-info-500 text-white text-xs font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <p class="text-sm font-bold text-white">En préparation (centre)</p>
                        <p class="text-xs text-neutral-400">Quand le plat est prêt → <span class="text-success-400 font-bold">PRÊT — SERVIR</span></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-success-500 text-white text-xs font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <p class="text-sm font-bold text-white">Prêtes à servir (droite)</p>
                        <p class="text-xs text-neutral-400">Les commandes prêtes restent visibles jusqu'à ce qu'un serveur les emporte</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-neutral-700 text-white text-xs font-bold flex items-center justify-center shrink-0">🔊</div>
                    <div>
                        <p class="text-sm font-bold text-white">Synthèse vocale activée</p>
                        <p class="text-xs text-neutral-400">À chaque commande : annonce vocale avec le numéro de table et les plats</p>
                    </div>
                </div>
            </div>

            <button @click="closeTutorial()"
                    class="w-full py-3 rounded-xl text-white font-bold text-sm transition active:scale-[0.97] bg-primary-500 hover:bg-primary-600">
                J'ai compris, commencer !
            </button>
        </div>
    </div>

    <script>
    function kitchenDisplay() {
        return {
            orders: @json($ordersJson),
            counts: { new: 0, preparing: 0, ready: 0 },
            soundEnabled: true,
            voiceEnabled: true,
            showAlert: false,
            alertTitle: 'Nouvelle commande !',
            alertDetail: '',
            showTutorial: false,
            clock: '',
            online: true,
            token: '{{ $token }}',
            pollInterval: null,
            knownIds: {},

            get newOrders() {
                return this.orders.filter(o => o.status === 'paid' || o.status === 'confirmed');
            },
            get preparingOrders() {
                return this.orders.filter(o => o.status === 'preparing');
            },
            get readyOrders() {
                return this.orders.filter(o => o.status === 'ready');
            },

            init() {
                // knownIds = dictionnaire des IDs connus au chargement initial
                // les nouvelles commandes arrivées APRÈS déclenchent l'alerte
                this.orders.forEach(o => { this.knownIds[o.id] = true; });
                this.updateCounts();
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
                // Polling toutes les 5s — fiable même si WebSocket indisponible
                this.pollInterval = setInterval(() => this.fetchOrders(), 5000);

                window.addEventListener('online',  () => { this.online = true;  });
                window.addEventListener('offline', () => { this.online = false; });
                this.online = navigator.onLine;

                // WakeLock — empêche l'écran de se mettre en veille sur tablette
                this.requestWakeLock();
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') this.requestWakeLock();
                });

                // Reverb temps réel — canal public kitchen.{token} (pas besoin d'auth session)
                if (window.Echo) {
                    window.Echo.channel('kitchen.{{ $token }}')
                        .listen('.order.created',        () => this.fetchOrders())
                        .listen('.order.status_changed', () => this.fetchOrders());
                }

                // Tutorial première visite
                if (!localStorage.getItem('kitchen_tutorial_seen_{{ $token }}')) {
                    this.showTutorial = true;
                }
            },

            closeTutorial() {
                this.showTutorial = false;
                localStorage.setItem('kitchen_tutorial_seen_{{ $token }}', '1');
            },

            async requestWakeLock() {
                try {
                    if ('wakeLock' in navigator) {
                        await navigator.wakeLock.request('screen');
                    }
                } catch (_) {}
            },

            updateClock() {
                const now = new Date();
                this.clock = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },

            updateCounts() {
                this.counts.new      = this.orders.filter(o => o.status === 'paid' || o.status === 'confirmed').length;
                this.counts.preparing = this.orders.filter(o => o.status === 'preparing').length;
                this.counts.ready    = this.orders.filter(o => o.status === 'ready').length;
            },

            async fetchOrders() {
                try {
                    const resp = await fetch(`/cuisine/${this.token}/data`);
                    if (!resp.ok) { this.online = false; return; }
                    this.online = true;

                    const data = await resp.json();

                    // Détecte les commandes vraiment nouvelles (pas dans knownIds)
                    const brandNew = data.orders.filter(o => !this.knownIds[o.id]);
                    if (brandNew.length > 0) {
                        brandNew.forEach(order => this.announceNewOrder(order));
                        brandNew.forEach(o => { this.knownIds[o.id] = true; });
                    }

                    this.orders = data.orders;
                    this.counts = data.counts;
                } catch (e) {
                    this.online = false;
                }
            },

            async updateOrder(orderId, action) {
                try {
                    const resp = await fetch(`/cuisine/${this.token}/orders/${orderId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ action }),
                    });
                    if (resp.ok) await this.fetchOrders();
                } catch (e) {}
            },

            // ── Annonce une nouvelle commande : son + synthèse vocale + overlay ──
            announceNewOrder(order) {
                // Overlay visuel
                this.alertTitle = 'Nouvelle commande !';
                const tableInfo = order.table_number ? `Table ${order.table_number}` : (order.type || '');
                const dishes = order.items.map(i => `${i.quantity} ${i.name}`).join(', ');
                this.alertDetail = [tableInfo, dishes].filter(Boolean).join(' — ');
                this.showAlert = true;
                setTimeout(() => { this.showAlert = false; }, 5000);

                // Son
                this.playSound();

                // Synthèse vocale
                this.speakOrder(order);
            },

            // ── Synthèse vocale complète ──
            speakOrder(order) {
                if (!this.voiceEnabled) return;
                if (!('speechSynthesis' in window)) return;

                window.speechSynthesis.cancel(); // annuler toute annonce en cours

                const ref = order.reference || order.id;
                const table = order.table_number ? `table ${order.table_number}` : '';
                const type = order.type ? order.type.toLowerCase() : '';
                const dishes = order.items.map(i => {
                    const qty = i.quantity > 1 ? `${i.quantity} ` : '';
                    return qty + i.name;
                }).join(', ');
                const instructions = order.items
                    .filter(i => i.instructions)
                    .map(i => `attention ${i.name} : ${i.instructions}`)
                    .join('. ');

                // Construction du texte d'annonce
                let text = `Nouvelle commande ! Numéro ${ref}.`;
                if (table) text += ` ${table}.`;
                else if (type) text += ` ${type}.`;
                text += ` ${dishes}.`;
                if (instructions) text += ` ${instructions}.`;

                const utter = new SpeechSynthesisUtterance(text);
                utter.lang = 'fr-FR';
                utter.rate = 0.9;
                utter.pitch = 1.0;
                utter.volume = 1.0;

                // Choisir une voix française si disponible
                const voices = window.speechSynthesis.getVoices();
                const frVoice = voices.find(v => v.lang.startsWith('fr'));
                if (frVoice) utter.voice = frVoice;

                // Léger délai après le son (son dure ~540ms)
                setTimeout(() => window.speechSynthesis.speak(utter), 600);
            },

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
            },

            toggleVoice() {
                this.voiceEnabled = !this.voiceEnabled;
                if (!this.voiceEnabled) window.speechSynthesis.cancel();
            },

            playSound() {
                if (!this.soundEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    // Triple bip ascendant + une note grave de confirmation
                    const sequence = [880, 1100, 1320, 880];
                    sequence.forEach((freq, i) => {
                        setTimeout(() => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.frequency.value = freq;
                            osc.type = i === 3 ? 'triangle' : 'sine';
                            gain.gain.setValueAtTime(i === 3 ? 0.25 : 0.38, ctx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (i === 3 ? 0.5 : 0.3));
                            osc.start(ctx.currentTime);
                            osc.stop(ctx.currentTime + (i === 3 ? 0.5 : 0.3));
                        }, i * 135);
                    });
                } catch (e) {}
            },
        };
    }

    // Charger les voix dès que possible (Chrome nécessite un appel préalable)
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        window.speechSynthesis.addEventListener('voiceschanged', () => {
            window.speechSynthesis.getVoices();
        });
    }
    </script>
</body>
</html>
