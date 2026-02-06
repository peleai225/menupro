# 🧪 Test Complet de la Plateforme MenuPro

## 📋 Checklist de Test - Agent

### ✅ Phase 1 : Test d'Inscription et Essai Gratuit

#### Test 1.1 : Inscription Nouveau Restaurant
- [ ] Accéder à `/register`
- [ ] Remplir le formulaire d'inscription
- [ ] Vérifier qu'aucun paiement n'est demandé
- [ ] Vérifier que le compte est créé avec statut ACTIVE
- [ ] Vérifier qu'un essai gratuit de 14 jours est créé
- [ ] Vérifier la redirection vers le dashboard
- [ ] Vérifier l'email de bienvenue reçu

#### Test 1.2 : Vérification Essai Gratuit
- [ ] Se connecter avec le nouveau compte
- [ ] Vérifier le badge "Essai gratuit" dans la page abonnement
- [ ] Vérifier la date d'expiration (14 jours)
- [ ] Vérifier que les commandes sont autorisées
- [ ] Vérifier l'accès complet au dashboard

---

### ✅ Phase 2 : Test Gestion des Commandes (Gestionnaire)

#### Test 2.1 : Vue Liste des Commandes
- [ ] Accéder à `/dashboard/commandes`
- [ ] Vérifier l'affichage des commandes
- [ ] Tester les filtres (statut, type, date, recherche)
- [ ] Vérifier les liens vers Kanban et Rush

#### Test 2.2 : Vue Kanban
- [ ] Accéder à `/dashboard/commandes/kanban`
- [ ] Vérifier les 7 colonnes (pending_payment, paid, confirmed, preparing, ready, delivering, completed)
- [ ] Tester le drag & drop pour changer le statut
- [ ] Vérifier le rafraîchissement automatique
- [ ] Tester les filtres (recherche, type)
- [ ] Vérifier les statistiques en en-tête

#### Test 2.3 : Mode Rush
- [ ] Accéder à `/dashboard/commandes/rush`
- [ ] Vérifier l'affichage simplifié
- [ ] Tester les actions rapides (Confirmer, Préparer, Prête, Terminer)
- [ ] Vérifier le filtre "Nouvelles uniquement"
- [ ] Vérifier l'auto-refresh

#### Test 2.4 : Modification de Commande (Gestionnaire)
- [ ] Accéder à une commande (statut PAID ou CONFIRMED)
- [ ] Cliquer sur "Modifier la commande"
- [ ] Tester l'ajout d'un article
- [ ] Tester le retrait d'un article
- [ ] Tester la modification de quantité
- [ ] Vérifier le recalcul automatique du total
- [ ] Vérifier la gestion du stock (si activé)

#### Test 2.5 : Changement de Statut
- [ ] Tester toutes les transitions de statut
- [ ] Vérifier la déduction du stock à CONFIRMED
- [ ] Vérifier les notifications

---

### ✅ Phase 3 : Test Interface Client

#### Test 3.1 : Consultation Menu Public
- [ ] Accéder à `/r/{slug}/menu`
- [ ] Vérifier l'affichage des catégories et plats
- [ ] Tester l'ajout au panier
- [ ] Tester les options de plat
- [ ] Tester les instructions spéciales

#### Test 3.2 : Checkout et Commande
- [ ] Accéder au checkout
- [ ] Remplir les informations client
- [ ] Tester la recherche d'adresse (Geoapify)
- [ ] Tester "Utiliser ma position actuelle"
- [ ] Choisir type de commande (sur place, à emporter, livraison)
- [ ] Appliquer un code promo
- [ ] Valider la commande
- [ ] Vérifier la création de la commande

#### Test 3.3 : Suivi de Commande Client
- [ ] Accéder à la page de suivi avec le token
- [ ] Vérifier l'affichage du statut
- [ ] Vérifier le QR code de partage
- [ ] Vérifier le rafraîchissement automatique
- [ ] Tester le lien de partage

#### Test 3.4 : Modification de Commande (Client)
- [ ] Accéder à une commande avec statut PAID (moins de 5 min)
- [ ] Cliquer sur "Modifier ma commande"
- [ ] Tester l'ajout d'un article
- [ ] Tester le retrait d'un article
- [ ] Tester la modification de quantité
- [ ] Vérifier les notifications au restaurant
- [ ] Vérifier les remboursements partiels (si applicable)

