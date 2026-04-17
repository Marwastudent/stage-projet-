# Rapport de Projet de Fin d’Études (Version académique)

## Page de garde

**Titre :** Plateforme d’affichage multimédia pour clubs sportifs  
**Étudiant(e) :** [À compléter]  
**Encadrant(e) pédagogique :** [À compléter]  
**Encadrant(e) entreprise :** [À compléter]  
**Établissement :** [À compléter]  
**Année universitaire :** 2025–2026

---

## Remerciements

Je tiens à remercier l’ensemble des personnes ayant contribué à la réussite de ce projet, notamment mes encadrants académiques et professionnels pour leur accompagnement, ainsi que l’équipe technique pour son soutien tout au long du développement.

---

## Résumé

Ce projet présente la conception et la réalisation d’une plateforme web destinée à la gestion centralisée d’écrans d’affichage dans des clubs sportifs. La solution permet la gestion des médias, des playlists, des programmes sportifs, des affectations écran/playlist et la diffusion automatisée via un player web fullscreen.  
L’application repose sur une architecture Laravel 12 avec API REST, une interface d’administration SPA en JavaScript Vanilla, un système d’authentification par token et OTP, ainsi qu’un mécanisme de traçabilité des actions.  
Les fonctionnalités avancées incluent l’import CSV des programmes, l’export PDF tabulaire, la navigation sans rechargement et la synchronisation des données en quasi temps réel.

**Mots-clés :** Laravel, API REST, SPA, affichage dynamique, player web, OTP, planning sportif.

---

## Abstract

This project presents the design and implementation of a centralized multimedia display platform for sports clubs. The system supports media management, playlists, sports scheduling, screen-playlist assignments, and automated fullscreen playback through a web player.  
It is built using Laravel 12 and a REST API, with an admin Single-Page Application written in Vanilla JavaScript. Security is ensured with token-based authentication and OTP verification, while action logging improves traceability.  
Advanced features include CSV import for schedules, PDF export, no-reload navigation, and near real-time data synchronization.

**Keywords:** Laravel, REST API, SPA, dynamic signage, web player, OTP, scheduling.

---

## Table des matières

1. Introduction générale  
2. Étude de l’existant et analyse des besoins  
3. Conception de la solution  
4. Réalisation technique  
5. Validation et tests  
6. Résultats et discussion  
7. Conclusion et perspectives  
8. Annexes

---

## 1. Introduction générale

### 1.1 Contexte
Les clubs sportifs utilisent des écrans pour informer les adhérents (cours, annonces, promotions). Dans de nombreux cas, la diffusion reste manuelle, ce qui entraîne un manque de cohérence et une charge opérationnelle importante.

### 1.2 Problématique
Comment concevoir une plateforme centralisée, sécurisée et maintenable permettant :
- la gestion unifiée des contenus multimédias,
- la planification des programmes sportifs,
- la diffusion automatique sur plusieurs écrans,
- le suivi fiable des actions administratives ?

### 1.3 Objectifs
- Développer une API REST robuste.
- Offrir une interface admin fluide (SPA).
- Garantir la diffusion continue via un player dédié.
- Mettre en place un historique d’actions exploitable.
- Fournir des outils d’import/export (CSV/PDF) pour le planning.

### 1.4 Démarche adoptée
Le projet suit une approche incrémentale : modélisation, implémentation modulaire, validation continue, puis amélioration orientée retours utilisateur.

---

## 2. Étude de l’existant et analyse des besoins

### 2.1 Existant
Les solutions classiques observées sont souvent :
- soit trop génériques,
- soit trop coûteuses,
- soit peu adaptées au contexte club (planning + diffusion mixte).

### 2.2 Besoins fonctionnels
- Authentification sécurisée (OTP + token).
- CRUD complet : écrans, médias, playlists, programmes, coachs, salles, utilisateurs.
- Affectation playlist → écran.
- Diffusion fullscreen sans interruption.
- Import CSV des programmes.
- Export PDF des programmes.
- Dashboard synthétique avec alertes et historique.

### 2.3 Besoins non fonctionnels
- Performance correcte sur usage quotidien.
- Maintenabilité (architecture claire Laravel).
- Sécurité d’accès par rôle.
- Fiabilité de synchronisation SPA/API.
- Extensibilité pour futurs modules.

---

## 3. Conception de la solution

### 3.1 Architecture générale
Architecture en couches :
- **Présentation** : Blade + SPA Vanilla JS.
- **API** : Contrôleurs Laravel.
- **Métier** : Services applicatifs.
- **Données** : Eloquent + MySQL.

### 3.2 Modélisation des données (principales entités)
- `users`, `api_tokens`, `login_challenges`
- `sports_halls`, `screens`
- `media`, `playlists`, `playlist_items`, `screen_playlists`
- `programs`, `coaches`
- `action_logs`

