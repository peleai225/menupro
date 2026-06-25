<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine - {{ $restaurant->name }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { user-select: none; }
        .card-enter { animation: cardSlideIn 0.3s ease-out; }
        @keyframes cardSlideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="h-full bg-neutral-950 text-white overflow-hidden font-sans" x-data="kitchenDisplay()" x-init="init()">

    {{-- Header --}}
    <header class="h-14 px-4 flex items-center justify-between shrink-0 bg-primary-600">
        <div class="flex items-center gap-3">
            @if($restaurant->logo_url)
                <img src="{{ $restaurant->logo_url }}" alt="" class="w-8 h-8 rounded-full object-cover border border-white/20">
            @endif
            <h1 class="text-base font-bold">{{ $restaurant->name }}</h1>
            <span class="text-white/50 text-xs hidden sm:inline">| Cuisine</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 text-xs">
                <span class="bg-white/20 px-2 py-1 rounded font-bold" x-text="counts.new + ' en attente'"></span>
                <span class="bg-white/10 px-2 py-1 rounded" x-text="counts.preparing + ' en prep'"></span>
            </div>
            <button @click="toggleSound()" class="p-1.5 rounded hover:bg-white/10 transition" :class="soundEnabled ? 'text-white' : 'text-white/30'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path x-show="soundEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/>
                    <path x-show="!soundEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
            </button>
            <span class="text-white/40 text-xs font-mono hidden sm:inline" x-text="clock"></span>
        </div>
    </header>

    {{-- Column Layout: A gauche = Nouvelles | A droite = En preparation --}}
    <main class="h-[calc(100vh-56px)] flex">

        {{-- Colonne Gauche: Nouvelles commandes (a confirmer / a commencer) --}}
        <div class="flex-1 flex flex-col border-r border-neutral-800 min-w-0">
            <div class="h-10 flex items-center px-4 bg-primary-500/10 border-b border-neutral-800 shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-primary-500 mr-2"></span>
                <span class="text-sm font-bold text-primary-400">Nouvelles</span>
                <span class="ml-auto text-xs text-primary-400/70 font-mono" x-text="counts.new"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in newOrders" :key="order.id">
                    <div class="card-enter bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
                        {{-- Card top bar --}}
                        <div class="flex items-center justify-between px-4 py-2 border-b border-neutral-800/50"
                             :class="{
                                 'bg-primary-500/10': order.status === 'paid',
                                 'bg-warning-500/10': order.status === 'confirmed',
                             }">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-black text-white" x-text="'#' + order.reference"></span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded"
                                      :class="{
                                          'bg-primary-500 text-white': order.status === 'paid',
                                          'bg-warning-500 text-black': order.status === 'confirmed',
                                      }"
                                      x-text="order.status === 'paid' ? 'NOUVELLE' : 'CONFIRMEE'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-neutral-400">
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
                            {{-- Client + Type --}}
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-neutral-200" x-text="order.customer_name"></span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-neutral-500" x-text="order.type"></span>
                                    <template x-if="order.table_number">
                                        <span class="text-[10px] bg-neutral-800 text-white px-1.5 py-0.5 rounded font-bold" x-text="'T' + order.table_number"></span>
                                    </template>
                                </div>
                            </div>

                            {{-- Items list --}}
                            <div class="space-y-1.5 mb-3">
                                <template x-for="(item, idx) in order.items" :key="idx">
                                    <div class="flex items-start gap-2">
                                        <span class="text-sm font-black text-white min-w-[20px]" x-text="item.quantity + 'x'"></span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm text-neutral-100" x-text="item.name"></span>
                                            <template x-if="item.options && item.options.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    <template x-for="(opt, oi) in item.options" :key="oi">
                                                        <span class="text-[10px] bg-neutral-800 text-neutral-300 px-1.5 py-0.5 rounded" x-text="typeof opt === 'string' ? opt : (opt.name || '')"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="item.instructions">
                                                <p class="text-[11px] text-warning-500 mt-0.5" x-text="item.instructions"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Action --}}
                            <template x-if="order.status === 'paid'">
                                <button @click="updateOrder(order.id, 'confirm')"
                                        class="w-full py-3 rounded-lg bg-primary-500 hover:bg-primary-400 text-white font-black text-sm uppercase tracking-wide transition active:scale-[0.97]">
                                    Confirmer la commande
                                </button>
                            </template>
                            <template x-if="order.status === 'confirmed'">
                                <button @click="updateOrder(order.id, 'prepare')"
                                        class="w-full py-3 rounded-lg bg-warning-500 hover:bg-warning-600 text-black font-black text-sm uppercase tracking-wide transition active:scale-[0.97]">
                                    Commencer la preparation
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="newOrders.length === 0" class="flex items-center justify-center h-full text-neutral-600 text-center py-12">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-sm">Aucune nouvelle commande</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne Droite: En preparation --}}
        <div class="flex-1 flex flex-col min-w-0">
            <div class="h-10 flex items-center px-4 bg-info-500/10 border-b border-neutral-800 shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-info-500 mr-2"></span>
                <span class="text-sm font-bold text-info-500">En preparation</span>
                <span class="ml-auto text-xs text-info-500/70 font-mono" x-text="counts.preparing"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in preparingOrders" :key="order.id">
                    <div class="card-enter bg-neutral-900 rounded-xl border border-info-500/30 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2 bg-info-500/10 border-b border-neutral-800/50">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-black text-white" x-text="'#' + order.reference"></span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-info-500 text-white">EN COURS</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold"
                                      :class="{
                                          'text-error-500': order.minutes_ago > 20,
                                          'text-warning-500': order.minutes_ago > 12,
                                          'text-info-500': order.minutes_ago <= 12
                                      }"
                                      x-text="order.minutes_ago + 'min'"></span>
                            </div>
                        </div>

                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-neutral-200" x-text="order.customer_name"></span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-neutral-500" x-text="order.type"></span>
                                    <template x-if="order.table_number">
                                        <span class="text-[10px] bg-neutral-800 text-white px-1.5 py-0.5 rounded font-bold" x-text="'T' + order.table_number"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-1.5 mb-3">
                                <template x-for="(item, idx) in order.items" :key="idx">
                                    <div class="flex items-start gap-2">
                                        <span class="text-sm font-black text-white min-w-[20px]" x-text="item.quantity + 'x'"></span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm text-neutral-100" x-text="item.name"></span>
                                            <template x-if="item.options && item.options.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    <template x-for="(opt, oi) in item.options" :key="oi">
                                                        <span class="text-[10px] bg-neutral-800 text-neutral-300 px-1.5 py-0.5 rounded" x-text="typeof opt === 'string' ? opt : (opt.name || '')"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="item.instructions">
                                                <p class="text-[11px] text-warning-500 mt-0.5" x-text="item.instructions"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button @click="updateOrder(order.id, 'ready')"
                                    class="w-full py-3 rounded-lg bg-success-500 hover:bg-success-600 text-white font-black text-sm uppercase tracking-wide transition active:scale-[0.97]">
                                Pret — Servir !
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="preparingOrders.length === 0" class="flex items-center justify-center h-full text-neutral-600 text-center py-12">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm">Aucune preparation en cours</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- New Order Alert Overlay --}}
    <div x-show="showAlert" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         @click="showAlert = false"
         class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
        <div class="rounded-3xl p-10 text-center shadow-2xl bg-primary-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-2xl font-black text-white">Nouvelle commande !</p>
            <p class="text-sm mt-2 text-white/60">Touchez pour fermer</p>
        </div>
    </div>

    {{-- Onboarding Tutorial (first visit) --}}
    <div x-show="showTutorial" x-cloak
         class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-neutral-900 rounded-2xl max-w-md w-full p-6 text-center border border-neutral-800 shadow-2xl">
            <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center bg-primary-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <h2 class="text-xl font-black text-white mb-2">Bienvenue en cuisine !</h2>
            <p class="text-neutral-400 text-sm mb-6">Voici comment utiliser cet ecran :</p>

            <div class="text-left space-y-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-primary-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <p class="text-sm font-bold text-white">Colonne gauche = Nouvelles commandes</p>
                        <p class="text-xs text-neutral-400">Appuyez sur <span class="text-primary-400 font-bold">CONFIRMER</span> pour accepter, puis <span class="text-warning-500 font-bold">COMMENCER</span> quand vous la preparez</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-info-500 text-white text-xs font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <p class="text-sm font-bold text-white">Colonne droite = En preparation</p>
                        <p class="text-xs text-neutral-400">Quand le plat est pret, appuyez sur <span class="text-success-500 font-bold">PRET — SERVIR</span></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-neutral-700 text-white text-xs font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <p class="text-sm font-bold text-white">Un son retentit a chaque nouvelle commande</p>
                        <p class="text-xs text-neutral-400">L'ecran se met a jour automatiquement toutes les 5 secondes</p>
                    </div>
                </div>
            </div>

            <button @click="closeTutorial()" class="w-full py-3 rounded-xl text-white font-bold text-sm transition active:scale-[0.97] bg-primary-500 hover:bg-primary-600">
                J'ai compris, commencer !
            </button>
        </div>
    </div>

    <script>
    function kitchenDisplay() {
        return {
            orders: @json($ordersJson),
            counts: { new: 0, preparing: 0 },
            soundEnabled: true,
            showAlert: false,
            showTutorial: false,
            clock: '',
            token: '{{ $token }}',
            pollInterval: null,
            previousOrderIds: [],

            get newOrders() {
                return this.orders.filter(o => o.status === 'paid' || o.status === 'confirmed');
            },

            get preparingOrders() {
                return this.orders.filter(o => o.status === 'preparing');
            },

            init() {
                this.previousOrderIds = this.orders.map(o => o.id);
                this.updateCounts();
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
                this.pollInterval = setInterval(() => this.fetchOrders(), 8000);

                // Listen for real-time order events via Reverb
                if (window.Echo) {
                    window.Echo.channel('restaurant.{{ $restaurant->id }}.orders')
                        .listen('.order.created', () => this.fetchOrders())
                        .listen('.order.status_changed', () => this.fetchOrders());
                }

                // Show tutorial on first visit
                if (!localStorage.getItem('kitchen_tutorial_seen_{{ $token }}')) {
                    this.showTutorial = true;
                }
            },

            closeTutorial() {
                this.showTutorial = false;
                localStorage.setItem('kitchen_tutorial_seen_{{ $token }}', '1');
            },

            updateClock() {
                const now = new Date();
                this.clock = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },

            updateCounts() {
                this.counts.new = this.orders.filter(o => o.status === 'paid' || o.status === 'confirmed').length;
                this.counts.preparing = this.orders.filter(o => o.status === 'preparing').length;
            },

            async fetchOrders() {
                try {
                    const resp = await fetch(`/cuisine/${this.token}/data`);
                    if (!resp.ok) return;
                    const data = await resp.json();

                    const newIds = data.orders.map(o => o.id);
                    const brandNewOrders = newIds.filter(id => !this.previousOrderIds.includes(id));

                    if (brandNewOrders.length > 0) {
                        this.playSound();
                        this.showAlert = true;
                        setTimeout(() => this.showAlert = false, 3000);
                    }

                    this.orders = data.orders;
                    this.counts = data.counts;
                    this.previousOrderIds = newIds;
                } catch (e) {}
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

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
            },

            playSound() {
                if (!this.soundEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    [880, 1100, 1320].forEach((freq, i) => {
                        setTimeout(() => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.frequency.value = freq;
                            osc.type = 'sine';
                            gain.gain.value = 0.35;
                            osc.start();
                            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                            osc.stop(ctx.currentTime + 0.35);
                        }, i * 180);
                    });
                } catch (e) {}
            }
        };
    }
    </script>
</body>
</html>
