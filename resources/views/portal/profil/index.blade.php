{{-- ════════════════════════════════════════════════════
     index.blade.php — Halaman Profil Peserta
     Layout: layouts.portal (portal.blade.php)
     ════════════════════════════════════════════════════ --}}
@extends('layouts.portal')

@section('title', 'Profil Saya')

{{-- ════════════════════════════════════════════════════
     STYLES — di-push ke @stack('styles') di <head>
     [DIUBAH] Semua CSS halaman ini dipindah ke @push('styles')
     agar tidak ada style inline di body. Ini memperbaiki
     struktur CSS dan mencegah FOUC (flash of unstyled content).
     ════════════════════════════════════════════════════ --}}
@push('styles')
<style>

/* ── Sidebar ────────────────────────────────────────── */
.profil-sidebar {
    background: #fff;
    border-radius: var(--r-xl);
    border: 1px solid var(--c-border);
    box-shadow: var(--shadow-md);
    padding: 24px 16px;
}

/* ── Foto profil dengan overlay kamera ─────────────── */
.foto-wrapper {
    width: 112px;
    height: 112px;
    position: relative;
    cursor: pointer;
    margin: 0 auto;
}
.foto-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    /* [DIUBAH] Border diganti dengan box-ring agar lebih modern */
    box-shadow: 0 0 0 3px #fff, 0 0 0 5px var(--c-primary-light);
    transition: box-shadow var(--t-normal);
}
.foto-wrapper:hover img {
    box-shadow: 0 0 0 3px #fff, 0 0 0 5px var(--c-primary);
}
.foto-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.25rem;
    opacity: 0;
    transition: opacity var(--t-normal);
    cursor: pointer;
}
.foto-wrapper:hover .foto-overlay,
.foto-wrapper:focus-within .foto-overlay { opacity: 1; }

/* ── Progress bar profil ───────────────────────────── */
.profil-progress-track {
    height: 8px;
    border-radius: 99px;
    background: var(--c-border);
    overflow: hidden;
}
.profil-progress-fill {
    height: 100%;
    border-radius: 99px;
    transition: width .7s cubic-bezier(.4,0,.2,1);
}

/* ── Navigasi vertikal tab ─────────────────────────── */
.profil-nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.profil-nav-item {
    display: flex;
    align-items: center;
    padding: 9px 11px;
    border: none;
    background: transparent;
    border-radius: var(--r-md);
    font-size: .84rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: background var(--t-fast), color var(--t-fast), transform var(--t-fast);
    width: 100%;
    text-align: left;
    gap: 8px;
}
.profil-nav-item:hover {
    background: var(--c-primary-50);
    color: var(--c-primary-dark);
    transform: translateX(2px); /* [BARU] Efek geser halus saat hover */
}
.profil-nav-item.active {
    background: var(--c-primary-light);
    color: var(--c-primary-dark);
    font-weight: 700;
}
.profil-nav-item i {
    width: 16px;
    text-align: center;
    flex-shrink: 0;
    font-size: .95rem;
}
.profil-nav-item .nav-label { flex: 1; }

