@extends('admin.layouts.app')
@section('title', 'Kalender Libur')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-calendar-x me-2"></i>Kalender Libur</h5>
                        <small class="text-muted">Hari libur nasional & khusus lembaga — dipakai otomatis oleh sistem absensi guru (auto-alpa & reminder).</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.kalender-libur.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-light-primary me-1" data-bs-toggle="modal" data-bs-target="#modal-tambah-rentang">
                            <i class="bi bi-calendar-range me-1"></i>Tambah Rentang
                        </button>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-body border-bottom bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold">Lembaga</label>
                        <select class="form-select form-select-sm" id="filter-lembaga" {{ $isSuperAdmin ? '' : 'disabled' }}>
                            @if($isSuperAdmin)
                            <option value="" @selected(!$activeLembagaId)>-- Semua Lembaga --</option>
                            @endif
                            @foreach($lembagaList as $l)
                            <option value="{{ $l->id }}" @selected($activeLembagaId == $l->id)>[{{ $l->kode }}] {{ $l->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold">Tahun</label>
                        <input type="number" class="form-control form-control-sm" id="filter-tahun" placeholder="2026">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-light" id="btn-reset-filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="kalender-libur-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Tanggal</th>
                                <th>Keterangan</th>
                                <th width="20%">Lembaga</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah (satu tanggal) ── --}}
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Hari Libur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="keterangan" maxlength="150" placeholder="Cth: Libur Hari Raya Idul Fitri" required>
                    </div>
                    @if($isSuperAdmin)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lembaga</label>
                        <select class="form-select" name="lembaga_id">
                            <option value="">-- Semua Lembaga (Nasional) --</option>
                            @foreach($lembagaList as $l)
                            <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Tambah Rentang ── --}}
<div class="modal fade" id="modal-tambah-rentang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah-rentang">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-calendar-range me-1"></i>Tambah Rentang Tanggal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Akhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_akhir" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Keterangan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="keterangan" maxlength="150" placeholder="Cth: Libur Hari Raya Idul Fitri" required>
                        </div>
                        @if($isSuperAdmin)
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lembaga</label>
                            <select class="form-select" name="lembaga_id">
                                <option value="">-- Semua Lembaga (Nasional) --</option>
                                @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-12">
                            <small class="text-muted">Tanggal yang sudah terdaftar sebelumnya akan dilewati otomatis. Maksimal rentang 92 hari.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal" id="edit-tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="keterangan" id="edit-keterangan" maxlength="150" required>
                    </div>
                    @if($isSuperAdmin)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lembaga</label>
                        <select class="form-select" name="lembaga_id" id="edit-lembaga_id">
                            <option value="">-- Semua Lembaga (Nasional) --</option>
                            @foreach($lembagaList as $l)
                            <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#kalender-libur-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.master.kalender-libur.list') }}',
            data: function (d) {
                d.lembaga_id = $('#filter-lembaga').val();
                d.tahun = $('#filter-tahun').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'tanggal_fmt' },
            { data: 'keterangan' },
            { data: 'lembaga_nama' },
            { data: 'action', orderable: false, searchable: false },
        ],
        language: { url: '/assets/datatables-id.json' },
    });

    $('#filter-lembaga, #filter-tahun').on('change', function () { table.ajax.reload(); });
    $('#btn-reset-filter').on('click', function () {
        $('#filter-tahun').val('');
        table.ajax.reload();
    });

    function handleAjaxError(xhr) {
        const errors = xhr.responseJSON?.errors;
        const msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
        Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
    }

    // ── Tambah ───────────────────────────────────────────────
    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('admin.master.kalender-libur.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modal-tambah').modal('hide');
                $('#form-tambah')[0].reset();
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            },
            error: handleAjaxError,
        });
    });

    // ── Tambah Rentang ───────────────────────────────────────
    $('#form-tambah-rentang').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('admin.master.kalender-libur.rentang') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modal-tambah-rentang').modal('hide');
                $('#form-tambah-rentang')[0].reset();
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2500, showConfirmButton: false });
            },
            error: handleAjaxError,
        });
    });

    // ── Edit ─────────────────────────────────────────────────
    window.editLibur = function (id) {
        $.get(`{{ url('admin/master/kalender-libur') }}/${id}`, function (res) {
            const d = res.data;
            $('#form-edit').attr('action', `{{ url('admin/master/kalender-libur') }}/${id}`);
            $('#edit-tanggal').val(d.tanggal?.substring(0, 10));
            $('#edit-keterangan').val(d.keterangan);
            $('#edit-lembaga_id').val(d.lembaga_id ?? '');
            $('#modal-edit').modal('show');
        });
    };

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                $('#modal-edit').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            },
            error: handleAjaxError,
        });
    });

    // ── Hapus ─────────────────────────────────────────────────
    window.hapusLibur = function (id) {
        Swal.fire({
            title: 'Hapus Hari Libur?',
            text: 'Data ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `{{ url('admin/master/kalender-libur') }}/${id}`,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                },
                error: handleAjaxError,
            });
        });
    };
});
</script>
@endpush
