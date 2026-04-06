@extends('layouts.portal')

@section('title', 'Pilih Jalur Pendaftaran — PPDB')

@section('content')

@php
    $jalurList   = $jalur['data'] ?? [];
    $profilCukup = $cekProfil['cukup'] ?? false;
    $kekurangan  = $cekProfil['kekurangan'] ?? [];

    /**
     * Warna header card per index (siklus 6 warna)
     */
    $cardColors = [
        ['bg' => '#15803d', 'light' => '#dcfce7', 'border' => '#86efac'],
        ['bg' => '#0369a1', 'light' => '#dbeafe', 'border' => '#93c5fd'],
        ['bg' => '#7c3aed', 'light' => '#ede9fe', 'border' => '#c4b5fd'],
        ['bg' => '#b45309', 'light' => '#fef3c7', 'border' => '#fcd34d'],
        ['bg' => '#be185d', 'light' => '#fce7f3', 'border' => '#f9a8d4'],
        ['bg' => '#0f766e', 'light' => '#ccfbf1', 'border' => '#5eead4'],
    ];

    $bulanIndo = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
        7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
    ];
    $fmtDate = function($d) use ($bulanIndo) {
        if (!$d) return '—';
        $dt = $d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d);
        return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
    };
    $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════ --}}
<div class="pj-page-header mb-4">
    <div class="pj-header-left">
        <div class="pj-header-icon">
            <i class="bi bi-signpost-2-fill"></i>
        </div>
        <div>
            <h1 class="pj-page-title">Pilih Jalur Pendaftaran</h1>
            <p class="pj-page-subtitle">Pilih jalur yang sesuai dengan kelayakan Anda</p>
        </div>
    </div>
    <div class="pj-header-actions">
        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Pendaftaran Saya
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PROFIL BELUM CUKUP — Warning besar
═══════════════════════════════════════════════════════════════════ --}}
@if(!$profilCukup)
<div class="profil-warning-card mb-4">
    <div class="profil-warning-icon">
        <i class="bi bi-shield-exclamation"></i>
    </div>
    <div class="profil-warning-body">
        <h4 class="profil-warning-title">
            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
            Lengkapi Data Profil Sebelum Mendaftar
        </h4>
        <p class="profil-warning-desc">
            Sistem membutuhkan data profil yang lengkap untuk memastikan pendaftaran Anda
            valid dan sesuai standar Dapodik. Harap lengkapi data berikut terlebih dahulu:
        </p>
        <ul class="profil-kekurangan-list">
            @foreach($kekurangan as $item)
            <li class="profil-kekurangan-item">
                <span class="kekurangan-icon"><i class="bi bi-x-circle-fill text-danger"></i></span>
                <span>{{ $item }}</span>
            </li>
            @endforeach
        </ul>
        <a href="{{ route('ppdb.profil.index') }}" class="btn btn-warning btn-lengkapi fw-bold mt-2">
            <i class="bi bi-pencil-square me-2"></i>Lengkapi Profil Sekarang
        </a>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     KONTEN JALUR
═══════════════════════════════════════════════════════════════════ --}}
@if(empty($jalurList))
{{-- Tidak ada jalur tersedia --}}
<div class="no-jalur-card">
    <div style="font-size:3rem;margin-bottom:12px;">📋</div>
    <h5 class="fw-700 text-dark mb-2">Belum Ada Jalur Pendaftaran Aktif</h5>
    <p class="text-muted mb-4" style="max-width:380px;margin:0 auto">
        Saat ini belum ada jalur pendaftaran yang terbuka.<br>
        Pantau terus informasi pembukaan PPDB.
    </p>
    <a href="{{ route('ppdb.dashboard') }}" class="btn btn-outline-success px-4">
        <i class="bi bi-house me-2"></i>Kembali ke Dashboard
    </a>
</div>

@else

<div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h5 class="pj-sub-heading mb-0">
        <i class="bi bi-grid-3x3-gap me-2 text-success"></i>
        Pilih Jalur yang Sesuai
        <span class="badge bg-success-subtle text-success ms-2" style="font-size:0.75rem;font-weight:600;">
            {{ count($jalurList) }} Jalur
        </span>
    </h5>
    @if(!$profilCukup)
    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
        <i class="bi bi-lock me-1"></i>Lengkapi profil untuk dapat mendaftar
    </span>
    @endif
