# Plan GOLD — Spec Technique
**MenuPro — Complexes multi-espaces**
Date : 2026-07-23

---

## 1. Contexte et Problème

Un maquis, hôtel ou complexe de restauration à Abidjan peut avoir plusieurs espaces autonomes (salle, VIP, VVIP, bar, terrasse, room service...) avec jusqu'à 100+ tables, des dizaines de serveurs, plusieurs caisses et une cuisine centrale.

Les 3 peurs du propriétaire :
1. **Le vol** — plat servi non facturé, encaissement cash non enregistré
2. **Les erreurs** — commande mal prise, stock qui ne correspond pas
3. **L'ignorance** — pas de visibilité temps réel sur ce qui se passe dans chaque espace

Le Plan GOLD résout ces 3 problèmes avec une chaîne de responsabilité fermée et des rapports automatiques.

---

## 2. Plan Tarifaire

| Plan | Prix | Cible |
|------|------|-------|
| Stand | 5 000 F/mois | Vendeur rue, panini, jus |
| Essentiel | 15 000 F/mois | Petit maquis |
| Pro | 25 000 F/mois | Restaurant moyen |
| Business | 45 000 F/mois | Grand restaurant |
| **GOLD** | **85 000 F/mois** | Complexe multi-espaces |

---

## 3. Architecture Multi-espaces

### 3.1 Concept
Un restaurant GOLD peut créer des **Espaces** illimités. Chaque espace est quasi-autonome :
- Son propre menu et ses propres prix
- Son propre stock
- Ses propres serveurs (identifiés par PIN)
- Sa propre caisse (compte staff limité à cet espace)
- Ses propres tables et QR codes

La cuisine centrale reçoit toutes les commandes de tous les espaces, avec filtre et code couleur par espace.

### 3.2 Structure DB

```sql
-- Espaces
restaurant_spaces
  id, restaurant_id, name, color, description, is_active, created_at

-- Serveurs (waiter = rôle spécifique GOLD)
waiters
  id, space_id, restaurant_id, name, pin_hash (bcrypt 4 chiffres), is_active, created_at

-- Tables liées à un espace
tables (existant → ajouter space_id)
  + space_id (nullable, FK restaurant_spaces)

-- Commandes liées à un espace et un serveur
orders (existant → ajouter space_id + waiter_id)
  + space_id (nullable, FK restaurant_spaces)
  + waiter_id (nullable, FK waiters)
  + opened_at (timestamp ouverture table)
  + closed_at (timestamp fermeture = paiement)

-- Plats liés à un espace
dishes (existant → ajouter space_id)
  + space_id (nullable, FK restaurant_spaces)

-- Stock lié à un espace
stock_items (existant → ajouter space_id)
  + space_id (nullable, FK restaurant_spaces)
```

### 3.3 Dashboard patron
- Vue globale : tous les espaces, chiffre consolidé
- Vue par espace : filtre dropdown ou onglets
- Écran cuisine : onglets par espace, code couleur

---

## 4. Système Serveurs avec PIN

### 4.1 Flux complet
```
Serveur arrive à la table
        ↓
Ouvre l'URL de l'espace sur tablette/téléphone
(ex: menupro.ci/r/{slug}/espace/{space_id}/serveur)
        ↓
Tape son PIN à 4 chiffres
        ↓
Sélectionne la table (vue liste ou numéro)
        ↓
Table s'ouvre et se verrouille à son nom
        ↓
Prend la commande (plats + notes)
        ↓
Valide → ticket imprimé cuisine + stock débité
        ↓
Si client scanne QR table → commande liée au même serveur
        ↓
Serveur peut ajouter des plats en cours de service
        ↓
Caisse encaisse → reçu imprimé → table fermée et libérée
```

### 4.2 Verrouillage de table
- Une table ouverte par un serveur ne peut pas être prise par un autre
- Exception : manager peut forcer la réassignation
- Table ouverte = visible en orange sur le plan de salle
- Table fermée = libérée automatiquement après paiement

### 4.3 Lien QR client → serveur
- Chaque QR de table encode `table_id`
- Quand un client scanne, le système vérifie si la table est ouverte
- Si ouverte → commande liée au serveur assigné
- Si pas ouverte → commande en attente d'assignation (notification au serveur de l'espace)

### 4.4 Sécurité PIN
- PIN hashé en bcrypt (jamais stocké en clair)
- 3 tentatives incorrectes → blocage 5 minutes
- Manager peut réinitialiser le PIN depuis le dashboard

---

## 5. Chaîne de Responsabilité Fermée

