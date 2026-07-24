<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Serveur — {{ $restaurant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0f172a; color: #f1f5f9; font-family: system-ui, sans-serif; }
        .pin-btn { width: 64px; height: 64px; border-radius: 50%; background: #1e293b; border: 1px solid #334155; font-size: 1.5rem; font-weight: bold; cursor: pointer; transition: background 0.15s; }
        .pin-btn:hover { background: #334155; }
        .pin-btn:active { background: #475569; transform: scale(0.96); }
        .pin-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid #64748b; transition: background 0.15s; }
        .pin-dot.filled { background: #6366f1; border-color: #6366f1; }
    </style>
</head>
<body class="min-h-screen">

{{-- Splash déverrouillage audio --}}
<div id="splash" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900">
    <div class="text-center">
        <div class="text-4xl mb-4">👨‍🍳</div>
        <h1 class="text-2xl font-bold text-white mb-2">{{ $restaurant->name }}</h1>
        <p class="text-slate-400 mb-8">Interface Serveur</p>
        <button onclick="startApp()" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl text-lg hover:bg-indigo-500 transition">
            Appuyer pour démarrer
        </button>
    </div>
</div>

{{-- App principale --}}
<div id="app" class="hidden min-h-screen">

    {{-- PIN Screen --}}
    <div id="pin-screen" class="flex items-center justify-center min-h-screen">
        <div class="text-center w-full max-w-xs mx-auto px-4">
            <h2 class="text-xl font-bold text-white mb-2">Entrez votre PIN</h2>
            <p class="text-slate-400 text-sm mb-8">{{ $restaurant->name }}</p>

            {{-- Dots indicateurs --}}
            <div class="flex justify-center gap-4 mb-8">
                <div class="pin-dot" id="dot-0"></div>
                <div class="pin-dot" id="dot-1"></div>
                <div class="pin-dot" id="dot-2"></div>
                <div class="pin-dot" id="dot-3"></div>
            </div>

            {{-- Clavier PIN --}}
            <div class="grid grid-cols-3 gap-4 justify-items-center mb-4">
                @foreach([1,2,3,4,5,6,7,8,9] as $n)
                <button class="pin-btn" onclick="addPin('{{ $n }}')">{{ $n }}</button>
                @endforeach
                <div></div>
                <button class="pin-btn" onclick="addPin('0')">0</button>
                <button class="pin-btn text-slate-400" onclick="deletePin()">&#x232B;</button>
            </div>

            <div id="pin-error" class="text-red-400 text-sm mt-4 hidden">PIN incorrect. Réessayez.</div>
            <div id="pin-locked" class="text-amber-400 text-sm mt-4 hidden">Trop de tentatives. Attendez 5 minutes.</div>
        </div>
    </div>

    {{-- Dashboard Serveur --}}
    <div id="dashboard" class="hidden">
        <header class="bg-slate-800 border-b border-slate-700 px-4 py-3 flex items-center justify-between">
            <div>
                <span class="text-white font-bold" id="waiter-name-display">Serveur</span>
                <span class="text-slate-400 text-sm ml-2" id="space-display"></span>
            </div>
            <button onclick="logout()" class="text-slate-400 text-sm hover:text-white transition">Déconnexion</button>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-6">
            {{-- Info table --}}
            <div class="bg-slate-800 rounded-2xl p-4 mb-6">
                <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wide mb-2">Numéro de table</label>
                <input type="text" id="table-input" placeholder="Ex: 5, VIP-3, Terrasse 2..."
                    class="w-full bg-slate-700 text-white rounded-xl px-4 py-3 text-lg font-bold placeholder-slate-500 border border-slate-600 focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Commandes actives --}}
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wide mb-3">Mes commandes actives</h3>
            <div id="orders-list" class="space-y-3">
                <p class="text-slate-500 text-sm text-center py-8">Aucune commande active</p>
            </div>
        </div>
    </div>

</div>

<script>
    const TOKEN    = '{{ $token }}';
    const AUTH_URL = '{{ route('waiter.auth', $token) }}';
    const DATA_URL = '{{ route('waiter.data', $token) }}';
    const CSRF     = '{{ csrf_token() }}';

    let pin = '';
    let waiterSession = null;
    let pollInterval  = null;

    // --- Splash ---
    function startApp() {
        document.getElementById('splash').classList.add('hidden');
        document.getElementById('app').classList.remove('hidden');
    }

    // --- PIN ---
    function addPin(digit) {
        if (pin.length >= 4) return;
        pin += digit;
        updateDots();
        if (pin.length === 4) setTimeout(submitPin, 200);
    }

    function deletePin() {
        pin = pin.slice(0, -1);
        updateDots();
        hideErrors();
    }

    function updateDots() {
        for (let i = 0; i < 4; i++) {
            document.getElementById('dot-' + i).classList.toggle('filled', i < pin.length);
        }
    }

    function hideErrors() {
        document.getElementById('pin-error').classList.add('hidden');
        document.getElementById('pin-locked').classList.add('hidden');
    }

    async function submitPin() {
        try {
            const res = await fetch(AUTH_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ pin }),
            });
            const data = await res.json();
            if (data.success) {
                waiterSession = data;
                document.getElementById('pin-screen').classList.add('hidden');
                document.getElementById('dashboard').classList.remove('hidden');
                document.getElementById('waiter-name-display').textContent = data.waiter_name;
                document.getElementById('space-display').textContent = data.space_name ? '— ' + data.space_name : '';
                startPolling();
            } else {
                pin = '';
                updateDots();
                if (data.locked) {
                    document.getElementById('pin-locked').classList.remove('hidden');
                } else {
                    document.getElementById('pin-error').classList.remove('hidden');
                }
            }
        } catch (e) {
            pin = '';
            updateDots();
        }
    }

    function logout() {
        waiterSession = null;
        pin = '';
        updateDots();
        stopPolling();
        document.getElementById('dashboard').classList.add('hidden');
        document.getElementById('pin-screen').classList.remove('hidden');
        hideErrors();
    }

    // --- Polling commandes ---
    function startPolling() {
        pollInterval = setInterval(fetchOrders, 5000);
        fetchOrders();
    }

    function stopPolling() {
        if (pollInterval) clearInterval(pollInterval);
    }

    async function fetchOrders() {
        if (!waiterSession) return;
        try {
            const res  = await fetch(DATA_URL + '?waiter_id=' + waiterSession.waiter_id);
            const data = await res.json();
            renderOrders(data.orders || []);
        } catch (e) {}
    }

    function renderOrders(orders) {
        const list = document.getElementById('orders-list');
        if (!orders.length) {
            list.innerHTML = '<p class="text-slate-500 text-sm text-center py-8">Aucune commande active</p>';
            return;
        }
        list.innerHTML = orders.map(o => `
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-white">Table ${o.table || '?'}</span>
                    <span class="text-xs px-2 py-1 rounded-full font-semibold ${statusClass(o.status)}">${o.status_label}</span>
                </div>
                <p class="text-slate-400 text-sm">${o.items}</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-slate-500 text-xs">${o.reference} · ${o.created_at}</span>
                    <span class="text-indigo-400 font-bold text-sm">${o.total}</span>
                </div>
            </div>
        `).join('');
    }

    function statusClass(status) {
        return {
            confirmed: 'bg-blue-900 text-blue-300',
            preparing: 'bg-amber-900 text-amber-300',
            ready:     'bg-emerald-900 text-emerald-300',
        }[status] || 'bg-slate-700 text-slate-300';
    }
</script>
</body>
</html>
