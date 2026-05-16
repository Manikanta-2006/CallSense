@extends('layouts.app')
@section('title', 'Critical Alerts — CallSense')
@section('page-number', 'PAGE 4 OF 6')
@section('page-quote', 'ESCALATE IMMEDIATELY!')

@section('styles')
<style>
    .page { padding:1.2rem 1.5rem; height:100%; overflow-y:auto; }

    .chapter-header {
        padding-bottom:1rem; margin-bottom:1rem;
        border-bottom:4px solid var(--panel-border); position:relative;
        display:flex; align-items:flex-start; justify-content:space-between;
    }
    .chapter-header::after {
        content:''; position:absolute; bottom:-4px; left:0;
        width:100%; height:4px;
        background:repeating-linear-gradient(90deg, var(--panel-border) 0px, var(--panel-border) 8px, transparent 8px, transparent 12px);
    }
    .chapter-num { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:4px; color:#aaa; }
    .chapter-title { font-family:'Bangers',cursive; font-size:2rem; color:var(--text-color); letter-spacing:3px; line-height:1; }
    .chapter-sub { font-family:'Caveat',cursive; font-size:0.85rem; color:#999; }

    .alert-count-badge {
        font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:2px;
        border:2px solid var(--panel-border); padding:0.25rem 0.6rem;
        display:flex; align-items:center; gap:0.3rem;
    }

    .success-banner {
        border:3px solid var(--panel-border); background:var(--accent); color:var(--page-bg);
        padding:0.6rem 0.9rem; margin-bottom:0.8rem;
        font-family:'Bangers',cursive; font-size:0.8rem; letter-spacing:2px;
        animation:slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from{transform:translateY(-10px);opacity:0} to{transform:translateY(0);opacity:1} }

    /* ── STATS STRIP ── */
    .alert-stats {
        display:grid; grid-template-columns:repeat(4,1fr); gap:0;
        border:3px solid var(--panel-border); margin-bottom:1rem;
    }
    .alert-stat {
        padding:0.5rem; text-align:center;
        border-right:2px solid #eee; position:relative;
    }
    .alert-stat:last-child { border-right:none; }
    .alert-stat::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .alert-stat > * { position:relative; z-index:1; }
    .alert-stat-val { font-family:'Bangers',cursive; font-size:1.8rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .alert-stat-lbl { font-family:'Caveat',cursive; font-size:0.7rem; color:#888; }
    .alert-stat-tag { font-family:'Bangers',cursive; font-size:0.4rem; letter-spacing:2px; color:#bbb; }

    /* ── THREAT BAR ── */
    .threat-bar {
        border:4px solid var(--panel-border); padding:0.8rem 1.2rem;
        display:flex; align-items:center; gap:1rem;
        background:var(--accent); color:var(--page-bg);
        margin-bottom:1.2rem;
        position:relative; overflow:hidden;
        animation:threatPulse 2s infinite;
    }
    @keyframes threatPulse {
        0%,100% { box-shadow:0 0 0 rgba(0,0,0,0); }
        50% { box-shadow:0 0 20px rgba(0,0,0,0.3); }
    }
    .threat-bar::before {
        content:''; position:absolute; inset:0;
        background:repeating-linear-gradient(-45deg, transparent, transparent 4px, rgba(255,255,255,0.03) 4px, rgba(255,255,255,0.03) 8px);
        pointer-events:none;
    }
    .threat-icon { font-size:1.5rem; flex-shrink:0; position:relative; z-index:1; }
    .threat-info { position:relative; z-index:1; }
    .threat-title { font-family:'Bangers',cursive; font-size:1.1rem; letter-spacing:2px; }
    .threat-sub { font-family:'Caveat',cursive; font-size:0.8rem; color:#999; }
    .threat-level {
        font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:2px;
        padding:0.3rem 0.8rem; border:2px solid var(--page-bg); color:var(--page-bg);
        margin-left:auto; white-space:nowrap; position:relative; z-index:1;
    }

    /* ── FILTER TABS ── */
    .filter-tabs {
        display:flex; gap:0; margin-bottom:1rem;
        border:3px solid var(--panel-border);
    }
    .filter-tab {
        flex:1; padding:0.4rem; text-align:center;
        font-family:'Bangers',cursive; font-size:0.65rem; letter-spacing:2px;
        color:#aaa; cursor:pointer; border-right:2px solid #eee;
        transition:all 0.15s; text-decoration:none;
    }
    .filter-tab:last-child { border-right:none; }
    .filter-tab:hover { background:var(--halftone); color:var(--text-color); }
    .filter-tab.active { background:var(--accent); color:var(--page-bg); }

    /* ── ALERT LIST ── */
    .alert-list { display:flex; flex-direction:column; gap:1rem; }

    .alert-row { display:flex; align-items:flex-start; gap:1rem; }
    .alert-icon-col { flex-shrink:0; text-align:center; width:50px; }
    .alert-icon-col .case-id { font-family:'Bangers',cursive; font-size:0.6rem; color:#aaa; letter-spacing:1px; }
    .alert-icon-col .case-num { font-family:'Bangers',cursive; font-size:1.2rem; color:var(--text-color); display:block; }
    .alert-icon-col .alert-emoji { font-size:2rem; }

    .alert-bubble {
        flex:1; border:3px solid var(--panel-border); padding:1rem;
        background:var(--page-bg); position:relative;
        box-shadow:3px 3px 0 rgba(0,0,0,0.1); overflow:hidden;
    }
    .alert-bubble::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .alert-bubble > * { position:relative; z-index:1; }
    .alert-tail {
        position:absolute; left:-14px; top:16px;
        width:0; height:0;
        border:7px solid transparent; border-right-color:var(--panel-border);
    }
    .alert-tail-inner {
        position:absolute; left:-8px; top:19px;
        width:0; height:0;
        border:4px solid transparent; border-right-color:var(--page-bg);
    }

    .alert-top { display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.3rem; }
    .alert-tag { font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:3px; color:#aaa; }
    .severity-badge {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid var(--panel-border);
    }
    .severity-critical { background:#333; color:#fff; }
    .severity-high { background:#666; color:#fff; }
    .severity-medium { background:#aaa; color:#fff; }
    .severity-low { background:#ddd; }

    .status-badge {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid #ccc;
    }
    .status-pending { border-color:#333; color:#333; }
    .status-investigating { border-color:#888; color:#888; background:#f5f5f5; }
    .status-resolved { border-color:#aaa; color:#aaa; background:#f0f0f0; text-decoration:line-through; }

    .alert-text { font-family:'Caveat',cursive; font-size:0.95rem; color:var(--text-color); line-height:1.4; margin-bottom:0.6rem; }

    .alert-footer { display:flex; align-items:center; justify-content:space-between; border-top:2px solid #eee; padding-top:0.5rem; flex-wrap:wrap; gap:0.4rem; }
    .score-block {}
    .score-val { font-family:'Bangers',cursive; font-size:1.4rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .score-label { font-family:'Caveat',cursive; font-size:0.7rem; color:#aaa; }

    .alert-actions { display:flex; gap:0.3rem; flex-wrap:wrap; }
    .action-btn {
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px;
        padding:0.2rem 0.5rem; border:2px solid var(--panel-border);
        background:none; cursor:pointer; transition:all 0.15s; color:var(--text-color);
    }
    .action-btn:hover { background:var(--accent); color:var(--page-bg); }
    .action-btn.escalate { background:var(--accent); color:var(--page-bg); border-color:var(--panel-border); }
    .action-btn.escalate:hover { background:var(--page-bg); color:var(--text-color); }

    .alert-date { font-family:'Caveat',cursive; font-size:0.75rem; color:#aaa; }
    .resolved-info {
        font-family:'Caveat',cursive; font-size:0.75rem; color:#999;
        margin-top:0.3rem; padding-top:0.3rem; border-top:1px solid #eee;
    }

    .empty-state {
        text-align:center; padding:3rem;
        color:#bbb; font-family:'Caveat',cursive; font-size:1.1rem;
    }
    .empty-icon { font-size:2.5rem; margin-bottom:0.5rem; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div>
            <div class="chapter-num">CHAPTER 04</div>
            <div class="chapter-title">CRITICAL ALERTS</div>
            <div class="chapter-sub">// Emergency response — operational alert center</div>
        </div>
        <div class="alert-count-badge">
            ⚠ {{ $alerts->where('status', '!=', 'resolved')->count() }} ACTIVE
        </div>
    </div>

    @if(session('success'))
        <div class="success-banner">⚡ {{ session('success') }}</div>
    @endif

    {{-- Real-time stats --}}
    <div class="alert-stats anim-reveal" style="animation-delay: 0.2s;">
        <div class="alert-stat">
            <div class="alert-stat-tag">// TOTAL</div>
            <div class="alert-stat-val">{{ $alerts->count() }}</div>
            <div class="alert-stat-lbl">Alerts</div>
        </div>
        <div class="alert-stat">
            <div class="alert-stat-tag">// PENDING</div>
            <div class="alert-stat-val">{{ $alerts->where('status', 'pending')->count() }}</div>
            <div class="alert-stat-lbl">Awaiting</div>
        </div>
        <div class="alert-stat">
            <div class="alert-stat-tag">// INVESTIGATING</div>
            <div class="alert-stat-val">{{ $alerts->where('status', 'investigating')->count() }}</div>
            <div class="alert-stat-lbl">In Progress</div>
        </div>
        <div class="alert-stat">
            <div class="alert-stat-tag">// RESOLVED</div>
            <div class="alert-stat-val">{{ $alerts->where('status', 'resolved')->count() }}</div>
            <div class="alert-stat-lbl">Closed</div>
        </div>
    </div>

    @if($alerts->whereIn('status', ['pending', 'investigating'])->count() > 0)
    <div class="threat-bar anim-pop" style="animation-delay: 0.3s;">
        <div class="threat-icon">⚠️</div>
        <div class="threat-info">
            <div class="threat-title">ACTIVE ALERTS DETECTED!</div>
            <div class="threat-sub">{{ $alerts->whereIn('status', ['pending', 'investigating'])->count() }} case(s) require attention</div>
        </div>
        <div class="threat-level">THREAT: {{ $alerts->where('severity', 'critical')->count() > 0 ? 'CRITICAL' : 'HIGH' }}</div>
    </div>
    @endif

    <div class="alert-list anim-slide-up" style="animation-delay: 0.4s;">
        @forelse($alerts as $alert)
            @php
                $call = $alert->call;
                $severityClass = 'severity-' . $alert->severity;
                $statusClass = 'status-' . $alert->status;
                $emoji = match($alert->severity) {
                    'critical' => '🚨',
                    'high' => '😡',
                    'medium' => '⚡',
                    default => '📋',
                };
            @endphp
            <div class="alert-row">
                <div class="alert-icon-col">
                    <div class="case-id">ALERT<span class="case-num">#{{ $alert->id }}</span></div>
                    <div class="alert-emoji">{{ $emoji }}</div>
                </div>
                <div class="alert-bubble">
                    <div class="alert-tail"></div>
                    <div class="alert-tail-inner"></div>

                    <div class="alert-top">
                        <span class="alert-tag">// {{ strtoupper($alert->type) }}</span>
                        <span class="severity-badge {{ $severityClass }}">{{ strtoupper($alert->severity) }}</span>
                        <span class="status-badge {{ $statusClass }}">{{ strtoupper($alert->status) }}</span>
                    </div>

                    <div class="alert-text">
                        {{ $alert->description ?? 'No description' }}
                        @if($call)
                            <br><em style="font-size:0.85rem;color:#888;">"{{ \Illuminate\Support\Str::limit($call->transcript, 120) }}"</em>
                        @endif
                    </div>

                    <div class="alert-footer">
                        <div class="score-block">
                            @if($call)
                                <div class="score-val">{{ number_format($call->sentiment_score, 2) }}</div>
                                <div class="score-label">Sentiment Score</div>
                            @endif
                        </div>

                        <div class="alert-actions">
                            @if($alert->status === 'pending')
                                <form method="POST" action="{{ route('alerts.status', $alert) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="investigating">
                                    <button type="submit" class="action-btn">🔍 INVESTIGATE</button>
                                </form>
                                <form method="POST" action="{{ route('alerts.escalate', $alert) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="action-btn escalate">⚡ ESCALATE</button>
                                </form>
                            @endif
                            @if($alert->status === 'investigating')
                                <form method="POST" action="{{ route('alerts.status', $alert) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="resolved">
                                    <input type="hidden" name="resolution_notes" value="Resolved by agent review.">
                                    <button type="submit" class="action-btn">✓ RESOLVE</button>
                                </form>
                            @endif
                            @if($alert->status === 'resolved')
                                <span style="font-family:'Caveat',cursive;font-size:0.75rem;color:#aaa;">✓ Resolved</span>
                            @endif
                        </div>

                        <div class="alert-date">{{ $alert->created_at->diffForHumans() }}</div>
                    </div>

                    {{-- Resolution history --}}
                    @if($alert->status === 'resolved' && $alert->resolved_at)
                        <div class="resolved-info">
                            ✓ Resolved by {{ $alert->resolver?->name ?? 'Agent' }}
                            on {{ $alert->resolved_at->format('d M Y H:i') }}
                            @if($alert->resolution_notes)
                                — {{ $alert->resolution_notes }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            {{-- Fallback to negative calls if no alerts exist yet --}}
            @if(isset($negativeCalls) && $negativeCalls->count() > 0)
                @foreach($negativeCalls as $call)
                <div class="alert-row">
                    <div class="alert-icon-col">
                        <div class="case-id">CASE<span class="case-num">#{{ $call->id }}</span></div>
                        <div class="alert-emoji">😡</div>
                    </div>
                    <div class="alert-bubble">
                        <div class="alert-tail"></div>
                        <div class="alert-tail-inner"></div>
                        <div class="alert-tag">// ⚠ NEGATIVE SENTIMENT DETECTED</div>
                        <div class="alert-text">"{{ \Illuminate\Support\Str::limit($call->transcript, 180) }}"</div>
                        <div class="alert-footer">
                            <div class="score-block">
                                <div class="score-val">{{ number_format($call->sentiment_score, 2) }}</div>
                                <div class="score-label">Sentiment Score</div>
                            </div>
                            <form method="POST" action="{{ route('alerts.create', $call) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="action-btn escalate">⚡ CREATE ALERT</button>
                            </form>
                            <div class="alert-date">{{ $call->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state"><div class="empty-icon">✅</div>No critical alerts. All missions stable.</div>
            @endif
        @endforelse
    </div>
</div>
@endsection
