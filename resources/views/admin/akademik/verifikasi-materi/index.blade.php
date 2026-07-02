@extends('admin.layouts.app')
@section('title', 'Verifikasi Materi Ajar')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-shield-check me-2"></i>Verifikasi Materi Ajar</h5>
            <small class="text-muted">Tinjau dan setujui/tolak materi yang diunggah oleh guru.</small>
          </div>
          <div class="flex-shrink-0 d-flex gap-2">
            <select id="filterStatus" class="form-select form-select-sm" style="width:auto">
              <option value="">Semua Status</option>
              <option value="pending" selected>Menunggu Verifikasi</option>
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
          <table class="table table-sm table-hover align-middle" id="tableVerifikasi">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Judul Materi</th>
                <th>Guru</th>
                <th>Mata Pelajaran</th>
                <th>Tanggal</th>
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

{{-- Modal Detail + Verifikasi --}}
<div class="modal fade" id="modalVerifikasi" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Verifikasi Materi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <div class="row g-3" id="infoArea"></div>
        <div id="formArea" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const tableVer = $('#tableVerifikasi').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: '{{ route('admin.akademik.verifikasi-materi.list') }}',
    data: d => {
      d.status          = $('#filterStatus').val();
      d.guru_id         = $('#filterGuru').val();
    }
  },
  columns: [
    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
    { data: 'judul' },
    { data: 'guru_nama' },
    { data: 'mapel_nama' },
    { data: 'tanggal_fmt', searchable: false },
    { data: 'status_badge', orderable: false },
    { data: 'action', orderable: false, searchable: false, className: 'text-center' },
  ],
  pageLength: 25,
  language: { url: '/vendor/datatables/lang/Indonesian.json' },
});

$('#filterStatus, #filterGuru').on('change', () => tableVer.ajax.reload());

window.verifikasiMateri = function(id, aksi) {
  const judul = aksi === 'disetujui' ? 'Setujui Materi' : 'Tolak Materi';
  const btnClass = aksi === 'disetujui' ? 'btn-success' : 'btn-danger';
  const icon = aksi === 'disetujui' ? 'bi-check-lg' : 'bi-x-lg';

  $('#modalVerifikasi .modal-title').html(`<i class="bi ${icon} me-2"></i>${judul}`);
  $('#infoArea').html(`<p class="text-muted mb-0">Anda akan <strong>${judul.toLowerCase()}</strong> materi ini. Tambahkan catatan jika perlu.</p>`);
  $('#formArea').html(`
    <div class="mb-3">
      <label class="form-label fw-semibold">Catatan (opsional)</label>
      <textarea class="form-control" id="catatanRevisi" rows="3" placeholder="Catatan untuk guru..."></textarea>
    </div>
    <div class="d-flex gap-2 justify-content-end">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
      <button type="button" class="btn ${btnClass} btn-sm" onclick="submitVerifikasi(${id},'${aksi}')">
        <i class="bi ${icon} me-1"></i>${judul}
      </button>
    </div>
  `);

  new bootstrap.Modal(document.getElementById('modalVerifikasi')).show();
};

window.submitVerifikasi = function(id, aksi) {
  $.ajax({
    url: `/admin/akademik/verifikasi-materi/${id}/aksi`,
    method: 'POST',
    data: {
      _token: '{{ csrf_token() }}',
      aksi,
      catatan_revisi: $('#catatanRevisi').val(),
    },
    success(res) {
      bootstrap.Modal.getInstance(document.getElementById('modalVerifikasi'))?.hide();
      tableVer.ajax.reload();
      toastr?.success(res.message ?? 'Berhasil.');
    },
    error(xhr) {
      toastr?.error(xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
    }
  });
};
</script>
@endpush
