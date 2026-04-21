@extends('layouts.portal')
@section('title', 'Status & Pengumuman Pendaftaran')

@section('content')

    @include('portal.pengumuman.partials._pengumuman_header')

    <div class="row g-4">
        {{-- 🛰️ SIDEBAR NAVIGATION --}}
        <div class="col-lg-3 col-md-4">
            @include('portal.pengumuman.partials._pengumuman_sidebar')
        </div>

        {{-- 📂 CONTENT AREA --}}
        <div class="col-lg-9 col-md-8">

            @if ($pendaftaranList->isEmpty())
                <div class="profil-card">
                    <div class="profil-card-body py-5">
                        <div class="empty-announcement">
                            <img src="https://illustrations.popsy.co/blue/waiting-for-notification.svg" class="empty-img"
                                alt="empty">
                            <h3 class="fw-bold text-dark mt-4">Belum Ada Pendaftaran</h3>
                            <p class="text-muted mx-auto mb-4" style="max-width: 400px">Sepertinya Anda belum melakukan
                                pendaftaran. Silakan pilih jalur pendaftaran yang tersedia untuk memulai perjalanan Anda.</p>
                            <a href="{{ route('ppdb.pendaftaran.pilih') }}"
                                class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow">
                                <i class="bi bi-rocket-takeoff me-2"></i>Daftar Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @foreach ($pendaftaranList as $pendaftaran)
                    @php
                        $status = $pendaftaran->status;
                        $hasilSeleksi = $pendaftaran->hasil_seleksi ?? ($pendaftaran->hasilSeleksi ?? null);
                        $waktuPengumuman = $hasilSeleksi ? $hasilSeleksi->waktu_pengumuman : null;
                        $pengumumanTersedia = $waktuPengumuman && now()->greaterThanOrEqualTo($waktuPengumuman);

                        // Step Mapping (Berdasarkan logic lama)
                        $steps = [
                            ['label' => 'Mendaftar', 'desc' => 'Draft/Submit'],
                            ['label' => 'Verifikasi', 'desc' => 'Validasi Berkas'],
                            ['label' => 'Seleksi', 'desc' => 'Proses Penilaian'],
                            ['label' => 'Pengumuman', 'desc' => 'Hasil Akhir'],
                            ['label' => 'Daftar Ulang', 'desc' => 'Registrasi Ulang'],
                            ['label' => 'Siswa Tetap', 'desc' => 'Selesai'],
                        ];

                        $activeStep = 0;
                        if (in_array($status, ['draft', 'submit'])) {
                            $activeStep = 0;
                        } elseif ($status === 'verifikasi') {
                            $activeStep = 1;
                        } elseif (in_array($status, ['diverifikasi', 'valid', 'proses_seleksi'])) {
                            $activeStep = 2;
                        } elseif (in_array($status, ['lulus', 'tidak_lulus', 'cadangan'])) {
                            $activeStep = 3;
                        } elseif ($status === 'daftar_ulang') {
                            $activeStep = 4;
                        } elseif ($status === 'siswa_tetap') {
                            $activeStep = 5;
                        }
                    @endphp

                    <div class="portal-card mb-4"
                        style="border-top: 5px solid @if($status == 'lulus') #10b981 @elseif($status == 'tidak_lulus') #ef4444 @else #3b82f6 @endif !important;">
                        <div class="card-header-portal">
                            <span>
                                <i class="bi @if($status == 'lulus') bi-award-fill @else bi-tag-fill @endif"></i>
                                {{ $pendaftaran->jalurPendaftaran->nama ?? 'Jalur Reguler' }}
                            </span>
                            <span
                                class="badge @if($status == 'lulus') bg-success @elseif($status == 'tidak_lulus') bg-danger @else bg-primary @endif rounded-pill px-3 py-2 fw-bold shadow-sm">
                                <i
                                    class="bi @if($status == 'lulus') bi-check-circle-fill @elseif($status == 'tidak_lulus') bi-x-circle-fill @else bi-info-circle-fill @endif me-2"></i>
                                {{ str_replace('_', ' ', strtoupper($status)) }}
                            </span>
                        </div>

                        <div class="profil-card-body p-4 pt-3">
                            {{-- Integrated Sub-Header --}}
                            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                                <div class="bg-primary-light text-primary px-3 py-1 rounded-3 fw-800 x-small">
                                    <i class="bi bi-calendar-event me-2"></i>{{ $pendaftaran->tahunPelajaran->nama ?? '2026/2027' }}
                                </div>
                                <div class="text-muted x-small fw-bold">
                                    <i class="bi bi-hash me-1"></i>No. Pendaftaran: <span
                                        class="text-dark">{{ $pendaftaran->no_pendaftaran }}</span>
                                </div>
                            </div>

                            <!-- timeline -->
                            <div class="timeline-container px-2">
                                @foreach($steps as $index => $step)
                                    @php
                                        $stateClass = '';
                                        if ($index < $activeStep)
                                            $stateClass = 'completed';
                                        elseif ($index == $activeStep)
                                            $stateClass = 'active';
                                    @endphp
                                    <div class="timeline-step {{ $stateClass }}">
                                        <div class="timeline-icon">
                                            @if($index < $activeStep)
                                                <i class="bi bi-check-lg"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <div class="timeline-label">{{ $step['label'] }}</div>
                                        <div class="timeline-desc">{{ $step['desc'] }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <hr class="border-light my-4">

                            <!-- Status specific content -->
                            <div class="status-content">
                                @if($status === 'draft')
                                    <div class="decision-card decision-card--info">
                                        <div class="mb-3 fs-1 text-primary"><i class="bi bi-pencil-square"></i></div>
                                        <h3 class="fw-800 mb-2">Formulir Belum Lengkap</h3>
                                        <p class="mb-4">Data pendaftaran Anda masih dalam bentuk draft. Selesaikan pengisian data agar
                                            dapat segera diproses oleh panitia.</p>
                                        <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id) }}"
                                            class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                                            Lanjutkan Pendaftaran <i class="bi bi-arrow-right ms-2"></i>
                                        </a>
                                    </div>

                                @elseif($status === 'submit')
                                    <div class="decision-card decision-card--info">
                                        <div class="mb-3 fs-1 text-primary"><i class="bi bi-send-check-fill"></i></div>
                                        <h3 class="fw-800 mb-2">Pendaftaran Terkirim</h3>
                                        <p class="mb-0">Data Anda telah kami terima dan sedang mengantre untuk diverifikasi oleh admin.
                                            Mohon tunggu pembaruan selanjutnya.</p>
                                    </div>

                                @elseif($status === 'verifikasi')
                                    <div class="decision-card decision-card--info">
                                        <div class="mb-3">
                                            <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;">
                                            </div>
                                        </div>
                                        <h3 class="fw-800 mb-2">Verifikasi Berjalan</h3>
                                        <p class="mb-0">Admin sedang melakukan validasi kesesuaian dokumen yang Anda unggah. Harap
                                            pastikan nomor kontak Anda aktif.</p>
                                    </div>

                                @elseif(in_array($status, ['lulus', 'tidak_lulus', 'cadangan']))
                                    @if(!$pengumumanTersedia)
                                        <div class="decision-card decision-card--info">
                                            <div class="mb-3 fs-1 text-muted opacity-50"><i class="bi bi-megaphone"></i></div>
                                            <h3 class="fw-800 mb-2">Hasil Seleksi Segera Tiba</h3>
                                            <p class="mb-0">Hasil seleksi Anda akan diumumkan secara serentak. Pantau halaman ini secara
                                                berkala untuk rilis pengumuman.</p>
                                        </div>
                                    @else
                                        <!-- RESULTS RELEASED -->
                                        @if($status === 'lulus')
                                            <div class="decision-card decision-card--success">
                                                <div class="lulus-title">🏆 SELAMAT!</div>
                                                <h3 class="fw-800 mb-3">ANDA DINYATAKAN LULUS</h3>
                                                <p class="px-md-5 opacity-75">Kami dengan bangga mengumumkan bahwa Anda berhasil lolos tahap
                                                    seleksi <strong>{{ $pendaftaran->jalurPendaftaran->nama }}</strong>.</p>

                                                <div class="stats-grid">
                                                    <div class="stats-item">
                                                        <span class="stats-val">{{ $hasilSeleksi->total_nilai ?? '-' }}</span>
                                                        <span class="stats-label">Skor Akhir</span>
                                                    </div>
                                                    <div class="stats-item">
                                                        <span class="stats-val">{{ $hasilSeleksi->peringkat ?? '-' }}</span>
                                                        <span class="stats-label">Peringkat</span>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-3 justify-content-center mt-2">
                                                    <button
                                                        onclick="downloadKartu('{{ route('ppdb.pengumuman.download', $pendaftaran->id) }}', this)"
                                                        id="btn-download-{{ $pendaftaran->id }}"
                                                        class="btn btn-outline-success px-4 py-2 rounded-pill fw-bold bg-white">
                                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                        <span class="btn-text"><i class="bi bi-download me-2"></i> Download Kartu</span>
                                                    </button>
                                                    <a href="{{ route('ppdb.daftar-ulang.index', $pendaftaran->id) }}"
                                                        class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow">
                                                        Daftar Ulang Sekarang <i class="bi bi-check-lg ms-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif($status === 'tidak_lulus')
                                            <div class="decision-card decision-card--danger">
                                                <div class="mb-3 fs-1 text-danger opacity-50"><i class="bi bi-cloud-rain-fill"></i></div>
                                                <h3 class="fw-800 mb-2 text-dark">Tetap Semangat!</h3>
                                                <p class="mb-4">Kami memohon maaf, Anda belum berhasil melewati tahap seleksi kali ini. Jangan
                                                    menyerah, jadikan ini motivasi untuk peluang berikutnya!</p>

                                                <div class="stats-grid m-0">
                                                    <div class="px-4 border-end">
                                                        <span class="stats-label">Skor</span>
                                                        <span class="stats-val">{{ $hasilSeleksi->total_nilai ?? '-' }}</span>
                                                    </div>
                                                    <div class="px-4">
                                                        <span class="stats-label">Peringkat</span>
                                                        <span class="stats-val">{{ $hasilSeleksi->peringkat ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                @elseif($status === 'siswa_tetap')
                                    <div class="decision-card"
                                        style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: #fff;">
                                        <div class="mb-3 fs-1"><i class="bi bi-mortarboard-fill"></i></div>
                                        <h2 class="fw-900 mb-2">Pendaftaran Selesai</h2>
                                        <p class="opacity-75 mb-4">Selamat! Anda telah resmi terdaftar sebagai siswa baru tahun ajaran
                                            2026/2027. Selamat menimba ilmu di sekolah kami!</p>
                                        <a href="{{ route('ppdb.daftar-ulang.index', $pendaftaran->id) }}"
                                            class="btn btn-light px-4 py-2 rounded-pill fw-bold text-primary">
                                            <i class="bi bi-eye me-2"></i>Lihat Detail Status
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>

@endsection

@push('styles')
    @include('portal.pengumuman.partials._pengumuman_styles')
@endpush

@push('scripts')
    @include('portal.pengumuman.partials._pengumuman_scripts')
@endpush