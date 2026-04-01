@extends('admin.layouts.app')
@section('title', 'Detail Pendaftaran')

@section('content')
@php
    $meta = $data['section_metadata'];
    $biodata = $data['section_biodata'];
    $fields = $data['section_field_values'];
    $dokumens = $data['section_dokumen'];
    $progress = $data['progress'];
    
    $statusColors = [
        'draft' => 'secondary',
        'submit' => 'warning',
        'verifikasi' => 'info',
        'lulus' => 'success',
        'tidak_lulus' => 'danger',
        'daftar_ulang' => 'primary',
        'siswa_tetap' => 'dark',
    ];
    $statusColor = $statusColors[$meta['status']] ?? 'secondary';
@endphp

<div class="row mb-3">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $biodata['nama_lengkap'] }} <span class="badge bg-{{ $statusColor }} ms-2">{{ strtoupper(str_replace('_', ' ', $meta['status'])) }}</span></h3>
                    <p class="mb-0 text-muted">No: {{ $meta['no_pendaftaran'] }} | Jalur: {{ $meta['jalur'] }} | Tahun: {{ $meta['tahun_pelajaran'] }}</p>
                    <p class="mb-0 text-muted small"><i class="ri-calendar-line"></i> Didaftar: {{ $meta['tanggal_daftar'] ?? '-' }}</p>
                </div>
                <div>
                   <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <label>Kelengkapan Formulir ({{ $progress['field_wajib']['percent'] }}%)</label>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress['field_wajib']['percent'] }}%" aria-valuenow="{{ $progress['field_wajib']['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small class="text-muted">{{ $progress['field_wajib']['terisi'] }} dari {{ $progress['field_wajib']['total'] }} wajib terisi</small>
    </div>
    <div class="col-md-6">
        <label>Kelengkapan Dokumen ({{ $progress['dokumen_wajib']['percent'] }}%)</label>
        <div class="progress">
            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $progress['dokumen_wajib']['percent'] }}%" aria-valuenow="{{ $progress['dokumen_wajib']['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small class="text-muted">{{ $progress['dokumen_wajib']['uploaded'] }} dari {{ $progress['dokumen_wajib']['total'] }} wajib terupload</small>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri -->
    <div class="col-xl-4 col-lg-5">
        <!-- Section 1: Data Peserta -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Ringkasan Data Peserta</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="{{ $biodata['foto_url'] ?? 'https://ui-avatars.com/api/?name='.urlencode($biodata['nama_lengkap']).'&background=random' }}" class="rounded-circle avatar-xl img-thumbnail" alt="foto" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr><th style="width:40%">NIK</th><td>: {{ $biodata['nik'] ?? '-' }}</td></tr>
                        <tr><th>NISN</th><td>: {{ $biodata['nisn'] ?? '-' }}</td></tr>
                        <tr><th>Jenis Kelamin</th><td>: {{ $biodata['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                        <tr><th>TTL</th><td>: {{ $biodata['tempat_lahir'] }}, {{ $biodata['tanggal_lahir'] }}</td></tr>
                        <tr><th>No. HP</th><td>: {{ $biodata['no_hp'] ?? '-' }}</td></tr>
                        <tr><th>Email</th><td>: {{ $biodata['email'] ?? '-' }}</td></tr>
                        <tr><th>Alamat</th><td>: {{ $biodata['alamat'] }}, {{ $biodata['desa_kelurahan'] }}, {{ $biodata['kecamatan'] }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($meta['status'] === 'submit')
        <!-- Section 4: Aksi Verifikasi -->
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 text-white">Aksi Verifikasi</h5>
            </div>
            <div class="card-body text-center">
                <p>Verifikasi data dan dokumen di atas. Apakah semua lengkap dan sesuai?</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="konfirmasiVerif('approve')"><i class="ri-check-line"></i> Approve (Terima)</button>
                    <button class="btn btn-danger btn-lg" onclick="showRejectModal()"><i class="ri-close-line"></i> Reject (Kembalikan)</button>
                </div>
            </div>
        </div>
        @endif

        <!-- Section 5: Log Status -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Riwayat Verifikasi</h5>
            </div>
            <div class="card-body">
                @if($meta['verifier'])
                <div class="alert alert-info mb-0">
                    <p class="mb-1"><strong>Diverifikasi Oleh:</strong> {{ $meta['verifier']['nama'] }}</p>
                    <p class="mb-1"><strong>Tanggal:</strong> {{ $meta['verifier']['verified_at'] }}</p>
                    @if($meta['catatan_verifikasi'])
                    <hr>
                    <p class="mb-0"><strong>Catatan:</strong> <br> {{ $meta['catatan_verifikasi'] }}</p>
                    @endif
                </div>
                @else
                <p class="text-muted text-center mb-0">Belum diverifikasi</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-xl-8 col-lg-7">
        
        <!-- Section 2: Isian Formulir -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Isian Formulir Dinamis</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($fields as $f)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted mb-1">{{ $f['label'] }} {!! $f['is_required'] ? '<span class="text-danger">*</span>' : '' !!}</label>
                        <div class="fw-bold">{{ $f['value_display'] }}</div>
                    </div>
                    @empty
                    <div class="col-12"><p class="text-muted mb-0">Tidak ada isian formulir tambahan.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Section 3: Dokumen -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Dokumen Persyaratan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($dokumens as $dok)
                    <div class="col-md-6 mb-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $dok['nama_syarat'] }} {!! $dok['wajib'] ? '<span class="text-danger">*</span>' : '' !!}</h6>
                                
                                <span class="badge bg-{{ $dok['status_verifikasi'] == 'valid' ? 'success' : ($dok['status_verifikasi'] == 'invalid' ? 'danger' : 'warning') }}" id="badge-dok-{{$dok['id']}}">
                                    {{ strtoupper($dok['status_verifikasi']) }}
                                </span>
                            </div>
                            <p class="text-muted small mb-2">{{ $dok['nama_file'] }} ({{ $dok['ukuran_file_kb'] }} KB)</p>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="previewDoc('{{ $dok['url_preview'] }}', {{ $dok['is_image'] ? 'true' : 'false' }}, '{{ $dok['nama_syarat'] }}')">
                                    <i class="ri-eye-line"></i> Preview
                                </button>
                            </div>
                            
                            @if($meta['status'] == 'submit')
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-sm btn-soft-success flex-grow-1" onclick="verifDokumen({{ $dok['id'] }}, 'valid')"><i class="ri-check-line"></i> Valid</button>
                                <button class="btn btn-sm btn-soft-danger flex-grow-1" onclick="verifDokumen({{ $dok['id'] }}, 'invalid')"><i class="ri-close-line"></i> Invalid</button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-12"><p class="text-muted mb-0">Belum ada dokumen yang diunggah.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Preview Dokumen -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalTitle">Preview Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0" style="height: 75vh;">
                <div id="previewContainer" class="w-100 h-100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formReject">
                <div class="modal-header">
                    <h5 class="modal-title">Kembalikan Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">Pendaftaran ini akan dikembalikan ke status Draft agar peserta dapat melengkapi kekurangannya. Email/Notif akan dikirimkan ke peserta.</div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Perbaikan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="catatan" id="catatanReject" rows="4" required placeholder="Tulis catatan apa saja yang kurang atau salah..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Simpan & Kembalikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Header setup for CSRF
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const pendaftaranId = {{ $meta['id'] }};

    function previewDoc(url, isImage, title) {
        $('#previewModalTitle').text(title);
        let container = $('#previewContainer');
        container.empty();
        
        if (isImage) {
            container.append(`<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain;">`);
        } else {
            container.append(`<iframe src="${url}" allowfullscreen style="width:100%; height:100%; border:none;"></iframe>`);
        }
        
        $('#previewModal').modal('show');
    }

    function verifDokumen(dokId, status) {
        if (status === 'invalid') {
            Swal.fire({
                title: 'Dokumen Invalid',
                input: 'textarea',
                inputLabel: 'Masukkan keterangan kenapa dokumen tidak valid:',
                inputPlaceholder: 'Tuliskan alasan penolakan...',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Keterangan wajib diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeVerifDokumen(dokId, status, result.value);
                }
            });
        } else {
            Swal.fire({
                title: 'Validasi Dokumen',
                text: 'Tandai dokumen ini sebagai Valid?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Valid!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeVerifDokumen(dokId, status, '');
                }
            });
        }
    }

    function executeVerifDokumen(dokId, status, ket) {
        $.ajax({
            url: `/admin/pendaftaran/${pendaftaranId}/dokumen/${dokId}/verifikasi`,
            type: 'POST',
            data: { status: status, keterangan: ket },
            success: function(res) {
                // Update Badge
                let badge = $(`#badge-dok-${dokId}`);
                badge.removeClass('bg-warning bg-success bg-danger');
                if (status === 'valid') badge.addClass('bg-success').text('VALID');
                if (status === 'invalid') badge.addClass('bg-danger').text('INVALID');
                
                Swal.fire('Berhasil!', res.message, 'success');
            },
            error: function(err) {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal mengubah status dokumen', 'error');
            }
        });
    }

    function konfirmasiVerif(action) {
        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: 'Apakah Anda yakin menyetujui pendaftaran ini untuk lanjut ke tahap seleksi?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pendaftaran/${pendaftaranId}/verifikasi`,
                    type: 'POST',
                    data: { action: action, catatan: '' },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal menyetujui pendaftaran', 'error');
                    }
                });
            }
        });
    }

    function showRejectModal() {
        $('#rejectModal').modal('show');
    }

    $('#formReject').on('submit', function(e) {
        e.preventDefault();
        let cat = $('#catatanReject').val();
        
        $.ajax({
            url: `/admin/pendaftaran/${pendaftaranId}/verifikasi`,
            type: 'POST',
            data: { action: 'reject', catatan: cat },
            success: function(res) {
                $('#rejectModal').modal('hide');
                Swal.fire('Dikembalikan!', res.message, 'success').then(() => {
                    window.location.reload();
                });
            },
            error: function(err) {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal mengembalikan pendaftaran', 'error');
            }
        });
    });

</script>
@endpush
