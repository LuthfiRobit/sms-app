<script>
/**
 * ── CONSTANTS ──────────────────────────────────────────────────────────
 */
const STORE_URL      = "{{ route('ppdb.daftar-ulang.store', optional($pendaftaran)->id ?? 0) }}";
const CSRF_TOKEN     = document.querySelector('meta[name="csrf-token"]')?.content;
@if($deadline)
const DEADLINE_TS    = {{ \Carbon\Carbon::parse($deadline)->timestamp }};
@else
const DEADLINE_TS    = null;
@endif

/**
 * ── CHECKBOX TOGGLE ────────────────────────────────────────────────────
 */
function toggleConfirmBtn() {
    const chk = document.getElementById('chk-hadir');
    const btn = document.getElementById('btnDaftarUlang');
    if (chk && btn) {
        btn.disabled = !chk.checked;
    }
}

/**
 * ── SHOW MODAL ─────────────────────────────────────────────────────────
 */
function showModalKonfirmasi() {
    const chk = document.getElementById('chk-hadir');
    if (!chk || !chk.checked) return;
    const modalElement = document.getElementById('modalKonfirmasi');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

/**
 * ── SUBMIT DAFTAR ULANG (AJAX) ─────────────────────────────────────────
 */
function submitDaftarUlang() {
    const modalSpinner = document.getElementById('modalSpinner');
    const modalBtnText = document.getElementById('modalBtnText');
    const modalSubmit  = document.getElementById('btnModalSubmit');

    // Loading state
    if (modalSpinner) modalSpinner.classList.remove('d-none');
    if (modalBtnText) modalBtnText.textContent = 'Memproses...';
    if (modalSubmit)  modalSubmit.disabled = true;

    fetch(STORE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  CSRF_TOKEN,
        },
        body: JSON.stringify({
            pernyataan_hadir: true,
        }),
    })
    .then(res => res.json())
    .then(data => {
        // Tutup modal
        const modalElement = document.getElementById('modalKonfirmasi');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();

        if (data.success) {
            showToast('success', data.message || 'Daftar ulang berhasil dikonfirmasi!');
            // Reload halaman untuk tampilkan state "Menunggu konfirmasi admin"
            setTimeout(() => { location.reload(); }, 1800);
        } else {
            showToast('error', data.message || 'Gagal melakukan daftar ulang.');
            // Reset modal button
            if (modalSpinner) modalSpinner.classList.add('d-none');
            if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
            if (modalSubmit)  modalSubmit.disabled = false;
        }
    })
    .catch(err => {
        console.error('[DaftarUlang] Error:', err);
        const modalElement = document.getElementById('modalKonfirmasi');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
        showToast('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        if (modalSpinner) modalSpinner.classList.add('d-none');
        if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
        if (modalSubmit)  modalSubmit.disabled = false;
    });
}

/**
 * ── TOAST ───────────────────────────────────────────────────────────────
 */
function showToast(type, message) {
    const wrap  = document.getElementById('toastWrap');
    if (!wrap) return;
    const toast = document.createElement('div');
    toast.className = `du-toast du-toast-${type}`;
    toast.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <span>${type === 'success' ? '✅' : '❌'}</span>
            <span>${message}</span>
        </div>`;
    wrap.appendChild(toast);
    requestAnimationFrame(() => { toast.classList.add('show'); });
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/**
 * ── COUNTDOWN TIMER ─────────────────────────────────────────────────────
 */
(function initCountdown() {
    if (!DEADLINE_TS) return;
    const elHari  = document.getElementById('cd-hari');
    const elJam   = document.getElementById('cd-jam');
    const elMenit = document.getElementById('cd-menit');
    const elDetik = document.getElementById('cd-detik');
    if (!elHari || !elJam || !elMenit || !elDetik) return;

    function padTwo(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const now   = Math.floor(Date.now() / 1000);
        const diff  = DEADLINE_TS - now;

        if (diff <= 0) {
            elHari.textContent  = '0';
            elJam.textContent   = '00';
            elMenit.textContent = '00';
            elDetik.textContent = '00';
            // Reload agar tampilkan state terlambat
            location.reload();
            return;
        }

        const hari  = Math.floor(diff / 86400);
        const jam   = Math.floor((diff % 86400) / 3600);
        const menit = Math.floor((diff % 3600) / 60);
        const detik = diff % 60;

        elHari.textContent  = hari;
        elJam.textContent   = padTwo(jam);
        elMenit.textContent = padTwo(menit);
        elDetik.textContent = padTwo(detik);
    }

    tick();
    setInterval(tick, 1000);
})();

/**
 * ── CONFETTI ─────────────────────────────────────────────────────────────
 */
(function initConfetti() {
    const container = document.getElementById('confettiContainer');
    if (!container) return;

    const colors = ['#ff595e','#ffca3a','#8ac926','#1982c4','#6a4c93','#ff924c','#c77dff','#4cc9f0'];
    const count  = 18;

    for (let i = 0; i < count; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.cssText = `
            left: ${Math.random() * 100}%;
            top: -10px;
            background-color: ${colors[Math.floor(Math.random() * colors.length)]};
            animation-delay: ${(i * 0.18).toFixed(2)}s;
            animation-duration: ${(2.5 + Math.random() * 1.5).toFixed(2)}s;
            width: ${6 + Math.floor(Math.random() * 6)}px;
            height: ${6 + Math.floor(Math.random() * 6)}px;
            border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
        `;
        container.appendChild(piece);
    }
})();

/**
 * ── RESET modal state on hide ────────────────────────────────────────────
 */
document.getElementById('modalKonfirmasi')?.addEventListener('hidden.bs.modal', function () {
    const modalSpinner = document.getElementById('modalSpinner');
    const modalBtnText = document.getElementById('modalBtnText');
    const modalSubmit  = document.getElementById('btnModalSubmit');
    if (modalSpinner) modalSpinner.classList.add('d-none');
    if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
    if (modalSubmit)  modalSubmit.disabled = false;
});
</script>
