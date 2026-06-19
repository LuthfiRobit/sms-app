@extends('admin.layouts.app')
@section('title', 'Data Lembaga')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Data Lembaga LP Ma'arif NU Kraksaan (Marifat)</h5>
                @if(auth()->user()->hasPermissionTo('admin.master.lembaga.store'))
                <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-lembaga">
                    <i class="bi bi-plus-lg"></i> Tambah Lembaga
                </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="lembaga-table" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Lembaga</th>
                                <th>Jenis</th>
                                <th>NPSN</th>
                                <th>Kepala Sekolah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Create/Edit --}}
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Tambah Lembaga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('admin.master.lembaga._form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-simpan">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Lembaga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const URLS = {
    list:   '{{ route("admin.master.lembaga.list") }}',
    store:  '{{ route("admin.master.lembaga.store") }}',
    show:   (id) => `/admin/master/lembaga/${id}`,
    update: (id) => `/admin/master/lembaga/${id}`,
    toggle: (id) => `/admin/master/lembaga/${id}/toggle-status`,
    destroy:(id) => `/admin/master/lembaga/${id}`,
};

let editId = null;
const table = $('#lembaga-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: URLS.list, type: 'GET', data: { _token: '{{ csrf_token() }}' } },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'kode' },
        { data: 'nama' },
        { data: 'jenis_badge', orderable: false },
        { data: 'npsn', defaultContent: '-' },
        { data: 'kepala_sekolah', defaultContent: '-' },
        { data: 'status', orderable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
});

// Open create modal
$('#btn-tambah-lembaga').on('click', function () {
    editId = null;
    $('#formModalLabel').text('Tambah Lembaga');
    $('#lembaga-form')[0].reset();
    $('#lembaga-id').val('');
    new bootstrap.Modal('#formModal').show();
});

// Open edit modal
$(document).on('click', '.btn-edit', function () {
    editId = $(this).data('id');
    $.get(URLS.show(editId), function (res) {
        if (!res.status) return toastr.error(res.message);
        const d = res.data;
        $('#formModalLabel').text('Edit Lembaga');
        $('#lembaga-id').val(d.id);
        $('#f-kode').val(d.kode);
        $('#f-nama').val(d.nama);
        $('#f-npsn').val(d.npsn);
        $('#f-jenis').val(d.jenis);
        $('#f-alamat').val(d.alamat);
        $('#f-telepon').val(d.telepon);
        $('#f-email').val(d.email);
        $('#f-kepala').val(d.kepala_sekolah);
        $('#f-status').val(d.status);
        $('#f-urutan').val(d.urutan);
        new bootstrap.Modal('#formModal').show();
    });
});

// Open detail modal
$(document).on('click', '.btn-show', function () {
    const id = $(this).data('id');
    $('#detail-body').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    new bootstrap.Modal('#detailModal').show();
    $.get(URLS.show(id), function (res) {
        if (!res.status) return $('#detail-body').html('<p class="text-danger">Data tidak ditemukan</p>');
        const d = res.data;
        $('#detail-body').html(`
            <table class="table table-bordered table-sm">
                <tr><th>Kode</th><td>${d.kode}</td></tr>
                <tr><th>Nama</th><td>${d.nama}</td></tr>
                <tr><th>Jenis</th><td>${d.jenis}</td></tr>
                <tr><th>NPSN</th><td>${d.npsn ?? '-'}</td></tr>
                <tr><th>Alamat</th><td>${d.alamat ?? '-'}</td></tr>
                <tr><th>Telepon</th><td>${d.telepon ?? '-'}</td></tr>
                <tr><th>Email</th><td>${d.email ?? '-'}</td></tr>
                <tr><th>Kepala Sekolah</th><td>${d.kepala_sekolah ?? '-'}</td></tr>
                <tr><th>Status</th><td>${d.status}</td></tr>
            </table>
        `);
    });
});

// Save (create or update)
$('#btn-simpan').on('click', function () {
    const id = $('#lembaga-id').val();
    const formData = new FormData($('#lembaga-form')[0]);
    const url = id ? URLS.update(id) : URLS.store;
    if (id) formData.append('_method', 'PUT');

    $.ajax({
        url, type: 'POST', data: formData,
        processData: false, contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            if (!res.status) return toastr.error(res.message);
            toastr.success(res.message);
            bootstrap.Modal.getInstance('#formModal').hide();
            table.ajax.reload();
        },
        error: function (xhr) {
            const errs = xhr.responseJSON?.errors;
            if (errs) {
                toastr.error(Object.values(errs).map(e => e[0]).join('<br>'));
            } else {
                toastr.error('Terjadi kesalahan');
            }
        },
    });
});

// Toggle status
$(document).on('click', '.btn-toggle-status', function () {
    const id = $(this).data('id');
    $.post(URLS.toggle(id), { _token: '{{ csrf_token() }}' }, function (res) {
        if (!res.status) return toastr.error(res.message);
        toastr.success(res.message);
        table.ajax.reload(null, false);
    });
});

// Delete
$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Hapus Lembaga?', text: 'Data tidak dapat dipulihkan.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: URLS.destroy(id), type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (!res.status) return toastr.error(res.message);
                toastr.success(res.message);
                table.ajax.reload();
            },
        });
    });
});
</script>
@endpush
