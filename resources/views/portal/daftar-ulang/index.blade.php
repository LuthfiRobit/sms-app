@extends('layouts.portal')
@section('title', 'Daftar Ulang Peserta')

@php
    $pendaftaran      = $info['pendaftaran']       ?? null;
    $peserta          = $info['peserta']            ?? null;
    $hasilSeleksi     = $info['hasil_seleksi']      ?? null;
    $jalur            = $info['jalur']              ?? null;
    $tahunPelajaran   = $info['tahun_pelajaran']    ?? null;
    $deadline         = $info['deadline']           ?? null;
    $sisaHari         = $info['sisa_hari']          ?? 0;
    $sisaJam          = $info['sisa_jam']           ?? 0;
    $sisaMenit        = $info['sisa_menit']         ?? 0;
    $sudahDaftarUlang = $info['sudah_daftar_ulang'] ?? false;
    $terlambat        = $info['terlambat']          ?? true;
    $statusKelulusan  = $info['status_kelulusan']   ?? null;
    $statusPendaftaran = $info['status_pendaftaran'] ?? null;

    $namaPeserta   = optional($peserta?->user)->name ?? 'Peserta';
    $namaJalur     = optional($jalur)->nama ?? 'Reguler';
    $namaSekolah   = config('ppdb.nama_sekolah', 'SMK Negeri');
    $noPendaftaran = optional($pendaftaran)->no_pendaftaran ?? ('#' . optional($pendaftaran)->id);
    $namaTahun     = optional($tahunPelajaran)->nama ?? '2026/2027';
@endphp

