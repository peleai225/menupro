# ✅ Correction de l'Intégration Lygos selon la Documentation Officielle

**Date :** 16 janvier 2026

---

## 📋 Modifications Effectuées

### 1. **URL de Base** ✅
- **Avant :** `https://api.lygos.ci`
- **Après :** `https://api.lygosapp.com/v1`
- **Fichier :** `config/services.php`, `app/Services/LygosGateway.php`

### 2. **Authentification** ✅
- **Avant :** 
  ```php
  'Authorization' => "Bearer {$this->apiKey}",
  'X-API-Secret' => $this->apiSecret,
  ```
- **Après :** 
  ```php
  'api-key' => $this->apiKey,
  ```
- **Fichier :** `app/Services/LygosGateway.php`
- **Note :** Selon la documentation, seule l'API key est requise pour l'authentification

### 3. **Endpoint de Création de Paiement** ✅
- **Avant :** `POST /v1/payments`
- **Après :** `POST /v1/gateway`
- **Fichier :** `app/Services/LygosGateway.php`

### 4. **Format du Payload** ✅
- **Avant :**
  ```php
  [
      'amount' => $order->total,
      'currency' => 'XOF',
      'reference' => $order->reference,
      'description' => "...",
      'customer' => [...],
      'return_url' => ...,
      'cancel_url' => ...,
      'webhook_url' => ...,
      'metadata' => [...],
  ]
  ```
- **Après :**
  ```php
  [
      'amount' => (int) $order->total,        // Required: integer
      'shop_name' => $restaurant->name,      // Required: string
      'message' => "...",                    // Optional
      'success_url' => $returnUrl,            // Optional
      'failure_url' => $cancelUrl,            // Optional
      'order_id' => $order->reference,        // Optional: used to track
  ]
  ```
- **Fichier :** `app/Services/LygosGateway.php`

### 5. **Gestion de la Réponse** ✅
- **Avant :** `$data['payment_url']`
- **Après :** `$data['link']` (selon documentation Lygos)
- **Fichier :** `app/Services/LygosGateway.php`

### 6. **Vérification du Statut de Paiement** ✅
- **Avant :** `GET /v1/payments/{paymentId}`
- **Après :** `GET /v1/gateway/payin/{order_id}`
- **Format de réponse :** `{ "order_id": "...", "status": "..." }`
- **Fichiers :** 
  - `app/Services/LygosGateway.php`
  - `app/Http/Controllers/Public/CheckoutController.php`
  - `app/Http/Controllers/Restaurant/SubscriptionController.php`

### 7. **Webhook Controller** ✅
- **Corrections :**
  - Utilisation de `order_id` pour identifier les commandes
  - Support des deux formats (`order_id` et `payment_id`) pour compatibilité
  - Correction du canal de logging (`payments` au lieu de `payment`)
- **Fichier :** `app/Http/Controllers/Webhook/LygosWebhookController.php`

### 8. **Méthode `markAsRefunded()`** ✅
- **Ajout :** Méthode manquante dans le modèle `Order`
- **Fichier :** `app/Models/Order.php`

### 9. **Vérification de Configuration** ✅
- **Avant :** Requiert `apiKey` ET `apiSecret`
- **Après :** Requiert uniquement `apiKey` (selon documentation)
- **Fichier :** `app/Services/LygosGateway.php`

---

## 📚 Références Documentation

- [Introduction Lygos API](https://docs.lygosapp.com/api-reference/introduction)
- [Environnements et URLs](https://docs.lygosapp.com/api-reference/environment)
- [Créer une Gateway](https://docs.lygosapp.com/api-reference/gateway/create-payment-gateway)
- [Vérifier le Statut Payin](https://docs.lygosapp.com/api-reference/payin/get-payin-status)

---

## ⚠️ Notes Importantes

1. **API Secret :** 
   - L'authentification API utilise uniquement `api-key`
   - L'`api-secret` peut être utilisé pour la vérification des webhooks (si Lygos l'envoie)
   - Le code garde la compatibilité avec les deux

2. **Order ID :**
   - Lygos utilise `order_id` pour identifier les transactions
   - Dans notre système, `order_id` correspond à `order.reference`
   - Pour les abonnements, format : `SUB-{id}-{date}`

3. **Webhooks :**
   - Le webhook controller supporte maintenant `order_id` ET `payment_id` pour la compatibilité
   - La vérification de signature utilise toujours `api-secret` si disponible

4. **Rétrocompatibilité :**
   - Le code garde une certaine flexibilité pour gérer les anciens et nouveaux formats
   - Les logs sont maintenant tous dans le canal `payments`

---

## ✅ Tests à Effectuer

1. **Création de paiement :**
   - Vérifier que la création de session fonctionne avec le nouveau format
   - Vérifier que l'URL de redirection (`link`) est correcte

2. **Vérification de statut :**
   - Tester `verifyPayment()` avec `order_id`
   - Vérifier que le statut est correctement interprété

3. **Webhooks :**
   - Tester la réception de webhooks avec `order_id`
   - Vérifier que les commandes sont correctement identifiées
   - Vérifier la vérification de signature

4. **Abonnements :**
   - Tester la création de paiement d'abonnement
   - Vérifier la vérification du statut

---

## 🔄 Prochaines Étapes

1. Tester l'intégration avec des clés API réelles
2. Vérifier le format exact des webhooks Lygos
3. Ajuster si nécessaire selon les retours de test

---

**Status :** ✅ Intégration corrigée selon la documentation officielle

