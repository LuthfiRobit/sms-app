@extends('admin.layouts.app')
@section('title', 'Manajemen Profil Sekolah')

@push('styles')
    <style>
        .logo-preview-container {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            position: relative;
        }

        .logo-preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .section-title {
            position: relative;
            padding-bottom: 8px;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title h6 {
            /* color: #4e73df; */
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .card-header h5 {
            font-weight: 700;
        }

        .form-label {
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <form id="form-profil" action="{{ route('admin.master.profil-sekolah.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 text-primary"><i class="bi bi-building me-2"></i> Pengaturan Profil Sekolah
                                </h5>
                                <small class="text-muted">Kelola informasi identitas, alamat, dan pimpinan lembaga
                                    pendidikan.</small>
                            </div>
                            @if(auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store'))
                                <div class="flex-shrink-0">
                                    <button type="submit" class="btn btn-sm btn-primary" id="btn-submit">
                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Side: Basic Info & Logo -->
                            <div class="col-lg-4 col-md-5 border-end">
                                <div class="text-center mb-4">
                                    <div class="section-title text-start">
                                        <h6 class="mb-0">Visual & Identitas Utama</h6>
                                    </div>
                                    <div class="logo-preview-container mx-auto">
                                        <img id="logo-preview"
                                            src="{{ $profil && $profil->logo ? asset($profil->logo) : asset('assets/images/no-logo.png') }}"
                                            alt="Logo Preview">
                                    </div>
                                    @if(auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store'))
                                        <div class="mb-4">
                                            <label for="logo-input" class="btn btn-outline-primary btn-sm px-3">
                                                <i class="bi bi-camera me-1"></i> Ganti Logo
                                            </label>
                                            <input type="file" id="logo-input" name="logo" class="d-none" accept="image/*"
                                                onchange="previewLogo(this)">
                                            <div class="small text-muted mt-2">Format: <strong>JPG, PNG, JPEG</strong> (Maks.
                                                2MB)</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-uppercase">NPSN <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="npsn"
                                            value="{{ $profil->npsn ?? '' }}" maxlength="8" placeholder="Contoh: 20123456"
                                            required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-uppercase">NSS</label>
                                        <input type="text" class="form-control" name="nss" value="{{ $profil->nss ?? '' }}"
                                            maxlength="12" placeholder="Contoh: 101234567890" {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-uppercase">Bentuk Pendidikan <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control selectpicker" name="bentuk_pendidikan"
                                            data-live-search="true" title="Pilih Jenjang" required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                            @php
                                                $jenjangs = ['SD', 'SMP', 'SMA', 'SMK', 'Madrasah Ibtidaiyah', 'Madrasah Tsanawiyah', 'Madrasah Aliyah', 'TK', 'PAUD'];
                                                $currentJenjang = $profil->bentuk_pendidikan ?? '';
                                            @endphp
                                            @foreach($jenjangs as $j)
                                                <option value="{{ $j }}" {{ $currentJenjang == $j ? 'selected' : '' }}>{{ $j }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-uppercase">Status Sekolah <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control selectpicker" name="status_sekolah" required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                            <option value="Negeri" {{ ($profil && $profil->status_sekolah == 'Negeri') ? 'selected' : '' }}>Negeri</option>
                                            <option value="Swasta" {{ ($profil && $profil->status_sekolah == 'Swasta') ? 'selected' : '' }}>Swasta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Detailed Info -->
                            <div class="col-lg-8 col-md-7">
                                <div class="ps-lg-3">
                                    <!-- 1. Nama & Kontak -->
                                    <div class="section-title">
                                        <h6 class="mb-0">Informasi Lembaga & Kontak</h6>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-uppercase">Nama Resmi Sekolah <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nama_sekolah"
                                                value="{{ $profil->nama_sekolah ?? '' }}"
                                                placeholder="Masukkan nama lengkap sekolah" required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Email Resmi <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i
                                                        class="bi bi-envelope"></i></span>
                                                <input type="email" class="form-control" name="email"
                                                    value="{{ $profil->email ?? '' }}" placeholder="info@sekolah.sch.id"
                                                    required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Nomor Telepon <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i
                                                        class="bi bi-telephone"></i></span>
                                                <input type="text" class="form-control" name="telepon"
                                                    value="{{ $profil->telepon ?? '' }}" placeholder="021-xxxxxx" required
                                                    {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-uppercase">Alamat Website</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                                <input type="text" class="form-control" name="website"
                                                    value="{{ $profil->website ?? '' }}" placeholder="www.sekolah.sch.id" {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Alamat Fisik -->
                                    <div class="section-title">
                                        <h6 class="mb-0">Lokasi & Alamat Fisik</h6>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-uppercase">Alamat Lengkap <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" name="alamat" rows="3"
                                                placeholder="Jl. Raya No. XX, Kelurahan..." required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>{{ $profil->alamat ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Kode Pos <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="kode_pos"
                                                value="{{ $profil->kode_pos ?? '' }}" maxlength="5" placeholder="12345"
                                                required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Wilayah
                                                (Desa/Kecamatan/Kota)</label>
                                            <input type="text" class="form-control bg-light"
                                                value="Akan tersedia pada Fase 4 (Referensi Wilayah)" disabled>
                                        </div>
                                    </div>

                                    <!-- 3. Manajemen -->
                                    <div class="section-title">
                                        <h6 class="mb-0">Pimpinan & Manajemen</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold small text-uppercase">Nama Kepala Sekolah <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="kepala_sekolah"
                                                value="{{ $profil->kepala_sekolah ?? '' }}" placeholder="Nama gelar lengkap"
                                                required {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold small text-uppercase">NIP / NIY</label>
                                            <input type="text" class="form-control" name="nip_kepsek"
                                                value="{{ $profil->nip_kepsek ?? '' }}" maxlength="18"
                                                placeholder="18 digit angka" {{ !auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store') ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.profil-sekolah.store'))
                        <div class="card-footer bg-light text-end py-3">
                            <button type="reset" class="btn btn-sm btn-secondary px-4 me-2">Reset</button>
                            <button type="submit" class="btn btn-sm btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#logo-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function () {
            // Initialize selectpicker
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker({
                    style: 'btn-outline-secondary',
                    size: 4
                });
            }

            $('#form-profil').on('submit', function (e) {
                e.preventDefault();
                let form = this;
                let url = $(this).attr('action');
                let btn = $(this).find('button[type="submit"]');
                let originalBtnHtml = btn.html();

                // Clear previous validation errors
                ResponseHandler.clearValidationErrors(form);

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');

                let formData = new FormData(form);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        ResponseHandler.handleSuccess(response.message);
                        btn.prop('disabled', false).html(originalBtnHtml);
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(originalBtnHtml);
                        // Standardized error handling using global handler
                        ResponseHandler.handleHttpError(xhr, null, form);
                    }
                });
            });
        });
    </script>
@endpush