<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Extractor Tester</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0f1117;
            --bg-card:    #181c27;
            --bg-input:   #1e2336;
            --border:     #2a3050;
            --border-focus: #4f6ef7;
            --text:       #e8ecf5;
            --muted:      #6b7a9e;
            --accent:     #4f6ef7;
            --accent-dim: #1e2d6b;
            --green:      #22c55e;
            --green-dim:  #052e16;
            --red:        #f87171;
            --red-dim:    #2d0a0a;
            --yellow:     #fbbf24;
            --yellow-dim: #3b2000;
            --radius:     10px;
            --mono:       'JetBrains Mono', 'Fira Code', monospace;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .page-wrap { max-width: 900px; margin: 0 auto; }

        /* ── Header ── */
        .header { display: flex; align-items: center; gap: 14px; margin-bottom: 2rem; }
        .header-icon {
            width: 44px; height: 44px;
            background: var(--yellow-dim);
            border: 1px solid var(--yellow);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .header h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
        .header p  { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .nav-link {
            margin-left: auto;
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            transition: all .15s;
        }
        .nav-link:hover { color: var(--accent); border-color: var(--accent); }

        /* ── Card ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .card-title {
            font-size: 11px; font-weight: 600;
            letter-spacing: .08em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 1rem;
        }

        /* ── Form ── */
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .field { margin-bottom: 12px; }
        .field:last-child { margin-bottom: 0; }
        label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 5px; }
        input[type="text"] {
            width: 100%; background: var(--bg-input);
            border: 1px solid var(--border); border-radius: 7px;
            color: var(--text); font-size: 14px; padding: 9px 12px;
            outline: none; transition: border-color .15s;
        }
        input[type="text"]:focus { border-color: var(--border-focus); }

        /* ── Dropzone ── */
        #dropzone {
            border: 1.5px dashed var(--border); border-radius: var(--radius);
            padding: 2.5rem 1rem; text-align: center; cursor: pointer;
            transition: border-color .15s, background .15s; margin-bottom: 1.25rem;
        }
        #dropzone:hover, #dropzone.drag { border-color: var(--yellow); background: var(--yellow-dim); }
        #dropzone.has-file { border-color: var(--green); border-style: solid; background: var(--green-dim); }
        #dropzone svg { display: block; margin: 0 auto 10px; }
        #dropzone p   { font-size: 13px; color: var(--muted); }
        #dropzone .filename { font-size: 14px; color: var(--green); font-weight: 500; margin-top: 4px; }

        /* ── Buttons ── */
        .btn-row { display: flex; gap: 10px; margin-bottom: 1.25rem; }
        button {
            border: 1px solid var(--border); background: var(--bg-input);
            color: var(--text); font-size: 13px; font-weight: 500;
            padding: 9px 18px; border-radius: 7px; cursor: pointer;
            transition: all .15s; display: flex; align-items: center; gap: 7px;
        }
        button:hover { border-color: var(--accent); color: var(--accent); }
        button:disabled { opacity: .35; cursor: not-allowed; }
        button.primary {
            flex: 1; background: var(--yellow); border-color: var(--yellow);
            color: #000; justify-content: center; font-weight: 600;
        }
        button.primary:hover { background: #e0a800; border-color: #e0a800; color: #000; }

        /* ── Alert ── */
        .alert { padding: 10px 14px; border-radius: 7px; font-size: 13px; margin-bottom: 1.25rem; display: none; }
        .alert.info    { background: var(--accent-dim); border: 1px solid var(--accent); color: #a5b4fc; }
        .alert.success { background: var(--green-dim);  border: 1px solid var(--green);  color: var(--green); }
        .alert.error   { background: var(--red-dim);    border: 1px solid var(--red);    color: var(--red); }

        /* ── Results ── */
        #results { display: none; }

        /* ── Extracted cards ── */
        .section-title {
            font-size: 11px; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: var(--muted);
            margin-bottom: 10px; margin-top: 1.5rem;
        }
        .section-title:first-child { margin-top: 0; }

        .extracted-group {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 10px;
        }
        .extracted-group-header {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-input);
            display: flex; align-items: center; justify-content: space-between;
        }
        .extracted-group-header span {
            font-size: 12px; font-weight: 600; color: var(--text);
        }
        .coverage-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--muted);
        }
        .coverage-badge.good  { border-color: var(--green); color: var(--green); background: var(--green-dim); }
        .coverage-badge.mid   { border-color: var(--yellow); color: var(--yellow); background: var(--yellow-dim); }
        .coverage-badge.low   { border-color: var(--red); color: var(--red); background: var(--red-dim); }

        .field-row-result {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 14px;
            border-bottom: 0.5px solid var(--border);
            font-size: 13px;
        }
        .field-row-result:last-child { border-bottom: none; }
        .field-name { color: var(--muted); font-size: 12px; }
        .field-value {
            font-family: var(--mono);
            font-size: 13px;
            font-weight: 500;
            text-align: right;
        }
        .field-value.found    { color: var(--text); }
        .field-value.negative { color: var(--red); }
        .field-value.null-val { color: var(--border); font-style: italic; font-size: 12px; }
        .field-right { display: flex; align-items: center; gap: 8px; }

        .source-badge {
            font-size: 10px; padding: 2px 6px; border-radius: 4px;
            background: var(--accent-dim); color: var(--accent);
            cursor: pointer; border: 1px solid transparent;
            transition: border-color .15s;
        }
        .source-badge:hover { border-color: var(--accent); }

        /* ── Found-at modal ── */
        .overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 100;
        }
        .overlay.show { display: block; }
        .modal {
            display: none; position: fixed;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.25rem 1.5rem;
            z-index: 101; min-width: 340px; max-width: 500px;
            box-shadow: 0 8px 40px rgba(0,0,0,.6);
        }
        .modal.show { display: block; }
        .modal h4 { font-size: 13px; margin-bottom: 14px; }
        .modal-row { display: flex; gap: 10px; margin-bottom: 8px; font-size: 12px; }
        .modal-key { color: var(--muted); min-width: 130px; flex-shrink: 0; }
        .modal-val { font-family: var(--mono); color: var(--text); word-break: break-all; }
        .modal-close { width: 100%; justify-content: center; margin-top: 14px; }

        /* ── Download ── */
        .btn-download { width: 100%; justify-content: center; margin-top: 10px; }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- Header --}}
    <div class="header">
        <div class="header-icon">🔍</div>
        <div>
            <h1>Extractor Tester</h1>
            <p>Test ekstraksi data finansial dari PDF laporan keuangan</p>
        </div>
        <a class="nav-link" href="{{ route('python-tester.index') }}">← Chunking Tester</a>
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
                <input type="text" id="period" value="2024" placeholder="e.g. 2024" />
            </div>
        </div>
    </div>

    {{-- Dropzone --}}
    <div id="dropzone"
         onclick="document.getElementById('fileInput').click()"
         ondragover="event.preventDefault(); this.classList.add('drag')"
         ondragleave="this.classList.remove('drag')"
         ondrop="handleDrop(event)">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6b7a9e" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p>Drop PDF untuk diekstrak, atau klik untuk browse</p>
        <p class="filename" id="filename" style="display:none"></p>
        <input type="file" id="fileInput" accept=".pdf" style="display:none" onchange="handleFile(this.files[0])" />
    </div>

    {{-- Alert --}}
    <div class="alert" id="alert"></div>

    {{-- Actions --}}
    <div class="btn-row">
        <button onclick="checkHealth()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Health Check
        </button>
        <button class="primary" id="btnExtract" onclick="runExtract()" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Extract Financial Data
        </button>
    </div>

    {{-- Results --}}
    <div id="results">

        <div class="section-title">Balance Sheet</div>
        <div class="extracted-group" id="groupBS">
            <div class="extracted-group-header">
                <span>Laporan Posisi Keuangan (Neraca)</span>
                <span class="coverage-badge" id="badgeBS">—</span>
            </div>
            <div id="rowsBS"></div>
        </div>

        <div class="section-title">Income Statement</div>
        <div class="extracted-group" id="groupIS">
            <div class="extracted-group-header">
                <span>Laporan Laba Rugi</span>
                <span class="coverage-badge" id="badgeIS">—</span>
            </div>
            <div id="rowsIS"></div>
        </div>

        <div class="section-title">Cash Flow</div>
        <div class="extracted-group" id="groupCF">
            <div class="extracted-group-header">
                <span>Laporan Arus Kas</span>
                <span class="coverage-badge" id="badgeCF">—</span>
            </div>
            <div id="rowsCF"></div>
        </div>

        <button class="btn-download" onclick="downloadJSON()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download JSON
        </button>
    </div>

