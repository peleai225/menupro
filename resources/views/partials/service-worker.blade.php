{{--
    Service worker unique du site (/sw.js) : cache hors ligne + réception des notifications push.
    La config Firebase (publique) est passée dans l'URL du script : le service worker l'a ainsi
    dès son démarrage, même réveillé par un push alors que tous les onglets sont fermés.
    Toutes les pages doivent enregistrer exactement la même URL, sinon elles se remplacent.
--}}
@php
    try {
        $fbProjectId = \App\Models\SystemSetting::get('firebase_project_id');
        $fbVapidKey  = \App\Models\SystemSetting::get('firebase_vapid_key');
        $fbConfig = ($fbProjectId && $fbVapidKey) ? json_encode([
            'projectId'         => $fbProjectId,
            'apiKey'            => \App\Models\SystemSetting::get('firebase_api_key') ?? '',
            'appId'             => \App\Models\SystemSetting::get('firebase_app_id') ?? '',
            'messagingSenderId' => \App\Models\SystemSetting::get('firebase_sender_id') ?? '',
        ]) : null;
    } catch (\Throwable $e) {
        $fbConfig = null;
        $fbVapidKey = null;
    }
    $swUrl = '/sw.js' . ($fbConfig ? '?fb=' . rtrim(strtr(base64_encode($fbConfig), '+/', '-_'), '=') : '');
@endphp
<meta name="sw-url" content="{{ $swUrl }}">
@if($fbConfig)
    <meta name="firebase-config" content="{{ $fbConfig }}">
    <meta name="firebase-vapid-key" content="{{ $fbVapidKey }}">
    @auth
        <meta name="push-token-url" content="{{ route('push-token.store') }}">
    @endauth
@endif
<script>
    // Proposition d'installation (Android / Chrome) : capturée dès le <head>, car l'événement
    // peut arriver avant le démarrage d'Alpine. Les bandeaux d'installation la lisent ensuite.
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.__mpInstallPrompt = e;
        window.dispatchEvent(new CustomEvent('mp:installable'));
    });
    window.addEventListener('appinstalled', function () {
        window.__mpInstallPrompt = null;
        window.dispatchEvent(new CustomEvent('mp:installed'));
    });
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(@json($swUrl), { scope: '/' })
                .catch(err => console.debug('[SW] Échec', err));
        });
    }
</script>
