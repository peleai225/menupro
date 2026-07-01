Crée une application PWA complète appelée **menupro-driver** avec Next.js 14 (App Router), TypeScript, Tailwind CSS, shadcn/ui. C'est l'application mobile des livreurs freelance pour la plateforme MenuPro en Côte d'Ivoire.

---

## STACK OBLIGATOIRE

```
next@14, typescript, tailwindcss, shadcn/ui
@tanstack/react-query
zustand + persist
axios
react-hook-form + zod
framer-motion
react-leaflet + leaflet (carte navigation)
laravel-echo + pusher-js (WebSocket)
next-pwa
sonner (toasts)
lucide-react
date-fns
```

---

## BACKEND API

Toutes les requêtes : `https://menupro.ci/api/v1`
Header : `Authorization: Bearer {token}`

### AUTH LIVREUR
```
POST /driver/auth/register    Content-Type: multipart/form-data
  fields: name, phone, password, city, zone?, vehicle_type (moto|vélo|voiture), vehicle_plate?, cni_number
  files:  cni_photo, license_photo, vehicle_photo (max 5MB chacun)
  → réponse: { message: "Dossier reçu, vérification sous 24-48h" }

POST /driver/auth/login       body: { phone, password }
  → réponse: { token, driver: { id, name, phone, city, vehicle_type, verification_status, is_available, rating, total_deliveries, total_earnings_xof } }

GET  /driver/auth/me
POST /driver/auth/logout
PATCH /driver/auth/fcm-token  body: { fcm_token }
```

### DISPONIBILITÉ & GPS
```
POST  /driver/status          body: { online: true|false }
PATCH /driver/location        body: { latitude, longitude, accuracy?, speed?, heading? }
  → appeler toutes les 5-10 secondes quand le livreur est en ligne (watchPosition GPS)
```

### COURSES
```
GET  /driver/deliveries/pending
  → [{ id, pickup_address, pickup_name, pickup_lat, pickup_lng,
        delivery_address, delivery_lat, delivery_lng,
        distance_to_pickup_km, delivery_fee, driver_earning, items_count, estimated_minutes }]

GET  /driver/deliveries/active
  → course en cours ou null

POST /driver/deliveries/{id}/accept
  → { delivery: { id, status, pickup: { name, address, phone, lat, lng },
                   dropoff: { address, phone, instructions, lat, lng },
                   order: { reference, items_count, total, driver_earning } } }

POST /driver/deliveries/{id}/decline

PATCH /driver/deliveries/{id}/status
  body: { status: "heading_to_restaurant"|"picked_up"|"delivering"|"delivered" }
  → transition stricte dans cet ordre, pas de retour en arrière
```

### GAINS
```
GET /driver/earnings
  → { available_balance, today: { deliveries, earnings }, this_week: { deliveries, earnings }, total_lifetime }

GET /driver/earnings/history?page=1
  → [{ id, order_ref, gross_amount, platform_cut, net_amount, status, paid_at, created_at }]

POST /driver/earnings/payout
  body: { amount (centimes), mobile, payment_method: "wave" }
  → max 3 virements par jour
```

---

## WEBSOCKET TEMPS RÉEL (Laravel Reverb)

```javascript
broadcaster: 'reverb'
key: process.env.NEXT_PUBLIC_REVERB_APP_KEY
wsHost: process.env.NEXT_PUBLIC_REVERB_HOST
```

Canal privé `driver.{driver_id}` — écouter :
- `.driver.assigned`   → nouvelle course assignée directement à ce livreur
- `.delivery.available` → course disponible dans la ville

Canal public `drivers.city.{ville}` — écouter :
- `.delivery.available` → { restaurant_name, pickup_address, driver_earning, estimated_minutes }
  → afficher une notification toast pendant 10s avec bouton "Voir"

---

## PAGES À CRÉER

### `/login`
- Champs : téléphone + mot de passe
- Lien vers inscription

### `/register`
- **Formulaire en 3 étapes** (stepper) :
  - Étape 1 : Infos personnelles (nom, téléphone, mot de passe, ville, zone)
  - Étape 2 : Véhicule (type: moto/vélo/voiture, plaque optionnelle, numéro CNI)
  - Étape 3 : Documents — upload 3 photos (CNI recto, permis de conduire, photo du véhicule)
    - Prévisualisation des images avant envoi
    - Envoi en `multipart/form-data`
- Message de confirmation : "Dossier soumis, vous recevrez une réponse sous 24-48h"

### `/` — Dashboard
- **Switch prominent EN LIGNE / HORS LIGNE** (toggle grand format en haut)
  - Quand ON → démarrer watchPosition GPS + envoyer position toutes les 5-10s
  - Quand OFF → arrêter le GPS
- Si course active → afficher directement la card de la course en cours avec les actions
- Si en ligne sans course → "En attente de courses..."
- Compteurs du jour : nb livraisons + gains du jour
- Notification automatique si nouvelle course disponible dans sa ville (WebSocket)

