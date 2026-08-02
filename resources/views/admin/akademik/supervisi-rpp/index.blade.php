@extends('admin.layouts.app')
@section('title', 'Supervisi RPP')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-clipboard-check me-2"></i>Supervisi RPP</h5>
            <small class="text-muted">Riwayat penilaian Instrumen Supervisi (Perencanaan/Pelaksanaan/Asesmen Pembelajaran) atas RPP guru. Mulai supervisi baru dari halaman detail sebuah RPP.</small>
          </div>
          <div class="flex-shrink-0 d-flex gap-2">
            <select id="filterGuru" class="form-select form-select-sm" style="width:auto">
              <option value="">Semua Guru</option>
              @foreach($guruList as $g)
              <option value="{{ $g->id }}">{{ $g->nama }}</option>
              @endforeach
            </select>
            <select id="filterMapel" class="form-select form-select-sm" style="width:auto">
              <option value="">Semua Mata Pelajaran</option>
              @foreach($mapelList as $m)
              <option value="{{ $m->id }}">{{ $m->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle" id="tableSupervisiRpp">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Guru</th>
                <th>Mata Pelajaran</th>
                <th>Materi RPP</th>
                <th>Predikat</th>
                <th class="text-center">Aksi</th>
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
const tableSupervisiRpp = $('#tableSupervisiRpp').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: '{{ route('admin.akademik.supervisi-rpp.list') }}',
    data: d => {
      d.guru_id = $('#filterGuru').val();
      d.mata_pelajaran_id = $('#filterMapel').val();
    },
  },
  columns: [
    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
    { data: 'tanggal', orderable: false },
    { data: 'guru_nama' },
    { data: 'mapel_nama' },
    { data: 'materi_rpp' },
    { data: 'predikat_badges', orderable: false },
    { data: 'action', orderable: false, searchable: false, className: 'text-center' },
  ],
  pageLength: 25,
});

$('#filterGuru, #filterMapel').on('change', () => tableSupervisiRpp.ajax.reload());

window.hapusSupervisi = function (id, judul) {
  if (!confirm(`Hapus data supervisi untuk RPP "${judul}"?`)) return;

  $.ajax({
    url: `/admin/akademik/supervisi-rpp/${id}`,
    method: 'DELETE',
    data: { _token: '{{ csrf_token() }}' },
    success: function (res) {
      tableSupervisiRpp.ajax.reload(null, false);
      toastr.success(res.message ?? 'Berhasil dihapus.');
    },
    error: function (xhr) {
      toastr.error(xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
    },
  });
};
</script>
@endpush
