@extends('admin.layouts.app')
@section('title', 'Program Kerja')

@push('styles')
<style>
    .progress { min-width: 90px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary">
                            <i class="bi bi-briefcase-fill me-2"></i>Program Kerja
                        </h5>
                        <small class="text-muted">Kelola program kerja per bidang dan tahun pelajaran.</small>
                    </div>
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-buat">
                            <i class="bi bi-plus-lg me-1"></i>Buat Program Kerja
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Filter Bar --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-lembaga">
                            <option value="">-- Semua Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" {{ $activeLembagaId == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filter-tahun">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filter-bidang">
                            <option value="">-- Semua Bidang --</option>
                            @foreach($bidangConfig as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">-- Semua Status --</option>
                            <option value="draft">Draft</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="diverifikasi">Diverifikasi</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="pk-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="12%">Aksi</th>
                                <th>Nama Program</th>
                                <th width="13%">Bidang</th>
                                <th width="8%">Status</th>
                                <th width="14%">Progress</th>
                                <th width="11%">Anggaran</th>
                                <th width="10%">Tahun</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Buat Program Kerja ─────────────────────────────────────────── --}}
<div class="modal fade" id="modal-buat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-buat">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-briefcase me-1"></i>Buat Program Kerja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="buat-lembaga" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" {{ $activeLembagaId == $l->id ? 'selected' : '' }}>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="buat-tahun" required>
                                <option value="">-- Pilih Tahun --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                        {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bidang <span class="text-danger">*</span></label>
                            <select class="form-select" name="bidang" id="buat-bidang" required>
                                <option value="">-- Pilih Bidang --</option>
                                @foreach($bidangConfig as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Program <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_program" id="buat-nama" required
                                   placeholder="Contoh: Program Penguatan Ekstrakurikuler">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tujuan <small class="text-muted fw-normal">(opsional)</small></label>
                            <textarea class="form-control" name="tujuan" id="buat-tujuan" rows="2"
                                      placeholder="Tujuan program kerja..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi <small class="text-muted fw-normal">(opsional)</small></label>
                            <textarea class="form-control" name="deskripsi" id="buat-deskripsi" rows="2"
                                      placeholder="Deskripsi singkat program kerja..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-buat">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Submit ke Verifikasi ──────────────────────────────────────── --}}
