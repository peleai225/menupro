# Vérification intégration API Paiements GeniusPay (production)

Référence : [https://pay.genius.ci/docs/api](https://pay.genius.ci/docs/api)

---

## 1. Authentification

| Doc | Implémentation | Statut |
|-----|----------------|--------|
| `X-API-Key` (pk_sandbox_... / pk_live_...) | `GeniusPayGateway::getHeaders()` → `X-API-Key` | OK |
| `X-API-Secret` (sk_sandbox_... / sk_live_...) | `X-API-Secret` | OK |
| `Content-Type: application/json` | `Content-Type` + `Accept: application/json` | OK |

**Verdict :** Conforme.

---

## 2. Initier un paiement – POST /payments

| Paramètre doc | Requis | Envoyé par MenuPro | Fichier |
|---------------|--------|--------------------|---------|
| amount | ✓ (min 200 XOF) | `max(200, (int) $order->total)` | GeniusPayGateway |
| currency | - | `XOF` | OK |
| payment_method | - | `wave` si téléphone valide, sinon omis (checkout) | OK |
| description | - | Commande / Abonnement | OK |
| customer.name | - | Envoyé | OK |
| customer.email | - | Envoyé | OK |
| customer.phone | - | Envoyé (format +225 normalisé) | OK |
| success_url | - | `returnUrl` (success) | OK |
| error_url | - | `error_url` (cancel) | OK |
| metadata | - | order_id, restaurant_id, type, reference | OK |

**Réponse utilisée :** `data.reference` ou `data.id`, `data.checkout_url` ou `data.payment_url`, `data.expires_at`.

**Verdict :** Conforme.

---

## 3. Récupérer un paiement – GET /payments/{reference}

| Doc | Implémentation | Statut |
|-----|----------------|--------|
| GET `/payments/{reference}` | `verifyPayment($reference)` → GET avec `urlencode($reference)` | OK |
| Statuts considérés « payé » | completed, success, paid | OK (doc : completed) |

Utilisé dans `CheckoutController::success()` pour vérifier le paiement au retour client (si webhook pas encore reçu).

**Verdict :** Conforme.

---

## 4. Webhooks

### 4.1 Headers

| Header doc | Utilisation | Statut |
|------------|-------------|--------|
| X-Webhook-Signature | Vérification HMAC | OK |
| X-Webhook-Timestamp | Replay (≤ 5 min) | OK |
| X-Webhook-Event | payment.success, payment.failed, etc. | OK |

### 4.2 Vérification signature

**Doc :** `signature = HMAC-SHA256(timestamp + "." + json_payload, secret)`

**Implémentation :** `$payload = $request->getContent()` (body brut) → `hash_hmac('sha256', $timestamp . '.' . $payload, $secret)`.

- On utilise le **body brut** (comme la plupart des APIs de paiement). Si GeniusPay signe exactement le corps de la requête, c’est correct.
- Si un jour la signature est refusée en production, vérifier auprès du support GeniusPay s’ils signent le body brut ou un `json_encode($request->all())`.

**Verdict :** Conforme (approche standard).

### 4.3 Événements gérés

| Événement doc | Géré | Action |
|---------------|------|--------|
| payment.success | Oui | Commande/abonnement marqué payé, notifications |
| payment.failed | Oui | Log (order) / abonnement annulé (subscription) |
| payment.cancelled | Oui | Idem |
| payment.refunded | Oui | Log (subscription) |
| payment.expired | Oui | Log |
| payment.initiated | Non | Optionnel |

**Verdict :** Suffisant pour la production.

### 4.4 Payload

- Lecture de `data` : `$webhookData = $data['data'] ?? $data`.
- Métadonnées : `metadata.order_id`, `metadata.subscription_id`, `metadata.type`, etc.
- Référence : `$webhookData['reference']`.

**Verdict :** Conforme au format doc.

---

## 5. Base URL et config

| Élément | Valeur |
|---------|--------|
| Base URL doc | `https://pay.genius.ci/api/v1/merchant` |
| Config | `config('services.geniuspay.base_url')` défaut idem |
| .env | `GENIUSPAY_BASE_URL` optionnel |

**Verdict :** OK.

---

## 6. Production – checklist

| Point | À vérifier |
|-------|------------|
| Clés | Utiliser **pk_live_** et **sk_live_** (Super Admin ou clés restaurant). |
| Mode | Super Admin > GeniusPay > Mode = **Production (live)**. |
| Webhook | Secret configuré (Super Admin ou `GENIUSPAY_WEBHOOK_SECRET` dans .env). |
| URL webhook | Dans le dashboard GeniusPay : `https://VOTRE_DOMAINE/webhooks/geniuspay`. |
| Accessibilité | L’app doit être joignable depuis Internet (pas uniquement localhost) pour recevoir les webhooks. |

---

## 7. Résumé

L’intégration de l’[API Paiements GeniusPay](https://pay.genius.ci/docs/api) est **conforme à la documentation** pour :

- Authentification (X-API-Key, X-API-Secret).
- Création de paiement (POST /payments) avec tous les paramètres utiles.
- Récupération / vérification (GET /payments/{reference}).
- Webhooks (signature, timestamp, événements, payload).
- Gestion des statuts (completed, failed, cancelled, etc.).

En production, il reste à confirmer : clés live, secret webhook, URL webhook côté GeniusPay, et hébergement accessible depuis Internet.
