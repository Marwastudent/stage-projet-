<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sports Club Display</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #051018;
            --bg-2: #10364a;
            --bg-3: #0f5c63;
            --panel: rgba(7, 20, 30, 0.74);
            --line: rgba(255,255,255,0.15);
            --txt: #eaf6ff;
            --soft: #9ec0d2;
            --ok: #37d9b2;
            --err: #ff6b6b;
            --warn: #f59e0b;
            --main: #ffb100;
            --font-body: "Manrope", "Trebuchet MS", sans-serif;
            --font-title: "Rajdhani", "Franklin Gothic Medium", sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background:
                radial-gradient(circle at 10% 14%, rgba(255,177,0,0.24), transparent 35%),
                radial-gradient(circle at 86% 82%, rgba(55,217,178,0.2), transparent 35%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 55%, var(--bg-3));
            color: var(--txt);
            font-family: var(--font-body);
        }
        .auth-view {
            width: 100%;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 14px;
        }
        .auth-card {
            width: min(470px, 94vw);
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--panel);
            padding: 16px;
            backdrop-filter: blur(8px);
        }
        .page { width: min(1280px, 96vw); margin: 14px auto 24px; display: grid; gap: 10px; }
        .hero, .card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--panel);
            padding: 12px;
            backdrop-filter: blur(8px);
        }
        .hero h1 {
            margin: 0;
            font-size: clamp(1.45rem, 2.6vw, 2.2rem);
            letter-spacing: 0.05em;
            font-family: var(--font-title);
        }
        .hero p { margin: 6px 0 0; color: var(--soft); }
        .layout { display: grid; grid-template-columns: 320px 1fr; gap: 10px; }
        h2 {
            margin: 0 0 8px;
            font-size: 1.2rem;
            letter-spacing: 0.04em;
            font-family: var(--font-title);
        }
        h3 {
            margin: 10px 0 8px;
            font-size: 1rem;
            letter-spacing: 0.03em;
            font-family: var(--font-title);
        }
        .stack, .grid-2, .grid-3 { display: grid; gap: 8px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        label {
            display: block;
            margin: 0 0 5px;
            color: var(--soft);
            font-size: 0.84rem;
            letter-spacing: 0.02em;
        }
        input, select, button {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #071826;
            color: var(--txt);
            padding: 8px 9px;
            font: 500 0.92rem var(--font-body);
        }
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"] {
            color-scheme: dark;
        }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: brightness(0) invert(1);
            opacity: 1;
            cursor: pointer;
        }
        button { cursor: pointer; }
        .btn-main { background: rgba(255,177,0,0.24); border-color: rgba(255,177,0,0.58); }
        .btn-ok { background: rgba(55,217,178,0.22); border-color: rgba(55,217,178,0.56); }
        .btn-danger { background: rgba(255,107,107,0.22); border-color: rgba(255,107,107,0.56); }
        .btn-warn { background: rgba(245,158,11,0.18); border-color: rgba(245,158,11,0.5); }
        .tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
        .tabs button { width: auto; }
        .tabs button.active { border-color: rgba(55,217,178,0.72); background: rgba(55,217,178,0.2); }
        .panel { display: none; gap: 9px; }
        .panel.active { display: grid; }
        .msg { min-height: 18px; color: var(--soft); font-size: 0.9rem; }
        .msg.ok { color: var(--ok); }
        .msg.error { color: var(--err); }
        .msg.warn { color: var(--warn); }
        .pill { display: inline-block; border: 1px solid var(--line); border-radius: 999px; padding: 3px 8px; font-size: 0.78rem; }
        .note { color: var(--soft); font-size: 0.85rem; line-height: 1.4; border-top: 1px dashed var(--line); padding-top: 7px; }
        .table-wrap { overflow: auto; border: 1px solid var(--line); border-radius: 10px; }
        table { width: 100%; min-width: 760px; border-collapse: collapse; background: rgba(5,12,20,0.55); }
        th, td { padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; font-size: 0.9rem; }
        th { color: var(--soft); background: rgba(0,0,0,0.18); }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .actions button, .actions a {
            width: auto;
            padding: 4px 7px;
            font-size: 0.8rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #071826;
            color: var(--txt);
            text-decoration: none;
        }
        .icon-action {
            width: 34px !important;
            height: 30px;
            padding: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .icon-action svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
        }
        .icon-action.play { color: #7ed8ff; }
        .icon-action.edit { color: #ffd166; }
        .icon-action.delete { color: #ff9a9a; }
        .icon-action.stop { color: #ffb366; }
        .drag-handle { cursor: grab; font-weight: 700; color: var(--soft); }
        tr.dragging { opacity: 0.45; }
        tr.drop-target { outline: 1px dashed rgba(255,183,3,0.8); outline-offset: -2px; }
        @media (max-width: 1000px) {
            .layout { grid-template-columns: 1fr; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<section id="auth-view" class="auth-view">
    <div class="auth-card">
        <h2>Connexion Dashboard (2FA)</h2>
        <p class="note" style="border-top:none;padding-top:0;margin-top:0;">Etape 1: email + mot de passe. Etape 2: saisir le code OTP recu.</p>
        <div class="stack">
            <input id="auth-email" type="email" value="marwaaitbahadou4@gmail.com" placeholder="Email">
            <input id="auth-password" type="password" value="password123" placeholder="Mot de passe">
            <button id="btn-login" class="btn-main">Connexion</button>
            <input id="auth-otp" type="text" maxlength="6" placeholder="Code OTP (6 chiffres)" style="display:none;letter-spacing:0.28em;">
            <div id="auth-otp-actions" class="grid-2" style="display:none;">
                <button id="btn-verify-otp" class="btn-ok">Valider OTP</button>
                <button id="btn-resend-otp" class="btn-warn">Renvoyer OTP</button>
            </div>
        </div>
        <p id="auth-msg" class="msg"></p>
    </div>
</section>

<div id="app-view" class="page" style="display:none;">
    <section class="hero">
        <h1>Plateforme d'affichage multimedia - Clubs sportifs</h1>
        <p>Workflow: media -> playlist -> items -> ecran -> player</p>
    </section>

    <section class="layout">
        <aside class="card">
            <h2>Session Dashboard</h2>
            <div class="stack">
                <p id="session-user"><span class="pill">Verification session...</span></p>
                <button id="btn-logout" class="btn-danger">Se deconnecter</button>
                <p class="note" style="margin:0;border-top:none;padding-top:0;">Mode SPA actif: login + dashboard sans changement de page.</p>
            </div>
            <p id="global-msg" class="msg"></p>
            <p class="note">
                1) Upload media<br>
                2) Creer playlist<br>
                3) Ajouter items + drag/drop<br>
                4) Affecter playlist a ecran<br>
                5) Ouvrir player
            </p>
        </aside>

        <main class="card">
            <div class="tabs">
                <button class="tab-btn active" data-tab="screens">Ecrans</button>
                <button class="tab-btn" data-tab="assignments">Affectations</button>
                <button class="tab-btn" data-tab="sports-halls">Salles sport</button>
                <button class="tab-btn" data-tab="ads">Publicites</button>
                <button class="tab-btn" data-tab="media">Medias</button>
                <button class="tab-btn" data-tab="programs">Programmes</button>
                <button class="tab-btn" data-tab="playlists">Playlists</button>
                <button class="tab-btn" data-tab="items">Items playlist</button>
                <button class="tab-btn" data-tab="users">Utilisateurs</button>
            </div>

            <section id="panel-screens" class="panel active">
                <h2>Ecrans (CRUD)</h2>
                <div class="grid-2">
                    <input id="screen-name" placeholder="Nom ecran">
                    <select id="screen-emplacement">
                        <option value="">Selectionner emplacement</option>
                        <option value="entree">Entree</option>
                        <option value="sortie">Sortie</option>
                        <option value="cafeteria">Cafeteria</option>
                    </select>
                    <select id="screen-sports-hall-id"><option value="">Selectionner salle de sport</option></select>
                    <select id="screen-status">
                        <option value="offline">offline</option>
                        <option value="online">online</option>
                        <option value="maintenance">maintenance</option>
                    </select>
                </div>
                <div class="grid-2">
                    <button id="btn-save-screen" class="btn-main">Ajouter ecran</button>
                    <button id="btn-cancel-screen" class="btn-warn">Annuler edition</button>
                </div>
                <p id="screen-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-screens"></table></div>
            </section>

            <section id="panel-assignments" class="panel">
                <h2>Affectation playlist -> ecran</h2>
                <div class="grid-2">
                    <select id="assign-screen-id"><option value="">Selectionner un ecran</option></select>
                    <select id="assign-playlist-id"><option value="">Selectionner une playlist</option></select>
                </div>
                <div class="grid-2">
                    <div>
                        <label for="assign-start-at">Date et heure debut</label>
                        <input id="assign-start-at" type="datetime-local" step="60">
                    </div>
                    <div>
                        <label for="assign-end-at">Date et heure fin</label>
                        <input id="assign-end-at" type="datetime-local" step="60">
                    </div>
                </div>
                <button id="btn-assign-playlist" class="btn-ok">Affecter la playlist</button>
                <p id="assignment-msg" class="msg"></p>
                <input id="assignment-search" placeholder="Filtrer affectations (ecran, device key, playlist, statut, date)">
                <div class="table-wrap"><table id="table-assignments"></table></div>
            </section>

            <section id="panel-sports-halls" class="panel">
                <h2>Salles de sport (CRUD)</h2>
                <div class="grid-3">
                    <input id="sports-hall-name" placeholder="Nom salle de sport">
                    <input id="sports-hall-matricule" placeholder="Matricule">
                    <input id="sports-hall-localisation" placeholder="Localisation">
                </div>
                <div class="grid-2">
                    <button id="btn-save-sports-hall" class="btn-main">Ajouter salle de sport</button>
                    <button id="btn-cancel-sports-hall" class="btn-warn">Annuler edition</button>
                </div>
                <p id="sports-hall-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-sports-halls"></table></div>
            </section>

            <section id="panel-ads" class="panel">
                <h2>Programmation publicitaire</h2>
                <div class="grid-3">
                    <input id="ad-name" placeholder="Nom campagne pub">
                    <select id="ad-screen-id"><option value="">Selectionner ecran</option></select>
                    <select id="ad-media-id"><option value="">Selectionner media publicite</option></select>
                    <input id="ad-starts-at" type="datetime-local" placeholder="Debut">
                    <input id="ad-ends-at" type="datetime-local" placeholder="Fin">
                    <input id="ad-duration-override" type="number" min="1" placeholder="Duree override (sec)">
                    <input id="ad-display-every-loops" type="number" min="1" value="1" placeholder="Affichage tous les X loops">
                    <select id="ad-is-active">
                        <option value="1">active</option>
                        <option value="0">inactive</option>
                    </select>
                </div>
                <div class="grid-2">
                    <button id="btn-save-ad" class="btn-main">Programmer publicite</button>
                    <button id="btn-cancel-ad" class="btn-warn">Annuler edition</button>
                </div>
                <p id="ad-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-ads"></table></div>
            </section>

            <section id="panel-media" class="panel">
                <h2>Medias (CRUD)</h2>
                <div class="grid-2">
                    <input id="media-title" placeholder="Titre media">
                    <select id="media-type"><option value="image">image</option><option value="video">video</option></select>
                    <input id="media-duration" type="number" min="0" placeholder="Duree image (sec)">
                    <input id="media-file" type="file" accept="image/*,video/*">
                </div>
                <div class="grid-2">
                    <button id="btn-save-media" class="btn-main">Uploader media</button>
                    <button id="btn-cancel-media" class="btn-warn">Annuler edition</button>
                </div>
                <p class="note">En edition, le fichier est optionnel.</p>
                <p id="media-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-media"></table></div>
            </section>

            <section id="panel-programs" class="panel">
                <h2>Programmes (CRUD)</h2>
                <div class="grid-3">
                    <input id="program-day" placeholder="Jour">
                    <input id="program-start-time" type="time">
                    <input id="program-end-time" type="time">
                    <input id="program-coach" placeholder="Coach">
                    <input id="program-room" placeholder="Salle">
                </div>
                <div class="grid-2">
                    <button id="btn-save-program" class="btn-main">Ajouter programme</button>
                    <button id="btn-cancel-program" class="btn-warn">Annuler edition</button>
                </div>
                <p id="program-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-programs"></table></div>
            </section>

            <section id="panel-playlists" class="panel">
                <h2>Playlists (CRUD)</h2>
                <div class="grid-2"><input id="playlist-name" placeholder="Nom playlist"></div>
                <div class="grid-2">
                    <button id="btn-save-playlist" class="btn-main">Creer playlist</button>
                    <button id="btn-cancel-playlist" class="btn-warn">Annuler edition</button>
                </div>
                <p id="playlist-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-playlists"></table></div>
            </section>

            <section id="panel-items" class="panel">
                <h2>Items playlist (CRUD + drag/drop)</h2>
                <div class="grid-2">
                    <select id="items-playlist-filter"><option value="">Selectionner une playlist</option></select>
                    <button id="btn-load-items" class="btn-main">Charger items</button>
                </div>
                <div class="grid-3">
                    <select id="item-media-id"><option value="">Selectionner media</option></select>
                    <input id="item-order" type="number" min="1" placeholder="Order (optionnel)">
                    <input id="item-duration-override" type="number" min="1" placeholder="Duration override (optionnel)">
                </div>
                <div class="grid-2">
                    <button id="btn-save-item" class="btn-main">Ajouter item</button>
                    <button id="btn-cancel-item" class="btn-warn">Annuler edition</button>
                </div>
                <button id="btn-save-order" class="btn-ok">Sauvegarder ordre</button>
                <p id="item-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-items"></table></div>
            </section>

            <section id="panel-users" class="panel">
                <h2>Utilisateurs (CRUD admin)</h2>
                <div class="grid-2">
                    <input id="user-name" placeholder="Nom">
                    <input id="user-email" type="email" placeholder="Email">
                    <input id="user-password" type="text" placeholder="Mot de passe">
                    <select id="user-role"><option value="client">client</option><option value="manager">manager</option><option value="admin">admin</option></select>
                </div>
                <div class="grid-2">
                    <button id="btn-save-user" class="btn-main">Ajouter utilisateur</button>
                    <button id="btn-cancel-user" class="btn-warn">Annuler edition</button>
                </div>
                <p id="user-msg" class="msg"></p>
                <div class="table-wrap"><table id="table-users"></table></div>
            </section>
        </main>
    </section>
</div>

<script>
const state = {
    token: localStorage.getItem('club_api_token') || '',
    authChallenge: null,
    authExpiresInDays: 30,
    authDeviceName: 'dashboard-web',
    user: null,
    screens: [],
    assignments: [],
    sportsHalls: [],
    adSchedules: [],
    media: [],
    programs: [],
    playlists: [],
    playlistItems: [],
    users: [],
    editing: {
        screenId: null,
        sportsHallId: null,
        adScheduleId: null,
        mediaId: null,
        programId: null,
        playlistId: null,
        assignmentId: null,
        itemId: null,
        userId: null,
    },
    draggingItemId: null,
};

const refs = {
    authView: document.getElementById('auth-view'),
    appView: document.getElementById('app-view'),
    btnLogin: document.getElementById('btn-login'),
    authEmail: document.getElementById('auth-email'),
    authPassword: document.getElementById('auth-password'),
    authOtp: document.getElementById('auth-otp'),
    authOtpActions: document.getElementById('auth-otp-actions'),
    btnVerifyOtp: document.getElementById('btn-verify-otp'),
    btnResendOtp: document.getElementById('btn-resend-otp'),
    authMsg: document.getElementById('auth-msg'),
    sessionUser: document.getElementById('session-user'),
    globalMsg: document.getElementById('global-msg'),
    msg: {
        screens: document.getElementById('screen-msg'),
        assignments: document.getElementById('assignment-msg'),
        sportsHalls: document.getElementById('sports-hall-msg'),
        ads: document.getElementById('ad-msg'),
        media: document.getElementById('media-msg'),
        programs: document.getElementById('program-msg'),
        playlists: document.getElementById('playlist-msg'),
        items: document.getElementById('item-msg'),
        users: document.getElementById('user-msg'),
    },
    table: {
        screens: document.getElementById('table-screens'),
        assignments: document.getElementById('table-assignments'),
        sportsHalls: document.getElementById('table-sports-halls'),
        ads: document.getElementById('table-ads'),
        media: document.getElementById('table-media'),
        programs: document.getElementById('table-programs'),
        playlists: document.getElementById('table-playlists'),
        items: document.getElementById('table-items'),
        users: document.getElementById('table-users'),
    },
};

function setMessage(el, text, mode = '') {
    el.textContent = text || '';
    el.classList.remove('ok', 'error', 'warn');
    if (mode) el.classList.add(mode);
}

function firstValidationError(payload) {
    if (!payload || !payload.errors) return '';
    return Object.values(payload.errors).flat()[0] || '';
}

async function api(path, options = {}) {
    const headers = { Accept: 'application/json' };
    const method = options.method || 'GET';
    let body;

    if (options.json !== undefined) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(options.json);
    } else if (options.formData) {
        body = options.formData;
    }

    if (state.token) headers.Authorization = `Bearer ${state.token}`;

    const response = await fetch(`/api${path}`, { method, headers, body });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || firstValidationError(payload) || `Erreur API ${response.status}`);
    }

    return payload;
}

