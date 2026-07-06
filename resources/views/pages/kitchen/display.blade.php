<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Cuisine — {{ $restaurant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0a0a0a; color: #fff; height: 100vh; overflow: hidden; user-select: none; -webkit-tap-highlight-color: transparent; }

        /* ── SPLASH ────────────────────────────────────────── */
        #splash { position: fixed; inset: 0; z-index: 200; background: #0a0a0a; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; transition: opacity .3s; }
        #splash.gone { opacity: 0; pointer-events: none; }
        #splash-icon { font-size: 64px; }
        #splash h2 { font-size: 22px; font-weight: 900; color: #f97316; }
        #splash p  { font-size: 14px; color: #6b7280; }
        #splash-btn { padding: 14px 40px; background: #f97316; color: #fff; border: none; border-radius: 14px; font-size: 17px; font-weight: 900; cursor: pointer; }
        #splash-btn:active { opacity: .85; transform: scale(.97); }

        /* ── HEADER ────────────────────────────────────────── */
        #header { height: 52px; background: #f97316; display: flex; align-items: center; justify-content: space-between; padding: 0 14px; gap: 10px; flex-shrink: 0; }
        #header h1 { font-size: 14px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0; }
        .hd-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .iBtn { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .iBtn:hover { background: rgba(255,255,255,.15); }
        .iBtn.off { opacity: .28; }
        #dot { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; }
        #dot.red { background: #ef4444; }
        #clk { font-size: 11px; font-family: monospace; opacity: .65; }

        /* ── TABS MOBILE ───────────────────────────────────── */
        #tabs { display: none; background: #111; border-bottom: 1px solid #1a1a1a; }
        #tabs button { flex: 1; padding: 10px 4px; background: transparent; border: none; color: #6b7280; font-size: 12px; font-weight: 700; cursor: pointer; border-bottom: 2px solid transparent; }
        #tabs button.active { color: #f97316; border-bottom-color: #f97316; }
        #tabs .tb { display: flex; font-size: 11px; justify-content: center; gap: 4px; }
        #tabs .tb-cnt { background: #f97316; color: #fff; border-radius: 10px; padding: 1px 6px; font-weight: 900; }
        #tabs .tb-cnt.prep { background: #3b82f6; }
        #tabs .tb-cnt.rdy  { background: #22c55e; }

        /* ── LAYOUT ────────────────────────────────────────── */
        #main { display: flex; height: calc(100vh - 52px); }
        .col { flex: 1; display: flex; flex-direction: column; border-right: 1px solid #181818; min-width: 0; }
        .col:last-child { border-right: none; }
        .col-hd { height: 38px; display: flex; align-items: center; padding: 0 12px; border-bottom: 1px solid #181818; font-size: 13px; font-weight: 700; gap: 7px; flex-shrink: 0; }
        .col-body { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 8px; }
        .col-body::-webkit-scrollbar { width: 3px; }
        .col-body::-webkit-scrollbar-thumb { background: #222; border-radius: 2px; }
        .col-new  .col-hd { background: rgba(249,115,22,.07); color: #fb923c; }
        .col-prep .col-hd { background: rgba(59,130,246,.07); color: #60a5fa; }
        .col-rdy  .col-hd { background: rgba(34,197,94,.07);  color: #4ade80; }
        .col-cnt { margin-left: auto; font-family: monospace; font-size: 12px; opacity: .5; }
        .d8 { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .dn { background: #f97316; } .dp { background: #3b82f6; } .dr { background: #22c55e; }

        /* ── CARTES ─────────────────────────────────────────
           flex-shrink:0 OBLIGATOIRE — sans ça, 44 cartes dans
           une col flex se compriment à quelques pixels chacune */
        .card { background: #111; border-radius: 11px; border: 1px solid #1d1d1d; overflow: hidden; opacity: 1; flex-shrink: 0; }
        .ctop { display: flex; align-items: center; flex-wrap: wrap; gap: 5px; padding: 8px 11px; border-bottom: 1px solid #1a1a1a; }
        .ctop-paid      { background: rgba(249,115,22,.09); }
        .ctop-confirmed { background: rgba(234,179,8,.09); }
        .ctop-preparing { background: rgba(59,130,246,.09); }
        .ctop-ready     { background: rgba(34,197,94,.09); }
        .cref   { font-size: 14px; font-weight: 900; }
        .cbadge { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 5px; border-radius: 4px; white-space: nowrap; }
        .bg-paid        { background: #f97316; color: #fff; }
        .bg-confirmed   { background: #eab308; color: #000; }
        .bg-preparing   { background: #3b82f6; color: #fff; }
        .bg-ready       { background: #22c55e; color: #fff; }
        .ctbl  { font-size: 11px; background: #222; padding: 2px 7px; border-radius: 20px; font-weight: 700; white-space: nowrap; }
        .ctmr  { font-size: 11px; font-weight: 700; margin-left: auto; white-space: nowrap; }
        .tok   { color: #6b7280; } .twarn { color: #eab308; } .tlate { color: #ef4444; }
        .cbody { padding: 9px 11px; }
        .cust  { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #d1d5db; margin-bottom: 7px; gap: 6px; }
        .ctype { font-size: 11px; color: #6b7280; font-weight: 400; flex-shrink: 0; }
        .item  { display: flex; gap: 7px; margin-bottom: 4px; align-items: flex-start; }
        .iqty  { font-size: 13px; font-weight: 900; min-width: 20px; flex-shrink: 0; }
        .iname { font-size: 13px; color: #e5e7eb; }
        .iopt  { display: inline-block; font-size: 10px; background: #1e1e1e; color: #9ca3af; padding: 1px 4px; border-radius: 3px; margin: 2px 2px 0 0; }
        .inote { font-size: 11px; color: #fbbf24; background: rgba(251,191,36,.1); padding: 2px 5px; border-radius: 3px; margin-top: 2px; }
        .cbtn  { display: block; width: 100%; padding: 10px; border: none; border-radius: 9px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; cursor: pointer; margin-top: 9px; }
        .cbtn:active { opacity: .82; transform: scale(.97); }
        .cbtn:disabled { opacity: .45; cursor: wait; }
        .bc { background: #f97316; color: #fff; }
        .bp { background: #eab308; color: #000; }
        .br { background: #22c55e; color: #fff; }
        .rdy-lbl { text-align: center; font-size: 12px; color: #4ade80; font-weight: 700; padding: 8px; background: rgba(34,197,94,.07); border-radius: 8px; margin-top: 9px; }
        .empty { display: flex; align-items: center; justify-content: center; height: 100px; color: #252525; font-size: 13px; }

        /* ── ALERTE ────────────────────────────────────────── */
        #alrt { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.78); z-index: 150; align-items: center; justify-content: center; }
        #alrt.on { display: flex; }
        #alrt-box { background: #f97316; border-radius: 20px; padding: 32px 26px; text-align: center; max-width: 290px; width: 90%; }
        #alrt-box .ai { font-size: 44px; margin-bottom: 6px; }
        #alrt-box .at { font-size: 22px; font-weight: 900; color: #fff; }
        #alrt-box .ad { font-size: 13px; color: rgba(255,255,255,.88); margin-top: 4px; min-height: 18px; }
        #alrt-box .ah { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 12px; }

        /* ── RESPONSIVE ─────────────────────────────────────── */
        @media (max-width: 767px) {
            #tabs { display: flex; }
            #main { height: calc(100vh - 52px - 42px); }
            .col { display: none; }
            .col.active { display: flex; flex: 1; border-right: none; }
        }
        @media (min-width: 768px) and (max-width: 1023px) {
            .cref  { font-size: 13px; }
            .iname { font-size: 12px; }
            .cbtn  { font-size: 12px; padding: 9px; }
        }
    </style>
</head>
<body>

{{-- SPLASH --}}
<div id="splash">
    <div id="splash-icon">🍳</div>
    <h2>Écran Cuisine</h2>
    <p>{{ $restaurant->name }}</p>
    <button id="splash-btn" onclick="kdsStart()">▶&nbsp;&nbsp;Démarrer</button>
</div>

{{-- HEADER --}}
<header id="header">
    <div style="display:flex;align-items:center;gap:9px;min-width:0;flex:1;">
        @if($restaurant->logo_url ?? null)
            <img src="{{ $restaurant->logo_url }}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        @endif
        <h1>{{ $restaurant->name }}</h1>
    </div>
    <div class="hd-right">
        <div id="dot"></div>
        <button class="iBtn" id="btnV" onclick="toggleVoice()" title="Synthèse vocale">
            <svg id="vOn"  width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            <svg id="vOff" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z"/></svg>
        </button>
        <button class="iBtn" id="btnS" onclick="toggleSound()" title="Son">
            <svg id="sOn"  width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6 9H4a1 1 0 00-1 1v4a1 1 0 001 1h2l4 4V5L6 9z"/></svg>
            <svg id="sOff" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
        </button>
        <span id="clk"></span>
    </div>
</header>

{{-- TABS (mobile uniquement) --}}
<div id="tabs">
    <button id="tab-n" class="active" onclick="showTab('n')">
        <div class="tb">Nouvelles <span id="tc-n" class="tb-cnt">0</span></div>
    </button>
    <button id="tab-p" onclick="showTab('p')">
        <div class="tb">En prép. <span id="tc-p" class="tb-cnt prep">0</span></div>
    </button>
    <button id="tab-r" onclick="showTab('r')">
        <div class="tb">Prêtes <span id="tc-r" class="tb-cnt rdy">0</span></div>
    </button>
</div>

{{-- COLONNES --}}
<div id="main">
    <div class="col col-new active" id="col-wrap-n">
        <div class="col-hd"><span class="d8 dn"></span>Nouvelles<span class="col-cnt" id="cn">0</span></div>
        <div class="col-body" id="col-n"></div>
    </div>
    <div class="col col-prep" id="col-wrap-p">
        <div class="col-hd"><span class="d8 dp"></span>En préparation<span class="col-cnt" id="cp">0</span></div>
        <div class="col-body" id="col-p"></div>
    </div>
    <div class="col col-rdy" id="col-wrap-r">
        <div class="col-hd"><span class="d8 dr"></span>Prêtes<span class="col-cnt" id="cr">0</span></div>
        <div class="col-body" id="col-r"></div>
    </div>
</div>

{{-- ALERTE --}}
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
    var TOKEN   = '{{ $token }}';
    var CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    // JSON encodé en base64 pour éviter tout conflit avec quotes/caractères spéciaux
    var INITIAL = JSON.parse(atob('{{ base64_encode(json_encode($ordersJson)) }}'));

    var audioCtx  = null;
    var soundOn   = true;
    var voiceOn   = true;
    var knownIds  = {};
    var alrtTimer = null;
    var firstRender = true;

    /* ══ INIT ══════════════════════════════════════════════
       Rendu + polling démarrent immédiatement.
    ══════════════════════════════════════════════════════ */
    INITIAL.forEach(function(o){ knownIds[o.id] = true; });
    render(INITIAL);
    firstRender = false;

    fetch('/cuisine/'+TOKEN+'/data').then(handleData).catch(function(){ setDot(false); });
    setInterval(function(){
        fetch('/cuisine/'+TOKEN+'/data').then(handleData).catch(function(){ setDot(false); });
    }, 5000);

    startClock();
    watchNet();

    /* ══ SPLASH — uniquement pour déverrouiller AudioContext ══ */
    window.kdsStart = function() {
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch(e){}
        if ('speechSynthesis' in window) window.speechSynthesis.getVoices();
        var s = document.getElementById('splash');
        s.classList.add('gone');
        setTimeout(function(){ s.style.display='none'; }, 350);
        wakeLock();
    };

    /* ══ TABS MOBILE ══════════════════════════════════════ */
    window.showTab = function(t) {
        ['n','p','r'].forEach(function(id){
            document.getElementById('col-wrap-'+id).classList.toggle('active', id===t);
            document.getElementById('tab-'+id).classList.toggle('active', id===t);
        });
    };

    /* ══ DONNÉES ══════════════════════════════════════════ */
    function handleData(r) {
        setDot(r.ok);
        if (!r.ok) return;
        r.json().then(function(data){
            data.orders.forEach(function(o){
                if (!knownIds[o.id]) { knownIds[o.id]=true; announceOrder(o); }
            });
            render(data.orders);
        });
    }

    /* ══ ACTION BOUTONS ═══════════════════════════════════ */
    window.act = function(id, action, btn) {
        btn.disabled = true;
        fetch('/cuisine/'+TOKEN+'/orders/'+id+'/status', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({action:action})
        }).then(function(r){
            if (r.ok) fetch('/cuisine/'+TOKEN+'/data').then(handleData);
            else btn.disabled = false;
        }).catch(function(){ btn.disabled=false; });
    };

    /* ══ RENDU ════════════════════════════════════════════ */
    function h(s) {
        return String(s==null?'':s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
    function tc(m){ return m>20?'tlate':m>10?'twarn':'tok'; }

    function itemsHtml(items) {
        if (!items||!items.length) return '';
        return items.map(function(it){
            var opts = (it.options||[]).map(function(o){
                var label = typeof o==='string'?o:(o&&o.name?o.name:'');
                return label ? '<span class="iopt">'+h(label)+'</span>' : '';
            }).join('');
            var note = it.instructions ? '<div class="inote">&#9888; '+h(it.instructions)+'</div>' : '';
            return '<div class="item">'
                +'<span class="iqty">'+h(it.quantity)+'x</span>'
                +'<div><span class="iname">'+h(it.name||'Plat')+'</span>'
                +(opts?'<div style="display:flex;flex-wrap:wrap;gap:2px;margin-top:2px;">'+opts+'</div>':'')
                +note+'</div></div>';
        }).join('');
    }

    function cardHtml(o, isNew) {
        var id  = parseInt(o.id, 10);
        var st  = String(o.status||'');
        var min = Math.round(Number(o.minutes_ago)||0);
        var tbl = o.table_number ? '<span class="ctbl">Table '+h(o.table_number)+'</span>' : '';
        var badgeLabel = st==='paid' ? 'NOUVELLE' : st==='confirmed' ? 'CONFIRM&#201;E' : st==='preparing' ? 'EN COURS' : 'PR&#202;T';

        var html = '<div class="card'+(isNew?' new-anim':'')+'" id="card-'+id+'">'
            +'<div class="ctop ctop-'+h(st)+'">'
            +'<span class="cref">#'+h(o.reference)+'</span>'
            +'<span class="cbadge bg-'+h(st)+'">'+badgeLabel+'</span>'
            +tbl
            +'<span class="ctmr '+tc(min)+'">'+min+'min</span>'
            +'</div>'
            +'<div class="cbody">'
            +'<div class="cust"><span>'+h(o.customer_name||'Client')+'</span>'
            +'<span class="ctype">'+h(o.type||'')+'</span></div>'
            +itemsHtml(o.items);

        if      (st==='paid')      html += '<button class="cbtn bc" onclick="act('+id+',\'confirm\',this)">&#10003; Confirmer</button>';
        else if (st==='confirmed') html += '<button class="cbtn bp" onclick="act('+id+',\'prepare\',this)">&#127859; Commencer</button>';
        else if (st==='preparing') html += '<button class="cbtn br" onclick="act('+id+',\'ready\',this)">&#9989; Pr&#234;t &#8212; Servir !</button>';
        else                       html += '<div class="rdy-lbl">En attente d\'un serveur</div>';

        html += '</div></div>';
        return html;
    }

    function render(orders) {
        var grp = {n:[],p:[],r:[]};
        (orders||[]).forEach(function(o){
            var st = String(o.status||'');
            if (st==='paid'||st==='confirmed') grp.n.push(o);
            else if (st==='preparing')          grp.p.push(o);
            else if (st==='ready')              grp.r.push(o);
        });
        fill('col-n','cn','tc-n', grp.n);
        fill('col-p','cp','tc-p', grp.p);
        fill('col-r','cr','tc-r', grp.r);
    }

    function fill(colId, cntId, tabCntId, orders) {
        var col = document.getElementById(colId);
        var cnt = orders.length;
        document.getElementById(cntId).textContent    = cnt;
        document.getElementById(tabCntId).textContent = cnt;

        if (!cnt) {
            col.innerHTML = '<div class="empty">Aucune commande</div>';
            return;
        }

        // Récupère les IDs déjà affichés pour savoir quelles cartes sont nouvelles
        var existing = {};
        col.querySelectorAll('.card[id]').forEach(function(el){ existing[el.id] = true; });

        col.innerHTML = orders.map(function(o){
            var cid = 'card-'+parseInt(o.id,10);
            // firstRender = true → jamais d'animation sur le chargement initial
            var isNew = !firstRender && !existing[cid];
            return cardHtml(o, isNew);
        }).join('');
    }

    /* ══ ALERTE NOUVELLE COMMANDE ═════════════════════════ */
    function announceOrder(o) {
        var parts = [];
        if (o.table_number) parts.push('Table '+o.table_number);
        var dishes = (o.items||[]).map(function(i){ return i.quantity+'x '+i.name; }).join(', ');
        if (dishes) parts.push(dishes);
        document.getElementById('alrt-d').textContent = parts.join(' — ');
        document.getElementById('alrt').classList.add('on');
        clearTimeout(alrtTimer);
        alrtTimer = setTimeout(closeAlrt, 7000);
        playSound();
        speak(o);
    }
    window.closeAlrt = function(){ document.getElementById('alrt').classList.remove('on'); clearTimeout(alrtTimer); };

    /* ══ SON ══════════════════════════════════════════════ */
    function playSound() {
        if (!soundOn || !audioCtx) return;
        try {
            if (audioCtx.state==='suspended') audioCtx.resume();
            [880,1100,1320,880].forEach(function(freq,i){
                setTimeout(function(){
                    var o=audioCtx.createOscillator(), g=audioCtx.createGain();
                    o.connect(g); g.connect(audioCtx.destination);
                    o.frequency.value=freq; o.type=i===3?'triangle':'sine';
                    var d=i===3?.5:.28;
                    g.gain.setValueAtTime(i===3?.22:.35,audioCtx.currentTime);
                    g.gain.exponentialRampToValueAtTime(.001,audioCtx.currentTime+d);
                    o.start(audioCtx.currentTime); o.stop(audioCtx.currentTime+d);
                },i*130);
            });
        } catch(e){}
    }

    /* ══ SYNTHÈSE VOCALE ══════════════════════════════════ */
    var EL_ENABLED = {{ \App\Models\SystemSetting::get('elevenlabs_api_key') ? 'true' : 'false' }};

    function speak(o) {
        if (!voiceOn) return;
        var t = 'Nouvelle commande ' + (o.reference||'').replace('#','') + '. ';
        if (o.table_number) t += 'Table ' + o.table_number + '. ';
        t += (o.items||[]).map(function(i){ return (i.quantity>1 ? i.quantity+' ' : '') + i.name; }).join(', ') + '.';

        if (EL_ENABLED && audioCtx) {
            // ElevenLabs via proxy — lecture via AudioContext (déjà déverrouillé au splash)
            // new Audio().play() est bloqué par autoplay hors geste utilisateur,
            // audioCtx.decodeAudioData + BufferSource fonctionne car audioCtx est "running"
            fetch('/cuisine/' + TOKEN + '/tts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ text: t })
            })
            .then(function(r) { return r.ok ? r.arrayBuffer() : null; })
            .then(function(buf) {
                if (!buf) return;
                if (audioCtx.state === 'suspended') audioCtx.resume();
                audioCtx.decodeAudioData(buf, function(decoded) {
                    var src = audioCtx.createBufferSource();
                    src.buffer = decoded;
                    src.connect(audioCtx.destination);
                    src.start(0);
                });
            })
            .catch(function(){});
        } else if ('speechSynthesis' in window) {
            // Fallback : voix navigateur si ElevenLabs non configuré
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(t);
            u.lang='fr-FR'; u.rate=.88; u.pitch=1; u.volume=1;
            var fr = window.speechSynthesis.getVoices().find(function(v){ return v.lang.startsWith('fr'); });
            if (fr) u.voice = fr;
            setTimeout(function(){ window.speechSynthesis.speak(u); }, 400);
        }
    }

    /* ══ TOGGLES ══════════════════════════════════════════ */
    window.toggleVoice=function(){
        voiceOn=!voiceOn;
        document.getElementById('btnV').classList.toggle('off',!voiceOn);
        document.getElementById('vOn').style.display=voiceOn?'':'none';
        document.getElementById('vOff').style.display=voiceOn?'none':'';
        if (!voiceOn && !EL_ENABLED && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    };
    window.toggleSound=function(){
        soundOn=!soundOn;
        document.getElementById('btnS').classList.toggle('off',!soundOn);
        document.getElementById('sOn').style.display=soundOn?'':'none';
        document.getElementById('sOff').style.display=soundOn?'none':'';
    };

    /* ══ HORLOGE ══════════════════════════════════════════ */
    function startClock(){
        function tick(){ var e=document.getElementById('clk'); if(e) e.textContent=new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }
        tick(); setInterval(tick,1000);
    }

    /* ══ RÉSEAU ═══════════════════════════════════════════ */
    function setDot(ok){ var d=document.getElementById('dot'); if(d){d.className=ok?'':'red';d.id='dot';} }
    function watchNet(){
        window.addEventListener('online',function(){ setDot(true); });
        window.addEventListener('offline',function(){ setDot(false); });
        setDot(navigator.onLine);
    }

    /* ══ WAKE LOCK ════════════════════════════════════════ */
    function wakeLock(){
        if(!('wakeLock' in navigator)) return;
        navigator.wakeLock.request('screen').catch(function(){});
        document.addEventListener('visibilitychange',function(){
            if(document.visibilityState==='visible') navigator.wakeLock.request('screen').catch(function(){});
        });
    }

    if('speechSynthesis' in window){
        window.speechSynthesis.addEventListener('voiceschanged',function(){ window.speechSynthesis.getVoices(); });
    }

})();
</script>
</body>
</html>
