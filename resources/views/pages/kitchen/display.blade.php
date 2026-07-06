<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine — {{ $restaurant->name }}</title>
    @livewireStyles
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0a0a0a; color: #fff; height: 100vh; overflow: hidden; user-select: none; }

        /* SPLASH (déverrouillage audio) */
        #splash { position: fixed; inset: 0; background: #0a0a0a; z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; cursor: pointer; }
        #splash h2 { font-size: 22px; font-weight: 900; color: #f97316; }
        #splash p  { font-size: 14px; color: #6b7280; }
        #splash .splash-icon { font-size: 64px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.12);opacity:.75} }

        /* HEADER */
        #header { height: 56px; background: #f97316; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; gap: 12px; flex-shrink: 0; }
        #header h1 { font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
        .header-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .icon-btn { width: 34px; height: 34px; border-radius: 8px; border: none; background: transparent; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
        .icon-btn:hover { background: rgba(255,255,255,.15); }
        .icon-btn.off { opacity: .3; }
        #clock { font-size: 11px; font-family: monospace; opacity: .65; }
        #dot-net { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; }
        #dot-net.off { background: #ef4444; }

        /* LAYOUT */
        #kds-wrap { height: calc(100vh - 56px); display: block; }
        .kds-main { display: flex; height: 100%; overflow: hidden; }
        .kds-col { flex: 1; display: flex; flex-direction: column; border-right: 1px solid #1a1a1a; min-width: 0; }
        .kds-col:last-child { border-right: none; }
        .kds-col-header { height: 40px; display: flex; align-items: center; padding: 0 14px; border-bottom: 1px solid #1a1a1a; flex-shrink: 0; font-size: 13px; font-weight: 700; gap: 8px; }
        .kds-col-body { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 10px; }
        .kds-col-body::-webkit-scrollbar { width: 3px; }
        .kds-col-body::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 2px; }

        .kds-col-new  .kds-col-header { background: rgba(249,115,22,.07);  color: #fb923c; }
        .kds-col-prep .kds-col-header { background: rgba(59,130,246,.07);  color: #60a5fa; }
        .kds-col-ready .kds-col-header { background: rgba(34,197,94,.07); color: #4ade80; }
        .kds-col-count { margin-left: auto; font-family: monospace; font-size: 12px; opacity: .55; }

        .kds-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .kds-dot-new   { background: #f97316; }
        .kds-dot-prep  { background: #3b82f6; }
        .kds-dot-ready { background: #22c55e; }

        /* CARTES */
        .kds-card { background: #111; border-radius: 12px; border: 1px solid #1e1e1e; overflow: hidden; animation: slideIn .2s ease-out; }
        @keyframes slideIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .kds-card-top { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; padding: 8px 12px; border-bottom: 1px solid #1a1a1a; }
        .kds-top-paid       { background: rgba(249,115,22,.09); }
        .kds-top-confirmed  { background: rgba(234,179,8,.09);  }
        .kds-top-preparing  { background: rgba(59,130,246,.09); }
        .kds-top-ready      { background: rgba(34,197,94,.09);  }
        .kds-ref  { font-size: 15px; font-weight: 900; }
        .kds-badge { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
        .kds-badge-paid        { background: #f97316; color: #fff; }
        .kds-badge-confirmed   { background: #eab308; color: #000; }
        .kds-badge-preparing   { background: #3b82f6; color: #fff; }
        .kds-badge-ready       { background: #22c55e; color: #fff; }
        .kds-table { font-size: 11px; background: #222; color: #fff; padding: 2px 8px; border-radius: 20px; font-weight: 700; }
        .kds-timer { font-size: 11px; font-weight: 700; margin-left: auto; }
        .kds-ok   { color: #6b7280; }
        .kds-warn { color: #eab308; }
        .kds-late { color: #ef4444; }

        .kds-card-body { padding: 10px 12px; }
        .kds-customer { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #d1d5db; margin-bottom: 8px; }
        .kds-type { font-size: 11px; color: #6b7280; font-weight: 400; }
        .kds-item { display: flex; gap: 8px; margin-bottom: 5px; }
        .kds-qty  { font-size: 13px; font-weight: 900; min-width: 22px; }
        .kds-name { font-size: 13px; color: #e5e7eb; }
        .kds-opt  { display: inline-block; font-size: 10px; background: #1e1e1e; color: #9ca3af; padding: 1px 5px; border-radius: 3px; margin: 2px 2px 0 0; }
        .kds-note { font-size: 11px; color: #fbbf24; background: rgba(251,191,36,.1); padding: 2px 6px; border-radius: 4px; margin-top: 3px; }

        .kds-btn { width: 100%; padding: 10px; border: none; border-radius: 10px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; margin-top: 10px; transition: opacity .15s, transform .1s; }
        .kds-btn:hover  { opacity: .88; }
        .kds-btn:active { transform: scale(.97); }
        .kds-btn:disabled { opacity: .5; cursor: wait; }
        .kds-btn-confirm { background: #f97316; color: #fff; }
        .kds-btn-prepare { background: #eab308; color: #000; }
        .kds-btn-ready   { background: #22c55e; color: #fff; }
        .kds-ready-label { text-align: center; font-size: 12px; color: #4ade80; font-weight: 700; padding: 6px; background: rgba(34,197,94,.07); border-radius: 8px; margin-top: 10px; }

        .kds-empty { display: flex; align-items: center; justify-content: center; height: 120px; color: #2a2a2a; font-size: 13px; }

        /* OVERLAY NOUVELLE COMMANDE */
        #alert-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 60; align-items: center; justify-content: center; }
        #alert-overlay.show { display: flex; }
        #alert-box { background: #f97316; border-radius: 24px; padding: 36px 28px; text-align: center; max-width: 300px; width: 90%; animation: pop .25s ease-out; }
        @keyframes pop { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
        #alert-box .a-icon  { font-size: 48px; margin-bottom: 6px; }
        #alert-box .a-title { font-size: 26px; font-weight: 900; color: #fff; }
        #alert-box .a-detail{ font-size: 13px; color: rgba(255,255,255,.85); margin-top: 4px; }
        #alert-box .a-hint  { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 14px; }
    </style>
</head>
<body>

{{-- SPLASH : déverrouillage AudioContext (obligatoire Chrome/Safari) --}}
<div id="splash">
    <div class="splash-icon">🍳</div>
    <h2>Écran Cuisine</h2>
    <p>Touchez l'écran pour activer le son et démarrer</p>
</div>

{{-- HEADER (masqué jusqu'au déverrouillage) --}}
<header id="header" style="display:none;">
    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        @if($restaurant->logo_url ?? null)
            <img src="{{ $restaurant->logo_url }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        @endif
        <h1>{{ $restaurant->name }}</h1>
    </div>
    <div class="header-actions">
        <div id="dot-net"></div>
        <button class="icon-btn" id="btn-voice" title="Synthèse vocale" onclick="kdsToggleVoice()">
            <svg id="iv-on"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            <svg id="iv-off" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/></svg>
        </button>
        <button class="icon-btn" id="btn-sound" title="Son" onclick="kdsToggleSound()">
            <svg id="is-on"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/></svg>
            <svg id="is-off" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
        </button>
        <span id="clock"></span>
    </div>
</header>

{{-- CONTENU LIVEWIRE --}}
<div id="kds-wrap" style="display:none;">
    @livewire('kitchen.kitchen-display', ['token' => $token])
</div>

{{-- OVERLAY NOUVELLE COMMANDE --}}
<div id="alert-overlay" onclick="kdsDismissAlert()">
    <div id="alert-box">
        <div class="a-icon">🔔</div>
        <div class="a-title">Nouvelle commande !</div>
        <div class="a-detail" id="a-detail"></div>
        <div class="a-hint">Touchez pour fermer</div>
    </div>
</div>

@livewireScripts

<script>
(function () {
    /* ─── STATE ─────────────────────────────────── */
    var audioCtx   = null;
    var soundOn    = true;
    var voiceOn    = true;
    var alertTimer = null;
    var knownIds   = {};   // IDs vus — pour détecter les nouvelles commandes
    var unlocked   = false;

    /* ─── SPLASH / DÉVERROUILLAGE ───────────────── */
    document.getElementById('splash').addEventListener('click', unlock, { once: true });

    function unlock() {
        // Crée et résume l'AudioContext DANS le geste utilisateur
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch(e) {}

        // Charge les voix (peut prendre un tick)
        if ('speechSynthesis' in window) {
            window.speechSynthesis.getVoices();
        }

        // Montre l'interface, cache le splash
        document.getElementById('splash').style.display = 'none';
        document.getElementById('header').style.display = '';
        document.getElementById('kds-wrap').style.display = '';

        unlocked = true;
        startClock();
        watchNetwork();
        wakeLock();

        // Marque les commandes déjà à l'écran comme connues
        document.querySelectorAll('[wire\\:key^="order-"]').forEach(function(el) {
            var id = el.getAttribute('wire:key').replace('order-', '');
            knownIds[id] = true;
        });
    }

    /* ─── LIVEWIRE : détection nouvelles commandes ─
       Après chaque update Livewire on compare les IDs
       présents dans le DOM avec knownIds               */
    document.addEventListener('livewire:updated', function () {
        if (!unlocked) return;
        var current = [];
        document.querySelectorAll('[wire\\:key^="order-"]').forEach(function(el) {
            current.push(el.getAttribute('wire:key').replace('order-', ''));
        });
        current.forEach(function(id) {
            if (!knownIds[id]) {
                knownIds[id] = true;
                // Récupère les infos de la carte pour l'annonce
                var card = document.querySelector('[wire\\:key="order-' + id + '"]');
                if (card) announceFromCard(card);
            }
        });
    });

    function announceFromCard(card) {
        var ref    = (card.querySelector('.kds-ref')   || {}).textContent || '';
        var table  = (card.querySelector('.kds-table') || {}).textContent || '';
        var names  = [];
        card.querySelectorAll('.kds-name').forEach(function(n) { names.push(n.textContent.trim()); });
        var detail = [table.trim(), names.join(', ')].filter(Boolean).join(' — ');

        document.getElementById('a-detail').textContent = detail;
        document.getElementById('alert-overlay').classList.add('show');
        clearTimeout(alertTimer);
        alertTimer = setTimeout(kdsDismissAlert, 6000);

        kdsPlaySound();
        kdsSpeak(ref, table, names);
    }

    window.kdsDismissAlert = function() {
        document.getElementById('alert-overlay').classList.remove('show');
        clearTimeout(alertTimer);
    };

    /* ─── SON ───────────────────────────────────── */
    function kdsPlaySound() {
        if (!soundOn || !audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            var freqs = [880, 1100, 1320, 880];
            freqs.forEach(function(freq, i) {
                setTimeout(function() {
                    var osc  = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.frequency.value = freq;
                    osc.type = i === 3 ? 'triangle' : 'sine';
                    var dur = i === 3 ? 0.5 : 0.28;
                    gain.gain.setValueAtTime(i === 3 ? 0.22 : 0.35, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + dur);
                }, i * 130);
            });
        } catch(e) {}
    }

    /* ─── SYNTHÈSE VOCALE ───────────────────────── */
    function kdsSpeak(ref, table, names) {
        if (!voiceOn || !('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var text = 'Nouvelle commande ' + ref + '. ';
        if (table) text += table + '. ';
        text += names.join(', ') + '.';
        var u = new SpeechSynthesisUtterance(text);
        u.lang   = 'fr-FR';
        u.rate   = 0.88;
        u.pitch  = 1.0;
        u.volume = 1.0;
        var voices = window.speechSynthesis.getVoices();
        var fr = voices.find(function(v) { return v.lang.startsWith('fr'); });
        if (fr) u.voice = fr;
        setTimeout(function() { window.speechSynthesis.speak(u); }, 500);
    }

    /* ─── TOGGLES ───────────────────────────────── */
    window.kdsToggleVoice = function() {
        voiceOn = !voiceOn;
        document.getElementById('btn-voice').classList.toggle('off', !voiceOn);
        document.getElementById('iv-on').style.display  = voiceOn ? '' : 'none';
        document.getElementById('iv-off').style.display = voiceOn ? 'none' : '';
        if (!voiceOn && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    };
    window.kdsToggleSound = function() {
        soundOn = !soundOn;
        document.getElementById('btn-sound').classList.toggle('off', !soundOn);
        document.getElementById('is-on').style.display  = soundOn ? '' : 'none';
        document.getElementById('is-off').style.display = soundOn ? 'none' : '';
    };

    /* ─── HORLOGE ───────────────────────────────── */
    function startClock() {
        function tick() {
            document.getElementById('clock').textContent =
                new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }
        tick(); setInterval(tick, 1000);
    }

    /* ─── RÉSEAU ────────────────────────────────── */
    function setNet(v) {
        var d = document.getElementById('dot-net');
        if (d) { d.className = v ? '' : 'off'; d.id = 'dot-net'; }
    }
    function watchNetwork() {
        window.addEventListener('online',  function() { setNet(true); });
        window.addEventListener('offline', function() { setNet(false); });
        setNet(navigator.onLine);
    }

    /* ─── WAKE LOCK ─────────────────────────────── */
    function wakeLock() {
        if (!('wakeLock' in navigator)) return;
        navigator.wakeLock.request('screen').catch(function(){});
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible')
                navigator.wakeLock.request('screen').catch(function(){});
        });
    }

    /* ─── VOIX CHROME : reload différé ─────────────
       Chrome charge les voix de façon asynchrone     */
    if ('speechSynthesis' in window) {
        window.speechSynthesis.addEventListener('voiceschanged', function() {
            window.speechSynthesis.getVoices();
        });
    }

})();
</script>
</body>
</html>
