/**
 * PWA — comportements globaux (toutes les pages qui chargent app.js) :
 *  1. indicateur « Hors ligne » / « Connexion rétablie » ;
 *  2. resynchronisation immédiate au retour du réseau ou au retour au premier plan
 *     (sans attendre le prochain wire:poll) — événement window « menupro-resync » ;
 *  3. bannière « Nouvelle version — Mettre à jour » quand le service worker change.
 *     La page n'est JAMAIS rechargée sans un appui de l'utilisateur (une commande
 *     peut être en attente d'acceptation sur le tableau de bord).
 */

const isStandalone = () =>
    window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;

export const isIos = () =>
    /iphone|ipad|ipod/i.test(navigator.userAgent)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1); // iPadOS se présente comme un Mac

export { isStandalone };

/* ─── Hauteur de la barre de navigation basse (pour placer les bannières au-dessus) ── */
function bottomOffset() {
    const nav = document.querySelector('[data-bottom-nav]');
    if (nav && nav.offsetParent !== null) return nav.getBoundingClientRect().height + 8;
    return 12;
}

function el(tag, attrs = {}, html = '') {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => node.setAttribute(k, v));
    if (html) node.innerHTML = html;
    return node;
}

/* ─── 1. Indicateur hors ligne ─────────────────────────────────────────────── */
let offlineBar = null;
let onlineTimer = null;

function renderConnectivity(online, justBack = false) {
    if (!offlineBar) {
        offlineBar = el('div', {
            id: 'mp-offline-bar',
            role: 'status',
            'aria-live': 'polite',
            style: [
                'position:fixed', 'left:50%', 'transform:translateX(-50%)',
                'top:calc(env(safe-area-inset-top, 0px) + 8px)', 'z-index:300',
                'display:none', 'align-items:center', 'gap:8px',
                'padding:8px 14px', 'border-radius:9999px',
                'font:600 13px/1.3 system-ui,-apple-system,sans-serif', 'color:#fff',
                'box-shadow:0 6px 20px rgba(0,0,0,.25)', 'max-width:calc(100vw - 24px)',
                'pointer-events:none', 'text-align:center',
            ].join(';'),
        });
        document.body.appendChild(offlineBar);
    }
    clearTimeout(onlineTimer);
    if (!online) {
        offlineBar.style.background = '#b91c1c';
        offlineBar.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#fecaca;flex-shrink:0"></span>'
            + '<span>' + offlineMessage() + '</span>';
        offlineBar.style.display = 'flex';
        document.documentElement.classList.add('is-offline');
        return;
    }
    document.documentElement.classList.remove('is-offline');
    if (justBack) {
        offlineBar.style.background = '#15803d';
        offlineBar.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#bbf7d0;flex-shrink:0"></span>'
            + '<span>Connexion rétablie</span>';
        offlineBar.style.display = 'flex';
        onlineTimer = setTimeout(() => { offlineBar.style.display = 'none'; }, 2500);
    } else {
        offlineBar.style.display = 'none';
    }
}

function offlineMessage() {
    const backOffice = ['/dashboard', '/admin', '/crm', '/commando'].some((p) => window.location.pathname.startsWith(p));
    return backOffice
        ? 'Hors ligne — les nouvelles commandes s\'afficheront au retour du réseau'
        : 'Hors ligne — vérifiez votre connexion internet';
}

/* ─── 2. Resynchronisation ─────────────────────────────────────────────────── */
let resyncTimer = null;
let hiddenAt = null;

function refreshPolledLivewireComponents() {
    const Livewire = window.Livewire;
    if (!Livewire?.find) return;
    const seen = new Set();
    document.querySelectorAll('*').forEach((node) => {
        // Les composants qui se rafraîchissent par wire:poll(.Xs)="méthode"
        const attr = Array.from(node.attributes || []).find((a) => a.name.startsWith('wire:poll'));
        if (!attr) return;
        const root = node.closest('[wire\\:id]');
        const id = root?.getAttribute('wire:id');
        if (!id || seen.has(id)) return;
        seen.add(id);
        try {
            const component = Livewire.find(id);
            if (!component) return;
            const method = attr.value && /^[A-Za-z_][\w]*$/.test(attr.value) ? attr.value : null;
            if (method && typeof component[method] === 'function') component[method]();
            else if (typeof component.$refresh === 'function') component.$refresh();
        } catch (e) {
            // composant retiré entre-temps : sans effet
        }
    });
}

function resync(reason) {
    if (!navigator.onLine) return;
    clearTimeout(resyncTimer);
    // Petit délai : regroupe « online » + « visibilitychange » qui arrivent souvent ensemble
    resyncTimer = setTimeout(() => {
        refreshPolledLivewireComponents();
        window.dispatchEvent(new CustomEvent('menupro-resync', { detail: { reason } }));
    }, 400);
}

/* ─── 3. Mise à jour du service worker ─────────────────────────────────────── */
let updateBanner = null;
let lastUpdateCheck = 0;

