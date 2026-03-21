/**
 * MenuPro Service Worker — Offline-first pour l'Afrique
 * Stratégie : Cache-first pour assets statiques, Network-first pour pages dynamiques
 */

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
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key.startsWith('menupro-') && key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
          .map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
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
  }).catch(() => cached);

  return cached || networkFetch;
}

/* ─── Helpers ───────────────────────────────────────────────────────────────── */

function isStaticAsset(url) {
  return /\.(js|css|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico|webp)(\?.*)?$/.test(url.pathname);
}

function isExternalApi(url) {
  const externalDomains = ['api.wave.com', 'cinetpay.com', 'fusionpay.io', 'geniuspay.app'];
  return externalDomains.some((domain) => url.hostname.includes(domain));
}

/* ─── Push Notifications (préparé pour future intégration) ──────────────────── */
self.addEventListener('push', (event) => {
  if (!event.data) return;

  const data = event.data.json();
  event.waitUntil(
    self.registration.showNotification(data.title || 'MenuPro', {
      body: data.body || '',
      icon: '/icon-192.png',
      badge: '/favicon.svg',
      tag: data.tag || 'menupro-notification',
      data: data.url ? { url: data.url } : {},
      vibrate: [100, 50, 100],
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  if (event.notification.data?.url) {
    event.waitUntil(clients.openWindow(event.notification.data.url));
  }
});
