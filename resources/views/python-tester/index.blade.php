{{-- Temporary untuk teks koneksi dengan python aja --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Python Service Tester</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f1117;
            --bg-card:   #181c27;
            --bg-input:  #1e2336;
            --border:    #2a3050;
            --border-focus: #4f6ef7;
            --text:      #e8ecf5;
            --muted:     #6b7a9e;
            --accent:    #4f6ef7;
            --accent-dim:#1e2d6b;
            --green:     #22c55e;
            --green-dim: #052e16;
            --red:       #f87171;
            --red-dim:   #2d0a0a;
            --yellow:    #fbbf24;
            --yellow-dim:#3b2000;
            --radius:    10px;
            --mono:      'JetBrains Mono', 'Fira Code', monospace;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .page-wrap {
            max-width: 860px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 2rem;
        }
        .header-icon {
            width: 44px; height: 44px;
            background: var(--accent-dim);
            border: 1px solid var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .header h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
        .header p  { font-size: 13px; color: var(--muted); margin-top: 2px; }

        /* ── Status badge ── */
        #healthBadge {
            margin-left: auto;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid var(--border);
            color: var(--muted);
            background: var(--bg-input);
            cursor: pointer;
            transition: all .15s;
        }
        #healthBadge.ok     { border-color: var(--green); color: var(--green); background: var(--green-dim); }
        #healthBadge.error  { border-color: var(--red);   color: var(--red);   background: var(--red-dim); }

        /* ── Card ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .card-title {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1rem;
        }

        /* ── Form controls ── */
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .field { margin-bottom: 12px; }
        .field:last-child { margin-bottom: 0; }
        label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--text);
            font-size: 14px;
            padding: 9px 12px;
            outline: none;
            transition: border-color .15s;
        }
        input[type="text"]:focus { border-color: var(--border-focus); }

        /* ── Dropzone ── */
        #dropzone {
            border: 1.5px dashed var(--border);
            border-radius: var(--radius);
            padding: 2.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            margin-bottom: 1.25rem;
        }
        #dropzone:hover, #dropzone.drag { border-color: var(--accent); background: var(--accent-dim); }
        #dropzone.has-file { border-color: var(--green); border-style: solid; background: var(--green-dim); }
        #dropzone svg { display: block; margin: 0 auto 10px; }
        #dropzone p   { font-size: 13px; color: var(--muted); }
        #dropzone .filename { font-size: 14px; color: var(--green); font-weight: 500; margin-top: 4px; }

        /* ── Buttons ── */
        .btn-row { display: flex; gap: 10px; }
        button {
            border: 1px solid var(--border);
            background: var(--bg-input);
            color: var(--text);
            font-size: 13px;
            font-weight: 500;
            padding: 9px 18px;
            border-radius: 7px;
            cursor: pointer;
            transition: all .15s;
            display: flex; align-items: center; gap: 7px;
        }
        button:hover { border-color: var(--accent); color: var(--accent); }
        button:disabled { opacity: .35; cursor: not-allowed; }
        button.primary {
            flex: 1;
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            justify-content: center;
        }
        button.primary:hover { background: #3a57d4; border-color: #3a57d4; color: #fff; }

        /* ── Alert ── */
        .alert {
            padding: 10px 14px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 1.25rem;
            display: none;
        }
        .alert.info    { background: var(--accent-dim); border: 1px solid var(--accent); color: #a5b4fc; }
        .alert.success { background: var(--green-dim);  border: 1px solid var(--green);  color: var(--green); }
        .alert.error   { background: var(--red-dim);    border: 1px solid var(--red);    color: var(--red); }

        /* ── Results ── */
        #results { display: none; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 1.25rem;
        }
        .stat {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
        }
        .stat .label { font-size: 11px; color: var(--muted); margin-bottom: 4px; }
        .stat .value { font-size: 22px; font-weight: 600; }

        .statements-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.25rem;
        }
        .stmt-tag {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 12px;
        }
        .stmt-tag span { color: var(--accent); margin-left: 5px; }
        .stmt-checks {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .stmt-check-item {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 8px 14px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            user-select: none;
            font-size: 13px;
        }
        .stmt-check-item:has(input:checked) {
            border-color: var(--accent);
            background: var(--accent-dim);
            color: var(--accent);
        }
        .stmt-check-item input[type="checkbox"] {
            accent-color: var(--accent);
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        /* ── Chunk viewer ── */
        .chunk-viewer {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .chunk-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .chunk-toolbar .nav-btn {
            padding: 4px 10px;
            font-size: 12px;
        }
        .chunk-toolbar select {
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 6px;
            outline: none;
            margin-left: auto;
        }
        .chunk-counter { font-size: 12px; color: var(--muted); }
        .chunk-body {
            padding: 16px;
            font-family: var(--mono);
            font-size: 12px;
            line-height: 1.7;
            color: #a8b5d1;
            white-space: pre-wrap;
            max-height: 320px;
            overflow-y: auto;
        }
        .chunk-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 10px 14px;
            border-top: 1px solid var(--border);
            font-size: 11px;
        }
        .chunk-meta .kv { display: flex; gap: 5px; }
        .chunk-meta .k  { color: var(--muted); }
        .chunk-meta .v  { color: var(--text); font-family: var(--mono); }

        .btn-download {
            width: 100%;
            justify-content: center;
            margin-top: 10px;
        }

        .btn-embed {
            width: 100%;
            justify-content: center;
            margin-top: 10px;
            background: var(--green-dim);
            border-color: var(--green);
            color: var(--green);
        }
        .btn-embed:hover {
            background: var(--green);
            border-color: var(--green);
            color: #0f1117;
        }
        .btn-embed:disabled {
            opacity: .35;
            cursor: not-allowed;
        }
        .embed-status {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            margin-top: 6px;
        }
        .embed-status.success { color: var(--green); }
        .embed-status.error   { color: var(--red); }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- Header --}}
    <div class="header">
        <div class="header-icon">⚡</div>
        <div>
            <h1>Python Service Tester</h1>
            <p>Upload a financial PDF and inspect the extracted chunks</p>
        </div>
        <button id="healthBadge" onclick="checkHealth()">● Check Health</button>
    </div>

    {{-- Form --}}
    <div class="card">
        <div class="card-title">Document Details</div>
        <div class="field-row">
            <div class="field">
                <label>Company Name</label>
                <input type="text" id="company" value="PT Pilar" placeholder="e.g. PT Anugerah Sakti" />
            </div>
            <div class="field">
                <label>Period</label>
                <input type="text" id="period" value="2024" placeholder="e.g. 2024 or Q1-2024" />
            </div>
        </div>
    </div>

    <div class="field" style="margin-top:4px;">
        <label>Statement Types <span style="color:var(--red)">*</span></label>
        <div class="stmt-checks">
            <label class="stmt-check-item">
                <input type="checkbox" name="stmt_type" value="neraca" checked>
                Neraca
            </label>
            <label class="stmt-check-item">
                <input type="checkbox" name="stmt_type" value="laba_rugi" checked>
                Laba Rugi
            </label>
            <label class="stmt-check-item">
                <input type="checkbox" name="stmt_type" value="arus_kas" checked>
                Arus Kas
            </label>
        </div>
    </div>

    {{-- Dropzone --}}
    <div id="dropzone"
         onclick="document.getElementById('fileInput').click()"
         ondragover="event.preventDefault(); this.classList.add('drag')"
         ondragleave="this.classList.remove('drag')"
         ondrop="handleDrop(event)">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6b7a9e" stroke-width="1.5">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="12" y1="18" x2="12" y2="12"/>
            <polyline points="9 15 12 12 15 15"/>
        </svg>
        <p>Drop your PDF here, or click to browse</p>
        <p class="filename" id="filename" style="display:none"></p>
        <input type="file" id="fileInput" accept=".pdf" style="display:none" onchange="handleFile(this.files[0])" />
    </div>

    {{-- Alert --}}
    <div class="alert" id="alert"></div>

    {{-- Actions --}}
    <div class="btn-row" style="margin-bottom:1.25rem;">
        <button onclick="checkHealth()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Health Check
        </button>
        <button class="primary" id="btnIngest" onclick="runIngest()" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
            Send to Python Service
        </button>
    </div>

    {{-- Results --}}
    <div id="results">
        <div class="stats-grid">
            <div class="stat"><div class="label">Total Chunks</div><div class="value" id="statChunks">—</div></div>
            <div class="stat"><div class="label">Statements Found</div><div class="value" id="statStmts">—</div></div>
            <div class="stat"><div class="label">Source File</div><div class="value" style="font-size:13px; padding-top:6px;" id="statSource">—</div></div>
        </div>

        <div class="statements-row" id="statementsRow"></div>

        <div class="chunk-viewer">
            <div class="chunk-toolbar">
                <button class="nav-btn" onclick="prevChunk()">← Prev</button>
                <button class="nav-btn" onclick="nextChunk()">Next →</button>
                <span class="chunk-counter" id="chunkCounter">0 / 0</span>
                <select id="typeFilter" onchange="filterChunks()">
                    <option value="">All types</option>
                </select>
            </div>
            <div class="chunk-body" id="chunkBody">No chunks loaded.</div>
            <div class="chunk-meta" id="chunkMeta"></div>
        </div>

        <button class="btn-embed" id="btnEmbed" onclick="runEmbed()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.36 6.36l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m6.36-6.36l4.24-4.24"/>
            </svg>
            Start Data Loader (Embed to Vector Store)
        </button>

        <div class="embed-status" id="embedStatus"></div>

        <button class="btn-download" onclick="downloadJSON()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Full JSON
        </button>
    </div>