</div>

{{-- Found-at modal --}}
<div class="overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal" id="modal">
    <h4 id="modalTitle">Sumber Data</h4>
    <div id="modalBody"></div>
    <button class="modal-close" onclick="closeModal()">Tutup</button>
</div>

<script>
const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
const extractUrl = "{{ route('python-tester.extract') }}";
const healthUrl  = "{{ route('python-tester.health') }}";

let selectedFile  = null;
let fullResponse  = null;

const FIELD_LABELS = {
    // Balance sheet
    current_assets:           'Aset Lancar',
    receivables:              'Piutang Usaha',
    inventory:                'Persediaan',
    total_assets:             'Total Aset',
    current_liabilities:      'Kewajiban Lancar',
    non_current_liabilities:  'Kewajiban Jangka Panjang',
    total_liabilities:        'Total Kewajiban',
    total_equity:             'Total Ekuitas / Modal',
    // Income statement
    revenue:                  'Pendapatan',
    gross_profit:             'Laba Kotor',
    operating_profit:         'Laba Usaha',
    net_profit_before_tax:    'Laba Sebelum Pajak',
    net_profit:               'Laba Bersih',
    // Cash flow
    cfo:                      'Arus Kas Operasi',
    cfi:                      'Arus Kas Investasi',
    cff:                      'Arus Kas Pendanaan',
};

