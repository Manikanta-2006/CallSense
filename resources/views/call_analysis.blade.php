@extends('layouts.app')
@section('title', 'Call Analysis — CallSense')
@section('page-number', 'PAGE 2 OF 6')
@section('page-quote', 'Scanning emotions… stand by for results!')

@section('styles')
<style>
    .page { padding:1.2rem 1.5rem; height:100%; overflow-y:auto; }

    /* ── CHAPTER HEADER ── */
    .chapter-header {
        padding-bottom:1rem; margin-bottom:1rem;
        border-bottom:4px solid var(--panel-border);
        position:relative;
    }
    .chapter-header::after {
        content:''; position:absolute; bottom:-4px; left:0;
        width:100%; height:4px;
        background:repeating-linear-gradient(90deg, var(--panel-border) 0px, var(--panel-border) 8px, transparent 8px, transparent 12px);
    }
    .chapter-num { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:4px; color:#aaa; }
    .chapter-title { font-family:'Bangers',cursive; font-size:2rem; color:var(--text-color); letter-spacing:3px; line-height:1; }
    .chapter-sub { font-family:'Caveat',cursive; font-size:0.85rem; color:#999; }

    .alert-box {
        padding:0.7rem 1rem; background:var(--page-bg); border:3px solid var(--panel-border);
        margin-bottom:1rem; font-family:'Bangers',cursive; font-size:0.85rem;
        letter-spacing:1px;
    }

    /* ── FORM PANELS ── */
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; margin-bottom:0; }
    .form-panel {
        border:3px solid var(--panel-border); padding:1rem; background:var(--page-bg); position:relative; overflow:hidden;
    }
    .form-panel::before {
        content:''; position:absolute; inset:0;
        background-image:radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size:10px 10px; pointer-events:none;
    }
    .form-panel > * { position:relative; z-index:1; }

    .panel-tag { font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:3px; color:#aaa; margin-bottom:0.2rem; }
    .panel-heading { font-family:'Bangers',cursive; font-size:1rem; color:var(--text-color); letter-spacing:1px; margin-bottom:0.6rem; }

    .upload-zone {
        border:3px dashed #ccc; padding:1.5rem; text-align:center;
        cursor:pointer; transition:all 0.2s; position:relative;
    }
    .upload-zone:hover { border-color:var(--accent); background:var(--halftone); }
    .upload-zone.has-file { border-color:var(--accent); border-style:solid; background:var(--page-bg); }
    .upload-zone input { position:absolute; inset:0; opacity:0; cursor:pointer; }
    .upload-icon { font-size:2rem; margin-bottom:0.3rem; }
    .upload-text { font-size:0.95rem; color:var(--sub-text); }
    .upload-text strong { color:var(--text-color); }
    .upload-formats { font-size:0.75rem; color:#bbb; margin-top:0.2rem; }

    .audio-player-wrap { display:none; margin-top:0.6rem; padding:0.5rem; border:2px solid var(--panel-border); background:var(--page-bg); }
    .audio-player-wrap.visible { display:block; }
    .file-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem; }
    .file-name-text { font-size:0.85rem; color:#555; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%; }
    .btn-remove { background:none; border:2px solid var(--sub-text); color:var(--sub-text); font-family:'Bangers',cursive; font-size:0.7rem; padding:0.15rem 0.5rem; cursor:pointer; letter-spacing:1px; }
    audio { width:100%; height:28px; }
    
    /* ── RECORDING FEATURE ── */
    .record-controls {
        display: flex; align-items: center; justify-content: center; gap: 1rem;
        margin-top: 1rem; padding: 0.8rem; border: 3px solid var(--panel-border);
        background: var(--halftone);
    }
    .btn-record {
        width: 50px; height: 50px; border-radius: 50%;
        border: 4px solid var(--panel-border); background: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s; position: relative;
    }
    .btn-record::after {
        content: ''; width: 20px; height: 20px; background: #f87171; border-radius: 50%;
        transition: all 0.2s;
    }
    .btn-record.recording { border-color: #f87171; animation: recPulse 1.5s infinite; }
    .btn-record.recording::after { border-radius: 4px; width: 16px; height: 16px; }
    
    @keyframes recPulse { 0% { box-shadow: 0 0 0 0 rgba(248, 113, 113, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(248, 113, 113, 0); } 100% { box-shadow: 0 0 0 0 rgba(248, 113, 113, 0); } }
    
    .record-status { font-family: 'Bangers', cursive; font-size: 0.8rem; letter-spacing: 1px; color: var(--text-color); }
    .recording-timer { font-family: 'Bangers', cursive; font-size: 1.2rem; color: #f87171; display: none; }
    .recording-timer.visible { display: block; }

    /* Extra fields */
    .field-row { display:flex; gap:0.5rem; margin-bottom:0.5rem; }
    .field-input {
        flex:1; padding:0.5rem 0.7rem;
        border:2.5px solid #ccc; background:var(--page-bg);
        font-family:'Caveat',cursive; font-size:0.95rem; color:var(--text-color);
        outline:none; transition:border-color 0.2s;
    }
    .field-input:focus { border-color:var(--accent); }
    .field-input::placeholder { color:#ccc; }

    textarea {
        width:100%; height:100px; padding:0.8rem;
        background:var(--page-bg); border:3px solid var(--panel-border);
        font-family:'Caveat',cursive; font-size:1rem;
        resize:none; color:var(--text-color); line-height:1.5;
        transition:border-color 0.2s;
    }
    textarea:focus { outline:none; border-color:var(--accent); }
    textarea::placeholder { color:#ccc; }
    .error-text { color:var(--accent); font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:1px; margin-top:0.3rem; }

    /* ── SUBMIT BAR ── */
    .submit-bar {
        border:3px solid var(--panel-border); padding:0.8rem 1rem;
        display:flex; align-items:center; justify-content:space-between;
        background:var(--accent); color:var(--page-bg);
    }
    .submit-hint { font-family:'Bangers',cursive; font-size:0.7rem; letter-spacing:2px; color:#888; }
    .btn-analyze {
        padding:0.6rem 2rem; background:var(--page-bg); color:var(--text-color);
        border:3px solid var(--panel-border); font-family:'Bangers',cursive;
        font-size:1rem; letter-spacing:2px; cursor:pointer;
        transition:all 0.15s; box-shadow:3px 3px 0 #555;
    }
    .btn-analyze:hover { transform:translate(-2px,-2px); box-shadow:5px 5px 0 var(--accent); }
    .btn-analyze:disabled { opacity:0.3; cursor:not-allowed; transform:none; box-shadow:none; }

    /* ── RESULTS ── */
    .results-header {
        display:flex; align-items:center; gap:1rem;
        margin:1.5rem 0 1rem; padding-bottom:0.5rem;
        border-bottom:3px solid var(--panel-border);
    }
    .results-title { font-family:'Bangers',cursive; font-size:1.3rem; color:var(--text-color); letter-spacing:2px; white-space:nowrap; }
    .results-line { flex:1; height:3px; background:repeating-linear-gradient(90deg, var(--panel-border) 0px, var(--panel-border) 6px, transparent 6px, transparent 10px); }

    .results-list { display:flex; flex-direction:column; gap:1.2rem; }
    .call-row { display:flex; align-items:flex-start; gap:0.8rem; }
    .call-meta { flex-shrink:0; text-align:center; width:55px; }
    .call-meta .case-id { font-family:'Bangers',cursive; font-size:0.65rem; color:#aaa; letter-spacing:1px; }
    .call-meta .emotion-icon { font-size:1.8rem; }

    .speech-card {
        flex:1; border:3px solid var(--panel-border); padding:0.8rem 1rem; position:relative;
        background:var(--page-bg); box-shadow:3px 3px 0 rgba(0,0,0,0.1);
    }
    .speech-card::before {
        content:''; position:absolute; left:-14px; top:12px;
        border:7px solid transparent; border-right-color:var(--panel-border);
    }
    .speech-card::after {
        content:''; position:absolute; left:-8px; top:15px;
        border:4px solid transparent; border-right-color:var(--page-bg);
    }
    .speech-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.3rem; flex-wrap:wrap; gap:0.3rem; }
    .speech-text { font-family:'Caveat',cursive; font-size:0.95rem; color:var(--text-color); line-height:1.4; }
    .speech-score { font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:2px; color:#aaa; margin-top:0.3rem; }

    .badge {
        font-family:'Bangers',cursive; font-size:0.6rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid var(--panel-border);
    }
    .badge-Positive { background:#eee; }
    .badge-Negative { background:#ccc; }
    .badge-Neutral { background:#f5f5f5; }
    .badge-emotion {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid #bbb; color:#666;
    }
    .badge-risk {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid var(--panel-border);
    }
    .risk-high { background:#333; color:#fff; }
    .risk-medium { background:#888; color:#fff; }
    .risk-low { background:#ddd; }

    /* Sentence analysis */
    .sentence-list { margin-top:0.4rem; border-top:2px solid #eee; padding-top:0.3rem; }
    .sentence-item {
        display:flex; align-items:center; gap:0.4rem;
        padding:0.2rem 0; font-family:'Caveat',cursive; font-size:0.8rem;
        color:#555; border-bottom:1px solid #f5f5f5;
    }
    .sentence-item:last-child { border-bottom:none; }
    .sentence-score {
        font-family:'Bangers',cursive; font-size:0.5rem; letter-spacing:1px;
        padding:0.05rem 0.3rem; border:1.5px solid #ddd; flex-shrink:0;
        min-width:35px; text-align:center;
    }
    .sentence-text { flex:1; }

    .btn-play {
        width:24px; height:24px; border-radius:50%; border:2px solid #ccc;
        background:#fff; color:#888; cursor:pointer;
        display:flex; align-items:center; justify-content:center; transition:all 0.15s;
    }
    .btn-play:hover { border-color:var(--accent); color:var(--accent); }
    .btn-play svg { width:10px; height:10px; }

    /* Detail modal */
    .detail-btn {
        font-family:'Bangers',cursive; font-size:0.55rem; letter-spacing:1px;
        padding:0.1rem 0.4rem; border:2px solid #ccc; background:none;
        cursor:pointer; color:#888; transition:all 0.15s;
    }
    .detail-btn:hover { border-color:#000; color:#000; }

    .empty-state { text-align:center; padding:2.5rem; color:#bbb; font-family:'Caveat',cursive; font-size:1.1rem; }
    .empty-icon { font-size:2.5rem; margin-bottom:0.5rem; }
</style>
@endsection

@section('content')
<div class="page">
    <div class="chapter-header anim-slide-down">
        <div class="chapter-num">CHAPTER 02</div>
        <div class="chapter-title">DECODE EMOTIONS</div>
        <div class="chapter-sub">// Upload a call recording and analyze sentiment</div>
    </div>

    @if(session('success'))
        <div class="alert-box">✓ {{ session('success') }}</div>
    @endif

    <form action="{{ route('calls.store') }}" method="POST" enctype="multipart/form-data" id="analysisForm">
        @csrf
        <div class="form-grid">
            <div class="form-panel anim-pop" style="animation-delay: 0.2s;">
                <div class="panel-tag">// STEP 01 — INCOMING TRANSMISSION</div>
                <div class="panel-heading">UPLOAD RECORDING</div>
                <div class="upload-zone" id="uploadZone">
                    <input type="file" name="audio_file" id="audioFile" accept=".mp3,.wav,.ogg,.m4a,.webm">
                    <div class="upload-icon" id="uploadIcon">📼</div>
                    <div class="upload-text" id="uploadText">
                        <strong>Click to upload</strong> or drag & drop<br>
                        <div class="upload-formats">MP3 · WAV · OGG · M4A · WEBM — Max 20MB</div>
                    </div>
                </div>

                <div class="record-controls">
                    <button type="button" class="btn-record" id="recordBtn" title="Start Recording"></button>
                    <div style="flex:1;">
                        <div class="record-status" id="recordStatus">SPONTANEOUS RECORD</div>
                        <div class="recording-timer" id="recordTimer">00:00</div>
                    </div>
                </div>
                @error('audio_file')<div class="error-text">{{ $message }}</div>@enderror
                <div class="audio-player-wrap" id="audioPlayer">
                    <div class="file-row">
                        <span class="file-name-text" id="fileNameText">—</span>
                        <button type="button" class="btn-remove" id="removeFile">✕ REMOVE</button>
                    </div>
                    <audio controls id="audioPreview"></audio>
                </div>
                <div style="margin-top:0.6rem;">
                    <div class="panel-tag">// CALLER DETAILS</div>
                    <div class="field-row">
                        <input type="text" name="customer_name" class="field-input" placeholder="Customer Name (optional)">
                        <input type="text" name="agent_name" class="field-input" placeholder="Agent Name (optional)">
                    </div>
                </div>
            </div>
            <div class="form-panel anim-pop" style="animation-delay: 0.3s;">
                <div class="panel-tag">// STEP 02 — VOICE TRANSCRIPT</div>
                <div class="panel-heading">DECODE DIALOGUE</div>
                <textarea name="transcript" id="transcript" placeholder="Paste the call transcript here or leave empty to auto-transcribe…"></textarea>
                <div style="font-size:0.75rem;color:#bbb;margin-top:0.3rem;font-family:'Caveat',cursive;">Sentiment is analyzed per-sentence. Each sentence gets its own score.</div>
                @error('transcript')<div class="error-text">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="submit-bar anim-slide-up" style="animation-delay: 0.4s;">
            <div class="submit-hint">// STEP 03 — EXECUTE SCAN</div>
            <button type="submit" class="btn-analyze" id="analyzeBtn" disabled>⚡ ANALYZE</button>
        </div>
    </form>

    <div class="results-header anim-reveal" style="animation-delay: 0.5s;">
        <div class="results-title">DECODED CALLS ({{ $calls->count() }})</div>
        <div class="results-line"></div>
    </div>

    <div class="results-list anim-pop" style="animation-delay: 0.6s;">
        @forelse($calls as $call)
            @php
                $icon = match($call->emotion ?? 'neutral') {
                    'angry' => '🤬',
                    'frustrated' => '😤',
                    'happy' => '😊',
                    'satisfied' => '😌',
                    default => $call->sentiment_label === 'Positive' ? '😊' : ($call->sentiment_label === 'Negative' ? '😡' : '😐'),
                };
                $riskClass = ($call->risk_score ?? 0) >= 70 ? 'risk-high' : (($call->risk_score ?? 0) >= 40 ? 'risk-medium' : 'risk-low');
            @endphp
            <div class="call-row">
                <div class="call-meta">
                    <div class="case-id">CASE<br>#{{ $call->id }}</div>
                    <div class="emotion-icon">{{ $icon }}</div>
                </div>
                <div class="speech-card">
                    <div class="speech-top">
                        <span class="badge badge-{{ $call->sentiment_label }}">{{ strtoupper($call->sentiment_label) }}</span>
                        @if($call->emotion)
                            <span class="badge-emotion">{{ strtoupper($call->emotion) }}</span>
                        @endif
                        @if($call->risk_score !== null)
                            <span class="badge-risk {{ $riskClass }}">RISK: {{ round($call->risk_score) }}%</span>
                        @endif
                        @if($call->audio_path)
                            <button class="btn-play" onclick="togglePlay(this,'{{ asset('storage/'.$call->audio_path) }}')">
                                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </button>
                        @endif
                    </div>
                    <div class="speech-text">"{{ \Illuminate\Support\Str::limit($call->transcript, 140) }}"</div>
                    <div class="speech-score">
                        SCORE: {{ number_format($call->sentiment_score, 2) }}
                        @if($call->sentiment_magnitude) · MAG: {{ number_format($call->sentiment_magnitude, 2) }} @endif
                        @if($call->customer_name) · CUSTOMER: {{ $call->customer_name }} @endif
                        @if($call->agent_name) · AGENT: {{ $call->agent_name }} @endif
                    </div>

                    {{-- Sentence-level analysis --}}
                    @if(!empty($call->sentence_analysis) && count($call->sentence_analysis) > 1)
                        <div class="sentence-list">
                            @foreach(array_slice($call->sentence_analysis, 0, 5) as $sentence)
                                <div class="sentence-item">
                                    <span class="sentence-score">{{ number_format($sentence['score'] ?? 0, 1) }}</span>
                                    <span class="sentence-text">"{{ \Illuminate\Support\Str::limit($sentence['text'] ?? '', 80) }}"</span>
                                </div>
                            @endforeach
                            @if(count($call->sentence_analysis) > 5)
                                <div class="sentence-item" style="color:#bbb;font-size:0.7rem;">
                                    +{{ count($call->sentence_analysis) - 5 }} more sentences…
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Keywords --}}
                    @if(!empty($call->keywords))
                        <div style="margin-top:0.3rem;display:flex;flex-wrap:wrap;gap:0.2rem;">
                            @foreach(array_slice($call->keywords, 0, 5) as $kw)
                                <span style="font-family:'Bangers',cursive;font-size:0.5rem;letter-spacing:1px;border:1.5px solid #ddd;padding:0.05rem 0.3rem;color:#888;">{{ $kw }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state"><div class="empty-icon">📡</div>No calls decoded yet. Upload a recording to begin.</div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<audio id="tableAudio" style="display:none;"></audio>
<script>
    const audioFileInput=document.getElementById('audioFile'),uploadZone=document.getElementById('uploadZone'),audioPlayer=document.getElementById('audioPlayer'),audioPreview=document.getElementById('audioPreview'),fileNameText=document.getElementById('fileNameText'),removeFileBtn=document.getElementById('removeFile'),analyzeBtn=document.getElementById('analyzeBtn'),transcriptField=document.getElementById('transcript');
    function updateBtn(){analyzeBtn.disabled=!(audioFileInput.files&&audioFileInput.files.length>0);}
    audioFileInput.addEventListener('change',e=>{const f=e.target.files[0];if(f){audioPreview.src=URL.createObjectURL(f);fileNameText.textContent=f.name;audioPlayer.classList.add('visible');uploadZone.classList.add('has-file');document.getElementById('uploadIcon').textContent='✅';document.getElementById('uploadText').innerHTML='<strong>File ready</strong> — click to change';updateBtn();}});
    removeFileBtn.addEventListener('click',()=>{audioFileInput.value='';audioPreview.src='';audioPlayer.classList.remove('visible');uploadZone.classList.remove('has-file');document.getElementById('uploadIcon').textContent='📼';document.getElementById('uploadText').innerHTML='<strong>Click to upload</strong> or drag & drop<br><div class="upload-formats">MP3 · WAV · OGG · M4A · WEBM — Max 20MB</div>';updateBtn();});
    transcriptField.addEventListener('input',updateBtn);
    uploadZone.addEventListener('dragover',e=>{e.preventDefault();uploadZone.style.borderColor='#000';});
    uploadZone.addEventListener('dragleave',()=>{uploadZone.style.borderColor='';});
    uploadZone.addEventListener('drop',e=>{e.preventDefault();uploadZone.style.borderColor='';if(e.dataTransfer.files.length){audioFileInput.files=e.dataTransfer.files;audioFileInput.dispatchEvent(new Event('change'));}});

    // ── RECORDING LOGIC ──
    const recordBtn = document.getElementById('recordBtn'), recordStatus = document.getElementById('recordStatus'), recordTimer = document.getElementById('recordTimer');
    let mediaRecorder, audioChunks = [], timerInterval, startTime;
    let recognition;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        
        recognition.onresult = (event) => {
            let finalTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                }
            }
            if (finalTranscript) {
                transcriptField.value = (transcriptField.value + ' ' + finalTranscript).trim();
                updateBtn();
            }
        };
    }

    recordBtn.addEventListener('click', async () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            stopRecording();
        } else {
            startRecording();
        }
    });

    async function startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                const blob = new Blob(audioChunks, { type: 'audio/webm' });
                const file = new File([blob], "recorded_call.webm", { type: 'audio/webm' });
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                audioFileInput.files = dataTransfer.files;
                audioFileInput.dispatchEvent(new Event('change'));
            };

            mediaRecorder.start();
            if (recognition) {
                transcriptField.value = ''; // Clear for new recording
                recognition.start();
            }
            
            recordBtn.classList.add('recording');
            recordStatus.textContent = 'RECORDING LIVE...';
            recordTimer.classList.add('visible');
            
            startTime = Date.now();
            timerInterval = setInterval(updateTimer, 1000);
        } catch (err) {
            alert('Microphone access denied or not available.');
        }
    }

    function stopRecording() {
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        if (recognition) recognition.stop();
        
        recordBtn.classList.remove('recording');
        recordStatus.textContent = 'RECORDING SAVED!';
        clearInterval(timerInterval);
        recordTimer.classList.remove('visible');
    }

    function updateTimer() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const mins = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const secs = (elapsed % 60).toString().padStart(2, '0');
        recordTimer.textContent = `${mins}:${secs}`;
    }

    let currentBtn=null;const tableAudio=document.getElementById('tableAudio');
    function togglePlay(btn,src){if(currentBtn===btn&&!tableAudio.paused){tableAudio.pause();btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';currentBtn=null;return;}if(currentBtn)currentBtn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';tableAudio.src=src;tableAudio.play();btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';currentBtn=btn;tableAudio.onended=()=>{btn.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';currentBtn=null;};}
</script>
@endsection