@section('content')

    @include('portal.daftar-ulang.partials._daftar_ulang_header')

    <div class="row g-4">
        {{-- 🛰️ SIDEBAR NAVIGATION --}}
        <div class="col-lg-3 col-md-4">
            @include('portal.daftar-ulang.partials._daftar_ulang_sidebar')
        </div>

        {{-- 📂 CONTENT AREA --}}
        <div class="col-lg-9 col-md-8">

            {{-- ================================================================
                 STATE 1: SISWA TETAP — Sudah dikonfirmasi admin
                 ================================================================ --}}
            @if($statusPendaftaran === 'siswa_tetap')
                <div class="portal-card">
                    {{-- Celebration Header --}}
                    <div class="du-celebration-header">
                        <div class="confetti-overlay" id="confettiContainer"></div>
                        <div class="display-3 mb-3" style="position: relative; z-index: 2;">🎓</div>
                        <h1 class="fw-900 text-white mb-2" style="position: relative; z-index: 2;">Selamat!</h1>
                        <p class="text-white opacity-75 mb-4 fs-5" style="position: relative; z-index: 2;">
                            Anda resmi menjadi <strong>Siswa Baru</strong>!
                        </p>

                        {{-- Info Tiles --}}
                        <div class="row g-2 justify-content-center" style="position: relative; z-index: 2; max-width: 480px; margin: 0 auto;">
                            <div class="col-6 col-sm-4">
                                <div class="du-tile">
                                    <div class="du-tile-label">Nama</div>
                                    <div class="du-tile-value" style="font-size: .85rem;">{{ \Illuminate\Support\Str::limit($namaPeserta, 15) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4">
                                <div class="du-tile">
                                    <div class="du-tile-label">TP</div>
                                    <div class="du-tile-value" style="font-size: .85rem;">{{ $namaTahun }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="du-tile">
                                    <div class="du-tile-label">No. Daftar</div>
                                    <div class="du-tile-value" style="font-size: .85rem;">{{ $noPendaftaran }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-4 p-md-5">
                        <div class="du-alert du-alert-success mb-4 text-center d-block">
                            <div class="fs-1 mb-2">🎉</div>
                            <h5 class="fw-bold mb-1">Selamat Datang di {{ $namaSekolah }}!</h5>
                            <p class="opacity-75 mb-0">
                                Anda telah terdaftar sebagai siswa resmi angkatan tahun pelajaran
                                <strong>{{ $namaTahun }}</strong>. Pantau informasi lebih lanjut melalui portal.
                            </p>
                        </div>
                        <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none">
                            <i class="bi bi-house"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>

            {{-- ================================================================
                 STATE 2: DAFTAR ULANG — Menunggu konfirmasi admin
                 ================================================================ --}}
            @elseif($statusPendaftaran === 'daftar_ulang')
                <div class="portal-card">
                    {{-- Success Header --}}
                    <div style="background: linear-gradient(135deg, #0c4a6e 0%, #075985 100%); padding: 2.5rem 2rem; text-align: center; position: relative;">
                        <div style="font-size: 3rem; margin-bottom: .5rem;">✅</div>
                        <h2 class="text-white fw-800 mb-2">Daftar Ulang Terkirim!</h2>
                        <p class="text-white opacity-75 mb-0">Konfirmasi Anda telah berhasil dikirim ke sistem.</p>
                    </div>

                    {{-- Body --}}
                    <div class="p-4 p-md-5">
                        {{-- Waiting Card --}}
                        <div class="du-alert du-alert-success mb-4">
                            <div class="du-alert-icon">
                                <span class="du-pulse-dot"></span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Menunggu Konfirmasi Admin</h6>
                                <p class="opacity-75 mb-0 small">
                                    Data daftar ulang Anda sedang divalidasi oleh panitia PPDB.
                                    Notifikasi konfirmasi akan dikirim melalui email dan portal peserta ini.
                                </p>
                            </div>
                        </div>

                        {{-- Info Items --}}
                        <div class="bg-light p-4 rounded-4 mb-4 border">
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <div class="text-muted x-small fw-800 text-uppercase letter-spacing-1 mb-1">No. Pendaftaran</div>
                                    <div class="fw-bold text-dark">{{ $noPendaftaran }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-muted x-small fw-800 text-uppercase letter-spacing-1 mb-1">Jalur</div>
                                    <div class="fw-bold text-dark">{{ $namaJalur }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-muted x-small fw-800 text-uppercase letter-spacing-1 mb-1">Tahun Pelajaran</div>
                                    <div class="fw-bold text-dark">{{ $namaTahun }}</div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #0c4a6e, #075985);">
                            <i class="bi bi-house"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>

            {{-- ================================================================
                 STATE 3: LULUS + TERLAMBAT — Deadline terlewat
                 ================================================================ --}}
            @elseif($statusKelulusan === 'lulus' && $terlambat)
                <div class="portal-card">
                    {{-- Danger Header --}}
                    <div style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); padding: 2.5rem 2rem; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: .5rem;">⏰</div>
                        <h2 class="text-white fw-800 mb-2">Batas Waktu Terlewat</h2>
                        <p class="text-white opacity-75 mb-0">
                            Deadline daftar ulang telah melewati batas yang ditentukan.
                        </p>
                    </div>

                    <div class="p-4 p-md-5">
                        {{-- Warning Detail --}}
                        <div class="du-alert du-alert-danger mb-4">
                            <div class="du-alert-icon">🚫</div>
                            <div>
                                <h6 class="fw-bold mb-1">Batas Waktu Daftar Ulang Telah Lewat</h6>
                                @if($deadline)
                                    <p class="opacity-75 mb-1 small">
                                        Deadline:
                                        <strong>{{ \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
                                        pukul <strong>{{ \Carbon\Carbon::parse($deadline)->format('H:i') }} WIB</strong>
                                    </p>
                                @endif
                                <p class="opacity-75 mb-0 small">
                                    Waktu pendaftaran Anda sudah tidak bisa diproses secara otomatis.
                                </p>
                            </div>
                        </div>

                        {{-- Contact Admin --}}
                        <div class="du-alert du-alert-warning mb-4 d-block">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-telephone me-2"></i>Hubungi Admin Sekolah
                            </h6>
                            <p class="mb-3 small opacity-75">
                                Segera hubungi panitia PPDB {{ $namaSekolah }} untuk mendapatkan penanganan lebih lanjut.
                            </p>
                            @php $kontak = config('ppdb.kontak', null); @endphp
                            @if($kontak)
                                <div class="d-flex flex-wrap gap-2">
                                    @if(isset($kontak['whatsapp']))
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $kontak['whatsapp']) }}" target="_blank"
                                           class="btn btn-sm btn-success rounded-pill fw-semibold px-3">
                                            <i class="bi bi-whatsapp me-1"></i>{{ $kontak['whatsapp'] }}
                                        </a>
                                    @endif
                                    @if(isset($kontak['email']))
                                        <a href="mailto:{{ $kontak['email'] }}"
                                           class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
                                            <i class="bi bi-envelope me-1"></i>Email
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p class="mb-0 small fw-semibold text-dark">
                                    Datang langsung ke kantor {{ $namaSekolah }} pada jam kerja (07.00 – 14.00 WIB).
                                </p>
                            @endif
                        </div>

                        <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #b45309, #92400e);">
                            <i class="bi bi-house"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>

            {{-- ================================================================
                 STATE 4: LULUS + BELUM TERLAMBAT — Form konfirmasi
                 ================================================================ --}}
            @elseif($statusKelulusan === 'lulus' && !$terlambat)
                <div class="portal-card">
                    {{-- Success Header --}}
                    <div class="du-celebration-header">
                        <div class="confetti-overlay" id="confettiContainer"></div>
                        <div style="position: relative; z-index: 2;">
                            <div class="display-4 mb-2">🏆</div>
                            <h2 class="fw-900 text-white mb-2">Selamat, Anda Lulus!</h2>
                            <p class="text-white opacity-75 mb-4">
                                Segera lakukan daftar ulang sebelum batas waktu.
                            </p>

                            {{-- Tiles --}}
                            <div class="row g-2 justify-content-center" style="max-width: 480px; margin: 0 auto;">
                                <div class="col-6 col-sm-3">
                                    <div class="du-tile">
                                        <div class="du-tile-label">Jalur</div>
                                        <div class="du-tile-value" style="font-size: .8rem;">{{ \Illuminate\Support\Str::limit($namaJalur, 12) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="du-tile">
                                        <div class="du-tile-label">Skor</div>
                                        <div class="du-tile-value">{{ $hasilSeleksi?->total_nilai ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="du-tile">
                                        <div class="du-tile-label">Peringkat</div>
                                        <div class="du-tile-value">{{ $hasilSeleksi?->peringkat ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="du-tile">
                                        <div class="du-tile-label">No. Daftar</div>
                                        <div class="du-tile-value" style="font-size: .8rem;">{{ \Illuminate\Support\Str::limit($noPendaftaran, 12) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-4 p-md-5">
                        {{-- Countdown --}}
                        @if($deadline)
                            <div class="du-countdown-wrap">
                                <div class="du-countdown-label">
                                    <i class="bi bi-alarm me-2"></i>Sisa Waktu Daftar Ulang
                                </div>
                                <div class="du-countdown-timer">
                                    <div class="du-countdown-unit">
                                        <span class="du-countdown-number" id="cd-hari">{{ $sisaHari }}</span>
                                        <div class="du-countdown-unit-label">Hari</div>
                                    </div>
                                    <div class="du-countdown-sep">:</div>
                                    <div class="du-countdown-unit">
                                        <span class="du-countdown-number" id="cd-jam">{{ str_pad($sisaJam, 2, '0', STR_PAD_LEFT) }}</span>
                                        <div class="du-countdown-unit-label">Jam</div>
                                    </div>
                                    <div class="du-countdown-sep">:</div>
                                    <div class="du-countdown-unit">
                                        <span class="du-countdown-number" id="cd-menit">{{ str_pad($sisaMenit, 2, '0', STR_PAD_LEFT) }}</span>
                                        <div class="du-countdown-unit-label">Menit</div>
                                    </div>
                                    <div class="du-countdown-sep">:</div>
                                    <div class="du-countdown-unit">
                                        <span class="du-countdown-number" id="cd-detik">00</span>
                                        <div class="du-countdown-unit-label">Detik</div>
                                    </div>
                                </div>
                                <div class="mt-3 small text-primary-hover opacity-75 fw-bold">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Batas akhir:
                                    {{ \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                                    pukul {{ \Carbon\Carbon::parse($deadline)->format('H:i') }} WIB
                                </div>
                            </div>
                        @endif

                        {{-- Form Konfirmasi --}}
                        <div class="du-confirm-section">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <span style="width:8px;height:8px;border-radius:50%;background:var(--color-primary);display:inline-block;"></span>
                                Pernyataan Daftar Ulang
                            </h6>

                            <form id="formDaftarUlang">
                                @csrf
                                {{-- Pernyataan --}}
                                <p class="text-muted small mb-3">
                                    Dengan melakukan daftar ulang, saya menyatakan siap mengikuti proses penerimaan
                                    peserta didik baru <strong>{{ $namaSekolah }}</strong>
                                    tahun pelajaran <strong>{{ $namaTahun }}</strong>.
                                </p>

                                {{-- Checkbox --}}
                                <label class="du-checkbox-wrap" for="chk-hadir">
                                    <input type="checkbox" id="chk-hadir" name="pernyataan_hadir"
                                        value="1" onchange="toggleConfirmBtn()">
                                    <span class="du-checkbox-label">
                                        Saya menyatakan <strong>hadir dan siap mengikuti proses selanjutnya</strong>
                                        dalam penerimaan peserta didik baru {{ $namaSekolah }}.
                                    </span>
                                </label>

                                {{-- Submit Button --}}
                                <button type="button" id="btnDaftarUlang" class="du-btn-confirm" disabled
                                    onclick="showModalKonfirmasi()">
                                    <div class="du-btn-spinner" id="btnSpinner"></div>
                                    <i class="bi bi-check-circle-fill" id="btnIcon"></i>
                                    <span id="btnText">Konfirmasi Daftar Ulang</span>
                                </button>
                            </form>
                        </div>

                        {{-- Info Tambahan --}}
                        <div class="small text-muted text-center mt-3">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            Setelah konfirmasi, status Anda akan berubah menjadi "Menunggu Konfirmasi Admin".
                        </div>
                    </div>
                </div>

            {{-- ================================================================
                 STATE 5: Bukan peserta lulus — Akses tidak valid
                 ================================================================ --}}
            @else
                <div class="portal-card">
                    <div style="background: linear-gradient(135deg, #78350f 0%, #92400e 100%); padding: 2.5rem 2rem; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: .5rem;">🔒</div>
                        <h2 class="text-white fw-800 mb-2">Akses Tidak Valid</h2>
                        <p class="text-white opacity-75 mb-0">Halaman ini hanya tersedia untuk peserta tertentu.</p>
                    </div>

                    <div class="p-4 p-md-5">
                        <div class="du-alert du-alert-danger mb-4">
                            <div class="du-alert-icon">⚠️</div>
                            <div>
                                <h6 class="fw-bold mb-1">Akses Ditolak</h6>
                                <p class="opacity-75 mb-0 small">
                                    Halaman daftar ulang <strong>hanya untuk peserta yang dinyatakan lulus</strong>
                                    berdasarkan hasil seleksi PPDB. Status pendaftaran Anda saat ini tidak memenuhi
                                    syarat untuk mengakses halaman ini.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('ppdb.pengumuman.index') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #78350f, #92400e);">
                            <i class="bi bi-arrow-left"></i>
                            Kembali ke Pengumuman
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ── MODAL KONFIRMASI AKHIR ──────────────────────────────────────────── --}}
    <div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-labelledby="modalKonfirmasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 p-4 pb-2">
                    <h5 class="modal-title fw-800" id="modalKonfirmasiLabel">
                        Konfirmasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-2">
                    <div class="text-center mb-3">
                        <div style="font-size: 3rem;">🎯</div>
                    </div>
                    <p class="text-muted small text-center mb-3">
                        Apakah Anda yakin ingin melakukan daftar ulang?
                        Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                    </p>
                    <div class="bg-light rounded-3 p-3 small text-center border">
                        <div class="text-muted mb-1">Konfirmasi untuk:</div>
                        <div class="fw-bold text-dark">{{ $namaPeserta }}</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-2 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnModalSubmit" class="btn btn-success rounded-pill px-4 fw-bold flex-grow-1"
                        onclick="submitDaftarUlang()">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="modalSpinner" role="status"></span>
                        <span id="modalBtnText">Ya, Konfirmasi!</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TOAST ───────────────────────────────────────────────────────────── --}}
    <div class="du-toast-wrap" id="toastWrap"></div>

@endsection

@push('styles')
    @include('portal.daftar-ulang.partials._daftar_ulang_styles')
@endpush

@push('scripts')
    @include('portal.daftar-ulang.partials._daftar_ulang_scripts')
@endpush
