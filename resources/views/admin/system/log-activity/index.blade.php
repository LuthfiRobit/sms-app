@extends('admin.layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Log Aktivitas Sistem</h5>
                <button type="button" class="btn btn-danger btn-sm" id="btn-delete-all">
                    <i class="bi bi-trash"></i> Bersihkan Semua Log
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="log-activity-table" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>IP Address</th>
                                <th>Waktu</th>
                                <th width="100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show Detail Modal -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Log Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="180px">Pengguna</th>
                        <td id="show-user">: </td>
                    </tr>
                    <tr>
                        <th>Jenis Aksi</th>
                        <td id="show-action">: </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td id="show-description">: </td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td id="show-ip">: </td>
                    </tr>
                    <tr>
                        <th>Waktu Kejadian</th>
                        <td id="show-time">: </td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td id="show-user-agent">: </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        const table = $('#log-activity-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[4, 'desc']], // order by time (created_at) by default
            ajax: "{{ route('admin.system.log-activity.list') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user_name', name: 'user_name' },
                { data: 'action', name: 'action' },
                { data: 'ip_address', name: 'ip_address' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action_btn', name: 'action_btn', orderable: false, searchable: false },
            ]
        });

        // Handle Show Detail
        $('#log-activity-table').on('click', '.btn-show', function () {
            let id = $(this).data('id');
            let url = "{{ route('admin.system.log-activity.show', ':id') }}".replace(':id', id);

            handleAjax(url, 'GET', {}, function (response) {
                let data = response.data;
                $('#show-user').text(': ' + data.user_name);
                $('#show-action').text(': ' + data.action);
                $('#show-description').text(': ' + (data.description || '-'));
                $('#show-ip').text(': ' + data.ip_address);
                $('#show-time').text(': ' + data.formatted_date);
                $('#show-user-agent').text(': ' + data.user_agent);
                $('#showModal').modal('show');
            });
        });

        // Handle Delete All
        $('#btn-delete-all').on('click', function () {
            ResponseHandler.confirm({
                text: "Semua data log akan dihapus selamanya!",
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus semua!',
                onConfirm: function () {
                    let url = "{{ route('admin.system.log-activity.delete-all') }}";
                    handleAjax(url, 'DELETE', {}, function (response) {
                        table.ajax.reload();
                    });
                }
            });
        });
    });
</script>
@endpush