</div>

{{-- Grid jalur --}}
<div class="jalur-grid mb-5">
    @foreach($jalurList as $idx => $item)
    @php
        $jalur         = $item['jalur'];
        $pembukaan     = $item['pembukaan'];
        $tahun         = $item['tahun_pelajaran'];
        $jadwalList    = $item['jadwal'] ?? collect();
        $syaratList    = $item['syarat'] ?? collect();
        $kuotaJurusan  = $item['kuota_jurusan'] ?? collect();
        $biayaList     = $item['biaya'] ?? collect();
        $kuotaTersedia = $item['kuota_tersedia'] ?? 0;
        $sudahDaftar   = $item['sudah_daftar'] ?? false;
        $bisaDaftar    = $item['bisa_daftar'] ?? false;
        $jadwalAktif   = $item['jadwal_aktif'] ?? null;
        $pendAktif     = $item['pendaftaran_aktif'] ?? null;

        // Total kuota dari kuota jurusan
        $totalKuota  = $kuotaJurusan->sum('kuota');
        $totalTerisi = $kuotaJurusan->sum('terisi');
        $pctTerisi   = $totalKuota > 0 ? round(($totalTerisi / $totalKuota) * 100) : 0;
        $hampirPenuh = $kuotaTersedia > 0 && $kuotaTersedia < 10;

        // Biaya utama (ambil pertama jika ada)
        $biayaUtama = $biayaList->first();
        $biayaNominal = $biayaUtama?->nominal ?? 0;

        // Warna kartu
        $color  = $cardColors[$idx % count($cardColors)];
        $cardId = 'jalur-' . $jalur->id;

        // State tombol
        $canSelect = $bisaDaftar && $profilCukup;
        $isDisabled = !$bisaDaftar || !$profilCukup;
    @endphp

    <div class="jalur-card {{ $isDisabled && !$sudahDaftar ? 'jalur-card--disabled' : '' }}"
         id="{{ $cardId }}">

        {{-- ══ CARD HEADER ══ --}}
        <div class="jalur-card-header" style="background: {{ $color['bg'] }};">
            <div class="jalur-header-content">
                <div class="jalur-kode-badge">{{ $jalur->kode_jalur }}</div>
                <h3 class="jalur-nama">{{ $jalur->nama }}</h3>
                <div class="jalur-tahun">
                    <i class="bi bi-calendar3 me-1"></i>{{ $tahun?->nama ?? $pembukaan?->nama }}
                </div>
            </div>
            <div class="jalur-status-badge-wrapper">
                @if($sudahDaftar)
                    <span class="jalur-status-chip chip-sudah">
                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Daftar
                    </span>
                @elseif($kuotaTersedia <= 0)
                    <span class="jalur-status-chip chip-penuh">
                        <i class="bi bi-x-circle-fill me-1"></i>Kuota Penuh
                    </span>
                @elseif(!$jadwalAktif)
                    <span class="jalur-status-chip chip-tutup">
                        <i class="bi bi-clock me-1"></i>Di Luar Jadwal
                    </span>
                @else
                    <span class="jalur-status-chip chip-tersedia">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Tersedia
                    </span>
                @endif
            </div>
        </div>

        {{-- ══ CARD BODY ══ --}}
        <div class="jalur-card-body">

            {{-- Deskripsi --}}
            @if($jalur->deskripsi)
            <p class="jalur-desc">{{ Str::limit($jalur->deskripsi, 120) }}</p>
            @endif

            {{-- Info Row: Kuota | Sisa | Biaya --}}
            <div class="jalur-info-grid">
                <div class="jalur-info-item">
                    <div class="jalur-info-label">Total Kuota</div>
                    <div class="jalur-info-val fw-bold">{{ number_format($totalKuota) }} siswa</div>
                </div>
                <div class="jalur-info-item">
                    <div class="jalur-info-label">Sisa Kuota</div>
                    <div class="jalur-info-val fw-bold {{ $kuotaTersedia <= 0 ? 'text-danger' : ($hampirPenuh ? 'text-warning' : 'text-success') }}">
                        {{ number_format($kuotaTersedia) }} siswa
                        @if($hampirPenuh && $kuotaTersedia > 0)
                            <div class="hampir-penuh-text">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Hampir penuh!
                            </div>
                        @endif
                    </div>
                </div>
                <div class="jalur-info-item">
                    <div class="jalur-info-label">Biaya Registrasi</div>
                    <div class="jalur-info-val fw-bold text-primary">
                        @if($biayaNominal > 0)
                            {{ $fmtRupiah($biayaNominal) }}
                        @else
                            <span class="text-success">Gratis</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Progress Bar Kuota Terisi --}}
            <div class="kuota-progress-wrapper">
                <div class="kuota-progress-label">
                    <span class="text-muted" style="font-size:0.75rem;">Kuota Terisi</span>
                    <span class="fw-600" style="font-size:0.75rem;">{{ $totalTerisi }}/{{ $totalKuota }} ({{ $pctTerisi }}%)</span>
                </div>
                <div class="kuota-progress-track">
                    <div class="kuota-progress-fill {{ $pctTerisi >= 80 ? 'kuota-fill-danger' : ($pctTerisi >= 50 ? 'kuota-fill-warning' : 'kuota-fill-ok') }}"
                         style="width: {{ $pctTerisi }}%">
                    </div>
                </div>
            </div>

            {{-- Kuota per Jurusan --}}
            @if($kuotaJurusan->isNotEmpty())
            <div class="kuota-jurusan-list">
                @foreach($kuotaJurusan as $kj)
                @php $kpct = $kj->kuota > 0 ? round(($kj->terisi/$kj->kuota)*100) : 0; @endphp
                <div class="kuota-jurusan-item">
                    <span class="kj-nama">{{ $kj->jurusan?->nama ?? 'Jurusan #'.$kj->id }}</span>
                    <span class="kj-sisa {{ ($kj->kuota - $kj->terisi) <= 0 ? 'text-danger' : 'text-success' }}">
                        Sisa {{ $kj->kuota - $kj->terisi }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Accordion: Syarat Dokumen --}}
            <div class="accordion jalur-accordion" id="acc-syarat-{{ $jalur->id }}">
                <div class="accordion-item jalur-acc-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button jalur-acc-btn collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#syarat-{{ $jalur->id }}"
                                aria-expanded="false">
                            <i class="bi bi-paperclip me-2"></i>
                            Syarat Dokumen
                            <span class="ms-2 badge bg-secondary-subtle text-secondary" style="font-size:0.7rem;">
                                {{ $syaratList->count() }} syarat
                            </span>
                        </button>
                    </h2>
                    <div id="syarat-{{ $jalur->id }}" class="accordion-collapse collapse"
                         data-bs-parent="#acc-syarat-{{ $jalur->id }}">
                        <div class="accordion-body jalur-acc-body">
                            @if($syaratList->isEmpty())
                                <p class="text-muted mb-0" style="font-size:0.8rem;">
                                    Tidak ada syarat dokumen yang ditentukan.
                                </p>
                            @else
                                <ul class="syarat-list mb-0">
                                    @foreach($syaratList as $syarat)
                                    <li class="syarat-item">
                                        <i class="bi bi-file-earmark-text text-{{ $syarat->wajib ? 'danger' : 'secondary' }} me-2"></i>
                                        <span>{{ $syarat->nama }}</span>
                                        @if($syarat->wajib)
                                            <span class="badge bg-danger-subtle text-danger ms-auto" style="font-size:0.65rem;">Wajib</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary ms-auto" style="font-size:0.65rem;">Opsional</span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Jadwal --}}
            <div class="accordion jalur-accordion mt-2" id="acc-jadwal-{{ $jalur->id }}">
                <div class="accordion-item jalur-acc-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button jalur-acc-btn collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#jadwal-{{ $jalur->id }}"
                                aria-expanded="false">
                            <i class="bi bi-calendar2-range me-2"></i>
                            Jadwal
                            <span class="ms-2 badge {{ $jadwalAktif ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}" style="font-size:0.7rem;">
                                {{ $jadwalAktif ? 'Aktif' : 'Lihat Jadwal' }}
                            </span>
                        </button>
                    </h2>
                    <div id="jadwal-{{ $jalur->id }}" class="accordion-collapse collapse"
                         data-bs-parent="#acc-jadwal-{{ $jalur->id }}">
                        <div class="accordion-body jalur-acc-body">
                            @if($jadwalList->isEmpty())
                                <p class="text-muted mb-0" style="font-size:0.8rem;">Jadwal belum ditetapkan.</p>
                            @else
                                <div class="jadwal-list">
                                    @foreach($jadwalList as $jadwal)
                                    @php
                                        $isAktifNow = $jadwal->mulai && $jadwal->selesai
                                            && now()->between($jadwal->mulai, $jadwal->selesai);
                                    @endphp
                                    <div class="jadwal-item {{ $isAktifNow ? 'jadwal-item--aktif' : '' }}">
                                        <div class="jadwal-tipe-badge">
                                            {{ ucfirst(str_replace('_', ' ', $jadwal->tipe ?? '')) }}
                                        </div>
                                        <div class="jadwal-tanggal">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i>
                                            {{ $fmtDate($jadwal->mulai) }} – {{ $fmtDate($jadwal->selesai) }}
                                        </div>
                                        @if($isAktifNow)
                                        <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">Berlangsung</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /card body --}}

        {{-- ══ CARD FOOTER ══ --}}
        <div class="jalur-card-footer" style="border-top: 2px solid {{ $color['light'] }};">

            @if($sudahDaftar && $pendAktif)
                {{-- Sudah daftar di jalur ini --}}
                <a href="{{ route('ppdb.pendaftaran.show', $pendAktif->id) }}"
                   class="btn btn-primary btn-jalur-action w-100">
                    <i class="bi bi-eye me-2"></i>Lihat Pendaftaran Saya
                </a>

            @elseif($kuotaTersedia <= 0)
                {{-- Kuota penuh --}}
                <button class="btn btn-secondary btn-jalur-action w-100" disabled>
                    <i class="bi bi-x-circle me-2"></i>Kuota Penuh
                </button>

            @elseif(!$jadwalAktif)
                {{-- Di luar jadwal --}}
                <button class="btn btn-secondary btn-jalur-action w-100" disabled>
                    <i class="bi bi-clock me-2"></i>Di Luar Jadwal Pendaftaran
                </button>

            @elseif(!$profilCukup)
                {{-- Profil belum cukup --}}
                <a href="{{ route('ppdb.profil.index') }}"
                   class="btn btn-warning btn-jalur-action w-100">
                    <i class="bi bi-person-check me-2"></i>Lengkapi Profil Dulu
                </a>

            @elseif($canSelect)
                {{-- Bisa daftar! --}}
                <button type="button"
                        class="btn btn-success btn-jalur-action w-100 btn-pilih-jalur"
                        data-jalur-id="{{ $jalur->id }}"
                        data-jalur-nama="{{ $jalur->nama }}"
                        data-biaya="{{ $biayaNominal > 0 ? $fmtRupiah($biayaNominal) : 'Gratis' }}"
                        data-syarat-count="{{ $syaratList->where('wajib', true)->count() }}"
                        onclick="bukaModalKonfirmasi(this)">
                    <i class="bi bi-check-circle me-2"></i>Pilih Jalur Ini
                </button>

            @else
                <button class="btn btn-secondary btn-jalur-action w-100" disabled>
                    <i class="bi bi-lock me-2"></i>Tidak Tersedia
                </button>
            @endif

        </div>{{-- /card footer --}}

    </div>{{-- /jalur-card --}}
    @endforeach
