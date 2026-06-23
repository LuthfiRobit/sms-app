@extends('admin.layouts.app')
@section('title', 'Persetujuan Raport - Kepala Sekolah')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-white"><i class="bi bi-check-circle-fill me-2"></i>Persetujuan Raport</h5>
                        <small class="text-white-50">Kepala sekolah memberikan persetujuan akhir raport.</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Filter --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-lembaga">
                            <option value="">-- Semua Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-semester">
                            <option value="">-- Semua Semester --</option>
                            @foreach($semesterList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="approval-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="12%">Aksi</th>
                                <th>Rombel</th>
                                <th>Wali Kelas</th>
                                <th>Semester</th>
                                <th>Tahun Pelajaran</th>
                                <th width="12%">Tgl Diajukan</th>
                                <th>Diverifikasi Oleh</th>
                                <th width="12%">Tgl Verifikasi</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Setujui ── --}}
<div class="modal fade" id="modal-setujui" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-check-circle me-1"></i>Setujui Raport</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="setujui-id">
                <div class="alert alert-success d-flex gap-2 py-2">
                    <i class="bi bi-check-circle-fill mt-1"></i>
                    <div>
                        Anda akan memberikan <strong>persetujuan akhir</strong> untuk raport ini.
                        Status akan berubah menjadi <strong>Disetujui</strong> dan raport siap dicetak.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Persetujuan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="catatan-approval" rows="3"
                        placeholder="Tambahkan catatan persetujuan jika diperlukan..."></textarea>
                </div>
                <div id="setujui-alert" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-setujui">
                    <i class="bi bi-check-circle me-1"></i>Setujui Raport
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tolak ── --}}
<div class="modal fade" id="modal-tolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bi bi-x-circle me-1"></i>Tolak Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tolak-id">
                <div class="alert alert-warning d-flex gap-2 py-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>Pengajuan yang ditolak akan dikembalikan untuk diperbaiki kembali.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Alasan Penolakan <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(wajib diisi)</small>
                    </label>
                    <textarea class="form-control" id="catatan-tolak" rows="4"
                        placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                    <div id="tolak-catatan-error" class="text-danger small d-none mt-1">Alasan penolakan wajib diisi.</div>
                </div>
                <div id="tolak-alert" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-konfirmasi-tolak">
                    <i class="bi bi-x-circle me-1"></i>Tolak Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var table = $('#approval-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.raport.approval.list') }}',
            data: function (d) {
                d.semester_id = $('#filter-semester').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',          orderable: false, searchable: false },
            { data: 'action',               orderable: false, searchable: false },
            { data: 'rombel_nama' },
            { data: 'wali_kelas' },
            { data: 'semester_nama' },
            { data: 'tahun_nama' },
            { data: 'diajukan_at_fmt' },
            { data: 'diverifikasi_by_name' },
            { data: 'diverifikasi_at_fmt' },
            { data: 'status_badge',         orderable: false, searchable: false },
        ],
        language: {
            sEmptyTable: 'Tidak ada raport yang menunggu persetujuan',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst: 'Pertama', sPrevious: 'Sebelumnya', sNext: 'Selanjutnya', sLast: 'Terakhir' },
        },
    });

    $('#btn-filter').on('click', function () { table.ajax.reload(); });

    // ── Setujui ────────────────────────────────────────────────────────────
    window.setujuiRaport = function (id) {
        $('#setujui-id').val(id);
        $('#catatan-approval').val('');
        $('#setujui-alert').addClass('d-none').html('');
        $('#modal-setujui').modal('show');
    };

    $('#btn-konfirmasi-setujui').on('click', function () {
        var id = $('#setujui-id').val();
        var catatan = $('#catatan-approval').val();
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Memproses...');

        $.ajax({
            url: '{{ url('admin/akademik/raport/approval') }}/' + id + '/approve',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan_approval: catatan },
            success: function (res) {
                if (res.status === 200) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-setujui')).hide();
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Disetujui', text: res.message, timer: 2500, showConfirmButton: false });
                } else {
                    showAlert('#setujui-alert', 'danger', res.message || 'Terjadi kesalahan.');
                }
            },
            error: function (xhr) {
                showAlert('#setujui-alert', 'danger', xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Setujui Raport');
            }
        });
    });

    // ── Tolak ──────────────────────────────────────────────────────────────
    window.tolakRaport = function (id) {
        $('#tolak-id').val(id);
        $('#catatan-tolak').val('');
        $('#tolak-catatan-error').addClass('d-none');
        $('#tolak-alert').addClass('d-none').html('');
        $('#modal-tolak').modal('show');
    };

    $('#btn-konfirmasi-tolak').on('click', function () {
        var id = $('#tolak-id').val();
        var catatan = $.trim($('#catatan-tolak').val());

        if (!catatan) {
            $('#tolak-catatan-error').removeClass('d-none');
            return;
        }
        $('#tolak-catatan-error').addClass('d-none');

        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Memproses...');

        $.ajax({
            url: '{{ url('admin/akademik/raport/approval') }}/' + id + '/reject',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: catatan },
            success: function (res) {
                if (res.status === 200) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-tolak')).hide();
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Ditolak', text: res.message, timer: 2500, showConfirmButton: false });
                } else {
                    showAlert('#tolak-alert', 'danger', res.message || 'Terjadi kesalahan.');
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                showAlert('#tolak-alert', 'danger', msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Tolak Pengajuan');
            }
        });
    });

    function showAlert(sel, type, msg) {
        $(sel).removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).html(msg);
    }
});
</script>
@endpush
