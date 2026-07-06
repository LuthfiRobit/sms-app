@extends('admin.layouts.app')
@section('title', 'RPP')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i>RPP</h5>
                        <small class="text-muted">Rencana Pelaksanaan Pembelajaran terstruktur — isi poin-poinnya, sistem yang merangkai jadi dokumen.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.rpp.store'))
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.akademik.rpp.create') }}" class="btn btn-sm btn-primary px-3">
                            <i class="bi bi-plus-lg me-1"></i>Tambah RPP
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-guru">
                            <option value="">-- Semua Guru --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-mapel">
                            <option value="">-- Semua Mata Pelajaran --</option>
                            @foreach($mapelList as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Menunggu Verifikasi</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="rpp-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Materi</th>
                                <th width="12%">Status</th>
                                <th width="8%">File</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var table = $('#rpp-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.rpp.list') }}',
            data: function (d) {
                d.guru_id = $('#filter-guru').val();
                d.mata_pelajaran_id = $('#filter-mapel').val();
                d.status = $('#filter-status').val();
            },
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'materi' },
            { data: 'status_badge', orderable: false },
            { data: 'file_link', orderable: false, searchable: false },
        ],
        language: {
            sEmptyTable: 'Tidak ada data RPP',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst: 'Pertama', sPrevious: 'Sebelumnya', sNext: 'Selanjutnya', sLast: 'Terakhir' },
        },
    });

    $('#filter-guru, #filter-mapel, #filter-status').on('change', function () {
        table.ajax.reload();
    });

    window.hapusRpp = function (id, judul) {
        Swal.fire({
            title: 'Hapus RPP?',
            text: 'RPP "' + judul + '" akan dihapus permanen. Data tidak dapat dipulihkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/akademik/rpp') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    toastr.success(res.message);
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message ?? 'Gagal menghapus RPP.');
                },
            });
        });
    };
});
</script>
@endpush
