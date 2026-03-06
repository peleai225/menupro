{{-- Bannière de consentement aux cookies - conforme RGPD (logique Alpine inline pour éviter dépendance à app.js) --}}
<div x-data="{
    showBanner: false,
    cookieName: 'menupro_cookie_consent',
    cookieExpiryDays: 365,
    init() {
        const consent = this.getConsent();
        if (!consent) this.showBanner = true;
    },
    getConsent() {
        const value = document.cookie.split('; ').find(row => row.startsWith(this.cookieName + '='));
        return value ? decodeURIComponent(value.split('=')[1]) : null;
    },
    setCookie(value) {
        const date = new Date();
        date.setTime(date.getTime() + (this.cookieExpiryDays * 24 * 60 * 60 * 1000));
        document.cookie = this.cookieName + '=' + encodeURIComponent(value) + '; expires=' + date.toUTCString() + '; path=/; SameSite=Lax';
    },
    accept() { this.setCookie('accepted'); this.showBanner = false; },
    decline() { this.setCookie('essential_only'); this.showBanner = false; }
}"
     x-show="showBanner"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
     class="fixed bottom-0 left-0 right-0 z-[200] p-4 sm:p-6"
     x-cloak>
    <div class="max-w-4xl mx-auto bg-white dark:bg-neutral-800 rounded-xl shadow-elevated border border-neutral-200 dark:border-neutral-700 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-1">
                    🍪 Nous utilisons des cookies
                </h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Pour assurer le bon fonctionnement du site (connexion, panier, préférences) et améliorer votre expérience.
                    En cliquant sur « Accepter », vous consentez à l'utilisation des cookies essentiels.
                    <a href="{{ route('privacy') }}#cookies" class="text-primary-500 hover:text-primary-600 underline">En savoir plus</a>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                <button @click="accept()"
                        class="btn btn-primary btn-sm">
                    Accepter
                </button>
                <button @click="decline()"
                        class="btn btn-ghost btn-sm text-neutral-600 dark:text-neutral-400">
                    Refuser les non essentiels
                </button>
            </div>
        </div>
    </div>
</div>
