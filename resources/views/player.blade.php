<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player - {{ $deviceKey }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #051018;
            --bg-mid: #10364a;
            --bg-soft: #0f5c63;
            --text: #eaf6ff;
            --badge: rgba(8, 20, 30, 0.74);
            --accent: #ffb100;
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
                radial-gradient(circle at 12% 18%, rgba(255, 177, 0, 0.24), transparent 36%),
                radial-gradient(circle at 84% 80%, rgba(55, 217, 178, 0.2), transparent 40%),
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
            border: 1px solid rgba(255, 255, 255, 0.15);
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
            color: rgba(240, 244, 248, 0.9);
            text-align: center;
            padding: 24px;
            background: linear-gradient(180deg, rgba(6, 9, 15, 0.75), rgba(20, 38, 53, 0.65));
        }

        #empty-state h1 {
            margin: 0;
            font-size: clamp(1.2rem, 2vw, 2rem);
            font-weight: 700;
            font-family: var(--font-title);
            letter-spacing: 0.05em;
        }

        #empty-state p {
            margin: 0;
            color: rgba(240, 244, 248, 0.72);
            font-size: clamp(0.95rem, 1.2vw, 1.2rem);
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 0 rgba(255, 190, 11, 0.75);
            animation: pulse 1.7s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 190, 11, 0.75);
            }

            70% {
                box-shadow: 0 0 0 16px rgba(255, 190, 11, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 190, 11, 0);
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
    const feedCacheKey = `player_feed_${deviceKey}`;
    const query = new URLSearchParams(window.location.search);
    const transitionMode = query.get('transition') === 'slide' ? 'slide' : 'fade';

    let items = [];
    let ads = [];
    let currentIndex = 0;
    let adCursor = 0;
    let loopCount = 0;
    let playingAd = false;
    let refreshHandle = null;
    let playbackHandle = null;
    let currentVideo = null;
    let playlistSignature = '';

    function setStatus(text) {
        statusEl.textContent = text;
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

        if (currentVideo) {
            currentVideo.pause();
            currentVideo.removeAttribute('src');
            currentVideo.load();
            currentVideo = null;
        }
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

    function buildSignature(nextItems, nextAds) {
        const itemSig = nextItems.map(item => `${item.playlist_item_id}:${item.media_id}:${item.duration}:${item.url}`).join('|');
        const adSig = nextAds.map(ad => `${ad.ad_schedule_id}:${ad.media_id}:${ad.duration}:${ad.display_every_loops}:${ad.url}`).join('|');
        return `${itemSig}##${adSig}`;
    }

    function applyFeed(feed, source = 'api') {
        const nextItems = feed?.items ?? [];
        const nextAds = feed?.ads ?? [];
        const nextSignature = buildSignature(nextItems, nextAds);

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
        try {
            const response = await fetch(`/api/player/${encodeURIComponent(deviceKey)}`, {
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const feed = payload?.data ?? { items: [], ads: [] };
            cacheFeed(feed);
            applyFeed(feed, 'online');
        } catch (error) {
            const cached = readCachedFeed();

            if (cached) {
                applyFeed(cached, 'cache');
                setStatus(`Ecran ${deviceKey} - mode offline (cache local)`);
                return;
            }

            showEmptyState('Erreur de chargement du player');
            setStatus(`Erreur reseau player (${deviceKey})`);
        }
    }

    async function boot() {
        if (document.fullscreenEnabled) {
            document.documentElement.requestFullscreen().catch(() => {});
        }

        await refreshPlaylist();

        refreshHandle = setInterval(refreshPlaylist, 60_000);

        window.addEventListener('online', () => {
            setStatus(`Ecran ${deviceKey} - connexion retablie`);
            refreshPlaylist();
        });

        window.addEventListener('offline', () => {
            setStatus(`Ecran ${deviceKey} - hors ligne, cache local actif`);
        });

        window.addEventListener('beforeunload', () => {
            if (refreshHandle) {
                clearInterval(refreshHandle);
            }
            clearPlayback();
        });
    }

    boot();
</script>
</body>
</html>
