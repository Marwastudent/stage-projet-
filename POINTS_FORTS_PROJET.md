# Points forts du projet + code utilisé

## 1) Authentification sécurisée OTP (2 étapes)
**Point fort :** login + vérification OTP + renvoi OTP pour sécuriser l’accès admin.

**Code principal :**
- `routes/api.php` : routes `POST /api/login`, `POST /api/login/verify-otp`, `POST /api/login/resend-otp`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Models/LoginChallenge.php`

```php
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login/resend-otp', [AuthController::class, 'resendOtp']);
```

---

## 2) Backoffice SPA sans React (navigation sans reload)
**Point fort :** interface fluide en Vanilla JS, sans rechargement de page.

**Code principal :**
- `resources/js/admin/app.js`
- `resources/js/admin/router.js`

```js
const router = createRouter({
    basePath: config.portalBasePath,
    onChange: handleRouteChange,
});
```

---

## 3) Portail admin masqué (sécurité par chemin dynamique)
**Point fort :** URL admin non triviale via `ADMIN_PORTAL_PATH`.

**Code principal :**
- `routes/web.php`

```php
$defaultAdminPortalPath = 'portal-'.substr(hash('sha256', (string) config('app.key')), 0, 20);
$adminPortalPath = trim((string) env('ADMIN_PORTAL_PATH', $defaultAdminPortalPath), '/');
```

---

## 4) Player fullscreen intelligent (média + planning)
**Point fort :** diffusion auto, mode planning, fallback cache offline, refresh périodique.

**Code principal :**
- `resources/views/player.blade.php`
- `app/Http/Controllers/Api/PlayerController.php`

```js
const REFRESH_INTERVAL_MS = 10_000;
const EMPTY_REFRESH_INTERVAL_MS = 4_000;
const OFFLINE_REFRESH_INTERVAL_MS = 12_000;
```

---

## 5) Synchronisation de l’heure player avec le serveur
**Point fort :** horloge affichée alignée sur l’heure backend (timezone serveur).

**Code principal :**
- `app/Http/Controllers/Api/PlayerController.php`
- `resources/views/player.blade.php`
- `config/app.php`

```php
'timezone' => env('APP_TIMEZONE', 'Africa/Casablanca'),
```

---

## 6) Historique des actions (traçabilité métier)
**Point fort :** journalisation des actions CRUD et affichage dashboard.

**Code principal :**
- `app/Services/ActionLogger.php`
- `app/Services/DashboardSummaryService.php`
- `database/migrations/2026_04_15_090000_create_action_logs_table.php`

```php
$logger->log($request, 'program.updated', $program, [
    'label' => $program->title,
    'actor' => $request->user()?->name,
]);
```

---

## 7) Module Programmes avancé (CSV + PDF)
**Point fort :**
- CRUD planning complet
- Import CSV robuste
- Export PDF simplifié (tableau lisible)

**Code principal :**
- `app/Http/Controllers/Api/ProgramController.php`
- `app/Services/ProgramPdfExporter.php`
- `resources/js/admin/pages/programs.js`

```php
Route::post('/programs/import/csv', [ProgramController::class, 'importCsv']);
Route::get('/programs/export/pdf', [ProgramController::class, 'exportPdf']);
```

---

## 8) Import CSV robuste (cas réels)
**Point fort :** support des séparateurs `, ; tab |`, alias FR/EN, erreurs ligne par ligne.

**Code principal :**
- `app/Http/Controllers/Api/ProgramController.php`

```php
$delimiter = $this->detectCsvDelimiter($handle);
$rawHeaders = fgetcsv($handle, 0, $delimiter) ?: [];
```

---

## 9) Contrôle d’accès par rôles (admin/manager)
**Point fort :** permissions claires, suppression du rôle `client`.

**Code principal :**
- `app/Http/Middleware/EnsureRole.php`
- `routes/api.php`
- `app/Http/Requests/Api/UserStoreRequest.php`

```php
Route::middleware('role:admin')->group(function (): void {
    Route::apiResource('users', UserController::class);
});
```

---

## 10) Gestion média stricte (qualité diffusion)
**Point fort :** validation type/poids/durée pour garantir un player stable.

**Code principal :**
- `app/Http/Controllers/Api/MediaController.php`

```php
if ($detectedType === 'video' && (int) $duration > self::MAX_VIDEO_DURATION_SECONDS) {
    throw ValidationException::withMessages([
        'duration' => ['La duree de la video ne doit pas depasser 5 minutes.'],
    ]);
}
```

---

## 11) Données démo réalistes pour soutenance
**Point fort :** seed complet (clubs, écrans, playlists, programmes, coachs, médias).

**Code principal :**
- `database/seeders/DemoClubSeeder.php`

```php
Program::updateOrCreate(
    [
        'screen_id' => $screen->id,
        'day' => $day,
        'start_time' => $start->format('H:i:s'),
        'room' => $room,
    ],
    [
        'title' => $title,
        'duration' => $duration,
        'is_active' => true,
    ]
);
```

---

## 12) Qualité et validation automatisée
**Point fort :** tests Feature API sur modules critiques.

**Code principal :**
- `tests/Feature/CoachApiTest.php`
- `tests/Feature/ProgramApiTest.php`
- `tests/Feature/MediaApiTest.php`
- `tests/Feature/ScreenApiTest.php`
- `tests/Feature/UserApiTest.php`

```php
$response = $this->postJson('/api/users', [
    'name' => 'Manager Test',
    'email' => 'manager.test@example.com',
    'password' => 'password123',
    'role' => 'manager',
], $headers);
```

---

## Résumé valeur ajoutée
- Sécurité forte (OTP + rôles)
- UX fluide (SPA)
- Diffusion robuste (player + cache offline)
- Exploitation métier complète (programmes CSV/PDF)
- Traçabilité fiable (action logs)
- Code maintenable (Laravel structuré + tests)
