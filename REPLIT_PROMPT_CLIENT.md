Crée une application PWA complète appelée **menupro-delivery** avec Next.js 14 (App Router), TypeScript, Tailwind CSS, shadcn/ui. C'est une app mobile-first de commande et livraison de repas pour la Côte d'Ivoire (style Glovo/Yango).

---

## STACK OBLIGATOIRE

```
next@14, typescript, tailwindcss, shadcn/ui
@tanstack/react-query (cache + requêtes)
zustand + persist (état global)
axios (HTTP avec interceptors)
react-hook-form + zod (formulaires)
framer-motion (animations)
react-leaflet + leaflet (carte OpenStreetMap)
laravel-echo + pusher-js (WebSocket temps réel)
next-pwa (service worker)
sonner (toasts)
lucide-react (icônes)
date-fns (dates)
```

---

## BACKEND API

Toutes les requêtes vont sur : `https://menupro.ci/api/v1`
Header obligatoire : `Authorization: Bearer {token}` pour les routes protégées
Format : JSON — `Accept: application/json`

### AUTH CLIENT
```
POST /client/auth/register    body: { name, phone, password, email?, city? }
POST /client/auth/login       body: { phone, password }
GET  /client/auth/me
POST /client/auth/logout
PATCH /client/auth/profile    body: { name?, email?, city? }
```

### RESTAURANTS (public, sans auth)
```
GET /restaurants                         query: city?, category?, lat?, lng?, open_now?
GET /restaurants/nearby                  query: lat, lng, radius_km?
GET /restaurants/{id}
GET /restaurants/{id}/menu
GET /restaurants/{id}/delivery-estimate  query: lat, lng
```

Réponse restaurant :
```json
{ "id": 1, "name": "Maquis Le Bon Coin", "category": "restaurant", "city": "Abidjan",
  "address": "Cocody Angré", "logo_url": "...", "is_open": true, "avg_prep_time": 20,
  "latitude": "5.3812", "longitude": "-3.9561", "distance_km": 2.3, "delivery_fee": 77000 }
```

Réponse menu :
```json
{ "categories": [{ "name": "Entrées", "dishes": [
  { "id": 10, "name": "Attieké poisson", "price": 300000, "image_url": "...", "is_available": true }
]}]}
```

Réponse estimate :
```json
{ "deliverable": true, "delivery_fee": 95000, "distance_km": 3.2,
  "estimated_minutes": 38, "is_peak_hour": false }
```

### ADRESSES CLIENT
```
GET    /client/addresses
POST   /client/addresses   body: { label, address, city, zone, latitude, longitude, instructions?, is_default }
PATCH  /client/addresses/{id}
DELETE /client/addresses/{id}
```

### COMMANDES
```
POST /client/orders
body: {
  restaurant_id, items: [{dish_id, quantity, notes?}],
  delivery_lat, delivery_lng, delivery_address, delivery_city,
  delivery_instructions?, payment_method: "wave"
}
réponse: { order: { id, reference, tracking_token, status, total, delivery_fee, estimated_minutes }, payment_url }

GET  /client/orders/history
GET  /client/orders/track/{tracking_token}   ← PUBLIC, sans auth
POST /client/orders/{id}/cancel
```

Réponse tracking :
```json
{ "order_status": "delivering", "estimated_minutes": 12,
  "delivery": { "status": "delivering", "driver": { "name": "Ibrahim", "phone": "...", "latitude": 5.33, "longitude": -4.00 } },
  "timeline": { "ordered_at": "...", "confirmed_at": "...", "picked_up_at": "...", "completed_at": null } }
```

### PAIEMENT
```
POST /client/payment/{orderId}/initiate   → retourne { payment_url }   (rediriger vers payment_url)
GET  /client/payment/{orderId}/status     → retourne { payment_status, order_status }
GET  /client/payment/success              ← callback Wave (rediriger vers page tracking)
GET  /client/payment/error               ← callback Wave (afficher erreur)
```

---

## WEBSOCKET TEMPS RÉEL (Laravel Reverb)

```javascript
// Config Echo
broadcaster: 'reverb'
key: process.env.NEXT_PUBLIC_REVERB_APP_KEY
wsHost: process.env.NEXT_PUBLIC_REVERB_HOST
```

Canal public `order.{tracking_token}` — écouter :
- `.driver.assigned`       → { driver: { name, phone, latitude, longitude, rating } }
- `.delivery.status_changed` → { new_status, status_label, estimated_minutes }
- `.driver.location`       → { lat, lng }

---

## PAGES À CRÉER

### `/` — Accueil
- Demander la position GPS (fallback : Abidjan 5.3542, -3.9827)
- Afficher les restaurants proches avec leurs frais de livraison
- Barre de recherche par nom
- Filtres : catégorie (fast food, restaurant, pizza, poulet, grillades...)
- Skeleton loading pendant le chargement

### `/restaurants` — Explorer
- Liste complète avec filtres ville + catégorie
- Tri par distance / popularité

