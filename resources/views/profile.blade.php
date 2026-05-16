@extends('layouts.app')
@section('title', 'Agent Profile — CallSense')
@section('page-number', 'PAGE 5 OF 6')
@section('page-quote', 'Identity verified. Welcome back, Agent.')

@section('styles')
<style>
    .page { padding:1rem 1.3rem; overflow-y:auto; height:100%; }

    /* ── CHAPTER HEADER ── */
    .chapter-header {
        padding-bottom:0.8rem; margin-bottom:0.9rem;
        border-bottom:4px solid var(--panel-border); position:relative;
        display:flex; align-items:flex-start; justify-content:space-between;
    }
    .chapter-header::after {
        content:''; position:absolute; bottom:-4px; left:0;
        width:100%; height:4px;
        background:repeating-linear-gradient(90deg, #000 0px, #000 8px, transparent 8px, transparent 12px);
    }
    .chapter-num { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:4px; color:#aaa; }
    .chapter-title { font-family:'Bangers',cursive; font-size:1.9rem; color:var(--text-color); letter-spacing:3px; line-height:1; }
    .chapter-sub { font-family:'Caveat',cursive; font-size:0.82rem; color:#999; }
    .online-badge {
        display:flex; align-items:center; gap:0.4rem;
        font-family:'Bangers',cursive; font-size:0.65rem; letter-spacing:2px;
        border:2px solid var(--panel-border); padding:0.25rem 0.6rem;
    }
    .online-dot { width:6px; height:6px; background:var(--accent); border-radius:50%; animation:blink 1.5s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.15} }

    .success-banner {
        border:3px solid var(--panel-border); background:var(--accent); color:var(--page-bg);
        padding:0.5rem 0.8rem; margin-bottom:0.8rem;
        font-family:'Bangers',cursive; font-size:0.75rem; letter-spacing:2px;
        animation:slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from{transform:translateY(-10px);opacity:0} to{transform:translateY(0);opacity:1} }

    /* ── PROFILE HERO ── */
    .profile-hero {
        display:grid; grid-template-columns:auto 1fr; gap:0;
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .profile-hero::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }

    .avatar-panel {
        width:110px; border-right:3px solid var(--panel-border);
        background:var(--page-bg);
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        padding:0.8rem 0.6rem; gap:0.4rem;
        position:relative;
    }
    .avatar-panel::after {
        content:''; position:absolute; inset:0;
        background:repeating-conic-gradient(rgba(0,0,0,0.02) 0deg 3deg, transparent 3deg 6deg);
        pointer-events:none;
    }
    .avatar-circle {
        width:70px; height:70px; border:3px solid var(--panel-border);
        border-radius:50%; overflow:hidden;
        position:relative; z-index:1;
        display:flex; align-items:center; justify-content:center;
        background:var(--page-bg); font-size:2rem;
    }
    .avatar-circle img { width:100%; height:100%; object-fit:cover; filter:grayscale(1) contrast(1.1); }
    .avatar-name {
        font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:1px;
        color:var(--text-color); text-align:center; position:relative; z-index:1;
    }
    .avatar-role {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:2px;
        color:#aaa; text-align:center; position:relative; z-index:1;
    }

    .profile-info {
        padding:0.8rem 1rem;
        display:flex; flex-direction:column; gap:0.4rem;
        position:relative; z-index:1;
    }
    .info-row { display:flex; align-items:baseline; gap:0.4rem; }
    .info-label {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:2px; color:#bbb;
        min-width:60px;
    }
    .info-val { font-family:'Caveat',cursive; font-size:0.9rem; color:var(--text-color); }
    .info-val strong { font-weight:700; color:var(--accent); }

    /* ── EDIT PROFILE ── */
    .edit-form {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .edit-form::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .edit-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:3px; color:#aaa;
        background:var(--accent); color:var(--page-bg);
        position:relative; z-index:1;
    }
    .edit-body { padding:0.6rem 0.8rem; position:relative; z-index:1; }
    .edit-row { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem; }
    .edit-label { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:2px; color:#999; min-width:70px; }
    .edit-input {
        flex:1; padding:0.35rem 0.5rem;
        border:2.5px solid #ccc; background:var(--page-bg);
        font-family:'Caveat',cursive; font-size:0.9rem; color:var(--text-color);
        outline:none; transition:border-color 0.15s;
    }
    .edit-input:focus { border-color:var(--accent); }
    .edit-save {
        padding:0.3rem 0.8rem; background:var(--accent); color:var(--page-bg);
        border:2px solid var(--panel-border); font-family:'Bangers',cursive;
        font-size:0.7rem; letter-spacing:2px; cursor:pointer; transition:all 0.15s;
    }
    .edit-save:hover { background:var(--page-bg); color:var(--text-color); }

    /* ── STATS GRID ── */
    .stats-panel {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .stats-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .stats-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:3px; color:#aaa;
        position:relative; z-index:1;
    }
    .stats-grid {
        display:grid; grid-template-columns:repeat(4,1fr); gap:0;
        position:relative; z-index:1;
    }
    .stat-box {
        padding:0.7rem; text-align:center; border-right:2px solid #eee;
        position:relative;
    }
    .stat-box:last-child { border-right:none; }
    .stat-box-val { font-family:'Bangers',cursive; font-size:1.8rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .stat-box-lbl { font-family:'Caveat',cursive; font-size:0.72rem; color:#888; }
    .stat-box-tag { font-family:'Bangers',cursive; font-size:0.42rem; letter-spacing:2px; color:#ccc; }

    /* ── API USAGE ── */
    .api-panel {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .api-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .api-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:3px; color:#aaa;
        position:relative; z-index:1;
    }
    .api-grid {
        display:grid; grid-template-columns:repeat(3,1fr); gap:0;
        position:relative; z-index:1;
    }
    .api-box {
        padding:0.7rem; text-align:center; border-right:2px solid #eee;
    }
    .api-box:last-child { border-right:none; }
    .api-box-val { font-family:'Bangers',cursive; font-size:1.6rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .api-box-lbl { font-family:'Caveat',cursive; font-size:0.72rem; color:#888; }

    /* ── LOGIN HISTORY ── */
    .login-panel {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .login-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .login-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:3px; color:#aaa;
        position:relative; z-index:1;
    }
    .login-list { padding:0.5rem 0.8rem; position:relative; z-index:1; }
    .login-item {
        display:flex; align-items:center; gap:0.5rem;
        padding:0.3rem 0; border-bottom:1px solid #f0f0f0;
        font-family:'Caveat',cursive; font-size:0.82rem; color:var(--text-color);
    }
    .login-item:last-child { border-bottom:none; }
    .login-action {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px;
        padding:0.05rem 0.3rem; border:1.5px solid #ddd;
    }
    .login-action.login { color:#333; }
    .login-action.logout { color:#aaa; }
    .login-ip { font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px; color:#bbb; margin-left:auto; }
    .login-time { font-family:'Caveat',cursive; font-size:0.75rem; color:#999; }

    /* ── ACTIVITY TIMELINE ── */
    .timeline-panel {
        border:3px solid var(--panel-border); margin-bottom:0.9rem;
        position:relative; overflow:hidden;
    }
    .timeline-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .timeline-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:3px; color:#aaa;
        position:relative; z-index:1;
    }
    .timeline-list { padding:0.6rem 0.8rem; display:flex; flex-direction:column; gap:0.5rem; position:relative; z-index:1; }
    .timeline-item { display:flex; align-items:flex-start; gap:0.6rem; }
    .timeline-dot {
        width:8px; height:8px; border:2px solid var(--panel-border); border-radius:50%;
        background:#fff; flex-shrink:0; margin-top:4px;
    }
    .timeline-dot.active { background:var(--accent); }
    .timeline-content { flex:1; }
    .timeline-action { font-family:'Caveat',cursive; font-size:0.85rem; color:var(--text-color); line-height:1.2; }
    .timeline-time { font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px; color:#bbb; }
    .timeline-connector { width:2px; height:12px; background:#eee; margin-left:3px; }

    /* speech bubble */
    .agent-speech {
        border:3px solid var(--panel-border); border-radius:14px;
        padding:0.6rem 0.9rem; margin-bottom:0.8rem;
        position:relative; background:var(--page-bg);
        box-shadow:3px 3px 0 rgba(0,0,0,0.08);
    }
    .agent-speech::after {
        content:''; position:absolute;
        bottom:-12px; left:1.5rem;
        border:6px solid transparent; border-top-color:var(--panel-border);
    }
    .agent-speech::before {
        content:''; position:absolute;
        bottom:-7px; left:1.7rem;
        border:4px solid transparent; border-top-color:var(--page-bg);
        z-index:1;
    }
    .speech-tag { font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:3px; color:#aaa; }
    .speech-text { font-family:'Caveat',cursive; font-size:0.9rem; color:var(--text-color); line-height:1.3; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div>
            <div class="chapter-num">CHAPTER 05</div>
            <div class="chapter-title">AGENT PROFILE</div>
            <div class="chapter-sub">// Identity clearance & performance stats</div>
        </div>
        <div class="online-badge"><div class="online-dot"></div>ACTIVE</div>
    </div>

    @if(session('success'))
        <div class="success-banner">⚡ {{ session('success') }}</div>
    @endif

    {{-- Agent speech bubble --}}
    <div class="agent-speech anim-bubble" style="animation-delay: 0.2s;">
        <div class="speech-tag">// AGENT KAITO</div>
        <div class="speech-text">"Agent {{ $user->name ?? 'Unknown' }}, your mission record speaks for itself. Keep the sentiment scores green!"</div>
    </div>

    {{-- Profile Hero — REAL Auth::user() data --}}
    <div class="profile-hero anim-pop" style="animation-delay: 0.4s;">
        <div class="avatar-panel">
            <div class="avatar-circle">
                @if($user->avatar ?? false)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                @else
                    <span>🕵</span>
                @endif
            </div>
            <div class="avatar-name">{{ Str::words($user->name ?? 'Agent', 1, '') }}</div>
            <div class="avatar-role">// ANALYST</div>
        </div>
        <div class="profile-info">
            <div class="info-row">
                <span class="info-label">NAME</span>
                <span class="info-val"><strong>{{ $user->name ?? 'Unknown Agent' }}</strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">EMAIL</span>
                <span class="info-val">{{ $user->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">ROLE</span>
                <span class="info-val">Sentiment Analyst</span>
            </div>
            <div class="info-row">
                <span class="info-label">STATUS</span>
                <span class="info-val"><strong>● Active Duty</strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">SINCE</span>
                <span class="info-val">{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
            </div>
        </div>
    </div>

    {{-- PROFILE EDITING — update avatar and display name --}}
    <div class="edit-form anim-pop" style="animation-delay: 0.45s;">
        <div class="edit-header">✏️ EDIT PROFILE</div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="edit-body">
            @csrf
            <div class="edit-row">
                <span class="edit-label">NAME</span>
                <input type="text" name="name" class="edit-input" value="{{ $user->name }}" required>
            </div>
            <div class="edit-row">
                <span class="edit-label">AVATAR</span>
                <input type="file" name="avatar" class="edit-input" accept="image/*" style="padding:0.2rem;">
                <button type="submit" class="edit-save">💾 SAVE</button>
            </div>
        </form>
    </div>

    {{-- Activity Analytics — REAL queries --}}
    <div class="stats-panel anim-pop" style="animation-delay: 0.5s;">
        <div class="stats-header">// PERFORMANCE METRICS</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-box-tag">// ANALYZED</div>
                <div class="stat-box-val">{{ $totalAnalyzed }}</div>
                <div class="stat-box-lbl">Calls</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-tag">// RESOLVED</div>
                <div class="stat-box-val">{{ $alertsHandled }}</div>
                <div class="stat-box-lbl">Alerts</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-tag">// SATISFACTION</div>
                <div class="stat-box-val">{{ $avgSatisfaction }}%</div>
                <div class="stat-box-lbl">Positive Rate</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-tag">// AVG SCORE</div>
                <div class="stat-box-val">{{ number_format($avgScore, 2) }}</div>
                <div class="stat-box-lbl">Sentiment</div>
            </div>
        </div>
    </div>

    {{-- API Usage Stats — REAL tracked data --}}
    <div class="api-panel anim-pop" style="animation-delay: 0.55s;">
        <div class="api-header">// API USAGE STATS</div>
        <div class="api-grid">
            <div class="api-box">
                <div class="api-box-val">{{ $nlpRequests }}</div>
                <div class="api-box-lbl">NLP Requests</div>
            </div>
            <div class="api-box">
                <div class="api-box-val">{{ $speechRequests }}</div>
                <div class="api-box-lbl">Speech API</div>
            </div>
            <div class="api-box">
                <div class="api-box-val">{{ $totalApiCalls }}</div>
                <div class="api-box-lbl">Total API Calls</div>
            </div>
        </div>
    </div>

    {{-- Login History — REAL tracked data --}}
    <div class="login-panel anim-pop" style="animation-delay: 0.6s;">
        <div class="login-header">// LOGIN HISTORY</div>
        <div class="login-list">
            @forelse($loginHistory as $activity)
                <div class="login-item">
                    <span class="login-action {{ $activity->action }}">{{ strtoupper($activity->action) }}</span>
                    <span class="login-time">{{ $activity->login_time->format('d M Y H:i') }}</span>
                    <span class="login-ip">{{ $activity->ip_address }}</span>
                </div>
            @empty
                <div style="font-family:'Caveat',cursive;font-size:0.85rem;color:#bbb;text-align:center;padding:0.5rem;">
                    No login history recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="timeline-panel anim-pop" style="animation-delay: 0.65s;">
        <div class="timeline-header">// RECENT ACTIVITY LOG</div>
        <div class="timeline-list">
            @forelse($recentCalls as $call)
            <div class="timeline-item">
                <div class="timeline-dot {{ $loop->first ? 'active' : '' }}"></div>
                <div class="timeline-content">
                    <div class="timeline-action">
                        Analyzed call #{{ $call->id }} —
                        <strong>{{ $call->sentiment_label }}</strong>
                        ({{ number_format($call->sentiment_score, 2) }})
                        @if($call->emotion) · {{ $call->emotion }} @endif
                    </div>
                    <div class="timeline-time">{{ $call->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @if(!$loop->last)<div class="timeline-connector"></div>@endif
            @empty
            <div style="font-family:'Caveat',cursive;font-size:0.9rem;color:#bbb;text-align:center;padding:0.5rem;">
                No activity yet. Begin analyzing calls to build your record.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
