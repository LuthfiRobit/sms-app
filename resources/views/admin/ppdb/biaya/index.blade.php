@extends('admin.layouts.app')
@section('title', 'Biaya Registrasi')

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
                        <h5 class="mb-0 text-primary"><i class="bi bi-wallet2 me-2"></i> Biaya Registrasi</h5>
                        <small class="text-muted">Kelola aturan biaya pendaftaran per jalur.</small>
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
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.biaya.store'))
                    <div class="col-md-7 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah" id="btn-tambah-biaya">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Biaya
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="biaya-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Aksi</th>
                                <th>Nama Tagihan</th>
                                <th width="20%">Nominal</th>
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
            <form id="form-tambah" action="{{ route('admin.ppdb.biaya.store') }}" method="POST">
                @csrf
                <input type="hidden" name="jalur_pendaftaran_id" id="tambah-jalur_pendaftaran_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Biaya</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Biaya <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Formulir Pendaftaran" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="nominal" min="1" placeholder="Contoh: 150000" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="aktifCheck" name="is_aktif" value="1" checked>
                        <label class="form-check-label fw-bold" for="aktifCheck">Aktif / Berlaku?</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="2" placeholder="Informasi tambahan terkait pembayaran ini..."></textarea>
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
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Biaya</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Biaya <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="nominal" id="edit-nominal" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="edit-is_aktif" name="is_aktif" value="1">
                        <label class="form-check-label fw-bold" for="edit-is_aktif">Aktif / Berlaku?</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="2"></textarea>
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

        const table = $('#biaya-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.ppdb.biaya.list') }}",
                data: function(d) {
                    d.jalur_pendaftaran_id = currentJalurId;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'nominal_format', name: 'nominal_format', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false }
            ]
        });

        $('#filter-jalur').on('change', function() {
            currentJalurId = $(this).val();
            table.ajax.reload();
        });

        $('#btn-tambah-biaya').on('click', function(e) {
            if(!currentJalurId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire('Oops...', 'Silahkan pilih Jalur Pendaftaran terlebih dahulu!', 'warning');
                return false;
            }
            $('#tambah-jalur_pendaftaran_id').val(currentJalurId);
        });

        setupCrudHandlers({
            tableId: '#biaya-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.ppdb.biaya.index') }}/{id}",
            updateUrl: "{{ route('admin.ppdb.biaya.index') }}/{id}",
            deleteUrl: "{{ route('admin.ppdb.biaya.index') }}/{id}",
            onEditSuccess: function(data) {
                // Formatting for manual inputs
                data.jalur_pendaftaran_id = currentJalurId;
                
                $('#edit-is_aktif').prop('checked', data.is_aktif ? true : false);
                // Strip decimal zeroes
                data.nominal = parseInt(data.nominal);

                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
            }
        });
    });
</script>
@endpush