function extractRows(payload) {
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload.data)) return payload.data;
    if (Array.isArray(payload.data?.data)) return payload.data.data;
    return [];
}

function localDateTimeToApi(value) {
    if (!value) return null;

    const normalized = value.includes('T') ? value : value.replace(' ', 'T');
    const parsed = new Date(normalized);

    if (Number.isNaN(parsed.getTime())) {
        return null;
    }

    return parsed.toISOString().slice(0, 19).replace('T', ' ');
}

function toApiDateTime(value) {
    if (!value) return null;
    const withSeconds = value.length === 16 ? `${value}:00` : value;
    return localDateTimeToApi(withSeconds);
}

function toInputDateTimeParts(value) {
    if (!value) return null;

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return null;

    const year = parsed.getFullYear();
    const month = String(parsed.getMonth() + 1).padStart(2, '0');
    const day = String(parsed.getDate()).padStart(2, '0');
    const hour = String(parsed.getHours()).padStart(2, '0');
    const minute = String(parsed.getMinutes()).padStart(2, '0');

    return {
        date: `${year}-${month}-${day}`,
        time: `${hour}:${minute}`,
        datetimeLocal: `${year}-${month}-${day}T${hour}:${minute}`,
    };
}

function fillAssignmentDefaults() {
    const start = new Date();
    start.setSeconds(0, 0);
    const end = new Date(start.getTime() + 60 * 60 * 1000);

    document.getElementById('assign-start-at').value = toInputDateTimeParts(start)?.datetimeLocal ?? '';
    document.getElementById('assign-end-at').value = toInputDateTimeParts(end)?.datetimeLocal ?? '';
}

