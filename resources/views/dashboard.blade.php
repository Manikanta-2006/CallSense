@extends('layouts.app')
@section('title', 'Mission Control — CallSense')
@section('page-number', 'PAGE 1 OF 6')
@section('page-quote', 'Incoming call detected! Scanning emotions…')

@section('styles')
<style>
    .page { padding:0; height:100%; display:flex; flex-direction:column; overflow-y:auto; }

    /* ── CHAPTER HEADER ── */
    .chapter-header {
        padding:0.8rem 1.2rem;
        border-bottom:4px solid var(--panel-border);
        display:flex; align-items:center; justify-content:space-between;
        position:relative; flex-shrink:0;
    }
    .chapter-header::after {
        content:''; position:absolute; bottom:-4px; left:0;
        width:100%; height:4px;
        background:repeating-linear-gradient(90deg, #000 0px, #000 8px, transparent 8px, transparent 12px);
    }
    .chapter-num { font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:4px; color:#aaa; }
    .chapter-title { font-family:'Bangers',cursive; font-size:1.7rem; color:var(--text-color); letter-spacing:3px; line-height:1; }
    .chapter-sub { font-family:'Caveat',cursive; font-size:0.78rem; color:#999; }
    .live-indicator {
        display:flex; align-items:center; gap:0.4rem;
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:2px; color:#000;
        border:2px solid #000; padding:0.25rem 0.6rem;
    }
    .live-dot { width:6px; height:6px; background:var(--accent); border-radius:50%; animation:blink 1.5s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.15} }

    /* ── SENTIMENT PERCENTAGES STRIP ── */
    .pct-strip {
        display:grid; grid-template-columns:repeat(3,1fr); gap:0;
        border-bottom:3px solid var(--panel-border); flex-shrink:0;
    }
    .pct-cell {
        padding:0.4rem 0.5rem; text-align:center;
        border-right:2px solid var(--panel-border); position:relative; overflow:hidden;
    }
    .pct-cell:last-child { border-right:none; }
    .pct-cell::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size:7px 7px; pointer-events:none;
    }
    .pct-val { font-family:'Bangers',cursive; font-size:1.6rem; color:var(--text-color); line-height:1; letter-spacing:2px; position:relative; z-index:1; }
    .pct-lbl { font-family:'Caveat',cursive; font-size:0.7rem; color:#888; position:relative; z-index:1; }
    .pct-tag { font-family:'Bangers',cursive; font-size:0.4rem; letter-spacing:2px; color:#ccc; position:relative; z-index:1; }

    /* ── MAIN GRID ── */
    .dash-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        grid-template-rows:auto auto;
        gap:0;
        flex:1;
    }

    /* ── MANGA PANEL ── */
    .m-panel {
        border:3px solid var(--panel-border);
        padding:0.7rem;
        position:relative;
        background:var(--page-bg);
        display:flex; flex-direction:column;
        overflow:hidden;
    }
    .m-panel::before {
        content:'';
        position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.035) 1px, transparent 1px);
        background-size:9px 9px;
        pointer-events:none;
    }
    .m-panel > * { position:relative; z-index:1; }

    .panel-tag { font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:3px; color:#aaa; margin-bottom:0.2rem; }
    .panel-heading { font-family:'Bangers',cursive; font-size:0.95rem; color:var(--text-color); letter-spacing:1px; margin-bottom:0.3rem; }

    /* ── SENTIMENT METER ── */
    .meter-panel { align-items:center; justify-content:center; text-align:center; }
    .meter-panel::after {
        content:''; position:absolute; inset:0;
        background:repeating-conic-gradient(rgba(0,0,0,0.012) 0deg 3deg, transparent 3deg 6deg);
        pointer-events:none;
    }
    .meter-ring { position:relative; width:90px; height:90px; margin:0.2rem auto; }
    .meter-ring svg { position:absolute; inset:0; transform:rotate(-90deg); }
    .meter-inner { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .meter-val { font-family:'Bangers',cursive; font-size:1.8rem; color:var(--text-color); line-height:1; letter-spacing:2px; }
    .meter-label { font-family:'Caveat',cursive; font-size:0.7rem; color:#888; }
    .stability-wrap { margin-top:auto; }
    .stability-header { display:flex; justify-content:space-between; font-family:'Caveat',cursive; font-size:0.7rem; color:#888; margin-bottom:0.15rem; }
    .stability-header span:last-child { font-weight:700; color:#000; }
    .stability-track { height:5px; background:#eee; border:2px solid #000; }
    .stability-fill { height:100%; background:linear-gradient(to right,#ccc,#888,#333); transition:width 1.5s ease-out; }

    /* ── CHART ── */
    .chart-area { display:flex; align-items:flex-end; gap:2px; height:55px; border-bottom:2px solid var(--panel-border); margin-top:auto; }
    .bar { flex:1; min-height:6%; border:2px solid var(--panel-border); transition:height 0.8s ease-out; }
    .bar.pos { background:#ddd; } .bar.neg { background:#666; } .bar.neu { background:#aaa; }
    .no-data { flex:1; display:flex; align-items:center; justify-content:center; font-size:0.75rem; color:#ccc; }

    /* ── THREAT LEVEL PANEL ── */
    .threat-strip {
        display:flex; border-bottom:3px solid var(--panel-border); flex-shrink:0;
    }
    .threat-label {
        background:var(--accent); color:var(--page-bg); padding:0.35rem 0.7rem;
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:2px;
        display:flex; align-items:center; gap:0.3rem;
        border-right:3px solid var(--panel-border); white-space:nowrap;
    }
    .threat-levels { display:flex; flex:1; }
    .tl-item {
        flex:1; padding:0.35rem 0.3rem; text-align:center;
        border-right:2px solid #eee; font-family:'Bangers',cursive;
        font-size:0.6rem; letter-spacing:1px; color:#ccc;
        transition:all 0.3s;
    }
    .tl-item:last-child { border-right:none; }
    .tl-item.active { background:#000; color:#fff; }
    .tl-item .tl-icon { display:block; font-size:0.9rem; margin-bottom:0.1rem; }

    /* ── KEYWORDS PANEL ── */
    .keyword-list { display:flex; flex-wrap:wrap; gap:0.3rem; margin-top:0.3rem; }
    .keyword-tag {
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px;
        border:2px solid var(--panel-border); padding:0.15rem 0.5rem;
        background:var(--page-bg); color:var(--text-color); transition:all 0.15s;
    }
    .keyword-tag:hover { background:var(--accent); color:var(--page-bg); }
    .keyword-count { font-family:'Caveat',cursive; font-size:0.65rem; color:#aaa; margin-left:0.2rem; }

    /* ── RECENT CALLS FEED ── */
    .feed-list { display:flex; flex-direction:column; gap:0.3rem; overflow:hidden; }
    .feed-item {
        display:flex; align-items:center; gap:0.5rem;
        padding:0.3rem 0; border-bottom:1px solid #f0f0f0;
    }
    .feed-item:last-child { border-bottom:none; }
    .feed-emoji { font-size:0.9rem; flex-shrink:0; }
    .feed-text { font-family:'Caveat',cursive; font-size:0.75rem; color:#555; flex:1; line-height:1.2; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .feed-score {
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px;
        border:1.5px solid #ddd; padding:0.1rem 0.3rem; flex-shrink:0;
    }

    /* ── AI RECOMMENDATIONS ── */
    .rec-list { display:flex; flex-direction:column; gap:0.25rem; }
    .rec-item {
        display:flex; align-items:flex-start; gap:0.4rem;
        font-family:'Caveat',cursive; font-size:0.75rem; color:#444;
        padding:0.25rem 0; border-bottom:1px solid #f0f0f0;
        line-height:1.2;
    }
    .rec-item:last-child { border-bottom:none; }
    .rec-icon { flex-shrink:0; font-size:0.8rem; }

    /* ── BOTTOM SPEECH ── */
    .bottom-strip {
        display:flex; align-items:center; gap:0.8rem;
        padding:0.5rem 0.8rem; border-top:3px solid var(--panel-border);
        flex-shrink:0;
    }
    .bottom-strip .manga-img {
        width:50px; height:50px; border:3px solid var(--panel-border);
        object-fit:cover; flex-shrink:0;
        filter: grayscale(1) contrast(1.2);
    }
    .bottom-speech {
        flex:1; background:var(--page-bg); border:3px solid var(--panel-border); border-radius:12px;
        padding:0.4rem 0.7rem; position:relative;
        box-shadow:2px 2px 0 rgba(0,0,0,0.08);
    }
    .bottom-speech::before {
        content:''; position:absolute; left:-10px; top:50%; transform:translateY(-50%);
        border:5px solid transparent; border-right-color:#000;
    }
    .bottom-speech::after {
        content:''; position:absolute; left:-6px; top:50%; transform:translateY(-50%);
        border:3px solid transparent; border-right-color:#fff;
    }
    .bottom-speech-text { font-family:'Caveat',cursive; font-size:0.8rem; color:#333; line-height:1.2; }
    .bottom-speech-tag { font-family:'Bangers',cursive; font-size:0.45rem; letter-spacing:3px; color:#aaa; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div>
            <div class="chapter-num">CHAPTER 01</div>
            <div class="chapter-title">MISSION CONTROL</div>
            <div class="chapter-sub">// Emotional Activity Command Center</div>
        </div>
        <div class="live-indicator"><div class="live-dot"></div>MONITORING</div>
    </div>

    {{-- Sentiment Percentages --}}
    <div class="pct-strip anim-reveal" style="animation-delay: 0.2s;">
        <div class="pct-cell">
            <div class="pct-tag">// POSITIVE</div>
            <div class="pct-val" id="posPct">0%</div>
            <div class="pct-lbl">Satisfied</div>
        </div>
        <div class="pct-cell">
            <div class="pct-tag">// NEGATIVE</div>
            <div class="pct-val" id="negPct">0%</div>
            <div class="pct-lbl">Upset</div>
        </div>
        <div class="pct-cell">
            <div class="pct-tag">// NEUTRAL</div>
            <div class="pct-val" id="neuPct">0%</div>
            <div class="pct-lbl">Neutral</div>
        </div>
    </div>

    {{-- Threat Level --}}
    <div class="threat-strip anim-slide-down" style="animation-delay: 0.4s;">
        <div class="threat-label">⚠ THREAT LEVEL</div>
        <div class="threat-levels" id="threatLevels">
            <div class="tl-item active" id="tl-stable"><span class="tl-icon">✅</span>STABLE</div>
            <div class="tl-item" id="tl-warning"><span class="tl-icon">⚡</span>WARNING</div>
            <div class="tl-item" id="tl-critical"><span class="tl-icon">🚨</span>CRITICAL</div>
        </div>
    </div>

    <div class="dash-grid">
        {{-- TOP-LEFT: Emotional Radar Chart --}}
        <div class="m-panel anim-pop" style="animation-delay: 0.5s;">
            <div class="panel-tag">// SIGNAL LOG</div>
            <div class="panel-heading">EMOTIONAL RADAR</div>
            <div class="chart-area" id="trendChart"><div class="no-data">📡 Awaiting…</div></div>
        </div>

        {{-- TOP-RIGHT: Sentiment Meter --}}
        <div class="m-panel meter-panel anim-pop" style="animation-delay: 0.6s;">
            <div class="panel-tag">// SENTIMENT RADAR</div>
            <div class="panel-heading">SUCCESS RATE</div>
            <div class="meter-ring">
                <svg viewBox="0 0 90 90" width="90" height="90">
                    <circle cx="45" cy="45" r="38" fill="none" stroke="#eee" stroke-width="7"/>
                    <circle cx="45" cy="45" r="38" fill="none" stroke="#333" stroke-width="7"
                        stroke-linecap="round" stroke-dasharray="239" stroke-dashoffset="239"
                        id="meterArc" style="transition:stroke-dashoffset 1.5s ease-out;"/>
                </svg>
                <div class="meter-inner">
                    <div class="meter-val" id="meterVal">0%</div>
                    <div class="meter-label">Positive</div>
                </div>
            </div>
            <div class="stability-wrap">
                <div class="stability-header"><span>Stability</span><span id="stabilityPct">0%</span></div>
                <div class="stability-track"><div class="stability-fill" id="stabilityFill" style="width:0%"></div></div>
            </div>
        </div>

        {{-- BOTTOM-LEFT: Top Complaint Keywords + Recent Feed --}}
        <div class="m-panel anim-pop" style="animation-delay: 0.7s;">
            <div class="panel-tag">// KEYWORD SCAN</div>
            <div class="panel-heading">TOP COMPLAINT TOPICS</div>
            <div class="keyword-list" id="keywordList">
                <span style="font-family:'Caveat',cursive;font-size:0.75rem;color:#ccc;">Scanning…</span>
            </div>
            <div style="margin-top:0.5rem;">
                <div class="panel-tag" style="margin-top:0.3rem;">// RECENT CALLS</div>
            </div>
            <div class="feed-list" id="feedList">
                <div style="font-family:'Caveat',cursive;font-size:0.75rem;color:#ccc;">No calls yet.</div>
            </div>
        </div>

        {{-- BOTTOM-RIGHT: AI Recommendations --}}
        <div class="m-panel anim-pop" style="animation-delay: 0.8s;">
            <div class="panel-tag">// AI ENGINE</div>
            <div class="panel-heading">RECOMMENDATIONS</div>
            <div class="rec-list" id="recList">
                <div class="rec-item"><span class="rec-icon">🧠</span>Awaiting data…</div>
            </div>
        </div>
    </div>

    {{-- Bottom speech bubble --}}
    <div class="bottom-strip">
        <img src="/images/manga_agent.png" alt="Agent" class="manga-img">
        <div class="bottom-speech anim-bubble" style="animation-delay: 1s;">
            <div class="bottom-speech-tag">// AGENT KAITO</div>
            <div class="bottom-speech-text" id="agentSpeech">
                "All systems operational. Monitoring incoming calls in real-time. Stay sharp, operator."
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function fetchData() {
        try {
            const res = await fetch('/api/dashboard-data');
            const data = await res.json();

            // Sentiment percentages
            document.getElementById('posPct').innerText = data.pos_pct + '%';
            document.getElementById('negPct').innerText = data.neg_pct + '%';
            document.getElementById('neuPct').innerText = data.neu_pct + '%';

            // Meter
            let pct = data.pos_pct;
            document.getElementById('meterVal').innerText = pct + '%';
            document.getElementById('stabilityPct').innerText = pct + '%';
            document.getElementById('stabilityFill').style.width = pct + '%';
            const arc = document.getElementById('meterArc');
            arc.style.strokeDashoffset = 239 - (239 * pct / 100);

            // Threat level
            document.querySelectorAll('.tl-item').forEach(t => t.classList.remove('active'));
            const level = data.threat_level || 'STABLE';
            if (level === 'CRITICAL') document.getElementById('tl-critical').classList.add('active');
            else if (level === 'WARNING') document.getElementById('tl-warning').classList.add('active');
            else document.getElementById('tl-stable').classList.add('active');

            // Trend chart
            const chart = document.getElementById('trendChart');
            if (data.trend && data.trend.length > 0) {
                chart.innerHTML = '';
                data.trend.forEach(item => {
                    const bar = document.createElement('div');
                    bar.className = 'bar ' + (item.sentiment_score > 0.2 ? 'pos' : item.sentiment_score < -0.2 ? 'neg' : 'neu');
                    bar.style.height = Math.max(8, Math.abs(item.sentiment_score) * 100) + '%';
                    bar.title = 'Score: ' + item.sentiment_score;
                    chart.appendChild(bar);
                });
            }

            // Keywords
            const kwList = document.getElementById('keywordList');
            if (data.keywords && data.keywords.length > 0) {
                kwList.innerHTML = '';
                data.keywords.forEach(kw => {
                    kwList.innerHTML += `<span class="keyword-tag">"${kw.word}"<span class="keyword-count">×${kw.count}</span></span>`;
                });
            } else {
                kwList.innerHTML = '<span style="font-family:Caveat,cursive;font-size:0.75rem;color:#ccc;">No complaints detected ✅</span>';
            }

            // Recent calls feed
            const feedList = document.getElementById('feedList');
            if (data.recent_calls && data.recent_calls.length > 0) {
                feedList.innerHTML = '';
                data.recent_calls.forEach(call => {
                    const emoji = call.sentiment_label === 'Positive' ? '😊' : call.sentiment_label === 'Negative' ? '😡' : '😐';
                    const txt = call.transcript ? call.transcript.substring(0, 40) + '…' : '—';
                    feedList.innerHTML += `<div class="feed-item"><span class="feed-emoji">${emoji}</span><span class="feed-text">#${call.id} ${txt}</span><span class="feed-score">${parseFloat(call.sentiment_score).toFixed(1)}</span></div>`;
                });
            }

            // AI Recommendations
            const recList = document.getElementById('recList');
            if (data.recommendations && data.recommendations.length > 0) {
                recList.innerHTML = '';
                data.recommendations.forEach(rec => {
                    recList.innerHTML += `<div class="rec-item"><span class="rec-icon">⚡</span>${rec}</div>`;
                });
            }

            // Agent speech updates based on threat
            const speech = document.getElementById('agentSpeech');
            if (level === 'CRITICAL') {
                speech.innerText = '"RED ALERT! Multiple negative calls detected. Escalation required immediately. All agents respond!"';
            } else if (level === 'WARNING') {
                speech.innerText = '"Caution — negative sentiment rising. Keep monitoring and prepare for escalation if needed."';
            } else {
                speech.innerText = '"All systems operational. Monitoring incoming calls in real-time. Stay sharp, operator."';
            }

        } catch(e) { console.error(e); }
    }
    fetchData();
    setInterval(fetchData, 10000);
</script>
@endsection
