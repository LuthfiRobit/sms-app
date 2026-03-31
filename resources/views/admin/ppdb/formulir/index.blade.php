@extends('admin.layouts.app')
@section('title', 'Formulir Pendaftaran')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-ui-checks-grid me-2"></i> Formulir Pendaftaran</h5>
                        <small class="text-muted">Kelola formulir pendaftaran dinamis per jalur per tahun pelajaran.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.formulir.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i> Buat Formulir
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Filter bar --}}
            <div class="card-body bg-light border-bottom py-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold mb-1">Filter Jalur Pendaftaran:</label>
                        <select class="form-select" id="filter-jalur">
                            <option value="">-- Semua Jalur --</option>
                            @foreach($jalurPendaftaran as $jalur)
                                <option value="{{ $jalur->id }}">
                                    {{ $jalur->nama }} — {{ $jalur->pembukaanPpdb?->nama ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Filter Tahun Pelajaran:</label>
                        <select class="form-select" id="filter-tahun">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-outline-secondary btn-sm" id="btn-reset-filter">
                            <i class="bi bi-x-circle me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="formulir-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="18%">Aksi</th>
                                <th>Nama Formulir</th>
                                <th width="18%">Jalur Pendaftaran</th>
                                <th width="13%">Tahun Pelajaran</th>
                                <th width="9%">Jml Field</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================================
     MODAL TAMBAH FORMULIR
     ===================================================================== --}}
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.ppdb.formulir.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Buat Formulir Pendaftaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Setiap jalur hanya boleh memiliki <strong>satu formulir</strong> per tahun pelajaran.
                        Setelah dibuat, tambahkan field melalui halaman <strong>Builder Formulir</strong>.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jalur Pendaftaran <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" name="jalur_pendaftaran_id" data-live-search="true" required>
                                <option value="">-- Pilih Jalur --</option>
                                @foreach($jalurPendaftaran as $jalur)
                                    <option value="{{ $jalur->id }}">{{ $jalur->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" name="tahun_pelajaran_id" data-live-search="true" required>
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nama Formulir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama"
                                   placeholder="Contoh: Formulir Zonasi 2026/2027" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2"
                                      placeholder="Keterangan singkat (opsional)..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan & Lanjut ke Builder</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     MODAL EDIT HEADER FORMULIR
     ===================================================================== --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Formulir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Formulir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="3"></textarea>
                    </div>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Jalur dan Tahun Pelajaran tidak dapat diubah. Hapus formulir ini jika membutuhkan konfigurasi berbeda.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function () {
    // Initialize DataTable
    const table = $('#formulir-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.ppdb.formulir.list') }}",
            data: function(d) {
                d.jalur_pendaftaran_id = $('#filter-jalur').val();
                d.tahun_pelajaran_id   = $('#filter-tahun').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action',      name: 'action', orderable: false, searchable: false },
            { data: 'nama',        name: 'nama' },
            { data: 'jalur',       name: 'jalur', orderable: false, searchable: false },
            { data: 'tahun',       name: 'tahun', orderable: false, searchable: false },
            {
                data: 'jumlah_field',
                name: 'jumlah_field',
                orderable: false,
                searchable: false,
                render: function(data) {
                    const cls = data > 0 ? 'bg-primary' : 'bg-secondary';
                    return `<span class="badge ${cls}">${data} field</span>`;
                }
            },
            { data: 'status_aktif', name: 'status_aktif', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
    });

    // Reload table on filter change
    $('#filter-jalur, #filter-tahun').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-jalur, #filter-tahun').val('');
        table.ajax.reload();
    });

    // ------- CRUD Handlers (pola project) -------
    setupCrudHandlers({
        tableId:       '#formulir-table',
        createFormId:  '#form-tambah',
        editFormId:    '#form-edit',
        createModalId: '#modal-tambah',
        editModalId:   '#modal-edit',
        editUrl:       "{{ route('admin.ppdb.formulir.index') }}/{id}",
        updateUrl:     "{{ route('admin.ppdb.formulir.index') }}/{id}",
        deleteUrl:     "{{ route('admin.ppdb.formulir.index') }}/{id}",
        onEditSuccess: function(data) {
            ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
            if ($.fn.selectpicker) {
                $('#modal-edit .selectpicker').selectpicker('refresh');
            }
        },
        onCreateSuccess: function(data) {
            // Redirect ke halaman builder setelah membuat formulir
            if (data && data.id) {
                const builderUrl = "{{ url('admin/ppdb/formulir') }}/" + data.id + "/builder";
                Swal.fire({
                    icon: 'success',
                    title: 'Formulir Dibuat!',
                    text: 'Formulir berhasil dibuat. Anda akan diarahkan ke Builder Formulir.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = builderUrl;
                });
            }
        }
    });

    // Toggle Aktif
    $('#formulir-table').on('click', '.btn-toggle-aktif', function() {
        const id  = $(this).data('id');
        const url = "{{ route('admin.ppdb.formulir.index') }}/" + id + "/toggle-aktif";

        Swal.fire({
            title: 'Ubah Status Aktif?',
            text: 'Formulir nonaktif tidak dapat digunakan untuk pendaftaran.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                handleAjax(url, 'POST', {}, function() {
                    table.ajax.reload(null, false);
                });
            }
        });
    });
});
</script>
@endpush
