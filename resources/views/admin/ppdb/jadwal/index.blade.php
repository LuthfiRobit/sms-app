@extends('admin.layouts.app')
@section('title', 'Jadwal Pendaftaran')

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
                        <h5 class="mb-0 text-primary"><i class="bi bi-calendar-event me-2"></i> Jadwal Pendaftaran</h5>
                        <small class="text-muted">Kelola jadwal berbagai tahap per jalur pendaftaran.</small>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light border-bottom">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Pilih Jalur Pendaftaran:</label>
                        <select class="form-control selectpicker" id="filter-jalur" data-live-search="true">
                            <option value="">-- Pilih Jalur --</option>
                            @foreach($jalurPendaftaran as $jalur)
                                <option value="{{ $jalur->id }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ $jalur->pembukaanPpdb->nama }} - {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.jadwal.store'))
                    <div class="col-md-7 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah" id="btn-tambah-jadwal">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="jadwal-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Nama Kegiatan</th>
                                <th width="15%">Tipe</th>
                                <th width="15%">Mulai</th>
                                <th width="15%">Selesai</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.ppdb.jadwal.store') }}" method="POST">
                @csrf
                <input type="hidden" name="jalur_pendaftaran_id" id="tambah-jalur_pendaftaran_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Jadwal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Seleksi Berkas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tipe" required>
                            <option value="pendaftaran">Pendaftaran</option>
                            <option value="seleksi">Seleksi</option>
                            <option value="pengumuman">Pengumuman</option>
                            <option value="daftar_ulang">Daftar Ulang</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="jalur_pendaftaran_id" id="edit-jalur_pendaftaran_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Jadwal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tipe" id="edit-tipe" required>
                            <option value="pendaftaran">Pendaftaran</option>
                            <option value="seleksi">Seleksi</option>
                            <option value="pengumuman">Pengumuman</option>
                            <option value="daftar_ulang">Daftar Ulang</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="mulai" id="edit-mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="selesai" id="edit-selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" id="edit-status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit-keterangan" rows="2"></textarea>
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
        let currentJalurId = $('#filter-jalur').val();

        const table = $('#jadwal-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.ppdb.jadwal.list') }}",
                data: function(d) {
                    d.jalur_pendaftaran_id = currentJalurId;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'tipe_badge', name: 'tipe_badge', orderable: false, searchable: false },
                { data: 'waktu_mulai', name: 'waktu_mulai', orderable: false, searchable: false },
                { data: 'waktu_selesai', name: 'waktu_selesai', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false }
            ]
        });

        $('#filter-jalur').on('change', function() {
            currentJalurId = $(this).val();
            table.ajax.reload();
        });

        $('#btn-tambah-jadwal').on('click', function(e) {
            if(!currentJalurId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Silahkan pilih Jalur Pendaftaran terlebih dahulu!'
                });
                return false;
            }
            $('#tambah-jalur_pendaftaran_id').val(currentJalurId);
        });

        setupCrudHandlers({
            tableId: '#jadwal-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.ppdb.jadwal.index') }}/{id}",
            updateUrl: "{{ route('admin.ppdb.jadwal.index') }}/{id}",
            deleteUrl: "{{ route('admin.ppdb.jadwal.index') }}/{id}",
            onEditSuccess: function(data) {
                // Ensure the jalur id stays correct
                data.jalur_pendaftaran_id = currentJalurId;
                
                if (data.mulai) {
                    data.mulai = data.mulai.substring(0, 16); // format datetime-local
                }
                if (data.selesai) {
                    data.selesai = data.selesai.substring(0, 16);
                }
                
                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
                if ($.fn.selectpicker) {
                    $('#edit-tipe, #edit-status').selectpicker('refresh');
                }
            }
        });
    });
</script>
@endpush
