<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Personnel — {{ $restaurant->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0a0a0a; color: #fff; min-height: 100vh; user-select: none; -webkit-tap-highlight-color: transparent; }

        /* SPLASH */
        #splash { position: fixed; inset: 0; z-index: 200; background: #0a0a0a; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
        #splash.gone { display: none; }
        #splash-icon { font-size: 60px; }
        #splash h2 { font-size: 22px; font-weight: 900; color: #8b5cf6; }
        #splash p  { font-size: 14px; color: #6b7280; text-align: center; max-width: 280px; }
        #splash-btn { padding: 14px 40px; background: #8b5cf6; color: #fff; border: none; border-radius: 14px; font-size: 17px; font-weight: 900; cursor: pointer; }

        /* HEADER */
        #header { height: 52px; background: #8b5cf6; display: flex; align-items: center; justify-content: space-between; padding: 0 14px; gap: 10px; position: sticky; top: 0; z-index: 10; }
        #header h1 { font-size: 15px; font-weight: 800; }
        #dot { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; }
        #dot.red { background: #ef4444; }
        #clk { font-size: 11px; font-family: monospace; opacity: .65; }

        /* LISTE */
        #list { padding: 14px; display: flex; flex-direction: column; gap: 10px; }

        /* CARTE APPEL */
        .scard { background: #111; border-radius: 14px; border: 1px solid #1d1d1d; overflow: hidden; animation: pulse-border 1.5s ease-in-out 3; }
        @keyframes pulse-border { 0%,100%{ border-color:#1d1d1d; } 50%{ border-color:#8b5cf6; } }
        .scard-top { display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: rgba(139,92,246,.1); border-bottom: 1px solid #1a1a1a; }
        .stype { font-size: 28px; }
        .sinfo { flex: 1; min-width: 0; }
        .stable { font-size: 17px; font-weight: 900; color: #e5e7eb; }
        .slabel { font-size: 13px; color: #a78bfa; font-weight: 600; }
        .sage { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .snotes { padding: 8px 14px; font-size: 13px; color: #fbbf24; border-bottom: 1px solid #1a1a1a; }
        .sbtn { display: block; width: 100%; padding: 12px; border: none; font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; background: #8b5cf6; color: #fff; }
        .sbtn:active { opacity: .82; transform: scale(.98); }
        .sbtn:disabled { opacity: .45; cursor: wait; }

        /* VIDE */
        #empty { text-align: center; padding: 80px 20px; color: #4b5563; }
        #empty .icon { font-size: 52px; margin-bottom: 16px; opacity: .5; }
        #empty p { font-size: 15px; }
    </style>
</head>
<body>

{{-- SPLASH : demande le clic pour déverrouiller l'audio --}}
<div id="splash">
    <div id="splash-icon">🔔</div>
    <h2>{{ $restaurant->name }}</h2>
    <p>Appuyez pour activer les notifications sonores des appels du personnel.</p>
    <button id="splash-btn" onclick="startApp()">Activer les notifications</button>
</div>

<div id="app" style="display:none">
    <div id="header">
        <h1>🔔 Appels du personnel — {{ $restaurant->name }}</h1>
        <div style="display:flex;align-items:center;gap:8px">
            <div id="dot"></div>
            <span id="clk"></span>
        </div>
    </div>

    <div id="list"></div>
    <div id="empty" style="display:none">
        <div class="icon">✅</div>
        <p>Aucun appel en attente</p>
    </div>
</div>

<script>
const TOKEN    = '{{ $token }}';
const TTS_URL  = '{{ route('restaurant.tts') }}';
const CSRF     = '{{ csrf_token() }}';
const DATA_URL = '{{ route('staff.data', $token) }}';
const DONE_URL = '{{ route('staff.done', ['token' => $token, 'id' => '__ID__']) }}';

let audioCtx   = null;
let lastIds    = new Set();
let polling    = null;

// ── DÉMARRAGE ──────────────────────────────────────────────────
function startApp() {
    // Déverrouille l'AudioContext sur interaction utilisateur
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') audioCtx.resume();

    document.getElementById('splash').classList.add('gone');
    document.getElementById('app').style.display = 'block';

    tick();
    setInterval(tick, 5000);
    setInterval(updateClock, 1000);
}

// ── HORLOGE ────────────────────────────────────────────────────
function updateClock() {
    const n = new Date();
    document.getElementById('clk').textContent =
        String(n.getHours()).padStart(2,'0') + ':' + String(n.getMinutes()).padStart(2,'0');
}

// ── POLLING DATA ───────────────────────────────────────────────
async function tick() {
    try {
        const res  = await fetch(DATA_URL);
        const data = await res.json();
        render(data.requests ?? []);

        // Connexion OK
        document.getElementById('dot').classList.remove('red');
    } catch {
        document.getElementById('dot').classList.add('red');
    }
}

// ── RENDU ──────────────────────────────────────────────────────
function render(requests) {
    const list  = document.getElementById('list');
    const empty = document.getElementById('empty');

    if (requests.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
        lastIds = new Set();
        return;
    }

    empty.style.display = 'none';

    // Nouveaux appels = IDs pas encore connus
    const newOnes = requests.filter(r => !lastIds.has(r.id));
    newOnes.forEach(r => {
        beep();
        const msg = 'Appel ' + r.type_label + ' à la ' + r.table
            + (r.notes ? '. Message : ' + r.notes : '');
        speak(msg);
    });
    lastIds = new Set(requests.map(r => r.id));

    // Rebuild DOM
    list.innerHTML = requests.map(r => `
        <div class="scard" id="card-${r.id}">
            <div class="scard-top">
                <span class="stype">${r.type_icon}</span>
                <div class="sinfo">
                    <div class="stable">${r.table}</div>
                    <div class="slabel">${r.type_label}</div>
                    <div class="sage">${r.age}</div>
                </div>
            </div>
            ${r.notes ? `<div class="snotes">📝 ${r.notes}</div>` : ''}
            <button class="sbtn" onclick="markDone(${r.id}, this)">✓ Traité</button>
        </div>
    `).join('');
}

// ── TRAITER ────────────────────────────────────────────────────
async function markDone(id, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    try {
        await fetch(DONE_URL.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        });
        document.getElementById('card-' + id)?.remove();
        lastIds.delete(id);
        if (document.getElementById('list').children.length === 0) {
            document.getElementById('empty').style.display = 'block';
        }
    } catch {
        btn.disabled = false;
        btn.textContent = '✓ Traité';
    }
}

// ── BIPS ───────────────────────────────────────────────────────
function beep() {
    if (!audioCtx) return;
    try {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        [660, 880, 660, 880].forEach((freq, i) => {
            setTimeout(() => {
                const o = audioCtx.createOscillator();
                const g = audioCtx.createGain();
                o.connect(g); g.connect(audioCtx.destination);
                o.frequency.value = freq;
                o.type = 'sine';
                g.gain.setValueAtTime(0.4, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.25);
                o.start(audioCtx.currentTime);
                o.stop(audioCtx.currentTime + 0.25);
            }, i * 150);
        });
    } catch(e) {}
}

// ── TTS ELEVENLABS ─────────────────────────────────────────────
async function speak(text) {
    try {
        const res = await fetch(TTS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ text }),
        });
        if (!res.ok) return;
        const blob = await res.blob();
        const audio = new Audio(URL.createObjectURL(blob));
        audio.play().catch(() => {});
    } catch(e) {}
}
</script>
</body>
</html>