</div>{{-- /jalur-grid --}}

@endif

{{-- ═══════════════════════════════════════════════════════════════
     MODAL KONFIRMASI PENDAFTARAN
═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalKonfirmasiJalur" tabindex="-1" aria-labelledby="modalKonfirmasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content modal-konfirmasi">

            <div class="modal-header modal-konfirmasi-header">
                <div class="modal-konfirmasi-icon">
                    <i class="bi bi-signpost-2-fill"></i>
                </div>
                <div>
                    <h5 class="modal-title fw-800 text-dark" id="modalKonfirmasiLabel">
                        Konfirmasi Pilihan Jalur
                    </h5>
                    <p class="text-muted mb-0" style="font-size:0.8rem;">
                        Pastikan Anda memilih jalur yang tepat sebelum melanjutkan.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body px-4 py-3">
                {{-- Info jalur yang dipilih --}}
                <div class="modal-jalur-info">
                    <div class="modal-info-row">
                        <span class="modal-info-label"><i class="bi bi-signpost me-1"></i>Jalur</span>
                        <span class="modal-info-val fw-bold" id="modal-jalur-nama">—</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-info-label"><i class="bi bi-cash-coin me-1"></i>Biaya Registrasi</span>
                        <span class="modal-info-val text-primary fw-bold" id="modal-biaya">—</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-info-label"><i class="bi bi-paperclip me-1"></i>Dokumen Wajib</span>
                        <span class="modal-info-val" id="modal-syarat-count">—</span>
                    </div>
                </div>

                {{-- Peringatan --}}
                <div class="modal-warning-box">
                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                    <small>
                        Setelah pendaftaran dibuat, Anda masih perlu melengkapi formulir dan
                        mengupload dokumen sebelum dapat disubmit. Pendaftaran yang tidak
                        disubmit tidak akan diproses.
                    </small>
                </div>

                {{-- Checkbox konfirmasi --}}
                <div class="modal-checkbox-wrapper">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkKonfirmasi"
                               onchange="toggleSubmitBtn(this)">
                        <label class="form-check-label" for="checkKonfirmasi" style="font-size:0.85rem;cursor:pointer;">
                            Saya memahami persyaratan jalur ini dan siap untuk melengkapi
                            formulir serta dokumen yang diperlukan.
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer modal-konfirmasi-footer">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>

                {{-- Form tersembunyi — akan di-submit saat klik Ya --}}
                <form id="formPilihJalur" method="POST" action="{{ route('ppdb.pendaftaran.store') }}">
                    @csrf
                    <input type="hidden" name="jalur_pendaftaran_id" id="hidden-jalur-id" value="">
                    <button type="submit" id="btnSubmitKonfirmasi"
                            class="btn btn-success px-4 fw-bold" disabled>
                        <i class="bi bi-check-circle me-2"></i>Ya, Mulai Pendaftaran
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════════════════════════════ */
.pj-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #1e40af 0%, #0369a1 50%, #0891b2 100%);
    border-radius: 16px;
    padding: 22px 28px;
    box-shadow: 0 6px 24px rgba(3,105,161,0.18);
}
.pj-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pj-header-icon {
    width: 50px; height: 50px;
    border-radius: 13px;
    background: rgba(255,255,255,0.18);
    border: 2px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #fff;
    flex-shrink: 0;
}
.pj-page-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 2px;
}
.pj-page-subtitle {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.75);
    margin: 0;
}
.pj-header-actions .btn-light {
    background: rgba(255,255,255,0.18);
    border: 1.5px solid rgba(255,255,255,0.4);
    color: #fff;
    font-size: 0.8125rem;
}
.pj-header-actions .btn-light:hover {
    background: rgba(255,255,255,0.28);
    color: #fff;
}

