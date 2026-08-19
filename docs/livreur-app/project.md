# MenuPro Livreur — Spec Projet

**Application Flutter** pour les chauffeurs-livreurs de la plateforme MenuPro.
Se connecte exclusivement à l'API Sanctum existante (`api/v1/driver/`).

---

## 1. Objectif

Permettre à un livreur de :
1. S'authentifier et maintenir sa session
2. Recevoir des notifications de livraison en temps réel
3. Accepter ou refuser une livraison
4. Suivre le parcours restaurant → client sur la carte
5. Mettre à jour le statut à chaque étape
6. Gérer ses encaissements cash-on-delivery
7. Consulter ses gains et son solde

---

## 2. Stack technique

| Couche | Choix | Raison |
|--------|-------|--------|
| Framework | **Flutter 3.24+** (Dart 3.5) | Cross-platform iOS/Android, un seul codebase |
| État | **Riverpod 2.x** | Réactif, composable, excellent pour les streams GPS |
| HTTP | **Dio 5.x** | Intercepteurs Sanctum + retry auto sur 401 |
| Real-time | **pusher_channels_flutter** | Backend Laravel Broadcasting (Pusher protocol) |
| Cartes | **flutter_map 7.x + latlong2** | OpenStreetMap, aucune clé API |
| Routing | **go_router 14.x** | Navigation déclarative, deep-links |
| Stockage sécurisé | **flutter_secure_storage** | Token Sanctum chiffré sur l'appareil |
| Cache local | **hive_flutter** | Cache livraisons, profil |
| Push | **firebase_messaging 15.x** | FCM — token stocké via `PATCH /driver/auth/fcm-token` |
| Localisation | **geolocator 13.x** | GPS foreground + background |
| Background | **flutter_background_service** | Service GPS actif pendant une livraison |
| DI | Riverpod providers | Pas de get_it nécessaire avec Riverpod |

---

## 3. Architecture

### Structure des dossiers

```
lib/
├── main.dart
├── app.dart                         # MaterialApp + ProviderScope
├── core/
│   ├── api/
│   │   ├── api_client.dart          # Dio instance, intercepteurs
│   │   ├── auth_interceptor.dart    # Inject Bearer token + refresh 401
│   │   └── endpoints.dart           # Constantes URL
│   ├── auth/
│   │   ├── auth_repository.dart
│   │   └── auth_state.dart
│   ├── errors/
│   │   ├── app_exception.dart
│   │   └── error_handler.dart
│   ├── router/
│   │   └── app_router.dart
│   └── theme/
│       ├── app_theme.dart
│       └── app_colors.dart
├── features/
│   ├── auth/
│   │   ├── data/
│   │   │   └── auth_api.dart        # login, register, me, logout, updateFcmToken
│   │   ├── domain/
│   │   │   ├── driver_model.dart
│   │   │   └── auth_provider.dart
│   │   └── presentation/
│   │       ├── login_screen.dart
│   │       └── register_screen.dart
│   ├── deliveries/
│   │   ├── data/
│   │   │   └── delivery_api.dart    # pending, active, accept, decline, updateStatus
│   │   ├── domain/
│   │   │   ├── delivery_model.dart
│   │   │   ├── delivery_status.dart # enum miroir du backend
│   │   │   └── deliveries_provider.dart
│   │   └── presentation/
│   │       ├── delivery_request_sheet.dart  # Bottom sheet d'acceptation
│   │       ├── active_delivery_screen.dart  # Carte + étapes
│   │       └── delivery_history_screen.dart
│   ├── map/
│   │   ├── map_provider.dart        # Camera, markers, route polyline
│   │   └── map_widget.dart          # flutter_map + OSM tiles
│   ├── location/
│   │   ├── location_service.dart    # Stream GPS, throttle 30s
│   │   └── location_provider.dart
│   ├── realtime/
│   │   ├── pusher_service.dart      # Connexion Pusher, subscribe/unsubscribe
│   │   └── realtime_provider.dart
│   ├── cash/
│   │   ├── data/
│   │   │   └── cash_api.dart        # balance, remittances, storeRemittance
│   │   ├── domain/
│   │   │   └── cash_provider.dart
│   │   └── presentation/
│   │       ├── cash_balance_screen.dart
│   │       └── remittance_form.dart
│   ├── profile/
│   │   └── presentation/
│   │       ├── profile_screen.dart
│   │       └── availability_toggle.dart  # POST /driver/status
│   └── notifications/
│       ├── notification_service.dart    # firebase_messaging handlers
│       └── notification_handler.dart    # Routing selon le payload FCM
└── shared/
    ├── widgets/
    │   ├── menupro_button.dart
    │   ├── status_badge.dart
    │   ├── loading_overlay.dart
    │   └── error_snackbar.dart
    └── utils/
        ├── formatters.dart          # Prix XOF, dates
        └── distance.dart           # Haversine pour affichage km
```

