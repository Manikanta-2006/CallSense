<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CallSense — AI Emotion Command Center</title>
    <meta name="description" content="CallSense AI-powered helpdesk sentiment analysis. Real-time call monitoring, emotion detection, and alert management.">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        html, body { 
            width:100vw; height:100vh; overflow:hidden; font-family:'Caveat',cursive; 
            background: #0a0a0a url('/desk_background_1778565656728.png') no-repeat center center;
            background-size: cover;
        }

        /* Dark vignette to focus on center */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(circle, transparent 20%, rgba(0,0,0,0.4) 100%);
            z-index: 2;
        }

        @keyframes bookEntrance {
            0% { transform: translateY(100vh) rotateX(30deg) rotateZ(-10deg) scale(0.7); opacity: 0; }
            60% { transform: translateY(-20px) rotateX(5deg) rotateZ(2deg) scale(1.02); opacity: 1; }
            100% { transform: translateY(0) rotateX(0deg) rotateZ(0deg) scale(1); opacity: 1; }
        }

        .book-scene {
            width:100vw; height:100vh;
            display:flex; justify-content: center; align-items: center;
            perspective: 2500px;
            position:relative;
            z-index: 10;
            padding-top: 50px;
            animation: bookEntrance 1.8s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }

        /* ─── THE MANGA BOOK ─── */
        .manga-book {
            width: 420px;
            height: 600px;
            background: #fff;
            border: 8px solid #000;
            cursor: pointer;
            position: relative;
            box-shadow: 
                0 20px 50px rgba(0,0,0,0.9),
                10px 10px 0 rgba(0,0,0,0.2);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s;
            overflow: hidden;
            text-decoration: none;
            display: block;
        }
        .manga-book:hover {
            transform: scale(1.03) translateY(-10px) rotate(1deg);
            box-shadow: 
                0 30px 70px rgba(0,0,0,1),
                15px 15px 0 rgba(0,0,0,0.15);
        }

        .manga-book img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: contrast(1.1) brightness(0.9);
        }

        /* Shine effect */
        .manga-book::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, transparent 45%, rgba(255,255,255,0.1) 50%, transparent 55%);
            background-size: 200% 200%;
            animation: shine 6s infinite;
        }
        @keyframes shine {
            0% { background-position: 200% 200%; }
            20% { background-position: -100% -100%; }
            100% { background-position: -100% -100%; }
        }

        /* Login Hint */
        .login-hint {
            position: absolute;
            bottom: 2rem;
            width: 100%;
            text-align: center;
            font-family: 'Bangers', cursive;
            font-size: 1.2rem;
            color: #fff;
            letter-spacing: 4px;
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
            opacity: 0.6;
            animation: pulse 2s infinite;
            z-index: 20;
            pointer-events: none;
        }
        @keyframes pulse { 0%, 100% { opacity: 0.4; transform: scale(0.95); } 50% { opacity: 0.8; transform: scale(1); } }

        /* ─── SYSTEM STATUS PANEL ─── */
        .status-panel {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 50;
            background: rgba(0,0,0,0.85);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            padding: 1rem 1.2rem;
            min-width: 220px;
            animation: fadeIn 1s ease-out 0.5s both;
        }
        @keyframes fadeIn { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }

        .status-title {
            font-family: 'Bangers', cursive;
            font-size: 0.6rem;
            letter-spacing: 4px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.6rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 0.4rem;
        }
        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.25rem 0;
        }
        .status-label {
            font-family: 'Bangers', cursive;
            font-size: 0.65rem;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.6);
        }
        .status-val {
            font-family: 'Bangers', cursive;
            font-size: 0.65rem;
            letter-spacing: 1px;
            display: flex; align-items: center; gap: 0.3rem;
        }
        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            animation: statusPulse 2s infinite;
        }
        .status-dot.green { background: #4ade80; }
        .status-dot.yellow { background: #fbbf24; }
        .status-dot.red { background: #f87171; }
        @keyframes statusPulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
        .status-val.green { color: #4ade80; }
        .status-val.yellow { color: #fbbf24; }
        .status-val.red { color: #f87171; }

        /* ─── LIVE METRICS PANEL ─── */
        .metrics-panel {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            z-index: 50;
            background: rgba(0,0,0,0.85);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            padding: 1rem 1.2rem;
            min-width: 200px;
            animation: fadeIn 1s ease-out 0.7s both;
        }
        .metrics-title {
            font-family: 'Bangers', cursive;
            font-size: 0.6rem;
            letter-spacing: 4px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.6rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 0.4rem;
        }
        .metric-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.3rem 0;
        }
        .metric-label {
            font-family: 'Caveat', cursive;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
        }
        .metric-val {
            font-family: 'Bangers', cursive;
            font-size: 1.1rem;
            letter-spacing: 2px;
            color: #fff;
        }

        /* Scan overlay */
        .scan-overlay {
            position:fixed; inset:0; z-index:1000;
            display:none; align-items:center; justify-content:center;
            background:rgba(0,0,0,0.95); flex-direction:column; gap:1.5rem;
        }
        .scan-overlay.active { display:flex; }
        .scan-bar {
            width:340px; height:6px; background:#111; overflow:hidden;
            border:2px solid #fff; position:relative;
        }
        .scan-progress {
            height:100%; background:#fff; width:0%;
            animation:scanMove 1.8s ease-out forwards;
        }
        @keyframes scanMove { 0%{width:0%} 100%{width:100%} }
        .scan-text {
            font-family:'Bangers',cursive; font-size:1.8rem; letter-spacing:6px;
            color:#fff;
        }

        @media (max-width: 768px) {
            .manga-book { width: 280px; height: 400px; }
            .status-panel, .metrics-panel { display: none; }
        }
    </style>
</head>
<body>

<div class="lamp-light"></div>

<!-- Scan overlay -->
<div class="scan-overlay" id="scanOverlay">
    <div class="scan-text">AUTHENTICATING AGENT…</div>
    <div class="scan-bar"><div class="scan-progress"></div></div>
</div>



<div class="book-scene">
    <a href="/auth/google" class="manga-book" onclick="showScan(event, this.href)">
        <img src="/manga_cover_art_1778565315512.png" alt="CallSense Manga Cover">
    </a>
</div>

<div class="login-hint">CLICK TO START MISSION</div>

<script>
    function showScan(e, dest) {
        e.preventDefault();
        document.getElementById('scanOverlay').classList.add('active');
        setTimeout(() => { window.location.href = dest; }, 2000);
    }

    // Fetch REAL system status from backend
    async function fetchStatus() {
        try {
            const res = await fetch('/api/system-status');
            const data = await res.json();

            // Database status
            const dbEl = document.getElementById('statusDb');
            if (data.database === 'connected') {
                dbEl.innerHTML = '<span class="status-dot green"></span> CONNECTED';
                dbEl.className = 'status-val green';
            } else {
                dbEl.innerHTML = '<span class="status-dot red"></span> OFFLINE';
                dbEl.className = 'status-val red';
            }

            // NLP API status
            const nlpEl = document.getElementById('statusNlp');
            if (data.nlp_api === 'configured') {
                nlpEl.innerHTML = '<span class="status-dot green"></span> ACTIVE';
                nlpEl.className = 'status-val green';
            } else {
                nlpEl.innerHTML = '<span class="status-dot yellow"></span> MOCK MODE';
                nlpEl.className = 'status-val yellow';
            }

            // Queue status
            const qEl = document.getElementById('statusQueue');
            if (data.queue && data.queue.status === 'running') {
                const failed = data.queue.failed_jobs || 0;
                if (failed > 0) {
                    qEl.innerHTML = '<span class="status-dot yellow"></span> ' + failed + ' FAILED';
                    qEl.className = 'status-val yellow';
                } else {
                    qEl.innerHTML = '<span class="status-dot green"></span> RUNNING';
                    qEl.className = 'status-val green';
                }
            } else {
                qEl.innerHTML = '<span class="status-dot red"></span> UNKNOWN';
                qEl.className = 'status-val red';
            }

            // Live metrics from REAL DB
            if (data.metrics) {
                document.getElementById('metricCalls').textContent = data.metrics.total_calls || 0;
                document.getElementById('metricAlerts').textContent = data.metrics.active_alerts || 0;
                document.getElementById('metricSentiment').textContent = data.metrics.avg_sentiment || '0.00';
            }
        } catch(e) {
            console.error('Status fetch failed:', e);
        }
    }

    fetchStatus();
    setInterval(fetchStatus, 15000);
</script>
</body>
</html>
