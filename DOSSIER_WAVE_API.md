# Dossier de Demande d'Accès API Wave
## MenuPro — Plateforme SaaS de Gestion de Restaurants

**À l'attention de :** Wave CI — Équipe Partenariats & Intégrations API  
**Date :** Juillet 2026  
**Objet :** Ouverture d'un compte marchand Wave et accès aux API de paiement et de payout

---

## 1. Présentation de l'Entreprise

**Nom de la société :** [Nom légal de votre entreprise]  
**Forme juridique :** [SARL / SAS / etc.]  
**Siège social :** [Adresse, Abidjan, Côte d'Ivoire]  
**Numéro RCCM :** [À compléter]  
**Numéro DFE :** [À compléter]  
**Représentant légal :** [Nom et prénom]  
**Téléphone marchand Wave :** [Numéro pour création du compte marchand]  
**Email de contact :** [Email]  
**Site web :** https://www.menupro.ci

---

## 2. Présentation de l'Application MenuPro

### 2.1 Description générale

MenuPro est une plateforme SaaS (Software as a Service) basée en Côte d'Ivoire, dédiée à la **digitalisation complète des restaurants, hôtels et points de restauration** sur le territoire ivoirien et en Afrique de l'Ouest.

La plateforme permet à chaque établissement de :
- Créer et publier son **menu digital** accessible via QR code
- Recevoir et gérer des **commandes en ligne** (sur place, à emporter, en livraison)
- Gérer les **réservations de tables**
- Piloter son **activité en temps réel** depuis un tableau de bord (commandes, stock, statistiques)
- Disposer d'une **interface cuisine connectée** pour le suivi des préparations
- Proposer à ses clients un **paiement mobile instantané** via Wave, Orange Money et MTN Money

MenuPro s'adresse à toutes les typologies d'établissements : restaurants traditionnels, fast-foods, hôtels, maquis, stands de restauration, et tout point de vente proposant de la nourriture ou des boissons.

### 2.2 Modèle économique

MenuPro fonctionne sur un modèle d'abonnement mensuel (plans Essentiel, Pro, Business) facturé aux restaurateurs. Sur chaque transaction de commande payée via Wave, MenuPro perçoit une **commission de plateforme** (en pourcentage du montant de la commande) automatiquement reversée dans le portefeuille de la plateforme, puis le solde net est reversé au restaurateur via Wave Payout.

### 2.3 Volume et cible

- **Cible principale :** Restaurateurs et gérants d'établissements de restauration en Côte d'Ivoire
- **Clients finaux (payeurs) :** Clients des restaurants (particuliers ivoiriens et expatriés)
- **Panier moyen estimé :** 3 000 à 25 000 XOF par commande
- **Volume de transactions mensuel estimé (phase de démarrage) :** 500 à 2 000 transactions/mois
- **Croissance projetée :** multiplication par 5 en 18 mois avec l'expansion commerciale

---

## 3. Parcours Client (User Journey) — Paiement Wave

### 3.1 Vue d'ensemble du flux

```
Client scanne QR code
        ↓
Menu digital MenuPro (navigateur mobile)
        ↓
Sélection des plats + validation panier
        ↓
Saisie des informations de livraison/nom
        ↓
Choix du mode de paiement : Wave CI
        ↓
Création d'une session Wave Checkout (API MenuPro → API Wave)
        ↓
Redirection vers Wave (wave_launch_url)
        ↓
Client confirme le paiement dans l'app Wave
        ↓
Wave envoie un webhook à MenuPro (checkout.session.completed)
        ↓
MenuPro confirme la commande + notifie le restaurant
        ↓
Commission prélevée automatiquement → solde net reversé au restaurant (Payout Wave)
```

### 3.2 Étape par étape

#### Étape 1 — Découverte du menu
Le client scanne un QR code affiché sur la table, au comptoir ou dans une chambre d'hôtel. Il accède au menu digital du restaurant directement dans son navigateur mobile, **sans télécharger d'application**.

#### Étape 2 — Composition de la commande
Le client parcourt les catégories et plats, ajoute des articles à son panier, peut saisir des instructions spéciales (allergies, cuisson, etc.) et indique son numéro de table ou de chambre.

#### Étape 3 — Validation et paiement
À la page de paiement, le client choisit **Wave CI** parmi les modes proposés (Wave, Orange Money, MTN Money, paiement à la livraison). Un bouton « Payer avec Wave » déclenche la création de la session.

#### Étape 4 — Session Wave Checkout
MenuPro appelle l'API Wave (`POST /v1/checkout/sessions`) avec :
- Le montant total de la commande en XOF
- Une référence unique (`ORDER-{id}-{timestamp}`)
- L'URL de succès et l'URL d'erreur (retour sur MenuPro)
- Le nom du restaurant et la description de la commande

Wave retourne une `wave_launch_url` vers laquelle le client est immédiatement redirigé.

#### Étape 5 — Paiement dans l'app Wave
Le client est redirigé dans l'application Wave CI sur son téléphone. Il voit le montant, le bénéficiaire (MenuPro/restaurant) et valide avec son code PIN Wave. La transaction prend généralement moins de 10 secondes.

#### Étape 6 — Confirmation par webhook
Wave envoie un webhook `POST` signé (signature HMAC-SHA256) à l'endpoint sécurisé de MenuPro :
`https://www.menupro.ci/webhooks/wave`

MenuPro :
1. Vérifie la signature du webhook
2. Identifie la commande par le `checkout_id` ou la `client_reference`
3. Marque la commande comme payée (atomiquement, avec lock pour éviter le double-traitement)
4. Notifie le restaurant en temps réel (notification dashboard + son d'alerte)
5. Enregistre la transaction dans la base de données

#### Étape 7 — Reversement au restaurant (Payout)
Après prélèvement de la commission plateforme, MenuPro utilise l'**API Wave Payout** pour reverser automatiquement le solde net vers le compte Wave Business du restaurant. Ce reversement est déclenché dès confirmation du paiement.

#### Étape 8 — Suivi côté restaurant
Le restaurateur voit la commande apparaître en temps réel sur son tableau de bord MenuPro avec le statut « Payé via Wave ». L'écran cuisine est également mis à jour automatiquement.

---

## 4. Intégration Technique

### 4.1 Endpoints Wave utilisés

| Endpoint | Usage |
|----------|-------|
| `POST /v1/checkout/sessions` | Création d'une session de paiement pour une commande client |
| `POST /v1/payouts` | Reversement automatique du solde net au restaurant |
| `POST /webhooks/wave` (notre endpoint) | Réception des notifications Wave (paiement confirmé/échoué) |

### 4.2 Sécurité

- Toutes les communications avec l'API Wave se font en **HTTPS/TLS 1.2+**
- Les webhooks entrants sont **vérifiés par signature HMAC-SHA256** avant tout traitement
- La clé API Wave est stockée en base de données chiffrée, jamais dans le code source
- Les transactions sont traitées dans des **transactions atomiques** avec verrou (lock) pour garantir l'idempotence et éviter les doubles traitements en cas de webhook dupliqué
- Les clés API ne sont accessibles qu'aux administrateurs de la plateforme via une interface sécurisée

### 4.3 Stack technique

- **Serveur :** PHP 8.3 / Laravel 11, hébergé en Côte d'Ivoire (cPanel)
- **Domaine :** https://www.menupro.ci
- **Base de données :** MySQL 8
- **Application mobile livreurs :** Progressive Web App (PWA) React/TypeScript

### 4.4 Gestion des erreurs

- En cas d'échec du paiement Wave, l'événement `checkout.session.payment_failed` est capturé et la commande reste en statut « En attente ». Le client peut retenter le paiement.
- Les logs de paiement sont conservés dans un canal dédié pour audit.
- En cas de non-réception du webhook (timeout réseau), un mécanisme de vérification du statut est prévu.

---

## 5. Modèle de Reversement (Payout)

MenuPro collecte les paiements Wave sur son compte marchand plateforme, puis reverse automatiquement les montants dus à chaque restaurant via l'API Payout Wave. Ce modèle permet :

- Un suivi centralisé de toutes les transactions
- Un prélèvement automatique et transparent de la commission MenuPro
- Un reversement rapide aux restaurateurs (déclenchement immédiat à la confirmation du paiement)
- Un historique complet des transactions accessible au restaurateur depuis son dashboard

---

## 6. Documents Joints

*Les documents suivants sont joints au présent dossier :*

- [ ] Extrait du Registre du Commerce et du Crédit Mobilier (RCCM)
- [ ] Déclaration Fiscale d'Existence (DFE)
- [ ] Statuts de l'entreprise
- [ ] Pièce d'identité du représentant légal
- [ ] Pièce d'identité de l'administrateur désigné
- [ ] Captures d'écran de l'interface MenuPro (menu client, page paiement, dashboard restaurant)

---

## 7. Contact

Pour toute information complémentaire ou démonstration de la plateforme :

**Nom :** [Nom du représentant]  
**Fonction :** [Directeur / CEO / etc.]  
**Téléphone :** [Numéro]  
**Email :** [Email]  
**Disponibilité :** Nous sommes disponibles pour une démonstration en ligne ou en présentiel à Abidjan à votre convenance.

---

*MenuPro — Digitalisons la restauration africaine*  
*https://www.menupro.ci*
