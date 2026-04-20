@extends('layouts.portal')

@section('title', 'Profil Saya')

@push('styles')
    @include('portal.profil.partials._style')
@endpush

@section('content')

    {{-- ═══════════════════════════════════════════════════════════════════════
    DATA PREPARATION
    Semua variable dan logic TIDAK DIUBAH - hanya diformat ulang
    ═══════════════════════════════════════════════════════════════════════ --}}
    @php
        $peserta = $profil['peserta'];
        $alamat = $profil['alamat'];
        $ortu = $profil['orang_tua'];
        $periodik = $profil['periodik'];
        $kontak = $profil['kontak'];
        $dokumen = $profil['dokumen_pribadi'];
        $klp = $profil['kelengkapan'];
        $persen = $klp['persen'];
        $user = auth()->user();

        // Status kelengkapan per tab
        $tabStatus = [
            'akun' => !empty($user->name) && !empty($user->email),
            'pribadi' => !empty($peserta->nama_lengkap) && !empty($peserta->jenis_kelamin) && !empty($peserta->tanggal_lahir),
            'alamat' => !empty($alamat?->alamat) && !empty($alamat?->kabupaten_kota),
            'ortu' => !empty($ortu['ayah']?->nama) || !empty($ortu['ibu']?->nama),
            'periodik' => !empty($periodik?->tinggi_badan) && !empty($periodik?->berat_badan),
            'kontak' => !empty($kontak?->no_hp),
            'dokumen' => !empty($dokumen?->no_kip) || !empty($dokumen?->no_pkh),
        ];

        // Progress color
        $progressColor = match (true) {
            $persen >= 80 => 'success',
            $persen >= 50 => 'warning',
            default => 'danger',
        };

        // Tab definitions
        $tabs = [
            ['id' => 'akun', 'icon' => 'bi-person-gear', 'label' => 'Akun', 'key' => 'akun'],
            ['id' => 'pribadi', 'icon' => 'bi-person-vcard', 'label' => 'Pribadi', 'key' => 'pribadi'],
            ['id' => 'alamat', 'icon' => 'bi-geo-alt', 'label' => 'Alamat', 'key' => 'alamat'],
            ['id' => 'ortu', 'icon' => 'bi-people', 'label' => 'Orang Tua', 'key' => 'ortu'],
            ['id' => 'periodik', 'icon' => 'bi-heart-pulse', 'label' => 'Periodik', 'key' => 'periodik'],
            ['id' => 'kontak', 'icon' => 'bi-telephone', 'label' => 'Kontak', 'key' => 'kontak'],
            ['id' => 'dokumen', 'icon' => 'bi-file-earmark-text', 'label' => 'Dokumen', 'key' => 'dokumen'],
        ];
    @endphp

    {{-- TOAST NOTIFICATION --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="ppdb-toast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold" id="ppdb-toast-msg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Tutup"></button>
            </div>
        </div>
    </div>

    {{-- PROFILE GREETING CARD Full-width card dengan foto, nama, email, status, dan progress --}}
    <div class="profile-greeting-card">
        <div class="profile-greeting-inner">

            {{-- Foto Profil dengan upload overlay --}}
            <div class="foto-wrapper">
                <img id="foto-preview"
                    src="{{ $peserta->foto
        ? asset('storage/' . $peserta->foto)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=140&background=16a34a&color=fff&bold=true&rounded=true' }}"
                    alt="Foto Profil {{ $user->name }}">
                <label for="input-foto" class="foto-overlay" title="Ganti Foto" tabindex="0">
                    <i class="bi bi-camera-fill" aria-hidden="true"></i>
                    <span class="visually-hidden">Ganti foto profil</span>
                </label>
                <input type="file" id="input-foto" accept="image/*" class="d-none" aria-label="Upload foto profil">
            </div>

            {{-- Info: Nama, Email, Status --}}
            <div class="profile-info">
                <h1 class="profile-name">{{ $user->name }}</h1>

                <div class="profile-email">
                    <i class="bi bi-envelope-fill"></i>
                    <span>{{ $user->email }}</span>
                </div>

                <span class="profile-status-badge">
                    <i class="bi bi-circle-fill"></i>
                    {{ $user->status === 'active' ? 'Akun Aktif' : ucfirst($user->status) }}
                </span>
            </div>

            {{-- Progress Kelengkapan --}}
            <div class="profile-completion">
                <div class="completion-header">
                    <span class="completion-label">Kelengkapan Profil</span>
                    <span class="completion-percent" id="persen-label">{{ $persen }}%</span>
                </div>

                <div class="profil-progress-track">
                    <div id="sidebar-progress-bar" class="profil-progress-fill bg-{{ $progressColor }}"
                        style="width:{{ $persen }}%" role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                @if(!empty($klp['item_kurang']))
                    <div class="completion-missing">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Belum lengkap:
                        {{ implode(', ', array_slice($klp['item_kurang'], 0, 3)) }}{{ count($klp['item_kurang']) > 3 ? '...' : '' }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MAIN CONTENT AREA Navigation (left) + Content (right) --}}
    <div class="row g-4">

        {{-- NAVIGATION SIDEBAR --}}
        <div class="col-lg-3 col-md-4">
            <div class="profil-sidebar">

                {{-- Tab Navigation --}}
                <nav class="profil-nav" aria-label="Navigasi profil">
                    @foreach($tabs as $tab)
                        <button class="profil-nav-item {{ $loop->first ? 'active' : '' }}" data-tab="{{ $tab['id'] }}"
                            id="nav-{{ $tab['id'] }}" type="button" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
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
                <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn-daftar">
                    <i class="bi bi-file-earmark-plus"></i>
                    Mulai Pendaftaran
                </a>

            </div>
        </div>

        {{-- CONTENT AREA (Forms) --}}
        <div class="col-lg-9 col-md-8">
            <div id="profil-content" role="tabpanel">

                {{-- Konten untuk setiap tab akan di-generate dari original file --}}
                {{-- Untuk brevity, saya akan include struktur yang sama persis --}}
                {{-- TAB: Akun & Keamanan --}}
                @include('portal.profil.partials._tab_akun')

                {{-- TAB: Data Pribadi --}}
                @include('portal.profil.partials._tab_pribadi')

                {{-- TAB: Alamat --}}
                @include('portal.profil.partials._tab_alamat')

                {{-- TAB: Orang Tua --}}
                @include('portal.profil.partials._tab_ortu')

                {{-- TAB: Data Periodik --}}
                @include('portal.profil.partials._tab_periodik')

                {{-- TAB: Kontak --}}
                @include('portal.profil.partials._tab_kontak')

                {{-- TAB: Dokumen --}}
                @include('portal.profil.partials._tab_dokumen')

            </div>
        </div>

    </div>

@endsection

{{-- JAVASCRIPT Semua logic TIDAK DIUBAH - preserve 100% --}}
@push('scripts')
    @include('portal.profil.partials._scripts')
@endpush