<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CallSense — Enter the Story</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Bangers&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            font-family: 'Caveat', cursive;
        }

        /* ═══════════════════════════════════════
           SCENE 1: Book on the Table
        ═══════════════════════════════════════ */
        .table-scene {
            position: fixed;
            inset: 0;
            background: #1a1209;
            background-image: url('/images/comic_book_cover.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            z-index: 100;
            cursor: pointer;
            transition: opacity 0.6s ease, transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-scene.zoom-out {
            opacity: 0;
            transform: scale(3);
            pointer-events: none;
        }

        /* Vignette overlay */
        .table-scene::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(0,0,0,0.5) 100%);
            pointer-events: none;
        }

        /* Subtle pulsing glow on the book area */
        .table-scene::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            height: 60%;
            background: radial-gradient(ellipse, rgba(255,240,210,0.08) 0%, transparent 70%);
            pointer-events: none;
            animation: glowPulse 3s ease-in-out infinite;
        }

        @keyframes glowPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* "Click to open" hint */
        .click-hint {
            margin-bottom: 3rem;
            font-family: 'Caveat', cursive;
            font-size: 1.6rem;
            color: rgba(255,240,210,0.6);
            letter-spacing: 3px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.6);
            animation: hintPulse 2s ease-in-out infinite;
            position: relative;
            z-index: 10;
        }

        @keyframes hintPulse {
            0%, 100% { opacity: 0.4; transform: translateY(0); }
            50% { opacity: 0.9; transform: translateY(-4px); }
        }

        /* Dust particles */
        .dust {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255,240,210,0.3);
            border-radius: 50%;
            pointer-events: none;
        }

        /* ═══════════════════════════════════════
           SCENE 2: Open Book (Login Page)
        ═══════════════════════════════════════ */
        .open-book-scene {
            position: fixed;
            inset: 0;
            background: #2a1f14;
            background-image:
                repeating-linear-gradient(90deg, transparent, transparent 60px, rgba(0,0,0,0.08) 60px, rgba(0,0,0,0.08) 62px),
                linear-gradient(180deg, #3a2a1a 0%, #2a1f14 40%, #1f1610 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease 0.4s;
        }

        .open-book-scene.visible {
            opacity: 1;
            pointer-events: all;
        }

        /* The open book spread */
        .open-book {
            display: flex;
            width: 90vw;
            max-width: 1100px;
            height: 85vh;
            max-height: 700px;
            perspective: 2500px;
            position: relative;
        }

        /* Spine shadow */
        .open-book::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 100%;
            background: linear-gradient(90deg,
                rgba(0,0,0,0.3) 0%,
                rgba(0,0,0,0.08) 30%,
                rgba(0,0,0,0) 50%,
                rgba(0,0,0,0.08) 70%,
                rgba(0,0,0,0.3) 100%
            );
            z-index: 10;
            pointer-events: none;
        }

        /* Page shared styles */
        .page-left, .page-right {
            width: 50%;
            background: #fffef8;
            position: relative;
            overflow: hidden;
        }

        .page-left {
            border-radius: 8px 0 0 8px;
            border: 3px solid #111;
            border-right: 1.5px solid #111;
            box-shadow: -10px 10px 30px rgba(0,0,0,0.4);
            animation: leftPageIn 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.5s both;
        }

        .page-right {
            border-radius: 0 8px 8px 0;
            border: 3px solid #111;
            border-left: 1.5px solid #111;
            box-shadow: 10px 10px 30px rgba(0,0,0,0.4);
            animation: rightPageIn 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.6s both;
        }

        @keyframes leftPageIn {
            0% { transform: perspective(2500px) rotateY(-40deg); opacity: 0; }
            100% { transform: perspective(2500px) rotateY(0deg); opacity: 1; }
        }
        @keyframes rightPageIn {
            0% { transform: perspective(2500px) rotateY(40deg); opacity: 0; }
            100% { transform: perspective(2500px) rotateY(0deg); opacity: 1; }
        }

        /* Halftone overlay on pages */
        .page-left::before, .page-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(0,0,0,0.04) 1px, transparent 1px);
            background-size: 14px 14px;
            pointer-events: none;
            z-index: 1;
        }

        /* Paper texture lines */
        .page-left::after, .page-right::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 28px,
                rgba(0,0,0,0.03) 28px,
                rgba(0,0,0,0.03) 29px
            );
            pointer-events: none;
        }

        /* ── LEFT PAGE: Illustration ── */
        .left-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
        }

        .page-number-left {
            position: absolute;
            bottom: 1.5rem;
            left: 2rem;
            font-family: 'Caveat', cursive;
            font-size: 0.9rem;
            color: #bbb;
        }

        .comic-title-label {
            font-family: 'Bangers', cursive;
            font-size: 0.75rem;
            letter-spacing: 4px;
            color: #999;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .comic-title {
            font-family: 'Bangers', cursive;
            font-size: 3.8rem;
            line-height: 1.05;
            color: #111;
            margin-bottom: 1.5rem;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.08);
        }

        /* Speech bubble */
        .speech-bubble {
            position: relative;
            background: #fff;
            border: 3px solid #111;
            border-radius: 20px;
            padding: 1rem 1.25rem;
            max-width: 320px;
            box-shadow: 4px 4px 0 #111;
            margin-bottom: 2rem;
        }
        .speech-bubble::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 35px;
            width: 0; height: 0;
            border: 9px solid transparent;
            border-top-color: #111;
        }
        .speech-bubble::before {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 38px;
            width: 0; height: 0;
            border: 6px solid transparent;
            border-top-color: #fff;
            z-index: 1;
        }
        .bubble-text {
            font-family: 'Caveat', cursive;
            font-size: 1.15rem;
            color: #111;
            line-height: 1.4;
        }

        /* Action lines behind title */
        .action-lines {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            pointer-events: none;
            z-index: 0;
        }
        .action-lines::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-conic-gradient(
                rgba(0,0,0,0.03) 0deg 5deg,
                transparent 5deg 10deg
            );
            border-radius: 50%;
        }

        .bottom-deco {
            font-family: 'Caveat', cursive;
            font-size: 1rem;
            color: #aaa;
            position: absolute;
            bottom: 1.5rem;
            right: 2rem;
        }

        /* ── RIGHT PAGE: Login Form ── */
        .right-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
        }

        .page-number-right {
            position: absolute;
            bottom: 1.5rem;
            right: 2rem;
            font-family: 'Caveat', cursive;
            font-size: 0.9rem;
            color: #bbb;
        }

        .login-panel {
            width: 100%;
            max-width: 340px;
        }

        .access-tag {
            display: inline-block;
            font-family: 'Bangers', cursive;
            font-size: 0.7rem;
            letter-spacing: 3px;
            background: #111;
            color: #fff;
            padding: 0.25rem 0.75rem;
            margin-bottom: 1.25rem;
        }

        .login-title {
            font-family: 'Bangers', cursive;
            font-size: 2.8rem;
            color: #111;
            line-height: 1.05;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-family: 'Caveat', cursive;
            font-size: 1.1rem;
            color: #888;
            margin-bottom: 2rem;
            line-height: 1.4;
        }

        /* Auth notice */
        .auth-notice {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            margin-bottom: 1.5rem;
            font-family: 'Caveat', cursive;
            font-size: 1.05rem;
            color: #555;
        }

        /* Google button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: #111;
            color: #fff;
            border: 3px solid #111;
            border-radius: 4px;
            font-family: 'Caveat', cursive;
            font-size: 1.3rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 5px 5px 0 #333;
        }
        .btn-google:hover {
            transform: translate(-3px, -3px);
            box-shadow: 8px 8px 0 #444;
            background: #222;
        }
        .btn-google:active {
            transform: translate(0, 0);
            box-shadow: 2px 2px 0 #333;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 2px;
            background: #ddd;
        }
        .divider span {
            font-family: 'Caveat', cursive;
            font-size: 1rem;
            color: #aaa;
        }

        .btn-dev {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: transparent;
            color: #aaa;
            border: 2px dashed #ccc;
            border-radius: 4px;
            font-family: 'Caveat', cursive;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-dev:hover {
            border-color: #888;
            color: #555;
            background: #fafafa;
        }

        .system-status {
            position: absolute;
            bottom: 1.5rem;
            left: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Caveat', cursive;
            font-size: 0.9rem;
            color: #aaa;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            background: #111;
            border-radius: 50%;
            animation: statusBlink 2s infinite;
        }
        @keyframes statusBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* ── Comic strip at bottom of left page ── */
        .mini-strip {
            display: flex;
            gap: 6px;
            margin-top: 1.5rem;
        }
        .mini-panel {
            flex: 1;
            height: 60px;
            border: 2px solid #111;
            background: #f8f8f8;
            position: relative;
            overflow: hidden;
        }
        .mini-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(0,0,0,0.06) 1px, transparent 1px);
            background-size: 6px 6px;
        }
        .mini-panel:nth-child(1) { border-radius: 4px 0 0 4px; }
        .mini-panel:nth-child(3) { border-radius: 0 4px 4px 0; }

        /* ── Page turn transition for navigation ── */
        .page-turn-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
            background: linear-gradient(to left, rgba(0,0,0,0) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.95) 100%);
        }
        .page-turn-overlay.active { opacity: 1; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .open-book {
                flex-direction: column;
                width: 95vw;
                height: 95vh;
                max-height: none;
            }
            .page-left, .page-right { width: 100%; height: 50%; }
            .page-left { border-radius: 8px 8px 0 0; border-right: 3px solid #111; border-bottom: 1.5px solid #111; }
            .page-right { border-radius: 0 0 8px 8px; border-left: 3px solid #111; border-top: 1.5px solid #111; }
            .comic-title { font-size: 2.5rem; }
            .login-title { font-size: 2rem; }
            .left-content, .right-content { padding: 1.5rem; }
            .book-on-table { width: 220px; height: 290px; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════
     SCENE 1: Closed Book on Table
═══════════════════════════════════════ -->
<div class="table-scene" id="tableScene" onclick="openBook()">
    <div class="click-hint">tap to open the book</div>

    <!-- Floating dust particles -->
    <div class="dust" style="top:15%;left:20%;animation:dustFloat 6s linear infinite;"></div>
    <div class="dust" style="top:70%;left:75%;animation:dustFloat 8s linear infinite 2s;"></div>
    <div class="dust" style="top:40%;left:60%;animation:dustFloat 7s linear infinite 1s;"></div>
    <div class="dust" style="top:25%;left:80%;animation:dustFloat 9s linear infinite 3s;"></div>
    <div class="dust" style="top:80%;left:30%;animation:dustFloat 5s linear infinite 0.5s;"></div>
</div>

<!-- ═══════════════════════════════════════
     SCENE 2: Open Book — Login
═══════════════════════════════════════ -->
<div class="open-book-scene" id="openBookScene">
    <div class="open-book">

        <!-- LEFT PAGE -->
        <div class="page-left">
            <div class="action-lines"></div>
            <div class="left-content">
                <div class="comic-title-label">Issue #01 — Origins</div>
                <h1 class="comic-title">
                    CALLSENSE
                </h1>

                <div class="speech-bubble">
                    <div class="bubble-text">
                        "System ready, Agent.<br>Authenticate to begin<br>your mission."
                    </div>
                </div>

                <div class="mini-strip">
                    <div class="mini-panel"></div>
                    <div class="mini-panel"></div>
                    <div class="mini-panel"></div>
                </div>
            </div>
            <div class="page-number-left">— 01</div>
            <div class="bottom-deco">✦ classified ✦</div>
        </div>

        <!-- RIGHT PAGE -->
        <div class="page-right">
            <div class="right-content">
                <div class="login-panel">
                    <div class="access-tag">// ACCESS PORTAL</div>
                    <h2 class="login-title">Enter the<br>Command Center</h2>
                    <p class="login-subtitle">Authorized personnel only. Sign in with your Google account to continue the story.</p>

                    <div class="auth-notice">
                        🔒 Clearance required to proceed
                    </div>

                    <a href="{{ url('/auth/google') }}" class="btn-google" id="googleBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#fff"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#ccc"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#999"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#ddd"/>
                        </svg>
                        Sign In with Google
                    </a>

                    <div class="divider"><span>or</span></div>
                    <a href="{{ url('/dashboard') }}" class="btn-dev" id="devBtn">⚡ Dev Access — Skip Login</a>
                </div>

                <div class="system-status">
                    <div class="status-dot"></div>
                    All systems operational
                </div>
            </div>
            <div class="page-number-right">02 —</div>
        </div>

    </div>
</div>

<!-- Page turn overlay for navigation -->
<div class="page-turn-overlay" id="pageTurnOverlay"></div>

<style>
    @keyframes dustFloat {
        0%   { transform: translateY(0) translateX(0); opacity: 0; }
        20%  { opacity: 0.6; }
        80%  { opacity: 0.3; }
        100% { transform: translateY(-120px) translateX(40px); opacity: 0; }
    }
</style>

<script>
    let bookOpened = false;
    function openBook() {
        if (bookOpened) return;
        bookOpened = true;

        const tableScene = document.getElementById('tableScene');
        const openBookScene = document.getElementById('openBookScene');

        // Zoom into the book
        tableScene.classList.add('zoom-out');

        // Show the open book after zoom
        setTimeout(() => {
            openBookScene.classList.add('visible');
        }, 400);
    }

    // Page turn transition for links
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#openBookScene a[href]').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('mailto')) return;

            link.addEventListener('click', function(e) {
                const dest = this.href;
                if (dest === window.location.href) return;
                e.preventDefault();
                document.getElementById('pageTurnOverlay').classList.add('active');

                // Close the book animation
                const leftPage = document.querySelector('.page-left');
                const rightPage = document.querySelector('.page-right');
                if (leftPage) leftPage.style.animation = 'leftPageIn 0.5s reverse forwards';
                if (rightPage) rightPage.style.animation = 'rightPageIn 0.5s reverse forwards';

                setTimeout(() => {
                    window.location.href = dest;
                }, 500);
            });
        });
    });
</script>
</body>
</html>