### `/restaurants/[id]` — Détail restaurant
- Photo de couverture, logo, infos (horaires, note, temps préparation)
- Indicateur ouvert/fermé
- Frais de livraison estimés (appel delivery-estimate avec position GPS)
- Bouton "Voir le menu"

### `/restaurants/[id]/menu` — Menu
- Onglets par catégorie
- Cards plats avec photo, nom, description, prix en FCFA
- Bouton +/- quantité sur chaque plat
- **CartFAB** : bouton flottant "Voir le panier (N articles — XXX FCFA)" fixé en bas
- Si on ajoute un plat d'un autre restaurant : dialog de confirmation "Vider le panier et changer de restaurant ?"

### `/cart` — Panier
- Liste des articles avec modification quantité
- Nom du restaurant
- Sélection adresse de livraison (liste adresses sauvegardées ou nouvelle)
- Estimation frais en temps réel (appel delivery-estimate)
- Récapitulatif : sous-total + frais livraison + total
- Bouton "Commander et payer"

### `/checkout` — Paiement
- Récap commande
- Seul moyen de paiement : Wave (bouton orange prominent)
- Au clic → POST /client/orders → récupérer tracking_token → POST payment/initiate → rediriger vers Wave

### `/orders/track/[token]` — Suivi en temps réel
- **ACCESSIBLE SANS CONNEXION**
- Carte Leaflet plein écran (60% de la page) avec position livreur en direct
- Timeline verticale des étapes (commandé → confirmé → en préparation → livreur assigné → récupéré → en livraison → livré)
- Info livreur : nom, véhicule, note, bouton téléphone
- Temps restant estimé
- Mise à jour auto via WebSocket

### `/orders` — Historique
- Liste commandes (protégé, auth requise)
- Statut coloré, date, montant, nom restaurant
- Bouton "Suivre" si en cours

### `/profile` — Profil
- Infos utilisateur modifiables
- Lien vers mes adresses

### `/profile/addresses` — Adresses
- Liste avec label (Maison, Bureau...)
- Ajouter / modifier / supprimer
- Sélection adresse par défaut

### `/login` et `/register`
- Formulaires simples avec validation Zod
- Champs register : nom, téléphone (format ivoirien 07/05/01...), email (optionnel), mot de passe, ville
- Pas de redirection forcée — laisser browseR sans compte, juste pousser à se connecter au moment de commander

---

## NAVIGATION

Bottom navigation fixe (mobile) avec 4 onglets :
- Accueil (Home icon)
- Explorer (Search icon)
- Commandes (ShoppingBag icon) + badge nombre articles panier
- Profil (User icon)

---

## PANIER — LOGIQUE ZUSTAND

```typescript
// Persister dans localStorage
store: {
  restaurantId: number | null
  restaurantName: string | null
  items: { dishId, name, price, quantity, notes? }[]
  addItem(restaurantId, restaurantName, item)  // Si autre restaurant → demander confirmation
  updateQuantity(dishId, quantity)
  removeItem(dishId)
  clear()
  subtotal()   // somme price*quantity
  itemCount()  // somme quantities
}
```

---

## DESIGN

- **Couleur principale** : Orange `#f97316` (orange-500 Tailwind)
- **Fond** : blanc, cartes légèrement ombragées
- **Typographie** : Inter (Google Fonts)
- **Arrondis** : `rounded-2xl` pour les cards, `rounded-full` pour les badges
- **Mobile first** : max-width 480px centré, padding bottom 80px (bottom nav)
- **Monnaie** : tous les prix sont en centimes XOF dans l'API → diviser par 100 → afficher en FCFA avec séparateur milliers
  - Ex : `300000` → `3 000 FCFA`
- Distances : `< 1km` → afficher en mètres, sinon en km avec 1 décimale
- **Skeleton loading** sur toutes les pages avec données asynchrones
- **États vides** illustrés (pas de restaurants, panier vide, aucune commande)

---

## VARIABLES D'ENVIRONNEMENT `.env.local`

```
NEXT_PUBLIC_API_URL=https://menupro.ci
NEXT_PUBLIC_REVERB_APP_KEY=menupro_key
NEXT_PUBLIC_REVERB_HOST=menupro.ci
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
NEXT_PUBLIC_APP_NAME=MenuPro Delivery
```

---

## PWA

- `manifest.ts` : name "MenuPro Delivery", short_name "MenuPro", theme_color "#f97316", display "standalone"
- `next-pwa` : cache offline pour la liste restaurants (5min) et les menus (10min)
- Icons : générer des placeholders orange avec le logo M

---

## CONTRAINTES IMPORTANTES

1. Leaflet ne fonctionne pas côté serveur → utiliser `dynamic(() => import('react-leaflet'), { ssr: false })`
2. Les prix viennent de l'API en **centimes** (300000 = 3000 FCFA)
3. Le tracking est public (sans connexion), mais commander nécessite un compte
4. Axios interceptor : si réponse 401 → supprimer le token et rediriger vers `/login`
5. TanStack Query : staleTime 5min pour restaurants, 10min pour menus
6. Toutes les erreurs API affichées via `sonner` toast (pas d'alert natif)