function formatDateTime(value) {
    if (!value) return '-';
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return value;
    return parsed.toLocaleString();
}

function normalizeAssignmentStatus(assignment) {
    const raw = String(assignment.runtime_status ?? '').toLowerCase();
    if (raw === 'running' || raw === 'planned' || raw === 'expired') return raw;
    if (raw === 'inactive') return 'expired';
    if (!assignment.is_active) return 'expired';
    return 'running';
}

function buildAssignmentSearchText(assignment) {
    return [
        assignment.id,
        assignment.screen?.name,
        assignment.screen?.device_key,
        assignment.playlist?.name,
        normalizeAssignmentStatus(assignment),
        formatDateTime(assignment.starts_at),
        formatDateTime(assignment.ends_at),
    ].join(' ').toLowerCase();
}

function showPanel(name) {
    document.querySelectorAll('.tab-btn').forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === name));
    document.querySelectorAll('.panel').forEach((panel) => panel.classList.toggle('active', panel.id === `panel-${name}`));
}

function updateSessionUi() {
    if (!state.token || !state.user) {
        refs.sessionUser.innerHTML = '<span class="pill">Non connecte</span>';
        return;
    }

    refs.sessionUser.innerHTML = `<span class="pill">${state.user.name} (${state.user.role})</span>`;
}

function syncSharedSelectors() {
    const screenSportsHall = document.getElementById('screen-sports-hall-id');
    const assignScreen = document.getElementById('assign-screen-id');
    const assignPlaylist = document.getElementById('assign-playlist-id');
    const itemsPlaylist = document.getElementById('items-playlist-filter');
    const itemMedia = document.getElementById('item-media-id');
    const adScreen = document.getElementById('ad-screen-id');
    const adMedia = document.getElementById('ad-media-id');

    const oldScreenSportsHall = screenSportsHall.value;
    const oldAssignScreen = assignScreen.value;
    const oldAssignPlaylist = assignPlaylist.value;
    const oldItemsPlaylist = itemsPlaylist.value;
    const oldItemMedia = itemMedia.value;
    const oldAdScreen = adScreen.value;
    const oldAdMedia = adMedia.value;

    screenSportsHall.innerHTML = '<option value="">Selectionner salle de sport</option>' +
        state.sportsHalls.map((hall) => `<option value="${hall.id}">${hall.name} (${hall.matricule})</option>`).join('');

    assignScreen.innerHTML = '<option value="">Selectionner un ecran</option>' +
        state.screens.map((s) => `<option value="${s.id}">${s.name} (${s.device_key})</option>`).join('');

    const playlistOptions = state.playlists.map((p) => `<option value="${p.id}">${p.name}</option>`).join('');
    assignPlaylist.innerHTML = '<option value="">Selectionner une playlist</option>' + playlistOptions;
    itemsPlaylist.innerHTML = '<option value="">Selectionner une playlist</option>' + playlistOptions;

    itemMedia.innerHTML = '<option value="">Selectionner media</option>' +
        state.media.map((m) => `<option value="${m.id}">#${m.id} - ${m.title} (${m.type})</option>`).join('');

    adScreen.innerHTML = '<option value="">Selectionner ecran</option>' +
        state.screens.map((s) => `<option value="${s.id}">${s.name} (${s.device_key})</option>`).join('');

    adMedia.innerHTML = '<option value="">Selectionner media publicite</option>' +
        state.media.map((m) => `<option value="${m.id}">#${m.id} - ${m.title} (${m.type})</option>`).join('');

    if (oldScreenSportsHall && state.sportsHalls.some((hall) => String(hall.id) === String(oldScreenSportsHall))) screenSportsHall.value = oldScreenSportsHall;
    if (oldAssignScreen && state.screens.some((s) => String(s.id) === String(oldAssignScreen))) assignScreen.value = oldAssignScreen;
    if (oldAssignPlaylist && state.playlists.some((p) => String(p.id) === String(oldAssignPlaylist))) assignPlaylist.value = oldAssignPlaylist;
    if (oldItemsPlaylist && state.playlists.some((p) => String(p.id) === String(oldItemsPlaylist))) itemsPlaylist.value = oldItemsPlaylist;
    if (oldItemMedia && state.media.some((m) => String(m.id) === String(oldItemMedia))) itemMedia.value = oldItemMedia;
    if (oldAdScreen && state.screens.some((s) => String(s.id) === String(oldAdScreen))) adScreen.value = oldAdScreen;
    if (oldAdMedia && state.media.some((m) => String(m.id) === String(oldAdMedia))) adMedia.value = oldAdMedia;
}

function renderTable(el, headers, rows) {
    if (!rows.length) {
        el.innerHTML = '<tr><td>Aucune donnee</td></tr>';
        return;
    }

    const head = `<thead><tr>${headers.map((h) => `<th>${h}</th>`).join('')}</tr></thead>`;
    const body = `<tbody>${rows.map((row) => `<tr ${row.attrs || ''}>${row.cells.map((cell) => `<td>${cell ?? ''}</td>`).join('')}</tr>`).join('')}</tbody>`;
    el.innerHTML = `${head}${body}`;
}

function renderAllTables() {
    renderTable(refs.table.screens, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.assignments, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.sportsHalls, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.ads, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.media, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.programs, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.playlists, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.items, ['Info'], [{ cells: ['Aucune donnee'] }]);
    renderTable(refs.table.users, ['Info'], [{ cells: ['Aucune donnee'] }]);
}

