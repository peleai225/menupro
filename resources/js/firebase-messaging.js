import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

export async function initFcm() {
    // La config Firebase est exposée via un meta tag dans le layout
    const configMeta = document.querySelector('meta[name="firebase-config"]');
    const vapidMeta = document.querySelector('meta[name="firebase-vapid-key"]');

    if (!configMeta || !vapidMeta) return; // Firebase non configuré

    let firebaseConfig;
    try {
        firebaseConfig = JSON.parse(configMeta.content);
    } catch (e) {
        return;
    }

    if (!firebaseConfig.projectId) return;

    const vapidKey = vapidMeta.content;
    if (!vapidKey) return;

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Envoie la config au service worker Firebase
    if ('serviceWorker' in navigator) {
        const reg = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        reg.active?.postMessage({ type: 'FIREBASE_CONFIG', config: firebaseConfig });
    }

    // Demande permission et obtient le token
    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        const token = await getToken(messaging, { vapidKey });
        if (!token) return;

        // Détermine l'endpoint selon si c'est un customer ou un driver
        // Utilise un meta tag pour savoir le type d'utilisateur authentifié
        const authTypeMeta = document.querySelector('meta[name="auth-type"]');
        const endpoint = authTypeMeta?.content === 'driver'
            ? '/api/v1/driver/auth/fcm-token'
            : '/api/v1/client/auth/fcm-token';

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta?.content ?? '';

        await fetch(endpoint, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ fcm_token: token }),
        });

        // Messages en premier plan
        onMessage(messaging, (payload) => {
            const { title, body } = payload.notification ?? {};
            if (title && 'Notification' in window && Notification.permission === 'granted') {
                new Notification(title, { body: body ?? '' });
            }
        });

    } catch (err) {
        console.warn('[FCM] token registration failed:', err);
    }
}
