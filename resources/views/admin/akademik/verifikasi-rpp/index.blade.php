@extends('admin.layouts.app')
@section('title', 'Verifikasi RPP')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-shield-check me-2"></i>Verifikasi RPP</h5>
            <small class="text-muted">Tinjau dan setujui/tolak RPP yang disusun guru sebelum tampil di aplikasi mobile.</small>
          </div>
          <div class="flex-shrink-0 d-flex gap-2">
            <select id="filterStatus" class="form-select form-select-sm" style="width:auto">
              <option value="pending" selected>Menunggu Verifikasi</option>
              <option value="">Semua Status</option>
              <option value="disetujui">Disetujui</option>
              <option value="ditolak">Ditolak</option>
            </select>
            <select id="filterGuru" class="form-select form-select-sm" style="width:auto">
              <option value="">Semua Guru</option>
              @foreach($guruList as $g)
              <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle" id="tableVerRpp">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Materi</th>
                <th>Guru</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Catatan Verifikasi --}}
<div class="modal fade" id="modalVerRpp" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalVerRppTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan (opsional)</label>
          <textarea class="form-control" id="catatanRevisiRpp" rows="3" placeholder="Catatan untuk guru..."></textarea>
        </div>
        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-sm" id="btnSubmitVerRpp">Simpan</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const tableVerRpp = $('#tableVerRpp').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: '{{ route('admin.akademik.verifikasi-rpp.list') }}',
    data: d => {
      d.status = $('#filterStatus').val();
      d.guru_id = $('#filterGuru').val();
    },
  },
  columns: [
    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
    { data: 'materi' },
    { data: 'guru_nama' },
    { data: 'mapel_nama' },
    { data: 'status_badge', orderable: false },
    { data: 'action', orderable: false, searchable: false, className: 'text-center' },
  ],
  pageLength: 25,
});

$('#filterStatus, #filterGuru').on('change', () => tableVerRpp.ajax.reload());

window.verifikasiRpp = function (id, aksi) {
  const judul = aksi === 'disetujui' ? 'Setujui RPP' : 'Tolak RPP';
  const btnClass = aksi === 'disetujui' ? 'btn-success' : 'btn-danger';

  $('#modalVerRppTitle').text(judul);
  $('#catatanRevisiRpp').val('');
  $('#btnSubmitVerRpp').removeClass('btn-success btn-danger').addClass(btnClass).text(judul);
  $('#btnSubmitVerRpp').off('click').on('click', function () {
    $.ajax({
      url: `/admin/akademik/verifikasi-rpp/${id}/aksi`,
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        aksi: aksi,
        catatan_revisi: $('#catatanRevisiRpp').val(),
      },
      success: function (res) {
        bootstrap.Modal.getInstance(document.getElementById('modalVerRpp'))?.hide();
        tableVerRpp.ajax.reload(null, false);
        toastr.success(res.message ?? 'Berhasil.');
      },
      error: function (xhr) {
        toastr.error(xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
      },
    });
  });

  new bootstrap.Modal(document.getElementById('modalVerRpp')).show();
};
</script>
@endpush