function resetForms() {
    state.editing = { screenId: null, sportsHallId: null, adScheduleId: null, mediaId: null, programId: null, playlistId: null, assignmentId: null, itemId: null, userId: null };

    document.getElementById('screen-name').value = '';
    document.getElementById('screen-emplacement').value = '';
    document.getElementById('screen-sports-hall-id').value = '';
    document.getElementById('screen-status').value = 'offline';
    document.getElementById('btn-save-screen').textContent = 'Ajouter ecran';

    document.getElementById('sports-hall-name').value = '';
    document.getElementById('sports-hall-matricule').value = '';
    document.getElementById('sports-hall-localisation').value = '';
    document.getElementById('btn-save-sports-hall').textContent = 'Ajouter salle de sport';

    document.getElementById('ad-name').value = '';
    document.getElementById('ad-screen-id').value = '';
    document.getElementById('ad-media-id').value = '';
    document.getElementById('ad-starts-at').value = '';
    document.getElementById('ad-ends-at').value = '';
    document.getElementById('ad-duration-override').value = '';
    document.getElementById('ad-display-every-loops').value = '1';
    document.getElementById('ad-is-active').value = '1';
    document.getElementById('btn-save-ad').textContent = 'Programmer publicite';

    document.getElementById('assign-screen-id').value = '';
    document.getElementById('assign-playlist-id').value = '';
    fillAssignmentDefaults();
    document.getElementById('btn-assign-playlist').textContent = 'Affecter la playlist';

    document.getElementById('media-title').value = '';
    document.getElementById('media-type').value = 'image';
    document.getElementById('media-duration').value = '';
    document.getElementById('media-file').value = '';
    document.getElementById('btn-save-media').textContent = 'Uploader media';

    document.getElementById('program-day').value = '';
    document.getElementById('program-start-time').value = '';
    document.getElementById('program-end-time').value = '';
    document.getElementById('program-coach').value = '';
    document.getElementById('program-room').value = '';
    document.getElementById('btn-save-program').textContent = 'Ajouter programme';

    document.getElementById('playlist-name').value = '';
    document.getElementById('btn-save-playlist').textContent = 'Creer playlist';

    document.getElementById('item-media-id').value = '';
    document.getElementById('item-order').value = '';
    document.getElementById('item-duration-override').value = '';
    document.getElementById('btn-save-item').textContent = 'Ajouter item';

    document.getElementById('user-name').value = '';
    document.getElementById('user-email').value = '';
    document.getElementById('user-password').value = '';
    document.getElementById('user-role').value = 'client';
    document.getElementById('btn-save-user').textContent = 'Ajouter utilisateur';
}

function switchSpaView(isAuthenticated) {
    refs.authView.style.display = isAuthenticated ? 'none' : 'grid';
    refs.appView.style.display = isAuthenticated ? 'grid' : 'none';
}

function setTwoFactorMode(enabled) {
    refs.authOtp.style.display = enabled ? 'block' : 'none';
    refs.authOtpActions.style.display = enabled ? 'grid' : 'none';
    refs.btnLogin.disabled = enabled;
    refs.authEmail.readOnly = enabled;
    refs.authPassword.readOnly = enabled;
    refs.btnVerifyOtp.disabled = !enabled;
    refs.btnResendOtp.disabled = !enabled;

    if (!enabled) {
        refs.authOtp.value = '';
        state.authChallenge = null;
    }
}

async function completeAuthenticatedSession(payload) {
    state.token = payload.token;
    localStorage.setItem('club_api_token', state.token);
    const authenticated = await loadMe();

    if (!authenticated) {
        setMessage(refs.authMsg, 'Token invalide apres connexion.', 'error');
        switchSpaView(false);
        return;
    }

    setTwoFactorMode(false);
    setMessage(refs.authMsg, 'Connexion reussie.', 'ok');
    setMessage(refs.globalMsg, 'Bienvenue sur le dashboard.', 'ok');
    switchSpaView(true);
    await refreshAll();
}

async function loadMe() {
    if (!state.token) {
        state.user = null;
        updateSessionUi();
        return false;
    }

    try {
        const payload = await api('/me');
        state.user = payload.data;
        updateSessionUi();
        return true;
    } catch {
        state.token = '';
        state.user = null;
        localStorage.removeItem('club_api_token');
        updateSessionUi();
        return false;
    }
}

async function login() {
    setTwoFactorMode(false);

    try {
        const payload = await api('/login', {
            method: 'POST',
            json: {
                email: refs.authEmail.value.trim(),
                password: refs.authPassword.value,
                device_name: state.authDeviceName,
                expires_in_days: state.authExpiresInDays,
            },
        });

        if (!payload.two_factor_required || !payload.challenge_token) {
            if (payload.token) {
                await completeAuthenticatedSession(payload);
                return;
            }

            setMessage(refs.authMsg, 'Reponse inattendue du serveur.', 'error');
            return;
        }

        state.authChallenge = {
            challengeToken: payload.challenge_token,
            expiresAt: payload.expires_at,
        };
        setTwoFactorMode(true);

        const otpInfo = payload.debug_otp ? ` Code dev: ${payload.debug_otp}` : '';
        setMessage(refs.authMsg, `Code OTP envoye. Verifie ta boite mail.${otpInfo}`, 'warn');
    } catch (error) {
        setMessage(refs.authMsg, error.message, 'error');
    }
}

async function verifyOtp() {
    if (!state.authChallenge?.challengeToken) {
        setMessage(refs.authMsg, 'Commence par la connexion email/mot de passe.', 'warn');
        return;
    }

    const otp = refs.authOtp.value.replace(/\D/g, '').trim();
    refs.authOtp.value = otp;

    if (!/^\d{6}$/.test(otp)) {
        setMessage(refs.authMsg, 'Le code OTP doit contenir 6 chiffres.', 'warn');
        return;
    }

    try {
        const payload = await api('/login/verify-otp', {
            method: 'POST',
            json: {
                challenge_token: state.authChallenge.challengeToken,
                otp,
                expires_in_days: state.authExpiresInDays,
            },
        });

        await completeAuthenticatedSession(payload);
    } catch (error) {
        const help = error.message === 'Invalid OTP code.'
            ? "Invalid OTP code. Utilise le dernier code recu, sans recliquer sur 'Connexion'."
            : error.message;
        setMessage(refs.authMsg, help, 'error');
    }
}

async function resendOtp() {
    if (!state.authChallenge?.challengeToken) {
        setMessage(refs.authMsg, 'Aucun challenge OTP actif.', 'warn');
        return;
    }

    try {
        const payload = await api('/login/resend-otp', {
            method: 'POST',
            json: {
                challenge_token: state.authChallenge.challengeToken,
            },
        });

        const otpInfo = payload.debug_otp ? ` Code dev: ${payload.debug_otp}` : '';
        setMessage(refs.authMsg, `Nouveau code OTP envoye.${otpInfo}`, 'ok');
    } catch (error) {
        setMessage(refs.authMsg, error.message, 'error');
    }
}