/* ═══════════════════════════════════════════════════════════════
   PROFIL WARNING
═══════════════════════════════════════════════════════════════════ */
.profil-warning-card {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    background: #fffbeb;
    border: 2px solid #fcd34d;
    border-left: 6px solid #f59e0b;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 4px 16px rgba(245,158,11,0.12);
}
.profil-warning-icon {
    font-size: 2.4rem;
    color: #f59e0b;
    flex-shrink: 0;
    line-height: 1;
    margin-top: 4px;
}
.profil-warning-title {
    font-size: 1rem;
    font-weight: 800;
    color: #92400e;
    margin-bottom: 6px;
}
.profil-warning-desc {
    font-size: 0.8375rem;
    color: #78350f;
    margin-bottom: 12px;
}
.profil-kekurangan-list {
    list-style: none;
    padding: 0;
    margin: 0 0 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.profil-kekurangan-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8375rem;
    color: #7c2d12;
    background: rgba(220,38,38,0.06);
    border-radius: 8px;
    padding: 7px 12px;
}
.kekurangan-icon { flex-shrink: 0; }
.btn-lengkapi {
    background: #f59e0b;
    border-color: #d97706;
    color: #fff;
    transition: background 0.2s, transform 0.15s;
}
.btn-lengkapi:hover {
    background: #d97706;
    color: #fff;
    transform: translateY(-1px);
}

