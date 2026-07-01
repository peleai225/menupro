# Guide complet — PWA Plateforme de Livraison MenuPro
> Application séparée du projet Laravel. Ce guide couvre l'installation, la configuration, toutes les APIs disponibles et l'implémentation complète.

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Stack technologique](#2-stack-technologique)
3. [Installation](#3-installation)
4. [Structure des projets](#4-structure-des-projets)
5. [Configuration](#5-configuration)
6. [APIs disponibles — Référence complète](#6-apis-disponibles--référence-complète)
7. [Implémentation — App Client](#7-implémentation--app-client)
8. [Implémentation — App Livreur](#8-implémentation--app-livreur)
9. [Temps réel — Laravel Reverb](#9-temps-réel--laravel-reverb)
10. [Design System](#10-design-system)
11. [PWA — Configuration](#11-pwa--configuration)
12. [Déploiement](#12-déploiement)

---

## 1. Vue d'ensemble

Deux applications PWA indépendantes, une seule API backend (MenuPro Laravel).

```
┌─────────────────────────────────────────────────────┐
│                   BACKEND MENUPRO                    │
│           Laravel 12 — API REST + Reverb             │
│         https://ton-domaine.com/api/v1/              │
└──────────────────┬──────────────────┬───────────────┘
                   │                  │
       ┌───────────▼──────┐  ┌────────▼──────────┐
       │  menupro-delivery │  │  menupro-driver    │
       │  App Client PWA   │  │  App Livreur PWA   │
       │  Next.js 14       │  │  Next.js 14        │
       │  localhost:3000   │  │  localhost:3001    │
       └──────────────────┘  └────────────────────┘
```

**Périmètre app client :**
- Parcourir les restaurants par ville / distance
- Consulter les menus et passer commande
- Payer via Wave / Orange Money
- Suivre la livraison en temps réel (carte + statut)

**Périmètre app livreur :**
- S'inscrire, envoyer ses documents
- Passer en ligne / hors ligne
- Voir et accepter les courses disponibles
- Mettre à jour le statut en temps réel
- Consulter ses gains et demander un virement

---

## 2. Stack technologique

### Les deux applications

| Catégorie | Outil | Version | Raison |
|---|---|---|---|
| Framework | Next.js | 14 (App Router) | SSR + PWA natif |
| Langage | TypeScript | 5+ | Typage strict |
| Styling | Tailwind CSS | 3 | Utility-first, mobile-first |
| Composants UI | shadcn/ui | latest | Accessibles, personnalisables |
| Icônes | Lucide React | latest | Cohérent avec shadcn |
| Animations | Framer Motion | 11 | Transitions fluides |
| Requêtes HTTP | Axios | 1.7 | Interceptors auth + erreurs |
| Cache données | TanStack Query | 5 | Cache offline, refetch auto |
| État global | Zustand | 4 | Léger, persist localStorage |
| Formulaires | React Hook Form + Zod | latest | Validation type-safe |
| Cartes | React-Leaflet | 4 | Open source, gratuit |
| Tuiles carte | OpenStreetMap | — | Gratuit, pas de clé API |
| Temps réel | Laravel Echo + Pusher-js | latest | Driver Reverb WebSocket |
| PWA | next-pwa | latest | Service worker automatique |
| Dates | date-fns | 3 | Léger, tree-shakable |
| Toasts | sonner | latest | Notifications élégantes |

---

## 3. Installation

### Prérequis
```bash
node >= 18
npm >= 9
```

### App Client
```bash
npx create-next-app@latest menupro-delivery \
  --typescript \
  --tailwind \
  --app \
  --src-dir \
  --import-alias "@/*"

cd menupro-delivery

# Initialiser shadcn
npx shadcn@latest init
# Choisir : Default style, Orange comme couleur principale, CSS variables: oui

# Ajouter les composants shadcn nécessaires
npx shadcn@latest add button input card badge sheet dialog \
  toast skeleton tabs avatar separator scroll-area progress \
  dropdown-menu drawer

# Dépendances métier
npm install \
  @tanstack/react-query \
  @tanstack/react-query-devtools \
  zustand \
  axios \
  react-hook-form \
  @hookform/resolvers \
  zod \
  framer-motion \
  lucide-react \
  react-leaflet \
  leaflet \
  laravel-echo \
  pusher-js \
  next-pwa \
  sonner \
  clsx \
  tailwind-merge \
  date-fns \
  @radix-ui/react-icons

# Types
npm install -D @types/leaflet @types/node
```

### App Livreur
```bash
npx create-next-app@latest menupro-driver \
  --typescript \
  --tailwind \
  --app \
  --src-dir \
  --import-alias "@/*"

cd menupro-driver

npx shadcn@latest init
npx shadcn@latest add button input card badge sheet dialog \
  toast skeleton avatar separator progress drawer

npm install \
  @tanstack/react-query \
  zustand \
  axios \
  react-hook-form \
  @hookform/resolvers \
  zod \
  framer-motion \
  lucide-react \
  react-leaflet \
  leaflet \
  laravel-echo \
  pusher-js \
  next-pwa \
  sonner \
  clsx \
  tailwind-merge \
  date-fns

npm install -D @types/leaflet @types/node
```

---

## 4. Structure des projets

### App Client — `menupro-delivery/src/`

```
src/
├── app/
│   ├── (auth)/
│   │   ├── layout.tsx
│   │   ├── login/
│   │   │   └── page.tsx
│   │   └── register/
│   │       └── page.tsx
│   ├── (main)/
│   │   ├── layout.tsx              ← Bottom nav + TopBar
│   │   ├── page.tsx                ← Accueil : restaurants proches
│   │   ├── restaurants/
│   │   │   ├── page.tsx            ← Liste avec filtres ville/catégorie
│   │   │   └── [id]/
│   │   │       ├── page.tsx        ← Détail restaurant + infos
│   │   │       └── menu/
│   │   │           └── page.tsx    ← Menu complet + panier flottant
│   │   ├── cart/
│   │   │   └── page.tsx            ← Récap panier + adresse livraison
│   │   ├── checkout/
│   │   │   └── page.tsx            ← Estimation frais + paiement Wave
│   │   ├── orders/
│   │   │   ├── page.tsx            ← Historique commandes
│   │   │   └── track/
│   │   │       └── [token]/
│   │   │           └── page.tsx    ← Suivi temps réel (carte + timeline)
│   │   └── profile/
│   │       ├── page.tsx            ← Infos profil
│   │       └── addresses/
│   │           └── page.tsx        ← Adresses sauvegardées
│   ├── layout.tsx                  ← Root layout (QueryProvider, Toaster)
│   ├── manifest.ts                 ← Manifest PWA
│   ├── globals.css
│   └── icon.png
├── components/
│   ├── ui/                         ← Composants shadcn générés
│   ├── layout/
│   │   ├── BottomNav.tsx           ← Navigation bas d'écran
│   │   ├── TopBar.tsx              ← Barre du haut (titre + actions)
│   │   └── PageTransition.tsx      ← Wrapper Framer Motion
│   ├── restaurant/
│   │   ├── RestaurantCard.tsx      ← Card restaurant (image, note, distance)
│   │   ├── RestaurantGrid.tsx      ← Grille avec skeleton loading
│   │   ├── DishCard.tsx            ← Card plat (photo, prix, +/-)
│   │   ├── CategoryTabs.tsx        ← Onglets catégories menu
│   │   └── DeliveryBadge.tsx       ← Frais + temps estimé
│   ├── cart/
│   │   ├── CartDrawer.tsx          ← Panier glissant depuis le bas
│   │   ├── CartItem.tsx            ← Ligne panier (nom, qté, prix)
│   │   ├── CartSummary.tsx         ← Total + bouton commander
│   │   └── CartFAB.tsx             ← Bouton flottant "Voir le panier"
│   ├── order/
│   │   ├── OrderCard.tsx           ← Carte historique commande
│   │   ├── TrackingMap.tsx         ← Carte Leaflet (livreur en direct)
│   │   ├── DeliveryTimeline.tsx    ← Étapes visuelles de livraison
│   │   └── DriverInfo.tsx          ← Nom, photo, note, téléphone livreur
│   └── common/
│       ├── LocationPicker.tsx      ← Sélecteur adresse + carte
│       ├── PriceTag.tsx            ← Affichage prix en FCFA
│       ├── RatingStars.tsx         ← Étoiles de notation
│       └── EmptyState.tsx          ← Illustration état vide
├── hooks/
│   ├── useAuth.ts                  ← Auth client (login, register, me)
│   ├── useCart.ts                  ← Actions panier
│   ├── useGeolocation.ts           ← Position GPS navigateur
│   ├── useOrderTracking.ts         ← WebSocket suivi commande
│   ├── useRestaurants.ts           ← Requêtes restaurants
│   └── useOrders.ts                ← Commandes + historique
├── lib/
│   ├── api.ts                      ← Instance Axios configurée
│   ├── echo.ts                     ← Instance Laravel Echo
│   ├── queryClient.ts              ← TanStack Query client
│   └── utils.ts                    ← cn(), formatPrice(), etc.
├── stores/
│   ├── authStore.ts                ← Token + infos client
│   └── cartStore.ts                ← Panier (persist)
└── types/
    ├── api.ts                      ← Types réponses API
    ├── restaurant.ts
    ├── order.ts
    └── driver.ts
```

### App Livreur — `menupro-driver/src/`

```
src/
├── app/
│   ├── (auth)/
│   │   ├── login/page.tsx
│   │   └── register/page.tsx       ← Formulaire + upload 3 photos
│   ├── (main)/
│   │   ├── layout.tsx              ← Bottom nav livreur
│   │   ├── page.tsx                ← Dashboard (statut + course active)
│   │   ├── deliveries/
│   │   │   ├── page.tsx            ← Courses disponibles
│   │   │   └── [id]/page.tsx       ← Détail course + navigation
│   │   ├── earnings/
│   │   │   └── page.tsx            ← Gains + demande virement
│   │   └── profile/
│   │       └── page.tsx
│   ├── layout.tsx
│   ├── manifest.ts
│   └── globals.css
├── components/
│   ├── layout/
│   │   ├── DriverBottomNav.tsx
│   │   └── OnlineToggle.tsx        ← Switch En ligne / Hors ligne
│   ├── delivery/
│   │   ├── DeliveryCard.tsx        ← Course disponible (adresses, gains)
│   │   ├── ActiveDelivery.tsx      ← Course en cours avec actions
│   │   ├── StatusStepper.tsx       ← Étapes (Assigné→Récupéré→Livré)
│   │   └── NavigationMap.tsx       ← Carte avec itinéraire
│   └── earnings/
│       ├── EarningsSummary.tsx     ← Solde + stats jour/semaine
│       ├── EarningsHistory.tsx     ← Liste des courses payées
│       └── PayoutForm.tsx          ← Demande virement Mobile Money
├── hooks/
│   ├── useDriverAuth.ts
│   ├── useGpsTracking.ts           ← watchPosition + envoi API
│   ├── useDeliveries.ts
│   └── useDriverEarnings.ts
├── lib/
│   ├── api.ts
│   ├── echo.ts
│   └── utils.ts
└── stores/
    ├── driverAuthStore.ts
    └── driverStatusStore.ts        ← is_online, activeDelivery
```

---

## 5. Configuration

### `src/lib/api.ts` (identique dans les deux apps)

```typescript
import axios from 'axios'

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL + '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
})

api.interceptors.request.use((config) => {
  const token = typeof window !== 'undefined'
    ? localStorage.getItem('token')
    : null
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

### `src/lib/echo.ts`

```typescript
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echo: Echo | null = null

export function getEcho(): Echo {
  if (!echo && typeof window !== 'undefined') {
    (window as any).Pusher = Pusher

    echo = new Echo({
      broadcaster: 'reverb',
      key: process.env.NEXT_PUBLIC_REVERB_APP_KEY!,
      wsHost: process.env.NEXT_PUBLIC_REVERB_HOST!,
      wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 80),
      wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 443),
      forceTLS: process.env.NEXT_PUBLIC_REVERB_SCHEME === 'https',
      enabledTransports: ['ws', 'wss'],
    })
  }
  return echo!
}
```

### `src/lib/queryClient.ts`

```typescript
import { QueryClient } from '@tanstack/react-query'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,     // 5 minutes
      gcTime: 1000 * 60 * 30,        // 30 minutes en cache
      retry: 2,
      refetchOnWindowFocus: false,
    },
  },
})
```

### `src/lib/utils.ts`

```typescript
import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Convertit les centimes XOF en string affichable
export function formatPrice(centimes: number): string {
  return new Intl.NumberFormat('fr-CI', {
    style: 'currency',
    currency: 'XOF',
    minimumFractionDigits: 0,
  }).format(centimes / 100)
}

// Durée lisible : "25 min"
export function formatMinutes(minutes: number): string {
  if (minutes < 60) return `${minutes} min`
  return `${Math.floor(minutes / 60)}h${minutes % 60 > 0 ? minutes % 60 : ''}`
}

// Distance lisible : "3.2 km" ou "800 m"
export function formatDistance(km: number): string {
  if (km < 1) return `${Math.round(km * 1000)} m`
  return `${km.toFixed(1)} km`
}
```

### Variables d'environnement

#### `menupro-delivery/.env.local`
```env
NEXT_PUBLIC_API_URL=https://ton-domaine.com
NEXT_PUBLIC_REVERB_APP_KEY=ton_reverb_app_key
NEXT_PUBLIC_REVERB_HOST=ton-domaine.com
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
NEXT_PUBLIC_APP_NAME=MenuPro Delivery
```

#### `menupro-driver/.env.local`
```env
NEXT_PUBLIC_API_URL=https://ton-domaine.com
NEXT_PUBLIC_REVERB_APP_KEY=ton_reverb_app_key
NEXT_PUBLIC_REVERB_HOST=ton-domaine.com
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
NEXT_PUBLIC_APP_NAME=MenuPro Driver
```

---

## 6. APIs disponibles — Référence complète

> **Base URL :** `https://ton-domaine.com/api/v1`
> **Auth :** Header `Authorization: Bearer {token}` pour les routes protégées
> **Format :** JSON — `Content-Type: application/json`

---

### 6.1 — AUTH CLIENT

#### Inscription
```
POST /client/auth/register
Rate limit : 5 req/heure/IP

Body :
{
  "name": "Kouassi Amed",
  "phone": "0708121520",
  "email": "amed@email.com",       // optionnel
  "password": "monmotdepasse",
  "city": "Abidjan"                // optionnel
}

Réponse 201 :
{
  "token": "1|xxxxxxxxxxxxxx",
  "customer": {
    "id": 1,
    "name": "Kouassi Amed",
    "email": "amed@email.com",
    "phone": "0708121520",
    "city": "Abidjan",
    "total_orders": 0
  }
}
```

#### Connexion
```
POST /client/auth/login
Rate limit : 10 req/min/IP

Body :
{
  "phone": "0708121520",
  "password": "monmotdepasse"
}

Réponse 200 :
{
  "token": "1|xxxxxxxxxxxxxx",
  "customer": { ...même structure... }
}

Erreurs :
  404 → Compte introuvable
  401 → Mot de passe incorrect
  403 → Compte suspendu
```

#### Profil connecté
```
GET /client/auth/me
Auth : requis

Réponse 200 :
{
  "id": 1,
  "name": "Kouassi Amed",
  "phone": "0708121520",
  "city": "Abidjan",
  "total_orders": 5
}
```

#### Déconnexion
```
POST /client/auth/logout
Auth : requis

Réponse 200 : { "message": "Déconnecté." }
```

#### Modifier le profil
```
PATCH /client/auth/profile
Auth : requis

Body (tous optionnels) :
{
  "name": "Nouveau nom",
  "email": "nouveau@email.com",
  "city": "Bouaké"
}
```

---

### 6.2 — RESTAURANTS (public)

#### Liste des restaurants
```
GET /restaurants
Rate limit : 120 req/min/IP

Query params :
  city       → "Abidjan" | "Bouaké" | ...
  category   → "fast_food" | "restaurant" | "pizza" | "poulet" | ...
  lat        → latitude client (ex: 5.3542)
  lng        → longitude client (ex: -3.9827)
  open_now   → true | false

Réponse 200 :
{
  "data": [
    {
      "id": 1,
      "name": "Maquis Le Bon Coin",
      "slug": "maquis-le-bon-coin",
      "category": "restaurant",
      "city": "Abidjan",
      "address": "Cocody Angré, Rue des Jardins",
      "phone": "0709123456",
      "logo_url": "https://ton-domaine.com/storage/restaurants/logo.jpg",
      "banner_url": "https://ton-domaine.com/storage/restaurants/banner.jpg",
      "is_open": true,
      "min_order_amount": 200000,    // centimes = 2000 FCFA
      "avg_prep_time": 20,
      "latitude": "5.3812",
      "longitude": "-3.9561",
      "distance_km": 2.3             // présent si lat+lng fournis
    }
  ]
}
```

#### Restaurants proches avec frais de livraison
```
GET /restaurants/nearby?lat=5.3542&lng=-3.9827&radius_km=10

Réponse 200 :
{
  "data": [
    {
      ...même structure...,
      "distance_km": 1.8,
      "delivery_fee": 77000,         // centimes = 770 FCFA
      "estimated_minutes": 35,
      "within_range": true
    }
  ]
}
```

#### Détail d'un restaurant
```
GET /restaurants/{id}

Réponse 200 :
{
  "id": 1,
  "name": "Maquis Le Bon Coin",
  "description": "Cuisine ivoirienne authentique...",
  "tagline": "Le goût de chez vous",
  "is_open": true,
  "opening_hours": {
    "monday": { "open": "08:00", "close": "22:00" },
    ...
  },
  "delivery_base_fee": 50000,       // 500 FCFA
  "delivery_fee_per_km": 15000,     // 150 FCFA/km
  "max_delivery_km": 10,
  ...
}
```

#### Menu complet
```
GET /restaurants/{id}/menu

Réponse 200 :
{
  "restaurant_id": 1,
  "currency": "XOF",
  "categories": [
    {
      "id": 1,
      "name": "Entrées",
      "dishes": [
        {
          "id": 10,
          "name": "Attieké poisson",
          "description": "Attieké frais avec poisson braisé",
          "price": 300000,           // centimes = 3000 FCFA
          "compare_price": 350000,   // prix barré optionnel
          "image_url": "https://...",
          "is_available": true,
          "is_featured": true,
          "is_spicy": false,
          "is_vegetarian": false,
          "prep_time": 15,
          "calories": 450
        }
      ]
    }
  ]
}
```

#### Estimation frais de livraison
```
GET /restaurants/{id}/delivery-estimate?lat=5.3200&lng=-4.0100

Réponse 200 (livrable) :
{
  "deliverable": true,
  "delivery_fee": 95000,            // 950 FCFA
  "distance_km": 3.2,
  "estimated_minutes": 38,
  "is_peak_hour": false,
  "breakdown": {
    "base_fee": 50000,
    "distance_fee": 48000,
    "peak_surcharge": 0,
    "prep_minutes": 20,
    "transit_minutes": 18
  }
}

Réponse 422 (hors zone) :
{
  "deliverable": false,
  "message": "Ce restaurant ne livre pas à cette adresse.",
  "distance_km": 14.5,
  "max_distance": 10
}
```

---

### 6.3 — ADRESSES CLIENT

```
GET    /client/addresses              → liste des adresses sauvegardées
POST   /client/addresses              → ajouter une adresse
PATCH  /client/addresses/{id}         → modifier
DELETE /client/addresses/{id}         → supprimer

Body pour POST/PATCH :
{
  "label": "Maison",                  // ou "Bureau", "Famille"...
  "address": "Angré 8ème tranche, résidence Les Acacias",
  "city": "Abidjan",
  "zone": "Cocody",
  "latitude": 5.3812,
  "longitude": -3.9561,
  "instructions": "Portail bleu, 2ème bâtiment",
  "is_default": true
}
```

---

### 6.4 — COMMANDES CLIENT

#### Créer une commande
```
POST /client/orders
Auth : requis
Rate limit : 20 req/heure/client

Body :
{
  "restaurant_id": 1,
  "items": [
    {
      "dish_id": 10,
      "quantity": 2,
      "notes": "Sans piment"          // optionnel
    },
    {
      "dish_id": 15,
      "quantity": 1
    }
  ],
  "delivery_lat": 5.3200,
  "delivery_lng": -4.0100,
  "delivery_address": "Yopougon Selmer, rue 12",
  "delivery_city": "Abidjan",
  "delivery_instructions": "Appeler en arrivant",
  "customer_notes": "Commande urgente",
  "payment_method": "wave"            // wave | orange_money | mtn_money | cash
}

Réponse 201 :
{
  "order": {
    "id": 42,
    "reference": "PLT-AB3CD4EF",
    "tracking_token": "xxxxxxxxxxxxxxxxxxx",
    "status": "pending_payment",
    "status_label": "En attente de paiement",
    "payment_status": "pending",
    "payment_method": "wave",
    "subtotal": 600000,
    "delivery_fee": 95000,
    "total": 695000,
    "estimated_minutes": 38,
    "items": [
      { "name": "Attieké poisson", "quantity": 2, "unit_price": 300000, "total": 600000 }
    ],
    "created_at": "2026-07-01T10:30:00Z"
  },
  "tracking_token": "xxxxxxxxxxxxxxxxxxx",
  "next_step": "payment",
  "payment_url": "/api/v1/client/payment/42/initiate"
}
```

#### Suivi commande (public — sans connexion)
```
GET /client/orders/track/{tracking_token}

Réponse 200 :
{
  "order_status": "delivering",
  "order_status_label": "En livraison",
  "estimated_minutes": 12,
  "delivery": {
    "status": "delivering",
    "status_label": "En livraison",
    "driver": {
      "name": "Ibrahim Koné",
      "phone": "0707654321",
      "latitude": 5.3300,
      "longitude": -4.0050,
      "vehicle": "moto",
      "rating": "4.80"
    }
  },
  "timeline": {
    "ordered_at": "2026-07-01T10:30:00Z",
    "confirmed_at": "2026-07-01T10:33:00Z",
    "preparing_at": "2026-07-01T10:35:00Z",
    "ready_at": "2026-07-01T10:55:00Z",
    "driver_assigned_at": "2026-07-01T10:56:00Z",
    "picked_up_at": "2026-07-01T11:05:00Z",
    "completed_at": null
  }
}
```

#### Historique commandes
```
GET /client/orders/history
Auth : requis

Query : ?page=1

Réponse 200 :
{
  "data": [ ...liste commandes... ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "total": 45
  }
}
```

#### Annuler une commande
```
POST /client/orders/{id}/cancel
Auth : requis

Réponse 200 : { "message": "Commande annulée." }
Réponse 422 : { "message": "Cette commande ne peut plus être annulée." }
```

---

### 6.5 — PAIEMENT

#### Initier un paiement Wave
```
POST /client/payment/{orderId}/initiate
Auth : requis
Rate limit : 5 req/5min/client

Réponse 200 :
{
  "payment_url": "https://pay.wave.com/checkout/xxx",
  "session_id": "wave_session_xxx",
  "order_id": 42,
  "amount": 695000,
  "tracking_token": "xxxxxxxxxxxxxxxxxxx"
}

→ Rediriger l'utilisateur vers payment_url
→ Wave redirige vers /client/payment/success?token=xxx ou /client/payment/error?token=xxx
```

#### Vérifier le statut
```
GET /client/payment/{orderId}/status
Auth : requis

Réponse 200 :
{
  "order_id": 42,
  "payment_status": "completed",
  "order_status": "paid",
  "paid_at": "2026-07-01T10:32:00Z"
}
```

---

### 6.6 — AUTH LIVREUR

#### Inscription livreur
```
POST /driver/auth/register
Rate limit : 5 req/heure/IP
Content-Type : multipart/form-data   ← pour les photos

Form data :
  name            : "Ibrahim Koné"
  phone           : "0707654321"
  email           : "ibrahim@email.com"     (optionnel)
  password        : "motdepasse"
  city            : "Abidjan"
  zone            : "Yopougon"              (optionnel)
  vehicle_type    : "moto"                  (moto | vélo | voiture)
  vehicle_plate   : "CI-1234-AB"            (optionnel)
  cni_number      : "CI0012345678"
  cni_photo       : [fichier image max 5MB]
  license_photo   : [fichier image max 5MB]
  vehicle_photo   : [fichier image max 5MB]

Réponse 201 :
{
  "message": "Inscription reçue. Votre dossier est en cours de vérification (24-48h)."
}
```

#### Connexion livreur
```
POST /driver/auth/login
Rate limit : 10 req/min/IP

Body :
{
  "phone": "0707654321",
  "password": "motdepasse"
}

Réponse 200 :
{
  "token": "2|xxxxxxxxxxxxxx",
  "driver": {
    "id": 5,
    "name": "Ibrahim Koné",
    "phone": "0707654321",
    "city": "Abidjan",
    "zone": "Yopougon",
    "vehicle_type": "moto",
    "verification_status": "approved",
    "is_active": true,
    "is_available": false,
    "rating": "4.80",
    "total_deliveries": 127,
    "total_earnings_xof": 15400000   // centimes = 154 000 FCFA total gagné
  }
}
```

#### Mettre à jour le token FCM (notifications push)
```
PATCH /driver/auth/fcm-token
Auth : requis

Body : { "fcm_token": "firebase_token_xxx" }
```

---

### 6.7 — LIVREUR — DISPONIBILITÉ & GPS

#### Passer en ligne / hors ligne
```
POST /driver/status
Auth : requis (livreur approuvé)

Body : { "online": true }   // ou false

Réponse 200 :
{
  "is_available": true,
  "message": "Vous êtes en ligne."
}
```

#### Envoyer la position GPS
```
PATCH /driver/location
Auth : requis
Rate limit : 30 req/min/livreur (toutes les ~2s max)

Body :
{
  "latitude": 5.3542,
  "longitude": -3.9827,
  "accuracy": 8.5,       // optionnel — précision en mètres
  "speed": 25.0,         // optionnel — km/h
  "heading": 180.0       // optionnel — direction en degrés
}

Réponse 200 : { "message": "Position mise à jour." }
```

---

### 6.8 — LIVREUR — COURSES

#### Courses disponibles dans sa zone
```
GET /driver/deliveries/pending
Auth : requis

Réponse 200 :
{
  "data": [
    {
      "id": 8,
      "pickup_address": "Cocody Angré, Rue des Jardins",
      "pickup_name": "Maquis Le Bon Coin",
      "pickup_lat": 5.3812,
      "pickup_lng": -3.9561,
      "delivery_address": "Yopougon Selmer, rue 12",
      "delivery_lat": 5.3200,
      "delivery_lng": -4.0100,
      "distance_to_pickup_km": 2.1,
      "delivery_fee": 95000,
      "driver_earning": 76000,      // 80% des frais = 760 FCFA
      "items_count": 3,
      "estimated_minutes": 38,
      "created_at": "2026-07-01T10:56:00Z"
    }
  ],
  "message": null
}
```

#### Accepter une course
```
POST /driver/deliveries/{id}/accept
Auth : requis

Réponse 200 :
{
  "message": "Course acceptée.",
  "delivery": {
    "id": 8,
    "status": "assigned",
    "status_label": "Livreur assigné",
    "pickup": {
      "name": "Maquis Le Bon Coin",
      "address": "Cocody Angré, Rue des Jardins",
      "phone": "0709123456",
      "lat": 5.3812,
      "lng": -3.9561
    },
    "dropoff": {
      "address": "Yopougon Selmer, rue 12",
      "phone": "0708121520",
      "instructions": "Appeler en arrivant",
      "lat": 5.3200,
      "lng": -4.0100
    },
    "order": {
      "reference": "PLT-AB3CD4EF",
      "items_count": 3,
      "total": 695000,
      "delivery_fee": 95000,
      "driver_earning": 76000
    }
  }
}
```

#### Refuser une course
```
POST /driver/deliveries/{id}/decline
Auth : requis

Réponse 200 : { "message": "Course refusée. Un autre livreur sera cherché." }
```

#### Mettre à jour le statut de la course
```
PATCH /driver/deliveries/{id}/status
Auth : requis

Body : { "status": "heading_to_restaurant" }

Statuts dans l'ordre :
  assigned              → heading_to_restaurant   (je pars vers le restaurant)
  heading_to_restaurant → picked_up               (j'ai récupéré la commande)
  picked_up             → delivering              (je suis en route vers le client)
  delivering            → delivered               (commande livrée ✓)

Réponse 200 :
{
  "message": "En route vers le resto",
  "status": "heading_to_restaurant"
}
```

#### Course active en cours
```
GET /driver/deliveries/active
Auth : requis

Réponse 200 : { "data": { ...détail course... } }
Réponse 200 : { "data": null, "message": "Aucune course en cours." }
```

---

### 6.9 — LIVREUR — GAINS

#### Résumé des gains
```
GET /driver/earnings
Auth : requis

Réponse 200 :
{
  "available_balance": 4250000,      // 42 500 FCFA disponibles
  "today": {
    "deliveries": 8,
    "earnings": 608000                // 6 080 FCFA aujourd'hui
  },
  "this_week": {
    "deliveries": 41,
    "earnings": 3116000
  },
  "total_lifetime": 15400000
}
```

#### Historique des gains
```
GET /driver/earnings/history?page=1
Auth : requis

Réponse 200 :
{
  "data": [
    {
      "id": 55,
      "order_ref": "PLT-AB3CD4EF",
      "gross_amount": 95000,
      "platform_cut": 19000,
      "net_amount": 76000,
      "status": "available",
      "paid_at": null,
      "created_at": "2026-07-01T12:00:00Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 5, "total": 89 }
}
```

#### Demander un virement
```
POST /driver/earnings/payout
Auth : requis
Rate limit : 3 req/jour/livreur

Body :
{
  "amount": 4000000,                 // centimes = 40 000 FCFA
  "mobile": "0707654321",
  "payment_method": "wave"           // seul wave disponible pour l'instant
}

Réponse 200 :
{
  "message": "Virement en cours. Vous recevrez une confirmation Wave.",
  "amount": 4000000,
  "reference": "DRIVER-5-20260701120000"
}

Réponse 422 :
{
  "message": "Solde insuffisant.",
  "available": 4250000
}
```

---

### 6.10 — RESTAURANT (protégé — admin restaurant)

```
GET   /restaurant/delivery/orders              → commandes plateforme en attente
POST  /restaurant/delivery/orders/{id}/confirm → confirmer + déduire stock
POST  /restaurant/delivery/orders/{id}/ready   → plat prêt, notifie livreur
GET   /restaurant/delivery/settings            → paramètres livraison
PATCH /restaurant/delivery/settings            → modifier tarifs/zone

Body PATCH /delivery/settings :
{
  "delivery_base_fee": 50000,
  "delivery_fee_per_km": 15000,
  "max_delivery_distance_km": 10,
  "avg_prep_time_minutes": 20,
  "platform_category": "fast_food"
}
```

---

### 6.11 — SUPER ADMIN

```
# Livreurs
GET  /admin/drivers                   → liste + compteurs
GET  /admin/drivers/{id}              → détail + documents + historique
POST /admin/drivers/{id}/approve      → activer
POST /admin/drivers/{id}/reject       → Body: { "reason": "..." }
POST /admin/drivers/{id}/suspend      → Body: { "reason": "..." }
POST /admin/drivers/{id}/reactivate   → réactiver

# Restaurants plateforme
GET   /admin/platform/restaurants
POST  /admin/platform/restaurants/{id}/enable   → Body: { "platform_category": "restaurant", "platform_commission_rate": 12 }
POST  /admin/platform/restaurants/{id}/disable
PATCH /admin/platform/restaurants/{id}/commission → Body: { "platform_commission_rate": 10 }

# Analytics
GET /admin/analytics/dashboard        → KPIs globaux (?days=30)
GET /admin/analytics/live-deliveries  → livraisons actives temps réel
GET /admin/analytics/commissions      → commissions par jour (?days=30)
GET /admin/analytics/driver-earnings  → top livreurs par gains (?days=30)
```

---

## 7. Implémentation — App Client

### `src/stores/cartStore.ts`

```typescript
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

export interface CartItem {
  dishId: number
  name: string
  price: number
  quantity: number
  notes?: string
}

interface CartStore {
  restaurantId: number | null
  restaurantName: string | null
  items: CartItem[]
  addItem: (restaurantId: number, restaurantName: string, item: CartItem) => void
  updateQuantity: (dishId: number, quantity: number) => void
  removeItem: (dishId: number) => void
  clear: () => void
  subtotal: () => number
  itemCount: () => number
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      restaurantId: null,
      restaurantName: null,
      items: [],

      addItem: (restaurantId, restaurantName, item) => {
        const { restaurantId: current, items } = get()
        if (current && current !== restaurantId) {
          // Nouveau restaurant : vider et recommencer
          set({ restaurantId, restaurantName, items: [item] })
          return
        }
        const existing = items.find(i => i.dishId === item.dishId)
        if (existing) {
          set({
            restaurantId,
            restaurantName,
            items: items.map(i =>
              i.dishId === item.dishId
                ? { ...i, quantity: i.quantity + item.quantity }
                : i
            ),
          })
        } else {
          set({ restaurantId, restaurantName, items: [...items, item] })
        }
      },

      updateQuantity: (dishId, quantity) => {
        if (quantity <= 0) {
          get().removeItem(dishId)
          return
        }
        set({ items: get().items.map(i => i.dishId === dishId ? { ...i, quantity } : i) })
      },

      removeItem: (dishId) => {
        const items = get().items.filter(i => i.dishId !== dishId)
        set({ items, ...(items.length === 0 ? { restaurantId: null, restaurantName: null } : {}) })
      },

      clear: () => set({ restaurantId: null, restaurantName: null, items: [] }),
      subtotal: () => get().items.reduce((s, i) => s + i.price * i.quantity, 0),
      itemCount: () => get().items.reduce((s, i) => s + i.quantity, 0),
    }),
    { name: 'menupro-cart' }
  )
)
```

### `src/stores/authStore.ts`

```typescript
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface Customer {
  id: number
  name: string
  phone: string
  email?: string
  city?: string
  total_orders: number
}

interface AuthStore {
  token: string | null
  customer: Customer | null
  setAuth: (token: string, customer: Customer) => void
  logout: () => void
  isAuthenticated: () => boolean
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      token: null,
      customer: null,
      setAuth: (token, customer) => {
        localStorage.setItem('token', token)
        set({ token, customer })
      },
      logout: () => {
        localStorage.removeItem('token')
        set({ token: null, customer: null })
      },
      isAuthenticated: () => !!get().token,
    }),
    { name: 'menupro-auth' }
  )
)
```

### `src/hooks/useRestaurants.ts`

```typescript
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/api'

interface RestaurantFilters {
  city?: string
  category?: string
  lat?: number
  lng?: number
  open_now?: boolean
}

export function useRestaurants(filters: RestaurantFilters = {}) {
  return useQuery({
    queryKey: ['restaurants', filters],
    queryFn: async () => {
      const { data } = await api.get('/restaurants', { params: filters })
      return data.data
    },
    staleTime: 1000 * 60 * 5,
  })
}

export function useNearbyRestaurants(lat: number, lng: number, radius = 10) {
  return useQuery({
    queryKey: ['restaurants', 'nearby', lat, lng, radius],
    queryFn: async () => {
      const { data } = await api.get('/restaurants/nearby', {
        params: { lat, lng, radius_km: radius },
      })
      return data.data
    },
    enabled: !!lat && !!lng,
    staleTime: 1000 * 60 * 3,
  })
}

export function useRestaurant(id: number) {
  return useQuery({
    queryKey: ['restaurant', id],
    queryFn: async () => {
      const { data } = await api.get(`/restaurants/${id}`)
      return data
    },
  })
}

export function useRestaurantMenu(id: number) {
  return useQuery({
    queryKey: ['restaurant', id, 'menu'],
    queryFn: async () => {
      const { data } = await api.get(`/restaurants/${id}/menu`)
      return data
    },
    staleTime: 1000 * 60 * 10,
  })
}

export function useDeliveryEstimate(restaurantId: number, lat?: number, lng?: number) {
  return useQuery({
    queryKey: ['delivery-estimate', restaurantId, lat, lng],
    queryFn: async () => {
      const { data } = await api.get(`/restaurants/${restaurantId}/delivery-estimate`, {
        params: { lat, lng },
      })
      return data
    },
    enabled: !!lat && !!lng,
  })
}
```

### `src/hooks/useGeolocation.ts`

```typescript
import { useState, useEffect } from 'react'

export function useGeolocation() {
  const [position, setPosition] = useState<{ lat: number; lng: number } | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!navigator.geolocation) {
      setError('Géolocalisation non supportée')
      setLoading(false)
      return
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        setPosition({ lat: pos.coords.latitude, lng: pos.coords.longitude })
        setLoading(false)
      },
      () => {
        // Fallback Abidjan si GPS refusé
        setPosition({ lat: 5.3542, lng: -3.9827 })
        setLoading(false)
      },
      { enableHighAccuracy: true, timeout: 10000 }
    )
  }, [])

  return { position, error, loading }
}
```

### `src/hooks/useOrderTracking.ts`

```typescript
import { useEffect, useState } from 'react'
import { getEcho } from '@/lib/echo'
import api from '@/lib/api'

export function useOrderTracking(trackingToken: string) {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  // Fetch initial
  useEffect(() => {
    if (!trackingToken) return
    api.get(`/client/orders/track/${trackingToken}`)
      .then(res => setData(res.data))
      .finally(() => setLoading(false))
  }, [trackingToken])

  // Abonnement WebSocket Reverb
  useEffect(() => {
    if (!trackingToken) return
    const echo = getEcho()
    const channel = echo.channel(`order.${trackingToken}`)

    channel.listen('.driver.assigned', (e: any) => {
      setData((prev: any) => ({
        ...prev,
        order_status: 'confirmed',
        delivery: { ...prev?.delivery, status: 'assigned', driver: e.driver },
        timeline: { ...prev?.timeline, driver_assigned_at: e.assigned_at },
      }))
    })

    channel.listen('.delivery.status_changed', (e: any) => {
      setData((prev: any) => ({
        ...prev,
        order_status: e.new_status === 'delivered' ? 'completed' : prev?.order_status,
        delivery: {
          ...prev?.delivery,
          status: e.new_status,
          status_label: e.status_label,
        },
      }))
    })

    channel.listen('.driver.location', (e: any) => {
      setData((prev: any) => ({
        ...prev,
        delivery: {
          ...prev?.delivery,
          driver: prev?.delivery?.driver
            ? { ...prev.delivery.driver, latitude: e.lat, longitude: e.lng }
            : null,
        },
      }))
    })

    return () => echo.leaveChannel(`order.${trackingToken}`)
  }, [trackingToken])

  return { data, loading }
}
```

---

## 8. Implémentation — App Livreur

### `src/stores/driverStatusStore.ts`

```typescript
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface Driver {
  id: number
  name: string
  phone: string
  city: string
  vehicle_type: string
  verification_status: string
  is_available: boolean
  rating: string
  total_deliveries: number
  total_earnings_xof: number
}

interface DriverStore {
  token: string | null
  driver: Driver | null
  isOnline: boolean
  setAuth: (token: string, driver: Driver) => void
  setOnline: (online: boolean) => void
  updateDriver: (updates: Partial<Driver>) => void
  logout: () => void
}

export const useDriverStore = create<DriverStore>()(
  persist(
    (set) => ({
      token: null,
      driver: null,
      isOnline: false,
      setAuth: (token, driver) => {
        localStorage.setItem('token', token)
        set({ token, driver })
      },
      setOnline: (isOnline) => set({ isOnline }),
      updateDriver: (updates) =>
        set(state => ({ driver: state.driver ? { ...state.driver, ...updates } : null })),
      logout: () => {
        localStorage.removeItem('token')
        set({ token: null, driver: null, isOnline: false })
      },
    }),
    { name: 'menupro-driver' }
  )
)
```

### `src/hooks/useGpsTracking.ts`

```typescript
import { useEffect, useRef } from 'react'
import api from '@/lib/api'
import { useDriverStore } from '@/stores/driverStatusStore'

export function useGpsTracking() {
  const { isOnline } = useDriverStore()
  const watcherRef = useRef<number | null>(null)

  useEffect(() => {
    if (!isOnline) {
      if (watcherRef.current !== null) {
        navigator.geolocation.clearWatch(watcherRef.current)
        watcherRef.current = null
      }
      return
    }

    if (!navigator.geolocation) return

    watcherRef.current = navigator.geolocation.watchPosition(
      (pos) => {
        api.patch('/driver/location', {
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
          accuracy: pos.coords.accuracy,
          speed: pos.coords.speed ? pos.coords.speed * 3.6 : null, // m/s → km/h
          heading: pos.coords.heading,
        }).catch(() => {}) // Silencieux si offline
      },
      null,
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
    )

    return () => {
      if (watcherRef.current !== null) {
        navigator.geolocation.clearWatch(watcherRef.current)
      }
    }
  }, [isOnline])
}
```

### `src/hooks/useDriverDeliveries.ts`

```typescript
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/api'
import { toast } from 'sonner'

export function usePendingDeliveries() {
  return useQuery({
    queryKey: ['driver', 'deliveries', 'pending'],
    queryFn: async () => {
      const { data } = await api.get('/driver/deliveries/pending')
      return data.data
    },
    refetchInterval: 15000, // Actualiser toutes les 15s
  })
}

export function useActiveDelivery() {
  return useQuery({
    queryKey: ['driver', 'deliveries', 'active'],
    queryFn: async () => {
      const { data } = await api.get('/driver/deliveries/active')
      return data.data
    },
    refetchInterval: 30000,
  })
}

export function useAcceptDelivery() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (deliveryId: number) =>
      api.post(`/driver/deliveries/${deliveryId}/accept`).then(r => r.data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['driver', 'deliveries'] })
      toast.success('Course acceptée ! Rendez-vous au restaurant.')
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message ?? 'Impossible d\'accepter la course.')
    },
  })
}

export function useUpdateDeliveryStatus() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ deliveryId, status }: { deliveryId: number; status: string }) =>
      api.patch(`/driver/deliveries/${deliveryId}/status`, { status }).then(r => r.data),
    onSuccess: (data) => {
      qc.invalidateQueries({ queryKey: ['driver', 'deliveries', 'active'] })
      toast.success(data.message)
    },
  })
}
```

---

## 9. Temps réel — Laravel Reverb

### Canaux disponibles

| Canal | Type | Qui écoute | Events |
|---|---|---|---|
| `order.{tracking_token}` | Public | Client, page suivi | `driver.assigned`, `delivery.status_changed`, `driver.location` |
| `driver.{driver_id}` | Privé | Le livreur lui-même | `driver.assigned`, `delivery.status_changed`, `delivery.available` |
| `drivers.city.{ville}` | Public | Tous livreurs d'une ville | `delivery.available` |
| `restaurant.{id}.deliveries` | Privé | Admins restaurant | `driver.assigned`, `delivery.status_changed` |

### Events et leur structure

#### `driver.assigned`
```json
{
  "delivery_id": 8,
  "order_ref": "PLT-AB3CD4EF",
  "driver": {
    "id": 5,
    "name": "Ibrahim Koné",
    "phone": "0707654321",
    "vehicle": "moto",
    "rating": "4.80",
    "lat": 5.3700,
    "lng": -3.9600
  },
  "pickup_address": "Cocody Angré, Rue des Jardins",
  "delivery_address": "Yopougon Selmer, rue 12",
  "estimated_minutes": 38,
  "assigned_at": "2026-07-01T10:56:00Z"
}
```

#### `delivery.status_changed`
```json
{
  "delivery_id": 8,
  "order_ref": "PLT-AB3CD4EF",
  "old_status": "assigned",
  "new_status": "heading_to_restaurant",
  "status_label": "En route vers le resto",
  "driver": { "name": "Ibrahim Koné", "lat": 5.3650, "lng": -3.9700, ... },
  "estimated_minutes": 35,
  "picked_up_at": null,
  "delivered_at": null
}
```

#### `driver.location`
```json
{
  "lat": 5.3542,
  "lng": -3.9827,
  "driver": "Ibrahim Koné",
  "status": "delivering"
}
```

#### `delivery.available`
```json
{
  "delivery_id": 8,
  "restaurant_name": "Maquis Le Bon Coin",
  "pickup_address": "Cocody Angré, Rue des Jardins",
  "pickup_lat": 5.3812,
  "pickup_lng": -3.9561,
  "delivery_address": "Yopougon Selmer, rue 12",
  "delivery_fee": 95000,
  "driver_earning": 76000,
  "items_count": 3,
  "estimated_minutes": 38,
  "city": "Abidjan"
}
```

### Usage dans l'app livreur — écouter les nouvelles courses

```typescript
useEffect(() => {
  const echo = getEcho()
  const channel = echo.channel(`drivers.city.Abidjan`)

  channel.listen('.delivery.available', (e: any) => {
    toast(`Nouvelle course : ${e.restaurant_name}`, {
      description: `+${formatPrice(e.driver_earning)} • ${e.estimated_minutes} min`,
      action: {
        label: 'Voir',
        onClick: () => router.push('/deliveries'),
      },
      duration: 10000,
    })
    queryClient.invalidateQueries({ queryKey: ['driver', 'deliveries', 'pending'] })
  })

  return () => echo.leaveChannel(`drivers.city.Abidjan`)
}, [])
```

---

## 10. Design System

### Couleurs

```css
/* globals.css */
:root {
  /* App Client — Orange */
  --primary: 24 95% 53%;        /* #f97316 */
  --primary-foreground: 0 0% 100%;

  /* Statuts */
  --success: 142 71% 45%;       /* #22c55e */
  --warning: 43 96% 56%;        /* #f59e0b */
  --destructive: 0 84% 60%;     /* #ef4444 */

  --background: 0 0% 100%;
  --foreground: 222 47% 11%;
  --muted: 210 40% 96%;
  --muted-foreground: 215 16% 47%;
  --border: 214 32% 91%;
  --card: 0 0% 100%;
}
```

```
App Client  → Thème Orange  #f97316
App Livreur → Thème Indigo  #6366f1
```

### Typographie — `tailwind.config.ts`

```typescript
import type { Config } from 'tailwindcss'

export default {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
} satisfies Config
```

Ajouter dans `layout.tsx` :
```typescript
import { Inter } from 'next/font/google'
const inter = Inter({ subsets: ['latin'] })
```

### Composant `PriceTag.tsx`

```typescript
import { cn } from '@/lib/utils'
import { formatPrice } from '@/lib/utils'

interface PriceTagProps {
  amount: number          // en centimes
  compareAmount?: number  // prix barré
  size?: 'sm' | 'md' | 'lg'
}

export function PriceTag({ amount, compareAmount, size = 'md' }: PriceTagProps) {
  const sizes = { sm: 'text-sm', md: 'text-base', lg: 'text-xl' }

  return (
    <span className="flex items-center gap-2">
      <span className={cn('font-bold text-orange-500', sizes[size])}>
        {formatPrice(amount)}
      </span>
      {compareAmount && (
        <span className="text-sm text-muted-foreground line-through">
          {formatPrice(compareAmount)}
        </span>
      )}
    </span>
  )
}
```

### Composant `TrackingMap.tsx`

```typescript
'use client'
import dynamic from 'next/dynamic'

// Import dynamique côté client uniquement (Leaflet nécessite window)
const MapContainer = dynamic(
  () => import('react-leaflet').then(m => m.MapContainer),
  { ssr: false }
)
const TileLayer = dynamic(
  () => import('react-leaflet').then(m => m.TileLayer),
  { ssr: false }
)
const Marker = dynamic(
  () => import('react-leaflet').then(m => m.Marker),
  { ssr: false }
)

import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const driverIcon = typeof window !== 'undefined'
  ? L.divIcon({
      className: '',
      html: `<div class="w-10 h-10 bg-orange-500 rounded-full flex items-center
                    justify-center shadow-lg border-2 border-white text-xl">🛵</div>`,
      iconSize: [40, 40],
      iconAnchor: [20, 20],
    })
  : undefined

const destinationIcon = typeof window !== 'undefined'
  ? L.divIcon({
      className: '',
      html: `<div class="w-8 h-8 bg-green-500 rounded-full flex items-center
                    justify-center shadow-lg border-2 border-white text-lg">📍</div>`,
      iconSize: [32, 32],
      iconAnchor: [16, 32],
    })
  : undefined

interface TrackingMapProps {
  driver: { lat: number; lng: number } | null
  destination: { lat: number; lng: number }
  className?: string
}

export default function TrackingMap({ driver, destination, className }: TrackingMapProps) {
  const center = driver ?? destination

  return (
    <MapContainer
      center={[center.lat, center.lng]}
      zoom={14}
      className={className ?? 'w-full h-64 rounded-2xl z-0'}
      zoomControl={false}
      scrollWheelZoom={false}
    >
      <TileLayer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        attribution='© <a href="https://openstreetmap.org">OpenStreetMap</a>'
      />

      {driver && driverIcon && (
        <Marker position={[driver.lat, driver.lng]} icon={driverIcon} />
      )}

      {destinationIcon && (
        <Marker position={[destination.lat, destination.lng]} icon={destinationIcon} />
      )}
    </MapContainer>
  )
}
```

### Composant `BottomNav.tsx`

```typescript
'use client'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { Home, Search, ShoppingBag, User } from 'lucide-react'
import { cn } from '@/lib/utils'
import { useCartStore } from '@/stores/cartStore'

const navItems = [
  { href: '/',          icon: Home,        label: 'Accueil'    },
  { href: '/restaurants', icon: Search,    label: 'Explorer'   },
  { href: '/orders',    icon: ShoppingBag, label: 'Commandes'  },
  { href: '/profile',   icon: User,        label: 'Profil'     },
]

export function BottomNav() {
  const pathname = usePathname()
  const itemCount = useCartStore(s => s.itemCount())

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-border
                    flex items-center justify-around h-16 px-2 safe-area-inset-bottom">
      {navItems.map(({ href, icon: Icon, label }) => {
        const active = pathname === href || (href !== '/' && pathname.startsWith(href))
        return (
          <Link key={href} href={href}
            className={cn(
              'flex flex-col items-center gap-0.5 flex-1 py-1 rounded-xl transition-colors',
              active ? 'text-orange-500' : 'text-muted-foreground'
            )}>
            <div className="relative">
              <Icon className="w-5 h-5" />
              {href === '/orders' && itemCount > 0 && (
                <span className="absolute -top-1 -right-1 w-4 h-4 bg-orange-500
                                  rounded-full text-white text-[10px] flex items-center
                                  justify-center font-bold">
                  {itemCount > 9 ? '9+' : itemCount}
                </span>
              )}
            </div>
            <span className="text-[10px] font-medium">{label}</span>
          </Link>
        )
      })}
    </nav>
  )
}
```

---

## 11. PWA — Configuration

### `next.config.ts` — App Client

```typescript
import withPWA from 'next-pwa'

const config = withPWA({
  dest: 'public',
  register: true,
  skipWaiting: true,
  disable: process.env.NODE_ENV === 'development',
  runtimeCaching: [
    {
      urlPattern: /\/api\/v1\/restaurants$/,
      handler: 'NetworkFirst',
      options: {
        cacheName: 'restaurants-list',
        expiration: { maxEntries: 50, maxAgeSeconds: 300 },
        networkTimeoutSeconds: 5,
      },
    },
    {
      urlPattern: /\/api\/v1\/restaurants\/\d+\/menu/,
      handler: 'StaleWhileRevalidate',
      options: {
        cacheName: 'menus',
        expiration: { maxEntries: 30, maxAgeSeconds: 600 },
      },
    },
    {
      urlPattern: /^https:\/\/.*\.tile\.openstreetmap\.org\/.*/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'map-tiles',
        expiration: { maxEntries: 500, maxAgeSeconds: 86400 * 7 },
      },
    },
    {
      urlPattern: /\/storage\/.*/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'images',
        expiration: { maxEntries: 200, maxAgeSeconds: 86400 * 30 },
      },
    },
  ],
})({
  images: {
    remotePatterns: [{ hostname: 'ton-domaine.com' }],
  },
})

