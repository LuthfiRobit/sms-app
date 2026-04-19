@extends('admin.layouts.app')
@section('title', 'Manajemen Pembayaran PPDB')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <!-- Filter Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="bi bi-funnel me-2"></i> Filter Data</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status Pembayaran</label>
                        <select class="form-control selectpicker" id="filter-status" multiple data-selected-text-format="count > 2" title="Semua Status">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="expired">Expired</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-wallet2 me-2"></i> Daftar Pembayaran PPDB</h5>
                        <small class="text-muted">Kelola transaksi dan konfirmasi pembayaran manual.</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="pembayaran-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Peserta</th>
                                <th>Jalur</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Waktu Bayar</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Show Detail -->
<div class="modal fade" id="modal-show" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-info-circle me-1"></i> Detail Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th width="30%" class="ps-3">Order ID</th>
                        <td id="show-order_id"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">No. Pendaftaran</th>
                        <td id="show-no_pendaftaran"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nama Peserta</th>
                        <td id="show-nama_peserta"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">No. HP</th>
                        <td id="show-no_hp"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Biaya Registrasi</th>
                        <td id="show-biaya"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nominal Bayar</th>
                        <td id="show-amount" class="fw-bold"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Metode</th>
                        <td id="show-metode"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Status</th>
                        <td id="show-status"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Waktu Bayar</th>
                        <td id="show-waktu_bayar"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Keterangan</th>
                        <td id="show-keterangan"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer bg-light text-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Manual -->
<div class="modal fade" id="modal-konfirmasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-konfirmasi" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="konfirmasi-id">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-check-circle me-1"></i> Konfirmasi Pembayaran Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i> Pastikan dana sudah masuk ke rekening sekolah.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bukti Transfer <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="bukti_bayar" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        <small class="text-muted">Format JPG, PNG, WEBP, PDF. Max 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Konfirmasi Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    let table;
    
    $(document).ready(function () {
        // Initialize DataTables
        table = $('#pembayaran-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.pembayaran.list') }}",
                data: function (d) {
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                { data: 'no_pendaftaran', name: 'no_pendaftaran' },
                { data: 'nama_peserta', name: 'nama_peserta' },
                { data: 'jalur', name: 'jalur' },
                { data: 'nominal', name: 'nominal' },
                { data: 'metode', name: 'metode' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'waktu_bayar', name: 'waktu_bayar' }
            ]
        });

        $('#filter-status').on('change', function() {
            table.ajax.reload();
        });

        // Form Konfirmasi Manual
        $('#form-konfirmasi').on('submit', function(e) {
            e.preventDefault();
            let id = $('#konfirmasi-id').val();
            let url = "{{ route('admin.pembayaran.index') }}/" + id + "/konfirmasi";
            let formData = new FormData(this);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modal-konfirmasi').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Pembayaran berhasil dikonfirmasi'
                    });
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.responseJSON?.message || 'Terjadi kesalahan sistem'
                    });
                }
            });
        });
    });

    function showDetail(id) {
        let url = "{{ route('admin.pembayaran.index') }}/" + id;
        
        $.get(url, function(response) {
            if(response.success) {
                let d = response.data;
                $('#show-order_id').text(d.order_id || '-');
                $('#show-no_pendaftaran').text(d.pendaftaran?.no_pendaftaran || '-');
                $('#show-nama_peserta').text(d.pendaftaran?.peserta?.nama_lengkap || '-');
                $('#show-no_hp').text(d.pendaftaran?.peserta?.kontak?.no_hp || '-');
                $('#show-biaya').text(d.biaya_registrasi?.nama || '-');
                
                $('#show-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.amount));
                $('#show-metode').text((d.metode || '-').toUpperCase());
                
                let statusClasses = {
                    'pending': 'warning',
                    'paid': 'success',
                    'expired': 'secondary',
                    'failed': 'danger',
                    'refund': 'info'
                };
                let badgeClass = statusClasses[d.status] || 'secondary';
                $('#show-status').html('<span class="badge bg-' + badgeClass + '">' + (d.status || '').toUpperCase() + '</span>');
                
                $('#show-waktu_bayar').text(d.waktu_bayar ? moment(d.waktu_bayar).format('DD/MM/YYYY HH:mm:ss') : '-');
                $('#show-keterangan').text(d.keterangan || '-');
                
                $('#modal-show').modal('show');
            }
        });
    }

    function openKonfirmasiModal(id) {
        $('#konfirmasi-id').val(id);
        $('#form-konfirmasi')[0].reset();
        $('#modal-konfirmasi').modal('show');
    }
</script>
@endpush