### `/deliveries` — Courses disponibles
- Liste des courses en attente dans sa zone
- Chaque card : nom restaurant, adresse pickup, adresse livraison, distance jusqu'au resto, **gain en FCFA**, temps estimé
- Bouton ACCEPTER (vert) sur chaque card
- Rafraîchissement automatique toutes les 15 secondes
- Vide si aucune course

### `/deliveries/[id]` — Course en cours
- Carte Leaflet avec deux marqueurs : restaurant (pickup) + client (livraison)
- Bouton "Ouvrir dans Maps" (lien `https://www.google.com/maps/dir/?api=1&destination={lat},{lng}`)
- **Stepper d'actions** selon le statut actuel :
  - `assigned` → bouton "Je pars vers le restaurant"  → passe à `heading_to_restaurant`
  - `heading_to_restaurant` → bouton "J'ai récupéré la commande" → passe à `picked_up`
  - `picked_up` → bouton "Je suis en route vers le client" → passe à `delivering`
  - `delivering` → bouton "Commande livrée ✓" → passe à `delivered`
- Infos restaurant : nom, adresse, téléphone (bouton appel)
- Infos client : adresse, instructions, téléphone (bouton appel)
- Détail commande : nb articles, montant, **gain livreur**

### `/earnings` — Mes gains
- Solde disponible en grand (FCFA) avec bouton "Demander un virement"
- Stats : aujourd'hui / cette semaine / total
- Historique des courses (liste paginée)
  - Chaque ligne : réf commande, montant brut, commission plateforme (20%), **net reçu**, statut (en attente / disponible / payé)
- **Modal virement** :
  - Montant à retirer (max = solde disponible)
  - Numéro Wave
  - Bouton confirmer
  - Limite 3 virements/jour affichée

### `/profile` — Profil
- Infos livreur : nom, téléphone, ville, véhicule, note moyenne
- Badge statut : "Approuvé ✓" (vert) / "En attente" (orange) / "Suspendu" (rouge)
- Déconnexion

---

## NAVIGATION LIVREUR

Bottom navigation 4 onglets :
- Dashboard (Home)
- Courses (Bike/Truck icon) + badge si courses disponibles
- Gains (Wallet icon)
- Profil (User icon)

---

## DESIGN

- **Couleur principale** : Indigo `#6366f1` (indigo-500 Tailwind)
- **Fond** : gris très léger `#f8fafc` (slate-50)
- **Typographie** : Inter
- **Mobile first**, plein écran
- **Switch En ligne** : très grand, couleur verte quand actif, grise quand inactif — c'est l'élément le plus important de l'app
- **Boutons d'action de course** : pleine largeur, couleur selon action (vert = récupéré/livré, orange = en route)
- Prix en FCFA (centimes ÷ 100, séparateur milliers)
- **Gains en vert**, commissions en gris/rouge

---

## LOGIQUE GPS CRITIQUE

```typescript
// Quand isOnline === true :
navigator.geolocation.watchPosition(
  (pos) => {
    api.patch('/driver/location', {
      latitude: pos.coords.latitude,
      longitude: pos.coords.longitude,
      accuracy: pos.coords.accuracy,
      speed: pos.coords.speed ? pos.coords.speed * 3.6 : null,  // m/s → km/h
      heading: pos.coords.heading,
    })
  },
  null,
  { enableHighAccuracy: true, maximumAge: 5000 }
)

// Quand isOnline === false → clearWatch()
```

---

## VARIABLES D'ENVIRONNEMENT `.env.local`

```
NEXT_PUBLIC_API_URL=https://menupro.ci
NEXT_PUBLIC_REVERB_APP_KEY=menupro_key
NEXT_PUBLIC_REVERB_HOST=menupro.ci
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
NEXT_PUBLIC_APP_NAME=MenuPro Driver
```

---

## PWA

- `manifest.ts` : name "MenuPro Driver", theme_color "#6366f1", display "standalone", background_color "#1e1b4b"
- Service worker pour fonctionner en zones de faible connectivité (cache des pages statiques)

---

## CONTRAINTES IMPORTANTES

1. Leaflet → `dynamic(() => import('react-leaflet'), { ssr: false })`
2. Upload photos en `multipart/form-data` (pas JSON) pour l'inscription
3. GPS watchPosition uniquement quand `isOnline === true`
4. Axios interceptor → 401 : supprimer token + rediriger `/login`
5. Les courses disponibles se rafraîchissent toutes les 15s ET via WebSocket
6. Le middleware `delivery.driver` côté serveur vérifie que le livreur est approuvé → si `verification_status !== 'approved'`, afficher un écran d'attente avec le message de l'API
7. Toutes les erreurs API → toast `sonner` (jamais d'alert natif)
