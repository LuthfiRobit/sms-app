@extends('admin.layouts.app')
@section('title', 'Role Permission')
@push('css')
    <style>
        .category-card {
            border: 1px solid #edf2f9;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #fff;
        }

        .category-header {
            background-color: #f8fafc !important;
            border-bottom: 1px solid #edf2f9 !important;
            padding: 1.25rem 2.25rem !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .category-header:hover {
            background-color: #f1f5f9;
        }

        .module-card {
            border: 1px solid #f1f5f9;
            border-radius: 0.75rem;
            height: 100%;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
        }

        .module-card:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .module-header {
            padding: 1rem 1.25rem;
            background-color: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }

        .module-body {
            padding: 1.25rem;
        }

        .permission-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8fafc;
        }

        .permission-item:last-child {
            border-bottom: none;
        }

        .permission-item .form-check-label {
            font-size: 0.875rem;
            color: #475569;
            cursor: pointer;
            display: block;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white mb-1"><i class="bi bi-shield-lock me-2"></i> Hak Akses:
                            {{ $role->display_name }}
                        </h5>
                        <p class="mb-0 opacity-75">Kelola izin akses untuk role ini secara spesifik.</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-white text-primary fs-6" id="total-assigned-badge">0</span>
                        <small class="d-block opacity-75">Permissions Aktif</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3 align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" id="btn-select-all">
                        <i class="bi bi-check-all"></i> Pilih Semua
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-deselect-all">
                        <i class="bi bi-x-circle"></i> Batalkan Semua
                    </button>
                </div>
                <a href="{{ route('admin.rbac.role.index') }}" class="btn btn-light btn-sm border">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>

            <div id="permission-loading" class="text-center py-5 shadow-sm bg-white rounded">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Menganalisa struktur hak akses...</p>
            </div>

            <form id="form-assign-permissions" class="d-none">
                @csrf
                <div id="permission-matrix">
                    <!-- Data will be populated here -->
                </div>

                <div class="sticky-bottom bg-white border-top p-3 text-end shadow-lg rounded-bottom" style="z-index: 100;">
                    <button type="button" class="btn btn-primary px-4" id="btn-save-permissions">
                        <i class="bi bi-save me-1"></i> Perbarui Hak Akses
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const roleId = "{{ $role->id }}";

            // Fetch permissions structure
            $.ajax({
                url: "{{ route('admin.rbac.role.permissions.list', ':id') }}".replace(':id', roleId),
                type: 'GET',
                success: function (response) {
                    $('#permission-loading').addClass('d-none');
                    $('#form-assign-permissions').removeClass('d-none');

                    if (response.status === 200) {
                        $('#total-assigned-badge').text(response.data.total_assigned);
                        renderMatrix(response.data.permissions_grouped);
                    }
                },
                error: function () {
                    $('#permission-loading').html('<div class="alert alert-danger">Gagal memuat data permissions. Silakan refresh halaman.</div>');
                }
            });

            function renderMatrix(groupedData) {
                let html = '';

                for (let category in groupedData) {
                    let modules = groupedData[category];

                    html += `
                                    <div class="card category-card">
                                        <div class="category-header d-flex justify-content-between align-items-center shadow-sm" style="padding: 1.25rem 2.25rem !important;" data-bs-toggle="collapse" data-bs-target="#cat-${category}">
                                            <h6 class="mb-0 text-uppercase fw-bold text-dark d-flex align-items-center">
                                                <i class="bi bi-folder2-open me-2 text-primary fs-5"></i> ${category}
                                            </h6>
                                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input check-all-category" type="checkbox" data-category="${category}" style="cursor: pointer;">
                                                <label class="form-check-label small fw-semibold text-muted" style="cursor: pointer;">Pilih Semua di ${category}</label>
                                            </div>
                                        </div>
                                        <div id="cat-${category}" class="collapse show">
                                            <div class="card-body p-2 p-md-3">
                                                <div class="row g-4">
                                    `;

                    for (let module in modules) {
                        let permissions = modules[module];

                        html += `
                                        <div class="col-md-6 col-lg-4">
                                            <div class="module-card">
                                                <div class="module-header d-flex justify-content-between align-items-center">
                                                    <span><i class="bi bi-collection me-2"></i> ${module}</span>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input check-all-module" type="checkbox" data-category="${category}" data-module="${module}">
                                                    </div>
                                                </div>
                                                <div class="module-body group-items-${category}-${module}">
                                        `;

                        permissions.forEach(function (perm) {
                            let checked = perm.checked ? 'checked' : '';
                            html += `
                                                <div class="permission-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input item-checkbox" type="checkbox" name="permissions[]" 
                                                            value="${perm.id}" id="perm_${perm.id}" ${checked}
                                                            data-category="${category}" data-module="${module}">
                                                        <label class="form-check-label" for="perm_${perm.id}">
                                                            ${perm.description || perm.name}
                                                        </label>
                                                    </div>
                                                </div>
                                            `;
                        });

                        html += `
                                                </div>
                                            </div>
                                        </div>
                                        `;
                    }

                    html += `
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                }

                $('#permission-matrix').html(html);
                syncCheckboxes();
            }

            // --- Event Handlers ---

            // Global Select All
            $('#btn-select-all').click(function () {
                $('.item-checkbox, .check-all-module, .check-all-category').prop('checked', true);
            });

            // Global Deselect All
            $('#btn-deselect-all').click(function () {
                $('.item-checkbox, .check-all-module, .check-all-category').prop('checked', false);
            });

            // Category Select All
            $(document).on('change', '.check-all-category', function (e) {
                e.stopPropagation(); // Prevent accordion collapse when clicking switch
                let cat = $(this).data('category');
                let checked = $(this).is(':checked');
                $(`.item-checkbox[data-category="${cat}"], .check-all-module[data-category="${cat}"]`).prop('checked', checked);
            });

            // Prevent collapse when clicking the switch container
            $(document).on('click', '.category-header .form-switch', function (e) {
                e.stopPropagation();
            });

            // Module Select All
            $(document).on('change', '.check-all-module', function () {
                let cat = $(this).data('category');
                let mod = $(this).data('module');
                let checked = $(this).is(':checked');
                $(`.item-checkbox[data-category="${cat}"][data-module="${mod}"]`).prop('checked', checked);

                // Check if all modules in category are checked
                let allModulesChecked = $(`.check-all-module[data-category="${cat}"]:not(:checked)`).length === 0;
                $(`.check-all-category[data-category="${cat}"]`).prop('checked', allModulesChecked);
            });

            // Individual Item Change
            $(document).on('change', '.item-checkbox', function () {
                let cat = $(this).data('category');
                let mod = $(this).data('module');

                // Sync module checkbox
                let allItemsInModuleChecked = $(`.item-checkbox[data-category="${cat}"][data-module="${mod}"]:not(:checked)`).length === 0;
                $(`.check-all-module[data-category="${cat}"][data-module="${mod}"]`).prop('checked', allItemsInModuleChecked);

                // Sync category checkbox
                let allModulesInCategoryChecked = $(`.check-all-module[data-category="${cat}"]:not(:checked)`).length === 0;
                $(`.check-all-category[data-category="${cat}"]`).prop('checked', allModulesInCategoryChecked);
            });

            function syncCheckboxes() {
                // Initial sync of group headers based on item state from DB
                $('.check-all-module').each(function () {
                    let cat = $(this).data('category');
                    let mod = $(this).data('module');
                    let allChecked = $(`.item-checkbox[data-category="${cat}"][data-module="${mod}"]:not(:checked)`).length === 0;
                    $(this).prop('checked', allChecked);
                });

                $('.check-all-category').each(function () {
                    let cat = $(this).data('category');
                    let allChecked = $(`.check-all-module[data-category="${cat}"]:not(:checked)`).length === 0;
                    $(this).prop('checked', allChecked);
                });
            }

            // Save Permissions
            $('#btn-save-permissions').click(function () {
                let btn = $(this);
                let originalText = btn.html();
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Memproses...');

                let formData = new FormData($('#form-assign-permissions')[0]);
                let url = "{{ route('admin.rbac.role.permissions.assign', ':id') }}".replace(':id', roleId);

                handleAjax(url, 'POST', formData, function (res) {
                    btn.prop('disabled', false).html(originalText);
                    if (res.status === 200) {
                        // Update the badge count (approximate as we don't refetch)
                        $('#total-assigned-badge').text($('.item-checkbox:checked').length);
                    }
                }, function () {
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });
    </script>
@endpush