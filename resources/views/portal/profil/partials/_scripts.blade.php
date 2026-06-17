<script>
(function () {
    'use strict';

    const CSRF        = document.querySelector('meta[name="csrf-token"]').content;
    const URL_DAPODIK = "{{ route('ppdb.profil.dapodik') }}";
    const URL_AKUN    = "{{ route('ppdb.profil.akun') }}";
    const URL_PASSWORD= "{{ route('ppdb.profil.password') }}";
    const LS_KEY      = 'ppdb_last_tab';

    // ── Toast (Tailwind) ──────────────────────────────────────────────────
    const toastEl  = document.getElementById('ppdb-toast');
    const toastMsg = document.getElementById('ppdb-toast-msg');
    const toastIcon= document.getElementById('ppdb-toast-icon');
    let   toastTimer;

    function showToast(msg, ok = true) {
        clearTimeout(toastTimer);
        toastEl.className = [
            'fixed top-5 right-5 z-[9999] min-w-[280px] max-w-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-on-primary text-body-sm font-semibold transition-all duration-300',
            ok ? 'bg-secondary' : 'bg-error',
        ].join(' ');
        toastIcon.textContent  = ok ? 'check_circle' : 'error';
        toastMsg.textContent   = msg;
        toastEl.classList.remove('hidden');
        toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    // ── Tab Navigation ────────────────────────────────────────────────────
    const navBtns = document.querySelectorAll('.profil-nav-item');
    const panes   = document.querySelectorAll('.tab-pane-profil');

    function switchTab(id) {
        navBtns.forEach(b => {
            const active = b.dataset.tab === id;
            b.classList.toggle('active', active);
            b.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panes.forEach(p => p.classList.toggle('hidden', p.id !== 'pane-' + id));
        localStorage.setItem(LS_KEY, id);
    }

    navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

    const saved = localStorage.getItem(LS_KEY);
    if (saved && document.getElementById('pane-' + saved)) {
        switchTab(saved);
    } else {
        switchTab('akun');
    }

    // ── Progress Update ───────────────────────────────────────────────────
    function updateProgress(klp) {
        if (!klp) return;
        const bar   = document.getElementById('sidebar-progress-bar');
        const label = document.getElementById('persen-label');
        if (bar)   { bar.style.width = klp.persen + '%'; bar.setAttribute('aria-valuenow', klp.persen); }
        if (label) { label.textContent = klp.persen + '%'; }
    }

    // ── Tab Dot Update ────────────────────────────────────────────────────
    const dotMap = { pribadi:'pribadi', alamat:'alamat', ayah:'ortu', ibu:'ortu', wali:'ortu', periodik:'periodik', kontak:'kontak', dokumen:'dokumen' };

    function markDotOk(section) {
        const tabId = dotMap[section] || section;
        const dot   = document.getElementById('dot-' + tabId);
        if (dot) { dot.classList.remove('bg-error/40', 'dot-miss'); dot.classList.add('bg-secondary', 'dot-ok'); }
    }

    // ── Button Loading ────────────────────────────────────────────────────
    function setLoading(btn, loading) {
        const text    = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.btn-spinner');
        if (text)    { text.classList.toggle('hidden', loading); }
        if (spinner) { spinner.classList.toggle('hidden', !loading); spinner.classList.toggle('flex', loading); }
        btn.disabled = loading;
    }

    // ── AJAX helper ───────────────────────────────────────────────────────
    async function ajaxForm(url, formData) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        return res.json();
    }

    // ── Dapodik Forms ─────────────────────────────────────────────────────
    ['pribadi','alamat','ayah','ibu','wali','periodik','kontak','dokumen'].forEach(section => {
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
                        const prev1 = document.getElementById('foto-preview');
                        const prev2 = document.getElementById('foto-preview-tab');
                        if (prev1) prev1.src = json.foto_url;
                        if (prev2) prev2.src = json.foto_url;
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

    // ── Akun Form ─────────────────────────────────────────────────────────
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

    // ── Password Form ─────────────────────────────────────────────────────
    const formPassword = document.getElementById('form-password');
    if (formPassword) {
        formPassword.addEventListener('submit', async (e) => {
            e.preventDefault();
            const pw   = document.getElementById('inp-pw-baru').value;
            const conf = document.getElementById('inp-pw-conf').value;
            if (pw !== conf) { showToast('Konfirmasi password tidak cocok.', false); return; }

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

    // ── Foto Preview ──────────────────────────────────────────────────────
    function bindFotoPreview(inputId, previewId) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        inp.addEventListener('change', function () {
            if (!this.files[0]) return;
            const reader = new FileReader();
            reader.onload = ev => {
                const prev = document.getElementById(previewId);
                const main = document.getElementById('foto-preview');
                if (prev) prev.src = ev.target.result;
                if (main) main.src = ev.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        });
    }
    bindFotoPreview('input-foto',     'foto-preview');
    bindFotoPreview('input-foto-tab', 'foto-preview-tab');

    // Sync foto input (header → tab)
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

    // ── GPS ───────────────────────────────────────────────────────────────
    const btnGps   = document.getElementById('btn-gps');
    const gpsStatus= document.getElementById('gps-status');
    if (btnGps) {
        btnGps.addEventListener('click', () => {
            if (!navigator.geolocation) { gpsStatus.textContent = 'Browser tidak mendukung GPS.'; return; }
            gpsStatus.textContent = 'Mendapatkan lokasi…';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    document.getElementById('inp-lintang').value = pos.coords.latitude.toFixed(7);
                    document.getElementById('inp-bujur').value   = pos.coords.longitude.toFixed(7);
                    gpsStatus.textContent = `✓ ${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
                },
                () => { gpsStatus.textContent = 'Gagal mendapatkan lokasi. Izinkan akses GPS.'; }
            );
        });
    }

    // ── Phone Input ───────────────────────────────────────────────────────
    document.querySelectorAll('.input-phone').forEach(inp => {
        inp.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
    });

    // ── Toggle Password ───────────────────────────────────────────────────
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', function () {
            const inp    = document.getElementById(this.dataset.target);
            const icon   = this.querySelector('.material-symbols-outlined');
            const hidden = inp.type === 'password';
            inp.type     = hidden ? 'text' : 'password';
            if (icon) icon.textContent = hidden ? 'visibility_off' : 'visibility';
        });
    });

    // ── Jenis Kelamin Radio (visual update) ───────────────────────────────
    document.querySelectorAll('input[name="jenis_kelamin"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const labels = document.querySelectorAll('label:has(input[name="jenis_kelamin"])');
            labels.forEach(lbl => {
                const isActive = lbl.querySelector('input').checked;
                lbl.classList.toggle('border-primary',      isActive);
                lbl.classList.toggle('bg-surface-container-low', isActive);
                lbl.classList.toggle('border-outline-variant', !isActive);
                lbl.classList.toggle('bg-surface-container-lowest', !isActive);
                const dot = lbl.querySelector('.w-5 .w-2\\.5');
                // Update inner dot visibility
                const outer = lbl.querySelector('.w-5.h-5');
                if (outer) {
                    outer.className = outer.className.replace(/border-(primary|outline)/g, isActive ? 'border-primary' : 'border-outline');
                    const inner = outer.querySelector('div');
                    if (inner)  inner.style.display = isActive ? '' : 'none';
                    else if (isActive) {
                        const d = document.createElement('div');
                        d.className = 'w-2.5 h-2.5 rounded-full bg-primary';
                        outer.appendChild(d);
                    }
                }
            });
        });
    });

    // ── Ortu Accordion ───────────────────────────────────────────────────
    document.querySelectorAll('.ortu-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const body     = document.getElementById(targetId);
            const chevron  = this.querySelector('.ortu-chevron');
            const isOpen   = !body.classList.contains('hidden');

            // Close all
            document.querySelectorAll('[id^="ortu-body-"]').forEach(b => b.classList.add('hidden'));
            document.querySelectorAll('.ortu-chevron').forEach(c => c.classList.remove('rotate-180'));
            document.querySelectorAll('.ortu-toggle').forEach(b => b.setAttribute('aria-expanded', 'false'));

            if (!isOpen) {
                body.classList.remove('hidden');
                chevron.classList.add('rotate-180');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // ── Stepper for Jumlah Saudara ────────────────────────────────────────
    window.stepNumber = function (id, delta) {
        const inp = document.getElementById(id);
        if (!inp) return;
        const val = parseInt(inp.value || 0) + delta;
        inp.value = Math.max(parseInt(inp.min || 0), Math.min(parseInt(inp.max || 999), val));
    };

})();
</script>
