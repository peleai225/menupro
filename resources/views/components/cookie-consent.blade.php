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
     x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-x-0 bottom-0 z-[200] p-3 sm:p-4 pointer-events-none"
     role="region" aria-label="Cookies"
     x-cloak>
    <div class="pointer-events-auto mx-auto max-w-3xl rounded-2xl bg-white text-neutral-900 px-4 py-3 sm:pl-5 shadow-[0_0_0_1px_rgba(0,0,0,.06),0_20px_40px_-16px_rgba(0,0,0,.25)] flex flex-col sm:flex-row sm:items-center gap-3">
        <p class="flex-1 text-sm leading-snug text-neutral-700">
            Cookies : connexion, panier et préférences.
            <a href="{{ route('privacy') }}#cookies" class="font-semibold text-[#C2410C] underline underline-offset-2 whitespace-nowrap">En savoir plus</a>
        </p>
        <div class="grid grid-cols-2 gap-2 shrink-0">
            <button type="button" @click="decline()"
                    class="min-h-10 px-3 rounded-xl text-sm font-semibold text-neutral-800 bg-neutral-100 hover:bg-neutral-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-300">
                Essentiels seulement
            </button>
            <button type="button" @click="accept()"
                    class="min-h-10 px-4 rounded-xl text-sm font-semibold text-white bg-neutral-900 hover:bg-black focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-300">
                Accepter
            </button>
        </div>
    </div>
</div>
