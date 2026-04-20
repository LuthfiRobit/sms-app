<script>
    (function () {
        'use strict';

        // Constants
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const URL_DAPODIK = "{{route('ppdb.profil.dapodik') }}";
        const URL_AKUN = "{{ route('ppdb.profil.akun') }}";
        const URL_PASSWORD = "{{ route('ppdb.profil.password') }}";
        const LS_KEY = 'ppdb_last_tab';

        // Toast
        const toastEl = document.getElementById('ppdb-toast');
        const toastMsg = document.getElementById('ppdb-toast-msg');
        const bsToast = new bootstrap.Toast(toastEl);

        function showToast(msg, ok = true) {
            toastEl.className = 'toast align-items-center border-0 text-white ' +
                (ok ? 'bg-success' : 'bg-danger');
            toastMsg.textContent = msg;
            bsToast.show();
        }

        // Tab Navigation
        const navBtns = document.querySelectorAll('.profil-nav-item');
        const panes = document.querySelectorAll('.tab-pane-profil');

        function switchTab(id) {
            navBtns.forEach(b => {
                const isActive = b.dataset.tab === id;
                b.classList.toggle('active', isActive);
                b.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            panes.forEach(p => p.classList.toggle('d-none', p.id !== 'pane-' + id));
            localStorage.setItem(LS_KEY, id);
        }

        navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

        // Restore last tab
        const saved = localStorage.getItem(LS_KEY);
        if (saved && document.getElementById('pane-' + saved)) switchTab(saved);

        // Progress Bar Update
        function updateProgress(klp) {
            if (!klp) return;
            const bar = document.getElementById('sidebar-progress-bar');
            const label = document.getElementById('persen-label');
            if (bar) {
                bar.style.width = klp.persen + '%';
                bar.setAttribute('aria-valuenow', klp.persen);
            }
            if (label) label.textContent = klp.persen + '%';
        }

        // Tab Dot Update
        const dotMap = {
            pribadi: 'pribadi',
            alamat: 'alamat',
            ayah: 'ortu',
            ibu: 'ortu',
            wali: 'ortu',
            periodik: 'periodik',
            kontak: 'kontak',
            dokumen: 'dokumen',
        };

        function markDotOk(section) {
            const tabId = dotMap[section] || section;
            const dot = document.getElementById('dot-' + tabId);
            if (dot) dot.className = 'tab-dot dot-ok';
        }

        // Button Loading State
        function setLoading(btn, loading) {
            btn.querySelector('.btn-text').classList.toggle('d-none', loading);
            btn.querySelector('.btn-spinner').classList.toggle('d-none', !loading);
            btn.disabled = loading;
        }

        // Generic AJAX POST
        async function ajaxForm(url, formData) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: formData,
            });
            return res.json();
        }

        // Dapodik Forms (Pribadi, Alamat, Ortu, dll)
        const dapodikForms = ['pribadi', 'alamat', 'ayah', 'ibu', 'wali', 'periodik', 'kontak', 'dokumen'];

        dapodikForms.forEach(section => {
            const form = document.getElementById('form-' + section);
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('.btn-save');
                setLoading(btn, true);

                const fd = new FormData(form);
                fd.set('_method', 'PUT');

                try {
                    const json = await ajaxForm(URL_DAPODIK, fd);
                    if (json.success) {
                        showToast(json.message);
                        markDotOk(section);
                        updateProgress(json.kelengkapan);
                        if (json.foto_url) {
                            document.getElementById('foto-preview').src = json.foto_url;
                            const tabFoto = document.getElementById('foto-preview-tab');
                            if (tabFoto) tabFoto.src = json.foto_url;
                        }
                    } else {
                        showToast(json.message || 'Gagal menyimpan.', false);
                    }
                } catch {
                    showToast('Terjadi kesalahan jaringan.', false);
                } finally {
                    setLoading(btn, false);
                }
            });
        });

        // Akun Form
        const formAkun = document.getElementById('form-akun');
        if (formAkun) {
            formAkun.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('btn-save-akun');
                setLoading(btn, true);
                const fd = new FormData(e.target);
                fd.set('_method', 'PUT');
                try {
                    const json = await ajaxForm(URL_AKUN, fd);
                    showToast(json.message, json.success);
                    if (json.success) markDotOk('akun');
                } catch {
                    showToast('Kesalahan jaringan.', false);
                } finally {
                    setLoading(btn, false);
                }
            });
        }

        // Password Form
        const formPassword = document.getElementById('form-password');
        if (formPassword) {
            formPassword.addEventListener('submit', async (e) => {
                e.preventDefault();
                const pw = document.getElementById('inp-pw-baru').value;
                const conf = document.getElementById('inp-pw-conf').value;
                if (pw !== conf) {
                    showToast('Konfirmasi password tidak cocok.', false);
                    return;
                }

                const btn = document.getElementById('btn-save-pw');
                setLoading(btn, true);
                const fd = new FormData(e.target);
                fd.set('_method', 'PUT');
                try {
                    const json = await ajaxForm(URL_PASSWORD, fd);
                    showToast(json.message, json.success);
                    if (json.success) e.target.reset();
                } catch {
                    showToast('Kesalahan jaringan.', false);
                } finally {
                    setLoading(btn, false);
                }
            });
        }

        // Foto Preview
        function bindFotoPreview(inputId, previewId) {
            const inp = document.getElementById(inputId);
            if (!inp) return;
            inp.addEventListener('change', function () {
                if (!this.files[0]) return;
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById('foto-preview').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            });
        }
        bindFotoPreview('input-foto', 'foto-preview');
        bindFotoPreview('input-foto-tab', 'foto-preview-tab');

        // Sync foto input
        const inputFoto = document.getElementById('input-foto');
        if (inputFoto) {
            inputFoto.addEventListener('change', function () {
                const tabInput = document.getElementById('input-foto-tab');
                if (tabInput && this.files[0]) {
                    const dt = new DataTransfer();
                    dt.items.add(this.files[0]);
                    tabInput.files = dt.files;
                }
            });
        }

        // GPS
        const btnGps = document.getElementById('btn-gps');
        const gpsStatus = document.getElementById('gps-status');
        if (btnGps) {
            btnGps.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    gpsStatus.textContent = 'Browser tidak mendukung GPS.';
                    return;
                }
                gpsStatus.textContent = 'Mendapatkan lokasi…';
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        document.getElementById('inp-lintang').value = pos.coords.latitude.toFixed(7);
                        document.getElementById('inp-bujur').value = pos.coords.longitude.toFixed(7);
                        gpsStatus.textContent = `✓ ${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
                    },
                    () => { gpsStatus.textContent = 'Gagal mendapatkan lokasi. Izinkan akses GPS.'; }
                );
            });
        }

        // Phone Input - only numbers
        document.querySelectorAll('.input-phone').forEach(inp => {
            inp.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '');
            });
        });

        // Toggle Password Visibility
        document.querySelectorAll('.toggle-pw').forEach(btn => {
            btn.addEventListener('click', function () {
                const inp = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                const isHidden = inp.type === 'password';
                inp.type = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
                this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
            });
        });

        // Stepper for Jumlah Saudara
        window.stepNumber = function (id, delta) {
            const inp = document.getElementById(id);
            if (!inp) return;
            const val = parseInt(inp.value || 0) + delta;
            inp.value = Math.max(parseInt(inp.min || 0), Math.min(parseInt(inp.max || 999), val));
        };

    })();
</script>