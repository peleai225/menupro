# MenuPro Livreur — Spec Design

Design system complet pour l'application Flutter des livreurs MenuPro.
Conçu pour une utilisation en extérieur, de nuit, sur des appareils Android mid-range.

---

## 1. Principes directeurs

| Principe | Règle |
|----------|-------|
| **Lisibilité extérieure** | Contraste élevé, police ≥ 16sp sur tout texte fonctionnel |
| **Action unique par écran** | L'action principale est toujours visible sans scroll |
| **Touch targets** | Toute zone interactive ≥ 56dp (les livreurs sont en mouvement) |
| **Feedback immédiat** | Tout appui déclenche un retour haptique + visuel < 100ms |
| **Économie cognitive** | Couleur = statut. Aucune ambiguïté visuelle. |
| **Mode sombre natif** | Thème dark en priorité — utilisation nocturne fréquente |

---

## 2. Palette de couleurs

### Couleurs principales

```dart
// app_colors.dart
class AppColors {
  // Brand
  static const primary     = Color(0xFFD45E0C);  // Orange MenuPro
  static const primaryDark = Color(0xFFB04D09);  // Orange foncé (pressed)
  static const primaryGlow = Color(0x33D45E0C);  // Orange 20% (shadows)

  // Backgrounds (thème dark)
  static const bgBase      = Color(0xFF0F0F0F);  // Fond principal
  static const bgSurface   = Color(0xFF1A1A1A);  // Cards, bottom sheets
  static const bgElevated  = Color(0xFF242424);  // Bottom nav, modals
  static const bgMuted     = Color(0xFF2E2E2E);  // Champs inactifs

  // Textes
  static const textPrimary   = Color(0xFFF5F5F5); // Titres, valeurs
  static const textSecondary = Color(0xFF9E9E9E); // Labels, descriptions
  static const textDisabled  = Color(0xFF5E5E5E); // Éléments inactifs

  // Bordures
  static const border        = Color(0xFF2E2E2E);
  static const borderFocus   = Color(0xFFD45E0C);

  // Statuts livraison
  static const statusPending    = Color(0xFFFFB800); // Jaune — En attente
  static const statusAssigned   = Color(0xFF4FC3F7); // Bleu clair — Assigné
  static const statusHeading    = Color(0xFF7E57C2); // Violet — En route restaurant
  static const statusPickedUp   = Color(0xFFFF7043); // Orange vif — Récupéré
  static const statusDelivering = Color(0xFF26C6DA); // Cyan — En livraison
  static const statusDelivered  = Color(0xFF66BB6A); // Vert — Livré
  static const statusCancelled  = Color(0xFFEF5350); // Rouge — Annulé

  // Sémantiques
  static const success = Color(0xFF66BB6A);
  static const error   = Color(0xFFEF5350);
  static const warning = Color(0xFFFFB800);
  static const info    = Color(0xFF4FC3F7);

  // Cash
  static const cashPositive = Color(0xFF66BB6A); // Gains
  static const cashDebt     = Color(0xFFEF5350); // Dettes COD
}
```

### Thème clair (fallback)

```dart
// Fond clair si l'utilisateur force le mode clair
static const bgBaseLight    = Color(0xFFF5F5F5);
static const bgSurfaceLight = Color(0xFFFFFFFF);
static const textPrimaryLight = Color(0xFF1A1A1A);
```

---

## 3. Typographie

**Police principale : Inter**
Raison : Excellente lisibilité sur petits écrans, disponible sur Google Fonts, supporte le latin étendu (accents français).

```dart
// app_theme.dart
import 'package:google_fonts/google_fonts.dart';

TextTheme buildTextTheme() => GoogleFonts.interTextTheme().copyWith(
  displayLarge:  GoogleFonts.inter(fontSize: 32, fontWeight: FontWeight.w800, letterSpacing: -0.5),
  displayMedium: GoogleFonts.inter(fontSize: 26, fontWeight: FontWeight.w700, letterSpacing: -0.3),
  titleLarge:    GoogleFonts.inter(fontSize: 20, fontWeight: FontWeight.w700),
  titleMedium:   GoogleFonts.inter(fontSize: 17, fontWeight: FontWeight.w600),
  titleSmall:    GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w600),
  bodyLarge:     GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.w400),
  bodyMedium:    GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w400),
  labelLarge:    GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.2),
  labelMedium:   GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w600, letterSpacing: 0.3),
  labelSmall:    GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w500, letterSpacing: 0.4),
);
```

