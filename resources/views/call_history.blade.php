@extends('layouts.app')
@section('title', 'Case Files — CallSense')
@section('page-number', 'PAGE 3 OF 6')
@section('page-quote', 'Mission log complete. All cases filed.')

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

    .export-btn {
        font-family:'Bangers',cursive; font-size:0.65rem; letter-spacing:2px;
        border:2px solid var(--panel-border); padding:0.3rem 0.6rem;
        text-decoration:none; color:var(--text-color); transition:all 0.15s;
        display:flex; align-items:center; gap:0.3rem;
    }
    .export-btn:hover { background:var(--accent); color:var(--page-bg); }

    /* ── SEARCH & FILTER ── */
    .search-panel {
        border:3px solid var(--panel-border); margin-bottom:1rem;
        position:relative; overflow:hidden;
    }
    .search-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .search-header {
        border-bottom:3px solid var(--panel-border); padding:0.4rem 0.8rem;
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:3px; color:#aaa;
        background:var(--accent); color:var(--page-bg);
        position:relative; z-index:1;
    }
    .search-body {
        padding:0.6rem 0.8rem; position:relative; z-index:1;
    }
    .search-row {
        display:flex; gap:0.5rem; margin-bottom:0.5rem; flex-wrap:wrap;
    }
    .search-input {
        flex:1; min-width:150px; padding:0.4rem 0.6rem;
        border:2.5px solid #ccc; background:var(--page-bg);
        font-family:'Caveat',cursive; font-size:0.9rem; color:var(--text-color);
        outline:none; transition:border-color 0.15s;
    }
    .search-input:focus { border-color:var(--accent); }
    .search-input::placeholder { color:#ccc; }

    .filter-select {
        padding:0.4rem 0.5rem; border:2.5px solid #ccc;
        font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:1px;
        color:var(--text-color); background:var(--page-bg);
        cursor:pointer; outline:none;
    }
    .filter-select:focus { border-color:var(--accent); }

    .date-input {
        padding:0.35rem 0.5rem; border:2.5px solid #ccc;
        font-family:'Caveat',cursive; font-size:0.85rem;
        color:var(--text-color); background:var(--page-bg);
        outline:none;
    }
    .date-input:focus { border-color:var(--accent); }

    .search-btn {
        padding:0.4rem 1rem; background:var(--accent); color:var(--page-bg);
        border:2.5px solid var(--panel-border); font-family:'Bangers',cursive;
        font-size:0.75rem; letter-spacing:2px; cursor:pointer;
        transition:all 0.15s;
    }
    .search-btn:hover { background:var(--page-bg); color:var(--text-color); }

    .clear-btn {
        padding:0.4rem 0.8rem; background:none; color:#aaa;
        border:2px solid #ddd; font-family:'Bangers',cursive;
        font-size:0.65rem; letter-spacing:1px; cursor:pointer;
        text-decoration:none; transition:all 0.15s;
    }
    .clear-btn:hover { border-color:#000; color:#000; }

    /* ── STATS ROW ── */
    .stats-row {
        display:grid; grid-template-columns:repeat(4,1fr); gap:0;
        margin-bottom:1rem;
    }
    .stat-cell {
        border:3px solid var(--panel-border); padding:0.7rem; text-align:center;
        background:var(--page-bg); position:relative;
    }
    .stat-cell::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .stat-cell > * { position:relative; z-index:1; }
    .stat-cell-val { font-family:'Bangers',cursive; font-size:2rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .stat-cell-lbl { font-family:'Caveat',cursive; font-size:0.75rem; color:#888; }
    .stat-cell-tag { font-family:'Bangers',cursive; font-size:0.45rem; letter-spacing:2px; color:#bbb; }

    /* ── CASE GRID ── */
    .cases-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1rem; }

    .case-card {
        border:3px solid var(--panel-border); padding:0.9rem; background:var(--page-bg);
        display:flex; flex-direction:column; gap:0.5rem;
        position:relative; overflow:hidden;
        box-shadow:3px 3px 0 rgba(0,0,0,0.08);
        transition:transform 0.15s, box-shadow 0.15s;
    }
    .case-card:hover {
        transform:translate(-2px,-2px);
        box-shadow:5px 5px 0 rgba(0,0,0,0.15);
    }
    .case-card::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
        background-size:8px 8px; pointer-events:none;
    }
    .case-card > * { position:relative; z-index:1; }

    .case-top { display:flex; align-items:flex-start; justify-content:space-between; }
    .case-num { font-family:'Bangers',cursive; font-size:0.8rem; letter-spacing:1px; color:var(--text-color); }
    .case-status { font-family:'Caveat',cursive; font-size:0.75rem; color:#aaa; }
    .case-emotion-badge { display:flex; align-items:center; gap:0.3rem; flex-wrap:wrap; }
    .case-emotion { font-size:1.2rem; }
    .case-badge {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid var(--panel-border);
    }
    .case-emotion-tag {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px;
        padding:0.05rem 0.3rem; border:1.5px solid #ccc; color:#888;
    }

    .case-names {
        font-family:'Caveat',cursive; font-size:0.75rem; color:#999;
        display:flex; gap:0.5rem; flex-wrap:wrap;
    }

    .case-transcript {
        font-family:'Caveat',cursive; font-size:0.85rem; color:var(--text-color);
        line-height:1.4; border-top:2px solid var(--panel-border); padding-top:0.5rem;
    }
    .case-footer {
        display:flex; align-items:center; justify-content:space-between;
        border-top:2px solid #eee; padding-top:0.4rem;
    }
    .case-score { font-family:'Bangers',cursive; font-size:1rem; color:var(--text-color); letter-spacing:1px; }
    .case-score span { font-size:0.6rem; color:#aaa; }
    .case-risk { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px; padding:0.1rem 0.3rem; border:1.5px solid #ddd; }
    .case-date { font-family:'Caveat',cursive; font-size:0.75rem; color:#aaa; }

    .btn-play {
        width:24px; height:24px; border-radius:50%; border:2px solid #ccc;
        background:#fff; color:#888; cursor:pointer;
        display:flex; align-items:center; justify-content:center; transition:all 0.15s;
    }
    .btn-play:hover { border-color:var(--accent); color:var(--accent); }
    .btn-play svg { width:10px; height:10px; }

    .empty-state {
        grid-column:1/-1; text-align:center; padding:3rem;
        color:#bbb; font-family:'Caveat',cursive; font-size:1.1rem;
    }
    .empty-icon { font-size:2.5rem; margin-bottom:0.5rem; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div>
            <div class="chapter-num">CHAPTER 03</div>
            <div class="chapter-title">CASE FILES</div>
            <div class="chapter-sub">// Mission log — full call investigation board</div>
        </div>
        <a href="{{ route('export.csv') }}" class="export-btn" id="exportCsv">📥 EXPORT CSV</a>
    </div>

    {{-- SEARCH & FILTER SYSTEM --}}
    <div class="search-panel anim-pop" style="animation-delay: 0.15s;">
        <div class="search-header">🔍 SEARCH & FILTER</div>
        <form method="GET" action="/history" class="search-body">
            <div class="search-row">
                <input type="text" name="search" class="search-input" placeholder="Search by name, transcript, keyword, or case #…" value="{{ request('search') }}">
                <select name="sentiment" class="filter-select">
                    <option value="">All Sentiments</option>
                    <option value="Positive" {{ request('sentiment') === 'Positive' ? 'selected' : '' }}>Positive</option>
                    <option value="Negative" {{ request('sentiment') === 'Negative' ? 'selected' : '' }}>Negative</option>
                    <option value="Neutral" {{ request('sentiment') === 'Neutral' ? 'selected' : '' }}>Neutral</option>
                </select>
                <select name="emotion" class="filter-select">
                    <option value="">All Emotions</option>
                    <option value="angry" {{ request('emotion') === 'angry' ? 'selected' : '' }}>Angry</option>
                    <option value="frustrated" {{ request('emotion') === 'frustrated' ? 'selected' : '' }}>Frustrated</option>
                    <option value="happy" {{ request('emotion') === 'happy' ? 'selected' : '' }}>Happy</option>
                    <option value="satisfied" {{ request('emotion') === 'satisfied' ? 'selected' : '' }}>Satisfied</option>
                    <option value="neutral" {{ request('emotion') === 'neutral' ? 'selected' : '' }}>Neutral</option>
                </select>
            </div>
            <div class="search-row">
                <input type="date" name="date_from" class="date-input" value="{{ request('date_from') }}" placeholder="From">
                <input type="date" name="date_to" class="date-input" value="{{ request('date_to') }}" placeholder="To">
                @if(isset($agents) && $agents->count() > 0)
                <select name="agent" class="filter-select">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent }}" {{ request('agent') === $agent ? 'selected' : '' }}>{{ $agent }}</option>
                    @endforeach
                </select>
                @endif
                <button type="submit" class="search-btn">⚡ SEARCH</button>
                <a href="/history" class="clear-btn">✕ CLEAR</a>
            </div>
        </form>
    </div>

    <div class="stats-row anim-reveal" style="animation-delay: 0.2s;">
        <div class="stat-cell">
            <div class="stat-cell-tag">// TOTAL</div>
            <div class="stat-cell-val">{{ $calls->count() }}</div>
            <div class="stat-cell-lbl">Cases</div>
        </div>
        <div class="stat-cell">
            <div class="stat-cell-tag">// RESOLVED</div>
            <div class="stat-cell-val">{{ $calls->where('sentiment_label','Positive')->count() }}</div>
            <div class="stat-cell-lbl">Positive</div>
        </div>
        <div class="stat-cell">
            <div class="stat-cell-tag">// CRITICAL</div>
            <div class="stat-cell-val">{{ $calls->where('sentiment_label','Negative')->count() }}</div>
            <div class="stat-cell-lbl">Negative</div>
        </div>
        <div class="stat-cell">
            <div class="stat-cell-tag">// PENDING</div>
            <div class="stat-cell-val">{{ $calls->where('sentiment_label','Neutral')->count() }}</div>
            <div class="stat-cell-lbl">Neutral</div>
        </div>
    </div>

    <div class="cases-grid anim-pop" style="animation-delay: 0.4s;">
        @forelse($calls as $call)
            @php
                $icon = match($call->emotion ?? 'neutral') {
                    'angry' => '🤬',
                    'frustrated' => '😤',
                    'happy' => '😊',
                    'satisfied' => '😌',
                    default => $call->sentiment_label==='Positive'?'😊':($call->sentiment_label==='Negative'?'😡':'😐'),
                };
                $tag=$call->sentiment_label==='Positive'?'Resolved':($call->sentiment_label==='Negative'?'Unresolved':'Pending');
            @endphp
            <div class="case-card">
                <div class="case-top">
                    <div>
                        <div class="case-num">CASE #{{ $call->id }}</div>
                        <div class="case-status">{{ $tag }}</div>
                    </div>
                    <div class="case-emotion-badge">
                        <div class="case-emotion">{{ $icon }}</div>
                        <div class="case-badge">{{ strtoupper($call->sentiment_label) }}</div>
                        @if($call->emotion)
                            <div class="case-emotion-tag">{{ strtoupper($call->emotion) }}</div>
                        @endif
                    </div>
                </div>
                @if($call->customer_name || $call->agent_name)
                    <div class="case-names">
                        @if($call->customer_name)<span>👤 {{ $call->customer_name }}</span>@endif
                        @if($call->agent_name)<span>🎧 {{ $call->agent_name }}</span>@endif
                    </div>
                @endif
                <div class="case-transcript">"{{ \Illuminate\Support\Str::limit($call->transcript, 90) }}"</div>
                <div class="case-footer">
                    <div class="case-score">{{ number_format($call->sentiment_score, 2) }} <span>score</span></div>
                    @if($call->risk_score !== null)
                        <div class="case-risk">RISK: {{ round($call->risk_score) }}%</div>
                    @endif
                    @if($call->audio_path)
                        <button class="btn-play" onclick="togglePlay(this,'{{ asset('storage/'.$call->audio_path) }}')">
                            <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    @endif
                    <div class="case-date">{{ $call->created_at->format('d M Y') }}</div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">🗂️</div>
                @if(request()->hasAny(['search', 'sentiment', 'emotion', 'date_from', 'date_to', 'agent']))
                    No cases match your filters. Try different search criteria.
                @else
                    No case files yet. Analyze a call to start the mission log.
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<audio id="tableAudio" style="display:none;"></audio>
<script>
    let currentBtn=null;const tableAudio=document.getElementById('tableAudio');
    function togglePlay(btn,src){if(currentBtn===btn&&!tableAudio.paused){tableAudio.pause();btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';currentBtn=null;return;}if(currentBtn)currentBtn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';tableAudio.src=src;tableAudio.play();btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';currentBtn=btn;tableAudio.onended=()=>{btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';currentBtn=null;};}
</script>
@endsection
