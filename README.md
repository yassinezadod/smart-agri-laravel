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

## 🚀 Fonctionnalités à venir

-   [ ] Tableaux de bord graphiques avec Chart.js.
-   [ ] Exportation des prédictions en format PDF/Excel.
-   [ ] Notifications par email en cas de chute de rendement prévue.

---

## 📄 Licence

Ce projet est sous licence MIT.
