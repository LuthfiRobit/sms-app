@extends('layouts.portal')
@section('title', 'Status & Pengumuman Pendaftaran')

@push('styles')
<style>
    /* TIMELINE STYLING */
    .timeline-wrapper {
        padding: 1.5rem 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .timeline-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-width: 750px;
        position: relative;
        padding: 0 10px;
    }
    .timeline-steps::before {
        content: "";
        position: absolute;
        top: 25px;
        left: 0;
        right: 0;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }
    .step-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        text-align: center;
    }
    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-bottom: 12px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: #94a3b8;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Connectors Logic */
    .step-item::after {
        content: "";
        position: absolute;
        top: 25px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #e2e8f0;
        z-index: -1;
    }
    .step-item:last-child::after { display: none; }

    /* DONE STATE */
    .step-item.done .step-icon {
        background: #10b981;
        border-color: #10b981;
        color: #fff;
    }
    .step-item.done::after {
        background: #10b981;
    }

    /* CURRENT STATE */
    .step-item.current .step-icon {
        background: #fff;
        border-color: #3b82f6;
        color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        animation: pulse-step 2s infinite;
    }
    @keyframes pulse-step {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }

    .step-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .step-desc {
        font-size: 0.7rem;
        color: #64748b;
    }

    /* CONFETTI */
    .confetti-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 10;
    }
    .confetti {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #ff0;
        border-radius: 2px;
        animation: confetti-fall 2.5s ease-out infinite;
    }
    @keyframes confetti-fall {
        0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(400px) rotate(720deg); opacity: 0; }
    }

    /* CARD PREMIUM */
    .card-announcement {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        background: #fff;
        margin-bottom: 2.5rem;
    }
    .card-announcement-header {
        padding: 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .status-badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
        padding: 8px 16px;
        border-radius: 100px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* SPINNER */
    .btn-loading .spinner-border {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        margin-right: 0.5rem;
    }
    .btn-loading .btn-text-content {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-800 text-dark mb-1">Status & Pengumuman</h2>
                <p class="text-muted mb-0">Lacak perjalanan pendaftaran Anda di sini.</p>
            </div>
            <div class="bg-white p-2 rounded-3 shadow-sm border">
                <span class="text-muted small px-2">Role: <strong class="text-primary">Calon Siswa</strong></span>
            </div>
        </div>

        @if($pendaftaranList->isEmpty())
            <div class="card card-announcement text-center p-5">
                <div class="py-5">
                    <img src="https://illustrations.popsy.co/blue/waiting-for-notification.svg" style="width: 200px" class="mb-4" alt="empty">
                    <h3 class="fw-bold text-dark">Belum Ada Pendaftaran</h3>
                    <p class="text-muted mx-auto" style="max-width: 400px">Sepertinya Anda belum melakukan pendaftaran. Silakan pilih jalur pendaftaran yang tersedia.</p>
                    <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-primary px-4 py-2 mt-3 rounded-pill fw-bold">Daftar Sekarang</a>
                </div>
            </div>
        @else
            @foreach($pendaftaranList as $pendaftaran)
                @php
                    $status = $pendaftaran->status;
                    $hasilSeleksi = $pendaftaran->hasil_seleksi ?? $pendaftaran->hasilSeleksi ?? null;
                    $waktuPengumuman = $hasilSeleksi ? $hasilSeleksi->waktu_pengumuman : null;
                    $pengumumanTersedia = $waktuPengumuman && now()->greaterThanOrEqualTo($waktuPengumuman);

                    // Step Mapping
                    $steps = [
                        ['label' => 'Mendaftar', 'desc' => 'Draft/Submit'],
                        ['label' => 'Verifikasi', 'desc' => 'Admin Cek'],
                        ['label' => 'Seleksi', 'desc' => 'Penilaian'],
                        ['label' => 'Pengumuman', 'desc' => 'Hasil Akhir'],
                        ['label' => 'Daftar Ulang', 'desc' => 'Konfirmasi'],
                        ['label' => 'Siswa Tetap', 'desc' => 'Selesai'],
                    ];

                    $activeStep = 0;
                    if (in_array($status, ['draft', 'submit'])) $activeStep = 0;
                    elseif ($status === 'verifikasi') $activeStep = 1;
                    elseif (in_array($status, ['diverifikasi', 'valid', 'proses_seleksi'])) $activeStep = 2;
                    elseif (in_array($status, ['lulus', 'tidak_lulus', 'cadangan'])) $activeStep = 3;
                    elseif ($status === 'daftar_ulang') $activeStep = 4;
                    elseif ($status === 'siswa_tetap') $activeStep = 5;
                @endphp

                <div class="card card-announcement">
                    <!-- Header Section -->
                    <div class="card-announcement-header">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fw-800 text-dark mb-1">{{ $pendaftaran->jalurPendaftaran->nama ?? 'Jalur Reguler' }}</h4>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-muted small"><i class="bi bi-hash text-primary me-1"></i> {{ $pendaftaran->id }}</span>
                                    <span class="text-muted small"><i class="bi bi-calendar3 text-primary me-1"></i> TP {{ $pendaftaran->tahunPelajaran->nama ?? '2026/2027' }}</span>
                                </div>
                            </div>
                            <div class="status-badge @if($status=='lulus') text-success @elseif($status=='tidak_lulus') text-danger @else text-primary @endif">
                                {{ str_replace('_', ' ', strtoupper($status)) }}
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="timeline-wrapper pt-3">
                            <div class="timeline-steps">
                                @foreach($steps as $index => $step)
                                    @php
                                        $stateClass = '';
                                        if ($index < $activeStep) $stateClass = 'done';
                                        elseif ($index == $activeStep) $stateClass = 'current';
                                    @endphp
                                    <div class="step-item {{ $stateClass }}">
                                        <div class="step-icon">
                                            @if($index < $activeStep) <i class="bi bi-check-lg"></i> @else {{ $index + 1 }} @endif
                                        </div>
                                        <div class="step-label text-nowrap">{{ $step['label'] }}</div>
                                        <div class="step-desc text-nowrap">{{ $step['desc'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Body Content -->
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            @if($status === 'draft')
                                <div class="col-md-8">
                                    <h5 class="fw-bold mb-2">Formulir Belum Lengkap</h5>
                                    <p class="text-muted">Ayo tuntaskan pendaftaranmu! Data yang kamu isi masih dalam bentuk draf dan belum masuk ke sistem admin.</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id) }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold w-100">Lanjutkan Pendaftaran <i class="bi bi-arrow-right ms-2"></i></a>
                                </div>

                            @elseif($status === 'submit')
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center gap-4 bg-light p-4 rounded-4 border border-info-subtle border-start-0 border-end-0 border-bottom-0 border-4">
                                        <div class="fs-1 text-primary"><i class="bi bi-clock-history"></i></div>
                                        <div>
                                            <h5 class="fw-bold mb-1">Pendaftaran Terkirim</h5>
                                            <p class="text-muted mb-0 small">Sistem sedang memproses data kamu. Pantau terus status login kamu untuk pembaruan berikutnya.</p>
                                        </div>
                                    </div>
                                </div>

                            @elseif($status === 'verifikasi')
                                <div class="col-md-12">
                                    <div class="bg-indigo-50 p-4 rounded-4 d-flex align-items-center gap-4 border-start border-primary border-4" style="background: #f5f3ff">
                                        <div class="spinner-grow text-primary" role="status"></div>
                                        <div>
                                            <h5 class="fw-bold text-primary mb-1">Verifikasi Berkas Sedang Berjalan</h5>
                                            <p class="mb-0 small text-primary opacity-75">Admin sedang memvalidasi kesesuaian dokumen yang kamu unggah.</p>
                                        </div>
                                    </div>
                                </div>

                            @elseif(in_array($status, ['lulus', 'tidak_lulus', 'cadangan']))
                                @if(!$pengumumanTersedia)
                                    <div class="col-md-12 text-center py-4">
                                        <div class="bg-light p-5 rounded-4 border">
                                            <i class="bi bi-megaphone fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                            <h5 class="fw-bold text-dark">Hasil Seleksi Segera Hadir</h5>
                                            <p class="text-muted mb-0">Pengumuman kelulusan akan dirilis secara serentak. Cek kembali secara berkala.</p>
                                        </div>
                                    </div>
                                @else
                                    <!-- RESULTS AVAILABLE -->
                                    @if($status === 'lulus')
                                        <div class="col-md-12 position-relative">
                                            <div class="confetti-overlay">
                                                @for($i=0; $i<12; $i++)
                                                    <div class="confetti" style="left: {{ rand(5, 95) }}%; top: -10px; animation-delay: {{ $i * 0.2 }}s; background-color: {{ ['#ff595e','#ffca3a','#8ac926','#1982c4','#6a4c93'][rand(0,4)] }}"></div>
                                                @endfor
                                            </div>

                                            <div class="p-4 p-md-5 rounded-4 text-center border-4 border-success border-top border-bottom" style="background: #f0fdf4">
                                                <div class="display-4 mb-3">🏆</div>
                                                <h1 class="fw-900 text-success mb-2">SELAMAT ANDA LULUS!</h1>
                                                <p class="text-success opacity-75 mb-4 px-md-5">Kamu berhasil lolos tahap seleksi <strong>{{ $pendaftaran->jalurPendaftaran->nama }}</strong>. Selamat bergabung menjadi bagian dari kami!</p>
                                                
                                                <div class="row g-3 justify-content-center mb-5">
                                                    <div class="col-6 col-md-3">
                                                        <div class="bg-white p-3 rounded-4 shadow-sm border border-success-subtle">
                                                            <div class="small text-muted mb-1 text-uppercase fw-bold">Skor Akhir</div>
                                                            <div class="h3 mb-0 fw-800 text-dark">{{ $hasilSeleksi->total_nilai ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <div class="bg-white p-3 rounded-4 shadow-sm border border-success-subtle">
                                                            <div class="small text-muted mb-1 text-uppercase fw-bold">Peringkat</div>
                                                            <div class="h3 mb-0 fw-800 text-dark">{{ $hasilSeleksi->peringkat ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-3 justify-content-center">
                                                    <button onclick="downloadKartu('{{ route('ppdb.pengumuman.download', $pendaftaran->id) }}', this)" 
                                                        id="btn-download-{{ $pendaftaran->id }}"
                                                        class="btn btn-outline-success px-4 py-2 rounded-pill fw-bold">
                                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                        <span class="btn-text"><i class="bi bi-download me-2"></i> Download Kartu</span>
                                                    </button>
                                                    <a href="{{ route('ppdb.daftar-ulang.index', $pendaftaran->id) }}" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow">
                                                        Daftar Ulang Sekarang <i class="bi bi-arrow-right ms-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($status === 'tidak_lulus')
                                        <div class="col-md-12 text-center p-5 rounded-4" style="background: #fff5f5; border: 2px dashed #feb2b2;">
                                            <div class="display-6 mb-3">☁️</div>
                                            <h4 class="fw-bold text-dark mb-2">Tetap Semangat!</h4>
                                            <p class="text-muted mb-4">Mohon maaf, kamu belum berhasil dalam seleksi kali ini. Jangan menyerah, masih banyak peluang lainnya!</p>
                                            <div class="d-inline-flex gap-4 p-3 bg-white rounded-3 shadow-sm border mb-4">
                                                <div class="text-center px-3 border-end">
                                                    <div class="small text-muted fw-bold">Skor</div>
                                                    <div class="h4 mb-0 fw-bold">{{ $hasilSeleksi->total_nilai ?? '-' }}</div>
                                                </div>
                                                <div class="text-center px-3">
                                                    <div class="small text-muted fw-bold">Peringkat</div>
                                                    <div class="h4 mb-0 fw-bold">{{ $hasilSeleksi->peringkat ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                            @elseif($status === 'siswa_tetap')
                                <div class="col-md-12 text-center py-5 rounded-4 text-white shadow-lg overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)">
                                    <div class="display-3 mb-3">🎓</div>
                                    <h2 class="fw-900 mb-2">Pendaftaran Selesai</h2>
                                    <p class="opacity-75 mb-4">Selamat! Kamu telah resmi terdaftar sebagai siswa baru tahun ajaran 2026/2027.</p>
                                    <a href="{{ route('ppdb.daftar-ulang.index', $pendaftaran->id) }}" class="btn btn-light px-4 py-2 rounded-pill fw-bold text-primary">Lihat Detail Status</a>
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

@push('scripts')
<script>
    /**
     * Download Kartu Peserta via Iframe
     */
    function downloadKartu(url, btnElement) {
        if (!btnElement || btnElement.getAttribute('disabled')) return;

        // Visual State: Loading
        btnElement.setAttribute('disabled', 'true');
        const spinner = btnElement.querySelector('.spinner-border');
        const icon = btnElement.querySelector('i');
        const textSpan = btnElement.querySelector('.btn-text');

        if (spinner) spinner.classList.remove('d-none');
        if (icon) icon.classList.add('d-none');

        // Processing Download
        const ifr = document.createElement('iframe');
        ifr.style.display = 'none';
        ifr.src = url;
        document.body.appendChild(ifr);

        // Reset state after estimated download initiation
        setTimeout(() => {
            btnElement.removeAttribute('disabled');
            if (spinner) spinner.classList.add('d-none');
            if (icon) icon.classList.remove('d-none');
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Pengumuman Page Loaded');
    });
</script>
@endpush
