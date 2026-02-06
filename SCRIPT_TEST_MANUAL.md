# 🧪 Script de Test Manuel - MenuPro

## 🎯 Objectif
Tester toutes les fonctionnalités de la plateforme en tant qu'agent.

---

## 📝 PRÉPARATION

### 1. Comptes de Test
```
Super Admin:
- Email: admin@menupro.ci
- Password: password

Restaurant Demo:
- Email: demo@menupro.ci  
- Password: password
```

### 2. Données de Test
```bash
php artisan db:seed
```

---

## ✅ TEST 1 : INSCRIPTION ET ESSAI GRATUIT

### Étape 1.1 : Nouvelle Inscription
1. Ouvrir `/register`
2. Remplir le formulaire :
   - Nom : "Agent Test"
   - Email : "agent-test@example.com"
   - Téléphone : "+225 0712345678"
   - Mot de passe : "password123"
   - Nom restaurant : "Restaurant Test Essai"
   - Type : "Restaurant"
   - Adresse : "Cocody, Abidjan"
   - Ville : "Abidjan"
3. Cliquer sur "S'inscrire"

**✅ Résultat Attendu :**
- ✅ Redirection vers `/dashboard` (pas de paiement)
- ✅ Message : "Bienvenue ! Votre essai gratuit de 14 jours a commencé"
- ✅ Compte activé immédiatement
- ✅ Email de bienvenue reçu

### Étape 1.2 : Vérification Essai
1. Aller dans `/dashboard/abonnement`
2. Vérifier :
   - Badge "Essai gratuit" visible
   - Date d'expiration = aujourd'hui + 14 jours
   - Statut : "Essai gratuit"
   - Montant : "Gratuit"

**✅ Résultat Attendu :**
- ✅ Essai de 14 jours visible
- ✅ Compte fonctionnel

---

## ✅ TEST 2 : GESTION DES COMMANDES (GESTIONNAIRE)

### Étape 2.1 : Vue Liste
1. Se connecter avec `demo@menupro.ci`
2. Aller dans `/dashboard/commandes`
3. Vérifier :
   - Liste des commandes affichée
   - Filtres fonctionnels
   - Boutons "Vue Kanban" et "Mode Rush" visibles

**✅ Résultat Attendu :**
- ✅ Liste chargée
- ✅ Filtres opérationnels

### Étape 2.2 : Vue Kanban
1. Cliquer sur "Vue Kanban"
2. Vérifier :
   - 7 colonnes visibles
   - Cartes de commandes affichées
   - Drag & drop fonctionnel
   - Auto-refresh actif

**Test Drag & Drop :**
- Glisser une commande d'une colonne à l'autre
- Vérifier le changement de statut

**✅ Résultat Attendu :**
- ✅ Kanban fonctionnel
- ✅ Drag & drop opérationnel

### Étape 2.3 : Mode Rush
1. Cliquer sur "Mode Rush"
2. Vérifier :
   - Vue simplifiée
   - Actions rapides visibles
   - Filtre "Nouvelles uniquement"

**Test Actions Rapides :**
- Cliquer sur "✓ Confirmer" → Statut change
- Cliquer sur "🍳 Préparer" → Statut change
- Cliquer sur "✓ Prête" → Statut change

**✅ Résultat Attendu :**
- ✅ Actions en un clic fonctionnelles
- ✅ Statuts mis à jour

### Étape 2.4 : Modification Commande
1. Aller dans `/dashboard/commandes/{id}` (commande PAID ou CONFIRMED)
2. Cliquer sur "Modifier la commande"
3. Tester :
   - Ajouter un article
   - Retirer un article
   - Modifier quantité
4. Vérifier :
   - Recalcul automatique du total
   - Stock ajusté (si activé)

**✅ Résultat Attendu :**
- ✅ Modifications fonctionnelles
- ✅ Totaux recalculés

---

## ✅ TEST 3 : INTERFACE CLIENT

