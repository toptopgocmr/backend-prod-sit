# 👗 Fashion Manager — Plateforme de Gestion Interne

Application interne complète de gestion pour boutique de mode & confection.
Développée en **Laravel 11** (full-stack Blade) + **API REST** pour application Flutter.

---

## 🏗️ Stack Technique

| Couche        | Technologie          |
|---------------|----------------------|
| Backend       | Laravel 11 (PHP 8.2+)|
| Frontend admin| Blade + Tailwind CSS |
| Auth API      | Laravel Sanctum      |
| Base de données| MySQL / PostgreSQL  |
| Cache / Queue | Redis                |
| Mobile        | Flutter (API REST)   |

---

## 📦 Installation

```bash
# 1. Cloner / extraire le projet
cd fashion-manager

# 2. Copier la configuration
cp .env.example .env

# 3. Configurer la BDD dans .env
DB_DATABASE=fashion_manager
DB_USERNAME=root
DB_PASSWORD=secret

# 4. Installer les dépendances
composer install

# 5. Générer la clé
php artisan key:generate

# 6. Migrer + seeder
php artisan migrate --seed

# 7. Lien storage
php artisan storage:link

# 8. Lancer le serveur
php artisan serve
```

---

## 🔐 Comptes par défaut (après seed)

| Nom                | Email                  | Mot de passe | Rôle          |
|--------------------|------------------------|--------------|---------------|
| Administrateur     | admin@fashion.local    | password     | Admin         |
| Marie Couturière   | marie@fashion.local    | password     | Couturier     |
| Jean Stock         | jean@fashion.local     | password     | Stock Manager |
| Sophie Caisse      | sophie@fashion.local   | password     | Caissier      |
| Pierre Livreur     | pierre@fashion.local   | password     | Livreur       |

---

## 🧩 Modules disponibles

### ✅ Implémentés (backend + vues)
- 🎛️ **Dashboard** — KPIs, graphiques CA/dépenses, alertes temps réel
- 👗 **Commandes sur mesure** — Workflow complet (8 statuts), mesures, couturier, paiement
- 📦 **Gestion stock** — Entrées/sorties, ajustements, alertes seuil bas
- 💰 **Finance** — Rapports mensuels, dépenses catégorisées, salaires
- 🛠️ **Maintenance** — Équipements, interventions, historique pannes
- 👤 **Clients** — Profils, mesures multiples, historique commandes

### 🔧 À compléter (routes + vues partielles)
- 🛍️ Ventes (tissus & prêt-à-porter)
- 🚚 Livraisons + géolocalisation
- 🏭 Planning atelier couturiers
- 📊 Rapports exports Excel/PDF
- 👥 Gestion utilisateurs (admin)

---

## 🗄️ Structure base de données (11 migrations)

```
users               — Utilisateurs (6 rôles)
clients             — Profils clients
measurements        — Mensurations clients (multiples)
categories          — Catégories produits
products            — Tissus + prêt-à-porter + accessoires
orders              — Ventes (tissus/PAP)
order_items         — Lignes de commande
custom_orders       — Commandes sur mesure
custom_order_statuses — Historique workflow
stock_movements     — Mouvements stock
purchase_orders     — Bons de commande fournisseurs
purchase_order_items
expense_categories
expenses            — Dépenses
payments            — Paiements (polymorphique)
salary_payments     — Paiement salaires
deliveries          — Livraisons
equipment           — Équipements atelier
maintenance_logs    — Historique maintenance
notifications       — Notifications Laravel
activity_logs       — Journal d'activité
promotions          — Promotions / codes promo
personal_access_tokens — Sanctum
```

---

## 🔌 API REST (Flutter)

Base URL : `http://votre-domaine.com/api/v1`

### Auth
```
POST   /api/v1/login          — Connexion → Bearer token
POST   /api/v1/logout         — Déconnexion
GET    /api/v1/user           — Profil utilisateur connecté
```

### Ressources principales
```
GET/POST   /api/v1/clients
GET/POST   /api/v1/orders
GET/POST   /api/v1/custom-orders
PUT        /api/v1/custom-orders/{id}/status
GET/POST   /api/v1/stock
GET/POST   /api/v1/maintenance
GET        /api/v1/dashboard
GET        /api/v1/finance/report?month=1&year=2024
```

### Authentification Flutter (Sanctum)
```dart
// Header à inclure sur chaque requête
headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

---

## 📁 Structure projet

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          ← Contrôleurs web admin
│   │   ├── Api/            ← Contrôleurs API Flutter
│   │   └── Auth/           ← Login
│   └── Middleware/
├── Models/                 ← Eloquent models
├── Services/
│   ├── StockService.php    ← Logique stock
│   └── FinanceService.php  ← Rapports financiers
└── Events/
    └── LowStockAlert.php

resources/views/
├── layouts/app.blade.php   ← Layout principal
├── auth/login.blade.php
├── dashboard/index.blade.php
└── orders/custom/...

database/
├── migrations/             ← 11 migrations complètes
└── seeders/DatabaseSeeder.php

routes/
├── web.php                 ← Routes admin (30+ routes)
└── api.php                 ← Routes API Flutter
```

---

## 💡 Personnalisation

### Changer le nom de l'application
```env
APP_NAME="Votre Boutique"
```

### Changer la devise
```env
CURRENCY=FCFA
```

### Seuil d'alerte stock global
```env
LOW_STOCK_THRESHOLD=5
```

---

## 🚀 Prochaines étapes recommandées

1. **Remplacer le logo** — placer `public/images/logo.png`
2. **Configurer les emails** — paramètres SMTP dans `.env`
3. **Configurer Firebase** — pour les notifications push Flutter
4. **Déploiement** — Apache/Nginx + SSL
5. **Compléter les vues** — pages produits, ventes, livraisons
6. **Connecter Flutter** — utiliser les routes `/api/v1/*`

---

*Fashion Manager — © {{ date('Y') }} — Développé avec Laravel*