// ── File handling ─────────────────────────────────────────────────────────────

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').classList.remove('drag');
    const f = e.dataTransfer.files[0];
    if (f && f.name.toLowerCase().endsWith('.pdf')) handleFile(f);
    else showAlert('Hanya file PDF yang diterima.', 'error');
}

function handleFile(f) {
    if (!f) return;
    selectedFile = f;
    document.getElementById('dropzone').classList.add('has-file');
    const fn = document.getElementById('filename');
    fn.textContent = f.name + ' (' + (f.size / 1024).toFixed(0) + ' KB)';
    fn.style.display = 'block';
    document.getElementById('btnExtract').disabled = false;
}

// ── Alert ─────────────────────────────────────────────────────────────────────

function showAlert(msg, type = 'info') {
    const el = document.getElementById('alert');
    el.className = 'alert ' + type;
    el.textContent = msg;
    el.style.display = 'block';
}

// ── Health check ──────────────────────────────────────────────────────────────

async function checkHealth() {
    showAlert('Checking health...', 'info');
    try {
        const res  = await fetch(healthUrl, { headers: { 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        if (data.ok) showAlert('✓ Python service healthy — v' + data.data.version, 'success');
        else         showAlert('Service error: ' + (data.error ?? 'unknown'), 'error');
    } catch (e) {
        showAlert('Tidak dapat menjangkau service: ' + e.message, 'error');
    }
}

// ── Extract ───────────────────────────────────────────────────────────────────

async function runExtract() {
    if (!selectedFile) { showAlert('Pilih PDF dulu.', 'error'); return; }

    const company = document.getElementById('company').value.trim();
    const period  = document.getElementById('period').value.trim();
    if (!company || !period) { showAlert('Company dan period wajib diisi.', 'error'); return; }

    const btn = document.getElementById('btnExtract');
    btn.disabled    = true;
    btn.textContent = 'Extracting...';
    document.getElementById('results').style.display = 'none';
    showAlert('Mengirim PDF ke Python service untuk ekstraksi...', 'info');

    const fd = new FormData();
    fd.append('file',    selectedFile);
    fd.append('company', company);
    fd.append('period',  period);

    try {
        const res  = await fetch(extractUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: fd,
        });
        const data = await res.json();

        if (!data.ok) {
            showAlert('Error: ' + (data.error ?? JSON.stringify(data)), 'error');
            return;
        }

        fullResponse = data.data;
        const found = countFound(fullResponse);
        showAlert(`✓ Selesai — ${found.found}/${found.total} nilai ditemukan dari ${fullResponse.source}`, 'success');
        renderResults(fullResponse);
    } catch (e) {
        showAlert('Request gagal: ' + e.message, 'error');
    } finally {
        btn.disabled    = false;
        btn.innerHTML   = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Extract Financial Data`;
    }
}

// ── Render ────────────────────────────────────────────────────────────────────

function countFound(d) {
    const all = { ...d.balance_sheet, ...d.income_statement, ...d.cash_flow };
    const total = Object.keys(all).length;
    const found = Object.values(all).filter(v => v !== null).length;
    return { found, total };
}

function renderResults(d) {
    renderGroup('BS', d.balance_sheet,    d._found_at, 'badgeBS', 'rowsBS');
    renderGroup('IS', d.income_statement, d._found_at, 'badgeIS', 'rowsIS');
    renderGroup('CF', d.cash_flow,        d._found_at, 'badgeCF', 'rowsCF');
    document.getElementById('results').style.display = 'block';
}

function renderGroup(prefix, data, foundAt, badgeId, rowsId) {
    if (!data || typeof data !== 'object') return;

    const entries = Object.entries(data);
    const found   = entries.filter(([, v]) => v !== null).length;
    const total   = entries.length;
    const pct     = total ? Math.round(found / total * 100) : 0;

    // Coverage badge
    const badge = document.getElementById(badgeId);
    badge.textContent = `${found}/${total} field`;
    badge.className   = 'coverage-badge ' + (pct >= 80 ? 'good' : pct >= 50 ? 'mid' : 'low');

    // Rows
    document.getElementById(rowsId).innerHTML = entries.map(([field, val]) => {
        const label    = FIELD_LABELS[field] ?? field;
        const fa       = foundAt[field];
        const isNull   = val === null || val === undefined;
        const isNeg    = !isNull && val < 0;
        const valClass = isNull ? 'null-val' : isNeg ? 'negative' : 'found';
        const valText  = isNull ? 'tidak ditemukan' : formatNumber(val);

        const srcBtn = fa
            ? `<span class="source-badge" onclick="showFoundAt('${field}', event)">sumber</span>`
            : '';

        return `<div class="field-row-result">
            <span class="field-name">${label}</span>
            <div class="field-right">
                ${srcBtn}
                <span class="field-value ${valClass}">${valText}</span>
            </div>
        </div>`;
    }).join('');
}

function formatNumber(n) {
    if (n === null || n === undefined) return '—';
    const abs = Math.abs(n);
    const fmt = new Intl.NumberFormat('id-ID').format(abs);
    return (n < 0 ? '(' : '') + 'Rp ' + fmt + (n < 0 ? ')' : '');
}

// ── Found-at modal ────────────────────────────────────────────────────────────

function showFoundAt(field, e) {
    e.stopPropagation();
    if (!fullResponse || !fullResponse._found_at[field]) return;

    const fa    = fullResponse._found_at[field];
    const label = FIELD_LABELS[field] ?? field;

    document.getElementById('modalTitle').textContent = 'Sumber: ' + label;
    document.getElementById('modalBody').innerHTML = [
        ['Field',        field],
        ['Label di PDF', fa.label_in_pdf   ?? '—'],
        ['Angka utama',  fa.raw_number     ?? '—'],
        ['Match type',   fa.match_type     ?? '—'],
        ['Semua angka',  (fa.all_numbers_on_row ?? []).join(', ') || '—'],
    ].map(([k, v]) =>
        `<div class="modal-row">
            <span class="modal-key">${k}</span>
            <span class="modal-val">${v}</span>
        </div>`
    ).join('');

    document.getElementById('overlay').classList.add('show');
    document.getElementById('modal').classList.add('show');
}

function closeModal() {
    document.getElementById('overlay').classList.remove('show');
    document.getElementById('modal').classList.remove('show');
}

// ── Download ──────────────────────────────────────────────────────────────────

function downloadJSON() {
    if (!fullResponse) return;
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(new Blob([JSON.stringify(fullResponse, null, 2)], { type: 'application/json' }));
    a.download = 'extraction_result.json';
    a.click();
}
</script>
</body>
</html>