---

### ✅ Phase 4 : Test Gestion du Stock

#### Test 4.1 : Gestion des Ingrédients
- [ ] Accéder à la gestion du stock
- [ ] Créer un ingrédient
- [ ] Vérifier les mouvements de stock
- [ ] Tester les alertes de stock bas

#### Test 4.2 : Impact sur les Commandes
- [ ] Créer une commande avec un plat nécessitant du stock
- [ ] Vérifier la déduction du stock à CONFIRMED
- [ ] Modifier la commande (ajouter/retirer)
- [ ] Vérifier l'ajustement du stock
- [ ] Tester avec stock insuffisant

---

### ✅ Phase 5 : Test Abonnement et Essai

#### Test 5.1 : Conversion Essai → Abonnement Payant
- [ ] Se connecter avec un compte en essai
- [ ] Accéder à `/dashboard/abonnement`
- [ ] Cliquer sur "Convertir en abonnement payant"
- [ ] Choisir un plan
- [ ] Vérifier la redirection vers Lygos
- [ ] Simuler le paiement (ou utiliser test mode)
- [ ] Vérifier l'activation de l'abonnement
- [ ] Vérifier l'expiration de l'essai

#### Test 5.2 : Expiration Essai
- [ ] Créer un compte de test avec essai expiré (modifier manuellement la date)
- [ ] Se connecter
- [ ] Vérifier le blocage des commandes
- [ ] Vérifier la redirection vers abonnement
- [ ] Vérifier l'alerte d'expiration
- [ ] Tester la souscription après expiration

#### Test 5.3 : Notifications Essai
- [ ] Vérifier l'email de bienvenue (essai démarré)
- [ ] Modifier une date d'essai pour J-3
- [ ] Exécuter le job ProcessTrialExpiration
- [ ] Vérifier l'email J-3
- [ ] Répéter pour J-1 et J-0

---

### ✅ Phase 6 : Test Paiements

#### Test 6.1 : Paiement Commande (Lygos)
- [ ] Créer une commande avec paiement Lygos
- [ ] Vérifier la redirection vers Lygos
- [ ] Simuler le paiement
- [ ] Vérifier le callback de succès
- [ ] Vérifier la mise à jour du statut

#### Test 6.2 : Remboursements Partiels
- [ ] Créer une commande payée
- [ ] Modifier la commande (retirer un article)
- [ ] Vérifier le remboursement partiel via Lygos
- [ ] Vérifier l'enregistrement dans order_refunds

---

### ✅ Phase 7 : Test Notifications

#### Test 7.1 : Notifications Restaurant
- [ ] Créer une nouvelle commande
- [ ] Vérifier la notification au restaurant
- [ ] Modifier une commande (client)
- [ ] Vérifier la notification de modification

#### Test 7.2 : Notifications Client
- [ ] Modifier une commande (gestionnaire)
- [ ] Vérifier la notification au client
- [ ] Vérifier l'email et la notification in-app

---

### ✅ Phase 8 : Test Performance et Erreurs

#### Test 8.1 : Performance
- [ ] Tester avec plusieurs commandes simultanées
- [ ] Vérifier les temps de chargement
- [ ] Tester le rafraîchissement automatique

#### Test 8.2 : Gestion d'Erreurs
- [ ] Tester avec données invalides
- [ ] Tester les permissions
- [ ] Tester les cas limites (stock insuffisant, etc.)

---

## 📊 Résultats Attendus

### ✅ Critères de Succès

1. **Inscription** : Compte créé avec essai gratuit, aucun paiement requis
2. **Essai** : Accès complet pendant 14 jours
3. **Kanban** : Drag & drop fonctionnel, rafraîchissement automatique
4. **Rush** : Actions rapides fonctionnelles
5. **Modifications** : Gestionnaire et client peuvent modifier
6. **Stock** : Déduction/restauration automatique
7. **Expiration** : Blocage automatique après 14 jours
8. **Notifications** :** Emails et notifications in-app fonctionnels

---

## 🐛 Bugs à Vérifier

- [ ] Pas d'erreurs JavaScript dans la console
- [ ] Pas d'erreurs PHP dans les logs
- [ ] Les routes fonctionnent correctement
- [ ] Les permissions sont respectées
- [ ] Les transactions DB sont cohérentes

---

## 📝 Notes de Test

_À remplir pendant les tests..._
