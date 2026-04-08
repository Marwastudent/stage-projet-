<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sports Club Display</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-a: #051018;
            --bg-b: #10364a;
            --bg-c: #0f5c63;
            --panel: rgba(8, 20, 30, 0.78);
            --line: rgba(255, 255, 255, 0.15);
            --txt: #eaf6ff;
            --soft: #9ec0d2;
            --ok: #37d9b2;
            --err: #ff6b6b;
            --main: #ffb100;
            --font-body: "Manrope", "Trebuchet MS", sans-serif;
            --font-title: "Rajdhani", "Franklin Gothic Medium", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: var(--font-body);
            color: var(--txt);
            background:
                radial-gradient(circle at 14% 12%, rgba(255, 177, 0, 0.26), transparent 34%),
                radial-gradient(circle at 82% 86%, rgba(55, 217, 178, 0.20), transparent 36%),
                linear-gradient(145deg, var(--bg-a), var(--bg-b) 56%, var(--bg-c));
            padding: 14px;
        }

        .card {
            width: min(460px, 94vw);
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--panel);
            padding: 18px;
            backdrop-filter: blur(8px);
        }

        h1 {
            margin: 0;
            font-size: 1.55rem;
            font-family: var(--font-title);
            letter-spacing: 0.05em;
        }

        p {
            margin: 8px 0 0;
            color: var(--soft);
        }

        .stack {
            display: grid;
            gap: 9px;
            margin-top: 14px;
        }

        input,
        button {
            width: 100%;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #081726;
            color: var(--txt);
            padding: 10px;
            font: 500 0.94rem var(--font-body);
        }

        button {
            cursor: pointer;
            background: rgba(255, 177, 0, 0.26);
            border-color: rgba(255, 177, 0, 0.62);
        }

        .secondary {
            background: rgba(55, 217, 178, 0.20);
            border-color: rgba(55, 217, 178, 0.58);
        }

        .msg {
            min-height: 20px;
            margin-top: 8px;
            color: var(--soft);
            font-size: 0.9rem;
        }

        .msg.ok {
            color: var(--ok);
        }

        .msg.error {
            color: var(--err);
        }
    </style>
</head>
<body>
<section class="card">
    <h1>Connexion Dashboard</h1>
    <p>Authentification separee du dashboard.</p>

    <div class="stack">
        <input id="email" type="email" value="admin@club.local" placeholder="Email">
        <input id="password" type="password" value="password123" placeholder="Mot de passe">
        <input id="device-name" type="text" value="dashboard-web" placeholder="Nom du poste">
        <button id="btn-login">Se connecter</button>
        <button id="btn-player" class="secondary">Ouvrir un player</button>
    </div>

    <p id="msg" class="msg"></p>
</section>

<script>
    const msg = document.getElementById('msg');

    function setMessage(text, mode = '') {
        msg.textContent = text || '';
        msg.classList.remove('ok', 'error');
        if (mode) msg.classList.add(mode);
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
        }

        if (options.token) {
            headers.Authorization = `Bearer ${options.token}`;
        }

        const response = await fetch(`/api${path}`, { method, headers, body });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(payload.message || firstValidationError(payload) || `Erreur API ${response.status}`);
        }

        return payload;
    }

    async function checkExistingToken() {
        const notice = sessionStorage.getItem('admin_login_notice');
        if (notice) {
            setMessage(notice, 'ok');
            sessionStorage.removeItem('admin_login_notice');
        }

        const token = localStorage.getItem('club_api_token');
        if (!token) return;

        try {
            await api('/me', { token });
            window.location.href = '/admin/dashboard';
        } catch {
            localStorage.removeItem('club_api_token');
        }
    }

    async function login() {
        try {
            const payload = await api('/login', {
                method: 'POST',
                json: {
                    email: document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value,
                    device_name: document.getElementById('device-name').value.trim() || 'dashboard-web',
                },
            });

            localStorage.setItem('club_api_token', payload.token);
            setMessage('Connexion reussie. Redirection...', 'ok');
            setTimeout(() => {
                window.location.href = '/admin/dashboard';
            }, 350);
        } catch (error) {
            setMessage(error.message, 'error');
        }
    }

    function openPlayer() {
        const key = prompt('Device key a ouvrir (ex: mmmmm):', 'mmmmm');
        if (!key) return;
        window.open(`/player/${encodeURIComponent(key)}`, '_blank');
    }

    document.getElementById('btn-login').addEventListener('click', login);
    document.getElementById('btn-player').addEventListener('click', openPlayer);

    checkExistingToken();
</script>
</body>
</html>
