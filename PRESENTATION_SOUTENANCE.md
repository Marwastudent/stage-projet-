# Présentation de soutenance (10 slides)

## Slide 1 — Contexte & problématique
- Les clubs sportifs utilisent plusieurs écrans (accueil, salles, zones d’entraînement).
- La gestion manuelle des contenus provoque des erreurs et des pertes de temps.
- Besoin d’une plateforme centralisée, sécurisée et simple à exploiter.

---

## Slide 2 — Objectifs du projet
- Centraliser la gestion des écrans, médias, playlists et programmes.
- Offrir une interface admin fluide (SPA, sans reload).
- Assurer une diffusion fiable via un player fullscreen.
- Ajouter traçabilité (historique actions) et outils d’exploitation (CSV/PDF).

---

## Slide 3 — Architecture globale
- Backend : Laravel 12 (API REST).
- Front admin : Blade + JavaScript Vanilla (SPA).
- Player : page web dédiée par `device_key`.
- Base de données : MySQL (ou SQLite en dev).
- Services : logique métier (dashboard, logs, export PDF).

---

## Slide 4 — Modèle de données (entités clés)
- `users`, `api_tokens`, `login_challenges`
- `sports_halls`, `screens`
- `media`, `playlists`, `playlist_items`, `screen_playlists`
- `programs`, `coaches`
- `action_logs`

Relations fortes :
- salle → écrans, playlist → items, écran → programmes, user → tokens/logs.

---

## Slide 5 — Modules fonctionnels
- Authentification OTP + token.
- Dashboard KPI + alertes + activité récente.
- Gestion écrans et affectations playlists.
- Gestion médias (upload image/vidéo).
- Gestion programmes (CRUD + import CSV + export PDF).
- Gestion coachs / clubs / utilisateurs.

---

## Slide 6 — Sécurité
- Login en 2 étapes : mot de passe + OTP.
- Middleware `auth.token` pour API.
- Middleware `role` (admin, manager).
- Portail admin protégé par chemin dynamique `ADMIN_PORTAL_PATH`.

---

## Slide 7 — Points forts techniques
- SPA sans framework lourd : rapide et maintenable.
- Player robuste : refresh auto + fallback offline cache.
- Heure player synchronisée avec le serveur.
- Import CSV programmes robuste (FR/EN + séparateurs variés).
- Export PDF simplifié en tableau lisible.
- Journalisation des actions (audit métier).

---

## Slide 8 — Démo (scénario utilisateur)
1. Admin se connecte (OTP).
2. Upload média.
3. Création playlist + items.
4. Affectation playlist à un écran.
5. Ajout/import programmes CSV.
6. Export PDF des programmes.
7. Ouverture du player `/player/{device_key}`.

---

## Slide 9 — Résultats & limites
### Résultats
- Gestion centralisée opérationnelle.
- Expérience admin fluide.
- Diffusion stable sur écrans.

### Limites
- Monitoring écran temps réel à renforcer.
- Génération auto d’un modèle CSV à ajouter.
- Quelques interactions front à uniformiser en toasts.

---

## Slide 10 — Conclusion & perspectives
- Objectifs atteints : plateforme fiable, extensible et orientée exploitation.
- Perspectives :
  - monitoring heartbeat des écrans,
  - rapport d’erreurs CSV téléchargeable,
  - tests E2E supplémentaires,
  - automatisations via jobs/queues.

---

## Conseils de soutenance (bonus)
- Montrer une démo courte et fluide (2–3 minutes).
- Insister sur la valeur métier : gain de temps + fiabilité diffusion.
- Mettre en avant la sécurité (OTP + rôles) et la traçabilité (action logs).
