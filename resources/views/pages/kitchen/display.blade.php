<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine — {{ $restaurant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0a0a0a; color: #fff; height: 100vh; overflow: hidden; user-select: none; }

        /* HEADER */
        #header { height: 56px; background: #f97316; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; gap: 12px; flex-shrink: 0; }
        #header h1 { font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
        .header-counts { display: flex; gap: 6px; font-size: 12px; }
        .badge { padding: 3px 8px; border-radius: 8px; font-weight: 700; white-space: nowrap; }
        .badge-new { background: rgba(255,255,255,0.25); }
        .badge-prep { background: rgba(255,255,255,0.15); }
        .badge-ready { background: #22c55e; }
        .header-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .icon-btn { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
        .icon-btn:hover { background: rgba(255,255,255,0.15); }
        .icon-btn.off { opacity: 0.35; }
        #dot-online { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
        #dot-online.offline { background: #ef4444; }
        #clock { font-size: 11px; font-family: monospace; opacity: 0.6; }

        /* COLONNES */
        #main { display: flex; height: calc(100vh - 56px); overflow: hidden; }
        .col { flex: 1; display: flex; flex-direction: column; border-right: 1px solid #1f1f1f; min-width: 0; }
        .col:last-child { border-right: none; }
        .col-header { height: 40px; display: flex; align-items: center; padding: 0 16px; border-bottom: 1px solid #1f1f1f; flex-shrink: 0; font-size: 13px; font-weight: 700; gap: 8px; }
        .col-body { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 12px; }
        .col-body::-webkit-scrollbar { width: 4px; }
        .col-body::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }

        /* COULEURS COLONNES */
        .col-new .col-header { background: rgba(249,115,22,0.08); color: #fb923c; }
        .col-prep .col-header { background: rgba(59,130,246,0.08); color: #60a5fa; }
        .col-ready .col-header { background: rgba(34,197,94,0.08); color: #4ade80; }

        /* DOT COULEUR */
        .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .dot-new { background: #f97316; }
        .dot-prep { background: #3b82f6; }
        .dot-ready { background: #22c55e; }
        .col-count { margin-left: auto; font-family: monospace; opacity: 0.6; font-size: 12px; }

        /* CARTES */
        .card { background: #111; border-radius: 12px; border: 1px solid #222; overflow: hidden; animation: slideIn 0.25s ease-out; }
        @keyframes slideIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .card-top { display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; border-bottom: 1px solid #1a1a1a; gap: 8px; }
        .card-top.new { background: rgba(249,115,22,0.08); }
        .card-top.confirmed { background: rgba(234,179,8,0.08); }
        .card-top.preparing { background: rgba(59,130,246,0.08); }
        .card-top.ready { background: rgba(34,197,94,0.08); }
        .card-ref { font-size: 15px; font-weight: 900; }
        .card-badge { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; flex-shrink: 0; }
        .badge-paid { background: #f97316; color: #fff; }
        .badge-confirmed { background: #eab308; color: #000; }
        .badge-preparing { background: #3b82f6; color: #fff; }
        .badge-ready { background: #22c55e; color: #fff; }
        .card-table { font-size: 11px; background: #222; color: #fff; padding: 2px 8px; border-radius: 20px; font-weight: 700; flex-shrink: 0; }
        .card-timer { font-size: 11px; font-weight: 700; flex-shrink: 0; }
        .timer-ok { color: #6b7280; }
        .timer-warn { color: #eab308; }
        .timer-late { color: #ef4444; }
        .card-body { padding: 12px 14px; }
        .card-customer { font-size: 13px; font-weight: 600; color: #d1d5db; margin-bottom: 8px; display: flex; justify-content: space-between; }
        .card-type { font-size: 11px; color: #6b7280; }
        .item-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 5px; }
        .item-qty { font-size: 13px; font-weight: 900; min-width: 24px; }
        .item-name { font-size: 13px; color: #e5e7eb; }
        .item-note { font-size: 11px; color: #fbbf24; background: rgba(251,191,36,0.1); padding: 2px 6px; border-radius: 4px; margin-top: 2px; }
        .card-btn { width: 100%; padding: 10px; border: none; border-radius: 10px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; margin-top: 10px; transition: opacity 0.15s, transform 0.1s; }
        .card-btn:hover { opacity: 0.9; }
        .card-btn:active { transform: scale(0.97); }
        .btn-confirm { background: #f97316; color: #fff; }
        .btn-prepare { background: #eab308; color: #000; }
        .btn-ready { background: #22c55e; color: #fff; }

        /* ÉTAT VIDE */
        .empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 140px; color: #333; font-size: 13px; text-align: center; gap: 6px; }

        /* OVERLAY NOUVELLE COMMANDE */
        #alert-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 50; align-items: center; justify-content: center; }
        #alert-overlay.show { display: flex; }
        #alert-box { background: #f97316; border-radius: 24px; padding: 40px 32px; text-align: center; max-width: 320px; width: 90%; animation: popIn 0.3s ease-out; }
        @keyframes popIn { from { opacity:0; transform:scale(0.85); } to { opacity:1; transform:scale(1); } }
        #alert-title { font-size: 28px; font-weight: 900; color: #fff; margin-bottom: 6px; }
        #alert-detail { font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500; }
        #alert-hint { font-size: 12px; color: rgba(255,255,255,0.45); margin-top: 16px; }

        /* SCROLLBAR GLOBAL */
        html { scrollbar-color: #333 transparent; }
    </style>
</head>
<body>

<!-- HEADER -->
<header id="header">
    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        @if($restaurant->logo_url)
            <img src="{{ $restaurant->logo_url }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,0.2);flex-shrink:0;">
        @endif
        <h1>{{ $restaurant->name }}</h1>
    </div>
    <div class="header-counts">
        <span class="badge badge-new" id="cnt-new">0 en attente</span>
        <span class="badge badge-prep" id="cnt-prep">0 en prép</span>
        <span class="badge badge-ready" id="cnt-ready" style="display:none;">0 prêt</span>
    </div>
    <div class="header-actions">
        <div id="dot-online" title="Connexion"></div>
        <button class="icon-btn" id="btn-voice" title="Synthèse vocale" onclick="toggleVoice()">
            <svg id="icon-voice-on" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            <svg id="icon-voice-off" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/></svg>
        </button>
        <button class="icon-btn" id="btn-sound" title="Son" onclick="toggleSound()">
            <svg id="icon-sound-on" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/></svg>
            <svg id="icon-sound-off" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
        </button>
        <span id="clock" style="font-size:11px;font-family:monospace;opacity:0.6;"></span>
    </div>
</header>

<!-- COLONNES -->
<div id="main">
    <div class="col col-new">
        <div class="col-header"><span class="dot dot-new"></span>Nouvelles<span class="col-count" id="col-cnt-new">0</span></div>
        <div class="col-body" id="col-new"></div>
    </div>
    <div class="col col-prep">
        <div class="col-header"><span class="dot dot-prep"></span>En préparation<span class="col-count" id="col-cnt-prep">0</span></div>
        <div class="col-body" id="col-prep"></div>
    </div>
    <div class="col col-ready">
        <div class="col-header"><span class="dot dot-ready"></span>Prêtes à servir<span class="col-count" id="col-cnt-ready">0</span></div>
        <div class="col-body" id="col-ready"></div>
    </div>
</div>

<!-- OVERLAY NOUVELLE COMMANDE -->
<div id="alert-overlay" onclick="closeAlert()">
    <div id="alert-box">
        <div style="font-size:48px;margin-bottom:8px;">🔔</div>
        <div id="alert-title">Nouvelle commande !</div>
        <div id="alert-detail"></div>
        <div id="alert-hint">Touchez pour fermer</div>
    </div>
</div>

<script>
(function() {
    var TOKEN   = '{{ $token }}';
    var CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    var INITIAL = @json($ordersJson);

    var state = {
        orders:       [],
        knownIds:     {},   // IDs présents au chargement — n'annonce pas d'alerte pour eux
        soundEnabled: true,
        voiceEnabled: true,
        alertTimer:   null,
    };

    /* ── INIT ──────────────────────────────────────────── */
    function init() {
        // Marque les commandes initiales comme "déjà connues"
        INITIAL.forEach(function(o) { state.knownIds[o.id] = true; });
        state.orders = INITIAL;
        render();
        startClock();
        startPolling();
        watchNetwork();
        wakeLock();
        loadVoices();
    }

    /* ── POLLING ───────────────────────────────────────── */
    function startPolling() {
        fetchOrders(); // premier fetch immédiat
        setInterval(fetchOrders, 5000);
    }

    function fetchOrders() {
        fetch('/cuisine/' + TOKEN + '/data')
            .then(function(r) {
                setOnline(r.ok);
                return r.ok ? r.json() : null;
            })
            .then(function(data) {
                if (!data) return;
                // Nouvelles commandes = IDs absents de knownIds
                data.orders.forEach(function(o) {
                    if (!state.knownIds[o.id]) {
                        state.knownIds[o.id] = true;
                        announceNewOrder(o);
                    }
                });
                state.orders = data.orders;
                render();
            })
            .catch(function() { setOnline(false); });
    }

    /* ── ACTIONS ───────────────────────────────────────── */
    window.updateOrder = function(orderId, action) {
        fetch('/cuisine/' + TOKEN + '/orders/' + orderId + '/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ action: action }),
        })
        .then(function(r) { if (r.ok) fetchOrders(); })
        .catch(function() {});
    };

    /* ── ESCAPE XSS ────────────────────────────────────── */
    function h(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ── RENDER ────────────────────────────────────────── */
    function render() {
        var newOrders  = state.orders.filter(function(o) { return o.status === 'paid' || o.status === 'confirmed'; });
        var prepOrders = state.orders.filter(function(o) { return o.status === 'preparing'; });
        var readyOrders= state.orders.filter(function(o) { return o.status === 'ready'; });

        renderCol('col-new',  newOrders,  renderNewCard);
        renderCol('col-prep', prepOrders, renderPrepCard);
        renderCol('col-ready',readyOrders,renderReadyCard);

        // Compteurs header
        document.getElementById('cnt-new').textContent  = newOrders.length  + ' en attente';
        document.getElementById('cnt-prep').textContent = prepOrders.length + ' en prép';
        var rdyBadge = document.getElementById('cnt-ready');
        if (readyOrders.length > 0) {
            rdyBadge.textContent = readyOrders.length + ' prêt' + (readyOrders.length > 1 ? 's' : '');
            rdyBadge.style.display = '';
        } else {
            rdyBadge.style.display = 'none';
        }

        // Compteurs colonnes
        document.getElementById('col-cnt-new').textContent  = newOrders.length;
        document.getElementById('col-cnt-prep').textContent = prepOrders.length;
        document.getElementById('col-cnt-ready').textContent= readyOrders.length;
    }

    function renderCol(id, orders, cardFn) {
        var col = document.getElementById(id);
        col.innerHTML = '';
        if (orders.length === 0) {
            col.innerHTML = '<div class="empty"><svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:.25"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg><span>Aucune commande</span></div>';
            return;
        }
        orders.forEach(function(o) {
            var div = document.createElement('div');
            div.className = 'card';
            div.innerHTML = cardFn(o);
            col.appendChild(div);
        });
    }

    function timerClass(min) {
        if (min > 20) return 'timer-late';
        if (min > 10) return 'timer-warn';
        return 'timer-ok';
    }

    function itemsHtml(items) {
        return items.map(function(item) {
            var opts = (item.options || []).map(function(opt) {
                var label = typeof opt === 'string' ? opt : (opt.name || '');
                return '<span style="font-size:10px;background:#1e1e1e;color:#9ca3af;padding:1px 5px;border-radius:3px;">' + h(label) + '</span>';
            }).join(' ');
            var note = item.instructions ? '<div class="item-note">&#9888; ' + h(item.instructions) + '</div>' : '';
            return '<div class="item-row"><span class="item-qty">' + h(item.quantity) + 'x</span><div><span class="item-name">' + h(item.name) + '</span>' + (opts ? '<div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:3px;">' + opts + '</div>' : '') + note + '</div></div>';
        }).join('');
    }

    function renderNewCard(o) {
        var topClass = o.status === 'paid' ? 'new' : 'confirmed';
        var badgeClass = o.status === 'paid' ? 'badge-paid' : 'badge-confirmed';
        var badgeLabel = o.status === 'paid' ? 'NOUVELLE' : 'CONFIRM&#201;E';
        var tableHtml = o.table_number ? '<span class="card-table">Table ' + h(o.table_number) + '</span>' : '';
        var oid = parseInt(o.id, 10);
        var btn = o.status === 'paid'
            ? '<button class="card-btn btn-confirm" onclick="updateOrder(' + oid + ', \'confirm\')">&#10003; Confirmer</button>'
            : '<button class="card-btn btn-prepare" onclick="updateOrder(' + oid + ', \'prepare\')">&#127859; Commencer</button>';
        return '<div class="card-top ' + topClass + '">'
            + '<span class="card-ref">#' + h(o.reference) + '</span>'
            + '<span class="card-badge ' + badgeClass + '">' + badgeLabel + '</span>'
            + tableHtml
            + '<span class="card-timer ' + timerClass(o.minutes_ago) + '">' + Math.round(o.minutes_ago) + 'min</span>'
            + '</div>'
            + '<div class="card-body">'
            + '<div class="card-customer"><span>' + h(o.customer_name || 'Client') + '</span><span class="card-type">' + h(o.type) + '</span></div>'
            + itemsHtml(o.items)
            + btn
            + '</div>';
    }

    function renderPrepCard(o) {
        var oid = parseInt(o.id, 10);
        return '<div class="card-top preparing">'
            + '<span class="card-ref">#' + h(o.reference) + '</span>'
            + '<span class="card-badge badge-preparing">EN COURS</span>'
            + (o.table_number ? '<span class="card-table">Table ' + h(o.table_number) + '</span>' : '')
            + '<span class="card-timer ' + timerClass(o.minutes_ago) + '">' + Math.round(o.minutes_ago) + 'min</span>'
            + '</div>'
            + '<div class="card-body">'
            + '<div class="card-customer"><span>' + h(o.customer_name || 'Client') + '</span><span class="card-type">' + h(o.type) + '</span></div>'
            + itemsHtml(o.items)
            + '<button class="card-btn btn-ready" onclick="updateOrder(' + oid + ', \'ready\')">&#9989; Pr&#234;t &#8212; Servir !</button>'
            + '</div>';
    }

    function renderReadyCard(o) {
        return '<div class="card-top ready">'
            + '<span class="card-ref">#' + h(o.reference) + '</span>'
            + '<span class="card-badge badge-ready">PR&#202;T</span>'
            + (o.table_number ? '<span class="card-table">Table ' + h(o.table_number) + '</span>' : '')
            + '<span style="font-size:11px;color:#4ade80;font-family:monospace;flex-shrink:0;">' + h(o.ready_at || o.created_at) + '</span>'
            + '</div>'
            + '<div class="card-body">'
            + '<div class="card-customer"><span>' + h(o.customer_name || 'Client') + '</span><span class="card-type">' + h(o.type) + '</span></div>'
            + itemsHtml(o.items)
            + '<div style="text-align:center;font-size:12px;color:#4ade80;font-weight:700;padding:6px;background:rgba(34,197,94,0.08);border-radius:8px;">En attente d\'un serveur</div>'
            + '</div>';
    }

    /* ── ALERTE ────────────────────────────────────────── */
    function announceNewOrder(order) {
        var tableInfo = order.table_number ? 'Table ' + order.table_number : (order.type || '');
        var dishes = order.items.map(function(i) { return i.quantity + ' ' + i.name; }).join(', ');
        document.getElementById('alert-detail').textContent = [tableInfo, dishes].filter(Boolean).join(' — ');
        document.getElementById('alert-overlay').classList.add('show');
        clearTimeout(state.alertTimer);
        state.alertTimer = setTimeout(closeAlert, 6000);
        playSound();
        speakOrder(order);
    }

    window.closeAlert = function() {
        document.getElementById('alert-overlay').classList.remove('show');
        clearTimeout(state.alertTimer);
    };

    /* ── SON ───────────────────────────────────────────── */
    function playSound() {
        if (!state.soundEnabled) return;
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            [880, 1100, 1320, 880].forEach(function(freq, i) {
                setTimeout(function() {
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.value = freq;
                    osc.type = i === 3 ? 'triangle' : 'sine';
                    gain.gain.setValueAtTime(i === 3 ? 0.25 : 0.38, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (i === 3 ? 0.5 : 0.3));
                    osc.start(ctx.currentTime); osc.stop(ctx.currentTime + (i === 3 ? 0.5 : 0.3));
                }, i * 135);
            });
        } catch(e) {}
    }

    /* ── SYNTHÈSE VOCALE ───────────────────────────────── */
    function loadVoices() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.getVoices();
            window.speechSynthesis.addEventListener('voiceschanged', function() { window.speechSynthesis.getVoices(); });
        }
    }

    function speakOrder(order) {
        if (!state.voiceEnabled || !('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var ref    = order.reference || order.id;
        var table  = order.table_number ? 'table ' + order.table_number : '';
        var dishes = order.items.map(function(i) { return (i.quantity > 1 ? i.quantity + ' ' : '') + i.name; }).join(', ');
        var notes  = order.items.filter(function(i) { return i.instructions; }).map(function(i) { return 'attention ' + i.name + ' : ' + i.instructions; }).join('. ');
        var text   = 'Nouvelle commande ! Numéro ' + ref + '.';
        if (table) text += ' ' + table + '.';
        else if (order.type) text += ' ' + order.type.toLowerCase() + '.';
        text += ' ' + dishes + '.';
        if (notes) text += ' ' + notes + '.';
        var utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'fr-FR'; utter.rate = 0.9; utter.pitch = 1.0; utter.volume = 1.0;
        var voices = window.speechSynthesis.getVoices();
        var frVoice = voices.find(function(v) { return v.lang.startsWith('fr'); });
        if (frVoice) utter.voice = frVoice;
        setTimeout(function() { window.speechSynthesis.speak(utter); }, 600);
    }

    /* ── TOGGLES ───────────────────────────────────────── */
    window.toggleVoice = function() {
        state.voiceEnabled = !state.voiceEnabled;
        document.getElementById('btn-voice').classList.toggle('off', !state.voiceEnabled);
        document.getElementById('icon-voice-on').style.display  = state.voiceEnabled ? '' : 'none';
        document.getElementById('icon-voice-off').style.display = state.voiceEnabled ? 'none' : '';
        if (!state.voiceEnabled) window.speechSynthesis.cancel();
    };
    window.toggleSound = function() {
        state.soundEnabled = !state.soundEnabled;
        document.getElementById('btn-sound').classList.toggle('off', !state.soundEnabled);
        document.getElementById('icon-sound-on').style.display  = state.soundEnabled ? '' : 'none';
        document.getElementById('icon-sound-off').style.display = state.soundEnabled ? 'none' : '';
    };

    /* ── HORLOGE ───────────────────────────────────────── */
    function startClock() {
        function tick() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        }
        tick(); setInterval(tick, 1000);
    }

    /* ── RÉSEAU ────────────────────────────────────────── */
    function setOnline(v) {
        document.getElementById('dot-online').className = v ? '' : 'offline';
        document.getElementById('dot-online').id = 'dot-online';
    }
    function watchNetwork() {
        window.addEventListener('online',  function() { setOnline(true);  fetchOrders(); });
        window.addEventListener('offline', function() { setOnline(false); });
        setOnline(navigator.onLine);
    }

    /* ── WAKE LOCK ─────────────────────────────────────── */
    function wakeLock() {
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(function() {});
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') navigator.wakeLock.request('screen').catch(function() {});
            });
        }
    }

    /* ── START ─────────────────────────────────────────── */
    init();
})();
</script>
</body>
</html>
