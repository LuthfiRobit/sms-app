<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
(function () {
    'use strict';

    // CSRF Token
    const _TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Endpoints
    const urlGetToken    = '{{ route('ppdb.pembayaran.token', $pendaftaran->id ?? 0) }}';
    const urlKonfirmasi  = '{{ route('ppdb.pembayaran.konfirmasi-manual', $pendaftaran->id ?? 0) }}';

    // ══════════════════════════════════════════════════════
    // 1. MIDTRANS FLOW
    // ══════════════════════════════════════════════════════
    const btnMidtrans      = document.getElementById('btn-bayar-midtrans');
    const midtransAlert    = document.getElementById('midtrans-alert');
    const midtransAlertMsg = document.getElementById('midtrans-alert-msg');

    if (btnMidtrans) {
        btnMidtrans.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            try {
                const res  = await fetch(urlGetToken, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _TOKEN, 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.success && data.data && data.data.snap_token) {
                    snap.pay(data.data.snap_token, {
                        onSuccess: function (result) {
                            showMidtransAlert('Pembayaran berhasil! Halaman akan dimuat ulang...', 'success');
                            setTimeout(() => window.location.reload(), 2000);
                        },
                        onPending: function (result) {
                            showMidtransAlert('Pembayaran pending. Segera selesaikan sesuai instruksi.', 'warning');
                            setTimeout(() => window.location.reload(), 3000);
                        },
                        onError: function (result) {
                            showMidtransAlert(result.status_message ?? 'Terjadi kesalahan pembayaran.', 'danger');
                        },
                        onClose: function () {
                            showMidtransAlert('Popup ditutup. Klik "Bayar Sekarang" untuk mencoba lagi.', 'warning');
                        },
                    });
                } else {
                    showMidtransAlert(data.message ?? 'Gagal mendapatkan token pembayaran.', 'danger');
                }
            } catch (err) {
                showMidtransAlert('Gagal menghubungi server. Silakan coba lagi.', 'danger');
            } finally {
                this.innerHTML = '<i class="bi bi-lightning-charge-fill me-2"></i>Bayar Sekarang — {{ $nominalFmt }}';
                this.disabled = false;
            }
        });
    }

    function showMidtransAlert(msg, type) {
        if (!midtransAlert) return;
        midtransAlert.className = `alert alert-${type} mt-4 rounded-4 border-0 shadow-sm`;
        midtransAlertMsg.textContent = msg;
        midtransAlert.classList.remove('d-none');
    }

    // ══════════════════════════════════════════════════════
    // 2. UPLOAD FILE UI
    // ══════════════════════════════════════════════════════
    const dropZone    = document.getElementById('file-drop-zone');
    const fileInput   = document.getElementById('input-bukti');
    const fileContent = document.getElementById('file-drop-content');
    const filePreview = document.getElementById('file-preview');
    const previewName = document.getElementById('file-preview-name');
    const previewSize = document.getElementById('file-preview-size');
    const btnRemove   = document.getElementById('btn-remove-file');

    if (dropZone && fileInput) {
        fileInput.addEventListener('change', handleFiles);

        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            fileInput.files = e.dataTransfer.files;
            handleFiles();
        });
    }

    function handleFiles() {
        const file = fileInput.files[0];
        if (!file) return;
        fileContent.classList.add('d-none');
        filePreview.classList.remove('d-none');
        previewName.textContent = file.name;
        previewSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            filePreview.classList.add('d-none');
            fileContent.classList.remove('d-none');
        });
    }

    // ══════════════════════════════════════════════════════
    // 3. KONFIRMASI MANUAL (AJAX UPLOAD)
    // ══════════════════════════════════════════════════════
    const formManual   = document.getElementById('form-konfirmasi-manual');
    const btnUpload    = document.getElementById('btn-upload-bukti');
    const progressWrap = document.getElementById('upload-progress-wrapper');
    const progressBar  = document.getElementById('upload-progress-bar');
    const progressPct  = document.getElementById('upload-pct');
    const resultDiv    = document.getElementById('manual-result');

    if (formManual) {
        formManual.addEventListener('submit', function (e) {
            e.preventDefault();
            const file = fileInput.files[0];
            if (!file) { showResult('warning', '<i class="bi bi-exclamation-circle me-2"></i>Pilih file bukti transfer terlebih dahulu.'); return; }

            btnUpload.disabled = true;
            btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            progressWrap.classList.remove('d-none');
            resultDiv.classList.add('d-none');

            const formData = new FormData(formManual);
            const xhr      = new XMLHttpRequest();
            xhr.open('POST', urlKonfirmasi);
            xhr.setRequestHeader('X-CSRF-TOKEN', _TOKEN);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    const pct = Math.round((event.loaded / event.total) * 100);
                    progressBar.style.width = pct + '%';
                    progressPct.textContent = pct + '% Mengupload...';
                }
            };

            xhr.onload = function () {
                progressWrap.classList.add('d-none');
                btnUpload.disabled = false;
                btnUpload.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Upload Bukti Transfer';

                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showResult('success', `<i class="bi bi-check-circle me-2"></i>${res.message || 'Bukti transfer berhasil dikirim!'}`);
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showResult('danger', `<i class="bi bi-x-circle me-2"></i>${res.message || 'Gagal mengirim bukti pendaftaran.'}`);
                    }
                } catch (err) {
                    showResult('danger', '<i class="bi bi-exclamation-circle me-2"></i>Terjadi kesalahan server.');
                }
            };

            xhr.onerror = function () {
                progressWrap.classList.add('d-none');
                btnUpload.disabled = false;
                btnUpload.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Upload Bukti Transfer';
                showResult('danger', '<i class="bi bi-wifi-off me-2"></i>Gagal terhubung ke server.');
            };

            xhr.send(formData);
        });
    }

    function showResult(type, html) {
        if (!resultDiv) return;
        resultDiv.className = `alert alert-${type} rounded-4 border-0 shadow-sm`;
        resultDiv.innerHTML = html;
        resultDiv.classList.remove('d-none');
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // 4. CLIPBOARD — Robust copy with fallback
    window.copyToClipboard = function (text, btn) {
        const copyAction = (str) => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(str);
            } else {
                // Fallback for non-secure contexts
                const textArea = document.createElement("textarea");
                textArea.value = str;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                return new Promise((res, rej) => {
                    document.execCommand('copy') ? res() : rej();
                    textArea.remove();
                });
            }
        };

        copyAction(text).then(() => {
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success"></i>';
            btn.classList.add('border-success');
            setTimeout(() => { 
                btn.innerHTML = origHTML; 
                btn.classList.remove('border-success');
            }, 2000);
        }).catch(err => {
            console.error('Gagal menyalin:', err);
        });
    };

    // Navigation Scroll Spy simple
    document.querySelectorAll('.profil-nav-item').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.querySelectorAll('.profil-nav-item').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

})();
</script>
