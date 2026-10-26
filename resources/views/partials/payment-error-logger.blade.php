{{--
    Journal des erreurs navigateur des pages de paiement d'abonnement (→ ClientErrorLogController,
    storage/logs/payments-*.log). Capte : erreurs JS, promesses rejetées, échecs fetch, et un envoi
    de formulaire de paiement resté bloqué (aucune redirection 20 s après l'appui sur « Payer »).
    Script autonome, sans dépendance : fonctionne même si Alpine/Vite ne se sont pas chargés.
--}}
<script>
(function () {
    if (window.__mpPaymentLogger) return;
    window.__mpPaymentLogger = true;
    var endpoint = @js(route('client-errors.store'));
    var sent = 0;
    var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;

    function report(type, message, extra) {
        if (sent >= 8) return; // pas d'inondation des journaux
        sent++;
        var payload = JSON.stringify({
            type: type,
            message: String(message || '').slice(0, 1900),
            source: extra && extra.source ? String(extra.source).slice(0, 490) : null,
            line: extra && extra.line ? parseInt(extra.line, 10) || null : null,
            page: location.pathname + location.search,
            context: Object.assign({
                standalone: standalone,
                width: window.innerWidth,
                online: navigator.onLine,
                alpine: !!window.Alpine
            }, (extra && extra.context) || {})
        });
        try {
            if (navigator.sendBeacon && navigator.sendBeacon(endpoint, new Blob([payload], { type: 'application/json' }))) return;
        } catch (e) {}
        try {
            fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: payload, keepalive: true, credentials: 'same-origin' }).catch(function () {});
        } catch (e) {}
    }
    window.mpReportPaymentError = report;

    window.addEventListener('error', function (e) {
        report('js_error', e.message, { source: e.filename, line: e.lineno });
    });
    window.addEventListener('unhandledrejection', function (e) {
        var r = e.reason;
        report('promise_rejection', r && (r.message || r.toString ? r.toString() : r));
    });

    if (window.fetch) {
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var url = typeof input === 'string' ? input : (input && input.url) || '';
            return originalFetch.apply(this, arguments).then(function (res) {
                if (!res.ok && url.indexOf(endpoint) === -1 && res.status !== 401) {
                    report('fetch_http_' + res.status, url);
                }
                return res;
            }, function (err) {
                if (url.indexOf(endpoint) === -1) report('fetch_failed', (err && err.message) + ' — ' + url);
                throw err;
            });
        };
    }

    // Formulaire de paiement envoyé mais aucune navigation 20 s plus tard (réseau coupé,
    // redirection bloquée dans la PWA…) : on le journalise et on prévient l'utilisateur.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-payment-form')) return;
        var data = {};
        try {
            ['plan', 'billing_period', 'jeko_operator'].forEach(function (k) { if (form.elements[k]) data[k] = form.elements[k].value; });
        } catch (err) {}
        setTimeout(function () {
            if (document.visibilityState === 'visible') {
                report('submit_stalled', 'Aucune redirection 20 s après l\'appui sur Payer', { context: data });
                var box = document.getElementById('payment-stalled-hint');
                if (box) box.hidden = false;
                form.querySelectorAll('button[type=submit]').forEach(function (b) { b.disabled = false; b.removeAttribute('aria-busy'); });
            }
        }, 20000);
    }, true);
})();
</script>
