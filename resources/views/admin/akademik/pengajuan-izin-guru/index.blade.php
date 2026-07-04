@extends('admin.layouts.app')
@section('title', 'Pengajuan Izin/Sakit Guru')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="bi bi-envelope-paper me-2"></i>Pengajuan Izin/Sakit Guru</h5>
                <small class="text-muted">Persetujuan pengajuan izin/sakit yang dikirim guru dari aplikasi mobile.</small>
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
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold">Guru</label>
                        <select class="form-select form-select-sm" id="filter-guru">
                            <option value="">-- Semua Guru --</option>
                            @foreach($guruList as $g)
                            <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">-- Semua --</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary" id="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="izin-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th>Guru</th>
                                <th width="14%">Lembaga</th>
                                <th width="8%">Jenis</th>
                                <th width="16%">Tanggal</th>
                                <th width="10%">Status</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Detail ── --}}
<div class="modal fade" id="modal-detail-izin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-eye me-1"></i>Detail Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-izin-body">
                <div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tolak (butuh catatan) ── --}}
<div class="modal fade" id="modal-tolak-izin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tolak-izin">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-x-lg me-1"></i>Tolak Pengajuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="catatan_admin" rows="3" maxlength="1000" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#izin-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.pengajuan-izin-guru.list') }}',
            data: function (d) {
                d.lembaga_id = $('#filter-lembaga').val();
                d.guru_id = $('#filter-guru').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'guru_nama' },
            { data: 'lembaga_nama' },
            { data: 'jenis_label' },
            { data: 'tanggal_fmt' },
            { data: 'status_badge', orderable: false },
            { data: 'action', orderable: false, searchable: false },
        ],
        language: { url: '/assets/datatables-id.json' },
    });

    $('#btn-filter').on('click', function () { table.ajax.reload(); });

    $('#filter-lembaga').on('change', function () {
        var lembagaId = $(this).val();
        var $guru = $('#filter-guru');
        $guru.find('option[data-lembaga]').each(function () {
            var show = !lembagaId || $(this).data('lembaga') == lembagaId;
            $(this).prop('hidden', !show);
            if (!show && $(this).is(':selected')) $guru.val('');
        });
    }).trigger('change');

    window.lihatDetailIzin = function (id) {
        $('#detail-izin-body').html('<div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>');
        $('#modal-detail-izin').modal('show');

        $.get('{{ url('admin/akademik/pengajuan-izin-guru') }}/' + id + '/detail', function (res) {
            if (res.status !== 200) return;
            var d = res.data;
            var statusMap = { menunggu: 'warning text-dark', disetujui: 'success', ditolak: 'danger' };
            var color = statusMap[d.status] || 'secondary';

            var html = '<dl class="row mb-3">' +
                '<dt class="col-sm-3">Guru</dt><dd class="col-sm-9">' + (d.guru?.nama_lengkap || '—') + '</dd>' +
                '<dt class="col-sm-3">Lembaga</dt><dd class="col-sm-9">' + (d.lembaga?.nama || '—') + '</dd>' +
                '<dt class="col-sm-3">Jenis</dt><dd class="col-sm-9">' + (d.jenis ? d.jenis.charAt(0).toUpperCase() + d.jenis.slice(1) : '—') + '</dd>' +
                '<dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">' + (d.tanggal_mulai?.substring(0,10)) + ' s/d ' + (d.tanggal_selesai?.substring(0,10)) + '</dd>' +
                '<dt class="col-sm-3">Alasan</dt><dd class="col-sm-9">' + $('<span>').text(d.alasan || '—').html() + '</dd>' +
                '<dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge bg-' + color + '">' + d.status.charAt(0).toUpperCase() + d.status.slice(1) + '</span></dd>' +
                (d.catatan_admin ? '<dt class="col-sm-3">Catatan Admin</dt><dd class="col-sm-9">' + $('<span>').text(d.catatan_admin).html() + '</dd>' : '') +
                '</dl>';

            if (d.lampiran_url) {
                html += '<a href="' + d.lampiran_url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-paperclip me-1"></i>Lihat Lampiran</a>';
            }

            $('#detail-izin-body').html(html);
        });
    };

    window.setujuiIzin = function (id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: 'Status absensi guru pada rentang tanggal ini akan otomatis diperbarui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post('{{ url('admin/akademik/pengajuan-izin-guru') }}/' + id + '/approve', { _token: '{{ csrf_token() }}' }, function (res) {
                if (res.status === 200) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                }
            }).fail(xhr => {
                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.' });
            });
        });
    };

    let _tolakId = null;
    window.tolakIzin = function (id) {
        _tolakId = id;
        $('#modal-tolak-izin').modal('show');
    };

    $('#form-tolak-izin').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url('admin/akademik/pengajuan-izin-guru') }}/' + _tolakId + '/reject',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modal-tolak-izin').modal('hide');
                $('#form-tolak-izin')[0].reset();
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.' });
            }
        });
    });
});
</script>
@endpush