---

## 4. API Backend — Référence

### Base URL
```
https://menupro.ci/api/v1/driver
```

### Authentification
Toutes les requêtes (sauf login/register) portent :
```
Authorization: Bearer {sanctum_token}
```

### Endpoints clés

| Action | Méthode | Endpoint |
|--------|---------|----------|
| Login | POST | `/driver/auth/login` |
| Register | POST | `/driver/auth/register` |
| Profil | GET | `/driver/auth/me` |
| MAJ FCM token | PATCH | `/driver/auth/fcm-token` |
| MAJ profil | PATCH | `/driver/auth/profile` |
| Toggle disponibilité | POST | `/driver/status` |
| MAJ localisation | PATCH | `/driver/location` (throttlé) |
| Livraisons en attente | GET | `/driver/deliveries/pending` |
| Livraison active | GET | `/driver/deliveries/active` |
| Accepter | POST | `/driver/deliveries/{id}/accept` |
| Refuser | POST | `/driver/deliveries/{id}/decline` |
| MAJ statut | PATCH | `/driver/deliveries/{id}/status` |
| Confirmer cash | POST | `/driver/deliveries/{id}/cash-collected` |
| Solde cash | GET | `/driver/cash-balance` |
| Remises | GET | `/driver/cash-remittances` |
| Nouvelle remise | POST | `/driver/cash-remittances` |

### Statuts de livraison (enum `DeliveryStatus`)

```
pending → assigned → heading_to_restaurant → picked_up → delivering → delivered
                                                                     ↘ cancelled
```

Le livreur ne peut faire avancer que sa propre livraison active, dans l'ordre.

---

## 5. Temps réel — Pusher

### Configuration
```dart
// pusher_service.dart
final pusher = PusherChannelsFlutter.getInstance();
await pusher.init(
  apiKey: 'PUSHER_APP_KEY',
  cluster: 'eu',
  // ou wsHost + wsPort si Soketi auto-hébergé
);
```

### Canaux à souscrire

| Canal | Événements à écouter | Action Flutter |
|-------|---------------------|----------------|
| `driver.{driverId}` | `DriverAssigned` | Ouvrir le bottom sheet d'acceptation |
| `city.{citySlug}` | `NewDeliveryAvailable` | Rafraîchir la liste pending |
| `delivery.{deliveryId}` | `DeliveryStatusChanged` | Mettre à jour l'état local |

### Règle de souscription
- Souscrire à `driver.{id}` dès la connexion
- Souscrire à `delivery.{id}` uniquement quand une livraison est active
- Se désabonner proprement sur logout et en arrière-plan

---

## 6. GPS background

### Flux
1. Livraison acceptée → `BackgroundService.start()`
2. Chaque 30 secondes : `PATCH /driver/location` avec `{lat, lng, accuracy, heading}`
3. Livraison terminée → `BackgroundService.stop()`

### Permissions requises

**Android** (`AndroidManifest.xml`) :
```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION"/>
<uses-permission android:name="android.permission.FOREGROUND_SERVICE"/>
<uses-permission android:name="android.permission.FOREGROUND_SERVICE_LOCATION"/>
```

