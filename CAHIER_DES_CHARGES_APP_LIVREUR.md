# Cahier des charges — Application PWA Livreur MenuPro

> Version 1.1 — Juillet 2026
> Domaine : `driver.menupro.ci` | Backend : Laravel 11 + Sanctum
> Type : **Progressive Web App (PWA) — Vite + React + TypeScript**
> Modèle : identique à l'app client MPA (`C:\laragon\www\MPA`)

---

## Table des matières

1. [Contexte et objectifs](#1-contexte-et-objectifs)
2. [Profil utilisateur — le livreur](#2-profil-utilisateur--le-livreur)
3. [Parcours d'inscription et validation](#3-parcours-dinscription-et-validation)
4. [Écrans et fonctionnalités](#4-écrans-et-fonctionnalités)
5. [Flux de livraison complet](#5-flux-de-livraison-complet)
6. [Modèle de rémunération](#6-modèle-de-rémunération)
7. [Référence API — Endpoints livreur](#7-référence-api--endpoints-livreur)
8. [Notifications push Web](#8-notifications-push-web)
9. [Exigences techniques PWA](#9-exigences-techniques-pwa)
10. [Structure de projet recommandée](#10-structure-de-projet-recommandée)
11. [Ce qui n'est pas encore implémenté côté backend](#11-ce-qui-nest-pas-encore-implémenté-côté-backend)

---

## 1. Contexte et objectifs

### 1.1 Contexte

MenuPro est une plateforme de commande et livraison de repas opérant en Côte d'Ivoire. Le backend API est entièrement opérationnel. L'app client existe déjà (`MPA` — Vite + React PWA). L'app livreur est la brique manquante.

**Choix PWA :** pas besoin de passer par le Play Store, le livreur installe l'app en un tap depuis le navigateur Chrome. Fonctionne hors ligne, reçoit des notifications push, accède au GPS — exactement comme une app native.

### 1.2 Objectifs

- Recevoir, accepter et effectuer des livraisons
- Mettre à jour sa position GPS en continu pendant une course
- Gérer ses gains et demander un virement Wave
- Recevoir des notifications push pour les nouvelles courses

### 1.3 URL cible

```
https://driver.menupro.ci
```

Sous-domaine dédié, séparé de l'app client (`www.menupro.ci`).

---

## 2. Profil utilisateur — le livreur

### 2.1 Qui est-il ?

- Homme ou femme, 18–45 ans
- Basé à Abidjan ou dans une ville de l'intérieur (Bouaké, Yamoussoukro, San-Pédro, Korhogo…)
- Possède un smartphone Android milieu de gamme (Chrome)
- Véhicule : moto (majoritaire), vélo, voiture
- Connexion : 3G/4G variable, parfois instable

### 2.2 Ce qu'il attend

- Interface simple, grandes zones tactiles, lisible au soleil
- Carte GPS pour navigation
- Transparence totale sur les gains
- Paiement rapide (Wave)
- App installable sans passer par un store

---

## 3. Parcours d'inscription et validation

### 3.1 Étapes côté livreur

```
1. Ouvrir driver.menupro.ci dans Chrome
2. Bannière "Ajouter à l'écran d'accueil" → installer la PWA
3. Remplir le formulaire d'inscription :
   - Nom complet, téléphone, mot de passe
   - Ville de base + commune/zone
   - Type de véhicule + plaque d'immatriculation
4. Uploader 3 photos obligatoires :
   - Photo recto/verso CNI
   - Permis de conduire (ou permis moto)
   - Photo du véhicule
5. Soumettre → statut "En attente de validation"
6. Recevoir une notification push "Compte approuvé" → accès complet
```

> L'upload de photos se fait via `<input type="file" accept="image/*" capture="environment">` — ouvre directement l'appareil photo sur mobile.

### 3.2 Statuts du compte

| Statut | Description | Accès |
|--------|-------------|-------|
| `pending` | Dossier en cours de vérification | Écran d'attente uniquement |
| `approved` | Validé par l'admin MenuPro | Accès complet |
| `rejected` | Refusé (raison affichée) | Message + bouton corriger le dossier |
| `suspended` | Compte suspendu | Message de suspension |

### 3.3 Validation côté Super Admin

Via le back-office MenuPro (déjà implémenté) : liste des dossiers, visualisation des photos, approuver / rejeter / suspendre / réactiver.

---

## 4. Écrans et fonctionnalités

### 4.1 Connexion / Inscription

**Page `/login`**
- Champ téléphone + mot de passe
- Bouton "Créer un compte livreur"
- Token sauvegardé dans `localStorage`

**Page `/register`**
- Formulaire en plusieurs étapes (wizard) :
  1. Informations personnelles (nom, téléphone, mot de passe, ville, zone)
  2. Véhicule (type, plaque)
  3. Documents (3 uploads photo)
- Validation côté client avant envoi
- Après soumission → page d'attente avec statut `pending`

---

### 4.2 Tableau de bord — `/dashboard`

```
┌─────────────────────────────────────────┐
│  Bonjour Kouamé  ●─── EN LIGNE ──○      │
│                        [toggle on/off]   │
├──────────────┬──────────────────────────┤
│ Gains du jour│  Solde disponible        │
│  3 200 FCFA  │  12 500 FCFA             │
│              │  [Demander un virement]  │
├──────────────┴──────────────────────────┤
│  🛵  Courses disponibles      [4]  →    │
├─────────────────────────────────────────┤
│  Course active                          │
│  ► Chez Tante Marie → Riviera 3         │
│  [Voir la course]                       │
├─────────────────────────────────────────┤
│  Cette semaine : 18 livraisons          │
│  Note moyenne  : ★ 4.8                 │
└─────────────────────────────────────────┘
```

- Si une course est active → la bannière "Course active" est mise en avant et cliquable
- Si aucune course active + en ligne → la liste des courses disponibles est visible
- Toggle en ligne/hors ligne : appel `POST /driver/status`

---

### 4.3 Courses disponibles — `/deliveries`

Liste des livraisons disponibles dans la ville du livreur.

Pour chaque course :

```
┌─────────────────────────────────────────┐
│  🍽️  Chez Tante Marie — Cocody          │
│  → Riviera 3, Apt 12                    │
│                                         │
│  📍 4.2 km    ⏱ ~35 min    💰 1 200 F  │
│                                         │
│  [Refuser]            [Accepter ✓]      │
└─────────────────────────────────────────┘
```

- Polling toutes les **10 secondes** si en ligne (ou WebSocket si disponible)
- Une seule course à la fois — si course active, la liste est masquée

---

### 4.4 Course active — `/delivery/active`

Étapes progressives avec carte :

**ÉTAPE 1 — En route vers le restaurant**
```
┌─────────────────────────────────────────┐
│  [CARTE — position livreur + restaurant]│
│                                         │
│  Récupérer chez :                       │
│  Chez Tante Marie                       │
│  Rue des Bananiers, Cocody              │
│  [Ouvrir dans Google Maps / Waze]       │
│                                         │
│  Commande #PLT-AB12CD34                 │
│  Attiéké Poisson ×2 — 2 500 FCFA       │
│                                         │
│  [Je suis arrivé au restaurant]         │
└─────────────────────────────────────────┘
```

**ÉTAPE 2 — Commande récupérée**
```
│  [Commande récupérée — En livraison]    │
│  Livrer à :                             │
│  Riviera 3, Apt 12                      │
│  [Ouvrir dans Google Maps / Waze]       │
│  [Livraison effectuée ✓]               │
```

**ÉTAPE 3 — Livraison confirmée**
```
│  ✅ Livraison effectuée !              │
│  Gain : +1 200 FCFA ajoutés            │
│  Nouveau solde : 13 700 FCFA           │
│  [Retour au tableau de bord]           │
```

**Carte :**
- Leaflet.js + OpenStreetMap (gratuit, pas de clé API)
- Marqueur restaurant (rouge) + marqueur client (vert) + marqueur livreur (bleu, temps réel)
- Bouton "Naviguer" → deep link `https://maps.google.com/?q={lat},{lng}&navigate=yes`

**GPS continu :**
- `navigator.geolocation.watchPosition()` pendant la course
- Appel `PATCH /driver/location` toutes les 5 secondes max (throttle côté front)

---

### 4.5 Gains — `/earnings`

```
┌─────────────────────────────────────────┐
│  Solde disponible                        │
│  12 500 FCFA                            │
│  [Demander un virement Wave]            │
├─────────────────────────────────────────┤
│  Aujourd'hui    :  3 200 FCFA           │
│  Cette semaine  : 18 600 FCFA           │
│  Ce mois        : 67 400 FCFA           │
│  Total cumulé   : 245 000 FCFA          │
├─────────────────────────────────────────┤
│  Historique                             │
│  ▸ PLT-AB12 — +1 200 FCFA — 19/07 14h  │
│  ▸ PLT-CD34 — +  960 FCFA — 19/07 12h  │
│  ▸ PLT-EF56 — +1 040 FCFA — 19/07 10h  │
│  [Voir plus]                            │
└─────────────────────────────────────────┘
```

**Modal demande de virement :**
- Champ montant (min 500 FCFA, max = solde disponible)
- Champ numéro Wave (pré-rempli si déjà connu)
- Limite : 3 virements/jour
- Confirmation avant envoi

---

### 4.6 Profil — `/profile`

- Nom, téléphone, ville, zone
- Type de véhicule + plaque
- Note globale (étoiles)
- Nombre total de livraisons
- Statut du compte (badge coloré)
- Bouton déconnexion

---

### 4.7 Écran d'attente (compte `pending`)

Affiché après l'inscription tant que le compte n'est pas approuvé :

```
┌─────────────────────────────────────────┐
│  ⏳ Dossier en cours de vérification    │
│                                         │
│  Votre dossier a bien été reçu.         │
│  L'équipe MenuPro le vérifie sous       │
│  24–48h. Vous serez notifié dès         │
│  la validation.                         │
│                                         │
│  [Activer les notifications]            │
│  [Déconnexion]                          │
└─────────────────────────────────────────┘
```

---

## 5. Flux de livraison complet

```
RESTAURANT marque la commande "Prête"
        │
        ▼
Backend assigne un livreur disponible dans la ville
        │
        ▼
Livreur reçoit notification push Web "Nouvelle course 🛵"
        │
        ▼
PWA au premier plan → polling /deliveries/pending rafraîchi
Livreur voit la course → clique Accepter
        │
        ▼
POST /driver/deliveries/{id}/accept
  → statut : ASSIGNED
  → Client notifié : "Livreur assigné"
        │
        ▼
PATCH /driver/deliveries/{id}/status → "heading_to_restaurant"
  + watchPosition() GPS démarre
  + PATCH /driver/location toutes les 5s
        │
        ▼
PATCH /driver/deliveries/{id}/status → "picked_up"
  → Client notifié : "Commande récupérée, en route !"
        │
        ▼
PATCH /driver/deliveries/{id}/status → "delivering"
        │
        ▼
PATCH /driver/deliveries/{id}/status → "delivered"
  → Order → COMPLETED
  → DriverEarning créé (80% frais livraison)
  → Client notifié : "Livraison effectuée 🎉"
  → watchPosition() GPS s'arrête
  → Écran confirmation gain affiché
```

---

## 6. Modèle de rémunération

### 6.1 Principe

Le livreur reçoit **80 % des frais de livraison** payés par le client. MenuPro prélève **20 %**.

```
Frais client  =  base + (distance × tarif/km)  [× 1.20 en heure de pointe]
Gain livreur  =  frais × 80 %
Commission    =  frais × 20 %
```

### 6.2 Tarifs par zone

#### Abidjan

| Paramètre | Valeur |
|-----------|--------|
| Frais de base | 500 FCFA |
| Tarif au km | 150 FCFA/km |
| Distance max | 25 km |
| Heure de pointe | +20 % (11h–14h, 18h–21h) |
| Gain typique | 400 – 1 200 FCFA / livraison |
| Livraisons/jour | 8 – 15 |
| Revenu/jour estimé | 3 200 – 18 000 FCFA |

#### Villes de l'intérieur (Bouaké, Yamoussoukro, San-Pédro, Korhogo…)

| Paramètre | Valeur |
|-----------|--------|
| Frais de base | 800 FCFA |
| Tarif au km | 200 FCFA/km |
| Distance max | 15 km |
| Heure de pointe | +20 % (11h–14h, 18h–21h) |
| Gain typique | 640 – 2 000 FCFA / livraison |
| Livraisons/jour | 4 – 8 |
| Revenu/jour estimé | 2 560 – 16 000 FCFA |

> Ces valeurs sont configurables par ville dans le back-office — sans mise à jour de la PWA.

### 6.3 Exemples

| Ville | Distance | Moment | Frais client | Gain livreur |
|-------|---------|--------|-------------|-------------|
| Abidjan | 3 km | Normal | 950 FCFA | 760 FCFA |
| Abidjan | 5 km | Pointe | 1 500 FCFA | 1 200 FCFA |
| Bouaké | 4 km | Normal | 1 600 FCFA | 1 280 FCFA |
| Bouaké | 6 km | Pointe | 2 400 FCFA | 1 920 FCFA |

### 6.4 Paiement des gains

| Modalité | Détail |
|----------|--------|
| Disponibilité | Immédiate après livraison confirmée |
| Minimum de retrait | 500 FCFA |
| Limite journalière | 3 virements/jour |
| Méthode | Wave Mobile Money |
| Orange Money / MTN | Prévu — pas encore câblé |
| Délai | Immédiat (Wave Business) ou sous 24h (traitement manuel) |

### 6.5 Comparatif mensuel (25 jours)

| | Abidjan | Intérieur |
|---|---|---|
| Base/km | 500 + 150/km | 800 + 200/km |
| Revenu/jour | 3 200 – 18 000 FCFA | 2 560 – 16 000 FCFA |
| Revenu/mois | 80 000 – 450 000 FCFA | 64 000 – 400 000 FCFA |

---

## 7. Référence API — Endpoints livreur

Base URL : `https://menupro.ci/api/v1`
Auth : `Authorization: Bearer {token}`

### 7.1 Auth

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/driver/auth/register` | Inscription (multipart/form-data — photos) |
| POST | `/driver/auth/login` | Connexion → token |
| GET | `/driver/auth/me` | Profil complet |
| POST | `/driver/auth/logout` | Révocation token |
| PATCH | `/driver/auth/fcm-token` | Token push Web |

### 7.2 Statut & GPS

| Méthode | Endpoint | Corps |
|---------|----------|-------|
| POST | `/driver/status` | `{ "online": true }` |
| PATCH | `/driver/location` | `{ "lat", "lng", "accuracy", "speed", "heading" }` |

### 7.3 Courses

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/driver/deliveries/pending` | Courses disponibles |
| GET | `/driver/deliveries/active` | Course en cours |
| POST | `/driver/deliveries/{id}/accept` | Accepter |
| POST | `/driver/deliveries/{id}/decline` | Refuser |
| PATCH | `/driver/deliveries/{id}/status` | Avancer le statut |

Transitions : `ASSIGNED → heading_to_restaurant → picked_up → delivering → delivered`

### 7.4 Gains

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/driver/earnings` | Résumé (solde, jour, semaine, total) |
| GET | `/driver/earnings/history` | Historique paginé |
| POST | `/driver/earnings/payout` | Demande virement Wave |

---

## 8. Notifications push Web

La PWA utilise l'**API Web Push** (VAPID) + **Service Worker** pour recevoir des notifications même quand la PWA est fermée.

### 8.1 Enregistrement

```javascript
// Dans le Service Worker
const subscription = await registration.pushManager.subscribe({
  userVisibleOnly: true,
  applicationServerKey: VAPID_PUBLIC_KEY,
});
// Envoyer subscription au backend → PATCH /driver/auth/fcm-token
```

> Le backend utilise FCM HTTP v1 — compatible avec les souscriptions Web Push via FCM.

### 8.2 Notifications reçues

| Déclencheur | Titre | Corps |
|------------|-------|-------|
| Nouvelle course disponible | "Nouvelle course 🛵" | "{restaurant} — Gain : {montant} FCFA" |
| Compte approuvé | "Compte activé 🎉" | "Vous pouvez commencer à livrer !" |
| Compte rejeté | "Dossier refusé ❌" | "{raison}" |
| Virement envoyé | "Virement reçu 💸" | "{montant} FCFA sur votre Wave" |

### 8.3 Comportement

- Clic sur la notification → ouvrir la PWA sur l'écran pertinent (courses disponibles)
- Si PWA déjà ouverte → afficher une bannière in-app à la place
- Demander la permission push après la connexion réussie (pas au chargement de la page)

---

## 9. Exigences techniques PWA

### 9.1 Stack — identique à MPA

| Élément | Technologie |
|---------|------------|
| Framework | **Vite + React 18 + TypeScript** |
| Style | **Tailwind CSS** |
| Routing | `react-router-dom` v6 |
| Carte | **Leaflet.js** + `react-leaflet` (OpenStreetMap — gratuit) |
| GPS | `navigator.geolocation.watchPosition()` |
| Push notifications | Web Push API + Service Worker |
| HTTP | `fetch` natif |
| Auth storage | `localStorage` (token + profil) |
| PWA | `vite-plugin-pwa` (Workbox) |
| Icons | `lucide-react` |

### 9.2 Manifest PWA

```json
{
  "name": "MenuPro Livreur",
  "short_name": "Livreur",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#0F172A",
  "theme_color": "#F97316",
  "icons": [
    { "src": "/icon-192.png", "sizes": "192x192", "type": "image/png" },
    { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png" }
  ]
}
```

### 9.3 GPS dans le navigateur

```javascript
// Démarrer pendant une course active
const watchId = navigator.geolocation.watchPosition(
  (pos) => sendLocation(pos.coords),
  (err) => console.warn(err),
  { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
);

// Arrêter à la fin de la course
navigator.geolocation.clearWatch(watchId);
```

> Sur Android Chrome, le GPS fonctionne même en arrière-plan si la page est ouverte dans l'onglet. Si la PWA est installée (mode `standalone`), le comportement est proche d'une app native.

### 9.4 Comportement hors ligne

| Situation | Comportement |
|-----------|-------------|
| Pas de connexion au démarrage | Afficher bannière "Hors ligne" |
| Perte connexion pendant une course | Stocker les updates GPS dans une queue, renvoyer à la reconnexion |
| Perte connexion pour changer de statut | Retry automatique × 3, puis afficher un message d'erreur |
| Assets (CSS/JS) | Mis en cache par le Service Worker (Workbox) — app chargeable hors ligne |

### 9.5 Sécurité

- Token Bearer stocké dans `localStorage` (même pattern que MPA)
- HTTPS obligatoire (requis pour GPS + Push + PWA)
- Toutes les requêtes API passent par `https://menupro.ci/api/v1`
- CORS : `driver.menupro.ci` à ajouter dans la config CORS du backend

---

## 10. Structure de projet recommandée

```
driver-pwa/
├── public/
│   ├── manifest.json
│   ├── sw.js              ← Service Worker (généré par vite-plugin-pwa)
│   ├── icon-192.png
│   └── icon-512.png
├── src/
│   ├── lib/
│   │   ├── api.ts         ← Toutes les fonctions fetch (identique à MPA/src/lib/api.ts)
│   │   ├── auth.tsx       ← AuthContext (token, profil livreur)
│   │   ├── types.ts       ← Types TypeScript (Driver, Delivery, Earning…)
│   │   ├── format.ts      ← formatFCFA, timeAgo, formatDate
│   │   └── geo.ts         ← watchPosition, sendLocation, haversine
│   ├── pages/
│   │   ├── LoginPage.tsx
│   │   ├── RegisterPage.tsx
│   │   ├── PendingPage.tsx
│   │   ├── DashboardPage.tsx
│   │   ├── DeliveriesPage.tsx
│   │   ├── ActiveDeliveryPage.tsx
│   │   ├── EarningsPage.tsx
│   │   └── ProfilePage.tsx
│   ├── components/
│   │   ├── DeliveryCard.tsx
│   │   ├── EarningItem.tsx
│   │   ├── Map.tsx        ← react-leaflet wrapper
│   │   ├── StatusToggle.tsx
│   │   └── PayoutModal.tsx
│   ├── App.tsx            ← Routes + AuthGuard
│   └── main.tsx
├── index.html
├── vite.config.ts
├── tailwind.config.ts
└── tsconfig.json
```

---

## 11. Ce qui n'est pas encore implémenté côté backend

Ces fonctionnalités nécessitent un développement backend avant intégration dans la PWA :

| Fonctionnalité | Priorité | Note |
|---------------|---------|------|
| Notation livreur par le client | P1 | Champs `rating` existent, endpoint POST manquant |
| Notification FCM de rejet de dossier | P1 | TODO dans le code |
| PATCH profil livreur (zone, véhicule) | P1 | Endpoint manquant |
| Réassignation automatique si pas de réponse | P2 | Pas de timeout/job |
| Orange Money / MTN payout | P2 | Validation OK, logique non câblée |
| Multi-livraison (batch) | V2 | 1 course à la fois actuellement |

---

*Document préparé depuis l'état du backend MenuPro — branche `main` — Juillet 2026.*
