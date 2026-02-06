# 🧪 Guide de Test Complet - Agent MenuPro

## 🎯 Objectif
Tester toutes les fonctionnalités de la plateforme en tant qu'agent.

---

## 📋 CHECKLIST RAPIDE

### ✅ 1. INSCRIPTION ET ESSAI GRATUIT (5 min)

#### Test 1.1 : Nouvelle Inscription
```
1. Aller sur http://127.0.0.1:8000/register
2. Remplir le formulaire :
   - Nom : "Agent Test"
   - Email : "agent-test@example.com"
   - Téléphone : "+225 0712345678"
   - Mot de passe : "password123"
   - Nom restaurant : "Restaurant Test"
   - Type : "Restaurant"
   - Adresse : "Cocody, Abidjan"
3. Cliquer "S'inscrire"
```

**✅ Vérifications :**
- [ ] Redirection vers `/dashboard` (PAS de paiement)
- [ ] Message : "Bienvenue ! Votre essai gratuit de 14 jours a commencé"
- [ ] Compte accessible immédiatement
- [ ] Aller dans `/dashboard/abonnement` → Badge "Essai gratuit" visible
- [ ] Date d'expiration = aujourd'hui + 14 jours

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 2. VUE KANBAN (3 min)

#### Test 2.1 : Accès Kanban
```
1. Se connecter : demo@menupro.ci / password
2. Aller dans /dashboard/commandes
3. Cliquer sur "Vue Kanban"
```

**✅ Vérifications :**
- [ ] 7 colonnes visibles (pending_payment, paid, confirmed, preparing, ready, delivering, completed)
- [ ] Cartes de commandes affichées
- [ ] Drag & drop fonctionnel (glisser une carte)
- [ ] Auto-refresh actif (indicateur vert clignotant)

**Test Drag & Drop :**
- [ ] Glisser une commande de "Payées" vers "Confirmées"
- [ ] Vérifier que le statut change

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 3. MODE RUSH (3 min)

#### Test 3.1 : Accès Rush
```
1. Depuis /dashboard/commandes, cliquer sur "Mode Rush"
```

**✅ Vérifications :**
- [ ] Vue simplifiée affichée
- [ ] Actions rapides visibles (Confirmer, Préparer, Prête, Terminer)
- [ ] Filtre "Nouvelles uniquement" fonctionnel
- [ ] Auto-refresh activable/désactivable

**Test Actions Rapides :**
- [ ] Cliquer "✓ Confirmer" sur une commande PAID
- [ ] Vérifier que le statut change
- [ ] Cliquer "🍳 Préparer" → Statut change
- [ ] Cliquer "✓ Prête" → Statut change

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 4. MODIFICATION COMMANDE (GESTIONNAIRE) (5 min)

#### Test 4.1 : Modification depuis Détail Commande
```
1. Aller dans /dashboard/commandes/{id} (commande PAID ou CONFIRMED)
2. Cliquer sur "Modifier la commande"
3. Tester :
   - Ajouter un article
   - Retirer un article  
   - Modifier quantité
```

**✅ Vérifications :**
- [ ] Modal de modification s'ouvre
- [ ] Ajout d'article fonctionne
- [ ] Retrait d'article fonctionne
- [ ] Modification quantité fonctionne
- [ ] Total recalculé automatiquement
- [ ] Stock ajusté (si activé)

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 5. INTERFACE CLIENT - RECHERCHE ADRESSE (3 min)

#### Test 5.1 : Recherche Adresse
```
1. Aller sur /r/demo/commander
2. Dans le champ "Adresse de livraison", taper "Cocody"
```

**✅ Vérifications :**
- [ ] Suggestions d'adresses apparaissent
- [ ] Cliquer sur une suggestion → Adresse remplie
- [ ] Coordonnées GPS remplies

#### Test 5.2 : Géolocalisation
```
1. Cliquer sur "Utiliser ma position actuelle"
2. Autoriser la géolocalisation
```

**✅ Vérifications :**
- [ ] Adresse remplie automatiquement
- [ ] Coordonnées GPS remplies

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 6. MODIFICATION COMMANDE (CLIENT) (5 min)

#### Test 6.1 : Modification Client
```
1. Créer une commande depuis le menu public
2. Noter le token de suivi
3. Aller sur /r/demo/commande/{token}
4. Cliquer "Modifier ma commande" (si statut PAID et < 5 min)
```

**✅ Vérifications :**
- [ ] Modal de modification s'ouvre
- [ ] Ajout/retrait/modification fonctionne
- [ ] Restaurant notifié (vérifier notifications)
- [ ] Remboursement partiel si applicable

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 7. CONVERSION ESSAI → ABONNEMENT (5 min)

#### Test 7.1 : Conversion Pendant Essai
```
1. Se connecter avec compte en essai
2. Aller dans /dashboard/abonnement
3. Cliquer "Convertir en abonnement payant"
4. Choisir un plan
```

**✅ Vérifications :**
- [ ] Redirection vers page de plans
- [ ] Message "Convertir votre essai gratuit" visible
- [ ] Bouton "Convertir maintenant" visible
- [ ] Redirection vers Lygos (si configuré)

**❌ Si erreur :** Noter l'erreur ici : ________________

---

### ✅ 8. EXPIRATION ESSAI (Test Manuel DB)

#### Test 8.1 : Simuler Expiration
```sql
-- Dans la base de données, modifier un essai pour qu'il expire :
UPDATE subscriptions 
SET ends_at = NOW() - INTERVAL 1 DAY 
WHERE is_trial = 1 
LIMIT 1;

UPDATE restaurants 
SET subscription_ends_at = NOW() - INTERVAL 1 DAY 
WHERE id = (SELECT restaurant_id FROM subscriptions WHERE is_trial = 1 LIMIT 1);
```

**Puis :**
```
1. Se connecter avec ce compte
2. Vérifier :
   - Alerte d'expiration visible
   - Commandes bloquées
   - Redirection vers abonnement
```

**✅ Vérifications :**
- [ ] Alerte rouge "Essai expiré" visible
- [ ] Liste des fonctionnalités bloquées affichée
- [ ] Bouton "Souscrire maintenant" visible
- [ ] Accès dashboard limité

**❌ Si erreur :** Noter l'erreur ici : ________________

---

## 📊 RÉSUMÉ DES TESTS

### Tests Critiques
- [x] Inscription sans paiement
- [x] Essai gratuit 14 jours
- [x] Vue Kanban
- [x] Mode Rush
- [x] Modifications gestionnaire
- [x] Modifications client
- [x] Recherche adresse
- [x] Conversion essai
- [x] Expiration essai

### Tests Secondaires
- [ ] Notifications email
- [ ] Remboursements partiels
- [ ] Gestion stock
- [ ] Statistiques

---

## 🐛 BUGS TROUVÉS

### Bug #1
**Description :** 
**Fichier :** 
**Ligne :** 
**Solution :** 

### Bug #2
**Description :** 
**Fichier :** 
**Ligne :** 
**Solution :** 

---

## ✅ VALIDATION FINALE

**Date du test :** _______________
**Testeur :** Agent
**Statut global :** ☐ ✅ Réussi ☐ ⚠️ Partiel ☐ ❌ Échec

**Notes finales :**
_________________________________________________
_________________________________________________
_________________________________________________
