<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Club Display</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #051018;
            --bg-2: #10364a;
            --bg-3: #0f5c63;
            --card: rgba(7, 20, 30, 0.72);
            --line: rgba(255, 255, 255, 0.18);
            --txt: #eaf6ff;
            --soft: #9ec0d2;
            --main: #ffb100;
            --ok: #37d9b2;
            --font-body: "Manrope", "Trebuchet MS", sans-serif;
            --font-title: "Rajdhani", "Franklin Gothic Medium", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--txt);
            font-family: var(--font-body);
            background:
                radial-gradient(circle at 12% 16%, rgba(255, 177, 0, 0.26), transparent 35%),
                radial-gradient(circle at 84% 78%, rgba(55, 217, 178, 0.23), transparent 38%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 55%, var(--bg-3));
        }

        .noise {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.08) 0.7px, transparent 0.7px);
            background-size: 3px 3px;
            opacity: 0.16;
        }

        .page {
            width: min(1120px, 94vw);
            margin: 28px auto;
            display: grid;
            gap: 14px;
        }

        .hero,
        .card {
            border: 1px solid var(--line);
            background: var(--card);
            border-radius: 16px;
            backdrop-filter: blur(8px);
        }

        .hero {
            padding: 22px;
        }

        .hero h1 {
            margin: 0;
            font-family: var(--font-title);
            letter-spacing: 0.05em;
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1;
        }

        .hero p {
            margin: 12px 0 0;
            color: var(--soft);
            max-width: 760px;
            font-size: 1.02rem;
        }

        .grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .card {
            padding: 16px;
        }

        .card h2 {
            margin: 0;
            font-family: var(--font-title);
            letter-spacing: 0.04em;
            font-size: 1.45rem;
        }

        .card p {
            margin: 8px 0 0;
            color: var(--soft);
            min-height: 58px;
        }

        .btn {
            margin-top: 12px;
            display: inline-block;
            padding: 9px 13px;
            border-radius: 10px;
            border: 1px solid var(--line);
            color: var(--txt);
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.16s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 177, 0, 0.68);
            background: rgba(255, 177, 0, 0.16);
        }

        .btn.alt:hover {
            border-color: rgba(55, 217, 178, 0.65);
            background: rgba(55, 217, 178, 0.16);
        }

        .footer {
            margin-top: 4px;
            color: var(--soft);
            font-size: 0.9rem;
            text-align: center;
        }

        @media (max-width: 920px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="noise"></div>

<div class="page">
    <section class="hero">
        <h1>SPORTS CLUB DISPLAY</h1>
        <p>Plateforme centralisee pour gerer, planifier et diffuser vos contenus multimedia sur ecrans dans les clubs sportifs.</p>
    </section>

    <section class="grid">
        <article class="card">
            <h2>Connexion Admin</h2>
            <p>Acces direct avec le profil administrateur pour la gestion globale de la plateforme.</p>
            <a class="btn" href="/admin">Ouvrir espace admin</a>
        </article>

        <article class="card">
            <h2>Connexion Manager</h2>
            <p>Acces direct avec le profil manager pour gerer ecrans, contenus et planifications.</p>
            <a class="btn alt" href="/admin">Ouvrir espace manager</a>
        </article>
    </section>

    <p class="footer">Sports Club Display - API REST Laravel + Player Web</p>
</div>
</body>
</html>