</div>

<script>
const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
const ingestUrl    = "{{ route('python-tester.ingest') }}";
const healthUrl    = "{{ route('python-tester.health') }}";
const embedUrl = "{{ route('python-tester.embed') }}";

let selectedFile   = null;
let allChunks      = [];
let filteredChunks = [];
let currentIdx     = 0;
let fullResponse   = null;

// ── File handling ────────────────────────────────────────────────────────────

function handleDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('dropzone');
    dz.classList.remove('drag');
    const f = e.dataTransfer.files[0];
    if (f && f.name.toLowerCase().endsWith('.pdf')) {
        handleFile(f);
    } else {
        showAlert('Only PDF files are accepted.', 'error');
    }
}

function handleFile(f) {
    if (!f) return;
    selectedFile = f;
    const dz = document.getElementById('dropzone');
    dz.classList.add('has-file');
    const fn = document.getElementById('filename');
    fn.textContent = f.name + ' (' + (f.size / 1024).toFixed(0) + ' KB)';
    fn.style.display = 'block';
    document.getElementById('btnIngest').disabled = false;
}

// ── Alerts ───────────────────────────────────────────────────────────────────

function showAlert(msg, type = 'info') {
    const el = document.getElementById('alert');
    el.className = 'alert ' + type;
    el.textContent = msg;
    el.style.display = 'block';
}

