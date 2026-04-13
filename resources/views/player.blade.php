<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player - {{ $deviceKey }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #040404;
            --bg-mid: #0b0b0b;
            --bg-soft: #17130a;
            --text: #f8f7f2;
            --badge: rgba(7, 7, 7, 0.78);
            --accent: #a57e1c;
            --font-main: "Manrope", "Trebuchet MS", sans-serif;
            --font-title: "Rajdhani", "Franklin Gothic Medium", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(165, 126, 28, 0.30), transparent 36%),
                radial-gradient(circle at 84% 80%, rgba(255, 255, 255, 0.10), transparent 40%),
                linear-gradient(160deg, var(--bg-dark) 0%, var(--bg-mid) 55%, var(--bg-soft) 100%);
            color: var(--text);
            font-family: var(--font-main);
        }

        #player-root {
            position: fixed;
            inset: 0;
        }

        #media-stage {
            position: relative;
            width: 100%;
            height: 100%;
            background: #000;
        }

        #media-stage.planning-mode {
            background:
                radial-gradient(circle at 12% 18%, rgba(165, 126, 28, 0.22), transparent 36%),
                radial-gradient(circle at 84% 80%, rgba(255, 255, 255, 0.08), transparent 40%),
                linear-gradient(160deg, var(--bg-dark) 0%, var(--bg-mid) 55%, var(--bg-soft) 100%);
            overflow: hidden;
        }

        .slide {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateX(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
            background: #000;
        }

        .slide.active {
            opacity: 1;
        }

        .slide.enter-slide {
            transform: translateX(30px);
        }

        .slide.active.enter-slide {
            transform: translateX(0);
        }

        .slide img,
        .slide video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }

        #status {
            position: absolute;
            left: 16px;
            bottom: 16px;
            z-index: 20;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--badge);
            border: 1px solid rgba(165, 126, 28, 0.42);
            color: var(--text);
            font-size: 13px;
            letter-spacing: 0.04em;
            backdrop-filter: blur(6px);
        }

        #empty-state {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            color: rgba(242, 213, 131, 0.92);
            text-align: center;
            padding: 24px;
            background: linear-gradient(180deg, rgba(7, 7, 7, 0.84), rgba(23, 19, 10, 0.72));
        }

        #empty-state h1 {
            margin: 0;
            font-size: clamp(1.2rem, 2vw, 2rem);
            font-weight: 700;
            font-family: var(--font-title);
            letter-spacing: 0.05em;
            color: #ffffff;
        }

        #empty-state p {
            margin: 0;
            color: rgba(242, 213, 131, 0.84);
            font-size: clamp(0.95rem, 1.2vw, 1.2rem);
        }

        .premium-planning-screen {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #080808;
        }

        .premium-bg-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.82), rgba(0, 0, 0, 0.92)),
                url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            z-index: 0;
        }

        .premium-layout {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            height: 100%;
        }

        .premium-side-info {
            width: 30%;
            min-width: 340px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 28px;
            border-right: 2px solid #a57e1c;
            background: rgba(0, 0, 0, 0.72);
            backdrop-filter: blur(15px);
            text-align: center;
        }

        .premium-screen-label {
            margin-bottom: 18px;
            color: rgba(242, 213, 131, 0.92);
            font-size: 1rem;
            letter-spacing: 0.28em;
            text-transform: uppercase;
        }

        .premium-clock {
            margin: 0;
            font-family: "Bebas Neue", cursive;
            font-size: clamp(5rem, 8vw, 7rem);
            line-height: 1;
            color: #f2d583;
        }

        .premium-date-display {
            margin-top: 15px;
            color: #eeeeee;
            font-family: "Montserrat", sans-serif;
            font-size: clamp(1rem, 1.5vw, 1.35rem);
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .premium-day-badge {
            margin-top: 24px;
            padding: 10px 18px;
            border: 1px solid rgba(242, 213, 131, 0.35);
            border-radius: 999px;
            color: #f2d583;
            font-family: "Montserrat", sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.04);
        }

        .premium-scroll-shell {
            width: 70%;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.18);
        }

        .premium-scroll-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            padding: 28px 40px 18px;
            border-bottom: 1px solid rgba(165, 126, 28, 0.28);
            background: rgba(0, 0, 0, 0.32);
            backdrop-filter: blur(8px);
        }

        .premium-scroll-header h1 {
            margin: 6px 0 0;
            color: #ffffff;
            font-family: "Montserrat", sans-serif;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
        }

        .premium-eyebrow {
            margin: 0;
            color: #f2d583;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        .premium-header-meta {
            display: grid;
            gap: 8px;
            justify-items: end;
            color: #dddddd;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .premium-scroll-container {
            position: relative;
            flex: 1;
            overflow: hidden;
        }

        .premium-scroll-content {
            position: absolute;
            width: 100%;
            animation: premium-scroll-up 60s linear infinite;
        }

        .premium-scroll-content.is-static {
            position: relative;
            animation: none;
        }

        .premium-row {
            min-height: 120px;
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 0 40px;
            border-bottom: 1px solid rgba(165, 126, 28, 0.2);
            background: rgba(0, 0, 0, 0.12);
        }

        .premium-row-time {
            width: 190px;
            color: #f2d583;
            font-family: "Bebas Neue", cursive;
            font-size: clamp(2.8rem, 3.5vw, 4rem);
            line-height: 1;
        }

        .premium-row-details {
            flex: 1;
            min-width: 0;
        }

        .premium-course-name {
            margin: 0;
            color: #ffffff;
            font-family: "Montserrat", sans-serif;
            font-size: clamp(1.6rem, 2.2vw, 2.3rem);
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .premium-row-meta {
            margin-top: 10px;
            color: #cccccc;
            font-family: "Montserrat", sans-serif;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .premium-row-room {
            width: 190px;
            color: #a57e1c;
            font-family: "Montserrat", sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            text-align: right;
            text-transform: uppercase;
        }

        .premium-row-empty .premium-row-details,
        .premium-row-empty .premium-row-room,
        .premium-row-empty .premium-row-time {
            color: rgba(242, 213, 131, 0.82);
        }

        @keyframes premium-scroll-up {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        .planning-screen {
            min-height: 100%;
            padding: 28px;
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 18px;
        }

        .planning-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 22px;
            border: 1px solid rgba(165, 126, 28, 0.38);
            border-radius: 22px;
            background: rgba(7, 7, 7, 0.74);
            backdrop-filter: blur(10px);
        }

        .planning-header h1 {
            margin: 0;
            font-family: var(--font-title);
            font-size: clamp(2rem, 3vw, 3.1rem);
            line-height: 1;
            letter-spacing: 0.05em;
        }

        .planning-header p {
            margin: 8px 0 0;
            color: rgba(242, 213, 131, 0.88);
            font-size: 1rem;
        }

        .planning-meta {
            text-align: right;
            color: rgba(248, 247, 242, 0.92);
            font-size: 0.98rem;
            line-height: 1.55;
        }

        .planning-meta strong {
            display: block;
            color: var(--soft);
            font-family: var(--font-title);
            font-size: 1.05rem;
            letter-spacing: 0.05em;
        }

        .planning-grid {
            display: grid;
            grid-template-columns: minmax(320px, 1fr);
            gap: 14px;
            align-items: start;
            max-width: 960px;
            width: 100%;
            justify-self: center;
        }

        .planning-column {
            min-height: calc(100vh - 230px);
            border: 1px solid rgba(165, 126, 28, 0.32);
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(165, 126, 28, 0.16), rgba(7, 7, 7, 0.88) 20%),
                rgba(7, 7, 7, 0.88);
            padding: 16px;
            display: grid;
            align-content: start;
            gap: 12px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }

        .planning-column.current-day {
            border-color: rgba(242, 213, 131, 0.82);
            box-shadow: 0 0 0 1px rgba(242, 213, 131, 0.3), 0 18px 42px rgba(0, 0, 0, 0.24);
        }

        .planning-column h2 {
            margin: 0;
            font-family: var(--font-title);
            font-size: 1.8rem;
            letter-spacing: 0.04em;
        }

        .planning-column .count {
            color: rgba(242, 213, 131, 0.9);
            font-size: 1rem;
        }

        .planning-items {
            display: grid;
            gap: 12px;
        }

        .planning-card {
            border: 1px solid rgba(242, 213, 131, 0.24);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
            padding: 14px;
            display: grid;
            gap: 8px;
        }

        .planning-time {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
        }

        .planning-badge {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(242, 213, 131, 0.28);
            border-radius: 999px;
            padding: 3px 9px;
            color: var(--soft);
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .planning-title {
            font-size: 1.3rem;
            line-height: 1.2;
            font-weight: 700;
            color: #ffffff;
        }

        .planning-type {
            color: var(--soft);
            font-size: 0.95rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .planning-card-meta {
            color: rgba(248, 247, 242, 0.92);
            font-size: 1rem;
            line-height: 1.55;
        }

        .planning-empty {
            min-height: 190px;
            border: 1px dashed rgba(165, 126, 28, 0.35);
            border-radius: 18px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 16px;
            color: rgba(242, 213, 131, 0.76);
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.02);
            line-height: 1.5;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 0 rgba(165, 126, 28, 0.72);
            animation: pulse 1.7s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(165, 126, 28, 0.72);
            }

            70% {
                box-shadow: 0 0 0 16px rgba(165, 126, 28, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(165, 126, 28, 0);
            }
        }
    </style>
</head>
<body>
<div id="player-root">
    <div id="media-stage"></div>
    <div id="status">Initialisation du player...</div>
</div>

<script>
    const deviceKey = @json($deviceKey);
    const stage = document.getElementById('media-stage');
    const statusEl = document.getElementById('status');
    const query = new URLSearchParams(window.location.search);
    const requestedMode = query.get('mode') === 'planning' ? 'planning' : 'auto';
    const feedCacheKey = `player_feed_${deviceKey}_${requestedMode}`;
    const transitionMode = query.get('transition') === 'slide' ? 'slide' : 'fade';
    const requestedPlanningDay = String(query.get('day') || '').toLowerCase();
    const REFRESH_INTERVAL_MS = 10_000;
    const EMPTY_REFRESH_INTERVAL_MS = 4_000;
    const OFFLINE_REFRESH_INTERVAL_MS = 12_000;
    const NOT_FOUND_REFRESH_INTERVAL_MS = 20_000;

    let items = [];
    let ads = [];
    let currentIndex = 0;
    let adCursor = 0;
    let loopCount = 0;
    let playingAd = false;
    let refreshHandle = null;
    let isRefreshing = false;
    let playbackHandle = null;
    let currentVideo = null;
    let playlistSignature = '';
    let planningPrograms = [];
    let effectiveMode = requestedMode === 'planning' ? 'planning' : 'media';
    let planningClockHandle = null;
    let serverClockOffsetMs = 0;
    let serverClockTimeZone = 'UTC';

    function setStatus(text) {
        statusEl.style.display = '';
        statusEl.textContent = text;
    }

    function hideStatus() {
        statusEl.style.display = 'none';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function normalizeDay(value) {
        return String(value ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function getServerNow() {
        return new Date(Date.now() + serverClockOffsetMs);
    }

    function updatePlanningClock() {
        const clockEl = document.getElementById('premium-clock');
        const dateEl = document.getElementById('premium-date-display');

        if (!clockEl || !dateEl) {
            return;
        }

        const now = getServerNow();

        clockEl.textContent = new Intl.DateTimeFormat('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: serverClockTimeZone,
        }).format(now);

        dateEl.textContent = new Intl.DateTimeFormat('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            timeZone: serverClockTimeZone,
        }).format(now).toUpperCase();
    }

    function startPlanningClock(serverClock = null, generatedAt = null) {
        if (planningClockHandle) {
            clearInterval(planningClockHandle);
            planningClockHandle = null;
        }

        const serverNow = new Date(serverClock?.iso || generatedAt || Date.now());
        serverClockOffsetMs = serverNow.getTime() - Date.now();
        serverClockTimeZone = serverClock?.timezone || 'UTC';

        updatePlanningClock();
        planningClockHandle = setInterval(updatePlanningClock, 1000);
    }

    function cacheFeed(feed) {
        localStorage.setItem(feedCacheKey, JSON.stringify({
            saved_at: Date.now(),
            feed,
        }));
    }

    function readCachedFeed() {
        try {
            const raw = localStorage.getItem(feedCacheKey);
            if (!raw) return null;
            const payload = JSON.parse(raw);
            return payload?.feed ?? null;
        } catch {
            return null;
        }
    }

    function clearPlayback() {
        if (playbackHandle) {
            clearTimeout(playbackHandle);
            playbackHandle = null;
        }

        if (planningClockHandle) {
            clearInterval(planningClockHandle);
            planningClockHandle = null;
        }

        if (currentVideo) {
            currentVideo.pause();
            currentVideo.removeAttribute('src');
            currentVideo.load();
            currentVideo = null;
        }

        stage.classList.toggle('planning-mode', effectiveMode === 'planning');
    }

    function createSlide() {
        const slide = document.createElement('div');
        slide.className = 'slide';

        if (transitionMode === 'slide') {
            slide.classList.add('enter-slide');
        }

        return slide;
    }

    function showEmptyState(message = 'Aucun contenu assigne') {
        clearPlayback();
        stage.innerHTML = `
            <div id="empty-state">
                <div class="dot"></div>
                <h1>${message}</h1>
                <p>Device key: ${deviceKey}</p>
            </div>
        `;
    }

    function renderOfflineScreen() {
        clearPlayback();
        stage.innerHTML = '';
        hideStatus();
    }

    function renderPlanningBoard(screen, programs, generatedAt, serverClock = null) {
        clearPlayback();

        const days = [
            { key: 'lundi', label: 'Lundi' },
            { key: 'mardi', label: 'Mardi' },
            { key: 'mercredi', label: 'Mercredi' },
            { key: 'jeudi', label: 'Jeudi' },
            { key: 'vendredi', label: 'Vendredi' },
            { key: 'samedi', label: 'Samedi' },
            { key: 'dimanche', label: 'Dimanche' },
        ];

        const serverNow = new Date(serverClock?.iso || generatedAt || Date.now());
        const derivedServerDayKey = serverClock?.day_key
            || normalizeDay(new Intl.DateTimeFormat('fr-FR', {
                weekday: 'long',
                timeZone: serverClock?.timezone || 'UTC',
            }).format(serverNow));
        const activeDay = days.find((day) => day.key === normalizeDay(requestedPlanningDay))
            ?? days.find((day) => day.key === derivedServerDayKey)
            ?? days[0];

        const dayPrograms = programs
            .filter((program) => String(program.day).toLowerCase() === activeDay.key)
            .sort((a, b) => `${a.start_time}|${a.display_order ?? 1}|${a.title ?? ''}`.localeCompare(`${b.start_time}|${b.display_order ?? 1}|${b.title ?? ''}`));

        const rowsMarkup = dayPrograms.length
            ? dayPrograms.map((program) => `
                <div class="premium-row">
                    <div class="premium-row-time">${escapeHtml(program.start_time)}</div>
                    <div class="premium-row-details">
                        <p class="premium-course-name">${escapeHtml(program.title || 'Programme')}</p>
                        <div class="premium-row-meta">
                            Coach <b>${escapeHtml(program.coach || '-')}</b>
                            ${program.course_type ? ` • ${escapeHtml(program.course_type)}` : ''}
                            • ${escapeHtml(program.duration)} min
                        </div>
                    </div>
                    <div class="premium-row-room">${escapeHtml(program.room || '-')}</div>
                </div>
            `).join('')
            : `
                <div class="premium-row premium-row-empty">
                    <div class="premium-row-time">--:--</div>
                    <div class="premium-row-details">
                        <p class="premium-course-name">Aucun programme</p>
                        <div class="premium-row-meta">Aucun cours prevu pour ${escapeHtml(activeDay.label)}</div>
                    </div>
                    <div class="premium-row-room">-</div>
                </div>
            `;

        const shouldScroll = dayPrograms.length > 5;
        const scrollMarkup = shouldScroll ? rowsMarkup + rowsMarkup : rowsMarkup;
        const scrollDuration = Math.max(30, dayPrograms.length * 8);
        const hallLabel = screen?.sports_hall?.name || screen?.name || deviceKey;

        stage.innerHTML = `
            <div class="premium-planning-screen">
                <div class="premium-bg-overlay"></div>
                <div class="premium-layout">
                    <aside class="premium-side-info">
                        <div class="premium-screen-label">${escapeHtml(screen?.device_key || deviceKey)}</div>
                        <div id="premium-clock" class="premium-clock">00:00:00</div>
                        <div id="premium-date-display" class="premium-date-display">CHARGEMENT...</div>
                        <div class="premium-day-badge">${escapeHtml(activeDay.label)}</div>
                    </aside>

                    <section class="premium-scroll-shell">
                        <header class="premium-scroll-header">
                            <div>
                                <p class="premium-eyebrow">Programme du jour serveur</p>
                                <h1>${escapeHtml(hallLabel)}</h1>
                            </div>
                            <div class="premium-header-meta">
                                <span>${dayPrograms.length} programme${dayPrograms.length > 1 ? 's' : ''}</span>
                                <span>Ecran ${escapeHtml(screen?.name || deviceKey)}</span>
                            </div>
                        </header>

                        <div class="premium-scroll-container">
                            <div
                                id="premium-scroll-list"
                                class="premium-scroll-content${shouldScroll ? '' : ' is-static'}"
                                ${shouldScroll ? `style="animation-duration:${scrollDuration}s"` : ''}
                            >
                                ${scrollMarkup}
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        `;

        startPlanningClock(serverClock, generatedAt);
    }

    function nextItem() {
        if (!items.length && !ads.length) {
            showEmptyState('Aucun contenu programme');
            return;
        }

        if (playingAd) {
            playingAd = false;
            playCurrentItem();
            return;
        }

        if (!items.length && ads.length) {
            adCursor = (adCursor + 1) % ads.length;
            playCurrentItem();
            return;
        }

        currentIndex += 1;
        if (currentIndex >= items.length) {
            currentIndex = 0;
            loopCount += 1;
        }

        playCurrentItem();
    }

    function renderImage(item) {
        const slide = createSlide();

        const img = document.createElement('img');
        img.src = item.url;
        img.alt = item.title || 'media-image';

        slide.appendChild(img);
        stage.innerHTML = '';
        stage.appendChild(slide);

        requestAnimationFrame(() => slide.classList.add('active'));

        const seconds = Math.max(1, Number(item.duration) || 10);
        playbackHandle = setTimeout(nextItem, seconds * 1000);

        const next = items.length > 0 ? items[(currentIndex + 1) % items.length] : null;
        if (next && next.type === 'image') {
            const prefetch = new Image();
            prefetch.src = next.url;
        }
    }

    function renderVideo(item) {
        const slide = createSlide();

        const video = document.createElement('video');
        video.src = item.url;
        video.autoplay = true;
        video.muted = true;
        video.playsInline = true;
        video.preload = 'auto';

        video.addEventListener('ended', nextItem);
        video.addEventListener('error', () => {
            setStatus('Erreur video, passage au media suivant...');
            playbackHandle = setTimeout(nextItem, 1500);
        });

        slide.appendChild(video);
        stage.innerHTML = '';
        stage.appendChild(slide);
        requestAnimationFrame(() => slide.classList.add('active'));

        currentVideo = video;

        const playPromise = video.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {
                setStatus('Lecture auto video bloquee, fallback par minuterie');
                const fallback = Math.max(1, Number(item.duration) || 12);
                playbackHandle = setTimeout(nextItem, fallback * 1000);
            });
        }
    }

    function getNextAdForCurrentLoop() {
        if (!ads.length) {
            return null;
        }

        const ad = ads[adCursor % ads.length];
        const every = Math.max(1, Number(ad.display_every_loops) || 1);

        if (loopCount % every !== 0) {
            return null;
        }

        return ad;
    }

    function playCurrentItem() {
        if (!items.length && !ads.length) {
            showEmptyState('Aucun contenu dans la playlist');
            return;
        }

        clearPlayback();

        if (!items.length && ads.length) {
            const adOnly = ads[adCursor % ads.length];
            setStatus(`Ecran ${deviceKey} - PUBLICITE - ${adCursor + 1}/${ads.length}`);
            playingAd = true;

            if (adOnly.type === 'video') {
                renderVideo(adOnly);
            } else {
                renderImage(adOnly);
            }

            return;
        }

        if (currentIndex === 0) {
            const ad = getNextAdForCurrentLoop();
            if (ad) {
                setStatus(`Ecran ${deviceKey} - PUBLICITE - boucle ${loopCount + 1}`);
                playingAd = true;
                adCursor = (adCursor + 1) % ads.length;

                if (ad.type === 'video') {
                    renderVideo(ad);
                } else {
                    renderImage(ad);
                }

                return;
            }
        }

        const item = items[currentIndex];
        setStatus(`Ecran ${deviceKey} - ${item.type.toUpperCase()} - ${currentIndex + 1}/${Math.max(items.length, 1)}`);

        if (item.type === 'video') {
            renderVideo(item);
            return;
        }

        renderImage(item);
    }

    function buildSignature(feed) {
        if (feed?.mode === 'planning') {
            const programsSignature = (feed?.programs ?? [])
                .map(program => `${program.id}:${program.day}:${program.start_time}:${program.computed_end_time}:${program.duration}:${program.title}`)
                .join('|');

            return `${feed?.server_clock?.date ?? ''}::${feed?.server_clock?.day_key ?? ''}::${programsSignature}`;
        }

        const nextItems = feed?.items ?? [];
        const nextAds = feed?.ads ?? [];
        const itemSig = nextItems.map(item => `${item.playlist_item_id}:${item.media_id}:${item.duration}:${item.url}`).join('|');
        const adSig = nextAds.map(ad => `${ad.ad_schedule_id}:${ad.media_id}:${ad.duration}:${ad.display_every_loops}:${ad.url}`).join('|');
        return `${itemSig}##${adSig}`;
    }

    function switchMode(nextMode) {
        if (effectiveMode === nextMode) {
            stage.classList.toggle('planning-mode', effectiveMode === 'planning');
            return;
        }

        effectiveMode = nextMode;
        playlistSignature = '';
        planningPrograms = [];
        items = [];
        ads = [];
        currentIndex = 0;
        adCursor = 0;
        loopCount = 0;
        playingAd = false;
        clearPlayback();
    }

    function scheduleRefresh(delayMs = REFRESH_INTERVAL_MS) {
        if (refreshHandle) {
            clearTimeout(refreshHandle);
        }

        refreshHandle = setTimeout(() => {
            refreshPlaylist();
        }, Math.max(1000, Number(delayMs) || REFRESH_INTERVAL_MS));
    }

    function applyFeed(feed, source = 'api') {
        const screenStatus = String(feed?.screen?.status || '').toLowerCase();

        if (screenStatus === 'offline') {
            switchMode('media');
            renderOfflineScreen();
            playlistSignature = 'offline';
            planningPrograms = [];
            items = [];
            ads = [];
            currentIndex = 0;
            adCursor = 0;
            loopCount = 0;
            playingAd = false;
            return;
        }

        const nextMode = feed?.mode === 'planning' ? 'planning' : 'media';
        switchMode(nextMode);

        if (effectiveMode === 'planning') {
            const nextSignature = buildSignature(feed);
            const nextPrograms = feed?.programs ?? [];

            if (nextSignature !== playlistSignature || !stage.querySelector('.premium-planning-screen')) {
                planningPrograms = nextPrograms;
                playlistSignature = nextSignature;
                renderPlanningBoard(feed?.screen, planningPrograms, feed?.generated_at, feed?.server_clock);
            }

            if (!nextPrograms.length) {
                renderPlanningBoard(feed?.screen, [], feed?.generated_at, feed?.server_clock);
                setStatus(`Ecran ${feed?.screen?.device_key ?? deviceKey} - planning vide (${source})`);
            } else {
                setStatus(`Ecran ${feed?.screen?.device_key ?? deviceKey} - planning (${source})`);
            }

            return;
        }

        const nextItems = feed?.items ?? [];
        const nextAds = feed?.ads ?? [];
        const nextSignature = buildSignature(feed);

        if (nextSignature !== playlistSignature) {
            items = nextItems;
            ads = nextAds;
            playlistSignature = nextSignature;
            currentIndex = 0;
            adCursor = 0;
            loopCount = 0;
            playingAd = false;
            playCurrentItem();
        }

        if (!nextItems.length && !nextAds.length) {
            showEmptyState('Playlist vide pour cet ecran');
            setStatus(`Ecran ${deviceKey} - en attente de contenu (${source})`);
        }
    }

    async function refreshPlaylist() {
        if (isRefreshing) {
            return;
        }

        isRefreshing = true;
        let nextDelay = REFRESH_INTERVAL_MS;

        try {
            const modeSuffix = requestedMode === 'planning' ? '?mode=planning' : '';
            const response = await fetch(`/api/player/${encodeURIComponent(deviceKey)}${modeSuffix}`, {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                const errorPayload = await response.json().catch(() => ({}));
                const apiMessage = (errorPayload?.message || '').trim();

                if (response.status === 404) {
                    throw new Error(apiMessage || 'Ecran introuvable pour cette device key.');
                }

                throw new Error(apiMessage || `HTTP ${response.status}`);
            }

            const payload = await response.json();
            const feed = payload?.data ?? { items: [], ads: [] };
            cacheFeed(feed);
            applyFeed(feed, 'online');
            const hasContent = feed?.mode === 'planning'
                ? (feed.programs?.length ?? 0) > 0
                : (feed.items?.length ?? 0) > 0 || (feed.ads?.length ?? 0) > 0;
            nextDelay = hasContent ? REFRESH_INTERVAL_MS : EMPTY_REFRESH_INTERVAL_MS;
        } catch (error) {
            const cached = readCachedFeed();

            if (cached) {
                applyFeed(cached, 'cache');
                setStatus(`Ecran ${deviceKey} - mode offline (cache local)`);
                nextDelay = OFFLINE_REFRESH_INTERVAL_MS;
            } else {
                const isNotFound = String(error.message || '').toLowerCase().includes('screen not found')
                    || String(error.message || '').toLowerCase().includes('introuvable');
                if (requestedMode === 'planning' && !isNotFound) {
                    renderPlanningBoard({ device_key: deviceKey }, [], null);
                } else {
                    showEmptyState(isNotFound ? 'Ecran introuvable pour cette device key' : 'Erreur de chargement du player');
                }
                setStatus(isNotFound ? `Device key invalide (${deviceKey})` : `Erreur reseau player (${deviceKey})`);
                nextDelay = isNotFound ? NOT_FOUND_REFRESH_INTERVAL_MS : OFFLINE_REFRESH_INTERVAL_MS;
            }
        } finally {
            isRefreshing = false;
            scheduleRefresh(nextDelay);
        }
    }

    async function boot() {
        stage.classList.toggle('planning-mode', effectiveMode === 'planning');

        if (document.fullscreenEnabled) {
            document.documentElement.requestFullscreen().catch(() => {});
        }

        await refreshPlaylist();

        window.addEventListener('online', () => {
            setStatus(`Ecran ${deviceKey} - connexion retablie`);
            refreshPlaylist();
        });

        window.addEventListener('offline', () => {
            setStatus(`Ecran ${deviceKey} - hors ligne, cache local actif`);
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refreshPlaylist();
            }
        });

        window.addEventListener('beforeunload', () => {
            if (refreshHandle) {
                clearTimeout(refreshHandle);
            }
            clearPlayback();
        });
    }

    boot();
</script>
</body>
</html>

