# 🌾 Smart Agri - Laravel Web App

Cette application est l'interface centrale du projet **Smart Agri**. Elle gère l'authentification, les profils utilisateurs, et sert de passerelle (Gateway) vers le microservice d'IA pour les prédictions de rendement agricole.

---

## 🏗️ Architecture du Projet

Le système est composé de deux briques principales :

1. **Web App (Laravel)** : Port `8001` - Gère l'utilisateur et la logique métier.
2. **AI Service (Python/FastAPI)** : Port `8000` - Gère les calculs et le modèle ML.

---

## 🛠️ Installation

### 1. Prérequis

-   PHP 8.1+ & Composer
-   Serveur MySQL
-   NPM

### 2. Étapes d'installation

```bash
# Cloner le dépôt
git clone https://github.com/yassinezadod/smart-agri-laravel.git
cd yield-ai-web

# Installer les dépendances
composer install
npm install && npm run dev

# Configurer l'environnement
cp .env.example .env
php artisan key:generate
```

### 3. Configuration de la base de données

Créez une base de données `yield_ai_web` dans MySQL et configurez votre `.env`. Ensuite, lancez les migrations :

```bash
php artisan migrate
```

---

## ⚙️ Configuration du Microservice IA

Pour que Laravel puisse communiquer avec l'IA, assurez-vous que votre service Python est lancé et configurez ces variables dans votre fichier `.env` :

```env
# URL du Microservice Python (Port Docker)
PYTHON_ML_SERVICE_URL=http://127.0.0.1:8000

# Clé de synchronisation interne
INTERNAL_SYNC_KEY="Votre clé de synchronisation interne"
```

### Lancer l'application Laravel

```bash
php artisan serve --port=8001
```

---

## 🔌 Points d'accès API (Documentation)

### 🔐 Authentification & Profil

| Méthode | Endpoint      | Description                                 |
| ------- | ------------- | ------------------------------------------- |
| POST    | /api/register | Inscription d'un nouvel utilisateur         |
| POST    | /api/login    | Connexion et génération de Token            |
| GET     | /api/users/me | Obtenir les informations du profil connecté |
| PATCH   | /api/users/me | Mettre à jour le profil                     |
| POST    | /api/logout   | Déconnexion de l'utilisateur                |

### 📈 Prédictions de Rendement

| Méthode | Endpoint           | Description                                                 |
| ------- | ------------------ | ----------------------------------------------------------- |
| POST    | /api/predict-yield | Envoyer des données climatiques pour obtenir une prédiction |
| GET     | /api/predictions   | Historique des prédictions enregistrées                     |

### 📊 Analyse & Comparaison

| Méthode | Endpoint               | Description                                          |
| ------- | ---------------------- | ---------------------------------------------------- |
| POST    | /api/yield/compare     | Comparer les prévisions IA avec les rendements réels |
| GET     | /api/yield/comparisons | Lister toutes les analyses effectuées                |

### 🛡️ Administration

| Méthode | Endpoint         | Description                                        |
| ------- | ---------------- | -------------------------------------------------- |
| GET     | /api/admin/users | Liste complète des utilisateurs (Admin uniquement) |

---

## 📖 Documentation des API (Swagger)

Chaque service possède sa propre documentation interactive pour tester les endpoints :

### 🤖 Microservice IA (FastAPI)

La documentation Swagger est générée automatiquement et permet de tester les prédictions :

-   **URL**: http://localhost:8000/docs
-   **Alternative (Redoc)**: http://localhost:8000/redoc

### 🌐 Web App (Laravel)

Pour Laravel, nous utilisons **L5-Swagger** (OpenAPI).

-   **URL**: http://localhost:8001/api/documentation
-   **Génération manuelle**: Si vous ajoutez de nouvelles routes, relancez la doc avec :

```bash
docker exec yield-ai-app php artisan l5-swagger:generate
```

## 🐳 Architecture Docker & Infrastructure

Le projet utilise une architecture en microservices conteneurisés pour garantir l'isolation et la portabilité.

### Détails des conteneurs :

-   **yield-ai-app** : Serveur Laravel 10 (PHP 8.2-FPM).
-   **yield-ai-db** : Base de données MySQL pour l'authentification et les métadonnées.
-   **yield-ai-service** : Microservice Python FastAPI pour les calculs IA.
-   **yield-ai-mongo** : Base de données NoSQL pour l'historique massif des données climatiques.

### 🚀 Commandes de lancement

1. **Lancer le moteur IA et MongoDB**

```bash
cd ../yield_ai_service
docker-compose up -d
```

2. **Lancer l'interface Web et MySQL**

```bash
cd ../yield-ai-web
docker-compose up -d
```

### 🛠️ Maintenance & Utilitaires

1. **Arrêter tous les services** :

```bash
 docker-compose down
```

2. **Voir les logs en temps réel** :

```bash
docker logs -f yield-ai-app
```

3. **Accès SQL interne** :

```bash
docker exec -it yield-ai-db mysql -u root -p
```

## 🚀 Fonctionnalités à venir

-   [ ] Tableaux de bord graphiques avec Chart.js.
-   [ ] Exportation des prédictions en format PDF/Excel.
-   [ ] Notifications par email en cas de chute de rendement prévue.

---

## 📄 Licence

Ce projet est sous licence MIT.
