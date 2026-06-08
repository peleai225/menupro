<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Livreur - {{ $driver->restaurant->name }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e40af">
    @vite(['resources/css/app.css'])
    <style>
        [x-cloak] { display: none !important; }
        .slide-up { animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="h-full bg-gray-50" x-data="driverApp()" x-init="init()">
    {{-- Header --}}
    <header class="bg-blue-700 text-white px-4 py-4 sticky top-0 z-40 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-bold text-lg" x-text="driverName">{{ $driver->name }}</h1>
                <p class="text-blue-200 text-sm">{{ $driver->restaurant->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-2xl font-bold" x-text="completedToday">{{ $completedToday }}</p>
                    <p class="text-blue-200 text-xs">aujourd'hui</p>
                </div>
                <button @click="toggleGps()" class="p-2 rounded-full" :class="gpsActive ? 'bg-green-500' : 'bg-gray-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="p-4 pb-20 space-y-4">
        {{-- No deliveries state --}}
        <div x-show="deliveries.length === 0" class="text-center py-16">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-gray-500 text-lg font-medium">Pas de course en attente</p>
            <p class="text-gray-400 text-sm mt-1">Vous serez notifie quand une course vous est assignee</p>
        </div>

        {{-- Delivery Cards --}}
        <template x-for="delivery in deliveries" :key="delivery.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden slide-up">
                {{-- Status Bar --}}
                <div class="px-4 py-2 text-sm font-medium text-white"
                     :class="{
                         'bg-blue-500': delivery.status === 'assigned',
                         'bg-indigo-500': delivery.status === 'heading_to_restaurant',
                         'bg-orange-500': delivery.status === 'picked_up',
                         'bg-purple-500': delivery.status === 'delivering',
                     }">
                    <span x-text="delivery.status_label"></span>
                    <span class="float-right opacity-75" x-text="'#' + delivery.order_reference"></span>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Restaurant Info (show when heading to restaurant) --}}
                    <div x-show="delivery.status === 'assigned' || delivery.status === 'heading_to_restaurant'" class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-medium">Recuperer chez</p>
                            <p class="font-semibold text-gray-900" x-text="delivery.restaurant_name"></p>
                            <p class="text-sm text-gray-500" x-text="delivery.restaurant_address"></p>
                        </div>
                    </div>

                    {{-- Delivery Address --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-medium">Livrer a</p>
                            <p class="font-semibold text-gray-900" x-text="delivery.customer_name"></p>
                            <p class="text-sm text-gray-600" x-text="delivery.delivery_address"></p>
                            <template x-if="delivery.delivery_instructions">
                                <p class="text-xs text-yellow-600 mt-1 italic" x-text="'Note: ' + delivery.delivery_instructions"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <a :href="'tel:' + delivery.delivery_phone" class="flex items-center gap-3 bg-green-50 rounded-xl p-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="font-medium text-green-700" x-text="delivery.delivery_phone"></span>
                    </a>

                    {{-- Action Buttons --}}
                    <div class="pt-2">
                        <template x-if="delivery.status === 'assigned'">
                            <button @click="updateDelivery(delivery.id, 'heading')"
                                    class="w-full py-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-lg transition active:scale-95">
                                En route vers le resto
                            </button>
                        </template>
                        <template x-if="delivery.status === 'heading_to_restaurant'">
                            <button @click="updateDelivery(delivery.id, 'pickup')"
                                    class="w-full py-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-lg transition active:scale-95">
                                Commande recuperee
                            </button>
                        </template>
                        <template x-if="delivery.status === 'picked_up'">
                            <button @click="updateDelivery(delivery.id, 'delivering')"
                                    class="w-full py-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-lg transition active:scale-95">
                                En route vers le client
                            </button>
                        </template>
                        <template x-if="delivery.status === 'delivering'">
                            <button @click="updateDelivery(delivery.id, 'delivered')"
                                    class="w-full py-4 rounded-xl bg-green-600 hover:bg-green-500 text-white font-bold text-lg transition active:scale-95">
                                Livree !
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </main>

    <script>
    function driverApp() {
        return {
            deliveries: @json($deliveriesJson),
            completedToday: {{ $completedToday }},
            driverName: '{{ $driver->name }}',
            token: '{{ $token }}',
            gpsActive: false,
            gpsWatchId: null,

            init() {
                setInterval(() => this.fetchData(), 8000);
                this.startGps();
            },

            async fetchData() {
                try {
                    const resp = await fetch(`/livreur/${this.token}/data`);
                    if (!resp.ok) return;
                    const data = await resp.json();

                    const hadNone = this.deliveries.length === 0;
                    this.deliveries = data.deliveries;
                    this.completedToday = data.completed_today;

                    if (hadNone && data.deliveries.length > 0) {
                        this.playSound();
                    }
                } catch (e) {}
            },

            async updateDelivery(deliveryId, action) {
                try {
                    const resp = await fetch(`/livreur/${this.token}/deliveries/${deliveryId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ action }),
                    });

                    if (resp.ok) {
                        await this.fetchData();
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            startGps() {
                if (!navigator.geolocation) return;
                this.gpsWatchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        this.gpsActive = true;
                        this.sendLocation(pos.coords.latitude, pos.coords.longitude);
                    },
                    () => { this.gpsActive = false; },
                    { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
                );
            },

            toggleGps() {
                if (this.gpsActive && this.gpsWatchId) {
                    navigator.geolocation.clearWatch(this.gpsWatchId);
                    this.gpsActive = false;
                    this.gpsWatchId = null;
                } else {
                    this.startGps();
                }
            },

            async sendLocation(lat, lng) {
                try {
                    await fetch(`/livreur/${this.token}/location`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng }),
                    });
                } catch (e) {}
            },

            playSound() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 660;
                    osc.type = 'sine';
                    gain.gain.value = 0.4;
                    osc.start();
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
                    osc.stop(ctx.currentTime + 0.8);
                } catch (e) {}
            }
        };
    }
    </script>
    @vite(['resources/js/app.js'])
</body>
</html>