function showUpdateBanner() {
    if (updateBanner) return;
    updateBanner = el('div', {
        id: 'mp-update-banner',
        role: 'alert',
        style: [
            'position:fixed', 'left:12px', 'right:12px', `bottom:calc(${bottomOffset()}px + env(safe-area-inset-bottom, 0px))`,
            'z-index:250', 'max-width:28rem', 'margin:0 auto',
            'display:flex', 'align-items:center', 'gap:12px',
            'padding:12px 12px 12px 16px', 'border-radius:16px',
            'background:#171717', 'color:#fff', 'box-shadow:0 10px 30px rgba(0,0,0,.3)',
            'font:500 14px/1.35 system-ui,-apple-system,sans-serif',
        ].join(';'),
    }, '<span style="flex:1;min-width:0"><strong style="display:block">Nouvelle version disponible</strong>'
        + '<span style="color:#a3a3a3;font-size:12px">Appuyez pour mettre à jour MenuPro.</span></span>'
        + '<button type="button" data-update style="min-height:44px;padding:0 16px;border-radius:12px;border:0;background:#f97316;color:#fff;font:700 14px system-ui,-apple-system,sans-serif;touch-action:manipulation">Mettre à jour</button>'
        + '<button type="button" data-later aria-label="Plus tard" style="min-width:44px;min-height:44px;border:0;background:transparent;color:#a3a3a3;font-size:22px;line-height:1">×</button>');
    updateBanner.querySelector('[data-update]').addEventListener('click', (e) => {
        e.currentTarget.textContent = 'Mise à jour…';
        e.currentTarget.disabled = true;
        window.location.reload();
    });
    updateBanner.querySelector('[data-later]').addEventListener('click', () => {
        updateBanner.remove();
        updateBanner = null;
    });
    document.body.appendChild(updateBanner);
}

function checkForSwUpdate() {
    if (!('serviceWorker' in navigator) || !navigator.onLine) return;
    const now = Date.now();
    if (now - lastUpdateCheck < 30 * 60 * 1000) return; // au plus toutes les 30 min (connexion faible)
    lastUpdateCheck = now;
    navigator.serviceWorker.getRegistration('/').then((reg) => reg?.update()).catch(() => {});
}

function initSwUpdates() {
    if (!('serviceWorker' in navigator)) return;
    // Pas de contrôleur au chargement = première installation : rien à « mettre à jour »
    let hadController = !!navigator.serviceWorker.controller;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        // Mise à jour arrivée au chargement même de la page : la page est déjà à jour, pas de bannière.
        // Page ouverte depuis longtemps (tableau de bord laissé ouvert) : on propose la mise à jour.
        if (hadController && performance.now() > 30000) showUpdateBanner();
        hadController = true;
    });
    lastUpdateCheck = Date.now(); // le navigateur vérifie déjà au chargement de la page
}

/* ─── 4. Bandeau d'installation (layout components/layouts/app) ────────────── */
const INSTALL_DISMISS_KEY = 'pwa_dismissed';
const INSTALL_DISMISS_MS = 7 * 24 * 3600 * 1000;

function installDismissed() {
    try {
        const at = parseInt(localStorage.getItem(INSTALL_DISMISS_KEY) || '0', 10);
        return at > 0 && Date.now() - at < INSTALL_DISMISS_MS;
    } catch (e) {
        return false;
    }
}

window.mpInstallBanner = () => ({
    showBanner: false,
    iosSteps: false,
    iosSafari: true,
    offset: 12,
    subtitle: 'Accès direct depuis l\'écran d\'accueil, même en connexion faible',
    init() {
        if (isStandalone() || installDismissed()) return;
        const backOffice = window.location.pathname.startsWith('/dashboard');
        if (backOffice) this.subtitle = 'Soyez alerté de chaque commande, même appli fermée';

        const showSoon = () => setTimeout(() => {
            if (isStandalone() || installDismissed()) return;
            this.offset = bottomOffset();
            this.showBanner = true;
        }, 4000);

        if (isIos()) {
            // Tableau de bord : un encart d'installation iPhone est déjà affiché dans la page
            if (backOffice) return;
            const ua = navigator.userAgent;
            this.iosSafari = !/CriOS|FxiOS|EdgiOS|OPiOS|FBAN|FBAV|Instagram|Line\/|WhatsApp|GSA\//i.test(ua);
            this.iosSteps = true;
            showSoon();
            return;
        }

        if (window.__mpInstallPrompt) showSoon();
        else window.addEventListener('mp:installable', showSoon, { once: true });
        window.addEventListener('mp:installed', () => { this.showBanner = false; });
    },
    async install() {
        const prompt = window.__mpInstallPrompt;
        if (!prompt) { this.dismiss(); return; }
        try {
            prompt.prompt();
            const { outcome } = await prompt.userChoice;
            if (outcome === 'accepted') this.showBanner = false;
            else this.dismiss();
        } catch (e) {
            this.dismiss();
        }
        window.__mpInstallPrompt = null; // une invite ne peut servir qu'une fois
    },
    dismiss() {
        try { localStorage.setItem(INSTALL_DISMISS_KEY, Date.now().toString()); } catch (e) { /* navigation privée */ }
        this.showBanner = false;
    },
});

/* ─── Démarrage ────────────────────────────────────────────────────────────── */
function boot() {
    if (!navigator.onLine) renderConnectivity(false);

    window.addEventListener('offline', () => renderConnectivity(false));
    window.addEventListener('online', () => {
        renderConnectivity(true, true);
        resync('online');
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            return;
        }
        // Retour au premier plan (appli rouverte, écran déverrouillé) : données fraîches tout de suite
        if (hiddenAt && Date.now() - hiddenAt > 2000) resync('visible');
        hiddenAt = null;
        checkForSwUpdate();
    });

    // bfcache (retour arrière) : la page restaurée peut être périmée
    window.addEventListener('pageshow', (e) => { if (e.persisted) resync('pageshow'); });

    initSwUpdates();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
