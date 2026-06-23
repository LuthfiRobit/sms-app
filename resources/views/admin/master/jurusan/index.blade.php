@extends('admin.layouts.app')
@section('title', 'Manajemen Jurusan / Program Studi')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-diagram-3 me-2"></i>Jurusan / Program Studi</h5>
                        <small class="text-muted">Kelola program studi per lembaga untuk keperluan kuota PPDB.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.jurusan.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Jurusan
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="jurusan-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th width="18%">Lembaga</th>
                                <th width="12%">Kode</th>
                                <th>Nama Jurusan</th>
                                <th width="12%">Status</th>
                                <th width="8%">Urutan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah ── --}}
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.master.jurusan.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Jurusan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" name="kode"
                                   placeholder="Contoh: IPA, TKJ" maxlength="30" required>
                            <div class="form-text">Singkatan unik, maks 30 karakter</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama"
                                   placeholder="Contoh: Ilmu Pengetahuan Alam" maxlength="255" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2"
                                      placeholder="Keterangan singkat (opsional)" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urutan Tampil</label>
                            <input type="number" class="form-control" name="urutan" value="0" min="0">
                        </div>
                    </div>
                    <div id="tambah-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-tambah-submit">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="edit-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" name="kode" id="edit-kode" maxlength="30" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="edit-nama" maxlength="255" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urutan Tampil</label>
                            <input type="number" class="form-control" name="urutan" id="edit-urutan" min="0">
                        </div>
                    </div>
                    <div id="edit-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-edit-submit">
                        <i class="bi bi-check-lg me-1"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var table = $('#jurusan-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.master.jurusan.list') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action',      orderable: false, searchable: false },
            { data: 'lembaga_nama' },
            { data: 'kode' },
            { data: 'nama' },
            { data: 'status_badge', orderable: false },
            { data: 'urutan' },
        ],
        language: {
            sEmptyTable: 'Tidak ada data jurusan',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst:'Pertama', sPrevious:'Sebelumnya', sNext:'Selanjutnya', sLast:'Terakhir' },
        },
    });

    // ── Tambah ─────────────────────────────────────────────────────────────
    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn-tambah-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    $('#modal-tambah').modal('hide');
                    $('#form-tambah')[0].reset();
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                } else {
                    showAlert('#tambah-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert('#tambah-alert', 'danger', msg);
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan');
            }
        });
    });

    // ── Edit ───────────────────────────────────────────────────────────────
    window.editJurusan = function (id) {
        $.get('{{ url('admin/master/jurusan') }}/' + id, function (res) {
            if (res.status === 'success') {
                var d = res.data;
                $('#form-edit').attr('action', '{{ url('admin/master/jurusan') }}/' + id);
                $('#edit-lembaga_id').val(d.lembaga_id);
                $('#edit-kode').val(d.kode);
                $('#edit-nama').val(d.nama);
                $('#edit-deskripsi').val(d.deskripsi);
                $('#edit-status').val(d.status);
                $('#edit-urutan').val(d.urutan);
                $('#edit-alert').addClass('d-none');
                $('#modal-edit').modal('show');
            }
        });
    };

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn-edit-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    $('#modal-edit').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                } else {
                    showAlert('#edit-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert('#edit-alert', 'danger', msg);
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Perbarui');
            }
        });
    });

    // ── Hapus ──────────────────────────────────────────────────────────────
    window.hapusJurusan = function (id, nama) {
        Swal.fire({
            title: 'Hapus Jurusan?',
            text: 'Jurusan "' + nama + '" akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/master/jurusan') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: res.status === 'success' ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menghapus.' });
                }
            });
        });
    };

    function showAlert(selector, type, msg) {
        $(selector).removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type).html(msg);
    }
});
</script>
@endpush