export default config
```

### `src/app/manifest.ts` — App Client

```typescript
import type { MetadataRoute } from 'next'

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: 'MenuPro Delivery — Commandez en Côte d\'Ivoire',
    short_name: 'MenuPro',
    description: 'Commandez vos repas préférés et faites-vous livrer partout en Côte d\'Ivoire',
    start_url: '/',
    display: 'standalone',
    background_color: '#ffffff',
    theme_color: '#f97316',
    orientation: 'portrait-primary',
    categories: ['food', 'shopping', 'lifestyle'],
    lang: 'fr',
    icons: [
      { src: '/icons/icon-72.png',   sizes: '72x72',   type: 'image/png' },
      { src: '/icons/icon-96.png',   sizes: '96x96',   type: 'image/png' },
      { src: '/icons/icon-128.png',  sizes: '128x128', type: 'image/png' },
      { src: '/icons/icon-144.png',  sizes: '144x144', type: 'image/png' },
      { src: '/icons/icon-152.png',  sizes: '152x152', type: 'image/png' },
      { src: '/icons/icon-192.png',  sizes: '192x192', type: 'image/png' },
      { src: '/icons/icon-384.png',  sizes: '384x384', type: 'image/png' },
      { src: '/icons/icon-512.png',  sizes: '512x512', type: 'image/png', purpose: 'maskable' },
    ],
    screenshots: [
      { src: '/screenshots/home.png', sizes: '390x844', type: 'image/png', form_factor: 'narrow' },
    ],
  }
}
```

### `src/app/manifest.ts` — App Livreur

```typescript
import type { MetadataRoute } from 'next'

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: 'MenuPro Driver — Espace Livreur',
    short_name: 'MP Driver',
    description: 'Gérez vos courses et suivez vos gains MenuPro',
    start_url: '/',
    display: 'standalone',
    background_color: '#1e1b4b',
    theme_color: '#6366f1',
    orientation: 'portrait-primary',
    lang: 'fr',
    icons: [
      { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
      { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
    ],
  }
}
```

---

## 12. Déploiement

### Backend Laravel (existant)

```bash
# Sur le serveur
php artisan migrate --force
php artisan db:seed --class=DeliveryZoneSeeder

