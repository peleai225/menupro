# Analyse du Flux de Commandes Actuel - MenuPro

## 📋 Situation Actuelle (AVANT modifications)

### 1. Processus de Commande Client

#### Étape 1 : Consultation du Menu
- Le client visite la page publique du restaurant (`/r/{slug}/menu`)
- Il peut parcourir les catégories et plats
- Il ajoute des articles au panier avec options et instructions

#### Étape 2 : Passage de Commande
- Le client va au checkout (`/r/{slug}/checkout`)
- Il remplit ses informations (nom, email, téléphone)
- Il choisit le type de commande (sur place, à emporter, livraison)
- Il peut appliquer un code promo
- Il choisit le mode de paiement (Lygos ou paiement à la livraison)
- Il valide la commande

#### Étape 3 : Après la Commande
- **❌ Le client NE PEUT PAS modifier sa commande**
- Il peut seulement voir le statut via le lien de suivi
- Pour changer quelque chose, il doit :
  1. Contacter le restaurant par téléphone
  2. Le restaurant doit annuler la commande
  3. Le client doit refaire une nouvelle commande

### 2. Gestion des Commandes par le Restaurant

#### Interface Actuelle
- **Page principale** : `/dashboard/commandes` (liste avec filtres)
- **Détail d'une commande** : `/dashboard/commandes/{id}`
- **Board cuisine** : `/dashboard/commandes/board` (affichage pour la cuisine)

#### Actions Disponibles pour le Gestionnaire

✅ **Ce qui EST possible :**
1. **Voir la liste des commandes** avec filtres (statut, type, date, recherche)
2. **Voir le détail d'une commande** (articles, client, adresse, notes)
3. **Changer le statut** :
   - PENDING_PAYMENT → PAID
   - PAID → CONFIRMED (déduit le stock si activé)
   - CONFIRMED → PREPARING
   - PREPARING → READY
   - READY → DELIVERED
4. **Annuler une commande** (restaure le stock si activé)
5. **Ajouter des notes internes**
6. **Modifier le temps de préparation estimé**
7. **Imprimer le ticket de commande**

❌ **Ce qui N'EST PAS possible :**
1. **Ajouter un article** à une commande existante
2. **Retirer un article** d'une commande existante
3. **Modifier la quantité** d'un article
4. **Modifier les options** d'un article
5. **Modifier l'adresse de livraison** après la commande
6. **Modifier le type de commande** (sur place → livraison)
7. **Permettre au client de modifier** sa commande directement

### 3. Problèmes Identifiés (selon le PDG du bar)

#### 🚨 Problème Principal : Modification de Commande
**Scénario problématique :**
- Un client commande 2 bières
- Pendant qu'il attend, il veut changer pour 2 cocktails
- **Actuellement :** Impossible ! Il faut annuler et recréer

**Impact :**
- Perte de temps pour le gestionnaire
- Frustration du client
- Risque d'erreur (double commande, confusion)
- Complexité inutile

#### 🚨 Problème Secondaire : Interface Complexe
**Points soulevés :**
- Le gestionnaire doit gérer plusieurs choses en même temps
- L'interface est trop chargée
- Trop d'étapes pour des actions simples
- Difficile de gérer plusieurs commandes simultanément

### 4. Flux Actuel Détaillé

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUX ACTUEL D'UNE COMMANDE               │
└─────────────────────────────────────────────────────────────┘

CLIENT                                    RESTAURANT
  │                                           │
  ├─ 1. Consulte le menu                      │
  │   (ajoute au panier)                       │
  │                                           │
  ├─ 2. Va au checkout                        │
  │   (remplit infos, choisit paiement)        │
  │                                           │
  ├─ 3. Valide la commande                    │
  │   ────────────────────────────────────────┼─► Commande créée
  │                                           │   Status: PENDING_PAYMENT
  │                                           │
  ├─ 4. Paiement (Lygos ou COD)               │
  │   ────────────────────────────────────────┼─► Status: PAID
  │                                           │
  │                                           ├─► Notification envoyée
  │                                           │   au restaurant
  │                                           │
  │                                           ├─► Gestionnaire voit
  │                                           │   la commande dans la liste
  │                                           │
  │                                           ├─► Gestionnaire clique
  │                                           │   sur "Confirmer"
  │                                           │   ───────────────────┐
  │                                           │   Status: CONFIRMED   │
  │                                           │   Stock déduit (si)   │
  │                                           │   activé              │
  │                                           │                       │
  │                                           ├─► Gestionnaire change │
  │                                           │   le statut           │
  │                                           │   ────────────────────┤
  │                                           │   Status: PREPARING  │
  │                                           │                       │
  │                                           ├─► Gestionnaire change │
  │                                           │   le statut           │
  │                                           │   ────────────────────┤
  │                                           │   Status: READY       │
  │                                           │                       │
  │                                           ├─► Gestionnaire change │
  │                                           │   le statut           │
  │                                           │   ────────────────────┘
  │                                           │   Status: DELIVERED
  │                                           │
  └─ 5. Reçoit notification                  │
      (commande prête/livrée)                  │
