/**
 * MenuPro Service Worker — Offline-first pour l'Afrique
 * Stratégie : cache d'abord pour les assets statiques, réseau d'abord pour les pages,
 * jamais de cache pour le tableau de bord, l'API, les paiements et les données en direct
 * + réception des notifications push Firebase (même tous les onglets fermés).
 */

/* ─── Notifications push (Firebase Cloud Messaging) ────────────────────────────
 * La config Firebase arrive dans l'URL du script (/sw.js?fb=…, voir partials/service-worker).
 * Elle doit être chargée ici, au démarrage du script : quand un push réveille le service
 * worker, aucune page n'est là pour la lui envoyer. Le SDK affiche alors lui-même la
 * notification et ouvre le lien (fcm_options.link) au clic. */
(() => {
  const encoded = new URL(self.location.href).searchParams.get('fb');
  if (!encoded) return;
  try {
    const base64 = encoded.replace(/-/g, '+').replace(/_/g, '/');
    const config = JSON.parse(atob(base64 + '='.repeat((4 - (base64.length % 4)) % 4)));
    importScripts(
      'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js',
      'https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js'
    );
    firebase.initializeApp(config);
    const messaging = firebase.messaging();

    // Compteur sur l'icône de l'application installée (comme WhatsApp) : data.badge = éléments
    // en attente côté serveur. Les messages avec bloc notification sont déjà affichés par le SDK :
    // on ne fait que mettre à jour le compteur, sans réafficher.
    messaging.onBackgroundMessage((payload) => {
      const badge = parseInt(payload?.data?.badge, 10);
      if (Number.isNaN(badge)) return;
      try {
        if (badge > 0 && self.navigator.setAppBadge) {
          self.navigator.setAppBadge(badge).catch(() => {});
        } else if (badge <= 0 && self.navigator.clearAppBadge) {
          self.navigator.clearAppBadge().catch(() => {});
        }
      } catch (e) {
        // API Badging non supportée : sans effet
      }
    });
  } catch (e) {
    console.warn('[SW] Firebase indisponible', e);
  }
})();

/* Version du cache : l'incrémenter purge les anciens caches à l'activation. */
const CACHE_VERSION = 'menupro-v2';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const MEDIA_CACHE   = `${CACHE_VERSION}-media`;
const OFFLINE_URL   = '/offline.html';
// Plafonds pour les téléphones d'entrée de gamme (peu de stockage)
const MAX_DYNAMIC_ENTRIES = 30;  // pages de menu gardées pour le hors-ligne
const MAX_MEDIA_ENTRIES   = 150; // photos de plats / logos (/storage, …)

// Coquille hors ligne mise en cache dès l'installation
const STATIC_ASSETS = [
  OFFLINE_URL,
  '/favicon.svg',
  '/icon-192.png',
];

/* Chemins JAMAIS servis depuis le cache (préfixes de pathname).
 * Tableau de bord, API, paiements, liens signés « commande à traiter », jetons push et CSRF,
 * écrans cuisine / personnel / serveur / livreur : toujours le réseau, sans exception. */
const NEVER_CACHE_PREFIXES = [
  '/dashboard',
  '/admin',
  '/api/',
  '/webhooks/',
  '/livewire',
  '/commande-a-traiter',
  '/push-token',
  '/csrf-token',
  '/connexion',
  '/deconnexion',
  '/logout',
  '/inscription',
  '/abonnement/',
  '/journal-client',
  '/mot-de-passe-oublie',
  '/reinitialiser-mot-de-passe',
  '/email/',
  '/verify',
  '/cuisine/',
  '/personnel/',
  '/serveur/',
  '/livreur/',
  '/commando',
  '/crm',
  '/telecharger',
  '/__clockwork',
];

/* ─── Installation ─────────────────────────────────────────────────────────────
 * La nouvelle version prend la main tout de suite (corrections du cache appliquées sans
 * attendre), mais la page n'est JAMAIS rechargée automatiquement : la page affiche une
 * bannière « Nouvelle version — Mettre à jour » (partials/service-worker) et ne recharge
 * que si l'utilisateur appuie dessus (aucune commande en attente n'est perdue). */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .catch(() => {})
      .then(() => self.skipWaiting())
  );
});

/* ─── Activation (nettoyage anciens caches) ────────────────────────────────── */
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys
          .filter((key) => key.startsWith('menupro-') && ![STATIC_CACHE, DYNAMIC_CACHE, MEDIA_CACHE].includes(key))
          .map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
      .catch(() => {})
  );
});

