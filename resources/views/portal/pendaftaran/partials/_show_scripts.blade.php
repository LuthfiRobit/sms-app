<script>
    // ══════════════════════════════════════════════════════
    // CONFIG
    // ══════════════════════════════════════════════════════
    const PENDAFTARAN_ID = {{ $pend?->id ?? 'null' }};
    const URL_SAVE = "{{ $urlSave }}";
    const URL_SUBMIT = "{{ $urlSubmit }}";
    const IS_DRAFT = {{ $isDraft ? 'true' : 'false' }};
    const _TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // ══════════════════════════════════════════════════════
    // AUTO-SAVE FORMULIR
    // ══════════════════════════════════════════════════════
    let saveTimer = null;

    function setAutosaveStatus(state, msg) {
        const ind = document.getElementById('autosave-indicator');
        const text = document.getElementById('autosave-text');
        if (!ind || !text) return;
        ind.className = 'autosave-indicator ' + state;
        const icons = { saving: 'bi-arrow-repeat', saved: 'bi-cloud-check', error: 'bi-cloud-slash' };
        ind.querySelector('i').className = 'bi ' + (icons[state] || 'bi-cloud') + ' me-1';
        text.textContent = msg;
    }

    function collectFields() {
        const fields = [];
        document.querySelectorAll('#formulirForm [data-field-id]').forEach(el => {
            if ((el.tagName !== 'INPUT' && el.tagName !== 'SELECT' && el.tagName !== 'TEXTAREA')) return;
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
            const res = await fetch(URL_SAVE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ fields })
            });
            const data = await res.json();
            if (data.success) {
                const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                setAutosaveStatus('saved', 'Disimpan ' + now);
                if (data.progress) updateProgressUI(data.progress);
            } else {
                setAutosaveStatus('error', 'Gagal menyimpan');
            }
        } catch (e) {
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

    function updateProgressUI(prog) {
        if (!prog) return;

        // Update Formulir
        if (prog.formulir && prog.formulir.persen !== undefined) {
            const fpEl = document.getElementById('formulir-pct');
            if (fpEl) fpEl.textContent = Math.round(prog.formulir.persen) + '%';
        }

        // Update Dokumen
        if (prog.dokumen && prog.dokumen.persen !== undefined) {
            const dpEl = document.getElementById('dokumen-pct');
            if (dpEl) dpEl.textContent = Math.round(prog.dokumen.persen) + '%';

            const counter = document.getElementById('dokumen-counter');
            if (counter) {
                counter.textContent = `${prog.dokumen.uploaded}/${prog.dokumen.total} dokumen wajib diupload`;
            }
        }

        // Update Total Progress recalculation
        const fp = parseFloat(document.getElementById('formulir-pct')?.textContent || 0);
        const dp = parseFloat(document.getElementById('dokumen-pct')?.textContent || 0);
        const total = Math.round((fp + dp) / 2);

        const mainPct = document.getElementById('main-pct');
        const mainBar = document.getElementById('main-bar');
        if (mainPct) mainPct.textContent = total + '%';
        if (mainBar) mainBar.style.width = total + '%';
    }

    // ══════════════════════════════════════════════════════
    // UPLOAD DOKUMEN
    // ══════════════════════════════════════════════════════
    function handleDrop(event, syaratId, uploadUrl) {
        event.preventDefault();
        document.getElementById('dropzone-' + syaratId)?.classList.remove('dragover');
        const file = event.dataTransfer.files[0];
        if (file) uploadFile(file, syaratId, uploadUrl);
    }

    // File input change handler (delegated)
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('file-input-hidden') && e.target.type !== 'file') return;
        const file = e.target.files[0];
        if (!file) return;
        const syaratId = e.target.dataset.syaratId;
        const uploadUrl = e.target.dataset.uploadUrl;
        if (syaratId && uploadUrl) uploadFile(file, syaratId, uploadUrl);
    });

    function uploadFile(file, syaratId, uploadUrl) {
        if (file.size > 5 * 1024 * 1024) { showDokError(syaratId, 'Ukuran file melebihi 5MB.'); return; }
        const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) { showDokError(syaratId, 'Format tidak didukung. Gunakan PDF, JPG, atau PNG.'); return; }

        clearDokError(syaratId);
        showUploadProgress(syaratId, true);

        const fd = new FormData();
        fd.append('dokumen', file);
        fd.append('_token', _TOKEN);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', _TOKEN);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                setDokProgress(syaratId, pct);
            }
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
            } catch (e) { showDokError(syaratId, 'Terjadi kesalahan.'); }
        };
        xhr.onerror = () => { showUploadProgress(syaratId, false); showDokError(syaratId, 'Upload gagal. Periksa koneksi Anda.'); };
        xhr.send(fd);
    }

    function showUploadProgress(syaratId, show) {
        const el = document.getElementById('dok-progress-' + syaratId);
        if (el) { el.classList.toggle('d-none', !show); if (!show) setDokProgress(syaratId, 0); }
    }

    function setDokProgress(syaratId, pct) {
        const bar = document.getElementById('dok-pbar-' + syaratId);
        const lbl = document.getElementById('dok-pbar-pct-' + syaratId);
        if (bar) bar.style.width = pct + '%';
        if (lbl) lbl.textContent = pct + '%';
    }

    function renderDokumenUploaded(syaratId, dok) {
        const card = document.getElementById('dok-card-' + syaratId);
        const isPdf = (dok.mime_type || '').includes('pdf');
        const sizeKb = ((dok.ukuran_file || 0) / 1024).toFixed(1);

        // Remove dropzone
        const dz = document.getElementById('dropzone-' + syaratId);
        if (dz) dz.remove();

        // Update status badge
        const badge = document.getElementById('dok-status-' + syaratId);
        if (badge) badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menunggu';

        // Inject file row
        const existing = document.getElementById('dok-file-row-' + syaratId);
        const rowHtml = `
    <div class="dok-file-row" id="dok-file-row-${syaratId}">
        <div class="dok-file-info">
            <i class="bi bi-file-earmark-${isPdf ? 'pdf text-danger' : 'image text-primary'} fs-5"></i>
            <div>
                <div class="dok-file-name">${dok.nama_file}</div>
                <div class="dok-file-size">${sizeKb} KB</div>
            </div>
        </div>
        <div class="dok-file-actions">
            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewDokumen('${dok.url}','${isPdf ? 'pdf' : 'image'}','${dok.nama_file}')">
                <i class="bi bi-eye me-1"></i>Preview
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusDokumen(${dok.id},${syaratId},'${dok.nama_file}')">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </div>
    </div>`;
        if (existing) existing.outerHTML = rowHtml;
        else card.insertAdjacentHTML('beforeend', rowHtml);
    }

    async function hapusDokumen(dokumenId, syaratId, namaFile) {
        const result = await Swal.fire({
            title: 'Hapus Dokumen?',
            text: `Apakah Anda yakin ingin menghapus "${namaFile}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        });

        if (!result.isConfirmed) return;

        const url = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${dokumenId}`;

        try {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': _TOKEN,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();
            Swal.close();

            if (data.success) {
                const row = document.getElementById('dok-file-row-' + syaratId);
                if (row) row.remove();

                const badge = document.getElementById('dok-status-' + syaratId);
                if (badge) badge.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Belum Upload';

                const card = document.getElementById('dok-card-' + syaratId);
                const uploadUrl = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${syaratId}`;
                card.insertAdjacentHTML('beforeend', `
                    <div class="dok-dropzone" id="dropzone-${syaratId}"
                         data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}"
                         onclick="document.getElementById('file-input-${syaratId}').click()"
                         ondragover="event.preventDefault();this.classList.add('dragover')"
                         ondragleave="this.classList.remove('dragover')"
                         ondrop="handleDrop(event, ${syaratId}, '${uploadUrl}')">
                        <i class="bi bi-cloud-arrow-up dok-drop-icon"></i>
                        <div class="dok-drop-text">Seret file ke sini atau <span class="text-success fw-semibold">klik untuk memilih</span></div>
                        <div class="dok-drop-hint">Format: PDF, JPG, PNG · Maks 5MB</div>
                        <input type="file" id="file-input-${syaratId}" class="file-input-hidden"
                               accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}">
                    </div>
                `);

                Swal.fire({
                    title: 'Terhapus!',
                    text: data.message || 'Dokumen berhasil dihapus.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (data.progress) updateProgressUI(data.progress);
            } else {
                Swal.fire('Gagal!', data.message || 'Gagal menghapus dokumen.', 'error');
            }
        } catch (e) {
            Swal.fire('Error!', 'Terjadi kesalahan sistem. Coba lagi nanti.', 'error');
        }
    }



    function showDokError(syaratId, msg) {
        const el = document.getElementById('dok-error-' + syaratId);
        if (el) { el.textContent = '\u26a0 ' + msg; el.classList.remove('d-none'); }
    }
    function clearDokError(syaratId) {
        const el = document.getElementById('dok-error-' + syaratId);
        if (el) { el.textContent = ''; el.classList.add('d-none'); }
    }

    // ══════════════════════════════════════════════════════
    // PREVIEW DOKUMEN
    // ══════════════════════════════════════════════════════
    function previewDokumen(url, type, nama) {
        document.getElementById('preview-filename').textContent = nama;
        const body = document.getElementById('preview-body');
        body.innerHTML = type === 'pdf'
            ? `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`
            : `<div class="text-center p-2"><img src="${url}" class="img-fluid" style="max-height:70vh;border-radius:8px;"></div>`;
        new bootstrap.Modal(document.getElementById('modalPreview')).show();
    }

    // ══════════════════════════════════════════════════════
    // SUBMIT PENDAFTARAN
    // ══════════════════════════════════════════════════════
    async function bukaModalSubmit() {
        // Simpan dulu sebelum buka modal
        await doAutoSave();

        // Ambil progress terbaru dari DOM
        const fPct = parseFloat(document.getElementById('formulir-pct')?.textContent || 0);
        const dPct = parseFloat(document.getElementById('dokumen-pct')?.textContent || 0);

        // Update checklist
        const setCheck = (id, pct, label) => {
            const ok = pct >= 100;
            const iconEl = document.getElementById('ck-' + id + '-icon');
            const pctEl = document.getElementById('ck-' + id + '-pct');
            const subEl = document.getElementById('ck-' + id + '-sub');
            const containerEl = document.getElementById('ck-' + id);

            if (iconEl) iconEl.innerHTML = ok ? '<i class="bi bi-check-circle-fill text-success"></i>'
                    : '<i class="bi bi-exclamation-circle-fill text-warning"></i>';
            if (pctEl) pctEl.textContent = pct + '%';
            if (subEl) subEl.textContent = ok ? 'Lengkap' : 'Belum lengkap';
            if (containerEl) containerEl.style.borderColor = ok ? '#86efac' : '#fcd34d';
        };
        setCheck('formulir', fPct);
        setCheck('dokumen', dPct);

        // Warning kekurangan
        const warnBox = document.getElementById('submit-warning-box');
        const warnList = document.getElementById('submit-kekurangan-list');

        if (warnBox && warnList) {
            const kurang = [];
            if (fPct < 100) kurang.push('Formulir: ' + fPct + '% — belum semua field wajib terisi');
            if (dPct < 100) kurang.push('Dokumen: ' + dPct + '% — belum semua dokumen wajib diupload');

            if (kurang.length) {
                warnBox.classList.remove('d-none');
                warnList.innerHTML = kurang.map(k => `<li>${k}</li>`).join('');
            } else {
                warnBox.classList.add('d-none');
            }
        }

        new bootstrap.Modal(document.getElementById('modalSubmit')).show();
    }

    async function doSubmit() {
        const btn = document.getElementById('btn-confirm-submit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        try {
            const res = await fetch(URL_SUBMIT, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            bootstrap.Modal.getInstance(document.getElementById('modalSubmit'))?.hide();
            if (data.success) {
                showToast('success', data.message || 'Pendaftaran berhasil dikirim!');
                setTimeout(() => window.location.reload(), 1800);
            } else {
                showToast('error', data.message || 'Gagal mengirim pendaftaran.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-2"></i>Ya, Kirim Sekarang';
            }
        } catch (e) {
            showToast('error', 'Terjadi kesalahan. Coba lagi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-2"></i>Ya, Kirim Sekarang';
        }
    }

    // ══════════════════════════════════════════════════════
    // TOAST
    // ══════════════════════════════════════════════════════
    function showToast(type, msg) {
        const toast = document.getElementById('ppdbToast');
        const msgEl = document.getElementById('ppdbToastMsg');
        toast.className = 'toast align-items-center border-0 text-white bg-' + (type === 'success' ? 'success' : 'danger');
        msgEl.textContent = msg;
        new bootstrap.Toast(toast, { delay: 4000 }).show();
    }

    // Animate progress on load
    document.addEventListener('DOMContentLoaded', () => {
        const bar = document.getElementById('main-bar');
        if (bar) { const w = bar.style.width; bar.style.width = '0'; setTimeout(() => { bar.style.width = w; }, 300); }
    });
</script>
