@extends('admin.layouts.app')
@section('title', 'Detail Pengajuan Raport')

@push('styles')
<style>
    .info-label  { font-size:.75rem; color:#6c757d; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.15rem; }
    .info-value  { font-size:.93rem; color:#222; font-weight:500; }
    .accordion-button:not(.collapsed) { background-color:#e8f0ff; color:#1a3c8f; }
    .accordion-button:focus           { box-shadow:none; }
    .tbl-nilai th { font-size:.8rem; background:#f8f9fa; }
    .inp-nilai    { width:70px; font-size:.85rem; padding:.25rem .4rem; }
    .inp-catatan  { font-size:.82rem; min-width:120px; }
    .na-cell      { font-weight:700; font-size:.9rem; }
    .predikat-A   { color:#16a34a; }
    .predikat-B   { color:#2563eb; }
    .predikat-C   { color:#d97706; }
    .predikat-D   { color:#dc2626; }
    .predikat-E   { color:#7c3aed; }
</style>
@endpush

@section('content')
@php
    $statusMap = [
        'draft'        => ['secondary', 'Draft'],
        'diajukan'     => ['primary',   'Diajukan'],
        'diverifikasi' => ['info',       'Diverifikasi'],
        'ditolak'      => ['danger',     'Ditolak'],
        'disetujui'    => ['success',    'Disetujui'],
    ];
    [$statusColor, $statusLabel] = $statusMap[$pengajuan->status] ?? ['secondary', ucfirst($pengajuan->status)];
    $canEdit = $pengajuan->status === 'draft' && $setting->allow_manual_nilai;
@endphp

{{-- ── Info Card ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary">
                            <i class="bi bi-journal-bookmark-fill me-2"></i>Detail Pengajuan Raport
                        </h5>
                        <small class="text-muted">
                            {{ $pengajuan->rombel->nama ?? '-' }} &mdash;
                            {{ $pengajuan->semester->nama ?? '-' }} &mdash;
                            {{ $pengajuan->tahunPelajaran->nama ?? '-' }}
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.akademik.raport.pengajuan.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="info-label">Rombel / Kelas</div>
                        <div class="info-value">
                            Kelas {{ $pengajuan->rombel->tingkat ?? '' }} &mdash; {{ $pengajuan->rombel->nama ?? '-' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Wali Kelas</div>
                        <div class="info-value">{{ $pengajuan->rombel->wali_kelas ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Semester</div>
                        <div class="info-value">{{ $pengajuan->semester->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Tahun Pelajaran</div>
                        <div class="info-value">{{ $pengajuan->tahunPelajaran->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Diajukan Pada</div>
                        <div class="info-value">
                            {{ $pengajuan->diajukan_at ? $pengajuan->diajukan_at->format('d M Y H:i') : '—' }}
                        </div>
                    </div>
                </div>

                @if($pengajuan->status === 'ditolak' && $pengajuan->catatan_verifikasi)
                <div class="alert alert-danger mt-3 mb-0 d-flex align-items-start gap-2">
                    <i class="bi bi-x-circle-fill mt-1"></i>
                    <div>
                        <strong>Pengajuan Ditolak:</strong>
                        {{ $pengajuan->catatan_verifikasi }}
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Action Bar ── --}}
            <div class="card-footer bg-light d-flex gap-2">
                @if(in_array($pengajuan->status, ['draft', 'ditolak']))
                    <button class="btn btn-sm btn-outline-secondary" onclick="doRefreshNilai({{ $pengajuan->id }})">
                        <i class="bi bi-arrow-repeat me-1"></i>Refresh Nilai
                    </button>
                    <button class="btn btn-sm btn-success" onclick="doSubmit({{ $pengajuan->id }})">
                        <i class="bi bi-send me-1"></i>
                        {{ $pengajuan->status === 'ditolak' ? 'Ajukan Ulang' : 'Submit ke Kurikulum' }}
                    </button>
                @endif

                @if($pengajuan->status === 'diajukan')
                    <button class="btn btn-sm btn-warning" onclick="doTarik({{ $pengajuan->id }})">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Tarik Pengajuan
                    </button>
                @endif

                <a href="{{ route('admin.akademik.raport.pengajuan.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Accordion Nilai Per Siswa ─────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-1 text-primary"></i>Nilai Per Siswa</h6>
                @if($canEdit)
                <small class="text-muted">Input nilai manual diaktifkan. Simpan per siswa setelah selesai.</small>
                @else
                <small class="text-muted">Nilai hanya dapat diubah saat status draft dan fitur input manual diaktifkan.</small>
                @endif
            </div>
            <div class="card-body p-0">
                @if($nilaiPerSiswa->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada data siswa di rombel ini. Klik <strong>Refresh Nilai</strong> untuk memuat dari sumber.
                    </div>
                @else
                <div class="accordion" id="accordion-siswa">
                    @foreach($nilaiPerSiswa as $siswa)
                    @php $pid = $siswa->peserta_id; @endphp
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="head-{{ $pid }}">
                            <button class="accordion-button collapsed py-2" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $pid }}"
                                aria-expanded="false" aria-controls="collapse-{{ $pid }}">
                                <span class="me-3 text-muted" style="font-size:.8rem;min-width:2rem;">
                                    {{ $siswa->no_absen ?? '—' }}
                                </span>
                                <strong>{{ $siswa->nama }}</strong>
                                <span class="ms-auto me-3 badge bg-light text-dark border" style="font-size:.75rem;">
                                    {{ count($siswa->nilai_rows) }} Mapel
                                </span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $pid }}" class="accordion-collapse collapse"
                            aria-labelledby="head-{{ $pid }}" data-bs-parent="#accordion-siswa">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 tbl-nilai">
                                        <thead>
                                            <tr>
                                                <th width="4%" class="text-center">No</th>
                                                <th>Mata Pelajaran</th>
                                                <th width="8%" class="text-center">Nilai Harian</th>
                                                <th width="8%" class="text-center">UTS</th>
                                                <th width="8%" class="text-center">UAS</th>
                                                <th width="8%" class="text-center">Nilai Akhir</th>
                                                <th width="6%" class="text-center">Predikat</th>
                                                <th width="6%" class="text-center">Status KKM</th>
                                                <th>Catatan Guru</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-{{ $pid }}">
                                            @forelse($siswa->nilai_rows as $i => $n)
                                            <tr data-mapel="{{ $n->mata_pelajaran_id }}">
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td>{{ $n->mapel_nama }}</td>
                                                <td class="text-center">
                                                    @if($canEdit)
                                                    <input type="number" class="form-control form-control-sm text-center inp-nilai inp-harian"
                                                        min="0" max="100" step="0.01"
                                                        value="{{ $n->nilai_harian !== null ? number_format((float)$n->nilai_harian, 2, '.', '') : '' }}">
                                                    @else
                                                    {{ $n->nilai_harian !== null ? number_format((float)$n->nilai_harian, 2) : '—' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($canEdit)
                                                    <input type="number" class="form-control form-control-sm text-center inp-nilai inp-uts"
                                                        min="0" max="100" step="0.01"
                                                        value="{{ $n->nilai_uts !== null ? number_format((float)$n->nilai_uts, 2, '.', '') : '' }}">
                                                    @else
                                                    {{ $n->nilai_uts !== null ? number_format((float)$n->nilai_uts, 2) : '—' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($canEdit)
                                                    <input type="number" class="form-control form-control-sm text-center inp-nilai inp-uas"
                                                        min="0" max="100" step="0.01"
                                                        value="{{ $n->nilai_uas !== null ? number_format((float)$n->nilai_uas, 2, '.', '') : '' }}">
                                                    @else
                                                    {{ $n->nilai_uas !== null ? number_format((float)$n->nilai_uas, 2) : '—' }}
                                                    @endif
                                                </td>
                                                <td class="text-center na-cell">
                                                    @php
                                                        $na = $n->nilai_akhir !== null ? (float)$n->nilai_akhir : null;
                                                        $naClass = '';
                                                        if ($na !== null) {
                                                            $naClass = $na >= ($setting->kkm_default ?? 70) ? 'text-success' : 'text-danger';
                                                        }
                                                    @endphp
                                                    <span class="na-display {{ $naClass }}">
                                                        {{ $na !== null ? number_format($na, 2) : '—' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($n->predikat)
                                                    <span class="fw-bold predikat-{{ $n->predikat }}">{{ $n->predikat }}</span>
                                                    @else
                                                    <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($na !== null)
                                                        @if($na >= ($setting->kkm_default ?? 70))
                                                            <span class="badge bg-success-subtle text-success">Tuntas</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger">Belum</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($canEdit)
                                                    <input type="text" class="form-control form-control-sm inp-catatan"
                                                        maxlength="500"
                                                        value="{{ $n->catatan_guru ?? '' }}">
                                                    @else
                                                    <span class="text-muted" style="font-size:.82rem;">{{ $n->catatan_guru ?? '—' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    Belum ada data nilai. Klik Refresh Nilai untuk memuat.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($canEdit && count($siswa->nilai_rows) > 0)
                                <div class="p-2 text-end bg-light border-top">
                                    <button class="btn btn-sm btn-primary"
                                        onclick="saveSiswaNilai({{ $pid }}, {{ $pengajuan->id }})">
                                        <i class="bi bi-save me-1"></i>Simpan Nilai Siswa
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Rekap Absensi ─────────────────────────────────────────────────────── --}}
<div class="row g-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-1 text-success"></i>Rekap Absensi</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th>Nama Siswa</th>
                                <th width="6%" class="text-center">No Absen</th>
                                <th width="7%" class="text-center text-success">Hadir</th>
                                <th width="7%" class="text-center text-warning">Sakit</th>
                                <th width="7%" class="text-center text-info">Izin</th>
                                <th width="7%" class="text-center text-danger">Alpa</th>
                                <th width="8%" class="text-center">Total Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nilaiPerSiswa as $i => $siswa)
                            @php
                                $absensi    = $siswa->absensi_rekap;
                                $totalAbsen = ($absensi->sakit ?? 0) + ($absensi->izin ?? 0) + ($absensi->alpa ?? 0);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $siswa->nama }}</td>
                                <td class="text-center">{{ $siswa->no_absen ?? '—' }}</td>
                                <td class="text-center text-success fw-bold">{{ $absensi->hadir ?? 0 }}</td>
                                <td class="text-center text-warning fw-bold">{{ $absensi->sakit ?? 0 }}</td>
                                <td class="text-center text-info fw-bold">{{ $absensi->izin ?? 0 }}</td>
                                <td class="text-center text-danger fw-bold">{{ $absensi->alpa ?? 0 }}</td>
                                <td class="text-center fw-bold {{ $totalAbsen > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $totalAbsen }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">Belum ada data absensi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Submit ── --}}
<div class="modal fade" id="modal-submit-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-send me-1"></i>Ajukan ke Kurikulum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted">Setelah diajukan, nilai tidak dapat diubah sampai pengajuan ditarik kembali.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Pengajuan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="catatan-submit-detail" rows="3"
                        placeholder="Pesan untuk tim kurikulum...">{{ $pengajuan->catatan_pengajuan ?? '' }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-submit-detail">
                    <i class="bi bi-send me-1"></i>Ajukan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var _pengajuanId = {{ $pengajuan->id }};
var _csrfToken   = '{{ csrf_token() }}';

// ── Refresh Nilai ──────────────────────────────────────────────────────────
function doRefreshNilai(id) {
    Swal.fire({
        title: 'Refresh Data Nilai?',
        text: 'Data nilai akan diperbarui dari sumber (tabel nilai & absensi). Nilai manual yang belum disimpan akan ditimpa.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Refresh',
        cancelButtonText: 'Batal',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Memuat...', didOpen: function () { Swal.showLoading(); }, allowOutsideClick: false });

        $.post('/admin/akademik/raport/pengajuan/' + id + '/refresh-nilai', { _token: _csrfToken }, function (res) {
            Swal.fire({
                icon: res.status === 200 ? 'success' : 'error',
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
            }).then(function () {
                if (res.status === 200) location.reload();
            });
        }).fail(function (xhr) {
            Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal refresh nilai.' });
        });
    });
}

// ── Submit Pengajuan ───────────────────────────────────────────────────────
function doSubmit(id) {
    $('#modal-submit-detail').modal('show');
}

$('#btn-konfirmasi-submit-detail').on('click', function () {
    var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengajukan...');

    $.ajax({
        url: '/admin/akademik/raport/pengajuan/' + _pengajuanId + '/submit',
        type: 'POST',
        data: {
            _token: _csrfToken,
            catatan_pengajuan: $('#catatan-submit-detail').val(),
        },
        success: function (res) {
            $('#modal-submit-detail').modal('hide');
            Swal.fire({
                icon: res.status === 200 ? 'success' : 'error',
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
            }).then(function () {
                if (res.status === 200) location.reload();
            });
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal mengajukan.' });
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Ajukan');
        }
    });
});

// ── Tarik Pengajuan ────────────────────────────────────────────────────────
function doTarik(id) {
    Swal.fire({
        title: 'Tarik Pengajuan?',
        text: 'Pengajuan akan dikembalikan ke status draft.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tarik',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#f59e0b',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.post('/admin/akademik/raport/pengajuan/' + id + '/withdraw', { _token: _csrfToken }, function (res) {
            Swal.fire({
                icon: res.status === 200 ? 'success' : 'error',
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
            }).then(function () {
                if (res.status === 200) location.reload();
            });
        }).fail(function (xhr) {
            Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menarik pengajuan.' });
        });
    });
}

// ── Live hitung nilai akhir (manual input) ─────────────────────────────────
$(document).on('input', '.inp-nilai', function () {
    var $row = $(this).closest('tr');
    var h = parseFloat($row.find('.inp-harian').val());
    var u = parseFloat($row.find('.inp-uts').val());
    var a = parseFloat($row.find('.inp-uas').val());

    if (!isNaN(h) && !isNaN(u) && !isNaN(a)) {
        var na     = Math.round((h * 0.4 + u * 0.3 + a * 0.3) * 100) / 100;
        var kkm    = {{ $setting->kkm_default ?? 70 }};
        var cls    = na >= kkm ? 'text-success' : 'text-danger';
        $row.find('.na-display').text(na.toFixed(2)).removeClass('text-success text-danger').addClass(cls);
    } else {
        $row.find('.na-display').text('—').removeClass('text-success text-danger');
    }
});

// ── Simpan nilai satu siswa ────────────────────────────────────────────────
function saveSiswaNilai(pesertaId, pengajuanId) {
    var rows = [];

    $('#tbody-' + pesertaId + ' tr').each(function () {
        var mapelId = $(this).data('mapel');
        if (!mapelId) return;

        rows.push({
            mata_pelajaran_id: parseInt(mapelId),
            nilai_harian:      $(this).find('.inp-harian').val()  || null,
            nilai_uts:         $(this).find('.inp-uts').val()     || null,
            nilai_uas:         $(this).find('.inp-uas').val()     || null,
            nilai_akhir:       null,
            catatan_guru:      $(this).find('.inp-catatan').val() || null,
        });
    });

    if (!rows.length) {
        Swal.fire({ icon: 'info', text: 'Tidak ada data nilai untuk disimpan.', timer: 2000, showConfirmButton: false });
        return;
    }

    $.ajax({
        url: '/admin/akademik/raport/pengajuan/' + pengajuanId + '/nilai',
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            _token:     _csrfToken,
            peserta_id: pesertaId,
            rows:       rows,
        }),
        success: function (res) {
            Swal.fire({
                icon: res.status === 200 ? 'success' : 'error',
                text: res.message,
                timer: 2500,
                showConfirmButton: false,
            });
        },
        error: function (xhr) {
            var msg = xhr.responseJSON?.message ?? 'Gagal menyimpan.';
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            Swal.fire({ icon: 'error', html: msg });
        }
    });
}
</script>
@endpush
