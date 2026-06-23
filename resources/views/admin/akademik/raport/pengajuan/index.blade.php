@extends('admin.layouts.app')
@section('title', 'Pengajuan Raport')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-journal-bookmark-fill me-2"></i>Pengajuan Raport</h5>
                        <small class="text-muted">Kelola pengajuan raport per kelas, semester, dan tahun pelajaran.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.raport.pengajuan.index'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-buat">
                            <i class="bi bi-plus-lg me-1"></i>Buat Pengajuan
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Bar --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-rombel">
                            <option value="">-- Semua Rombel --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}">Kelas {{ $r->tingkat }} - {{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filter-semester">
                            <option value="">-- Semua Semester --</option>
                            @foreach($semesterList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
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
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">-- Semua Status --</option>
                            <option value="draft">Draft</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="diverifikasi">Diverifikasi</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="pengajuan-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="14%">Aksi</th>
                                <th>Rombel</th>
                                <th width="14%">Wali Kelas</th>
                                <th width="10%">Semester</th>
                                <th width="10%">Tahun Pelajaran</th>
                                <th width="9%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Buat Pengajuan ── --}}
<div class="modal fade" id="modal-buat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-buat">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-journal-plus me-1"></i>Buat Pengajuan Raport</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="buat-lembaga" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel / Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" name="rombel_id" id="buat-rombel" required>
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($rombelList as $r)
                                    <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                        Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester_id" id="buat-semester" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach($semesterList as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-buat">
                        <i class="bi bi-save me-1"></i>Buat Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Submit Pengajuan ── --}}
<div class="modal fade" id="modal-submit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-send me-1"></i>Ajukan ke Kurikulum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted">Pengajuan yang sudah diajukan tidak dapat diubah nilainya. Pastikan semua data sudah benar.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Pengajuan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="catatan-pengajuan" rows="3"
                        placeholder="Tambahkan catatan untuk tim kurikulum..."></textarea>
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
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── DataTable ──────────────────────────────────────────────────────────
    var table = $('#pengajuan-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.raport.pengajuan.list') }}',
            data: function (d) {
                d.rombel_id          = $('#filter-rombel').val();
                d.semester_id        = $('#filter-semester').val();
                d.tahun_pelajaran_id = $('#filter-tahun').val();
                d.status             = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, className: 'text-center' },
            { data: 'action',        name: 'action',        orderable: false, searchable: false },
            { data: 'rombel_nama',   name: 'rombel_nama' },
            { data: 'wali_kelas',    name: 'wali_kelas',   orderable: false },
            { data: 'semester_nama', name: 'semester_nama' },
            { data: 'tahun_nama',    name: 'tahun_nama' },
            { data: 'status_badge',  name: 'status_badge',  orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[0, 'asc']],
        language: { processing: '<i class="bi bi-hourglass-split me-1"></i>Memuat data...', zeroRecords: 'Tidak ada pengajuan ditemukan.' },
        pageLength: 15,
    });

    // ── Filter ─────────────────────────────────────────────────────────────
    $('#btn-filter').on('click', function () { table.ajax.reload(); });

    // ── Filter rombel by lembaga saat modal buka ───────────────────────────
    $('#buat-lembaga').on('change', function () {
        var lembagaId = $(this).val();
        $('#buat-rombel option[data-lembaga]').each(function () {
            var show = !lembagaId || $(this).data('lembaga') == lembagaId;
            $(this).prop('hidden', !show);
            if (!show && $(this).is(':selected')) $('#buat-rombel').val('');
        });
    }).trigger('change');

    // ── Store Pengajuan ────────────────────────────────────────────────────
    $('#form-buat').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn-simpan-buat').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

        $.ajax({
            url: '{{ route('admin.akademik.raport.pengajuan.store') }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:              '{{ csrf_token() }}',
                lembaga_id:          $('#buat-lembaga').val(),
                rombel_id:           $('#buat-rombel').val(),
                semester_id:         $('#buat-semester').val(),
                tahun_pelajaran_id:  $('#buat-tahun').val(),
            }),
            success: function (res) {
                if (res.status === 200) {
                    $('#modal-buat').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', text: res.message, timer: 2500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal membuat pengajuan.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Buat Pengajuan');
            }
        });
    });

    // ── Submit Pengajuan ───────────────────────────────────────────────────
    var _submitId = null;

    window.submitPengajuan = function (id) {
        _submitId = id;
        $('#catatan-pengajuan').val('');
        $('#modal-submit').modal('show');
    };

    $('#btn-konfirmasi-submit').on('click', function () {
        if (!_submitId) return;
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengajukan...');

        $.ajax({
            url: '/admin/akademik/raport/pengajuan/' + _submitId + '/submit',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                catatan_pengajuan: $('#catatan-pengajuan').val(),
            },
            success: function (res) {
                $('#modal-submit').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2500, showConfirmButton: false });
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
    window.tarikPengajuan = function (id) {
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
            $.post('/admin/akademik/raport/pengajuan/' + id + '/withdraw', { _token: '{{ csrf_token() }}' }, function (res) {
                table.ajax.reload();
                Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2500, showConfirmButton: false });
            });
        });
    };

    // ── Hapus Pengajuan ────────────────────────────────────────────────────
    window.hapusPengajuan = function (id) {
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: 'Data pengajuan ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '/admin/akademik/raport/pengajuan/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2500, showConfirmButton: false });
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