<div class="modal fade" id="modal-submit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-send me-1"></i>Ajukan ke Verifikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Program kerja yang telah diajukan tidak dapat diedit hingga proses selesai atau ditolak.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="submit-catatan" rows="3" placeholder="Tambahkan catatan untuk reviewer..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-submit">
                    <i class="bi bi-send me-1"></i>Ajukan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Verifikasi ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-verifikasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-check-circle me-1"></i>Verifikasi Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Program kerja yang diverifikasi akan diteruskan ke tahap persetujuan.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Verifikasi <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="verifikasi-catatan" rows="3" placeholder="Catatan hasil verifikasi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info text-white" id="btn-konfirmasi-verifikasi">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Approval ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-approval" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-patch-check me-1"></i>Setujui Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Program kerja yang disetujui akan siap untuk dilaksanakan.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Persetujuan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="approval-catatan" rows="3" placeholder="Catatan persetujuan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-approval">
                    <i class="bi bi-patch-check me-1"></i>Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tolak ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-tolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bi bi-x-circle me-1"></i>Tolak Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tolak-catatan" rows="3"
                              placeholder="Tuliskan alasan penolakan..."></textarea>
                    <div class="form-text text-danger d-none" id="tolak-catatan-error">Alasan penolakan wajib diisi.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-konfirmasi-tolak">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── DataTable ──────────────────────────────────────────────────────────
    var table = $('#pk-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.program-kerja.list') }}',
            data: function (d) {
                d.lembaga_id          = $('#filter-lembaga').val();
                d.tahun_pelajaran_id  = $('#filter-tahun').val();
                d.bidang              = $('#filter-bidang').val();
                d.status              = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',  orderable: false, searchable: false, className: 'text-center' },
            { data: 'action',         name: 'action',       orderable: false, searchable: false },
            { data: 'nama_program',   name: 'nama_program' },
            { data: 'bidang_label',   name: 'bidang',       searchable: false },
            { data: 'status_badge',   name: 'status',       orderable: false, searchable: false, className: 'text-center' },
            { data: 'progress',       name: 'progress',     orderable: false, searchable: false },
            { data: 'anggaran_fmt',   name: 'anggaran_fmt', orderable: false, searchable: false, className: 'text-end' },
            { data: 'tahun_nama',     name: 'tahun_nama',   orderable: false },
        ],
        order: [[0, 'asc']],
        language: {
            processing:  '<i class="bi bi-hourglass-split me-1"></i>Memuat data...',
            zeroRecords: 'Tidak ada program kerja ditemukan.'
        },
        pageLength: 15,
    });

    // ── Filter ─────────────────────────────────────────────────────────────
    $('#btn-filter').on('click', function () { table.ajax.reload(); });
    $('#filter-lembaga, #filter-tahun, #filter-bidang, #filter-status').on('change', function () {
        table.ajax.reload();
    });

    // ── Store Program Kerja ────────────────────────────────────────────────
    $('#form-buat').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn-simpan-buat').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

        $.ajax({
            url:         '{{ route('admin.program-kerja.store') }}',
            type:        'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:              '{{ csrf_token() }}',
                lembaga_id:          $('#buat-lembaga').val(),
                tahun_pelajaran_id:  $('#buat-tahun').val(),
                bidang:              $('#buat-bidang').val(),
                nama_program:        $('#buat-nama').val(),
                tujuan:              $('#buat-tujuan').val(),
                deskripsi:           $('#buat-deskripsi').val(),
            }),
            success: function (res) {
                if (res.status === 200) {
                    $('#modal-buat').modal('hide');
                    table.ajax.reload();
                    $('#modal-buat').one('hidden.bs.modal', function () {
                        Swal.fire({ icon: 'success', text: res.message, timer: 2000, showConfirmButton: false });
                    });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal membuat program kerja.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
            }
        });
    });

    // Reset form saat modal dibuka
    $('#modal-buat').on('show.bs.modal', function () {
        $('#form-buat')[0].reset();
        $('#buat-lembaga').val('{{ $activeLembagaId }}');
        @foreach($tahunList as $t)
            @if($t->status === 'aktif')
            $('#buat-tahun').val('{{ $t->id }}');
            @endif
        @endforeach
    });

    // ── Submit ke Verifikasi ───────────────────────────────────────────────
    var _actionId = null;

    window.submitProgram = function (id) {
        _actionId = id;
        $('#submit-catatan').val('');
        $('#modal-submit').modal('show');
    };

    $('#btn-konfirmasi-submit').on('click', function () {
        if (!_actionId) return;
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + _actionId + '/submit',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#submit-catatan').val() },
            success: function (res) {
                $('#modal-submit').modal('hide');
                table.ajax.reload();
                $('#modal-submit').one('hidden.bs.modal', function () {
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal mengajukan.' });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Ajukan Sekarang');
            }
        });
    });

    // ── Tarik Pengajuan ────────────────────────────────────────────────────
    window.tarikProgram = function (id) {
        Swal.fire({
            title: 'Tarik Pengajuan?',
            text:  'Program kerja akan dikembalikan ke status draft.',
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Ya, Tarik',
            cancelButtonText:  'Batal',
            confirmButtonColor: '#f59e0b',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post('{{ url('admin/program-kerja') }}/' + id + '/withdraw', { _token: '{{ csrf_token() }}' }, function (res) {
                table.ajax.reload();
                Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
            });
        });
    };

    // ── Verifikasi ─────────────────────────────────────────────────────────
    window.verifikasiProgram = function (id) {
        _actionId = id;
        $('#verifikasi-catatan').val('');
        $('#modal-verifikasi').modal('show');
    };

    $('#btn-konfirmasi-verifikasi').on('click', function () {
        if (!_actionId) return;
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + _actionId + '/verifikasi',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#verifikasi-catatan').val() },
            success: function (res) {
                $('#modal-verifikasi').modal('hide');
                table.ajax.reload();
                $('#modal-verifikasi').one('hidden.bs.modal', function () {
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal memverifikasi.' });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Verifikasi');
            }
        });
    });

    // ── Approval ───────────────────────────────────────────────────────────
    window.approvalProgram = function (id) {
        _actionId = id;
        $('#approval-catatan').val('');
        $('#modal-approval').modal('show');
    };

    $('#btn-konfirmasi-approval').on('click', function () {
        if (!_actionId) return;
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + _actionId + '/approval',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#approval-catatan').val() },
            success: function (res) {
                $('#modal-approval').modal('hide');
                table.ajax.reload();
                $('#modal-approval').one('hidden.bs.modal', function () {
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menyetujui.' });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-patch-check me-1"></i>Setujui');
            }
        });
    });

    // ── Tolak ──────────────────────────────────────────────────────────────
    window.tolakProgram = function (id) {
        _actionId = id;
        $('#tolak-catatan').val('');
        $('#tolak-catatan-error').addClass('d-none');
        $('#modal-tolak').modal('show');
    };

    $('#btn-konfirmasi-tolak').on('click', function () {
        if (!_actionId) return;
        var catatan = $('#tolak-catatan').val().trim();
        if (!catatan) {
            $('#tolak-catatan-error').removeClass('d-none');
            return;
        }
        $('#tolak-catatan-error').addClass('d-none');
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + _actionId + '/tolak',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: catatan },
            success: function (res) {
                $('#modal-tolak').modal('hide');
                table.ajax.reload();
                $('#modal-tolak').one('hidden.bs.modal', function () {
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menolak.' });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Tolak');
            }
        });
    });

    // ── Hapus ──────────────────────────────────────────────────────────────
    window.hapusProgram = function (id) {
        Swal.fire({
            title: 'Hapus Program Kerja?',
            text:  'Data program kerja ini akan dihapus permanen.',
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText:  'Batal',
            confirmButtonColor: '#ef4444',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url:  '{{ url('admin/program-kerja') }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menghapus.' });
                }
            });
        });
    };
});
</script>
@endpush