### Règles typographiques

- **Montants XOF** : `fontWeight: FontWeight.w800`, taille ≥ 24sp, toujours sur une ligne
- **Statut** : `FontWeight.w700`, toujours en majuscules avec `letterSpacing: 0.8`
- **Numéro de livraison** : monospace `GoogleFonts.robotoMono`
- Jamais moins de `14sp` pour du texte fonctionnel

---

## 4. Système de composants

### 4.1 Bouton principal (`MenuProButton`)

```dart
// shared/widgets/menupro_button.dart
// Hauteur : 56dp (touch target généreux)
// Coins : 16dp
// Ripple : primaryGlow

ElevatedButton(
  style: ElevatedButton.styleFrom(
    backgroundColor: AppColors.primary,
    foregroundColor: Colors.white,
    minimumSize: const Size.fromHeight(56),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    elevation: 0,
    shadowColor: AppColors.primaryGlow,
  ),
  ...
)

// Variantes :
// .secondary → backgroundColor: bgElevated, foregroundColor: textPrimary
// .danger    → backgroundColor: error
// .ghost     → backgroundColor: transparent, side: border
```

### 4.2 Badge de statut (`StatusBadge`)

```dart
// Pill coloré selon DeliveryStatus
Container(
  padding: EdgeInsets.symmetric(horizontal: 10, vertical: 4),
  decoration: BoxDecoration(
    color: statusColor.withOpacity(0.15),
    border: Border.all(color: statusColor.withOpacity(0.4)),
    borderRadius: BorderRadius.circular(100),
  ),
  child: Row(children: [
    Icon(statusIcon, size: 12, color: statusColor),
    SizedBox(width: 4),
    Text(statusLabel, style: labelSmall.copyWith(color: statusColor)),
  ]),
)
```

### 4.3 Card de livraison (`DeliveryCard`)

```dart
Container(
  padding: EdgeInsets.all(16),
  decoration: BoxDecoration(
    color: AppColors.bgSurface,
    borderRadius: BorderRadius.circular(20),
    border: Border.all(color: AppColors.border),
  ),
  child: Column(children: [
    // En-tête : numéro + statut badge
    Row([deliveryNumber, StatusBadge]),
    SizedBox(height: 12),
    // Adresse restaurant
    _AddressRow(icon: Icons.storefront_outlined, label: restaurantName, address: pickupAddress),
    _Divider(dotted: true),
    // Adresse client
    _AddressRow(icon: Icons.location_on_outlined, label: clientPhone, address: deliveryAddress),
    SizedBox(height: 16),
    // Pied : distance + montant + bouton
    Row([distanceChip, amountChip, Spacer(), actionButton]),
  ]),
)
```

### 4.4 Bottom Sheet d'acceptation (`DeliveryRequestSheet`)

Apparaît depuis le bas de l'écran avec animation spring quand une livraison est disponible.

```
┌─────────────────────────────────┐
│  ━━━ (drag handle)              │
│                                 │
│  🔔 Nouvelle livraison          │
│                                 │
│  📍 Restaurant    →  🏠 Client  │
│  Le Délice           Cocody     │
│  Yopougon            2,3 km     │
│                                 │
│  💰 1 500 F CFA    🕐 8 min     │
│  Frais livreur     ETA          │
│                                 │
│  ┌─────────────┐ ┌───────────┐  │
│  │   REFUSER   │ │ ACCEPTER  │  │
│  └─────────────┘ └───────────┘  │
│                                 │
│  ⏱ Expire dans 45s  [======   ] │
└─────────────────────────────────┘
```

**Comportement :**
- Timer 45 secondes → fermeture automatique + `POST /decline`
- Barre de progression animée qui se réduit
- Retour haptique fort à l'apparition
- Bouton Accepter : orange + elevation
- Bouton Refuser : ghost border

### 4.5 Écran carte principal (`MapHomeScreen`)

