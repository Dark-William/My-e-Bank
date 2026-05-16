# 🏦 e-Bank - Plateforme Bancaire

Application une architecture propre, sécurisée et modulaire.

## 🚀 Lancement Rapide

Le projet est conçu pour être lancé facilement sans configuration complexe de base de données.

1. **Frontend (Vue 3)**

```bash
npm install
npm run dev
# → Dispo sur http://localhost:5173
```

2. **Backend (API PHP Natif + SQLite)**

```bash
cd native-backend
php -S localhost:8000 -t .
# → Dispo sur http://localhost:8000
```

_Note : La base `database.sqlite` se génère automatiquement à la première requête. Aucun setup requis._

---

## 🏗 Architecture Globale

Le projet suit une logique de séparation des préoccupations forte entre l'UI et la logique métier.

### 🎨 1. Frontend : Vue 3 + TypeScript + Tailwind v4

Le choix s'est porté sur **Vue 3 (Composition API)** et **TypeScript** pour allier vitesse d'exécution, typage strict et performances. L'architecture interne respecte les standards professionnels :

- **`core/models/`** : Contrats d'interfaces stricts (`User`, `Account`, `Transaction`) assurant la fiabilité des données venant de l'API.
- **`core/services/`** : Couche d'abstraction réseau (Axios) encapsulant les appels API et isolant la logique de requêtage des composants visuels.
- **`core/interceptors/`** : Configuration Axios centralisée pour :
  - L'injection automatique du token JWT (`Authorization: Bearer <token>`).
  - La gestion globale des erreurs et redirections (`401 Unauthorized` → `/login`).
- **`core/stores/` (Pinia)** :
  - `AuthStore` : Gère le cycle de vie de la session sécurisée.
  - `AccountStore` : Gère le solde et valide "optimistement" les tentatives de débits coté client pour un retour instantané.
- **UI / Composants** : Découpage granulaire (ex: `OperationForm.vue` mutualisé pour le crédit et le débit). Design system basé sur Tailwind CSS v4 intégrant Glassmorphism, Dark mode et feedback visuel dynamique (animations d'erreurs/succès).

### ⚙️ 2. Backend : API PHP Natif Sécurisée

Pour s'affranchir des problèmes d'environnement complexes et garantir un livrable fonctionnel "out-of-the-box" au recruteur, une API autonome et robuste a été développée en PHP natif (`native-backend/index.php`) répondant rigoureusement à chaque critère du test.

**Sécurité & Authentification :**

- Architecture REST "Stateless" via un système de Token Bearer (Similaire à Laravel Sanctum).
- Hashage fort des mots de passe (Argon2 / Bcrypt via `password_hash`).

**Intégrité Comptable :**

- Utilisation des **Transactions SQL** (`BEGIN TRANSACTION` / `COMMIT`) pour luter contre les "Race Conditions". Un crédit/débit et son écriture dans le journal comptable sont traités de manière atomique.
- **Validation serveur stricte** : Rejet automatique des opérations si le solde est insuffisant ou le montant invalide (<= 0), empêchant toute manipulation frauduleuse du payload API.

---

_Test réalisé en se basant sur des pratiques de production réelles (Clean Architecture, typage strict, protection des flux financiers)._