**iOS** (`Info.plist`) :
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>MenuPro Livreur utilise votre position pour les livraisons.</string>
<key>NSLocationAlwaysUsageDescription</key>
<string>MenuPro Livreur suit votre position en arrière-plan pendant une livraison.</string>
<key>UIBackgroundModes</key>
<array><string>location</string></array>
```

---

## 7. Carte (OpenStreetMap)

```dart
// map_widget.dart
FlutterMap(
  options: MapOptions(
    initialCenter: driverPosition,
    initialZoom: 14,
  ),
  children: [
    TileLayer(
      urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
      userAgentPackageName: 'ci.menupro.driver',
      maxZoom: 19,
    ),
    MarkerLayer(markers: [restaurantMarker, clientMarker, driverMarker]),
    PolylineLayer(polylines: [routePolyline]),
  ],
)
```

**Tuiles alternatives si OSM lent en CI :**
- `https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png` (HOT style)
- Serveur de tuiles auto-hébergé avec `mbtiles` pour l'Afrique de l'Ouest

**Route / directions :**
Utiliser l'API OSRM publique ou self-hosted :
```
GET https://router.project-osrm.org/route/v1/driving/{lng1},{lat1};{lng2},{lat2}?overview=full&geometries=geojson
```
→ Extraire `routes[0].geometry.coordinates` pour le polyline.

---

## 8. Notifications push (FCM)

### Payload attendu du backend
```json
{
  "notification": { "title": "Nouvelle livraison", "body": "Restaurant Le Délice → Cocody" },
  "data": {
    "type": "delivery_assigned",
    "delivery_id": "123",
    "restaurant_name": "Le Délice",
    "amount_xof": "1500"
  }
}
```

### Handling
```dart
// notification_handler.dart — 3 états : foreground / background / terminated
FirebaseMessaging.onMessage.listen(handleForeground);        // App ouverte
FirebaseMessaging.onMessageOpenedApp.listen(handleTap);      // App en fond
FirebaseMessaging.instance.getInitialMessage().then(handleTerminated); // App fermée
```

---

## 9. Gestion d'état — Riverpod providers

```dart
// Providers principaux
final authProvider         = StateNotifierProvider<AuthNotifier, AuthState>
final activeDeliveryProvider = StreamProvider<Delivery?>       // poll ou Pusher
final locationProvider     = StreamProvider<Position>
final cashBalanceProvider  = FutureProvider<CashBalance>
final availabilityProvider = StateNotifierProvider<bool>
```

---

## 10. Sécurité

- Token Sanctum stocké dans `flutter_secure_storage` (Keychain iOS / Keystore Android)
- Refresh automatique via intercepteur Dio sur HTTP 401
- Aucune donnée sensible dans les logs en production (`kReleaseMode`)
- Certificate pinning recommandé pour la prod (package `dio_certificate_pincer`)

---

## 11. pubspec.yaml — dépendances principales

```yaml
dependencies:
  flutter_riverpod: ^2.5.1
  riverpod_annotation: ^2.3.5
  dio: ^5.6.0
  go_router: ^14.2.7
  flutter_map: ^7.0.2
  latlong2: ^0.9.1
  geolocator: ^13.0.2
  flutter_background_service: ^5.0.5
  pusher_channels_flutter: ^2.0.3
  firebase_messaging: ^15.1.2
  firebase_core: ^3.5.0
  flutter_secure_storage: ^9.2.2
  hive_flutter: ^1.1.0
  cached_network_image: ^3.4.1
  intl: ^0.19.0

dev_dependencies:
  riverpod_generator: ^2.4.3
  build_runner: ^2.4.12
  flutter_lints: ^4.0.0
```

---

## 12. Écrans — liste complète

| Écran | Route | Description |
|-------|-------|-------------|
| Splash | `/` | Vérification token → redirect |
| Login | `/login` | Auth par téléphone + mot de passe |
| Register | `/register` | Inscription + upload docs |
| Map Home | `/home` | Carte plein écran + overlay statut |
| Delivery Request | (sheet) | Bottom sheet acceptation/refus |
| Active Delivery | `/delivery/:id` | Carte + étapes + actions |
| Cash Balance | `/cash` | Solde, dettes, remises |
| Delivery History | `/history` | Historique paginé |
| Profile | `/profile` | Profil, disponibilité, déconnexion |

---

## 13. Contraintes non fonctionnelles

- **Démarrage :** < 2s sur Android mid-range (Tecno/Infinix)
- **GPS :** Précision ≤ 10m, mise à jour toutes les 30s en livraison
- **Offline :** Afficher le dernier état connu si pas de réseau, banner "Hors ligne"
- **Batterie :** Mode économie GPS (distance filter 50m minimum) quand disponible sans livraison active
- **Taille APK :** Viser < 30 Mo (split ABIs en release)
- **Minimum SDK :** Android 7.0 (API 24), iOS 13
