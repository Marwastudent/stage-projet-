# Plateforme d'affichage multimedia - Clubs sportifs

Backend Laravel 12 expose une API REST pour:
- gestion des ecrans
- gestion des medias (upload image/video)
- gestion des programmes sportifs
- gestion des playlists et items
- gestion des utilisateurs et roles
- diffusion player fullscreen pour ecran TV/box/PC

## Stack
- Laravel 12 (PHP 8.2+)
- API REST JSON
- Base: SQLite (par defaut) ou MySQL
- Frontend Admin: Blade + JavaScript
- Player web: Blade + JavaScript fullscreen

## Lancement rapide
1. Installer les dependances:
```bash
composer install
```
2. Configurer `.env` (SQLite deja prete par defaut, ou MySQL).
   Exemple MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=club_display
DB_USERNAME=root
DB_PASSWORD=
```
3. Migrer + seed:
```bash
php artisan migrate:fresh --seed
```
4. Lier le storage public (pour les medias):
```bash
php artisan storage:link
```
5. Demarrer le serveur:
```bash
php artisan serve
```
6. Ouvrir les interfaces:
- Admin SPA: `http://127.0.0.1:8000/admin`
- Player: `http://127.0.0.1:8000/player/{device_key}`

## Comptes seedes
- admin: `admin@club.local` / `password123`
- manager: `manager@club.local` / `password123`
- client: `client@club.local` / `password123`

## Authentification API
- `POST /api/login`
- `POST /api/logout` (Bearer requis)
- `GET /api/me` (Bearer requis)

### Exemple login
```json
{
  "email": "admin@club.local",
  "password": "password123",
  "device_name": "dashboard-admin"
}
```

Reponse: token Bearer a utiliser dans `Authorization: Bearer <token>`.

## Endpoints principaux
- Screens: `GET/POST/PUT/DELETE /api/screens`
- Assignation playlist -> ecran: `POST /api/screens/{screen}/assign-playlist`
- Media: `GET/POST/PUT/DELETE /api/media`
- Programs: `GET/POST/PUT/DELETE /api/programs`
- Playlists: `GET/POST/PUT/DELETE /api/playlists`
- Reorder items: `PUT /api/playlists/{playlist}/items/reorder`
- Playlist items: `GET/POST/PUT/DELETE /api/playlist-items`
- Users (admin): `GET/POST/PUT/DELETE /api/users`

## Roles
- `admin`: acces complet + gestion utilisateurs
- `manager`: gestion ecrans, medias, programmes, playlists
- `client`: lecture uniquement

## Player ecran
- Feed JSON: `GET /api/player/{device_key}`
- Interface fullscreen: `GET /player/{device_key}`

Le player:
- charge la playlist active de l'ecran
- joue images selon `duration`
- joue videos jusqu'a la fin
- boucle en continu
- rafraichit la playlist automatiquement toutes les 60 secondes
- supporte un fallback offline via cache local
- supporte transitions `fade` (defaut) ou `slide` via `?transition=slide`

## Interface admin
Routes web:
- `/admin` (SPA: authentification + dashboard dans une seule page)
- `/admin/login` redirige vers `/admin`
- `/admin/dashboard` redirige vers `/admin`

Fonctions:
- CRUD complet: ecrans, medias, programmes, playlists, items, utilisateurs
- affectation playlist -> ecran
- ouverture directe du player depuis la table ecrans
- gestion d'ordre des items via drag & drop + sauvegarde ordre API

## Workflow recommande
1. Upload contenu dans `Medias`
2. Creer playlist dans `Playlists`
3. Ajouter les medias dans `Items playlist`
4. Reordonner via drag & drop
5. Affecter playlist a un ecran
6. Ouvrir `/player/{device_key}`

## Fichiers cles
- API routes: `routes/api.php`
- Web player route: `routes/web.php`
- Admin view: `resources/views/admin.blade.php`
- Controllers API: `app/Http/Controllers/Api`
- Middleware auth/role: `app/Http/Middleware/AuthenticateApiToken.php`, `app/Http/Middleware/EnsureRole.php`
- Player view: `resources/views/player.blade.php`
# stage-projet-