### Étape 3.1 : Menu Public
1. Aller sur `/r/demo/menu`
2. Vérifier :
   - Catégories et plats affichés
   - Ajout au panier fonctionnel

### Étape 3.2 : Checkout
1. Ajouter des articles au panier
2. Aller au checkout
3. Tester :
   - Recherche d'adresse (Geoapify)
   - "Utiliser ma position actuelle"
   - Sélection type commande
   - Validation commande

**✅ Résultat Attendu :**
- ✅ Recherche d'adresse fonctionnelle
   - Taper "Cocody" → Suggestions apparaissent
   - Cliquer sur une suggestion → Adresse remplie
- ✅ Géolocalisation fonctionnelle
   - Cliquer "Utiliser ma position" → Adresse remplie

### Étape 3.3 : Suivi Commande
1. Après création commande, noter le token
2. Aller sur `/r/demo/commande/{token}`
3. Vérifier :
   - Statut affiché
   - QR code visible
   - Auto-refresh actif

### Étape 3.4 : Modification Client
1. Créer une commande avec statut PAID
2. Aller sur la page de suivi (moins de 5 min après paiement)
3. Cliquer "Modifier ma commande"
4. Tester modifications
5. Vérifier notification au restaurant

**✅ Résultat Attendu :**
- ✅ Modifications possibles
- ✅ Restaurant notifié

---

## ✅ TEST 4 : CONVERSION ESSAI → ABONNEMENT

### Étape 4.1 : Conversion Pendant Essai
1. Se connecter avec compte en essai
2. Aller dans `/dashboard/abonnement`
3. Cliquer "Convertir en abonnement payant"
4. Choisir un plan
5. Vérifier redirection vers Lygos

**✅ Résultat Attendu :**
- ✅ Redirection vers paiement
- ✅ Essai converti après paiement

### Étape 4.2 : Expiration Essai
1. Modifier manuellement la date d'expiration d'un essai (DB)
2. Se connecter
3. Vérifier :
   - Alerte d'expiration visible
   - Commandes bloquées
   - Redirection vers abonnement

**✅ Résultat Attendu :**
- ✅ Compte bloqué
- ✅ Paiement obligatoire

---

## ✅ TEST 5 : NOTIFICATIONS

### Étape 5.1 : Notification Nouvelle Commande
1. Créer une commande depuis le menu public
2. Vérifier :
   - Email reçu par le restaurant
   - Notification in-app

### Étape 5.2 : Notification Modification
1. Modifier une commande (client ou gestionnaire)
2. Vérifier :
   - Email reçu
   - Notification in-app

---

## ✅ TEST 6 : REMBOURSEMENTS PARTIELS

### Étape 6.1 : Remboursement Partiel
1. Créer une commande payée (Lygos)
2. Modifier la commande (retirer un article)
3. Vérifier :
   - Remboursement partiel initié
   - Enregistrement dans `order_refunds`

**✅ Résultat Attendu :**
- ✅ Remboursement tracé
- ✅ Montant correct

---

## 📊 CHECKLIST FINALE

- [ ] Inscription sans paiement fonctionne
- [ ] Essai gratuit de 14 jours créé
- [ ] Vue Kanban fonctionnelle
- [ ] Mode Rush fonctionnel
- [ ] Modifications gestionnaire fonctionnelles
- [ ] Modifications client fonctionnelles
- [ ] Recherche d'adresse fonctionnelle
- [ ] Géolocalisation fonctionnelle
- [ ] Conversion essai fonctionnelle
- [ ] Expiration essai bloque le compte
- [ ] Notifications fonctionnelles
- [ ] Remboursements partiels fonctionnels

---

## 🐛 BUGS TROUVÉS

_À remplir pendant les tests..._

---

## ✅ VALIDATION FINALE

**Statut :** ☐ En cours ☐ Terminé ☐ Bloqué

**Notes :**
_À remplir..._