function hideAlert() {
    document.getElementById('alert').style.display = 'none';
}

// ── Health check ─────────────────────────────────────────────────────────────

async function checkHealth() {
    const badge = document.getElementById('healthBadge');
    badge.textContent = '● Checking…';
    badge.className   = '';
    try {
        const res  = await fetch(healthUrl, { headers: { 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        if (data.ok) {
            badge.textContent = '● Healthy — v' + data.data.version;
            badge.className   = 'ok';
            showAlert('Python service is up and running.', 'success');
        } else {
            badge.textContent = '● Unreachable';
            badge.className   = 'error';
            showAlert('Service error: ' + (data.error ?? 'unknown'), 'error');
        }
    } catch (e) {
        badge.textContent = '● Unreachable';
        badge.className   = 'error';
        showAlert('Cannot reach Laravel backend: ' + e.message, 'error');
    }
}

// ── Ingest ───────────────────────────────────────────────────────────────────

async function runIngest() {
    if (!selectedFile) { showAlert('Please select a PDF first.', 'error'); return; }

    const company = document.getElementById('company').value.trim();
    const period  = document.getElementById('period').value.trim();
    if (!company || !period) { showAlert('Company name and period are required.', 'error'); return; }

    // Ambil statement types yang dicentang
    const checkedBoxes = document.querySelectorAll('input[name="stmt_type"]:checked');
    const statementTypes = Array.from(checkedBoxes).map(cb => cb.value);
    if (statementTypes.length === 0) {
        showAlert('Pilih minimal satu statement type.', 'error');
        return;
    }

    const btn = document.getElementById('btnIngest');
    btn.disabled    = true;
    btn.textContent = 'Processing…';
    hideAlert();
    document.getElementById('results').style.display = 'none';
    showAlert('Uploading PDF to Python service — this may take a minute for large files…', 'info');

    const fd = new FormData();
    fd.append('file',            selectedFile);
    fd.append('company',         company);
    fd.append('period',          period);
    fd.append('statement_types', JSON.stringify(statementTypes));  // ← tambahan

    try {
        const res  = await fetch(ingestUrl, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body:    fd,
        });
        const data = await res.json();

        if (!data.ok) {
            showAlert('Error: ' + (data.error ?? JSON.stringify(data)), 'error');
            return;
        }

        fullResponse = data.data;
        showAlert('Done! ' + fullResponse.total_chunks + ' chunks extracted from ' + fullResponse.source, 'success');
        renderResults(fullResponse);
    } catch (e) {
        showAlert('Request failed: ' + e.message, 'error');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg> Send to Python Service`;
    }
}

// ── Render results ───────────────────────────────────────────────────────────

function renderResults(d) {
    document.getElementById('statChunks').textContent = d.total_chunks;
    document.getElementById('statStmts').textContent  = d.statements.length;
    document.getElementById('statSource').textContent = d.source;

    document.getElementById('statementsRow').innerHTML = d.statements.map(s =>
        `<div class="stmt-tag">${s.statement_label}<span>${s.chunk_count} chunks · pp.${s.page_start}–${s.page_end}</span></div>`
    ).join('');

    const sel = document.getElementById('typeFilter');
    sel.innerHTML = '<option value="">All types</option>' +
        [...new Set(d.chunks.map(c => c.metadata.statement_type))]
            .map(t => `<option value="${t}">${t}</option>`).join('');

    allChunks      = d.chunks;
    filteredChunks = [...allChunks];
    currentIdx     = 0;
    renderChunk();

    document.getElementById('results').style.display = 'block';
}

// ── Chunk viewer ─────────────────────────────────────────────────────────────

function filterChunks() {
    const val      = document.getElementById('typeFilter').value;
    filteredChunks = val ? allChunks.filter(c => c.metadata.statement_type === val) : [...allChunks];
    currentIdx     = 0;
    renderChunk();
}

function renderChunk() {
    if (!filteredChunks.length) return;
    const c = filteredChunks[currentIdx];
    const m = c.metadata;
    document.getElementById('chunkBody').textContent    = c.text;
    document.getElementById('chunkCounter').textContent = (currentIdx + 1) + ' / ' + filteredChunks.length;
    document.getElementById('chunkMeta').innerHTML      = [
        ['type',    m.statement_type],
        ['chunk #', m.chunk_index],
        ['pages',   m.page_start + '–' + m.page_end],
        ['company', m.company],
        ['period',  m.period],
    ].map(([k, v]) => `<div class="kv"><span class="k">${k}:</span><span class="v">${v}</span></div>`).join('');
}

function prevChunk() { if (currentIdx > 0)                        { currentIdx--; renderChunk(); } }
function nextChunk() { if (currentIdx < filteredChunks.length - 1) { currentIdx++; renderChunk(); } }

// ── Download ─────────────────────────────────────────────────────────────────

function downloadJSON() {
    if (!fullResponse) return;
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(new Blob([JSON.stringify(fullResponse, null, 2)], { type: 'application/json' }));
    a.download = 'ingest_result.json';
    a.click();
}

async function runEmbed() {
    if (!fullResponse || !fullResponse.chunks || !fullResponse.chunks.length) {
        showAlert('No chunks available. Run ingest first.', 'error');
        return;
    }

    const btn    = document.getElementById('btnEmbed');
    const status = document.getElementById('embedStatus');

    btn.disabled = true;
    btn.textContent = 'Embedding…';
    status.className = 'embed-status';
    status.textContent = 'Generating embeddings and saving to vector store…';

    try {
        const res = await fetch(embedUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ chunks: fullResponse.chunks }),
        });
        const data = await res.json();

        if (!data.ok) {
            status.className   = 'embed-status error';
            status.textContent = 'Error: ' + (data.error ?? JSON.stringify(data));
            return;
        }

        status.className   = 'embed-status success';
        status.textContent = '✅ ' + data.embedded + ' chunk(s) embedded to vector store.';
    } catch (e) {
        status.className   = 'embed-status error';
        status.textContent = 'Request failed: ' + e.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m6.36 6.36l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m6.36-6.36l4.24-4.24"/></svg> Start Data Loader (Embed to Vector Store)`;
    }
}
</script>
</body>
</html>
