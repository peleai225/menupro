# ✅ Vérification du Flux de Paiement Lygos

**Date :** 16 janvier 2026

---

## 🔍 QUESTION
**Un restaurant qui a rentré sa clé API Lygos peut-il recevoir des paiements ?**

**RÉPONSE : OUI, mais il faut les DEUX clés (API Key + API Secret)** ✅

---

## 📋 FLUX COMPLET

### 1. **Configuration par le Restaurant** ✅

#### Où configurer ?
- **Page :** Paramètres → Onglet "Paiement"
- **URL :** `/dashboard/parametres` (onglet paiement)

#### Champs disponibles :
- ✅ `lygos_enabled` : Activer/désactiver Lygos
- ✅ `lygos_api_key` : Clé API publique (visible dans le formulaire)
- ⚠️ `lygos_api_secret` : Clé API secrète (manque dans le formulaire)

#### Stockage :
- ✅ Clés **encryptées** dans la base de données
- ✅ Méthodes `getLygosApiKey()` et `getLygosApiSecret()` pour décrypter
- ✅ Accesseurs automatiques dans le modèle `Restaurant`

---

### 2. **Vérification de Configuration** ✅

#### Code dans `LygosGateway` :
```php
public function isConfigured(): bool
{
    return !empty($this->apiKey) && !empty($this->apiSecret);
}
```

**IMPORTANT :** Les DEUX clés sont nécessaires :
- ✅ `apiKey` (clé publique)
- ✅ `apiSecret` (clé secrète)

---

### 3. **Processus de Paiement** ✅

#### Étape 1 : Client passe commande
- Commande créée avec statut `PENDING_PAYMENT`
- Calcul des totaux (sous-total, taxes, frais, réduction)

#### Étape 2 : Vérification Lygos
```php
if ($this->restaurant->lygos_enabled && $lygos->isConfigured()) {
    // Redirection vers Lygos
}
```

**Conditions :**
- ✅ `lygos_enabled = true`
- ✅ `apiKey` présente
- ✅ `apiSecret` présente

#### Étape 3 : Création de la session de paiement
- Appel à l'API Lygos avec les clés du restaurant
- Création d'une session de paiement
- Redirection du client vers Lygos

#### Étape 4 : Paiement client
- Client paie sur la plateforme Lygos
- Lygos envoie un webhook à MenuPro

#### Étape 5 : Traitement du webhook
- Vérification de la signature (avec `apiSecret`)
- Mise à jour de la commande : `PAID`
- Notification au restaurant

---

## ⚠️ PROBLÈME DÉTECTÉ

### Champ `lygos_api_secret` manquant dans Settings

**Statut actuel :**
- ✅ `lygos_api_key` : Présent dans le formulaire
- ❌ `lygos_api_secret` : **MANQUANT** dans le formulaire

**Impact :**
- Le restaurant ne peut pas entrer sa clé secrète
- `isConfigured()` retournera `false` même avec la clé publique
- Les paiements ne fonctionneront pas

**Solution :** Ajouter le champ `lygos_api_secret` dans le formulaire Settings

---

## 🔧 CORRECTION NÉCESSAIRE

Il faut ajouter le champ `lygos_api_secret` dans :
1. `app/Livewire/Restaurant/Settings.php` (propriété + validation + sauvegarde)
2. `resources/views/livewire/restaurant/settings.blade.php` (champ input)

---

## ✅ CE QUI FONCTIONNE

1. ✅ **Stockage sécurisé** : Clés encryptées dans la DB
2. ✅ **Vérification** : `isConfigured()` vérifie les deux clés
3. ✅ **Création de paiement** : Utilise les clés du restaurant
4. ✅ **Webhooks** : Vérifie la signature avec `apiSecret`
5. ✅ **Sécurité** : Clés jamais exposées en clair

---

## 📊 RÉSUMÉ

| Élément | Statut | Notes |
|---------|--------|-------|
| **Formulaire de configuration** | ⚠️ Partiel | Manque `lygos_api_secret` |
| **Stockage sécurisé** | ✅ OK | Clés encryptées |
| **Vérification** | ✅ OK | Vérifie les deux clés |
| **Création paiement** | ✅ OK | Utilise les clés du restaurant |
| **Webhooks** | ✅ OK | Signature vérifiée |
| **Flux complet** | ⚠️ Bloqué | Sans `apiSecret`, pas de paiement |

---

## 🎯 CONCLUSION

**Actuellement :** Un restaurant peut entrer sa clé API, mais **ne peut pas recevoir de paiements** car la clé secrète n'est pas configurable.

**Après correction :** Un restaurant qui entre ses **deux clés** (API Key + API Secret) pourra recevoir des paiements via Lygos.

**Action requise :** Ajouter le champ `lygos_api_secret` dans le formulaire Settings.

