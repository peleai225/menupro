/**
 * MenuPro Service Worker — Offline-first pour l'Afrique
 * Stratégie : Cache-first pour assets statiques, Network-first pour pages dynamiques
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

const CACHE_VERSION = 'menupro-v1';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const OFFLINE_URL   = '/offline.html';

// Assets à mettre en cache immédiatement (shell app)
const STATIC_ASSETS = [
  '/',
  OFFLINE_URL,
  '/favicon.svg',
  '/manifest.json',
];

// Patterns d'URL à ne JAMAIS mettre en cache
const NEVER_CACHE = [
  '/webhooks/',
  '/api/',
  '/livewire/update',
  'livewire.min.js',
  '__clockwork',
  '/dashboard/',
  '/admin/',
  '/cuisine/',
  '/livreur/',
  '/commando/',
];

/* ─── Installation ─────────────────────────────────────────────────────────── */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(STATIC_ASSETS).catch(() => {}))
      .then(() => self.skipWaiting())
  );
});

/* ─── Activation (nettoyage anciens caches) ────────────────────────────────── */
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key.startsWith('menupro-') && key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
            .map((key) => caches.delete(key))
        )
      )
      .then(() => {
        // Claim seulement après activation complète
        return self.clients.claim().catch(() => {
          // Ignore l'erreur si le SW n'est pas encore actif
          console.log('SW will claim clients on next activation');
        });
      })
  );
});

/* ─── Fetch — Stratégie hybride ─────────────────────────────────────────────── */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorer les requêtes non-GET et les URLs à ne jamais cacher
  if (request.method !== 'GET') return;
  if (NEVER_CACHE.some((pattern) => url.pathname.includes(pattern))) return;
  if (url.protocol !== 'https:' && url.protocol !== 'http:') return;

  // Assets statiques (JS, CSS, fonts, images) → Cache-first
  if (isStaticAsset(url)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Pages HTML → Network-first avec fallback offline
  if (request.headers.get('Accept')?.includes('text/html')) {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }

  // API externe (ex: paiements) → Network-only
  if (isExternalApi(url)) return;

  // Autres requêtes → Stale-while-revalidate
  event.respondWith(staleWhileRevalidate(request));
});

/* ─── Stratégies de cache ───────────────────────────────────────────────────── */

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return new Response('Ressource non disponible hors ligne.', { status: 503 });
  }
}

async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      // Mettre en cache les pages de menu restaurant pour offline
      if (request.url.includes('/r/')) {
        const cache = await caches.open(DYNAMIC_CACHE);
        cache.put(request, response.clone());
      }
    }
    return response;
  } catch {
    // Essayer le cache dynamique
    const cached = await caches.match(request);
    if (cached) return cached;

    // Fallback sur la page offline
    const offlinePage = await caches.match(OFFLINE_URL);
    return offlinePage || new Response(
      '<html><body><h1>Pas de connexion</h1><p>Reconnectez-vous pour commander.</p></body></html>',
      { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(DYNAMIC_CACHE);
  const cached = await cache.match(request);

  const networkFetch = fetch(request).then((response) => {
    if (response.ok) cache.put(request, response.clone());
    return response;
  }).catch(() => cached || new Response('Hors ligne', { status: 503 }));

  return cached || networkFetch;
}

/* ─── Helpers ───────────────────────────────────────────────────────────────── */

function isStaticAsset(url) {
  return /\.(js|css|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico|webp)(\?.*)?$/.test(url.pathname);
}

function isExternalApi(url) {
  const externalDomains = ['api.wave.com', 'cinetpay.com', 'fusionpay.io', 'lygosapp.com'];
  return externalDomains.some((domain) => url.hostname.includes(domain));
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
