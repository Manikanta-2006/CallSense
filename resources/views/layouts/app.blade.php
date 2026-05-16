<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CallSense')</title>
    <meta name="description" content="CallSense — AI-powered helpdesk sentiment monitoring system. Manga cyber-control interface.">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #111;
            --page-bg: #fff;
            --panel-border: #000;
            --text-color: #000;
            --sub-text: #999;
            --halftone: rgba(0,0,0,0.055);
            --accent: #000;
        }

        /* NEON THEME */
        .theme-neon-manga {
            --bg-color: #000;
            --page-bg: #0a0a0a;
            --panel-border: #00ff00;
            --text-color: #00ff00;
            --sub-text: #008800;
            --halftone: rgba(0,255,0,0.08);
            --accent: #00ff00;
        }
        .theme-neon-manga .page-left,
        .theme-neon-manga .page-front,
        .theme-neon-manga .left-bottom-panel,
        .theme-neon-manga .chapter-tab,
        .theme-neon-manga .left-bubble {
            background: #0a0a0a !important;
            color: #00ff00 !important;
            border-color: #00ff00 !important;
        }
        .theme-neon-manga .chapter-tab:hover,
        .theme-neon-manga .chapter-tab.active {
            background: #00ff00 !important;
            color: #000 !important;
        }

        /* MONO COMIC (Old Paper) */
        .theme-mono-comic {
            --bg-color: #2b1d0e;
            --page-bg: #f4ecd8;
            --panel-border: #3c2a1a;
            --text-color: #3c2a1a;
            --sub-text: #7a634e;
            --halftone: rgba(60,42,26,0.08);
            --accent: #3c2a1a;
        }
        .theme-mono-comic .page-left,
        .theme-mono-comic .page-front,
        .theme-mono-comic .left-bottom-panel,
        .theme-mono-comic .chapter-tab,
        .theme-mono-comic .left-bubble {
            background: #f4ecd8 !important;
            color: #3c2a1a !important;
            border-color: #3c2a1a !important;
        }
        .theme-mono-comic .chapter-tab:hover,
        .theme-mono-comic .chapter-tab.active {
            background: #3c2a1a !important;
            color: #f4ecd8 !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            width: 100vw; height: 100vh;
            overflow: hidden;
            background: #0a0a0a url('/desk_background_1778565656728.png') no-repeat center center;
            background-size: cover;
            font-family: 'Caveat', cursive;
            transition: background 0.3s;
        }

        /* Dark vignette at edges */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(circle, transparent 20%, rgba(0,0,0,0.5) 100%);
            pointer-events: none;
            z-index: 200;
        }

        @keyframes bookEntrance {
            0% { transform: translateY(100vh) rotateX(30deg) scale(0.7); opacity: 0; }
            60% { transform: translateY(-20px) rotateX(0deg) scale(1.02); opacity: 1; }
            100% { transform: translateY(0) rotateX(0deg) scale(1); opacity: 1; }
        }
        @keyframes agentFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(1deg); }
        }

        /* ══════════════════════════════════════
           BOOK SCENE — fills the whole screen
        ══════════════════════════════════════ */
        .book-scene {
            width: 100vw;
            height: 100vh;
            display: flex;
            perspective: 2500px;
            perspective-origin: 50% 50%;
            position: relative;
            padding: 40px;
            justify-content: center;
            align-items: center;
            animation: bookEntrance 1.4s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        .book-scene > div {
            box-shadow: 0 30px 60px rgba(0,0,0,0.8), 0 0 100px rgba(0,0,0,0.4);
        }

        /* Dark vignette at edges */
        .book-scene::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at center, transparent 70%, rgba(0,0,0,0.7) 100%);
            pointer-events: none;
            z-index: 200;
        }

        /* ══════════════════════════════════════
           LEFT HALF — manga nav page
        ══════════════════════════════════════ */
        .page-left {
            width: 50%;
            height: 100%;
            position: relative;
            background: var(--page-bg);
            border-right: 5px solid var(--panel-border);
            overflow: hidden;
            transform-origin: right center;
        }

        /* Halftone dots */
        .page-left::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,0,0,0.055) 1.2px, transparent 1.2px);
            background-size: 13px 13px;
            pointer-events: none;
            z-index: 1;
        }

        /* Manga speed lines on left page */
        .speed-lines {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .speed-lines::before {
            content: '';
            position: absolute;
            top: 50%; left: 100%;
            transform: translate(-50%, -50%);
            width: 200%;
            height: 200%;
            background: repeating-conic-gradient(
                rgba(0,0,0,0.025) 0deg 2.5deg,
                transparent 2.5deg 5deg
            );
        }

        .left-inner {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 0;
        }

        /* Top manga panel — logo area with image */
        .left-top-panel {
            border-bottom: 4px solid var(--panel-border);
            position: relative;
            overflow: hidden;
            height: 42%;
            flex-shrink: 0;
        }
        .left-top-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            display: block;
            filter: contrast(1.1) brightness(0.95);
            animation: agentFloat 4s ease-in-out infinite;
        }

        /* Logo overlay on top panel */
        .left-logo-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.85));
            padding: 1.2rem 1.2rem 0.8rem;
        }
        .left-logo {
            font-family: 'Bangers', cursive;
            font-size: 2.4rem;
            color: #fff;
            letter-spacing: 4px;
            line-height: 1;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.5);
        }
        .left-logo-sub {
            font-family: 'Bangers', cursive;
            font-size: 0.55rem;
            letter-spacing: 5px;
            color: rgba(255,255,255,0.6);
            margin-top: 0.2rem;
        }

        /* Bottom nav panel */
        .left-bottom-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0.7rem 1rem;
            gap: 0.3rem;
            background: var(--page-bg);
            overflow: hidden;
        }

        /* Section label */
        .nav-section-label {
            font-family: 'Bangers', cursive;
            font-size: 0.5rem;
            letter-spacing: 3px;
            color: #ccc;
            padding: 0.2rem 0.3rem 0;
        }

        /* Chapter tabs — manga bookmark style */
        .chapter-tabs { display: flex; flex-direction: column; gap: 0.28rem; }
        .chapter-tab {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.42rem 0.7rem;
            background: var(--page-bg);
            border: 2.5px solid var(--panel-border);
            font-family: 'Bangers', cursive;
            font-size: 0.88rem;
            letter-spacing: 1.5px;
            color: var(--sub-text);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
            position: relative;
        }
        .chapter-tab::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #ddd;
            transition: background 0.15s;
        }
        .chapter-tab:hover {
            border-color: var(--accent); color: var(--accent);
            box-shadow: 3px 3px 0 var(--accent);
            transform: translate(-1px,-1px);
        }
        .chapter-tab:hover::before { background: #000; }
        .chapter-tab.active {
            background: var(--accent); color: var(--page-bg); border-color: var(--accent);
            box-shadow: 3px 3px 0 #555;
        }
        .chapter-tab.active::before { background: #fff; }
        .chapter-tab .tab-icon { font-size: 0.9rem; filter: grayscale(1); }
        .chapter-tab .tab-num {
            margin-left: auto;
            font-size: 0.55rem; letter-spacing: 2px; color: inherit; opacity: 0.6;
        }

        /* Logout tab — special style */
        .chapter-tab.logout-tab {
            border-color: #ddd; color: #bbb;
            margin-top: 0.2rem;
        }
        .chapter-tab.logout-tab:hover {
            border-color: #000; color: #fff;
            background: #000;
            box-shadow: 3px 3px 0 #333;
        }
        .chapter-tab.logout-tab::before { background: #ddd; }
        .chapter-tab.logout-tab:hover::before { background: #fff; }

        /* Speech bubble at bottom */
        .left-bubble {
            position: relative;
            background: var(--page-bg);
            border: 2.5px solid var(--panel-border);
            border-radius: 14px;
            padding: 0.5rem 0.8rem;
            font-family: 'Caveat', cursive;
            font-size: 0.88rem;
            color: var(--text-color);
            line-height: 1.3;
            text-align: center;
            box-shadow: 3px 3px 0 rgba(0,0,0,0.1);
            margin-top: auto;
        }
        .left-bubble::after {
            content: ''; position: absolute;
            top: -12px; left: 50%; transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: var(--panel-border);
        }
        .left-bubble::before {
            content: ''; position: absolute;
            top: -7px; left: 50%; transform: translateX(-50%);
            border: 4px solid transparent;
            border-bottom-color: var(--page-bg);
            z-index: 1;
        }

        .left-page-num {
            font-family: 'Bangers', cursive;
            font-size: 0.65rem; letter-spacing: 3px;
            color: #bbb; text-align: center;
            padding-top: 0.3rem;
        }

        /* ══════════════════════════════════════
           3D PAGE FLIP CONTAINER
        ══════════════════════════════════════ */
        .page-flip-container {
            width: 50%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transform-origin: left center;
        }

        /* FRONT face = right page (current content) */
        .page-front,
        .page-back {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            overflow: hidden;
        }

        /* FRONT — the right content page */
        .page-front {
            background: var(--page-bg);
            border-left: 5px solid var(--panel-border);
            overflow-y: auto;
        }
        /* Custom manga scrollbar */
        .page-front::-webkit-scrollbar,
        .left-bottom-panel::-webkit-scrollbar,
        .page::-webkit-scrollbar { 
            width: 8px; 
        }
        .page-front::-webkit-scrollbar-track,
        .page::-webkit-scrollbar-track { 
            background: var(--halftone); 
        }
        .page-front::-webkit-scrollbar-thumb,
        .page::-webkit-scrollbar-thumb { 
            background: var(--panel-border); 
            border: 2px solid var(--page-bg);
        }

        /* BACK — shown during flip */
        .page-back {
            background: #f0f0f0;
            border-left: 5px solid #000;
            transform: rotateY(180deg);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page-back::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,0,0,0.07) 1.5px, transparent 1.5px);
            background-size: 10px 10px;
        }
        .page-back-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 2rem;
        }
        .page-back-content .panel-back {
            border: 4px solid #000;
            padding: 2rem;
            box-shadow: 6px 6px 0 #000;
        }
        .page-back-content h2 {
            font-family: 'Bangers', cursive;
            font-size: 3rem;
            letter-spacing: 4px;
            color: #000;
        }
        .page-back-content p {
            font-family: 'Caveat', cursive;
            font-size: 1.1rem;
            color: #555;
            margin-top: 0.5rem;
        }

        /* Page content wrapper */
        #pageContent {
            position: relative;
            z-index: 2;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Alert badge on nav */
        .tab-alert-badge {
            font-family:'Bangers',cursive;
            font-size:0.45rem; letter-spacing:1px;
            background:#000; color:#fff;
            padding:0.05rem 0.3rem;
            margin-left:0.2rem;
            animation: badgePulse 1.5s infinite;
        }
        @keyframes badgePulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

        /* ── FLIP ANIMATION ── */
        @keyframes flipOut {
            0%   { transform: rotateY(0deg); }
            100% { transform: rotateY(-180deg); }
        }
        @keyframes flipIn {
            0%   { transform: rotateY(180deg); }
            100% { transform: rotateY(0deg); }
        }
        .page-flip-container.flipping {
            animation: flipOut 0.55s cubic-bezier(0.645, 0.045, 0.355, 1.000) forwards;
        }
        .page-flip-container.flipping-in {
            animation: flipIn 0.55s cubic-bezier(0.645, 0.045, 0.355, 1.000) forwards;
        }

        /* Page corner fold */
        .page-corner {
            position: absolute; bottom: 14px; right: 14px;
            width: 44px; height: 44px;
            cursor: pointer; z-index: 10;
            overflow: hidden;
        }
        .page-corner::before {
            content: '▶';
            position: absolute; bottom: 7px; right: 8px;
            font-size: 0.65rem; color: #777; opacity: 0.4;
            transition: opacity 0.2s;
        }
        .page-corner:hover::before { opacity: 1; }
        .page-corner::after {
            content: '';
            position: absolute; bottom: 0; right: 0;
            width: 0; height: 0;
            border-style: solid; border-width: 0 0 42px 42px;
            border-color: transparent transparent #e0e0e0 transparent;
            transition: border-width 0.2s;
            filter: drop-shadow(-2px -2px 3px rgba(0,0,0,0.15));
        }
        .page-corner:hover::after { border-width: 0 0 56px 56px; }

        /* ── MANGA ENTRANCE ANIMATIONS ── */
        @keyframes mangaPop {
            0% { transform: scale(0.8); opacity: 0; }
            70% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes mangaSlideDown {
            0% { transform: translateY(-30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes mangaSlideUp {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes mangaReveal {
            0% { clip-path: inset(0 100% 0 0); }
            100% { clip-path: inset(0 0 0 0); }
        }
        @keyframes bubblePop {
            0% { transform: scale(0); opacity: 0; }
            80% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .anim-pop { animation: mangaPop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; opacity: 0; }
        .anim-slide-down { animation: mangaSlideDown 0.6s ease-out forwards; opacity: 0; }
        .anim-slide-up { animation: mangaSlideUp 0.6s ease-out forwards; opacity: 0; }
        .anim-reveal { animation: mangaReveal 0.8s ease-in-out forwards; }
        .anim-bubble { animation: bubblePop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; opacity: 0; transform-origin: left center; }

        /* Custom scrollbar support */
        * { scrollbar-width: thin; scrollbar-color: var(--panel-border) var(--page-bg); }

        @yield('styles')
    </style>
</head>
<body class="theme-{{ auth()->user()->theme ?? 'dark-manga' }}">
<div class="book-scene">

    <!-- LEFT PAGE — Manga nav -->
    <div class="page-left">
        <div class="speed-lines"></div>
        <div class="left-inner">
            <div class="left-top-panel">
                <img src="/images/manga_agent.png" alt="CallSense Agent">
                <div class="left-logo-overlay">
                    <div class="left-logo">CALLSENSE</div>
                    <div class="left-logo-sub">HELPDESK SENTIMENT SYSTEM</div>
                </div>
            </div>
            <div class="left-bottom-panel">
                <div class="nav-section-label">// MAIN CHAPTERS</div>
                <div class="chapter-tabs">
                    <a href="/calls" class="chapter-tab {{ request()->is('calls') ? 'active' : '' }}" id="nav-calls">
                        <span class="tab-icon">📞</span> CALL ANALYSIS
                        <span class="tab-num">P.1</span>
                    </a>
                    <a href="/dashboard" class="chapter-tab {{ request()->is('dashboard') ? 'active' : '' }}" id="nav-dash">
                        <span class="tab-icon">📊</span> MISSION CONTROL
                        <span class="tab-num">P.2</span>
                    </a>
                    <a href="/history" class="chapter-tab {{ request()->is('history') ? 'active' : '' }}" id="nav-hist">
                        <span class="tab-icon">🗂</span> CASE FILES
                        <span class="tab-num">P.3</span>
                    </a>
                    <a href="/alerts" class="chapter-tab {{ request()->is('alerts') ? 'active' : '' }}" id="nav-alerts">
                        <span class="tab-icon">⚠</span> CRITICAL ALERTS
                        @php $activeAlertCount = \App\Models\Alert::whereIn('status', ['pending','investigating'])->count(); @endphp
                        @if($activeAlertCount > 0)
                            <span class="tab-alert-badge">{{ $activeAlertCount }}</span>
                        @endif
                        <span class="tab-num">P.4</span>
                    </a>
                    <a href="/profile" class="chapter-tab {{ request()->is('profile') ? 'active' : '' }}" id="nav-profile">
                        <span class="tab-icon">👤</span> AGENT PROFILE
                        <span class="tab-num">P.5</span>
                    </a>

                    <form method="POST" action="/logout" style="margin:0;" id="logout-form">
                        @csrf
                        <button type="submit" class="chapter-tab logout-tab" style="width:100%;border-top:none;background:none;text-align:left;">
                            <span class="tab-icon">🚪</span> EXIT CONTROL ROOM
                            <span class="tab-num">↩</span>
                        </button>
                    </form>
                </div>
                <div class="left-bubble">
                    ⚡ @yield('page-quote', 'Analyzing emotions...')
                </div>
                <div class="left-page-num">@yield('page-number', '— OF 5')</div>
            </div>
        </div>
    </div>

    <!-- RIGHT — 3D flip container -->
    <div class="page-flip-container" id="pageFlipContainer">

        <!-- FRONT: current page content -->
        <div class="page-front">
            <div id="pageContent">
                @yield('content')
            </div>
            <div class="page-corner" id="pageCornerNext" title="Next page"></div>
        </div>

        <!-- BACK: shown mid-flip -->
        <div class="page-back">
            <div class="page-back-content">
                <div class="panel-back">
                    <h2>LOADING<br>NEXT<br>CHAPTER…</h2>
                    <p>// turning page</p>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    const flipContainer = document.getElementById('pageFlipContainer');

    function turnPage(dest) {
        if (!dest || dest === window.location.href) return;
        flipContainer.classList.add('flipping');
        setTimeout(() => { window.location.href = dest; }, 550);
    }

    // Only intercept chapter-tab nav links — NOT all links on the page
    document.querySelectorAll('.chapter-tab[href]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            turnPage(this.href);
        });
    });

    const pageOrder = ['/calls', '/dashboard', '/history', '/alerts', '/profile'];
    const currentPath = window.location.pathname;
    const currentIdx = pageOrder.indexOf(currentPath);
    const cornerNext = document.getElementById('pageCornerNext');
    if (currentIdx >= 0 && currentIdx < pageOrder.length - 1) {
        cornerNext.addEventListener('click', () => turnPage(pageOrder[currentIdx + 1]));
    } else {
        if (cornerNext) cornerNext.style.display = 'none';
    }

    // Flip-in on page load
    flipContainer.classList.add('flipping-in');
    setTimeout(() => flipContainer.classList.remove('flipping-in'), 560);
</script>

@yield('scripts')
</body>
</html>