```
┌─────────────────────────────────┐
│ [🔔 2]          [Disponible ✓]  │  ← TopBar flottante
│                                 │
│                                 │
│        [OSM Map full screen]    │
│                                 │
│             🚗                  │  ← Marqueur livreur
│                                 │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ Aucune livraison active     │ │  ← Info card bas
│ │ En attente d'une commande…  │ │
│ └─────────────────────────────┘ │
│                                 │
│ [🏠] [📦] [💰] [👤]            │  ← Bottom Nav
└─────────────────────────────────┘
```

### 4.6 Overlay livraison active

```
┌─────────────────────────────────┐
│ [🔔]           [#CMD-2847]      │
│                                 │
│  ┌── Étapes ──────────────────┐ │
│  │ ✅ Assigné                 │ │
│  │ ✅ En route restaurant     │ │
│  │ → 📦 Récupérer au resto    │ │  ← étape active (orange)
│  │   🏠 Livrer au client      │ │
│  └────────────────────────────┘ │
│                                 │
│        [OSM Map 40% height]     │
│                                 │
│ 📍 Le Délice — 0,4 km           │
│                                 │
│ [    JE SUIS AU RESTAURANT    ] │  ← CTA primaire
└─────────────────────────────────┘
```

### 4.7 Champ de formulaire (`AppTextField`)

```dart
TextField(
  style: bodyLarge.copyWith(color: AppColors.textPrimary),
  decoration: InputDecoration(
    filled: true,
    fillColor: AppColors.bgMuted,
    contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide(color: AppColors.border),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide(color: AppColors.primary, width: 2),
    ),
    // Hauteur effective : 56dp
  ),
)
```

### 4.8 Navigation principale (`AppBottomNav`)

4 onglets, fond `bgElevated`, indicateur orange sur l'onglet actif :

| Index | Icône | Label | Route |
|-------|-------|-------|-------|
| 0 | `map_outlined` → `map` | Carte | `/home` |
| 1 | `local_shipping_outlined` → `local_shipping` | Livraisons | `/deliveries` |
| 2 | `payments_outlined` → `payments` | Cash | `/cash` |
| 3 | `person_outline` → `person` | Profil | `/profile` |

```dart
NavigationBar(
  backgroundColor: AppColors.bgElevated,
  indicatorColor: AppColors.primaryGlow,
  height: 64,
  labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
  ...
)
```

---

## 5. Marqueurs carte

| Entité | Icône | Couleur fond | Taille |
|--------|-------|--------------|--------|
| Livreur (moi) | `🚗` ou flèche directionnelle | `primary` | 44dp |
| Restaurant | `🍽️` ou `storefront` | `bgSurface` | 40dp |
| Client | `📍` pin plein | `statusDelivering` | 40dp |

Marqueur livreur : rotation en fonction du `heading` GPS (transform rotate).

---

## 6. Animations

| Élément | Animation | Durée |
|---------|-----------|-------|
| Bottom sheet | `spring(damping: 0.75, stiffness: 200)` | — |
| Changement de statut | Scale 1.0 → 1.08 → 1.0 | 300ms |
| Timer acceptation | Linear progress | 45s |
| Disponibilité toggle | Color tween + scale | 200ms |
| Marqueur GPS | Slide vers nouvelle position | 800ms linear |
| Loading skeleton | Shimmer fade | 1.5s loop |

---

## 7. Feedback haptique

```dart
// utils/haptics.dart
import 'package:flutter/services.dart';

class Haptics {
  static void light()   => HapticFeedback.lightImpact();
  static void medium()  => HapticFeedback.mediumImpact();
  static void heavy()   => HapticFeedback.heavyImpact();
  static void success() => HapticFeedback.notificationFeedback(); // vibreur court-court
  static void error()   => HapticFeedback.vibrate();
}

// Quand utiliser
// light  → appui sur un bouton ghost
// medium → toggle disponibilité, changement d'onglet
// heavy  → apparition de la bottom sheet livraison
// success → livraison marquée comme livrée
// error  → refus ou erreur réseau
```

---

## 8. États de chargement & vide

### Loading (Skeleton)
Utiliser `shimmer` package pour les listes et cards :
- Même forme que le contenu réel
- Couleur : `bgMuted` → `bgElevated` en loop

### État vide

```
        [Illustration SVG simple — boîte ouverte]

     Aucune livraison disponible

  Activez votre disponibilité pour
  commencer à recevoir des commandes.

        [Activer ma disponibilité]
```

### Erreur réseau