# Démarrer Reverb (WebSocket)
php artisan reverb:start --host=0.0.0.0 --port=8080

# Worker pour les jobs async
php artisan queue:work --queue=notifications,default --tries=3

# Ou avec Supervisor (recommandé en production)
# /etc/supervisor/conf.d/menupro.conf
```

### Frontend sur Vercel (recommandé)

```bash
# App Client
cd menupro-delivery
vercel deploy --prod
# → Définir les variables NEXT_PUBLIC_* dans le dashboard Vercel

# App Livreur
cd menupro-driver
vercel deploy --prod
# → Définir les variables NEXT_PUBLIC_* dans le dashboard Vercel
```

### Commandes locales pour démarrer

```bash
# Terminal 1 — Backend
cd C:\laragon\www\MenuPro
php artisan serve
php artisan reverb:start --port=8080
php artisan queue:work

# Terminal 2 — App Client
cd menupro-delivery
npm run dev        # → http://localhost:3000

# Terminal 3 — App Livreur
cd menupro-driver
npm run dev        # → http://localhost:3001
```

---

## Résumé des routes d'API utilisées par chaque app

### App Client utilise :
```
POST /client/auth/register
POST /client/auth/login
GET  /client/auth/me
GET  /restaurants
GET  /restaurants/nearby
GET  /restaurants/{id}
GET  /restaurants/{id}/menu
GET  /restaurants/{id}/delivery-estimate
GET  /client/addresses
POST /client/addresses
PATCH/DELETE /client/addresses/{id}
POST /client/orders
GET  /client/orders/history
GET  /client/orders/track/{token}         ← public
POST /client/orders/{id}/cancel
POST /client/payment/{id}/initiate
GET  /client/payment/{id}/status
WS   order.{token}                        ← Reverb
```

### App Livreur utilise :
```
POST  /driver/auth/register
POST  /driver/auth/login
GET   /driver/auth/me
PATCH /driver/auth/fcm-token
POST  /driver/status
PATCH /driver/location                    ← GPS toutes les 5-10s
GET   /driver/deliveries/pending
GET   /driver/deliveries/active
POST  /driver/deliveries/{id}/accept
POST  /driver/deliveries/{id}/decline
PATCH /driver/deliveries/{id}/status
GET   /driver/earnings
GET   /driver/earnings/history
POST  /driver/earnings/payout
WS    driver.{driver_id}                  ← Reverb
WS    drivers.city.{ville}               ← Reverb
```