/* Titik status tab (merah = kosong, hijau = terisi) */
.tab-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: background var(--t-normal);
}
.dot-ok   { background: var(--c-primary); }
.dot-miss { background: #fca5a5; }

/* ── Card konten ───────────────────────────────────── */
/* [DIUBAH] Pisahkan .profil-card dari .portal-card global
   agar bisa punya styling spesifik (header dengan bg subtle) */
.profil-card {
    background: #fff;
    border-radius: var(--r-lg);
    border: 1px solid var(--c-border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    /* [BARU] Animasi masuk saat tab berpindah */
    animation: fadeSlideIn .2s ease;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.profil-card + .profil-card { margin-top: 16px; }

.profil-card-header {
    padding: 14px 22px;
    background: var(--c-primary-50);
    border-bottom: 1px solid var(--c-primary-light);
    font-weight: 700;
    font-size: .855rem;
    color: var(--c-primary-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}
.profil-card-header i { font-size: 1rem; }

.profil-card-body { padding: 22px 24px; }

/* ── Form controls — override Bootstrap ────────────── */
/* [DIUBAH] Semua input mendapat border lebih tebal dan
   focus ring berwarna brand agar konsisten dengan sistem */
.form-control,
.form-select {
    border-radius: var(--r-sm);
    font-size: .855rem;
    border: 1.5px solid #d1d5db;
    color: var(--c-text);
    transition: border-color var(--t-fast), box-shadow var(--t-fast);
    padding: .48rem .75rem;
}
.form-control:focus,
.form-select:focus {
    border-color: var(--c-primary);
    box-shadow: 0 0 0 3px rgba(22,163,74,.14);
    outline: none;
}
.form-control.bg-light { color: var(--c-text-muted); cursor: not-allowed; }
.form-label {
    font-size: .8rem;
    font-weight: 600;
    margin-bottom: .3rem;
    color: #374151;
}
.form-text { font-size: .75rem; color: var(--c-text-muted); }

/* Input group — tombol kiri/kanan input */
.input-group .btn { border-radius: var(--r-sm); }
.input-group .form-control:not(:first-child) { border-left: none; }
.input-group .form-control:not(:last-child)  { border-right: none; }
.input-group-text {
    background: var(--c-primary-50);
    border: 1.5px solid #d1d5db;
    color: var(--c-primary);
    font-size: .875rem;
}

/* ── Info box (notifikasi di dalam form) ────────────── */
.info-box {
    background: var(--c-primary-50);
    border: 1px solid #bbf7d0;
    border-radius: var(--r-sm);
    padding: 10px 14px;
    font-size: .8rem;
    color: #166534;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.info-box i { margin-top: 1px; flex-shrink: 0; }

/* ── Tombol simpan ──────────────────────────────────── */
/* [DIUBAH] Ukuran minimum + font-weight untuk konsistensi */
.btn-save {
    min-width: 152px;
    font-weight: 600;
    border-radius: var(--r-sm);
    font-size: .855rem;
    padding: .45rem 1.1rem;
    transition: transform var(--t-fast), box-shadow var(--t-fast);
}
.btn-save:not(:disabled):hover { transform: translateY(-1px); box-shadow: var(--shadow-primary); }
.btn-save:active { transform: translateY(0); }

/* ── Badge status ───────────────────────────────────── */
.badge { font-size: .7rem; font-weight: 600; padding: .3em .6em; border-radius: 99px; }

/* ── Accordion orang tua ─────────────────────────────── */
.accordion-button:not(.collapsed) {
    background: var(--c-primary-50);
    color: var(--c-primary-dark);
    box-shadow: none;
}
.accordion-button:focus { box-shadow: 0 0 0 3px rgba(22,163,74,.14); }
.accordion-item { border-color: var(--c-border); }

/* ── Tombol Mulai Pendaftaran ───────────────────────── */
.btn-daftar {
    background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-accent) 100%);
    color: #fff;
    border: none;
    border-radius: var(--r-md);
    font-weight: 700;
    font-size: .855rem;
    padding: .6rem 1rem;
    width: 100%;
    transition: opacity var(--t-fast), transform var(--t-fast), box-shadow var(--t-fast);
    box-shadow: var(--shadow-primary);
}
.btn-daftar:hover {
    color: #fff;
    opacity: .92;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(22,163,74,.3);
}

/* ════════════════════════════════════════════════════
   RESPONSIF — Mobile-first adjustments
   [DIUBAH] Pendekatan mobile-first: sidebar jadi
   horizontal scroll-tab di layar kecil, bukan
   tersembunyi. Lebih usable daripada collapsible.
   ════════════════════════════════════════════════════ */
@media (max-width: 767px) {
    /* Sidebar menjadi bar horizontal di atas konten */
    .profil-sidebar {
        border-radius: var(--r-lg);
        padding: 16px;
    }
    /* Foto + info user disembunyikan di mobile (sudah ada di navbar) */
    .sidebar-user-info { display: none; }

    /* Nav menjadi baris yang bisa di-scroll horizontal */
    .profil-nav {
        flex-direction: row;
        overflow-x: auto;
        gap: 4px;
        padding-bottom: 4px;
        border-bottom: 1px dashed #d1d5db;
        /* [BARU] Hapus scrollbar visual agar lebih bersih */
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .profil-nav::-webkit-scrollbar { display: none; }

    .profil-nav-item {
        flex: 0 0 auto;
        flex-direction: column;
        justify-content: center;
        padding: 8px 10px;
        min-width: 60px;
        max-width: 80px;
        font-size: .7rem;
        gap: 4px;
        transform: none !important; /* matikan efek geser di mobile */
    }
    .profil-nav-item i { width: auto; font-size: 1.1rem; }
    .nav-label { font-size: .68rem; white-space: nowrap; }
    .tab-dot { display: none; } /* Sembunyikan dot, terlalu kecil di mobile */

    .profil-card-body { padding: 16px; }
}

@media (max-width: 575px) {
    .btn-save { width: 100%; }
}
</style>
@endpush

@section('content')

{{-- ════════════════════════════════════════════════════
     DATA PREP — tidak ada perubahan logika, hanya
     dirapikan formatnya agar lebih mudah dibaca
     ════════════════════════════════════════════════════ --}}
@php
    $peserta  = $profil['peserta'];
    $alamat   = $profil['alamat'];
    $ortu     = $profil['orang_tua'];
    $periodik = $profil['periodik'];
    $kontak   = $profil['kontak'];
    $dokumen  = $profil['dokumen_pribadi'];
    $klp      = $profil['kelengkapan'];
    $persen   = $klp['persen'];
    $user     = auth()->user();

    // Status kelengkapan per tab — menentukan warna dot sidebar
    $tabStatus = [
        'akun'     => !empty($user->name) && !empty($user->email),
        'pribadi'  => !empty($peserta->nama_lengkap) && !empty($peserta->jenis_kelamin) && !empty($peserta->tanggal_lahir),
        'alamat'   => !empty($alamat?->alamat) && !empty($alamat?->kabupaten_kota),
        'ortu'     => !empty($ortu['ayah']?->nama) || !empty($ortu['ibu']?->nama),
        'periodik' => !empty($periodik?->tinggi_badan) && !empty($periodik?->berat_badan),
        'kontak'   => !empty($kontak?->no_hp),
        'dokumen'  => !empty($dokumen?->no_kip) || !empty($dokumen?->no_pkh),
    ];

    // [DIUBAH] Warna progress dihitung via fungsi array agar lebih ringkas
    $progressColor = match(true) {
        $persen >= 80 => 'success',
        $persen >= 50 => 'warning',
        default       => 'danger',
    };

    // Definisi tab — data-driven agar mudah ditambah/diubah
    $tabs = [
        ['id' => 'akun',     'icon' => 'bi-person-gear',      'label' => 'Akun',       'key' => 'akun'],
        ['id' => 'pribadi',  'icon' => 'bi-person-vcard',     'label' => 'Pribadi',    'key' => 'pribadi'],
        ['id' => 'alamat',   'icon' => 'bi-geo-alt',          'label' => 'Alamat',     'key' => 'alamat'],
        ['id' => 'ortu',     'icon' => 'bi-people',           'label' => 'Orang Tua',  'key' => 'ortu'],
        ['id' => 'periodik', 'icon' => 'bi-heart-pulse',      'label' => 'Periodik',   'key' => 'periodik'],
        ['id' => 'kontak',   'icon' => 'bi-telephone',        'label' => 'Kontak',     'key' => 'kontak'],
        ['id' => 'dokumen',  'icon' => 'bi-file-earmark-text','label' => 'Dokumen',    'key' => 'dokumen'],
    ];
@endphp

{{-- ════════════════════════════════════════════════════
     TOAST NOTIFICATION
     [DIUBAH] Diletakkan di luar row agar z-index
     tidak terpengaruh oleh stacking context card/sidebar
     ════════════════════════════════════════════════════ --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="ppdb-toast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="ppdb-toast-msg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     LAYOUT UTAMA — Sidebar kiri + Konten kanan
     ════════════════════════════════════════════════════ --}}
<div class="row g-4">

    {{-- ══════════════════════════════════════
         SIDEBAR — Info user + Navigasi tab
         ══════════════════════════════════════ --}}
    <div class="col-lg-3 col-md-4">
        {{-- [DIUBAH] sticky-top menggunakan CSS var agar top selaras dengan navbar --}}
        <div class="profil-sidebar" style="position:sticky;top:calc(var(--navbar-h) + 16px)">

            {{-- Info pengguna (disembunyikan di mobile via CSS) --}}
            <div class="sidebar-user-info text-center mb-4">

                {{-- Foto profil dengan overlay upload --}}
                <div class="foto-wrapper mb-3">
                    <img id="foto-preview"
                         src="{{ $peserta->foto
                             ? asset('storage/'.$peserta->foto)
                             : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=16a34a&color=fff&bold=true&rounded=true' }}"
                         alt="Foto Profil {{ $user->name }}">
                    <label for="input-foto" class="foto-overlay" title="Ganti Foto" tabindex="0">
                        <i class="bi bi-camera-fill" aria-hidden="true"></i>
                        <span class="visually-hidden">Ganti foto profil</span>
                    </label>
                    <input type="file" id="input-foto" accept="image/*" class="d-none" aria-label="Upload foto profil">
                </div>

                {{-- Nama, email, badge status --}}
                <div class="fw-bold" style="font-size:.92rem;color:var(--c-text)">{{ $user->name }}</div>
                <div style="font-size:.75rem;color:var(--c-text-muted)" class="mb-2">{{ $user->email }}</div>
                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                    <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle" aria-hidden="true"></i>
                    {{ $user->status === 'active' ? 'Aktif' : ucfirst($user->status) }}
                </span>
            </div>

            {{-- Progress kelengkapan profil --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.76rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px">Kelengkapan</span>
                    <span id="persen-label" class="fw-bold text-{{ $progressColor }}" style="font-size:.88rem">{{ $persen }}%</span>
                </div>
                <div class="profil-progress-track">
                    <div id="sidebar-progress-bar"
                         class="profil-progress-fill bg-{{ $progressColor }}"
                         style="width:{{ $persen }}%"
                         role="progressbar"
                         aria-valuenow="{{ $persen }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                {{-- Item yang belum diisi --}}
                @if(!empty($klp['item_kurang']))
                    <div class="mt-2" style="font-size:.7rem;color:var(--c-text-muted)">
                        <i class="bi bi-exclamation-circle me-1 text-warning" aria-hidden="true"></i>
                        Belum: {{ implode(', ', array_slice($klp['item_kurang'], 0, 3)) }}{{ count($klp['item_kurang']) > 3 ? '…' : '' }}
                    </div>
                @endif
            </div>

            {{-- Navigasi tab vertikal --}}
            <nav class="profil-nav mb-4" aria-label="Navigasi profil">
                @foreach($tabs as $tab)
                    <button class="profil-nav-item {{ $loop->first ? 'active' : '' }}"
                            data-tab="{{ $tab['id'] }}"
                            id="nav-{{ $tab['id'] }}"
                            type="button"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            role="tab">
                        <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>
                        <span class="nav-label">{{ $tab['label'] }}</span>
                        <span class="tab-dot {{ $tabStatus[$tab['key']] ? 'dot-ok' : 'dot-miss' }}"
                              id="dot-{{ $tab['id'] }}"
                              aria-label="{{ $tabStatus[$tab['key']] ? 'Lengkap' : 'Belum diisi' }}"></span>
                    </button>
                @endforeach
            </nav>

            {{-- CTA Mulai Pendaftaran --}}
            <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn-daftar d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                Mulai Pendaftaran
            </a>

        </div>
    </div>{{-- /col sidebar --}}

    {{-- ══════════════════════════════════════
         AREA KONTEN TAB
         ══════════════════════════════════════ --}}
    <div class="col-lg-9 col-md-8">
    <div id="profil-content" role="tabpanel">

        {{-- ─────────────────────────────────────
             TAB 1 — Akun & Keamanan
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil" id="pane-akun">

            {{-- Informasi Akun --}}
            <div class="profil-card mb-4">
                <div class="profil-card-header">
                    <i class="bi bi-person-gear" aria-hidden="true"></i>Informasi Akun
                </div>
                <div class="profil-card-body">
                    <form id="form-akun" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="akun-nama">Nama Lengkap <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="text" id="akun-nama" name="nama_lengkap" class="form-control"
                                       value="{{ $user->name }}" required minlength="3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="akun-email">Email <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="email" id="akun-email" name="email" class="form-control"
                                       value="{{ $user->email }}" required>
                            </div>
                            <div class="col-12">
                                {{-- [DIUBAH] Info box diperhalus dengan ikon yang lebih relevan --}}
                                <div class="info-box">
                                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                                    <span>Terdaftar sejak <strong>{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</strong>
                                    &nbsp;·&nbsp; Status:
                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($user->status) }}
                                    </span></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save" id="btn-save-akun">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Perubahan</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ubah Password --}}
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>Ubah Password
                </div>
                <div class="profil-card-body">
                    <form id="form-password" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-lama">Password Lama <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_lama" id="inp-pw-lama" class="form-control" autocomplete="current-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-lama" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-baru">Password Baru <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="inp-pw-baru" class="form-control" minlength="8" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-baru" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-conf">Konfirmasi <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="inp-pw-conf" class="form-control" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-conf" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning fw-semibold btn-save" id="btn-save-pw">
                                    <span class="btn-text"><i class="bi bi-key me-1" aria-hidden="true"></i>Ubah Password</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>{{-- /pane-akun --}}

        {{-- ─────────────────────────────────────
             TAB 2 — Data Pribadi
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-pribadi">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-person-vcard" aria-hidden="true"></i>Data Pribadi (Dapodik)
                </div>
                <div class="profil-card-body">
                    <form id="form-pribadi" novalidate enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="pribadi">

                        {{-- Upload foto di dalam tab --}}
                        <div class="d-flex align-items-center gap-3 mb-4 p-3"
                             style="background:var(--c-primary-50);border-radius:var(--r-md);border:1px solid var(--c-primary-light)">
                            <img id="foto-preview-tab"
                                 src="{{ $peserta->foto
                                     ? asset('storage/'.$peserta->foto)
                                     : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=16a34a&color=fff&bold=true&rounded=true' }}"
                                 class="rounded-circle object-fit-cover flex-shrink-0"
                                 style="width:72px;height:72px;box-shadow:0 0 0 3px #fff,0 0 0 5px var(--c-primary-light)"
                                 alt="Foto Profil">
                            <div>
                                <label class="form-label" for="input-foto-tab">Foto Profil</label>
                                <input type="file" name="foto" id="input-foto-tab" accept="image/*"
                                       class="form-control form-control-sm" style="max-width:260px">
                                <div class="form-text">JPG/PNG/WebP · maks. 2 MB</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="pribadi-nama">Nama Lengkap <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="text" id="pribadi-nama" name="nama_lengkap" class="form-control"
                                       value="{{ $peserta->nama_lengkap }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Jenis Kelamin</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" id="jk-l"
                                               {{ $peserta->jenis_kelamin === 'L' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="jk-l">Laki-laki</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" id="jk-p"
                                               {{ $peserta->jenis_kelamin === 'P' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="jk-p">Perempuan</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pribadi-tempat-lahir">Tempat Lahir</label>
                                <input type="text" id="pribadi-tempat-lahir" name="tempat_lahir" class="form-control"
                                       value="{{ $peserta->tempat_lahir }}" placeholder="Kota tempat lahir">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pribadi-tgl-lahir">Tanggal Lahir</label>
                                <input type="date" id="pribadi-tgl-lahir" name="tanggal_lahir" class="form-control"
                                       value="{{ $peserta->tanggal_lahir?->format('Y-m-d') }}"
                                       max="{{ now()->subDay()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pribadi-agama">Agama</label>
                                <select id="pribadi-agama" name="agama" class="form-select">
                                    <option value="">— Pilih Agama —</option>
                                    @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $ag)
                                        <option value="{{ $ag }}" {{ $peserta->agama === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pribadi-kebutuhan">Kebutuhan Khusus</label>
                                <input type="text" id="pribadi-kebutuhan" name="kebutuhan_khusus" class="form-control"
                                       value="{{ $peserta->kebutuhan_khusus }}" placeholder="Kosongkan jika tidak ada">
                            </div>

                            {{-- NIK --}}
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-2" for="pribadi-nik">
                                    NIK
                                    @if($peserta->nik)
                                        <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                              title="Sudah terkunci, hubungi Admin jika ada kesalahan">
                                            🔒 Hanya Admin
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark" data-bs-toggle="tooltip"
                                              title="Dapat diisi satu kali — pastikan benar">
                                            🔓 Sekali Isi
                                        </span>
                                    @endif
                                </label>
                                <input type="text" id="pribadi-nik"
                                       name="{{ $peserta->nik ? '' : 'nik' }}"
                                       class="form-control {{ $peserta->nik ? 'bg-light' : '' }}"
                                       value="{{ $peserta->nik }}"
                                       placeholder="{{ $peserta->nik ? '' : '16 digit NIK' }}"
                                       maxlength="16"
                                       {{ $peserta->nik ? 'readonly' : '' }}>
                            </div>

                            {{-- NISN --}}
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-2" for="pribadi-nisn">
                                    NISN
                                    @if($peserta->nisn)
                                        <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                              title="Sudah terkunci, hubungi Admin jika ada kesalahan">
                                            🔒 Hanya Admin
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark" data-bs-toggle="tooltip"
                                              title="Dapat diisi satu kali — pastikan benar">
                                            🔓 Sekali Isi
                                        </span>
                                    @endif
                                </label>
                                <input type="text" id="pribadi-nisn"
                                       name="{{ $peserta->nisn ? '' : 'nisn' }}"
                                       class="form-control {{ $peserta->nisn ? 'bg-light' : '' }}"
                                       value="{{ $peserta->nisn }}"
                                       placeholder="{{ $peserta->nisn ? '' : '10 digit NISN' }}"
                                       maxlength="10"
                                       {{ $peserta->nisn ? 'readonly' : '' }}>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Data Pribadi</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>{{-- /pane-pribadi --}}

        {{-- ─────────────────────────────────────
             TAB 3 — Alamat
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-alamat">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>Data Alamat
                </div>
                <div class="profil-card-body">
                    <form id="form-alamat" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="alamat">
                        <input type="hidden" name="lintang" id="inp-lintang" value="{{ $alamat?->lintang }}">
                        <input type="hidden" name="bujur"   id="inp-bujur"   value="{{ $alamat?->bujur }}">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="alamat-lengkap">Alamat Lengkap <span class="text-danger" aria-label="wajib">*</span></label>
                                <textarea id="alamat-lengkap" name="alamat" class="form-control" rows="2" required
                                          placeholder="Jl. nama jalan, nomor rumah">{{ $alamat?->alamat }}</textarea>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="alamat-rt">RT</label>
                                <input type="text" id="alamat-rt" name="rt" class="form-control" value="{{ $alamat?->rt }}" placeholder="001">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="alamat-rw">RW</label>
                                <input type="text" id="alamat-rw" name="rw" class="form-control" value="{{ $alamat?->rw }}" placeholder="002">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="alamat-desa">Desa/Kelurahan</label>
                                <input type="text" id="alamat-desa" name="desa_kelurahan" class="form-control" value="{{ $alamat?->desa_kelurahan }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="alamat-kec">Kecamatan</label>
                                <input type="text" id="alamat-kec" name="kecamatan" class="form-control" value="{{ $alamat?->kecamatan }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="alamat-kab">Kabupaten/Kota <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="text" id="alamat-kab" name="kabupaten_kota" class="form-control" value="{{ $alamat?->kabupaten_kota }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="alamat-prov">Provinsi <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="text" id="alamat-prov" name="provinsi" class="form-control" value="{{ $alamat?->provinsi }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="alamat-pos">Kode Pos</label>
                                <input type="text" id="alamat-pos" name="kode_pos" class="form-control" value="{{ $alamat?->kode_pos }}" maxlength="10">
                            </div>

                            {{-- GPS Helper --}}
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button type="button" id="btn-gps" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-geo me-1" aria-hidden="true"></i>Gunakan GPS
                                    </button>
                                    <span id="gps-status" class="text-muted" style="font-size:.78rem" aria-live="polite"></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Alamat</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>{{-- /pane-alamat --}}

        {{-- ─────────────────────────────────────
             TAB 4 — Orang Tua / Wali
             [DIUBAH] Accordion tetap, hanya
             diperbaiki aksesibilitas dan spacing
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-ortu">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-people" aria-hidden="true"></i>Data Orang Tua / Wali
                    <small class="ms-1 fw-normal" style="color:var(--c-primary-dark);opacity:.7">(minimal isi Ayah atau Ibu)</small>
                </div>
                <div class="profil-card-body p-0">
                    <div class="accordion accordion-flush" id="accordionOrtu">
                        @foreach([
                            ['key'=>'ayah', 'label'=>'Data Ayah',          'icon'=>'bi-person',      'data'=>$ortu['ayah']],
                            ['key'=>'ibu',  'label'=>'Data Ibu',            'icon'=>'bi-person',      'data'=>$ortu['ibu']],
                            ['key'=>'wali', 'label'=>'Data Wali (Opsional)','icon'=>'bi-person-check','data'=>$ortu['wali']],
                        ] as $idx => $ot)
                        <div class="accordion-item border-0 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }} fw-semibold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $ot['key'] }}"
                                        aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse-{{ $ot['key'] }}">
                                    <i class="bi {{ $ot['icon'] }} me-2 text-success" aria-hidden="true"></i>
                                    {{ $ot['label'] }}
                                    @if(!empty($ot['data']?->nama))
                                        <span class="badge bg-success ms-2" style="font-size:.65rem">Terisi</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapse-{{ $ot['key'] }}"
                                 class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}"
                                 data-bs-parent="#accordionOrtu">
                                <div class="accordion-body">
                                    <form id="form-{{ $ot['key'] }}" novalidate>
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="section" value="{{ $ot['key'] }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    Nama {{ ucfirst($ot['key']) }}
                                                    @if($ot['key'] !== 'wali') <span class="text-danger" aria-label="wajib">*</span> @endif
                                                </label>
                                                <input type="text" name="nama" class="form-control"
                                                       value="{{ $ot['data']?->nama }}"
                                                       {{ $ot['key'] !== 'wali' ? 'required' : '' }}
                                                       placeholder="Nama lengkap {{ $ot['key'] }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NIK {{ ucfirst($ot['key']) }}</label>
                                                <input type="text" name="nik" class="form-control"
                                                       value="{{ $ot['data']?->nik }}" maxlength="16" placeholder="16 digit NIK">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Pekerjaan</label>
                                                <input type="text" name="pekerjaan" class="form-control" value="{{ $ot['data']?->pekerjaan }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Penghasilan / Bulan</label>
                                                <select name="penghasilan" class="form-select">
                                                    <option value="">— Pilih Rentang —</option>
                                                    @foreach(['< 500.000','500.000 - 1.000.000','1.000.001 - 2.000.000','2.000.001 - 5.000.000','> 5.000.000'] as $ph)
                                                        <option value="{{ $ph }}" {{ $ot['data']?->penghasilan === $ph ? 'selected' : '' }}>Rp {{ $ph }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Pendidikan Terakhir</label>
                                                <select name="pendidikan" class="form-select">
                                                    <option value="">— Pilih —</option>
                                                    @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3','Tidak Sekolah'] as $pdd)
                                                        <option value="{{ $pdd }}" {{ $ot['data']?->pendidikan === $pdd ? 'selected' : '' }}>{{ $pdd }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nomor HP</label>
                                                <input type="text" name="no_hp" class="form-control input-phone"
                                                       value="{{ $ot['data']?->no_hp }}" placeholder="08xxxxxxxxxx">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success btn-save btn-sm">
                                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan {{ ucfirst($ot['key']) }}</span>
                                                    <span class="btn-spinner d-none" aria-live="polite">
                                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>{{-- /pane-ortu --}}

        {{-- ─────────────────────────────────────
             TAB 5 — Data Periodik
             [DIUBAH] Input number di-wrap lebih
             rapi menggunakan input-group stepper
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-periodik">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-heart-pulse" aria-hidden="true"></i>Data Periodik
                </div>
                <div class="profil-card-body">
                    <form id="form-periodik" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="periodik">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="p-tinggi">Tinggi Badan <span class="text-muted fw-normal">(cm)</span></label>
                                <input type="number" id="p-tinggi" name="tinggi_badan" class="form-control"
                                       value="{{ $periodik?->tinggi_badan }}" min="50" max="250" step="0.1" placeholder="170">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="p-berat">Berat Badan <span class="text-muted fw-normal">(kg)</span></label>
                                <input type="number" id="p-berat" name="berat_badan" class="form-control"
                                       value="{{ $periodik?->berat_badan }}" min="10" max="200" step="0.1" placeholder="60">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="p-lingkar">Lingkar Kepala <span class="text-muted fw-normal">(cm)</span></label>
                                <input type="number" id="p-lingkar" name="lingkar_kepala" class="form-control"
                                       value="{{ $periodik?->lingkar_kepala }}" min="30" max="80" step="0.1" placeholder="54">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="p-jarak">Jarak ke Sekolah <span class="text-muted fw-normal">(km)</span></label>
                                <input type="number" id="p-jarak" name="jarak_rumah" class="form-control"
                                       value="{{ $periodik?->jarak_rumah }}" min="0" step="0.1" placeholder="5.5">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="p-waktu">Waktu Tempuh <span class="text-muted fw-normal">(menit)</span></label>
                                <input type="number" id="p-waktu" name="waktu_tempuh" class="form-control"
                                       value="{{ $periodik?->waktu_tempuh }}" min="0" placeholder="30">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="jumlah_saudara">Jumlah Saudara</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="stepNumber('jumlah_saudara',-1)" aria-label="Kurangi">−</button>
                                    <input type="number" name="jumlah_saudara" id="jumlah_saudara" class="form-control text-center"
                                           value="{{ $periodik?->jumlah_saudara ?? 0 }}" min="0" max="20">
                                    <button type="button" class="btn btn-outline-secondary" onclick="stepNumber('jumlah_saudara',1)" aria-label="Tambah">+</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Data Periodik</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>{{-- /pane-periodik --}}

        {{-- ─────────────────────────────────────
             TAB 6 — Kontak
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-kontak">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-telephone" aria-hidden="true"></i>Data Kontak
                </div>
                <div class="profil-card-body">
                    <form id="form-kontak" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="kontak">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="kontak-hp">Nomor HP <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone" aria-hidden="true"></i></span>
                                    <input type="text" id="kontak-hp" name="no_hp" class="form-control input-phone"
                                           value="{{ $kontak?->no_hp }}" required placeholder="08xxxxxxxxxx">
                                </div>
                                <div class="form-text">Hanya angka · 10–15 digit</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="kontak-email">Email Kontak</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                    <input type="email" id="kontak-email" name="email" class="form-control"
                                           value="{{ $kontak?->email }}" placeholder="email@contoh.com">
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Kontak</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>{{-- /pane-kontak --}}

        {{-- ─────────────────────────────────────
             TAB 7 — Dokumen Pribadi
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-dokumen">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>Dokumen Pribadi
                </div>
                <div class="profil-card-body">
                    <div class="info-box mb-4">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span>Isi nomor dokumen yang dimiliki. Dokumen yang tidak dimiliki dapat dikosongkan.</span>
                    </div>
                    <form id="form-dokumen" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="dokumen">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-1" for="dok-kip">
                                    Nomor KIP
                                    <i class="bi bi-question-circle text-muted" aria-hidden="true"
                                       data-bs-toggle="tooltip"
                                       title="Kartu Indonesia Pintar — diberikan kepada siswa kurang mampu"></i>
                                </label>
                                <input type="text" id="dok-kip" name="no_kip" class="form-control"
                                       value="{{ $dokumen?->no_kip }}" maxlength="30" placeholder="Nomor KIP (opsional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-1" for="dok-pkh">
                                    Nomor PKH
                                    <i class="bi bi-question-circle text-muted" aria-hidden="true"
                                       data-bs-toggle="tooltip"
                                       title="Program Keluarga Harapan — program bantuan sosial pemerintah"></i>
                                </label>
                                <input type="text" id="dok-pkh" name="no_pkh" class="form-control"
                                       value="{{ $dokumen?->no_pkh }}" maxlength="30" placeholder="Nomor PKH (opsional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-1" for="dok-kitas">
                                    Nomor KITAS
                                    <i class="bi bi-question-circle text-muted" aria-hidden="true"
                                       data-bs-toggle="tooltip"
                                       title="Kartu Izin Tinggal Terbatas — untuk WNA"></i>
                                </label>
                                <input type="text" id="dok-kitas" name="no_kitas" class="form-control"
                                       value="{{ $dokumen?->no_kitas }}" maxlength="30" placeholder="Nomor KITAS (opsional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-center gap-1" for="dok-paspor">
                                    Nomor Paspor
                                    <i class="bi bi-question-circle text-muted" aria-hidden="true"
                                       data-bs-toggle="tooltip"
                                       title="Nomor paspor untuk WNA atau peserta yang memiliki paspor"></i>
                                </label>
                                <input type="text" id="dok-paspor" name="no_paspor" class="form-control"
                                       value="{{ $dokumen?->no_paspor }}" maxlength="30" placeholder="Nomor Paspor (opsional)">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Dokumen</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>{{-- /pane-dokumen --}}

    </div>{{-- #profil-content --}}
    </div>{{-- col kanan --}}

</div>{{-- .row --}}

@endsection

{{-- ════════════════════════════════════════════════════
     SCRIPTS — di-push ke @stack('scripts') di footer
     [DIUBAH] Logika JS tidak berubah, hanya:
     1. Variabel const dikelompokkan di atas (config section)
     2. Fungsi helper diberi JSDoc singkat
     3. Tooltip dihapus dari sini (sudah diinisialisasi
        secara global di portal.blade.php)
     ════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Config ─────────────────────────────────────────── */
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
    const LS_KEY       = 'ppdb_profil_tab';
    const URL_DAPODIK  = "{{ route('ppdb.profil.dapodik') }}";
    const URL_AKUN     = "{{ route('ppdb.profil.akun') }}";
    const URL_PASSWORD = "{{ route('ppdb.profil.password') }}";

    /* ── Toast ──────────────────────────────────────────── */
    const toastEl  = document.getElementById('ppdb-toast');
    const toastMsg = document.getElementById('ppdb-toast-msg');
    const bsToast  = new bootstrap.Toast(toastEl, { delay: 3500 });

    /** Tampilkan toast sukses/gagal. ok=true → hijau, false → merah */
    function showToast(msg, ok = true) {
        toastEl.className = 'toast align-items-center border-0 text-white ' +
            (ok ? 'bg-success' : 'bg-danger');
        toastMsg.textContent = msg;
        bsToast.show();
    }

    /* ── Tab Navigation ─────────────────────────────────── */
    const navBtns = document.querySelectorAll('.profil-nav-item');
    const panes   = document.querySelectorAll('.tab-pane-profil');

    /** Pindah ke tab dengan id tertentu dan simpan pilihan ke localStorage */
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

    // Pulihkan tab terakhir yang dibuka
    const saved = localStorage.getItem(LS_KEY);
    if (saved && document.getElementById('pane-' + saved)) switchTab(saved);

    /* ── Progress Bar ───────────────────────────────────── */
    /** Perbarui progress bar sidebar setelah AJAX save berhasil */
    function updateProgress(klp) {
        if (!klp) return;
        const bar   = document.getElementById('sidebar-progress-bar');
        const label = document.getElementById('persen-label');
        if (bar) {
            bar.style.width = klp.persen + '%';
            bar.setAttribute('aria-valuenow', klp.persen);
        }
        if (label) label.textContent = klp.persen + '%';
    }

    /* ── Tab Dot ────────────────────────────────────────── */
    // Mapping section dapodik → id tab sidebar
    const dotMap = {
        pribadi : 'pribadi',
        alamat  : 'alamat',
        ayah    : 'ortu',
        ibu     : 'ortu',
        wali    : 'ortu',
        periodik: 'periodik',
        kontak  : 'kontak',
        dokumen : 'dokumen',
    };

    /** Ubah dot tab menjadi hijau (terisi) setelah data berhasil disimpan */
    function markDotOk(section) {
        const tabId = dotMap[section] || section;
        const dot   = document.getElementById('dot-' + tabId);
        if (dot) dot.className = 'tab-dot dot-ok';
    }

    /* ── Button Loading State ───────────────────────────── */
    function setLoading(btn, loading) {
        btn.querySelector('.btn-text').classList.toggle('d-none', loading);
        btn.querySelector('.btn-spinner').classList.toggle('d-none', !loading);
        btn.disabled = loading;
    }

    /* ── Generic AJAX POST ──────────────────────────────── */
    async function ajaxForm(url, formData) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        return res.json();
    }

    /* ── Dapodik Forms (Pribadi, Alamat, Ortu, dll) ─────── */
    const dapodikForms = ['pribadi','alamat','ayah','ibu','wali','periodik','kontak','dokumen'];

    dapodikForms.forEach(section => {
        const form = document.getElementById('form-' + section);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('.btn-save');
            setLoading(btn, true);

            const fd = new FormData(form);
            fd.set('_method', 'PUT'); // Laravel method spoofing

            try {
                const json = await ajaxForm(URL_DAPODIK, fd);
                if (json.success) {
                    showToast(json.message);
                    markDotOk(section);
                    updateProgress(json.kelengkapan);
                    if (json.foto_url) {
                        document.getElementById('foto-preview').src     = json.foto_url;
                        document.getElementById('foto-preview-tab').src = json.foto_url;
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

    /* ── Akun Form ──────────────────────────────────────── */
    document.getElementById('form-akun').addEventListener('submit', async (e) => {
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

    /* ── Password Form ──────────────────────────────────── */
    document.getElementById('form-password').addEventListener('submit', async (e) => {
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

    /* ── Foto Preview ───────────────────────────────────── */
    function bindFotoPreview(inputId, previewId) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        inp.addEventListener('change', function () {
            if (!this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(previewId).src  = e.target.result;
                document.getElementById('foto-preview').src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        });
    }
    bindFotoPreview('input-foto',     'foto-preview');
    bindFotoPreview('input-foto-tab', 'foto-preview-tab');

    // Sinkronisasi file dari sidebar overlay ke input di tab
    document.getElementById('input-foto').addEventListener('change', function () {
        const tabInput = document.getElementById('input-foto-tab');
        if (tabInput && this.files[0]) {
            const dt = new DataTransfer();
            dt.items.add(this.files[0]);
            tabInput.files = dt.files;
        }
    });

    /* ── GPS ────────────────────────────────────────────── */
    const btnGps    = document.getElementById('btn-gps');
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
                    document.getElementById('inp-bujur').value   = pos.coords.longitude.toFixed(7);
                    gpsStatus.textContent = `✓ ${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
                },
                () => { gpsStatus.textContent = 'Gagal mendapatkan lokasi. Izinkan akses GPS.'; }
            );
        });
    }

    /* ── Phone Input — hanya angka ──────────────────────── */
    document.querySelectorAll('.input-phone').forEach(inp => {
        inp.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });

    /* ── Toggle Visibility Password ─────────────────────── */
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', function () {
            const inp  = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            const isHidden = inp.type === 'password';
            inp.type       = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
        });
    });

    /* ── Stepper untuk Jumlah Saudara ───────────────────── */
    window.stepNumber = function (id, delta) {
        const inp = document.getElementById(id);
        if (!inp) return;
        const val = parseInt(inp.value || 0) + delta;
        inp.value = Math.max(parseInt(inp.min || 0), Math.min(parseInt(inp.max || 999), val));
    };

})();
</script>
@endpush