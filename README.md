# 🍽️ MenuPro

**La solution SaaS pour digitaliser votre restaurant et recevoir des commandes en ligne.**

MenuPro est une plateforme multi-restaurants permettant aux restaurateurs ivoiriens de créer leur site de commande en quelques minutes, gérer leur menu, leur stock et recevoir des paiements via Mobile Money (Lygos, GeniusPay).

![MenuPro](https://via.placeholder.com/1200x600/f97316/ffffff?text=MenuPro)

## ✨ Fonctionnalités

### 🏪 Pour les restaurants
- **Site de commande personnalisé** - URL unique pour chaque restaurant
- **Gestion du menu** - Catégories, plats, photos, prix, disponibilité
- **Gestion du stock** - Ingrédients, fournisseurs, alertes rupture, mouvements
- **Tableau de bord** - Commandes en temps réel, statistiques, CA
- **Paiement intégré** - Orange Money, MTN, Wave (Lygos, GeniusPay)
- **Multi-employés** - Accès restreints pour le personnel

### 👨‍💼 Pour l'administration
- **Validation des restaurants** - Workflow d'approbation
- **Gestion des abonnements** - Plan MenuPro (25 000 FCFA/mois)
- **Statistiques globales** - Vue d'ensemble de la plateforme
- **Logs d'activité** - Traçabilité des actions

### 📱 Pour les clients
- **Commander sans compte** - Pas d'inscription requise
- **Mobile-first** - Interface optimisée pour smartphone
- **Suivi de commande** - Statut en temps réel
- **Plusieurs modes** - Livraison ou sur place

## 🛠️ Stack Technique

- **Backend** : Laravel 12
- **Frontend** : Livewire 4 + Blade + Alpine.js
- **Styles** : Tailwind CSS 4
- **Base de données** : MySQL / PostgreSQL
- **Paiement** : Lygos, GeniusPay (Wave, Mobile Money)
- **Stockage** : Laravel Storage (local, cloud)

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+ ou PostgreSQL 14+

### Étapes

```bash
# Cloner le projet
git clone https://github.com/votre-username/menupro.git
cd menupro

# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Compiler les assets
npm run build

# Lancer le serveur
php artisan serve
```

### Développement

```bash
# Lancer en mode développement (avec hot-reload)
composer dev
# ou
npm run dev & php artisan serve
```

## 📁 Structure du projet

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SuperAdmin/
│   │   ├── Restaurant/
│   │   └── Public/
│   ├── Livewire/
│   │   ├── SuperAdmin/
│   │   ├── Restaurant/
│   │   └── RestaurantPublic/
│   └── Middleware/
├── Models/
├── Services/
│   ├── LygosGateway.php
│   ├── GeniusPayGateway.php
│   ├── StockManager.php
│   ├── PlanLimiter.php
│   └── MediaUploader.php
├── Jobs/
└── Events/

resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── public.blade.php
│   ├── auth.blade.php
│   ├── admin-restaurant.blade.php
│   ├── admin-super.blade.php
│   └── restaurant-public.blade.php
├── components/
│   ├── button.blade.php
│   ├── input.blade.php
│   ├── card.blade.php
│   ├── badge.blade.php
│   ├── alert.blade.php
│   └── modal.blade.php
├── pages/
│   ├── public/
│   ├── auth/
│   ├── restaurant/
│   ├── super-admin/
│   └── restaurant-public/
└── livewire/
```

## 🎨 Design System

### Couleurs
- **Primary (Orange)** : `#f97316` - Couleur principale, appétit, chaleur
- **Secondary (Vert)** : `#22c55e` - Succès, fraîcheur
- **Accent (Corail)** : `#f43f5e` - CTA, urgence
- **Neutral** : Gris chauds pour les textes et fonds

### Typographie
- **Sans** : DM Sans (corps de texte)
- **Display** : Playfair Display (titres)
- **Mono** : JetBrains Mono (code)

## 📊 Plan d'abonnement

| Fonctionnalité | MenuPro |
|----------------|---------|
| Prix/mois | 25 000 FCFA |
| Plats | 100 |
| Catégories | 30 |
| Employés | 5 |
| Gestion du stock | ✓ |
| Commandes/mois | 2 000 |
| Statistiques | ✓ |

## 🔒 Sécurité

- Authentification par email vérifié
- Chiffrement des clés API Lygos
- Validation stricte des uploads
- Rate limiting sur les formulaires
- Policies pour le multi-tenant
- Aucune donnée de carte stockée

## 📝 Variables d'environnement

```env
APP_NAME=MenuPro
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=menupro
DB_USERNAME=root
DB_PASSWORD=

LYGOS_API_URL=https://api.lygos.ci
LYGOS_WEBHOOK_SECRET=your-webhook-secret

# GeniusPay (optionnel)
GENIUSPAY_API_URL=
GENIUSPAY_WEBHOOK_SECRET=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```

## 📦 Déploiement

### Préparation

```bash
# Windows
prepare-deploy.bat

# Ou manuellement
composer install --no-dev --optimize-autoloader
npm run build
```

### Hébergement cPanel

- **Documentation** : `docs/PREPARATION_HEBERGEMENT.md`
- **Déploiement FTP** : `docs/DEPLOIEMENT_CPANEL.md`

### Commandes utiles (production)

```bash
# Corriger les données stock (restaurants sans plan, ingrédients orphelins)
php artisan stock:fix-data --dry-run  # Simulation
php artisan stock:fix-data            # Application

# Diagnostiquer un 403 sur une page ingrédient
php artisan stock:diagnose-403 {ingredient_id} {email}
```

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📄 License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez lire [CONTRIBUTING.md](CONTRIBUTING.md) avant de soumettre une pull request.

## 📞 Support

- Email : support@menupro.ci
- Documentation : https://docs.menupro.ci
- Status : https://status.menupro.ci

---

Fait avec ❤️ en Côte d'Ivoire 🇨🇮