### 3.3 Sécurité et droits
- Middleware `auth.token` pour accès API.
- Middleware `role` pour contrôle fin :
  - `admin` : accès complet + utilisateurs
  - `manager` : gestion opérationnelle

### 3.4 Navigation
Le portail admin est servi sous un chemin non standardisé (`ADMIN_PORTAL_PATH`) avec un routeur SPA interne pour éviter les rechargements.

---

## 4. Réalisation technique

### 4.1 Technologies utilisées
- Backend : Laravel 12, PHP 8.2+
- Front : Blade, JavaScript Vanilla, Vite
- Base : MySQL
- Génération PDF : FPDF/FPDI

### 4.2 API REST
Principaux endpoints :
- Auth : `/api/login`, `/api/login/verify-otp`, `/api/logout`, `/api/me`
- Dashboard : `/api/dashboard/overview`, `/api/dashboard/activity`
- CRUD métiers : `/api/screens`, `/api/media`, `/api/programs`, etc.
- Spécifiques :  
  - `/api/screens/{screen}/assign-playlist`
  - `/api/programs/import/csv`
  - `/api/programs/export/pdf`
  - `/api/player/{device_key}`

### 4.3 Module Programmes
- CRUD complet avec validation.
- Détection de conflits salle/horaire.
- Import CSV avec :
  - détection auto du séparateur,
  - mapping de colonnes FR/EN,
  - contrôle des lignes invalides.
- Export PDF simplifié en tableau aligné sur la vue backoffice.

### 4.4 Module Médias
- Upload image/vidéo.
- Validation type/poids/durée.
- Stockage public (`storage/app/public`) et exposition via `/storage`.
- Prérequis opérationnel : `php artisan storage:link`.

### 4.5 Player de diffusion
- Deux modes : média et planning.
- Rafraîchissement périodique du feed.
- Cache local en fallback réseau.
- Affichage heure serveur (timezone configurable).

### 4.6 Dashboard et traçabilité
- Statistiques globales (écrans, playlists, médias, programmes du jour).
- Alertes actionnables.
- Historique récent basé sur `action_logs` avec fallback intelligent.

---

## 5. Validation et tests

### 5.1 Tests automatisés
Le projet inclut des tests Feature sur les modules API principaux :
- écrans,
- médias,
- programmes,
- coachs,
- player planning feed.

### 5.2 Vérifications manuelles
- Parcours complet de bout en bout :
  1) upload média,  
  2) création playlist,  
  3) affectation écran,  
  4) visualisation player.
- Contrôle de l’import CSV et de l’export PDF.
- Contrôle des rôles et droits d’accès.

### 5.3 Résolution d’incidents observés
- Écran admin blanc lié à un `public/hot` résiduel.
- Erreurs 403 sur médias liées au lien storage manquant.
- Import CSV initial strict, ensuite rendu robuste (alias FR/EN + séparateurs).

---

## 6. Résultats et discussion

### 6.1 Résultats obtenus
- Interface admin moderne et fluide (SPA).
- Diffusion player stable et exploitable en environnement réel.
- Gestion centralisée complète du cycle contenu → écran.
- Fonctionnalités de planification enrichies (CSV/PDF).

### 6.2 Apports techniques
- Architecture Laravel structurée et maintenable.
- Séparation claire contrôleurs/services/requests/resources.
- Historique d’actions favorisant l’audit et l’exploitation.

### 6.3 Limites actuelles
- Pas encore de modèle CSV téléchargeable intégré.
- Certains messages fallback front encore via `alert()`.
- Monitoring temps réel des écrans perfectible.

---

## 7. Conclusion et perspectives

### 7.1 Conclusion
Le projet répond aux objectifs initiaux : il fournit une plateforme opérationnelle, sécurisée et extensible pour l’affichage multimédia en club sportif, avec une expérience admin sans reload et un player fiable.

### 7.2 Perspectives
- Ajouter un bouton “Télécharger modèle CSV”.
- Générer un rapport d’erreurs d’import détaillé.
- Mettre en place un monitoring heartbeat des écrans.
- Introduire des jobs asynchrones pour traitements lourds.
- Enrichir la couverture de tests (E2E front/back).

---

## 8. Annexes

### Annexe A — Commandes de démarrage
```bash
composer install
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

### Annexe B — Structure fonctionnelle (résumé)
- Authentification OTP + token
- Dashboard KPI + activité
- Gestion écrans / diffusions
- Gestion médias / playlists
- Gestion programmes / coachs / salles
- Player fullscreen

### Annexe C — Exemple d’en-tête CSV programmes
```csv
cours,jour,horaire,duree,coach,salle,ecran,statut,ordre
```
ou
```csv
title,day,start_time,duration,coach,room,screen,is_active,display_order
```

---

**Fin du rapport**