/* ─── Fetch ─────────────────────────────────────────────────────────────────────
 * Seules deux familles de requêtes sont interceptées :
 *  - assets statiques versionnés (/build, polices, images) → cache d'abord ;
 *  - navigations HTML → réseau d'abord, repli sur le cache (menus /r/{slug} seulement)
 *    puis sur /offline.html.
 * Tout le reste (JSON de suivi de commande, statut de paiement, données des écrans…)
 * va directement au réseau : jamais de réponse périmée. */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;
  if (request.headers.has('range')) return; // sons / médias : laissés au navigateur (requêtes partielles)

  const url = new URL(request.url);
  if (url.protocol !== 'https:' && url.protocol !== 'http:') return;
  if (url.origin !== self.location.origin) return;

  // Navigations : toujours le réseau. Hors ligne, page /offline.html (avec « Réessayer »),
  // y compris pour le tableau de bord — dont aucune page n'est jamais mise en cache.
  if (request.mode === 'navigate') {
    event.respondWith(networkFirstWithOfflineFallback(request, url));
    return;
  }

  if (isNeverCached(url.pathname)) return;

  if (isStaticAsset(url)) {
    event.respondWith(cacheFirst(request, url));
  }
});

/* ─── Stratégies ───────────────────────────────────────────────────────────── */

async function cacheFirst(request, url) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    if (response.ok && response.status === 200 && response.type === 'basic') {
      // /build (noms hachés) et icônes de l'appli : cache statique ; photos & logos : cache plafonné
      const isMedia = !url.pathname.startsWith('/build/') && /\.(png|jpe?g|gif|webp|avif)$/i.test(url.pathname)
        && !/^\/(icon-|apple-touch-icon)/.test(url.pathname);
      const cache = await caches.open(isMedia ? MEDIA_CACHE : STATIC_CACHE);
      // Sans await : la réponse part tout de suite vers la page (réseau lent), le cache suit
      cache.put(request, response.clone())
        .then(() => { if (isMedia) trimCache(MEDIA_CACHE, MAX_MEDIA_ENTRIES); })
        .catch(() => {});
    }
    return response;
  } catch (e) {
    return new Response('', { status: 503, statusText: 'Hors ligne' });
  }
}

async function networkFirstWithOfflineFallback(request, url) {
  const cacheable = isMenuPage(url.pathname);
  try {
    const response = await fetch(request);
    if (cacheable && response.ok && response.type === 'basic' && !response.redirected) {
      // Une seule entrée par menu (sans ?table=…), sans retarder l'affichage
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(url.origin + url.pathname, response.clone())
        .then(() => trimCache(DYNAMIC_CACHE, MAX_DYNAMIC_ENTRIES))
        .catch(() => {});
    }
    return response;
  } catch (e) {
    if (cacheable) {
      const cached = await caches.match(url.origin + url.pathname, { cacheName: DYNAMIC_CACHE });
      if (cached) return cached;
    }
    const offlinePage = await caches.match(OFFLINE_URL);
    return offlinePage || new Response(
      '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors ligne</title>'
      + '<body style="font-family:sans-serif;text-align:center;padding:3rem 1rem"><h1>Pas de connexion</h1>'
      + '<p>Vérifiez votre connexion internet.</p><button onclick="location.reload()" style="padding:.8rem 1.5rem;font-size:1rem">Réessayer</button></body>',
      { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}

async function trimCache(name, max) {
  try {
    const cache = await caches.open(name);
    const keys = await cache.keys();
    for (let i = 0; i < keys.length - max; i++) await cache.delete(keys[i]);
  } catch (e) { /* sans effet */ }
}

/* ─── Helpers ───────────────────────────────────────────────────────────────── */

function isNeverCached(pathname) {
  return NEVER_CACHE_PREFIXES.some((prefix) => pathname === prefix.replace(/\/$/, '') || pathname.startsWith(prefix));
}

// Menu public d'un restaurant (/r/{slug}) : seule page HTML gardée pour le hors-ligne.
// Les pages de suivi / paiement (/r/{slug}/commande/…) ne sont jamais mises en cache.
function isMenuPage(pathname) {
  return /^\/r\/[^/]+\/?$/.test(pathname);
}

function isStaticAsset(url) {
  if (url.pathname.startsWith('/build/')) return true;
  return /\.(css|js|woff2?|ttf|otf|svg|png|jpe?g|gif|ico|webp|avif)$/i.test(url.pathname);
}

/* ─── Clic sur une notification affichée par la page (onglet ouvert) ─────────────
 * Les pushs reçus app fermée sont affichés et gérés au clic par le SDK Firebase
 * (voir en tête de fichier) : on ne traite ici que nos propres notifications. */
self.addEventListener('notificationclick', (event) => {
  const data = event.notification.data || {};
  if (data.FCM_MSG) return; // géré par Firebase

  event.notification.close();
  const link = data.link || data.url;
  if (!link) return;

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
      const target = new URL(link, self.location.origin).href;
      for (const client of windows) {
        if (client.url === target && 'focus' in client) return client.focus();
      }
      return clients.openWindow(target);
    })
  );
});