```
        [Icône WiFi barré]

       Connexion perdue

  Vérifiez votre réseau mobile.
  Les dernières données sont affichées.

        [Réessayer]
```

Banner persistant en haut de l'écran (rouge, 40dp) quand hors ligne.

---

## 9. Écran Login

```
┌─────────────────────────────────┐
│                                 │
│  [Logo MenuPro]                 │
│                                 │
│  Bonjour, livreur 👋            │  ← displayMedium
│  Connectez-vous pour continuer  │  ← bodyMedium textSecondary
│                                 │
│  ┌─────────────────────────────┐│
│  │ 📱  07 00 00 00 00          ││  ← AppTextField
│  └─────────────────────────────┘│
│                                 │
│  ┌─────────────────────────────┐│
│  │ 🔒  Mot de passe       👁   ││
│  └─────────────────────────────┘│
│                                 │
│  [      SE CONNECTER         ]  │  ← MenuProButton (56dp)
│                                 │
│  Pas encore livreur ?           │
│  Déposer une candidature →      │  ← TextButton primary
│                                 │
└─────────────────────────────────┘
```

---

## 10. Écran Cash / Solde

```
┌─────────────────────────────────┐
│  ← Cash & Gains                 │
│                                 │
│  ┌─────────────────────────────┐│
│  │  💰 Solde disponible        ││
│  │  12 500 F CFA               ││  ← displayLarge, cashPositive
│  │  Mis à jour il y a 2 min    ││
│  └─────────────────────────────┘│
│                                 │
│  ┌──────────┐ ┌───────────────┐ │
│  │ 🔴 Dettes│ │ ✅ Versements │ │  ← 2 chips filtre
│  │ 3 200 F  │ │ 2 livrés      │ │
│  └──────────┘ └───────────────┘ │
│                                 │
│  Dettes en cours                │
│  ┌─────────────────────────────┐│
│  │ Restaurant Le Délice        ││
│  │ Commande #847 · 2 000 F     ││
│  │ [   DÉCLARER UN VERSEMENT ] ││
│  └─────────────────────────────┘│
│                                 │
└─────────────────────────────────┘
```

---

## 11. Règles Material 3 appliquées

| Élément M3 | Usage dans l'app |
|-----------|-----------------|
| `NavigationBar` | Bottom nav 4 onglets |
| `BottomSheet` | Delivery request, détails commande |
| `Card` (elevation 1) | DeliveryCard, CashCard |
| `FilledButton` | Action primaire |
| `OutlinedButton` | Refus, actions secondaires |
| `Chip` | Statuts, filtres |
| `SnackBar` | Confirmations, erreurs légères |
| `AlertDialog` | Confirmations destructives (annulation) |
| `LinearProgressIndicator` | Timer acceptation, chargement |
| `Switch` | Toggle disponibilité |

---

## 12. Accessibilité

- Toutes les images ont un `Semantics(label: ...)` 
- Contraste minimum : 4.5:1 pour le texte normal, 3:1 pour les grandes tailles
- `ExcludeSemantics` sur les éléments purement décoratifs
- Support de la police système agrandie (`textScaleFactor`)
- Focus visible sur les éléments interactifs (outline orange)

---

## 13. Assets requis

```
assets/
├── images/
│   ├── logo_menupro.svg         # Logo complet
│   ├── logo_menupro_icon.svg    # Icône seule (bottom nav)
│   ├── empty_deliveries.svg     # Illustration état vide
│   ├── empty_cash.svg           # Illustration solde vide
│   └── offline.svg              # Illustration hors ligne
├── fonts/
│   └── Inter/                   # Via google_fonts (téléchargé auto)
└── map/
    └── markers/
        ├── driver_marker.png    # 88×88px @2x
        ├── restaurant_marker.png
        └── client_marker.png
```

---

## 14. Palette de marqueurs & icônes carte

| Marqueur | Taille canvas | Contenu | Fond |
|----------|---------------|---------|------|
| Livreur | 88×88px | Flèche blanche (rotate sur heading) | `#D45E0C` |
| Restaurant | 80×80px | `storefront` blanc | `#1A1A1A` + border orange |
| Client | 80×80px | Pin plein | `#26C6DA` |

Marqueurs générés en Flutter avec `CustomPaint` pour éviter les assets bitmap.
