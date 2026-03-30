@extends('admin.layouts.app')
@section('title', 'Syarat Pendaftaran')

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
                        <h5 class="mb-0 text-primary"><i class="bi bi-list-check me-2"></i> Syarat Pendaftaran</h5>
                        <small class="text-muted">Kelola persyaratan dokumen atau isian form per jalur.</small>
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
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.syarat.store'))
                    <div class="col-md-7 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah" id="btn-tambah-syarat">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Syarat
                        </button>
                        <button class="btn btn-success d-none" id="btn-save-order">
                            <i class="bi bi-save me-1"></i> Simpan Urutan
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="syarat-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%">Urutan</th>
                                <th>Nama Syarat</th>
                                <th width="15%">Tipe</th>
                                <th width="10%" class="text-center">Wajib</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="syarat-tbody">
                            <!-- Populated by DataTables via JS (without standard DataTables pagination/sorting to allow drag) -->
                        </tbody>
                    </table>
                </div>
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Drag and drop baris untuk mengubah urutan, kemudian klik Simpan Urutan.</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.ppdb.syarat.store') }}" method="POST">
                @csrf
                <input type="hidden" name="jalur_pendaftaran_id" id="tambah-jalur_pendaftaran_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Syarat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Syarat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Kartu Keluarga" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tipe" required>
                            <option value="dokumen">Dokumen (Upload File)</option>
                            <option value="isian">Isian Teks</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="wajibCheck" name="wajib" value="1" checked>
                        <label class="form-check-label fw-bold" for="wajibCheck">Syarat Wajib?</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan Tambahan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Format file harus PDF, dll."></textarea>
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
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Syarat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Syarat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tipe" id="edit-tipe" required>
                            <option value="dokumen">Dokumen (Upload File)</option>
                            <option value="isian">Isian Teks</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="edit-wajib" name="wajib" value="1">
                        <label class="form-check-label fw-bold" for="edit-wajib">Syarat Wajib?</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan Tambahan</label>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    $(document).ready(function () {
        let currentJalurId = $('#filter-jalur').val();

        // Datatables setup
        const table = $('#syarat-table').DataTable({
            processing: true,
            serverSide: false, // Turn off serverSide to easily manipulate DOM rows
            ajax: {
                url: "{{ route('admin.ppdb.syarat.list') }}",
                data: function(d) {
                    d.jalur_pendaftaran_id = currentJalurId;
                }
            },
            columns: [
                { data: 'urutan', name: 'urutan', className: 'dt-body-center', orderable: false },
                { data: 'nama', name: 'nama', orderable: false },
                { data: 'tipe_badge', name: 'tipe_badge', orderable: false },
                { data: 'wajib_icon', name: 'wajib_icon', className: 'text-center', orderable: false },
                { data: 'action', name: 'action', orderable: false }
            ],
            ordering: false, // disable native ordering so it uses returned order
            paging: false,
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-id', data.id);
                $(row).addClass('draggable-row');
            }
        });

        // Initialize SortableJS
        var el = document.getElementById('syarat-tbody');
        var sortable = Sortable.create(el, {
            handle: '.bi-grip-vertical',
            animation: 150,
            onEnd: function (evt) {
                $('#btn-save-order').removeClass('d-none');
            }
        });

        $('#filter-jalur').on('change', function() {
            currentJalurId = $(this).val();
            table.ajax.reload();
            $('#btn-save-order').addClass('d-none');
        });

        $('#btn-tambah-syarat').on('click', function(e) {
            if(!currentJalurId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire('Oops...', 'Silahkan pilih Jalur Pendaftaran!', 'warning');
                return false;
            }
            $('#tambah-jalur_pendaftaran_id').val(currentJalurId);
        });

        // Save Order Function
        $('#btn-save-order').on('click', function() {
            let orderData = [];
            $('#syarat-tbody tr').each(function(index) {
                let id = $(this).data('id');
                if(id) {
                    orderData.push({
                        id: id,
                        urutan: index + 1
                    });
                }
            });

            if(orderData.length > 0) {
                handleAjax("{{ route('admin.ppdb.syarat.index') }}/reorder", 'POST', { urutan_data: orderData }, function(response) {
                    if(response.status === 200) {
                        $('#btn-save-order').addClass('d-none');
                        table.ajax.reload(null, false); // Reload cleanly from server
                    }
                });
            }
        });

        // CRUD handling
        setupCrudHandlers({
            tableId: '#syarat-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.ppdb.syarat.index') }}/{id}",
            updateUrl: "{{ route('admin.ppdb.syarat.index') }}/{id}",
            deleteUrl: "{{ route('admin.ppdb.syarat.index') }}/{id}",
            onEditSuccess: function(data) {
                data.jalur_pendaftaran_id = currentJalurId;
                
                // Handle checkbox mapping for ResponseHandler form fill automatically
                $('#edit-wajib').prop('checked', data.wajib ? true : false);
                
                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
                if ($.fn.selectpicker) {
                    $('#edit-tipe').selectpicker('refresh');
                }
            }
        });
    });
</script>
@endpush
