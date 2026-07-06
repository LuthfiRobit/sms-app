@extends('admin.layouts.app')
@section('title', 'Model Pembelajaran')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-diagram-2 me-2"></i>Model Pembelajaran</h5>
                    <small class="text-muted">Master model pembelajaran + tahapan sintaksnya — dipakai bagian "Inti" pada RPP terstruktur.</small>
                </div>
                <button class="btn btn-sm btn-primary" id="btn-tambah"><i class="bi bi-plus-lg me-1"></i>Tambah Model</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="model-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="12%">Aksi</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th width="10%">Jml Sintaks</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah/Edit Model --}}
<div class="modal fade" id="modal-model" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-model">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-model-title">Tambah Model Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="model-id">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" id="model-nama" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="model-deskripsi" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Urutan</label>
                            <input type="number" class="form-control" id="model-urutan" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="model-status">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Kelola Sintaks --}}
<div class="modal fade" id="modal-sintaks" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kelola Sintaks — <span id="sintaks-model-nama"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sintaks-model-id">
                <table class="table table-sm" id="sintaks-table">
                    <thead>
                        <tr>
                            <th>Nama Sintaks</th>
                            <th width="160">Fase</th>
                            <th width="70">Urutan</th>
                            <th width="40"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-info" id="btn-tambah-sintaks-row"><i class="bi bi-plus"></i> Tambah Baris</button>
                <div class="form-text">Fase (Memahami/Mengaplikasi/Merefleksi) sudah tetap dari kerangka Pembelajaran Mendalam — pilih yang sesuai untuk tiap tahapan sintaks model ini.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-sintaks">Simpan Semua</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var sintaksRowCount = 0;

function sintaksRowHtml(s) {
    s = s || {};
    var id = s.id || '';
    var nama = s.nama_sintaks || '';
    var fase = s.meta_fase || 'Memahami';
    var urutan = s.urutan != null ? s.urutan : (sintaksRowCount + 1);
    return '<tr class="sintaks-row" data-id="' + id + '">' +
        '<td><input type="text" class="form-control form-control-sm sintaks-nama" value="' + nama + '" required></td>' +
        '<td><select class="form-select form-select-sm sintaks-fase">' +
            '<option value="Memahami"' + (fase === 'Memahami' ? ' selected' : '') + '>Memahami</option>' +
            '<option value="Mengaplikasi"' + (fase === 'Mengaplikasi' ? ' selected' : '') + '>Mengaplikasi</option>' +
            '<option value="Merefleksi"' + (fase === 'Merefleksi' ? ' selected' : '') + '>Merefleksi</option>' +
        '</select></td>' +
        '<td><input type="number" class="form-control form-control-sm sintaks-urutan" value="' + urutan + '"></td>' +
        '<td><button type="button" class="btn btn-sm btn-danger btn-remove-sintaks"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';
}

$(document).ready(function () {
    var table = $('#model-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.master.model-pembelajaran.list') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
            { data: 'nama' },
            { data: 'deskripsi', defaultContent: '-' },
            { data: 'sintaks_count' },
            { data: 'status_badge', orderable: false },
        ],
    });

    $('#btn-tambah').on('click', function () {
        $('#form-model')[0].reset();
        $('#model-id').val('');
        $('#modal-model-title').text('Tambah Model Pembelajaran');
        new bootstrap.Modal('#modal-model').show();
    });

    window.editModel = function (id) {
        $.get('{{ url('admin/master/model-pembelajaran') }}/' + id, function (res) {
            if (!res.status || res.status !== 200) return toastr.error(res.message);
            var d = res.data;
            $('#model-id').val(d.id);
            $('#model-nama').val(d.nama);
            $('#model-deskripsi').val(d.deskripsi);
            $('#model-urutan').val(d.urutan);
            $('#model-status').val(d.status);
            $('#modal-model-title').text('Edit Model Pembelajaran');
            new bootstrap.Modal('#modal-model').show();
        });
    };

    $('#form-model').on('submit', function (e) {
        e.preventDefault();
        var id = $('#model-id').val();
        var data = {
            _token: '{{ csrf_token() }}',
            nama: $('#model-nama').val(),
            deskripsi: $('#model-deskripsi').val(),
            urutan: $('#model-urutan').val(),
            status: $('#model-status').val(),
        };
        if (id) data._method = 'PUT';

        $.ajax({
            url: id ? '{{ url('admin/master/model-pembelajaran') }}/' + id : '{{ route('admin.master.model-pembelajaran.store') }}',
            type: 'POST',
            data: data,
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('modal-model'))?.hide();
                toastr.success(res.message);
                table.ajax.reload();
            },
            error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menyimpan.'); },
        });
    });

    window.hapusModel = function (id) {
        Swal.fire({ title: 'Hapus Model Pembelajaran?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', confirmButtonColor: '#d33' })
            .then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: '{{ url('admin/master/model-pembelajaran') }}/' + id, type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) { toastr.success(res.message); table.ajax.reload(); },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menghapus.'); },
                });
            });
    };

    window.kelolaSintaks = function (id) {
        $.get('{{ url('admin/master/model-pembelajaran') }}/' + id, function (res) {
            if (!res.status || res.status !== 200) return toastr.error(res.message);
            $('#sintaks-model-id').val(id);
            $('#sintaks-model-nama').text(res.data.nama);
        });

        $.get('{{ url('admin/master/model-pembelajaran') }}/' + id + '/sintaks', function (res) {
            var rows = (res.data || []);
            sintaksRowCount = rows.length;
            var html = '';
            rows.forEach(function (s) { html += sintaksRowHtml(s); });
            $('#sintaks-table tbody').html(html);
            new bootstrap.Modal('#modal-sintaks').show();
        });
    };

    $('#btn-tambah-sintaks-row').on('click', function () {
        sintaksRowCount++;
        $('#sintaks-table tbody').append(sintaksRowHtml({ urutan: sintaksRowCount }));
    });

    $(document).on('click', '.btn-remove-sintaks', function () {
        $(this).closest('tr').remove();
    });

    $('#btn-simpan-sintaks').on('click', function () {
        var modelId = $('#sintaks-model-id').val();
        var sintaks = [];
        $('#sintaks-table tbody tr').each(function () {
            var $tr = $(this);
            sintaks.push({
                id: $tr.data('id') || null,
                nama_sintaks: $tr.find('.sintaks-nama').val(),
                meta_fase: $tr.find('.sintaks-fase').val(),
                urutan: $tr.find('.sintaks-urutan').val(),
            });
        });

        $.ajax({
            url: '{{ url('admin/master/model-pembelajaran') }}/' + modelId + '/sintaks',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', sintaks: sintaks },
            success: function (res) {
                toastr.success(res.message);
                bootstrap.Modal.getInstance(document.getElementById('modal-sintaks'))?.hide();
                table.ajax.reload();
            },
            error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menyimpan sintaks.'); },
        });
    });
});
</script>
@endpush
