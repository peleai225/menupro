# Analyse de fond – Intégration GeniusPay

## 1. Problème identifié : le secret webhook n’était pas utilisé

### Cause
- Le secret était **écrit en commentaire** dans `.env` (ligne 66), donc **jamais lu** comme variable.
- Le webhook ne lit que **SystemSetting** (base de données) pour le secret, pas `config()` / `.env`.
- Si le secret n’a jamais été saisi dans **Super Admin > Paramètres > GeniusPay**, il restait vide → **signature webhook refusée** ou non vérifiée.

### Correction appliquée
- **GeniusPayWebhookController** : le secret est pris dans l’ordre :
  1. Restaurant (pour les commandes avec clés propres),
  2. SystemSetting (paramètres Super Admin),
  3. **Fallback** : `config('services.geniuspay.webhook_secret')` → variable `GENIUSPAY_WEBHOOK_SECRET` dans `.env`.
- Le commentaire contenant le secret a été **retiré** du `.env` pour ne pas exposer le secret.

### À faire de votre côté
Choisir **une** des deux options :

- **Option A – .env**  
  Dans `.env`, décommenter et renseigner :
  ```env
  GENIUSPAY_WEBHOOK_SECRET=whsec_votre_secret
  ```
  (Utilisez le secret fourni par GeniusPay, ou régénérez-en un nouveau après l’avoir exposé.)

- **Option B – Super Admin**  
  Aller dans **Super Admin > Paramètres > Configuration GeniusPay** et remplir le champ **Secret webhook**.

---

## 2. URL du site et réception des webhooks

### Constat
- Dans votre `.env` : `APP_URL=http://127.0.0.1:8000` (localhost).

### Conséquence
- GeniusPay envoie les webhooks vers l’URL que vous avez configurée chez eux (ex. `https://votre-domaine.com/webhooks/geniuspay`).
- Si l’application n’est **accessible que** sur `http://127.0.0.1:8000`, les serveurs GeniusPay **ne peuvent pas** joindre votre machine → **aucun webhook reçu**.

### À faire
- En **production** : déployer sur un hébergement avec **URL publique** (ex. `https://menupro.ci`) et configurer cette URL dans le tableau de bord GeniusPay pour le webhook.
- En **développement** : utiliser un tunnel (ex. **ngrok**, **expose**) pour exposer `http://127.0.0.1:8000` et donner une URL publique à renseigner dans GeniusPay.

---

## 3. Flux de paiement (rappel)

1. **Création du paiement**  
   Checkout → `GeniusPayGateway::createOrderPayment()` → POST GeniusPay → redirection vers `checkout_url` ou `payment_url` (Wave direct si téléphone valide).

2. **Côté client**  
   Paiement sur la page GeniusPay ou dans l’app Wave.

3. **Webhook**  
   GeniusPay envoie un POST vers `https://votre-domaine/webhooks/geniuspay` avec l’événement (ex. `payment.success`).  
   Le contrôleur vérifie la signature avec le **secret webhook**, puis met à jour la commande (payée) ou l’abonnement.

4. **Si le webhook ne peut pas être appelé** (mauvaise URL, secret vide, ou refus de signature)  
   La commande peut rester « non payée » côté MenuPro même si le client a payé. La redirection après paiement (`success_url`) peut alors faire une **vérification manuelle** via `CheckoutController::success()` (GET + `verifyPayment`).

---

## 4. Checklist de vérification

| Élément | Statut |
|--------|--------|
| Secret webhook défini (Super Admin **ou** `GENIUSPAY_WEBHOOK_SECRET` dans `.env`) | À vérifier |
| URL webhook dans le dashboard GeniusPay = `https://VOTRE_DOMAINE/webhooks/geniuspay` | À vérifier |
| Application accessible depuis Internet (pas uniquement localhost) pour recevoir les webhooks | À vérifier |
| Clés GeniusPay en **live** (pk_live_ / sk_live_) pour des vrais prélèvements | À vérifier |
| Mode GeniusPay = **Production** dans Super Admin si vous utilisez des clés live | À vérifier |

---

## 5. Résumé des fichiers modifiés

- **`app/Http/Controllers/Webhook/GeniusPayWebhookController.php`**  
  Utilisation du secret : SystemSetting puis **fallback** sur `config('services.geniuspay.webhook_secret')` (lecture de `GENIUSPAY_WEBHOOK_SECRET` dans `.env`).

- **`.env`**  
  Suppression du commentaire contenant le secret ; ajout d’un commentaire pour documenter `GENIUSPAY_WEBHOOK_SECRET`.

- **`docs/ANALYSE_GENIUSPAY.md`**  
  Ce document d’analyse.

---

## 6. Recommandation sécurité

Le secret webhook a été partagé en clair. Il est préférable de le **régénérer** dans le tableau de bord GeniusPay (section Webhooks), puis de mettre à jour le nouveau secret soit dans `.env` (`GENIUSPAY_WEBHOOK_SECRET`), soit dans Super Admin > Paramètres GeniusPay.
