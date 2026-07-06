@extends('admin.layouts.app')

@section('title', 'Uji WhatsApp Fonnte')

@section('content')
<div class="row">
    <!-- Kolom Kiri: Konfigurasi & Status Device -->
    <div class="col-xl-4 col-md-5 mb-4">
        <!-- Card 1: Setup Status -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden position-relative">
            <div class="card-header bg-dark py-3">
                <h5 class="text-white mb-0 d-flex align-items-center">
                    <i class="bi bi-gear-fill me-2 text-warning animate-spin"></i>
                    Konfigurasi Fonnte
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small uppercase fw-bold">Status Integrasi</label>
                    <div>
                        @if($config['token_exists'])
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-1"></i> Terkonfigurasi
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-1">
                    <label class="form-label text-muted small uppercase fw-bold">Nomor Pengirim (Sender)</label>
                    <div class="fs-5 fw-semibold text-dark">
                        <i class="bi bi-phone me-1 text-primary"></i>
                        {{ $config['sender'] }}
                    </div>
                    <small class="text-muted">Ditentukan melalui variabel <code>FONNTE_SENDER</code> di .env</small>
                </div>
            </div>
        </div>

        <!-- Card 2: Status Device WhatsApp -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark py-3 d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0 d-flex align-items-center">
                    <i class="bi bi-whatsapp me-2 text-success"></i>
                    Status Device
                </h5>
                <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-1" id="btn-refresh-status" title="Refresh Status">
                    <i class="bi bi-arrow-clockwise fs-6"></i>
                </button>
            </div>
            <div class="card-body" id="device-status-container">
                <!-- Loader State -->
                <div id="device-status-loader" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Menghubungkan ke API Fonnte...</p>
                </div>

                <!-- Error State -->
                <div id="device-status-error" class="d-none py-3 text-center">
                    <div class="display-6 text-danger mb-2"><i class="bi bi-x-circle"></i></div>
                    <h6 class="text-danger fw-bold">Gagal Mengambil Status</h6>
                    <p class="text-muted small mb-3" id="device-error-message">Token tidak valid atau masalah koneksi.</p>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill" id="btn-retry-status">
                        <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                    </button>
                </div>

                <!-- Success / Data State -->
                <div id="device-status-success" class="d-none">
                    <div class="d-flex align-items-center mb-3">
                        <div class="position-relative me-3">
                            <div class="device-status-dot connected pulse-green"></div>
                            <span class="fs-1"><i class="bi bi-phone-vibrate text-primary"></i></span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" id="device-name">-</h6>
                            <small class="text-muted" id="device-number">-</small>
                        </div>
                    </div>

                    <hr class="opacity-10 my-3">

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-muted small d-block">Status Koneksi</span>
                            <span class="badge" id="device-conn-badge">Unknown</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Paket Layanan</span>
                            <span class="fw-semibold text-dark" id="device-package">-</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Sisa Kuota Pengiriman</span>
                            <span class="fw-bold text-dark small" id="device-quota">-</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" id="device-quota-progress" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="mb-1 text-center py-2 bg-light rounded border border-light-subtle">
                        <span class="text-muted small d-block">Masa Aktif Layanan</span>
                        <span class="fw-bold text-dark" id="device-expiry">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Uji Kirim & Log Respon -->
    <div class="col-xl-8 col-md-7 mb-4">
        <!-- Card 3: Form Uji Kirim -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark py-3">
                <h5 class="text-white mb-0 d-flex align-items-center">
                    <i class="bi bi-chat-left-dots-fill me-2 text-info"></i>
                    Form Uji Kirim WhatsApp
                </h5>
            </div>
            <div class="card-body">
                <form id="form-test-wa" autocomplete="off">
                    @csrf
                    <!-- Penerima -->
                    <div class="mb-3">
                        <label for="recipient" class="form-label fw-semibold text-dark">Nomor WhatsApp Tujuan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-phone"></i></span>
                            <input type="text" class="form-control" id="recipient" name="recipient" placeholder="Contoh: 081234567890 atau 6281234567890" required>
                        </div>
                        <div class="form-text text-muted">
                            Format bebas (diawali 0 atau 62). Sistem otomatis mengkonversi angka awal <code>0</code> menjadi kode negara <code>62</code>.
                        </div>
                    </div>

                    <!-- Template Selector -->
                    <div class="mb-3">
                        <label for="template-select" class="form-label fw-semibold text-dark">Pilih Template Pesan</label>
                        <select class="form-select" id="template-select">
                            <option value="custom" selected>-- Tulis Pesan Kustom --</option>
                            <option value="ppdb_pendaftaran">📋 Notifikasi PPDB - Pendaftaran Baru</option>
                            <option value="ppdb_pembayaran">✅ Notifikasi PPDB - Pembayaran Sukses</option>
                            <option value="ppdb_lulus">🎉 Notifikasi PPDB - Pengumuman Kelulusan</option>
                        </select>
                    </div>

                    <!-- Isi Pesan -->
                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold text-dark">Isi Pesan WhatsApp</label>
                        <textarea class="form-control" id="message" name="message" rows="8" placeholder="Tulis pesan Anda di sini..." required></textarea>
                        <div class="form-text text-muted">
                            Mendukung formatting WhatsApp: gunakan <code>*tebal*</code>, <code>_miring_</code>, atau <code>~coret~</code>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2" id="btn-send-message">
                            <span id="btn-send-text">
                                <i class="bi bi-send me-1"></i> Kirim Pesan Uji
                            </span>
                            <span id="btn-send-loader" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card 4: Logger Respons API -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark py-3 d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0 d-flex align-items-center">
                    <i class="bi bi-terminal-fill me-2 text-success"></i>
                    Log Respons API Fonnte
                </h5>
                <button type="button" class="btn btn-outline-light btn-sm text-xs rounded" id="btn-clear-log">
                    <i class="bi bi-trash me-1"></i> Bersihkan Log
                </button>
            </div>
            <div class="card-body bg-black text-success p-0 m-0 overflow-auto" style="max-height: 250px; min-height: 120px;">
                <pre class="p-3 mb-0" id="api-response-logger" style="font-family: 'Courier New', Courier, monospace; font-size: 0.85rem; line-height: 1.4; color: #39FF14;">// Menunggu data request...</pre>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling khusus status device dot */
    .device-status-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        position: absolute;
        bottom: 2px;
        right: -2px;
        border: 2px solid white;
    }
    
    .device-status-dot.connected {
        background-color: #2ec4b6;
    }
    
    .device-status-dot.disconnected {
        background-color: #e63946;
    }

    /* Efek pulsing green */
    .pulse-green {
        box-shadow: 0 0 0 0 rgba(46, 196, 182, 0.7);
        animation: pulse-green-anim 1.8s infinite;
    }
    
    @keyframes pulse-green-anim {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(46, 196, 182, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(46, 196, 182, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(46, 196, 182, 0);
        }
    }

    /* Efek pulsing red */
    .pulse-red {
        box-shadow: 0 0 0 0 rgba(230, 57, 70, 0.7);
        animation: pulse-red-anim 1.8s infinite;
    }
    
    @keyframes pulse-red-anim {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(230, 57, 70, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(230, 57, 70, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(230, 57, 70, 0);
        }
    }

    /* Putaran icon */
    .animate-spin {
        animation: spin-anim 8s linear infinite;
        display: inline-block;
    }
    
    @keyframes spin-anim {
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Objek berisi template pesan
        const templates = {
            custom: "",
            ppdb_pendaftaran: "📋 *PENDAFTARAN PPDB BARU*\n━━━━━━━━━━━━━━━━━━━━━\n\nAssalamu'alaikum Wr. Wb.\n\nHalo *Budi Santoso*,\n\nTerima kasih telah melakukan pendaftaran di SMK Ma'arif. Pendaftaran Anda dengan nomor *REG-2026-0001* telah berhasil kami terima.\n\nLangkah selanjutnya adalah melakukan pembayaran biaya pendaftaran dan melakukan verifikasi dokumen kelengkapan di portal PPDB.\n\nTerima kasih.\nWassalamu'alaikum Wr. Wb.",
            ppdb_pembayaran: "✅ *PEMBAYARAN PPDB BERHASIL*\n━━━━━━━━━━━━━━━━━━━━━\n\nHalo *Budi Santoso*,\n\nPembayaran biaya pendaftaran untuk nomor pendaftaran *REG-2026-0001* sebesar *Rp 150.000* telah terkonfirmasi *LUNAS*.\n\nDokumen Anda saat ini sedang dalam antrean verifikasi oleh Panitia PPDB. Pantau terus status kelulusan Anda di portal.\n\nTerima kasih atas kepercayaannya.",
            ppdb_lulus: "🎉 *PENGUMUMAN KELULUSAN PPDB*\n━━━━━━━━━━━━━━━━━━━━━\n\nHalo *Budi Santoso*,\n\nKabar gembira! Berdasarkan hasil verifikasi berkas dan seleksi akademik, Anda dinyatakan *DITERIMA (LULUS)* sebagai Calon Siswa Baru di SMK Ma'arif untuk Tahun Pelajaran 2026/2027.\n\nSegera lakukan proses daftar ulang di portal PPDB sebelum tanggal 15 Juli 2026 untuk mengamankan kursi Anda.\n\nSelamat bergabung!"
        };

        // Ganti isi textarea pesan jika template diubah
        $('#template-select').on('change', function () {
            let key = $(this).val();
            $('#message').val(templates[key] || "");
        });

        // Fungsi fetch status device dari controller
        function loadDeviceStatus() {
            $('#device-status-loader').removeClass('d-none');
            $('#device-status-success').addClass('d-none');
            $('#device-status-error').addClass('d-none');

            let url = "{{ route('admin.system.whatsapp.status') }}";

            // Gunakan handleAjax (fungsi pembungkus AJAX global di template)
            handleAjax(url, 'GET', {}, function (response) {
                // Success Callback
                $('#device-status-loader').addClass('d-none');
                
                if (response.status === 200 && response.data) {
                    let data = response.data;
                    
                    // Render details
                    $('#device-name').text(data.name || 'Fonnte Device');
                    $('#device-number').text('+' + (data.device || '-'));
                    $('#device-package').text(data.package || '-');
                    
                    // Quota
                    let quotaMax = parseInt(data.quota || 0);
                    let messagesCount = parseInt(data.messages || 0);
                    let quotaFormatted = quotaMax.toLocaleString('id-ID');
                    $('#device-quota').text(quotaFormatted);
                    
                    // Progress bar
                    let percent = 100;
                    if (quotaMax > 0) {
                        percent = ((quotaMax - messagesCount) / quotaMax) * 100;
                        if (percent < 0) percent = 0;
                    }
                    $('#device-quota-progress').css('width', percent + '%');
                    
                    // Expiry
                    if (data.expired) {
                        // Expired is epoch timestamp
                        let expiryDate = new Date(parseInt(data.expired) * 1000);
                        let options = { day: 'numeric', month: 'long', year: 'numeric' };
                        $('#device-expiry').text(expiryDate.toLocaleDateString('id-ID', options));
                    } else {
                        $('#device-expiry').text('Lifetime / Tidak Ada');
                    }

                    // Connection badge
                    let connBadge = $('#device-conn-badge');
                    let connDot = $('.device-status-dot');
                    connBadge.removeClass().addClass('badge');
                    connDot.removeClass('connected disconnected pulse-green pulse-red');

                    if (data.device_status === 'connect') {
                        connBadge.addClass('bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded').text('Connected');
                        connDot.addClass('connected pulse-green');
                    } else {
                        connBadge.addClass('bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded').text('Disconnected');
                        connDot.addClass('disconnected pulse-red');
                    }

                    $('#device-status-success').removeClass('d-none');
                } else {
                    showErrorState('Respon status invalid');
                }
            }, function (xhr) {
                // Error Callback
                $('#device-status-loader').addClass('d-none');
                let errMessage = 'Terjadi kesalahan koneksi atau token salah.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMessage = xhr.responseJSON.message;
                }
                showErrorState(errMessage);
            });
        }

        // Tampilkan State Error pada Device Status Card
        function showErrorState(message) {
            $('#device-status-loader').addClass('d-none');
            $('#device-status-success').addClass('d-none');
            $('#device-status-error').removeClass('d-none');
            $('#device-error-message').text(message);
        }

        // Jalankan fetch status saat halaman selesai dimuat
        @if($config['token_exists'])
            loadDeviceStatus();
        @else
            showErrorState('Token Fonnte belum dikonfigurasi di file .env.');
        @endif

        // Aksi tombol Refresh & Retry
        $('#btn-refresh-status, #btn-retry-status').on('click', function () {
            @if($config['token_exists'])
                loadDeviceStatus();
            @else
                ResponseHandler.error('Konfigurasi .env belum diatur.');
            @endif
        });

        // Bersihkan log respon
        $('#btn-clear-log').on('click', function () {
            $('#api-response-logger').html('// Log berhasil dibersihkan.');
        });

        // Form Submit handler
        $('#form-test-wa').on('submit', function (e) {
            e.preventDefault();

            // Client check
            let phone = $('#recipient').val().trim();
            let msg = $('#message').val().trim();

            if (!phone || !msg) {
                ResponseHandler.error('Harap lengkapi semua field!');
                return;
            }

            // UI loading state
            $('#btn-send-text').addClass('d-none');
            $('#btn-send-loader').removeClass('d-none');
            $('#btn-send-message').prop('disabled', true);

            let url = "{{ route('admin.system.whatsapp.send') }}";
            let data = $(this).serialize();

            // Kirim request AJAX
            handleAjax(url, 'POST', data, function (response) {
                // Success Handler
                $('#btn-send-text').removeClass('d-none');
                $('#btn-send-loader').addClass('d-none');
                $('#btn-send-message').prop('disabled', false);

                // Update response log viewer
                $('#api-response-logger').html(
                    '// REQUEST: SUCCESS\n' + 
                    JSON.stringify(response, null, 4)
                );

                ResponseHandler.success('Pesan WhatsApp sukses diantrekan/dikirim!');
                
                // Reload status device untuk mengupdate sisa kuota
                setTimeout(loadDeviceStatus, 1000);
            }, function (xhr) {
                // Error Handler
                $('#btn-send-text').removeClass('d-none');
                $('#btn-send-loader').addClass('d-none');
                $('#btn-send-message').prop('disabled', false);

                let logData = xhr.responseJSON || { status: xhr.status, message: xhr.statusText };
                $('#api-response-logger').html(
                    '// REQUEST: FAILED\n' + 
                    JSON.stringify(logData, null, 4)
                );

                let errText = 'Gagal mengirim pesan WhatsApp.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errText = xhr.responseJSON.message;
                }
                
                ResponseHandler.error(errText);
            });
        });
    });
</script>
@endpush
