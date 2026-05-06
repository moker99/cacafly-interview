@extends('layouts.app')

@section('title', '圖片上傳 – Cacafly Demo')

@push('styles')
<style>
    h2 { margin-bottom: 6px; }
    .sub { color: #606770; font-size: .9rem; margin-bottom: 24px; }

    /* ── Drop Zone ── */
    .drop-zone {
        border: 2.5px dashed #1877f2; border-radius: 10px;
        padding: 40px 24px; text-align: center;
        cursor: pointer; transition: background .15s;
        position: relative;
    }
    .drop-zone.dragover { background: #e7f0fd; }
    .drop-zone input[type=file] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer;
    }
    .drop-zone .icon { font-size: 2.5rem; margin-bottom: 8px; }
    .drop-zone p { color: #606770; font-size: .95rem; }
    .drop-zone strong { color: #1877f2; }

    /* ── Preview List ── */
    .file-list { margin-top: 24px; display: flex; flex-direction: column; gap: 14px; }

    .file-item {
        display: flex; align-items: center; gap: 14px;
        border: 1px solid #dddfe2; border-radius: 8px; padding: 12px 14px;
        background: #fafafa;
    }
    .file-item img {
        width: 60px; height: 60px; object-fit: cover;
        border-radius: 6px; flex-shrink: 0;
    }
    .file-meta { flex: 1; min-width: 0; }
    .file-meta .name {
        font-weight: 600; font-size: .95rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .file-meta .size { font-size: .82rem; color: #8d949e; }

    /* ── Progress ── */
    .progress-wrap { margin-top: 6px; }
    .progress-bar-bg {
        background: #e4e6eb; border-radius: 4px; height: 6px; overflow: hidden;
    }
    .progress-bar {
        height: 6px; border-radius: 4px; background: #1877f2;
        width: 0; transition: width .3s ease;
    }

    .status-badge {
        font-size: .78rem; font-weight: 600;
        padding: 3px 9px; border-radius: 20px;
        white-space: nowrap;
    }
    .status-pending  { background: #e4e6eb; color: #606770; }
    .status-uploading{ background: #dde8fc; color: #1877f2; }
    .status-done     { background: #d4edda; color: #155724; }
    .status-error    { background: #f8d7da; color: #721c24; }

    /* ── Action Row ── */
    .action-row {
        display: flex; align-items: center; gap: 14px;
        margin-top: 22px;
    }
    .counter { color: #606770; font-size: .9rem; }

    /* ── Toast ── */
    .toast {
        position: fixed; bottom: 32px; right: 32px;
        background: #155724; color: #fff;
        padding: 14px 22px; border-radius: 8px;
        font-size: .95rem; font-weight: 600;
        box-shadow: 0 4px 16px rgba(0,0,0,.18);
        opacity: 0; transform: translateY(16px);
        transition: opacity .3s, transform .3s;
        pointer-events: none; z-index: 9999;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="card">
    <h2>Q2 ─ 上傳圖片到雲端</h2>
    <p class="sub">最多選擇 3 張圖片，每張限制 10 MB，支援 JPG / PNG / GIF / WebP。</p>

    {{-- Drop Zone --}}
    <div class="drop-zone" id="dropZone">
        <input type="file" id="fileInput" accept="image/*" multiple>
        <div class="icon">🖼️</div>
        <p><strong>點擊選擇</strong> 或將圖片拖放到此處</p>
        <p style="margin-top:4px;font-size:.82rem;">最多 3 張</p>
    </div>

    {{-- File Preview List --}}
    <div class="file-list" id="fileList"></div>

    {{-- Action --}}
    <div class="action-row" id="actionRow" style="display:none!important">
        <button class="btn btn-primary" id="uploadBtn" onclick="startUpload()">
            ⬆ 開始上傳
        </button>
        <span class="counter" id="counter"></span>
    </div>
</div>

{{-- Toast Notification --}}
<div class="toast" id="toast">✅ 全部圖片上傳完成！</div>
@endsection

@push('scripts')
<script>
const MAX = 3;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let files = [];   // {file, status, previewEl, barEl, badgeEl}

// ── File Input / Drop ─────────────────────────────────────────────────────────

const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileList  = document.getElementById('fileList');
const actionRow = document.getElementById('actionRow');
const counter   = document.getElementById('counter');
const uploadBtn = document.getElementById('uploadBtn');

fileInput.addEventListener('change', e => addFiles(e.target.files));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    addFiles(e.dataTransfer.files);
});

function addFiles(incoming) {
    const remaining = MAX - files.length;
    if (remaining <= 0) return;
    const accepted = Array.from(incoming).filter(f => f.type.startsWith('image/')).slice(0, remaining);

    accepted.forEach(file => {
        const entry = { file, status: 'pending', previewEl: null, barEl: null, badgeEl: null };
        files.push(entry);
        renderItem(entry);
    });
    refreshUI();
}

// ── Render one file row ───────────────────────────────────────────────────────

function renderItem(entry) {
    const { file } = entry;
    const reader = new FileReader();

    const item = document.createElement('div');
    item.className = 'file-item';
    item.innerHTML = `
        <img src="" alt="preview">
        <div class="file-meta">
            <div class="name">${escHtml(file.name)}</div>
            <div class="size">${formatBytes(file.size)}</div>
            <div class="progress-wrap">
                <div class="progress-bar-bg"><div class="progress-bar"></div></div>
            </div>
        </div>
        <span class="status-badge status-pending">待上傳</span>
    `;

    entry.previewEl = item.querySelector('img');
    entry.barEl     = item.querySelector('.progress-bar');
    entry.badgeEl   = item.querySelector('.status-badge');

    reader.onload = e => { entry.previewEl.src = e.target.result; };
    reader.readAsDataURL(file);

    fileList.appendChild(item);
}

// ── Upload ────────────────────────────────────────────────────────────────────

async function startUpload() {
    const pending = files.filter(e => e.status === 'pending');
    if (!pending.length) return;

    uploadBtn.disabled = true;
    fileInput.disabled = true;

    // Upload sequentially so progress is visible per file
    for (const entry of pending) {
        await uploadFile(entry);
    }

    const allDone = files.every(e => e.status === 'done');
    if (allDone) showToast();

    uploadBtn.disabled = false;
}

function uploadFile(entry) {
    return new Promise(resolve => {
        setBadge(entry, 'uploading', '上傳中…');
        animateBar(entry, 0, 60);    // fake progress to 60% while waiting

        const fd = new FormData();
        fd.append('image', entry.file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("upload.store") }}');
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = e => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                entry.barEl.style.width = pct + '%';
            }
        };

        xhr.onload = () => {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                entry.barEl.style.width = '100%';
                entry.status = 'done';
                setBadge(entry, 'done', '✓ 完成');
            } else {
                entry.status = 'error';
                setBadge(entry, 'error', '✗ 失敗');
            }
            refreshUI();
            resolve();
        };

        xhr.onerror = () => {
            entry.status = 'error';
            setBadge(entry, 'error', '✗ 失敗');
            refreshUI();
            resolve();
        };

        xhr.send(fd);
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function animateBar(entry, from, to) {
    let cur = from;
    const iv = setInterval(() => {
        cur += 2;
        if (cur >= to || entry.status !== 'uploading') { clearInterval(iv); return; }
        entry.barEl.style.width = cur + '%';
    }, 80);
}

function setBadge(entry, cls, text) {
    const b = entry.badgeEl;
    b.className = 'status-badge status-' + cls;
    b.textContent = text;
}

function refreshUI() {
    const total   = files.length;
    const done    = files.filter(e => e.status === 'done').length;
    const pending = files.filter(e => e.status === 'pending').length;

    counter.textContent = `${done} / ${total} 張已上傳`;
    actionRow.style.display = (total > 0) ? 'flex' : 'none';
    fileInput.disabled = total >= MAX;
    dropZone.querySelector('input').disabled = total >= MAX;
}

function showToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