```

### 5. Points de Friction Identifiés

#### Pour le Client
1. ❌ **Pas de modification possible** après commande
2. ❌ **Doit contacter le restaurant** pour tout changement
3. ❌ **Doit refaire une commande** si erreur

#### Pour le Gestionnaire
1. ❌ **Pas d'ajout/retrait d'articles** dans une commande
2. ❌ **Doit annuler et recréer** pour modifier
3. ⚠️ **Interface complexe** avec plusieurs pages
4. ⚠️ **Gestion simultanée difficile** (plusieurs commandes)
5. ⚠️ **Pas de vue d'ensemble rapide** des commandes actives

### 6. Scénarios Problématiques Courants

#### Scénario 1 : Client veut changer une boisson
```
1. Client commande 2 bières (Commande #123)
2. Client réalise qu'il veut 2 cocktails à la place
3. ACTUELLEMENT :
   - Client appelle le restaurant
   - Gestionnaire doit :
     a. Aller sur la commande #123
     b. Cliquer "Annuler" (restaure le stock)
     c. Attendre que le client refasse une commande
     d. Ou créer manuellement une nouvelle commande
   - Risque : double commande, confusion, perte de temps
```

#### Scénario 2 : Client veut ajouter un article
```
1. Client commande 1 plat (Commande #124)
2. Client veut ajouter une boisson
3. ACTUELLEMENT :
   - Client doit faire une NOUVELLE commande
   - Deux commandes séparées
   - Deux paiements (si Lygos)
   - Confusion pour la cuisine
```

#### Scénario 3 : Gestionnaire en période de rush
```
1. 10 commandes arrivent en même temps
2. Gestionnaire doit :
   - Ouvrir chaque commande individuellement
   - Changer le statut une par une
   - Gérer les demandes de modification par téléphone
   - Tout faire manuellement
3. PROBLÈME : Surcharge, erreurs possibles, lenteur
```

### 7. Solutions Proposées (à implémenter)

#### Solution 1 : Modification de Commande par le Client
- ✅ Permettre modification si statut = PENDING_PAYMENT ou PAID
- ✅ Bouton "Modifier ma commande" sur la page de suivi
- ✅ Limiter les modifications (ex: avant confirmation)

#### Solution 2 : Modification de Commande par le Gestionnaire
- ✅ Bouton "Modifier la commande" sur la page détail
- ✅ Ajouter/retirer des articles
- ✅ Modifier les quantités
- ✅ Recalcul automatique du total
- ✅ Gestion du stock (ajout/retrait)

#### Solution 3 : Interface Simplifiée
- ✅ Vue Kanban pour les commandes (comme Trello)
- ✅ Actions rapides (boutons de statut visibles)
- ✅ Vue d'ensemble en temps réel
- ✅ Notifications en temps réel (WebSocket ou polling)

#### Solution 4 : Mode "Rush" pour les Pics d'Activité
- ✅ Vue simplifiée avec seulement l'essentiel
- ✅ Actions en un clic
- ✅ Filtres rapides (nouvelles commandes uniquement)
- ✅ Affichage compact

### 8. Recommandations pour l'Essai de 14 Jours

#### Avant l'essai
1. ✅ Documenter les problèmes actuels
2. ✅ Former le gestionnaire sur l'interface actuelle
3. ✅ Expliquer les limitations
4. ✅ Proposer des solutions de contournement temporaires

#### Pendant l'essai
1. ✅ Noter tous les cas de modification demandés
2. ✅ Mesurer le temps perdu sur les modifications
3. ✅ Identifier les fonctionnalités les plus demandées
4. ✅ Recueillir les retours du gestionnaire

#### Après l'essai
1. ✅ Prioriser les améliorations selon les besoins réels
2. ✅ Implémenter les fonctionnalités critiques
3. ✅ Simplifier l'interface selon les retours

---

## 📊 Résumé Exécutif

**État Actuel :**
- ✅ Système fonctionnel pour les commandes standard
- ❌ Pas de modification de commande (client ou gestionnaire)
- ⚠️ Interface complexe pour la gestion en période de rush

**Problèmes Critiques :**
1. Modification de commande impossible
2. Interface trop chargée
3. Gestion simultanée difficile

**Priorités :**
1. **URGENT** : Permettre la modification de commande (gestionnaire)
2. **IMPORTANT** : Simplifier l'interface de gestion
3. **Souhaitable** : Modification par le client (avec limitations)
