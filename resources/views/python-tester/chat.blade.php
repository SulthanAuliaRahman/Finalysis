<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RAG Chat Tester</title>
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
            max-width: 720px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 4rem);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 1.25rem;
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

        /* ── Chat area ── */
        .chat-card {
            flex: 1;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .msg {
            max-width: 85%;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .msg.user {
            align-self: flex-end;
            background: var(--accent);
            color: #fff;
            border-bottom-right-radius: 2px;
        }
        .msg.assistant {
            align-self: flex-start;
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text);
            border-bottom-left-radius: 2px;
        }
        .msg.error {
            align-self: flex-start;
            background: var(--red-dim);
            border: 1px solid var(--red);
            color: var(--red);
        }
        .msg.pending {
            align-self: flex-start;
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--muted);
            font-style: italic;
        }

        .empty-state {
            margin: auto;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        /* ── Input area ── */
        .chat-input-row {
            display: flex;
            gap: 10px;
            padding: 1rem;
            border-top: 1px solid var(--border);
        }
        textarea#chatInput {
            flex: 1;
            resize: none;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            padding: 10px 12px;
            outline: none;
            min-height: 44px;
            max-height: 120px;
            transition: border-color .15s;
        }
        textarea#chatInput:focus { border-color: var(--border-focus); }

        button#btnSend {
            border: 1px solid var(--accent);
            background: var(--accent);
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            padding: 0 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all .15s;
        }
        button#btnSend:hover    { background: #3a57d4; border-color: #3a57d4; }
        button#btnSend:disabled { opacity: .35; cursor: not-allowed; }

        .back-link {
            display: inline-block;
            margin-top: 12px;
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
        }
        .back-link:hover { color: var(--accent); }
    </style>
</head>
<body>
<div class="page-wrap">

    <div class="header">
        <div class="header-icon">💬</div>
        <div>
            <h1>RAG Chat Tester</h1>
            <p>Tanya jawab berdasarkan dokumen yang sudah di-embed</p>
        </div>
    </div>

    <div class="chat-card">
        <div class="chat-messages" id="chatMessages">
            <div class="empty-state" id="emptyState">
                Belum ada percakapan.<br>
                Pastikan sudah menjalankan "Start Data Loader" pada dokumen sebelumnya.
            </div>
        </div>

        <div class="chat-input-row">
            <textarea id="chatInput" placeholder="Tanya sesuatu tentang dokumen yang sudah di-embed…" rows="1"></textarea>
            <button id="btnSend" onclick="sendMessage()">Send</button>
        </div>
    </div>

    <a class="back-link" href="{{ route('python-tester.index') }}">← Back to Document Tester</a>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const askUrl    = "{{ route('python-tester.chat.ask') }}";

const messagesEl  = document.getElementById('chatMessages');
const emptyState  = document.getElementById('emptyState');
const inputEl     = document.getElementById('chatInput');
const sendBtn     = document.getElementById('btnSend');

// Auto-resize textarea
inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
});

// Send on Enter (Shift+Enter = newline)
inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function appendMessage(text, role) {
    if (emptyState) emptyState.remove();

    const div = document.createElement('div');
    div.className = 'msg ' + role;
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
}

async function sendMessage() {
    const text = inputEl.value.trim();
    if (!text) return;

    appendMessage(text, 'user');
    inputEl.value = '';
    inputEl.style.height = 'auto';

    sendBtn.disabled = true;
    inputEl.disabled = true;
    const pending = appendMessage('Thinking…', 'pending');

    try {
        const res  = await fetch(askUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        });
        const data = await res.json();

        pending.remove();

        if (!data.ok) {
            appendMessage('Error: ' + (data.error ?? JSON.stringify(data)), 'error');
            return;
        }

        appendMessage(data.answer, 'assistant');
    } catch (e) {
        pending.remove();
        appendMessage('Request failed: ' + e.message, 'error');
    } finally {
        sendBtn.disabled = false;
        inputEl.disabled = false;
        inputEl.focus();
    }
}
</script>
</body>
</html>