/* ═══════════════════════════════════════════════════════════════
   NO JALUR
═══════════════════════════════════════════════════════════════════ */
.no-jalur-card {
    text-align: center;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    padding: 60px 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}

/* ═══════════════════════════════════════════════════════════════
   SUB HEADING
═══════════════════════════════════════════════════════════════════ */
.pj-sub-heading {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
}

/* ═══════════════════════════════════════════════════════════════
   JALUR GRID
═══════════════════════════════════════════════════════════════════ */
.jalur-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
@media (max-width: 768px) {
    .jalur-grid { grid-template-columns: 1fr; }
}

/* ═══════════════════════════════════════════════════════════════
   JALUR CARD
═══════════════════════════════════════════════════════════════════ */
.jalur-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s, transform 0.2s;
}
.jalur-card:hover:not(.jalur-card--disabled) {
    box-shadow: 0 8px 28px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}
.jalur-card--disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.jalur-card--disabled * { cursor: not-allowed !important; }

/* Card Header */
.jalur-card-header {
    padding: 20px 22px;
    background: #15803d;
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.jalur-kode-badge {
    display: inline-block;
    background: rgba(255,255,255,0.22);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.3);
    margin-bottom: 8px;
}
.jalur-nama {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 4px;
    line-height: 1.3;
}
.jalur-tahun {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.78);
}
.jalur-status-badge-wrapper {
    flex-shrink: 0;
    margin-top: 4px;
}
.jalur-status-chip {
    display: inline-flex;
    align-items: center;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
}
.chip-tersedia { background: rgba(255,255,255,0.25); color: #fff; border: 1px solid rgba(255,255,255,0.4); }
.chip-penuh    { background: rgba(220,38,38,0.25); color: #fecaca; border: 1px solid rgba(220,38,38,0.4); }
.chip-tutup    { background: rgba(100,100,100,0.25); color: #d1d5db; border: 1px solid rgba(100,100,100,0.4); }
.chip-sudah    { background: rgba(59,130,246,0.3); color: #bfdbfe; border: 1px solid rgba(59,130,246,0.4); }

/* Card Body */
.jalur-card-body {
    padding: 20px 22px;
    flex: 1;
}
.jalur-desc {
    font-size: 0.8375rem;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 16px;
}

/* Info Grid */
.jalur-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.jalur-info-item {
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 10px;
    padding: 10px 12px;
}
.jalur-info-label {
    font-size: 0.68rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
}
.jalur-info-val {
    font-size: 0.85rem;
    color: #111827;
}
.hampir-penuh-text {
    font-size: 0.68rem;
    color: #ef4444;
    font-weight: 700;
    margin-top: 2px;
}

/* Kuota Progress */
.kuota-progress-wrapper { margin-bottom: 14px; }
.kuota-progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}
.kuota-progress-track {
    height: 7px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}
.kuota-progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s ease;
    min-width: 4px;
}
.kuota-fill-ok      { background: linear-gradient(90deg, #16a34a, #22c55e); }
.kuota-fill-warning { background: linear-gradient(90deg, #d97706, #fbbf24); }
.kuota-fill-danger  { background: linear-gradient(90deg, #dc2626, #ef4444); }

/* Kuota Jurusan */
.kuota-jurusan-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 14px;
    background: #f9fafb;
    border-radius: 10px;
    padding: 10px 12px;
}
.kuota-jurusan-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
}
.kj-nama  { color: #374151; }
.kj-sisa  { font-weight: 700; font-size: 0.75rem; }

/* Accordion */
.jalur-accordion { border: none; }
.jalur-acc-item {
    border: 1px solid #f3f4f6 !important;
    border-radius: 10px !important;
    overflow: hidden;
}
.jalur-acc-btn {
    font-size: 0.825rem;
    font-weight: 600;
    color: #374151;
    background: #f9fafb !important;
    padding: 10px 14px;
}
.jalur-acc-btn:not(.collapsed) {
    color: #15803d;
    background: #f0fdf4 !important;
}
.jalur-acc-btn::after {
    width: 14px; height: 14px;
    background-size: 14px;
}
.jalur-acc-body {
    padding: 12px 14px;
    background: #fff;
}

/* Syarat List */
.syarat-list { list-style: none; padding: 0; margin: 0; }
.syarat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 0.8125rem;
    color: #374151;
}
.syarat-item:last-child { border-bottom: none; }
.syarat-item .ms-auto  { flex-shrink: 0; }

/* Jadwal List */
.jadwal-list { display: flex; flex-direction: column; gap: 6px; }
.jadwal-item {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px 10px;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 0.8rem;
}
.jadwal-item--aktif {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.jadwal-tipe-badge {
    display: inline-block;
    background: #e5e7eb;
    color: #374151;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 4px;
    flex-shrink: 0;
}
.jalur-item--aktif .jadwal-tipe-badge {
    background: #dcfce7;
    color: #15803d;
}
.jadwal-tanggal { font-size: 0.78rem; color: #6b7280; flex: 1; }

/* Card Footer */
.jalur-card-footer {
    padding: 14px 22px;
    background: #fafafa;
}
.btn-jalur-action {
    font-size: 0.875rem;
    font-weight: 700;
    padding: 10px 0;
    border-radius: 10px;
    transition: transform 0.15s, box-shadow 0.15s;
}
.btn-jalur-action:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.btn-success.btn-jalur-action {
    background: linear-gradient(135deg, #16a34a, #059669);
    border: none;
    box-shadow: 0 3px 10px rgba(22,163,74,0.25);
}
.btn-success.btn-jalur-action:hover {
    box-shadow: 0 6px 18px rgba(22,163,74,0.35);
}

/* ═══════════════════════════════════════════════════════════════
   MODAL KONFIRMASI
═══════════════════════════════════════════════════════════════════ */
.modal-konfirmasi {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.modal-konfirmasi-header {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-bottom: 1px solid #bbf7d0;
    padding: 20px 24px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.modal-konfirmasi-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #16a34a, #059669);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: #fff;
    flex-shrink: 0;
}
.modal-jalur-info {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}
.modal-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.modal-info-label {
    font-size: 0.78rem;
    color: #9ca3af;
    flex-shrink: 0;
}
.modal-info-val {
    font-size: 0.875rem;
    color: #111827;
    text-align: right;
}
.modal-warning-box {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 14px;
    font-size: 0.79rem;
    color: #1e40af;
    line-height: 1.5;
}
.modal-checkbox-wrapper {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
}
.modal-checkbox-wrapper .form-check-input:checked {
    background-color: #16a34a;
    border-color: #16a34a;
}
.modal-konfirmasi-footer {
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    padding: 14px 24px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
@endpush

@push('scripts')
<script>
/**
 * Buka Modal Konfirmasi — isi data dari atribut tombol yang diklik
 */
function bukaModalKonfirmasi(btn) {
    const jalurId    = btn.dataset.jalurId;
    const jalurNama  = btn.dataset.jalurNama;
    const biaya      = btn.dataset.biaya;
    const syaratCount = btn.dataset.syaratCount;

    // Isi data modal
    document.getElementById('modal-jalur-nama').textContent  = jalurNama;
    document.getElementById('modal-biaya').textContent       = biaya;
    document.getElementById('modal-syarat-count').textContent =
        syaratCount + ' dokumen wajib';

    // Set hidden input
    document.getElementById('hidden-jalur-id').value = jalurId;

    // Reset checkbox & tombol submit
    const checkbox = document.getElementById('checkKonfirmasi');
    checkbox.checked = false;
    document.getElementById('btnSubmitKonfirmasi').disabled = true;

    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiJalur'));
    modal.show();
}

/**
 * Toggle tombol submit berdasarkan state checkbox konfirmasi
 */
function toggleSubmitBtn(checkbox) {
    document.getElementById('btnSubmitKonfirmasi').disabled = !checkbox.checked;
}

/**
 * Animate kuota progress bars saat halaman load
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kuota-progress-fill').forEach(function(bar) {
        const w = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() { bar.style.width = w; }, 300);
    });
});
</script>
@endpush