```
Commande créée (serveur ou QR client)
        ↓ [obligatoire]
Ticket imprimé en cuisine
        ↓ [automatique]
Stock débité par espace
        ↓ [obligatoire]
Table reste "ouverte" — impossible de fermer sans paiement
        ↓ [obligatoire]
Caisse encaisse (liée à l'espace)
        ↓ [automatique]
Reçu client imprimé
        ↓ [automatique]
Table fermée et libérée
```

**Alertes automatiques :**
- Stock restant physique ≠ stock système → alerte patron
- Table ouverte depuis plus de X heures → alerte
- Caisse : total encaissé ≠ total commandes fermées → alerte

---

## 6. Rapports Automatiques

### 6.1 Rapport de service (fin de soirée)
```
RAPPORT DE SERVICE — Samedi 23 Juillet 2026
Maquis Le Roi — Service Soir

PAR SERVEUR :
Koffi    → 18 tables / 47 commandes / 312 500 F
Aya      → 15 tables / 38 commandes / 287 000 F
Jean     → 21 tables / 52 commandes / 445 000 F

PAR ESPACE :
VIP      → 156 500 F
VVIP     → 280 000 F
Salle    → 608 000 F
TOTAL    → 1 044 500 F

INVENTAIRE :
Bière Flag    : ouverture 48 → commandées 31 → restantes 17 ✅
Hennessy      : ouverture 5  → commandées 3  → restantes 2  ✅
Bissap        : ouverture 20 → commandées 18 → restantes 2  ✅
```

### 6.2 Alertes stock
- Seuil minimum configurable par article
- Alerte si stock restant < seuil
- Alerte si écart entre stock système et stock physique déclaré

---

## 7. Impression Thermique

### 7.1 Solution retenue : QZ Tray
- Application légère installée sur PC/tablette de caisse
- Impression silencieuse sans dialogue de confirmation
- Fonctionne offline
- Compatible Epson, Xprinter, Star (marques courantes en CI)
- Gratuit pour usage commercial basique

### 7.2 Types de tickets

**Ticket Cuisine :**
```
╔══════════════════════════════╗
║  ESPACE : VIP                ║
║  Table 3 — Commande #047     ║
║  Serveur : Koffi             ║
║  14:32                       ║
╠══════════════════════════════╣
║  x2  Poulet braisé           ║
║  x1  Attiéké poisson         ║
║      → Sans piment           ║
║  x3  Bière Flag              ║
╠══════════════════════════════╣
║  Note : client pressé        ║
╚══════════════════════════════╝
```

**Reçu Client :**
```
╔══════════════════════════════╗
║  [LOGO RESTAURANT]           ║
║  Maquis Le Roi               ║
║  Table 3 — 23/07/2026 14:32  ║
╠══════════════════════════════╣
║  Poulet braisé x2   11 000 F ║
║  Attiéké poisson    4 500 F  ║
║  Bière Flag x3      6 000 F  ║
╠══════════════════════════════╣
║  TOTAL              21 500 F ║
║  Payé par Wave        ✅     ║
╠══════════════════════════════╣
║  Serveur : Koffi             ║
║  Merci de votre visite !     ║
╚══════════════════════════════╝
```

### 7.3 Configuration dans MenuPro
- Section "Imprimantes" dans les paramètres restaurant (GOLD only)
- Champ : nom imprimante cuisine, nom imprimante bar, nom imprimante caisse
- Bouton "Imprimer ticket test"
- QZ Tray détecte automatiquement les imprimantes disponibles

---

## 8. Plan d'Implémentation (4 phases)

### Phase 1 — Multi-espaces (2-3 semaines)
- Migration DB : `restaurant_spaces`, + `space_id` sur tables/plats/stock/commandes
- CRUD espaces dans le dashboard
- Filtre par espace sur dashboard, commandes, stock
- Écran cuisine avec onglets par espace

### Phase 2 — Serveurs & PIN (1-2 semaines)
- Migration DB : `waiters`
- Interface tablette serveur par espace
- Authentification PIN + verrouillage table
- Lien automatique commandes QR → serveur

### Phase 3 — Rapports & Alertes (1-2 semaines)
- Rapport de service par serveur et par espace
- Inventaire automatique fin de service
- Alertes stock et écart caisse
- Export PDF

### Phase 4 — Impression thermique (1 semaine)
- Intégration QZ Tray (JS client)
- Templates ESC/POS ticket cuisine + reçu client
- Configuration imprimantes dans paramètres
- Tests avec Epson/Xprinter

**Total estimé : 6 à 8 semaines**

---

## 9. Landing Page

Ajouter le Plan GOLD dans la section tarifs avec :
- Badge "Nouveau" ou "Premium"
- Description : "Pour les complexes, maquis multi-espaces et hôtels"
- Lien vers page dédiée ou contact WhatsApp pour démo

---

*MenuPro — Plan GOLD — Spec validée le 2026-07-23*
