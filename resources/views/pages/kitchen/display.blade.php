<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine — {{ $restaurant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0a0a0a; color: #fff; height: 100vh; overflow: hidden; user-select: none; }

        /* SPLASH */
        #splash { position: fixed; inset: 0; z-index: 200; background: #0a0a0a; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 18px; transition: opacity .35s; }
        #splash.gone { opacity: 0; pointer-events: none; }
        #splash-icon { font-size: 72px; animation: pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
        #splash h2 { font-size: 22px; font-weight: 900; color: #f97316; }
        #splash p  { font-size: 14px; color: #6b7280; }
        #splash-btn { padding: 14px 36px; background: #f97316; color: #fff; border: none; border-radius: 14px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 0 40px rgba(249,115,22,.45); }
        #splash-btn:active { transform: scale(.96); }

        /* HEADER */
        #header { height: 56px; background: #f97316; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; gap: 12px; }
        #header h1 { font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
        .hd-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .iBtn { width: 34px; height: 34px; border-radius: 8px; border: none; background: transparent; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .iBtn:hover { background: rgba(255,255,255,.15); }
        .iBtn.off { opacity: .28; }
        #dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
        #dot.red { background: #ef4444; }
        #clk { font-size: 11px; font-family: monospace; opacity: .6; }

        /* COLONNES */
        #main { display: flex; height: calc(100vh - 56px); }
        .col { flex: 1; display: flex; flex-direction: column; border-right: 1px solid #181818; min-width: 0; }
        .col:last-child { border-right: none; }
        .col-hd { height: 40px; display: flex; align-items: center; padding: 0 14px; border-bottom: 1px solid #181818; font-size: 13px; font-weight: 700; gap: 8px; flex-shrink: 0; }
        .col-body { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 10px; }
        .col-body::-webkit-scrollbar { width: 3px; }
        .col-body::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 2px; }
        .col-new  .col-hd { background: rgba(249,115,22,.07); color: #fb923c; }
        .col-prep .col-hd { background: rgba(59,130,246,.07); color: #60a5fa; }
        .col-rdy  .col-hd { background: rgba(34,197,94,.07);  color: #4ade80; }
        .col-cnt { margin-left: auto; font-family: monospace; font-size: 12px; opacity: .5; }
        .dot8 { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .dn { background: #f97316; } .dp { background: #3b82f6; } .dr { background: #22c55e; }

        /* CARTES */
        .card { background: #111; border-radius: 12px; border: 1px solid #1d1d1d; overflow: hidden; animation: si .2s ease-out; }
        @keyframes si { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .ctop { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; padding: 8px 12px; border-bottom: 1px solid #1a1a1a; }
        .ctop-paid      { background: rgba(249,115,22,.09); }
        .ctop-confirmed { background: rgba(234,179,8,.09); }
        .ctop-preparing { background: rgba(59,130,246,.09); }
        .ctop-ready     { background: rgba(34,197,94,.09); }
        .cref  { font-size: 15px; font-weight: 900; }
        .cbadge { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
        .bg-paid        { background: #f97316; color: #fff; }
        .bg-confirmed   { background: #eab308; color: #000; }
        .bg-preparing   { background: #3b82f6; color: #fff; }
        .bg-ready       { background: #22c55e; color: #fff; }
        .ctbl { font-size: 11px; background: #222; padding: 2px 8px; border-radius: 20px; font-weight: 700; }
        .ctmr { font-size: 11px; font-weight: 700; margin-left: auto; }
        .tok { color: #6b7280; } .twarn { color: #eab308; } .tlate { color: #ef4444; }
        .cbody { padding: 10px 12px; }
        .cust  { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #d1d5db; margin-bottom: 8px; }
        .ctype { font-size: 11px; color: #6b7280; font-weight: 400; }
        .item  { display: flex; gap: 8px; margin-bottom: 5px; }
        .iqty  { font-size: 13px; font-weight: 900; min-width: 22px; }
        .iname { font-size: 13px; color: #e5e7eb; }
        .iopt  { display: inline-block; font-size: 10px; background: #1e1e1e; color: #9ca3af; padding: 1px 5px; border-radius: 3px; margin: 2px 2px 0 0; }
        .inote { font-size: 11px; color: #fbbf24; background: rgba(251,191,36,.1); padding: 2px 6px; border-radius: 4px; margin-top: 3px; }
        .cbtn  { width: 100%; padding: 10px; border: none; border-radius: 10px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; margin-top: 10px; }
        .cbtn:active { transform: scale(.97); }
        .cbtn:disabled { opacity: .5; cursor: wait; }
        .bc { background: #f97316; color: #fff; }
        .bp { background: #eab308; color: #000; }
        .br { background: #22c55e; color: #fff; }
        .rdy-lbl { text-align: center; font-size: 12px; color: #4ade80; font-weight: 700; padding: 6px; background: rgba(34,197,94,.07); border-radius: 8px; margin-top: 10px; }
        .empty { display: flex; align-items: center; justify-content: center; height: 120px; color: #222; font-size: 13px; }

        /* ALERTE */
        #alrt { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.75); z-index: 150; align-items: center; justify-content: center; }
        #alrt.on { display: flex; }
        #alrt-box { background: #f97316; border-radius: 22px; padding: 36px 28px; text-align: center; max-width: 300px; width: 90%; animation: pop .25s ease-out; }
        @keyframes pop { from{opacity:0;transform:scale(.82)} to{opacity:1;transform:scale(1)} }
        #alrt-box .ai { font-size: 48px; margin-bottom: 6px; }
        #alrt-box .at { font-size: 24px; font-weight: 900; color: #fff; }
        #alrt-box .ad { font-size: 13px; color: rgba(255,255,255,.85); margin-top: 4px; }
        #alrt-box .ah { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 14px; }
    </style>
</head>
<body>

<div id="splash">
    <div id="splash-icon">🍳</div>
    <h2>Écran Cuisine</h2>
    <p>{{ $restaurant->name }}</p>
    <button id="splash-btn" onclick="kdsStart()">▶&nbsp;&nbsp;Démarrer</button>
</div>

<header id="header">
    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        @if($restaurant->logo_url ?? null)
            <img src="{{ $restaurant->logo_url }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        @endif
        <h1>{{ $restaurant->name }}</h1>
    </div>
    <div class="hd-right">
        <div id="dot"></div>
        <button class="iBtn" id="btnV" onclick="toggleVoice()" title="Synthèse vocale">
            <svg id="vOn"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            <svg id="vOff" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/></svg>
        </button>
        <button class="iBtn" id="btnS" onclick="toggleSound()" title="Son">
            <svg id="sOn"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/></svg>
            <svg id="sOff" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
        </button>
        <span id="clk"></span>
    </div>
</header>

<div id="main">
    <div class="col col-new">
        <div class="col-hd"><span class="dot8 dn"></span>Nouvelles<span class="col-cnt" id="cn">0</span></div>
        <div class="col-body" id="col-n"></div>
    </div>
    <div class="col col-prep">
        <div class="col-hd"><span class="dot8 dp"></span>En préparation<span class="col-cnt" id="cp">0</span></div>
        <div class="col-body" id="col-p"></div>
    </div>
    <div class="col col-rdy">
        <div class="col-hd"><span class="dot8 dr"></span>Prêtes<span class="col-cnt" id="cr">0</span></div>
        <div class="col-body" id="col-r"></div>
    </div>
</div>

<div id="alrt" onclick="closeAlrt()">
    <div id="alrt-box">
        <div class="ai">🔔</div>
        <div class="at">Nouvelle commande !</div>
        <div class="ad" id="alrt-d"></div>
        <div class="ah">Touchez pour fermer</div>
    </div>
</div>

<script>
(function(){
    var TOKEN    = '{{ $token }}';
    var CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    var INITIAL  = @json($ordersJson);

    var audioCtx  = null;
    var soundOn   = true;
    var voiceOn   = true;
    var knownIds  = {};
    var alrtTimer = null;
    var polling   = false;

    /* ══ SPLASH ══════════════════════════════════════════ */
    window.kdsStart = function() {
        // AudioContext DOIT être créé ici, dans le gestionnaire du clic
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch(e){}

        if ('speechSynthesis' in window) window.speechSynthesis.getVoices();

        var s = document.getElementById('splash');
        s.classList.add('gone');
        setTimeout(function(){ s.style.display = 'none'; }, 380);

        startClock();
        watchNet();
        wakeLock();
        startPolling();
    };

    /* ══ DONNÉES INITIALES ═══════════════════════════════ */
    // Marque les commandes déjà présentes au chargement
    INITIAL.forEach(function(o){ knownIds[o.id] = true; });

    /* ══ RENDU INITIAL (sans attendre le splash) ═════════ */
    render(INITIAL);

    /* ══ POLLING ═════════════════════════════════════════ */
    function startPolling() {
        if (polling) return;
        polling = true;
        fetch('/cuisine/' + TOKEN + '/data').then(handleData).catch(function(){ setDot(false); });
        setInterval(function(){
            fetch('/cuisine/' + TOKEN + '/data').then(handleData).catch(function(){ setDot(false); });
        }, 5000);
    }

    function handleData(r) {
        setDot(r.ok);
        if (!r.ok) return;
        r.json().then(function(data) {
            data.orders.forEach(function(o){
                if (!knownIds[o.id]) {
                    knownIds[o.id] = true;
                    announceOrder(o);
                }
            });
            render(data.orders);
        });
    }

    /* ══ ACTION BOUTONS ══════════════════════════════════ */
    window.act = function(id, action, btn) {
        btn.disabled = true;
        fetch('/cuisine/' + TOKEN + '/orders/' + id + '/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ action: action })
        }).then(function(r){
            if (r.ok) fetch('/cuisine/' + TOKEN + '/data').then(handleData);
            else btn.disabled = false;
        }).catch(function(){ btn.disabled = false; });
    };

    /* ══ RENDU ═══════════════════════════════════════════ */
    function h(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function tc(min) { return min > 20 ? 'tlate' : min > 10 ? 'twarn' : 'tok'; }

    function itemsHtml(items) {
        return items.map(function(it){
            var opts = (it.options||[]).map(function(o){
                return '<span class="iopt">' + h(typeof o==='string'?o:(o.name||'')) + '</span>';
            }).join('');
            var note = it.instructions ? '<div class="inote">&#9888; '+h(it.instructions)+'</div>' : '';
            return '<div class="item"><span class="iqty">'+h(it.quantity)+'x</span>'
                +'<div><span class="iname">'+h(it.name)+'</span>'
                +(opts?'<div style="display:flex;flex-wrap:wrap;gap:2px;margin-top:3px;">'+opts+'</div>':'')
                +note+'</div></div>';
        }).join('');
    }

    function cardHtml(o) {
        var id = parseInt(o.id, 10);
        var st = o.status;
        var min = Math.round(o.minutes_ago);
        var tbl = o.table_number ? '<span class="ctbl">Table '+h(o.table_number)+'</span>' : '';
        var top = '<div class="ctop ctop-'+h(st)+'">'
            +'<span class="cref">#'+h(o.reference)+'</span>'
            +'<span class="cbadge bg-'+h(st)+'">'
            +(st==='paid'?'NOUVELLE':st==='confirmed'?'CONFIRM&#201;E':st==='preparing'?'EN COURS':'PR&#202;T')
            +'</span>'+tbl
            +'<span class="ctmr '+tc(min)+'">'+min+'min</span>'
            +'</div>';
        var body = '<div class="cbody">'
            +'<div class="cust"><span>'+h(o.customer_name||'Client')+'</span>'
            +'<span class="ctype">'+h(o.type)+'</span></div>'
            +itemsHtml(o.items);
        if (st === 'paid')
            body += '<button class="cbtn bc" onclick="act('+id+',\'confirm\',this)">&#10003; Confirmer</button>';
        else if (st === 'confirmed')
            body += '<button class="cbtn bp" onclick="act('+id+',\'prepare\',this)">&#127859; Commencer</button>';
        else if (st === 'preparing')
            body += '<button class="cbtn br" onclick="act('+id+',\'ready\',this)">&#9989; Pr&#234;t &#8212; Servir !</button>';
        else
            body += '<div class="rdy-lbl">En attente d\'un serveur</div>';
        body += '</div>';
        return top + body;
    }

    function render(orders) {
        var byStatus = { new: [], prep: [], rdy: [] };
        orders.forEach(function(o){
            if (o.status==='paid'||o.status==='confirmed') byStatus.new.push(o);
            else if (o.status==='preparing') byStatus.prep.push(o);
            else if (o.status==='ready') byStatus.rdy.push(o);
        });
        fill('col-n', byStatus.new,  'cn');
        fill('col-p', byStatus.prep, 'cp');
        fill('col-r', byStatus.rdy,  'cr');
    }

    function fill(colId, orders, cntId) {
        var col = document.getElementById(colId);
        document.getElementById(cntId).textContent = orders.length;
        if (!orders.length) {
            col.innerHTML = '<div class="empty">Aucune commande</div>';
            return;
        }
        col.innerHTML = orders.map(function(o){
            return '<div class="card" id="card-'+parseInt(o.id,10)+'">'+cardHtml(o)+'</div>';
        }).join('');
    }

    /* ══ ALERTE ══════════════════════════════════════════ */
    function announceOrder(o) {
        var detail = [];
        if (o.table_number) detail.push('Table ' + o.table_number);
        var dishes = o.items.map(function(i){ return i.quantity+'x '+i.name; }).join(', ');
        if (dishes) detail.push(dishes);
        document.getElementById('alrt-d').textContent = detail.join(' — ');
        document.getElementById('alrt').classList.add('on');
        clearTimeout(alrtTimer);
        alrtTimer = setTimeout(closeAlrt, 7000);
        playSound();
        speak(o);
    }

    window.closeAlrt = function() {
        document.getElementById('alrt').classList.remove('on');
        clearTimeout(alrtTimer);
    };

    /* ══ SON ═════════════════════════════════════════════ */
    function playSound() {
        if (!soundOn || !audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            [880,1100,1320,880].forEach(function(freq,i){
                setTimeout(function(){
                    var o = audioCtx.createOscillator();
                    var g = audioCtx.createGain();
                    o.connect(g); g.connect(audioCtx.destination);
                    o.frequency.value = freq;
                    o.type = i===3 ? 'triangle' : 'sine';
                    var d = i===3 ? .5 : .28;
                    g.gain.setValueAtTime(i===3?.22:.35, audioCtx.currentTime);
                    g.gain.exponentialRampToValueAtTime(.001, audioCtx.currentTime+d);
                    o.start(audioCtx.currentTime); o.stop(audioCtx.currentTime+d);
                }, i*130);
            });
        } catch(e){}
    }

    /* ══ SYNTHÈSE VOCALE ═════════════════════════════════ */
    function speak(o) {
        if (!voiceOn || !('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var t = 'Nouvelle commande ' + (o.reference||'').replace('#','') + '. ';
        if (o.table_number) t += 'Table ' + o.table_number + '. ';
        t += o.items.map(function(i){ return (i.quantity>1?i.quantity+' ':'')+i.name; }).join(', ') + '.';
        var u = new SpeechSynthesisUtterance(t);
        u.lang='fr-FR'; u.rate=.88; u.pitch=1; u.volume=1;
        var fr = window.speechSynthesis.getVoices().find(function(v){ return v.lang.startsWith('fr'); });
        if (fr) u.voice = fr;
        setTimeout(function(){ window.speechSynthesis.speak(u); }, 450);
    }

    /* ══ TOGGLES ═════════════════════════════════════════ */
    window.toggleVoice = function(){
        voiceOn = !voiceOn;
        document.getElementById('btnV').classList.toggle('off',!voiceOn);
        document.getElementById('vOn').style.display  = voiceOn?'':'none';
        document.getElementById('vOff').style.display = voiceOn?'none':'';
        if (!voiceOn) window.speechSynthesis.cancel();
    };
    window.toggleSound = function(){
        soundOn = !soundOn;
        document.getElementById('btnS').classList.toggle('off',!soundOn);
        document.getElementById('sOn').style.display  = soundOn?'':'none';
        document.getElementById('sOff').style.display = soundOn?'none':'';
    };

    /* ══ HORLOGE ═════════════════════════════════════════ */
    function startClock(){
        function tick(){ var e=document.getElementById('clk'); if(e) e.textContent=new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }
        tick(); setInterval(tick,1000);
    }

    /* ══ RÉSEAU ══════════════════════════════════════════ */
    function setDot(ok){ var d=document.getElementById('dot'); if(d){d.className=ok?'':'red';d.id='dot';} }
    function watchNet(){
        window.addEventListener('online', function(){ setDot(true); });
        window.addEventListener('offline',function(){ setDot(false); });
        setDot(navigator.onLine);
    }

    /* ══ WAKE LOCK ═══════════════════════════════════════ */
    function wakeLock(){
        if(!('wakeLock' in navigator)) return;
        navigator.wakeLock.request('screen').catch(function(){});
        document.addEventListener('visibilitychange',function(){
            if(document.visibilityState==='visible') navigator.wakeLock.request('screen').catch(function(){});
        });
    }

    /* ══ VOIX ASYNC CHROME ═══════════════════════════════ */
    if('speechSynthesis' in window){
        window.speechSynthesis.addEventListener('voiceschanged',function(){ window.speechSynthesis.getVoices(); });
    }

})();
</script>
</body>
</html>
