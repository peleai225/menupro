# GeniusPay – Référence API Subscription

Documentation de référence : [https://pay.genius.ci/docs/subscription-api](https://pay.genius.ci/docs/subscription-api)

---

## Différence avec l’API Paiements

| | **API Paiements** (actuellement utilisée) | **API Subscription** |
|---|------------------------------------------|----------------------|
| **Base URL** | `https://pay.genius.ci/api/v1/merchant` | `https://api.geniuspay.ci/v1/merchant` |
| **Auth** | `X-API-Key` + `X-API-Secret` | `Authorization: Bearer YOUR_API_KEY` |
| **Usage** | Paiement unique (commande ou 1er abonnement) | Abonnements récurrents (facturation automatique) |
| **MenuPro** | ✅ Utilisée (commandes + paiement initial abo) | ❌ Non utilisée (gestion abo en BDD propre) |

MenuPro gère les abonnements en base (plans, dates, statuts) et utilise l’**API Paiements** pour le paiement initial. L’**API Subscription** permettrait de déléguer la récurrence à GeniusPay (relances, factures auto, etc.) si besoin futur.

---

## API Subscription – Résumé

### Authentification
```http
Authorization: Bearer YOUR_API_KEY
```

### Créer un abonnement
`POST https://api.geniuspay.ci/v1/merchant/subscriptions`

| Paramètre | Type | Description |
|-----------|------|--------------|
| customer.phone | string | Téléphone client (requis) |
| customer.name | string | Nom du client |
| plan_name | string | Nom du plan (requis) |
| amount | number | Montant en XOF (requis) |
| billing_cycle | string | daily, weekly, **monthly**, quarterly, yearly |
| trial_days | number | Jours d’essai gratuit |

### Autres endpoints
- **Lister** : `GET /v1/merchant/subscriptions` (status, customer_phone, billing_cycle, per_page)
- **Statut** : `GET /v1/merchant/subscriptions/{uuid}/status`
- **Annuler** : `POST /v1/merchant/subscriptions/{uuid}/cancel`
- **Stats** : `GET /v1/merchant/subscriptions/stats`
- **Tokens (SMS/Email)** : `POST /v1/merchant/notification-tokens/purchase`

### Webhooks abonnements
Événements dédiés à l’API Subscription :
- `subscription.created`
- `subscription.payment_succeeded`
- `subscription.payment_failed`
- `subscription.past_due`
- `subscription.cancelled`

À distinguer des webhooks **paiements** (`payment.success`, `payment.failed`, etc.) utilisés par MenuPro aujourd’hui.

---

## Codes d’erreur
| Code | Message |
|------|---------|
| SUBSCRIPTION_NOT_FOUND | Abonnement introuvable |
| SUBSCRIPTION_CREATE_FAILED | Échec de création |
| INSUFFICIENT_TOKENS | Tokens insuffisants |