async function logout() {
    try {
        if (state.token) await api('/logout', { method: 'POST' });
    } catch {
        // ignore
    }

    state.token = '';
    state.user = null;
    state.screens = [];
    state.assignments = [];
    state.sportsHalls = [];
    state.adSchedules = [];
    state.media = [];
    state.programs = [];
    state.playlists = [];
    state.playlistItems = [];
    state.users = [];

    localStorage.removeItem('club_api_token');
    setTwoFactorMode(false);
    updateSessionUi();
    syncSharedSelectors();
    renderAllTables();
    setMessage(refs.authMsg, 'Session fermee.', 'ok');
    switchSpaView(false);
}
async function loadScreens() {
    try {
        const payload = await api('/screens');
        state.screens = extractRows(payload);
        syncSharedSelectors();

        const rows = state.screens.map((s) => ({
            cells: [
                s.id,
                s.name,
                s.emplacement ?? '-',
                s.sports_hall?.name ?? '-',
                s.sports_hall?.localisation ?? '-',
                s.device_key,
                s.status,
                s.playlist?.name ?? '-',
                `<div class="actions">
                    <button data-action="screen-player" data-id="${s.id}" class="icon-action play" title="Ouvrir player" aria-label="Ouvrir player">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><polygon points="8,5 19,12 8,19"></polygon></svg>
                    </button>
                    <button data-action="screen-choose" data-id="${s.id}">Choisir</button>
                    <button data-action="screen-edit" data-id="${s.id}" class="icon-action edit" title="Modifier ecran" aria-label="Modifier ecran">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
                    <button data-action="screen-delete" data-id="${s.id}" class="icon-action delete" title="Supprimer ecran" aria-label="Supprimer ecran">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><polyline points="3,6 5,6 21,6"></polyline><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </div>`,
            ],
        }));

        renderTable(refs.table.screens, ['ID', 'Nom', 'Emplacement', 'Salle sport', 'Localisation', 'Device key (auto)', 'Status', 'Playlist active', 'Action'], rows);
        setMessage(refs.msg.screens, `${state.screens.length} ecrans charges.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.screens, error.message, 'error');
    }
}

async function saveScreen() {
    const id = state.editing.screenId;
    const data = {
        name: document.getElementById('screen-name').value.trim(),
        emplacement: document.getElementById('screen-emplacement').value,
        sports_hall_id: Number(document.getElementById('screen-sports-hall-id').value),
        status: document.getElementById('screen-status').value,
    };

    try {
        if (id) await api(`/screens/${id}`, { method: 'PUT', json: data });
        else await api('/screens', { method: 'POST', json: data });
        setMessage(refs.msg.screens, id ? 'Ecran modifie.' : 'Ecran ajoute.', 'ok');
        resetForms();
        await loadScreens();
    } catch (error) {
        setMessage(refs.msg.screens, error.message, 'error');
    }
}

async function deleteScreen(id) {
    if (!confirm(`Supprimer l'ecran #${id} ?`)) return;

    try {
        await api(`/screens/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.screens, `Ecran #${id} supprime.`, 'ok');
        await loadScreens();
    } catch (error) {
        setMessage(refs.msg.screens, error.message, 'error');
    }
}

async function assignPlaylistToScreen() {
    const editingId = state.editing.assignmentId;
    const screenId = document.getElementById('assign-screen-id').value;
    const playlistId = document.getElementById('assign-playlist-id').value;
    const startsAt = toApiDateTime(document.getElementById('assign-start-at').value);
    const endsAt = toApiDateTime(document.getElementById('assign-end-at').value);

    if (!screenId || !playlistId) {
        setMessage(refs.msg.assignments, 'Selectionne un ecran et une playlist.', 'warn');
        return;
    }

    if (startsAt && endsAt && endsAt < startsAt) {
        setMessage(refs.msg.assignments, 'La date/heure de fin doit etre egale ou apres le debut.', 'warn');
        return;
    }

    try {
        if (editingId) {
            await api(`/screen-playlists/${editingId}`, {
                method: 'PUT',
                json: {
                    screen_id: Number(screenId),
                    playlist_id: Number(playlistId),
                    starts_at: startsAt,
                    ends_at: endsAt,
                    is_active: true,
                },
            });
            setMessage(refs.msg.assignments, `Affectation #${editingId} modifiee.`, 'ok');
        } else {
            await api(`/screens/${screenId}/assign-playlist`, {
                method: 'POST',
                json: {
                    playlist_id: Number(playlistId),
                    starts_at: startsAt,
                    ends_at: endsAt,
                    is_active: true,
                },
            });
            setMessage(refs.msg.assignments, 'Affectation creee avec succes.', 'ok');
        }

        state.editing.assignmentId = null;
        fillAssignmentDefaults();
        document.getElementById('btn-assign-playlist').textContent = 'Affecter la playlist';
        await Promise.all([loadScreens(), loadAssignments()]);
    } catch (error) {
        setMessage(refs.msg.assignments, error.message, 'error');
    }
}

function renderAssignmentsTable() {
    const search = document.getElementById('assignment-search').value.trim().toLowerCase();
    const filteredAssignments = search
        ? state.assignments.filter((assignment) => buildAssignmentSearchText(assignment).includes(search))
        : state.assignments;

    const rows = filteredAssignments.map((assignment) => {
        const runtimeStatus = normalizeAssignmentStatus(assignment);
        const statusLabel = runtimeStatus === 'expired' ? 'expired' : runtimeStatus;
        const isExpired = runtimeStatus === 'expired';

        return {
            cells: [
                assignment.id,
                assignment.screen?.name ?? '-',
                assignment.screen?.device_key ?? '-',
                assignment.playlist?.name ?? '-',
                formatDateTime(assignment.starts_at),
                formatDateTime(assignment.ends_at),
                statusLabel,
                `<div class="actions">
                    <button data-action="assignment-edit" data-id="${assignment.id}" class="icon-action edit" title="Modifier affectation" aria-label="Modifier affectation">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
                    <button data-action="assignment-stop" data-id="${assignment.id}" class="icon-action stop" title="Arreter affichage" aria-label="Arreter affichage" ${isExpired ? 'disabled' : ''}>
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="7" y="7" width="10" height="10" rx="1"></rect></svg>
                    </button>
                    <button data-action="assignment-delete" data-id="${assignment.id}" class="icon-action delete" title="Supprimer affectation" aria-label="Supprimer affectation">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><polyline points="3,6 5,6 21,6"></polyline><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </div>`,
            ],
        };
    });

    renderTable(refs.table.assignments, ['ID', 'Ecran', 'Device key', 'Playlist', 'Debut', 'Fin', 'Statut', 'Action'], rows);

    if (!state.assignments.length) {
        setMessage(refs.msg.assignments, 'Aucune affectation disponible.', 'warn');
        return;
    }

    setMessage(refs.msg.assignments, `${filteredAssignments.length} affectations affichees sur ${state.assignments.length}.`, 'ok');
}

async function loadAssignments() {
    try {
        const payload = await api('/screen-playlists?per_page=200');
        state.assignments = extractRows(payload);
        renderAssignmentsTable();
    } catch (error) {
        setMessage(refs.msg.assignments, error.message, 'error');
    }
}

async function deleteAssignment(id) {
    if (!confirm(`Supprimer l'affectation #${id} ?`)) return;

    try {
        await api(`/screen-playlists/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.assignments, `Affectation #${id} supprimee.`, 'ok');
        if (String(state.editing.assignmentId ?? '') === String(id)) {
            state.editing.assignmentId = null;
            document.getElementById('btn-assign-playlist').textContent = 'Affecter la playlist';
            fillAssignmentDefaults();
        }
        await Promise.all([loadScreens(), loadAssignments()]);
    } catch (error) {
        setMessage(refs.msg.assignments, error.message, 'error');
    }
}

async function stopAssignment(id) {
    if (!confirm(`Arreter l'affectation #${id} ?`)) return;

    try {
        await api(`/screen-playlists/${id}`, {
            method: 'PUT',
            json: {
                is_active: false,
                ends_at: toApiDateTime(toInputDateTimeParts(new Date())?.datetimeLocal ?? ''),
            },
        });
        setMessage(refs.msg.assignments, `Affectation #${id} arretee (expired).`, 'ok');
        await Promise.all([loadScreens(), loadAssignments()]);
    } catch (error) {
        setMessage(refs.msg.assignments, error.message, 'error');
    }
}

async function loadSportsHalls() {
    try {
        const payload = await api('/sports-halls');
        state.sportsHalls = extractRows(payload);
        syncSharedSelectors();

        const rows = state.sportsHalls.map((hall) => ({
            cells: [
                hall.id,
                hall.name,
                hall.matricule,
                hall.localisation,
                hall.screens_count ?? 0,
                `<div class="actions">
                    <button data-action="sports-hall-edit" data-id="${hall.id}" class="btn-warn">Modifier</button>
                    <button data-action="sports-hall-delete" data-id="${hall.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.sportsHalls, ['ID', 'Nom', 'Matricule', 'Localisation', 'Nb ecrans', 'Action'], rows);
        setMessage(refs.msg.sportsHalls, `${state.sportsHalls.length} salles chargees.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.sportsHalls, error.message, 'error');
    }
}

async function saveSportsHall() {
    const id = state.editing.sportsHallId;
    const data = {
        name: document.getElementById('sports-hall-name').value.trim(),
        matricule: document.getElementById('sports-hall-matricule').value.trim(),
        localisation: document.getElementById('sports-hall-localisation').value.trim(),
    };

    try {
        if (id) await api(`/sports-halls/${id}`, { method: 'PUT', json: data });
        else await api('/sports-halls', { method: 'POST', json: data });

        setMessage(refs.msg.sportsHalls, id ? 'Salle modifiee.' : 'Salle ajoutee.', 'ok');
        resetForms();
        await loadSportsHalls();
        await loadScreens();
    } catch (error) {
        setMessage(refs.msg.sportsHalls, error.message, 'error');
    }
}

async function deleteSportsHall(id) {
    if (!confirm(`Supprimer la salle #${id} ?`)) return;

    try {
        await api(`/sports-halls/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.sportsHalls, `Salle #${id} supprimee.`, 'ok');
        await loadSportsHalls();
        await loadScreens();
    } catch (error) {
        setMessage(refs.msg.sportsHalls, error.message, 'error');
    }
}

async function loadAdSchedules() {
    try {
        const payload = await api('/ad-schedules');
        state.adSchedules = extractRows(payload);
        syncSharedSelectors();

        const rows = state.adSchedules.map((ad) => ({
            cells: [
                ad.id,
                ad.name,
                ad.screen?.name ?? '-',
                ad.media?.title ?? '-',
                formatDateTime(ad.starts_at),
                formatDateTime(ad.ends_at),
                ad.display_every_loops ?? 1,
                ad.is_active ? 'active' : 'inactive',
                `<div class="actions">
                    <button data-action="ad-edit" data-id="${ad.id}" class="btn-warn">Modifier</button>
                    <button data-action="ad-delete" data-id="${ad.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.ads, ['ID', 'Campagne', 'Ecran', 'Media', 'Debut', 'Fin', 'Frequence loops', 'Etat', 'Action'], rows);
        setMessage(refs.msg.ads, `${state.adSchedules.length} publicites programmees.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.ads, error.message, 'error');
    }
}

async function saveAdSchedule() {
    const id = state.editing.adScheduleId;
    const data = {
        name: document.getElementById('ad-name').value.trim(),
        screen_id: Number(document.getElementById('ad-screen-id').value),
        media_id: Number(document.getElementById('ad-media-id').value),
        starts_at: toApiDateTime(document.getElementById('ad-starts-at').value),
        ends_at: toApiDateTime(document.getElementById('ad-ends-at').value),
        display_every_loops: Number(document.getElementById('ad-display-every-loops').value || 1),
        is_active: document.getElementById('ad-is-active').value === '1',
    };

    const durationOverride = document.getElementById('ad-duration-override').value;
    if (durationOverride) {
        data.duration_override = Number(durationOverride);
    }

    try {
        if (id) await api(`/ad-schedules/${id}`, { method: 'PUT', json: data });
        else await api('/ad-schedules', { method: 'POST', json: data });

        setMessage(refs.msg.ads, id ? 'Programmation pub modifiee.' : 'Publicite programmee.', 'ok');
        resetForms();
        await loadAdSchedules();
    } catch (error) {
        setMessage(refs.msg.ads, error.message, 'error');
    }
}

async function deleteAdSchedule(id) {
    if (!confirm(`Supprimer la programmation pub #${id} ?`)) return;

    try {
        await api(`/ad-schedules/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.ads, `Programmation pub #${id} supprimee.`, 'ok');
        await loadAdSchedules();
    } catch (error) {
        setMessage(refs.msg.ads, error.message, 'error');
    }
}

async function loadMedia() {
    try {
        const payload = await api('/media');
        state.media = extractRows(payload);
        syncSharedSelectors();

        const rows = state.media.map((m) => ({
            cells: [
                m.id,
                m.title,
                m.type,
                m.duration,
                formatDateTime(m.created_at),
                `<div class="actions">
                    <a href="${m.file_url}" target="_blank" rel="noreferrer">Voir</a>
                    <button data-action="media-edit" data-id="${m.id}" class="btn-warn">Modifier</button>
                    <button data-action="media-delete" data-id="${m.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.media, ['ID', 'Titre', 'Type', 'Duree', 'Date', 'Action'], rows);
        setMessage(refs.msg.media, `${state.media.length} medias charges.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.media, error.message, 'error');
    }
}

async function saveMedia() {
    const id = state.editing.mediaId;
    const title = document.getElementById('media-title').value.trim();
    const type = document.getElementById('media-type').value;
    const duration = document.getElementById('media-duration').value;
    const file = document.getElementById('media-file').files[0];

    if (!id && !file) {
        setMessage(refs.msg.media, 'Fichier requis pour creer un media.', 'warn');
        return;
    }

    try {
        if (file) {
            const formData = new FormData();
            formData.append('title', title || file.name);
            formData.append('type', type);
            if (duration !== '') formData.append('duration', duration);
            formData.append('file', file);

            if (id) {
                formData.append('_method', 'PUT');
                await api(`/media/${id}`, { method: 'POST', formData });
            } else {
                await api('/media', { method: 'POST', formData });
            }
        } else {
            const data = { title, type };
            if (duration !== '') data.duration = Number(duration);
            await api(`/media/${id}`, { method: 'PUT', json: data });
        }

        setMessage(refs.msg.media, id ? 'Media modifie.' : 'Media cree.', 'ok');
        resetForms();
        await loadMedia();
    } catch (error) {
        setMessage(refs.msg.media, error.message, 'error');
    }
}

async function deleteMedia(id) {
    if (!confirm(`Supprimer le media #${id} ?`)) return;

    try {
        await api(`/media/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.media, `Media #${id} supprime.`, 'ok');
        await loadMedia();
    } catch (error) {
        setMessage(refs.msg.media, error.message, 'error');
    }
}

async function loadPrograms() {
    try {
        const payload = await api('/programs');
        state.programs = extractRows(payload);

        const rows = state.programs.map((p) => ({
            cells: [
                p.id, p.day, p.start_time, p.end_time, p.coach, p.room,
                `<div class="actions">
                    <button data-action="program-edit" data-id="${p.id}" class="btn-warn">Modifier</button>
                    <button data-action="program-delete" data-id="${p.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.programs, ['ID', 'Day', 'Start', 'End', 'Coach', 'Room', 'Action'], rows);
        setMessage(refs.msg.programs, `${state.programs.length} programmes charges.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.programs, error.message, 'error');
    }
}

async function saveProgram() {
    const id = state.editing.programId;
    const data = {
        day: document.getElementById('program-day').value.trim(),
        start_time: document.getElementById('program-start-time').value,
        end_time: document.getElementById('program-end-time').value,
        coach: document.getElementById('program-coach').value.trim(),
        room: document.getElementById('program-room').value.trim(),
    };

    try {
        if (id) await api(`/programs/${id}`, { method: 'PUT', json: data });
        else await api('/programs', { method: 'POST', json: data });
        setMessage(refs.msg.programs, id ? 'Programme modifie.' : 'Programme ajoute.', 'ok');
        resetForms();
        await loadPrograms();
    } catch (error) {
        setMessage(refs.msg.programs, error.message, 'error');
    }
}

async function deleteProgram(id) {
    if (!confirm(`Supprimer le programme #${id} ?`)) return;

    try {
        await api(`/programs/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.programs, `Programme #${id} supprime.`, 'ok');
        await loadPrograms();
    } catch (error) {
        setMessage(refs.msg.programs, error.message, 'error');
    }
}
async function loadPlaylists() {
    try {
        const payload = await api('/playlists');
        state.playlists = extractRows(payload);
        syncSharedSelectors();

        const rows = state.playlists.map((p) => ({
            cells: [
                p.id, p.name, p.items_count ?? '-',
                `<div class="actions">
                    <button data-action="playlist-items" data-id="${p.id}" class="btn-ok">Items</button>
                    <button data-action="playlist-edit" data-id="${p.id}" class="btn-warn">Modifier</button>
                    <button data-action="playlist-delete" data-id="${p.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.playlists, ['ID', 'Nom', 'Nb items', 'Action'], rows);
        setMessage(refs.msg.playlists, `${state.playlists.length} playlists chargees.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.playlists, error.message, 'error');
    }
}

async function savePlaylist() {
    const id = state.editing.playlistId;
    const data = { name: document.getElementById('playlist-name').value.trim() };

    try {
        if (id) await api(`/playlists/${id}`, { method: 'PUT', json: data });
        else await api('/playlists', { method: 'POST', json: data });
        setMessage(refs.msg.playlists, id ? 'Playlist modifiee.' : 'Playlist creee.', 'ok');
        resetForms();
        await loadPlaylists();
    } catch (error) {
        setMessage(refs.msg.playlists, error.message, 'error');
    }
}

async function deletePlaylist(id) {
    if (!confirm(`Supprimer la playlist #${id} ?`)) return;

    try {
        await api(`/playlists/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.playlists, `Playlist #${id} supprimee.`, 'ok');
        await loadPlaylists();
    } catch (error) {
        setMessage(refs.msg.playlists, error.message, 'error');
    }
}

function renderItemsTable() {
    const rows = state.playlistItems.map((i) => ({
        attrs: `draggable="true" data-item-id="${i.id}"`,
        cells: [
            '<span class="drag-handle">::</span>',
            i.id,
            i.order,
            i.media_id,
            i.media?.title ?? '-',
            i.media?.type ?? '-',
            i.duration_override ?? '-',
            `<div class="actions">
                <button data-action="item-edit" data-id="${i.id}" class="btn-warn">Modifier</button>
                <button data-action="item-delete" data-id="${i.id}" class="btn-danger">Supprimer</button>
            </div>`,
        ],
    }));

    renderTable(refs.table.items, ['Drag', 'ID', 'Order', 'Media ID', 'Media title', 'Type', 'Duration override', 'Action'], rows);
}

async function loadPlaylistItems() {
    const playlistId = document.getElementById('items-playlist-filter').value;

    if (!playlistId) {
        state.playlistItems = [];
        renderItemsTable();
        setMessage(refs.msg.items, 'Selectionne une playlist.', 'warn');
        return;
    }

    try {
        const payload = await api(`/playlists/${playlistId}`);
        const items = Array.isArray(payload.data?.items) ? payload.data.items : [];
        state.playlistItems = items.sort((a, b) => a.order - b.order).map((item, idx) => ({ ...item, order: idx + 1 }));
        renderItemsTable();
        setMessage(refs.msg.items, `${state.playlistItems.length} items charges.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.items, error.message, 'error');
    }
}

async function saveItem() {
    const playlistId = document.getElementById('items-playlist-filter').value;
    if (!playlistId) {
        setMessage(refs.msg.items, 'Selectionne une playlist.', 'warn');
        return;
    }

    const id = state.editing.itemId;
    const mediaId = document.getElementById('item-media-id').value;
    const order = document.getElementById('item-order').value;
    const durationOverride = document.getElementById('item-duration-override').value;

    if (!id && !mediaId) {
        setMessage(refs.msg.items, 'Selectionne un media.', 'warn');
        return;
    }

    const data = {};
    if (!id) data.playlist_id = Number(playlistId);
    if (mediaId) data.media_id = Number(mediaId);
    if (order) data.order = Number(order);
    if (durationOverride) data.duration_override = Number(durationOverride);

    try {
        if (id) await api(`/playlist-items/${id}`, { method: 'PUT', json: data });
        else await api('/playlist-items', { method: 'POST', json: data });

        setMessage(refs.msg.items, id ? 'Item modifie.' : 'Item ajoute.', 'ok');
        resetForms();
        await loadPlaylistItems();
        await loadPlaylists();
    } catch (error) {
        setMessage(refs.msg.items, error.message, 'error');
    }
}

async function deleteItem(id) {
    if (!confirm(`Supprimer item #${id} ?`)) return;

    try {
        await api(`/playlist-items/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.items, `Item #${id} supprime.`, 'ok');
        await loadPlaylistItems();
        await loadPlaylists();
    } catch (error) {
        setMessage(refs.msg.items, error.message, 'error');
    }
}

async function saveItemOrder() {
    const playlistId = document.getElementById('items-playlist-filter').value;

    if (!playlistId || !state.playlistItems.length) {
        setMessage(refs.msg.items, 'Rien a sauvegarder.', 'warn');
        return;
    }

    try {
        await api(`/playlists/${playlistId}/items/reorder`, {
            method: 'PUT',
            json: {
                items: state.playlistItems.map((item, idx) => ({ id: item.id, order: idx + 1 })),
            },
        });
        setMessage(refs.msg.items, 'Ordre sauvegarde.', 'ok');
        await loadPlaylistItems();
    } catch (error) {
        setMessage(refs.msg.items, error.message, 'error');
    }
}

async function loadUsers() {
    if (state.user?.role !== 'admin') {
        state.users = [];
        renderTable(refs.table.users, ['Info'], [{ cells: ['Acces reserve admin'] }]);
        setMessage(refs.msg.users, 'Module users reserve admin.', 'warn');
        return;
    }

    try {
        const payload = await api('/users');
        state.users = extractRows(payload);

        const rows = state.users.map((u) => ({
            cells: [
                u.id, u.name, u.email, u.role,
                `<div class="actions">
                    <button data-action="user-edit" data-id="${u.id}" class="btn-warn">Modifier</button>
                    <button data-action="user-delete" data-id="${u.id}" class="btn-danger">Supprimer</button>
                </div>`,
            ],
        }));

        renderTable(refs.table.users, ['ID', 'Nom', 'Email', 'Role', 'Action'], rows);
        setMessage(refs.msg.users, `${state.users.length} utilisateurs charges.`, 'ok');
    } catch (error) {
        setMessage(refs.msg.users, error.message, 'error');
    }
}

async function saveUser() {
    if (state.user?.role !== 'admin') {
        setMessage(refs.msg.users, 'Action reservee admin.', 'warn');
        return;
    }

    const id = state.editing.userId;
    const password = document.getElementById('user-password').value;
    const data = {
        name: document.getElementById('user-name').value.trim(),
        email: document.getElementById('user-email').value.trim(),
        role: document.getElementById('user-role').value,
    };

    if (password) data.password = password;
    if (!id && !password) {
        setMessage(refs.msg.users, 'Mot de passe requis en creation.', 'warn');
        return;
    }

    try {
        if (id) await api(`/users/${id}`, { method: 'PUT', json: data });
        else await api('/users', { method: 'POST', json: data });

        setMessage(refs.msg.users, id ? 'Utilisateur modifie.' : 'Utilisateur ajoute.', 'ok');
        resetForms();
        await loadUsers();
    } catch (error) {
        setMessage(refs.msg.users, error.message, 'error');
    }
}

async function deleteUser(id) {
    if (!confirm(`Supprimer user #${id} ?`)) return;

    try {
        await api(`/users/${id}`, { method: 'DELETE' });
        setMessage(refs.msg.users, `User #${id} supprime.`, 'ok');
        await loadUsers();
    } catch (error) {
        setMessage(refs.msg.users, error.message, 'error');
    }
}

async function refreshAll() {
    if (!state.token) return;
    await Promise.allSettled([loadSportsHalls(), loadScreens(), loadAssignments(), loadAdSchedules(), loadMedia(), loadPrograms(), loadPlaylists(), loadUsers()]);
}
function reorderItemsByDrag(fromId, toId) {
    const fromIndex = state.playlistItems.findIndex((i) => String(i.id) === String(fromId));
    const toIndex = state.playlistItems.findIndex((i) => String(i.id) === String(toId));
    if (fromIndex < 0 || toIndex < 0 || fromIndex === toIndex) return;

    const [moved] = state.playlistItems.splice(fromIndex, 1);
    state.playlistItems.splice(toIndex, 0, moved);
    state.playlistItems = state.playlistItems.map((item, idx) => ({ ...item, order: idx + 1 }));
    renderItemsTable();
    setMessage(refs.msg.items, 'Ordre local modifie. Clique sur "Sauvegarder ordre".', 'warn');
}

function bindItemDragEvents() {
    refs.table.items.addEventListener('dragstart', (event) => {
        const row = event.target.closest('tr[data-item-id]');
        if (!row) return;
        state.draggingItemId = row.dataset.itemId;
        row.classList.add('dragging');
        event.dataTransfer.effectAllowed = 'move';
    });

    refs.table.items.addEventListener('dragend', (event) => {
        const row = event.target.closest('tr[data-item-id]');
        if (row) row.classList.remove('dragging');
        state.draggingItemId = null;
        refs.table.items.querySelectorAll('tr.drop-target').forEach((tr) => tr.classList.remove('drop-target'));
    });

    refs.table.items.addEventListener('dragover', (event) => {
        const row = event.target.closest('tr[data-item-id]');
        if (!row) return;
        event.preventDefault();
        refs.table.items.querySelectorAll('tr.drop-target').forEach((tr) => tr.classList.remove('drop-target'));
        row.classList.add('drop-target');
    });

    refs.table.items.addEventListener('drop', (event) => {
        const row = event.target.closest('tr[data-item-id]');
        if (!row || !state.draggingItemId) return;
        event.preventDefault();
        reorderItemsByDrag(state.draggingItemId, row.dataset.itemId);
        refs.table.items.querySelectorAll('tr.drop-target').forEach((tr) => tr.classList.remove('drop-target'));
    });
}

function bindEvents() {
    document.querySelectorAll('.tab-btn').forEach((btn) => btn.addEventListener('click', () => showPanel(btn.dataset.tab)));

    document.getElementById('btn-login').addEventListener('click', login);
    document.getElementById('btn-verify-otp').addEventListener('click', verifyOtp);
    document.getElementById('btn-resend-otp').addEventListener('click', resendOtp);
    document.getElementById('auth-password').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') login();
    });
    document.getElementById('auth-otp').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') verifyOtp();
    });
    document.getElementById('btn-logout').addEventListener('click', logout);

    document.getElementById('btn-save-screen').addEventListener('click', saveScreen);
    document.getElementById('btn-cancel-screen').addEventListener('click', resetForms);
    document.getElementById('btn-assign-playlist').addEventListener('click', assignPlaylistToScreen);
    document.getElementById('assign-screen-id').addEventListener('change', loadAssignments);

    document.getElementById('btn-save-sports-hall').addEventListener('click', saveSportsHall);
    document.getElementById('btn-cancel-sports-hall').addEventListener('click', resetForms);

    document.getElementById('btn-save-ad').addEventListener('click', saveAdSchedule);
    document.getElementById('btn-cancel-ad').addEventListener('click', resetForms);

    document.getElementById('btn-save-media').addEventListener('click', saveMedia);
    document.getElementById('btn-cancel-media').addEventListener('click', resetForms);

    document.getElementById('btn-save-program').addEventListener('click', saveProgram);
    document.getElementById('btn-cancel-program').addEventListener('click', resetForms);

    document.getElementById('btn-save-playlist').addEventListener('click', savePlaylist);
    document.getElementById('btn-cancel-playlist').addEventListener('click', resetForms);

    document.getElementById('btn-load-items').addEventListener('click', loadPlaylistItems);
    document.getElementById('btn-save-item').addEventListener('click', saveItem);
    document.getElementById('btn-cancel-item').addEventListener('click', resetForms);
    document.getElementById('btn-save-order').addEventListener('click', saveItemOrder);

    document.getElementById('btn-save-user').addEventListener('click', saveUser);
    document.getElementById('btn-cancel-user').addEventListener('click', resetForms);

    refs.table.screens.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.screens.find((s) => String(s.id) === String(id));

        if (action === 'screen-player' && record) window.open(`/player/${encodeURIComponent(record.device_key)}`, '_blank');
        if (action === 'screen-choose') {
            document.getElementById('assign-screen-id').value = id;
            loadAssignments();
        }
        if (action === 'screen-delete') deleteScreen(id);
        if (action === 'screen-edit' && record) {
            state.editing.screenId = record.id;
            document.getElementById('screen-name').value = record.name;
            document.getElementById('screen-emplacement').value = record.emplacement ?? '';
            document.getElementById('screen-sports-hall-id').value = String(record.sports_hall_id ?? '');
            document.getElementById('screen-status').value = record.status;
            document.getElementById('btn-save-screen').textContent = `Enregistrer ecran #${record.id}`;
            setMessage(refs.msg.screens, `Edition ecran #${record.id}.`, 'warn');
        }
    });

    refs.table.assignments.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.assignments.find((item) => String(item.id) === String(id));

        if (action === 'assignment-delete') {
            deleteAssignment(id);
            return;
        }

        if (action === 'assignment-use' && record) {
            document.getElementById('assign-screen-id').value = String(record.screen_id);
            document.getElementById('assign-playlist-id').value = String(record.playlist_id);

            const startParts = toInputDateTimeParts(record.starts_at);
            const endParts = toInputDateTimeParts(record.ends_at);
            document.getElementById('assign-start-at').value = startParts?.datetimeLocal ?? '';
            document.getElementById('assign-end-at').value = endParts?.datetimeLocal ?? '';

            setMessage(refs.msg.assignments, `Affectation #${record.id} chargee dans le formulaire.`, 'ok');
            showPanel('assignments');
        }
    });

    refs.table.sportsHalls.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.sportsHalls.find((hall) => String(hall.id) === String(id));

        if (action === 'sports-hall-delete') {
            deleteSportsHall(id);
            return;
        }

        if (action === 'sports-hall-edit' && record) {
            state.editing.sportsHallId = record.id;
            document.getElementById('sports-hall-name').value = record.name;
            document.getElementById('sports-hall-matricule').value = record.matricule;
            document.getElementById('sports-hall-localisation').value = record.localisation;
            document.getElementById('btn-save-sports-hall').textContent = `Enregistrer salle #${record.id}`;
            setMessage(refs.msg.sportsHalls, `Edition salle #${record.id}.`, 'warn');
        }
    });

    refs.table.ads.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.adSchedules.find((ad) => String(ad.id) === String(id));

        if (action === 'ad-delete') {
            deleteAdSchedule(id);
            return;
        }

        if (action === 'ad-edit' && record) {
            state.editing.adScheduleId = record.id;
            document.getElementById('ad-name').value = record.name;
            document.getElementById('ad-screen-id').value = String(record.screen_id);
            document.getElementById('ad-media-id').value = String(record.media_id);
            document.getElementById('ad-starts-at').value = toInputDateTimeParts(record.starts_at)?.datetimeLocal ?? '';
            document.getElementById('ad-ends-at').value = toInputDateTimeParts(record.ends_at)?.datetimeLocal ?? '';
            document.getElementById('ad-duration-override').value = record.duration_override ?? '';
            document.getElementById('ad-display-every-loops').value = String(record.display_every_loops ?? 1);
            document.getElementById('ad-is-active').value = record.is_active ? '1' : '0';
            document.getElementById('btn-save-ad').textContent = `Enregistrer publicite #${record.id}`;
            setMessage(refs.msg.ads, `Edition publicite #${record.id}.`, 'warn');
        }
    });

    refs.table.media.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.media.find((m) => String(m.id) === String(id));

        if (action === 'media-delete') deleteMedia(id);
        if (action === 'media-edit' && record) {
            state.editing.mediaId = record.id;
            document.getElementById('media-title').value = record.title;
            document.getElementById('media-type').value = record.type;
            document.getElementById('media-duration').value = record.duration;
            document.getElementById('media-file').value = '';
            document.getElementById('btn-save-media').textContent = `Enregistrer media #${record.id}`;
            setMessage(refs.msg.media, `Edition media #${record.id}.`, 'warn');
        }
    });

    refs.table.programs.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.programs.find((p) => String(p.id) === String(id));

        if (action === 'program-delete') deleteProgram(id);
        if (action === 'program-edit' && record) {
            state.editing.programId = record.id;
            document.getElementById('program-day').value = record.day;
            document.getElementById('program-start-time').value = record.start_time;
            document.getElementById('program-end-time').value = record.end_time;
            document.getElementById('program-coach').value = record.coach;
            document.getElementById('program-room').value = record.room;
            document.getElementById('btn-save-program').textContent = `Enregistrer programme #${record.id}`;
            setMessage(refs.msg.programs, `Edition programme #${record.id}.`, 'warn');
        }
    });

    refs.table.playlists.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.playlists.find((p) => String(p.id) === String(id));

        if (action === 'playlist-delete') deletePlaylist(id);
        if (action === 'playlist-items') {
            document.getElementById('items-playlist-filter').value = id;
            showPanel('items');
            loadPlaylistItems();
        }
        if (action === 'playlist-edit' && record) {
            state.editing.playlistId = record.id;
            document.getElementById('playlist-name').value = record.name;
            document.getElementById('btn-save-playlist').textContent = `Enregistrer playlist #${record.id}`;
            setMessage(refs.msg.playlists, `Edition playlist #${record.id}.`, 'warn');
        }
    });

    refs.table.items.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.playlistItems.find((i) => String(i.id) === String(id));

        if (action === 'item-delete') deleteItem(id);
        if (action === 'item-edit' && record) {
            state.editing.itemId = record.id;
            document.getElementById('item-media-id').value = String(record.media_id);
            document.getElementById('item-order').value = record.order;
            document.getElementById('item-duration-override').value = record.duration_override ?? '';
            document.getElementById('btn-save-item').textContent = `Enregistrer item #${record.id}`;
            setMessage(refs.msg.items, `Edition item #${record.id}.`, 'warn');
        }
    });

    refs.table.users.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const record = state.users.find((u) => String(u.id) === String(id));

        if (action === 'user-delete') deleteUser(id);
        if (action === 'user-edit' && record) {
            state.editing.userId = record.id;
            document.getElementById('user-name').value = record.name;
            document.getElementById('user-email').value = record.email;
            document.getElementById('user-password').value = '';
            document.getElementById('user-role').value = record.role;
            document.getElementById('btn-save-user').textContent = `Enregistrer user #${record.id}`;
            setMessage(refs.msg.users, `Edition user #${record.id}.`, 'warn');
        }
    });

    bindItemDragEvents();
}

async function boot() {
    bindEvents();
    setTwoFactorMode(false);
    resetForms();
    renderAllTables();
    const authenticated = await loadMe();
    switchSpaView(authenticated);

    if (!authenticated) {
        setMessage(refs.authMsg, 'Connecte-toi pour acceder au dashboard.', 'warn');
        return;
    }

    setMessage(refs.globalMsg, 'Session active.', 'ok');
    fillAssignmentDefaults();
    document.getElementById('btn-assign-playlist').textContent = 'Affecter la playlist';
    await refreshAll();
}

boot();
</script>
</body>
</html>


































