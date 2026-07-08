@extends('admin.layouts.app')
@section('title', 'Kepatuhan RPP')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-clipboard-check me-2"></i>Kepatuhan RPP</h5>
                        <small class="text-muted">Kombinasi guru + mata pelajaran + tahun ajaran yang punya jadwal KBM tapi belum punya RPP berstatus disetujui.</small>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.akademik.rpp.index') }}" class="btn btn-sm btn-outline-primary px-3">
                            <i class="bi bi-journal-text me-1"></i>Kelola RPP
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="rpp-compliance-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Tahun Ajaran</th>
                                <th>Rombel Terdampak</th>
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
    $('#rpp-compliance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.akademik.rpp-compliance.list') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'tahun_nama' },
            { data: 'rombel_terdampak' },
        ],
        language: {
            sEmptyTable: 'Semua guru sudah punya RPP disetujui untuk mapel yang diajarkan',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst: 'Pertama', sPrevious: 'Sebelumnya', sNext: 'Selanjutnya', sLast: 'Terakhir' },
        },
    });
});
</script>
@endpush
