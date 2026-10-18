import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage, isSupported } from 'firebase/messaging';

/**
 * Notifications push du site (restaurateurs, employés, stands, clients connectés).
 * Le service worker /sw.js (enregistré par partials/service-worker) affiche les pushs
 * reçus app fermée ; ici on enregistre le token de l'appareil auprès du serveur.
 */

function readConfig() {
    const configMeta = document.querySelector('meta[name="firebase-config"]');
    const vapidMeta = document.querySelector('meta[name="firebase-vapid-key"]');
    const endpointMeta = document.querySelector('meta[name="push-token-url"]');
    if (!configMeta || !vapidMeta?.content || !endpointMeta) return null;

    try {
        const firebaseConfig = JSON.parse(configMeta.content);
        if (!firebaseConfig.projectId) return null;
        return { firebaseConfig, vapidKey: vapidMeta.content, endpoint: endpointMeta.content };
    } catch {
        return null;
    }
}

export function pushAvailable() {
    return !!readConfig() && 'serviceWorker' in navigator && 'Notification' in window;
}

export function pushPermission() {
    return 'Notification' in window ? Notification.permission : 'denied';
}

let foregroundBound = false;

/**
 * @param {boolean} askPermission  true uniquement suite à un clic (exigé par Safari/Chrome)
 * @returns {Promise<boolean>} true si l'appareil est enregistré
 */
export async function initFcm(askPermission = false) {
    const cfg = readConfig();
    if (!cfg || !('serviceWorker' in navigator) || !('Notification' in window)) return false;
    if (!(await isSupported())) return false;

    let permission = Notification.permission;
    if (permission === 'default' && askPermission) {
        permission = await Notification.requestPermission();
    }
    if (permission !== 'granted') return false;

    const app = initializeApp(cfg.firebaseConfig);
    const messaging = getMessaging(app);
    const registration = await navigator.serviceWorker.ready;

    const token = await getToken(messaging, {
        vapidKey: cfg.vapidKey,
        serviceWorkerRegistration: registration,
    });
    if (!token) return false;

    // Ré-enregistré à chaque visite : le serveur garde la date de dernière activité
    await fetch(cfg.endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({ token }),
    });

    // Onglet ouvert : le navigateur n'affiche pas le push lui-même, on le fait via le service worker
    // (new Notification() ne fonctionne pas sur Android).
    if (!foregroundBound) {
        foregroundBound = true;
        onMessage(messaging, (payload) => {
            const { title, body } = payload.notification ?? {};
            if (!title) return;
            registration.showNotification(title, {
                body: body ?? '',
                icon: '/icon-192.png',
                badge: '/icon-192.png',
                tag: payload.data?.order_id ? `order-${payload.data.order_id}` : undefined,
                data: { link: payload.fcmOptions?.link },
            });
        });
    }

    return true;
}
