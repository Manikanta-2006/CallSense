@extends('layouts.app')
@section('title', 'Settings — CallSense')
@section('page-number', 'PAGE 6 OF 6')
@section('page-quote', 'Configure mission parameters.')

@section('styles')
<style>
    .page { padding:1rem 1.3rem; overflow-y:auto; height:100%; }

    .chapter-header {
        padding-bottom:0.8rem; margin-bottom:0.9rem;
        border-bottom:4px solid var(--panel-border); position:relative;
    }
    .chapter-header::after {
        content:''; position:absolute; bottom:-4px; left:0;
        width:100%; height:4px;
        background:repeating-linear-gradient(90deg, #000 0px, #000 8px, transparent 8px, transparent 12px);
    }
    .chapter-num { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:4px; color:#aaa; }
    .chapter-title { font-family:'Bangers',cursive; font-size:1.9rem; color:var(--text-color); letter-spacing:3px; line-height:1; }
    .chapter-sub { font-family:'Caveat',cursive; font-size:0.82rem; color:#999; }

    /* ── SETTINGS SECTIONS ── */
    .settings-section {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .settings-section::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .section-header {
        border-bottom:3px solid var(--panel-border); padding:0.45rem 0.9rem;
        display:flex; align-items:center; gap:0.5rem;
        background:var(--accent);
        font-family:'Bangers',cursive; font-size:0.65rem; letter-spacing:3px; color:var(--page-bg);
        position:relative; z-index:1;
    }
    .section-icon { font-size:0.9rem; }
    .section-body { padding:0.7rem 0.9rem; position:relative; z-index:1; }

    /* ── SETTING ROWS ── */
    .setting-row {
        display:flex; align-items:center; justify-content:space-between;
        padding:0.45rem 0;
        border-bottom:2px solid #f0f0f0;
    }
    .setting-row:last-child { border-bottom:none; }
    .setting-label { display:flex; flex-direction:column; gap:0.1rem; }
    .setting-name { font-family:'Bangers',cursive; font-size:0.8rem; letter-spacing:1px; color:var(--text-color); }
    .setting-desc { font-family:'Caveat',cursive; font-size:0.75rem; color:#999; }

    /* Toggle switch manga-style */
    .manga-toggle { position:relative; width:44px; height:22px; flex-shrink:0; }
    .manga-toggle input { opacity:0; width:0; height:0; }
    .toggle-slider {
        position:absolute; inset:0;
        background:#ddd; border:2px solid var(--panel-border); cursor:pointer;
        transition:background 0.2s;
    }
    .toggle-slider::before {
        content:''; position:absolute;
        width:14px; height:14px; background:var(--page-bg);
        border:2px solid var(--panel-border); top:2px; left:2px;
        transition:transform 0.2s;
    }
    .manga-toggle input:checked + .toggle-slider { background:var(--accent); }
    .manga-toggle input:checked + .toggle-slider::before { transform:translateX(20px); background:var(--page-bg); }

    /* Number input manga-style */
    .manga-input {
        width:90px; padding:0.35rem 0.5rem;
        border:2.5px solid #ccc; background:var(--page-bg);
        font-family:'Bangers',cursive; font-size:0.9rem; letter-spacing:1px; color:var(--text-color);
        outline:none; transition:border-color 0.15s;
    }
    .manga-input:focus { border-color:#000; }

    /* Save button */
    .btn-save {
        display:inline-flex; align-items:center; gap:0.5rem;
        background:var(--accent); color:var(--page-bg);
        border:3px solid var(--panel-border); border-radius:0;
        font-family:'Bangers',cursive; font-size:0.9rem; letter-spacing:2px;
        padding:0.5rem 1.2rem; cursor:pointer;
        box-shadow:3px 3px 0 #555;
        transition:all 0.15s;
    }
    .btn-save:hover {
        background:var(--page-bg); color:var(--text-color);
        box-shadow:5px 5px 0 #333;
        transform:translate(-2px,-2px);
    }

    /* Threshold visual */
    .threshold-display {
        display:flex; align-items:center; gap:0.5rem; margin-top:0.4rem;
    }
    .threshold-track {
        flex:1; height:6px; background:#eee; border:2px solid var(--panel-border); position:relative;
    }
    .threshold-fill {
        height:100%; background:repeating-linear-gradient(90deg, #ccc 0,#ccc 4px, #aaa 4px, #aaa 8px);
        transition:width 0.3s;
    }
    .threshold-label { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px; color:#aaa; }

    /* Status box */
    .status-box {
        display:flex; align-items:center; gap:0.4rem;
        padding:0.4rem 0.7rem; border:2px solid var(--panel-border); background:var(--page-bg);
        font-family:'Caveat',cursive; font-size:0.85rem; color:var(--text-color);
        margin-bottom:0.5rem;
    }
    .status-indicator { width:7px; height:7px; background:#111; border-radius:50%; animation:blink 2s infinite; flex-shrink:0; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }

    /* Success banner */
    .success-banner {
        border:3px solid var(--panel-border); background:var(--accent); color:var(--page-bg);
        padding:0.6rem 0.9rem; margin-bottom:0.8rem;
        font-family:'Bangers',cursive; font-size:0.8rem; letter-spacing:2px;
        display:flex; align-items:center; gap:0.5rem;
        animation:slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from{transform:translateY(-10px);opacity:0} to{transform:translateY(0);opacity:1} }

    /* API key masked */
    .api-key-row { display:flex; align-items:center; gap:0.4rem; }
    .key-masked {
        font-family:'Bangers',cursive; font-size:0.8rem; letter-spacing:2px; color:#ccc;
        border:2px dashed #ddd; padding:0.3rem 0.6rem; flex:1;
    }
    .key-set { border-color:#000; color:#000; background:#f8f8f8; }
    .key-copy {
        background:none; border:2px solid #ccc; cursor:pointer;
        font-size:0.8rem; padding:0.25rem 0.4rem;
        transition:all 0.15s;
    }
    .key-copy:hover { border-color:#000; }

    /* Queue monitoring */
    .queue-grid {
        display:grid; grid-template-columns:repeat(2,1fr); gap:0;
    }
    .queue-box {
        padding:0.7rem; text-align:center; border-right:2px solid #eee;
    }
    .queue-box:last-child { border-right:none; }
    .queue-val { font-family:'Bangers',cursive; font-size:1.8rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .queue-lbl { font-family:'Caveat',cursive; font-size:0.72rem; color:#888; }
    .queue-status {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px;
        padding:0.1rem 0.3rem; border:1.5px solid #ddd;
        display:inline-block; margin-top:0.2rem;
    }
    .queue-ok { border-color:#333; color:#333; }
    .queue-warn { border-color:#888; color:#888; background:#f5f5f5; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div class="chapter-num">CHAPTER 06</div>
        <div class="chapter-title">SETTINGS</div>
        <div class="chapter-sub">// System configuration & mission parameters</div>
    </div>

    @if(session('success'))
    <div class="success-banner">⚡ {{ session('success') }}</div>
    @endif

    <div class="status-box">
        <div class="status-indicator"></div>
        All systems operational. Logged in as <strong>{{ $user->name ?? 'Agent' }}</strong>
    </div>

    <form method="POST" action="/settings">
        @csrf

        {{-- 1. API Configuration --}}
        <div class="settings-section anim-pop" style="animation-delay: 0.1s;">
            <div class="section-header">
                <span class="section-icon">🔑</span>
                API CONFIGURATION
            </div>
            <div class="section-body">
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">GOOGLE NLP API KEY</span>
                        <span class="setting-desc">Natural Language sentiment analysis</span>
                    </div>
                    <div class="api-key-row">
                        @php $nlpKey = env('GOOGLE_CLOUD_API_KEY'); @endphp
                        <div class="key-masked {{ $nlpKey ? 'key-set' : '' }}">
                            {{ $nlpKey ? '●●●●●●●●' . substr($nlpKey, -4) : '— NOT SET —' }}
                        </div>
                        <button type="button" class="key-copy" title="Configure in .env">{{ $nlpKey ? '✓' : '⚙' }}</button>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">SPEECH-TO-TEXT API KEY</span>
                        <span class="setting-desc">Audio transcription service</span>
                    </div>
                    <div class="api-key-row">
                        @php $speechKey = env('GOOGLE_SPEECH_API_KEY'); @endphp
                        <div class="key-masked {{ $speechKey ? 'key-set' : '' }}">
                            {{ $speechKey ? '●●●●●●●●' . substr($speechKey, -4) : '— NOT SET —' }}
                        </div>
                        <button type="button" class="key-copy" title="Configure in .env">{{ $speechKey ? '✓' : '⚙' }}</button>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">GOOGLE OAUTH CLIENT ID</span>
                        <span class="setting-desc">Authentication provider</span>
                    </div>
                    <div class="api-key-row">
                        @php $oauthId = env('GOOGLE_CLIENT_ID'); @endphp
                        <div class="key-masked {{ $oauthId ? 'key-set' : '' }}">
                            {{ $oauthId ? '●●●●●●●●' . substr($oauthId, -4) : '— NOT SET —' }}
                        </div>
                        <button type="button" class="key-copy">{{ $oauthId ? '✓' : '⚙' }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Sentiment Thresholds --}}
        <div class="settings-section anim-pop" style="animation-delay: 0.2s;">
            <div class="section-header">
                <span class="section-icon">📊</span>
                SENTIMENT THRESHOLDS
            </div>
            <div class="section-body">
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">NEGATIVE TRIGGER LEVEL</span>
                        <span class="setting-desc">Score below this → alert triggered</span>
                    </div>
                    <input type="number" name="negative_threshold" class="manga-input"
                        step="0.1" min="-1" max="0"
                        value="{{ auth()->user()->negative_threshold ?? -0.5 }}"
                        id="thresholdInput"
                        onchange="updateThreshold(this.value)">
                </div>
                <div class="threshold-display">
                    <span class="threshold-label">-1.0</span>
                    <div class="threshold-track">
                        <div class="threshold-fill" id="thresholdFill" style="width:50%"></div>
                    </div>
                    <span class="threshold-label">0.0</span>
                </div>
                <div style="font-family:'Caveat',cursive;font-size:0.78rem;color:#aaa;margin-top:0.3rem;">
                    Current: alerts fire when score &lt; <span id="thresholdDisplay">{{ auth()->user()->negative_threshold ?? -0.5 }}</span>
                </div>
            </div>
        </div>

        {{-- 3. Queue Monitoring — REAL data --}}
        <div class="settings-section anim-pop" style="animation-delay: 0.25s;">
            <div class="section-header">
                <span class="section-icon">⚙️</span>
                QUEUE MONITORING
            </div>
            <div class="section-body">
                <div class="queue-grid">
                    <div class="queue-box">
                        <div class="queue-val">{{ $queueStats['pending_jobs'] ?? 0 }}</div>
                        <div class="queue-lbl">Pending Jobs</div>
                        <div class="queue-status {{ ($queueStats['pending_jobs'] ?? 0) > 0 ? 'queue-warn' : 'queue-ok' }}">
                            {{ ($queueStats['pending_jobs'] ?? 0) > 0 ? 'PROCESSING' : 'IDLE' }}
                        </div>
                    </div>
                    <div class="queue-box">
                        <div class="queue-val">{{ $queueStats['failed_jobs'] ?? 0 }}</div>
                        <div class="queue-lbl">Failed Jobs</div>
                        <div class="queue-status {{ ($queueStats['failed_jobs'] ?? 0) > 0 ? 'queue-warn' : 'queue-ok' }}">
                            {{ ($queueStats['failed_jobs'] ?? 0) > 0 ? 'NEEDS ATTENTION' : 'ALL CLEAR' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Notification Settings --}}
        <div class="settings-section anim-pop" style="animation-delay: 0.3s;">
            <div class="section-header">
                <span class="section-icon">🔔</span>
                NOTIFICATION SETTINGS
            </div>
            <div class="section-body">
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">CRITICAL ALERTS</span>
                        <span class="setting-desc">Show in-app warnings for negative calls</span>
                    </div>
                    <label class="manga-toggle">
                        <input type="checkbox" name="critical_alerts" id="criticalAlerts"
                            {{ (auth()->user()->critical_alerts ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">EMAIL NOTIFICATIONS</span>
                        <span class="setting-desc">Send daily sentiment summary</span>
                    </div>
                    <label class="manga-toggle">
                        <input type="checkbox" name="email_notifications" id="emailNotif"
                            {{ (auth()->user()->email_notifications ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">ESCALATION QUEUE ALERTS</span>
                        <span class="setting-desc">Notify when calls enter escalation</span>
                    </div>
                    <label class="manga-toggle">
                        <input type="checkbox" name="escalation_alerts" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 5. Theme --}}
        <div class="settings-section anim-pop" style="animation-delay: 0.4s;">
            <div class="section-header">
                <span class="section-icon">🎨</span>
                INTERFACE THEME
            </div>
            <div class="section-body">
                <div class="setting-row">
                    <div class="setting-label">
                        <span class="setting-name">MANGA STYLE</span>
                        <span class="setting-desc">Visual theme for the control interface</span>
                    </div>
                    <select name="theme" class="manga-input" style="width:130px;cursor:pointer;">
                        <option value="dark-manga" {{ (auth()->user()->theme ?? 'dark-manga') === 'dark-manga' ? 'selected' : '' }}>Dark Manga</option>
                        <option value="neon-manga" {{ (auth()->user()->theme ?? '') === 'neon-manga' ? 'selected' : '' }}>Neon Manga</option>
                        <option value="mono-comic" {{ (auth()->user()->theme ?? '') === 'mono-comic' ? 'selected' : '' }}>Mono Comic</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:0.6rem;">
            <div class="anim-slide-up" style="animation-delay: 0.5s;">
            <button type="submit" class="btn-save">
                <span class="btn-icon">💾</span> SAVE MISSION CONFIG
            </button>
        </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function updateThreshold(val) {
        const num = parseFloat(val);
        if (isNaN(num)) return;
        const pct = ((num + 1) / 1) * 100;
        document.getElementById('thresholdFill').style.width = pct + '%';
        document.getElementById('thresholdDisplay').textContent = num.toFixed(1);
    }
    // Init
    updateThreshold(document.getElementById('thresholdInput')?.value || '-0.5');
</script>
@endsection
