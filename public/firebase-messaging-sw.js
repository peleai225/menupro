importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Config injectée via le méta tag au moment de l'install du SW
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        const app = firebase.initializeApp(event.data.config);
        const messaging = firebase.messaging(app);
        messaging.onBackgroundMessage((payload) => {
            const { title, body, icon } = payload.notification ?? {};
            self.registration.showNotification(title ?? 'Notification', {
                body: body ?? '',
                icon: icon ?? '/icon-192.png',
            });
        });
    }
});
