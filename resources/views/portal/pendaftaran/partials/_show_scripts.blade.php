<script>
(function () {
    // ══════════════════════════════════════════════════════
    // CONFIG
    // ══════════════════════════════════════════════════════
    const PENDAFTARAN_ID = {{ $pend?->id ?? 'null' }};
    const URL_SAVE   = "{{ $urlSave }}";
    const URL_SUBMIT = "{{ $urlSubmit }}";
    const IS_DRAFT   = {{ $isDraft ? 'true' : 'false' }};
    const _TOKEN     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // ══════════════════════════════════════════════════════
    // TOAST (Tailwind)
    // ══════════════════════════════════════════════════════
    const toastEl  = document.getElementById('ppdb-toast');
    const toastMsg = document.getElementById('ppdb-toast-msg');
    const toastIcon= document.getElementById('ppdb-toast-icon');
    let   toastTimer;

    function showToast(type, msg) {
        clearTimeout(toastTimer);
        toastEl.className = [
            'fixed top-5 right-5 z-[9999] min-w-[280px] max-w-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-on-primary text-body-sm font-semibold transition-all duration-300',
            type === 'success' ? 'bg-secondary' : 'bg-error',
        ].join(' ');
        toastIcon.textContent = type === 'success' ? 'check_circle' : 'error';
        toastMsg.textContent  = msg;
        toastEl.classList.remove('hidden');
        toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    // ══════════════════════════════════════════════════════
    // PROGRESS UI
    // ══════════════════════════════════════════════════════
    function updateProgressUI(prog) {
        if (!prog) return;
        if (prog.formulir?.persen !== undefined) {
            const el = document.getElementById('formulir-pct');
            if (el) el.textContent = Math.round(prog.formulir.persen) + '%';
            const dot = document.getElementById('dot-formulir');
            if (dot) {
                dot.classList.toggle('bg-secondary', prog.formulir.persen >= 100);
                dot.classList.toggle('bg-error/40', prog.formulir.persen < 100);
            }
        }
        if (prog.dokumen?.persen !== undefined) {
            const el = document.getElementById('dokumen-pct');
            if (el) el.textContent = Math.round(prog.dokumen.persen) + '%';
            const counter = document.getElementById('dokumen-counter');
            if (counter) counter.textContent = `${prog.dokumen.uploaded}/${prog.dokumen.total} dokumen wajib diupload`;
            const dot = document.getElementById('dot-dokumen');
            if (dot) {
                dot.classList.toggle('bg-secondary', prog.dokumen.persen >= 100);
                dot.classList.toggle('bg-error/40', prog.dokumen.persen < 100);
            }
        }
        const fp = parseFloat(document.getElementById('formulir-pct')?.textContent || 0);
        const dp = parseFloat(document.getElementById('dokumen-pct')?.textContent || 0);
        const total = Math.round((fp + dp) / 2);
        const mainPct = document.getElementById('main-pct');
        const mainBar = document.getElementById('main-bar');
        if (mainPct) mainPct.textContent = total + '%';
        if (mainBar) mainBar.style.width = total + '%';
    }

    // ══════════════════════════════════════════════════════
    // AUTO-SAVE FORMULIR
    // ══════════════════════════════════════════════════════
    let saveTimer = null;

    function setAutosaveStatus(state, msg) {
        const ind  = document.getElementById('autosave-indicator');
        const text = document.getElementById('autosave-text');
        const icon = document.getElementById('autosave-icon');
        if (!ind || !text || !icon) return;
        const icons = { saving: 'sync', saved: 'cloud_done', error: 'cloud_off' };
        icon.textContent  = icons[state] || 'cloud';
        text.textContent  = msg;
        ind.className = [
            'flex items-center gap-1.5 text-body-sm',
            state === 'saved'  ? 'text-secondary' :
            state === 'error'  ? 'text-error'    : 'text-on-surface-variant',
        ].join(' ');
    }

    function collectFields() {
        const fields = [];
        document.querySelectorAll('#formulirForm [data-field-id]').forEach(el => {
            if (!['INPUT','SELECT','TEXTAREA'].includes(el.tagName)) return;
            if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;
            const fid = parseInt(el.dataset.fieldId);
            if (!fid) return;
            if (fields.find(f => f.formulir_field_id === fid)) return;
            fields.push({ formulir_field_id: fid, value: el.value });
        });
        return fields;
    }

    async function doAutoSave() {
        if (!IS_DRAFT) return;
        const fields = collectFields();
        if (!fields.length) return;
        setAutosaveStatus('saving', 'Menyimpan...');
        try {
            const res  = await fetch(URL_SAVE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ fields }),
            });
            const data = await res.json();
            if (data.success) {
                const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                setAutosaveStatus('saved', 'Disimpan ' + now);
                if (data.progress) updateProgressUI(data.progress);
            } else {
                setAutosaveStatus('error', 'Gagal menyimpan');
            }
        } catch {
            setAutosaveStatus('error', 'Gagal menyimpan');
        }
    }

    document.querySelectorAll('.auto-save-field').forEach(el => {
        const evt = (el.tagName === 'SELECT' || el.type === 'radio' || el.type === 'checkbox') ? 'change' : 'blur';
        el.addEventListener(evt, () => {
            if (!IS_DRAFT) return;
            clearTimeout(saveTimer);
            setAutosaveStatus('saving', 'Menunggu...');
            saveTimer = setTimeout(doAutoSave, 1500);
        });
    });

    // ══════════════════════════════════════════════════════
    // UPLOAD DOKUMEN
    // ══════════════════════════════════════════════════════
    window.handleDrop = function (event, syaratId, uploadUrl) {
        event.preventDefault();
        const dz = document.getElementById('dropzone-' + syaratId);
        if (dz) dz.classList.remove('!border-primary', '!bg-primary/10');
        const file = event.dataTransfer.files[0];
        if (file) uploadFile(file, syaratId, uploadUrl);
    };

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('file-input-hidden') && e.target.type !== 'file') return;
        const file = e.target.files[0];
        if (!file) return;
        const syaratId  = e.target.dataset.syaratId;
        const uploadUrl = e.target.dataset.uploadUrl;
        if (syaratId && uploadUrl) uploadFile(file, syaratId, uploadUrl);
    });

    function uploadFile(file, syaratId, uploadUrl) {
        if (file.size > 5 * 1024 * 1024) { showDokError(syaratId, 'Ukuran file melebihi 5MB.'); return; }
        const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) { showDokError(syaratId, 'Format tidak didukung. Gunakan PDF, JPG, atau PNG.'); return; }

        clearDokError(syaratId);
        showUploadProgress(syaratId, true);

        const fd  = new FormData();
        fd.append('dokumen', file);
        fd.append('_token', _TOKEN);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', _TOKEN);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) setDokProgress(syaratId, Math.round((e.loaded / e.total) * 100));
        };
        xhr.onload = () => {
            showUploadProgress(syaratId, false);
            try {
                const r = JSON.parse(xhr.responseText);
                if (r.success && r.dokumen) {
                    renderDokumenUploaded(syaratId, r.dokumen);
                    showToast('success', r.message || 'Dokumen berhasil diupload.');
                    if (r.progress) updateProgressUI(r.progress);
                } else {
                    showDokError(syaratId, r.message || 'Gagal upload.');
                }
            } catch { showDokError(syaratId, 'Terjadi kesalahan.'); }
        };
        xhr.onerror = () => { showUploadProgress(syaratId, false); showDokError(syaratId, 'Upload gagal. Periksa koneksi Anda.'); };
        xhr.send(fd);
    }

    function showUploadProgress(syaratId, show) {
        const el = document.getElementById('dok-progress-' + syaratId);
        if (el) { el.classList.toggle('hidden', !show); if (!show) setDokProgress(syaratId, 0); }
    }

    function setDokProgress(syaratId, pct) {
        const bar = document.getElementById('dok-pbar-' + syaratId);
        const lbl = document.getElementById('dok-pbar-pct-' + syaratId);
        if (bar) bar.style.width = pct + '%';
        if (lbl) lbl.textContent = pct + '%';
    }

    function renderDokumenUploaded(syaratId, dok) {
        const card  = document.getElementById('dok-card-' + syaratId);
        const isPdf = (dok.mime_type || '').includes('pdf');
        const sizeKb = ((dok.ukuran_file || 0) / 1024).toFixed(1);
        const iconName = isPdf ? 'picture_as_pdf' : 'image';
        const iconCls  = isPdf ? 'text-red-500' : 'text-blue-500';
        const iconBg   = isPdf ? 'bg-red-50' : 'bg-blue-50';

        const dz = document.getElementById('dropzone-' + syaratId);
        if (dz) dz.remove();

        const badge = document.getElementById('dok-status-' + syaratId);
        if (badge) badge.innerHTML = '<span class="material-symbols-outlined text-[13px]">hourglass_empty</span>Menunggu';

        const rowHtml = `
        <div class="flex items-center gap-3" id="dok-file-row-${syaratId}">
            <div class="w-10 h-10 rounded-xl ${iconBg} flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined ${iconCls} text-[20px]" style="font-variation-settings:'FILL' 1">${iconName}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-label-md font-semibold text-on-surface truncate">${dok.nama_file}</div>
                <div class="text-body-sm text-on-surface-variant">${sizeKb} KB</div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button"
                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors"
                        onclick="previewDokumen('${dok.url}','${isPdf ? 'pdf' : 'image'}','${dok.nama_file}')"
                        title="Preview">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
                <button type="button"
                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors"
                        onclick="hapusDokumen(${dok.id},${syaratId},'${dok.nama_file}')"
                        title="Hapus">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                </button>
            </div>
        </div>`;

        const existing = document.getElementById('dok-file-row-' + syaratId);
        const container = card.querySelector('.flex.flex-col.justify-end, .p-4');
        if (existing) existing.outerHTML = rowHtml;
        else if (container) container.insertAdjacentHTML('afterbegin', rowHtml);
    }

    window.hapusDokumen = async function (dokumenId, syaratId, namaFile) {
        const result = await Swal.fire({
            title: 'Hapus Dokumen?',
            text: `Apakah Anda yakin ingin menghapus "${namaFile}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });
        if (!result.isConfirmed) return;

        const url = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${dokumenId}`;
        Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const res  = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
            const data = await res.json();
            Swal.close();
            if (data.success) {
                const row = document.getElementById('dok-file-row-' + syaratId);
                if (row) row.remove();
                const badge = document.getElementById('dok-status-' + syaratId);
                if (badge) badge.innerHTML = '<span class="material-symbols-outlined text-[13px]">cloud_upload</span>Belum Upload';

                const card      = document.getElementById('dok-card-' + syaratId);
                const uploadUrl = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${syaratId}`;
                card.insertAdjacentHTML('beforeend', `
                    <div class="dok-dropzone border-2 border-dashed border-outline-variant hover:border-primary rounded-xl p-6 flex flex-col items-center text-center cursor-pointer transition-colors bg-surface-container-lowest hover:bg-primary/5 m-4 mt-0"
                         id="dropzone-${syaratId}"
                         data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}"
                         onclick="document.getElementById('file-input-${syaratId}').click()"
                         ondragover="event.preventDefault();this.classList.add('!border-primary','!bg-primary/10')"
                         ondragleave="this.classList.remove('!border-primary','!bg-primary/10')"
                         ondrop="handleDrop(event, ${syaratId}, '${uploadUrl}')">
                        <span class="material-symbols-outlined text-[36px] text-primary/40 mb-2">cloud_upload</span>
                        <div class="text-body-sm text-on-surface-variant">Seret file ke sini atau <span class="text-primary font-semibold">klik untuk memilih</span></div>
                        <div class="text-body-xs text-on-surface-variant mt-1 opacity-60">Format: PDF, JPG, PNG · Maks 5MB</div>
                        <input type="file" id="file-input-${syaratId}" class="file-input-hidden hidden"
                               accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}">
                    </div>
                `);

                if (data.progress) updateProgressUI(data.progress);
                Swal.fire({ title: 'Terhapus!', text: data.message || 'Dokumen berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire('Gagal!', data.message || 'Gagal menghapus dokumen.', 'error');
            }
        } catch {
            Swal.fire('Error!', 'Terjadi kesalahan sistem. Coba lagi nanti.', 'error');
        }
    };

    function showDokError(syaratId, msg) {
        const el = document.getElementById('dok-error-' + syaratId);
        if (el) { el.textContent = '⚠ ' + msg; el.classList.remove('hidden'); }
    }
    function clearDokError(syaratId) {
        const el = document.getElementById('dok-error-' + syaratId);
        if (el) { el.textContent = ''; el.classList.add('hidden'); }
    }

    // ══════════════════════════════════════════════════════
    // PREVIEW DOKUMEN (Custom Modal)
    // ══════════════════════════════════════════════════════
    window.previewDokumen = function (url, type, nama) {
        document.getElementById('preview-filename').textContent = nama;
        const body = document.getElementById('preview-body');
        body.innerHTML = type === 'pdf'
            ? `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`
            : `<div class="text-center p-4"><img src="${url}" class="max-h-[70vh] mx-auto rounded-xl" alt="${nama}"></div>`;
        const modal = document.getElementById('modalPreview');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.tutupModalPreview = function () {
        const modal = document.getElementById('modalPreview');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    // ══════════════════════════════════════════════════════
    // SUBMIT PENDAFTARAN (Custom Modal)
    // ══════════════════════════════════════════════════════
    window.bukaModalSubmit = async function () {
        await doAutoSave();

        const fPct = parseFloat(document.getElementById('formulir-pct')?.textContent || 0);
        const dPct = parseFloat(document.getElementById('dokumen-pct')?.textContent || 0);

        const setCheck = (id, pct) => {
            const ok = pct >= 100;
            const iconEl      = document.getElementById('ck-' + id + '-icon');
            const pctEl       = document.getElementById('ck-' + id + '-pct');
            const subEl       = document.getElementById('ck-' + id + '-sub');
            const containerEl = document.getElementById('ck-' + id);

            if (iconEl) iconEl.innerHTML = ok
                ? '<span class="material-symbols-outlined text-[22px] text-secondary" style="font-variation-settings:\'FILL\' 1">check_circle</span>'
                : '<span class="material-symbols-outlined text-[22px] text-amber-500" style="font-variation-settings:\'FILL\' 1">warning</span>';
            if (pctEl) pctEl.textContent = pct + '%';
            if (subEl) subEl.textContent  = ok ? 'Lengkap' : 'Belum lengkap';
            if (containerEl) {
                containerEl.classList.toggle('border-green-200', ok);
                containerEl.classList.toggle('border-amber-200', !ok);
                containerEl.classList.toggle('border-outline-variant', false);
            }
        };
        setCheck('formulir', fPct);
        setCheck('dokumen',  dPct);

        const warnBox  = document.getElementById('submit-warning-box');
        const warnList = document.getElementById('submit-kekurangan-list');
        if (warnBox && warnList) {
            const kurang = [];
            if (fPct < 100) kurang.push('Formulir: ' + fPct + '% — belum semua field wajib terisi');
            if (dPct < 100) kurang.push('Dokumen: '  + dPct + '% — belum semua dokumen wajib diupload');
            if (kurang.length) {
                warnBox.classList.remove('hidden');
                warnBox.classList.add('flex');
                warnList.innerHTML = kurang.map(k => `<li>${k}</li>`).join('');
            } else {
                warnBox.classList.add('hidden');
                warnBox.classList.remove('flex');
            }
        }

        const modal = document.getElementById('modalSubmit');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.tutupModalSubmit = function () {
        const modal = document.getElementById('modalSubmit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    window.doSubmit = async function () {
        const btn = document.getElementById('btn-confirm-submit');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Mengirim...';
        try {
            const res  = await fetch(URL_SUBMIT, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
            const data = await res.json();
            tutupModalSubmit();
            if (data.success) {
                showToast('success', data.message || 'Pendaftaran berhasil dikirim!');
                setTimeout(() => window.location.reload(), 1800);
            } else {
                showToast('error', data.message || 'Gagal mengirim pendaftaran.');
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">send</span>Kirim Sekarang';
            }
        } catch {
            showToast('error', 'Terjadi kesalahan. Coba lagi.');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">send</span>Kirim Sekarang';
        }
    };

    // Close modals on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { tutupModalPreview(); tutupModalSubmit(); }
    });

    // Animate main progress bar on load
    document.addEventListener('DOMContentLoaded', () => {
        const bar = document.getElementById('main-bar');
        if (bar) { const w = bar.style.width; bar.style.width = '0'; setTimeout(() => { bar.style.width = w; }, 300); }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>
