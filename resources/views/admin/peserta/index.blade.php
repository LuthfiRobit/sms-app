@extends('admin.layouts.app')
@section('title', 'Manajemen Data Peserta')

@push('styles')
<style>
    /* ---- Step Indicator ---- */
    .step-wizard { display:flex; counter-reset:step; margin-bottom:1.5rem; }
    .step-wizard-item { flex:1; text-align:center; position:relative; }
    .step-wizard-item::before {
        content:''; position:absolute; top:18px; left:-50%; width:100%;
        height:3px; background:#dee2e6; z-index:0;
    }
    .step-wizard-item:first-child::before { display:none; }
    .step-wizard-item.active::before,
    .step-wizard-item.done::before { background:#4680ff; }
    .step-badge {
        width:36px; height:36px; border-radius:50%; display:inline-flex;
        align-items:center; justify-content:center; font-weight:700;
        font-size:.85rem; position:relative; z-index:1;
        background:#dee2e6; color:#6c757d; border:3px solid #dee2e6;
        transition: all .3s ease;
    }
    .step-wizard-item.active .step-badge { background:#4680ff; color:#fff; border-color:#4680ff; }
    .step-wizard-item.done .step-badge   { background:#2ca87f; color:#fff; border-color:#2ca87f; }
    .step-label { display:block; font-size:.75rem; margin-top:.35rem; color:#6c757d; font-weight:500; }
    .step-wizard-item.active .step-label { color:#4680ff; font-weight:700; }
    .step-wizard-item.done .step-label   { color:#2ca87f; }

    /* ---- Accordion Ortu ---- */
    .ortu-accordion .accordion-button:not(.collapsed) { background:#eef2ff; color:#4680ff; }
    .ortu-accordion .accordion-button::after { filter: none; }

    /* ---- Import Result ---- */
    #import-result-box { max-height:240px; overflow-y:auto; }
    #import-result-box .err-item { font-size:.8rem; padding:.2rem .4rem; border-bottom:1px solid #f0f0f0; }
    #import-result-box .err-item:last-child { border:none; }

    /* ---- Avatar ---- */
    .avatar-peserta { width:40px; height:40px; border-radius:50%; object-fit:cover; }

    /* ---- Tab pane transition ---- */
    .tab-pane-step { display:none; }
    .tab-pane-step.active { display:block; animation: fadeIn .3s ease; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2 flex-wrap">
                <div class="flex-grow-1">
                    <h5 class="mb-0 text-primary"><i class="bi bi-person-vcard-fill me-2"></i> Manajemen Data Peserta</h5>
                    <small class="text-muted">Data peserta sesuai standar Dapodik Kemdikbud.</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(auth()->user()->hasPermissionTo('admin.peserta.store'))
                    <button class="btn btn-primary btn-sm" id="btn-tambah-peserta">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Peserta
                    </button>
                    @endif
                    @if(auth()->user()->hasPermissionTo('admin.peserta.store'))
                    <button class="btn btn-success btn-sm" id="btn-import-csv">
                        <i class="bi bi-upload me-1"></i> Import CSV
                    </button>
                    @endif
                    @if(auth()->user()->hasPermissionTo('admin.peserta.export'))
                    <button class="btn btn-outline-secondary btn-sm" id="btn-export-csv">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </button>
                    @endif
                </div>
            </div>

            {{-- Filter Toolbar --}}
            <div class="card-body bg-light border-bottom py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1 small">Nama / NISN</label>
                        <input type="text" id="filter-nama" class="form-control form-control-sm" placeholder="Cari nama atau NISN...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1 small">Agama</label>
                        <select id="filter-agama" class="form-select form-select-sm">
                            <option value="">— Semua Agama —</option>
                            @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                <option value="{{ $agama }}">{{ $agama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1 small">Kecamatan</label>
                        <input type="text" id="filter-kecamatan" class="form-control form-control-sm" placeholder="Filter kecamatan...">
                    </div>
                    <div class="col-md-2">
                        <button id="btn-reset-filter" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="peserta-table">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="6%">Foto</th>
                                <th>Nama Lengkap</th>
                                <th width="12%">NISN</th>
                                <th width="13%">NIK</th>
                                <th width="10%">Agama</th>
                                <th>Kecamatan</th>
                                <th width="8%" class="text-center">Daftar</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================================= --}}
{{-- MODAL: Tambah / Edit Peserta (Multi-Step Form)                          --}}
{{-- ======================================================================= --}}
<div class="modal fade" id="modal-peserta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title text-white mb-0" id="modal-peserta-title">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Data Peserta
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Step Wizard Indicator --}}
                <div class="step-wizard" id="step-wizard">
                    <div class="step-wizard-item active" data-step="1">
                        <span class="step-badge">1</span>
                        <span class="step-label">Data Pribadi</span>
                    </div>
                    <div class="step-wizard-item" data-step="2">
                        <span class="step-badge">2</span>
                        <span class="step-label">Alamat</span>
                    </div>
                    <div class="step-wizard-item" data-step="3">
                        <span class="step-badge">3</span>
                        <span class="step-label">Orang Tua</span>
                    </div>
                    <div class="step-wizard-item" data-step="4">
                        <span class="step-badge">4</span>
                        <span class="step-label">Data Lain</span>
                    </div>
                </div>

                {{-- Step 1: Data Pribadi --}}
                <div class="tab-pane-step active" id="step-1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="f-nama_lengkap" placeholder="Nama sesuai akta/ijazah">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" id="f-jenis_kelamin">
                                <option value="">— Pilih —</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Agama <span class="text-danger">*</span></label>
                            <select class="form-select" id="f-agama">
                                <option value="">— Pilih —</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                    <option value="{{ $agama }}">{{ $agama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">NISN</label>
                            <input type="text" class="form-control" id="f-nisn" placeholder="10 digit NISN" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">NIK</label>
                            <input type="text" class="form-control" id="f-nik" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">No. KK</label>
                            <input type="text" class="form-control" id="f-no_kk" placeholder="Nomor Kartu Keluarga">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="f-tempat_lahir" placeholder="Kota/Kabupaten lahir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="f-tanggal_lahir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kebutuhan Khusus</label>
                            <select class="form-select" id="f-kebutuhan_khusus">
                                <option value="">Tidak Ada</option>
                                <option value="Tunanetra">Tunanetra</option>
                                <option value="Tunarungu">Tunarungu</option>
                                <option value="Tunagrahita">Tunagrahita</option>
                                <option value="Tunadaksa">Tunadaksa</option>
                                <option value="Autis">Autis</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Foto Peserta</label>
                            <input type="file" class="form-control" id="f-foto" accept="image/*">
                            <small class="text-muted">Maks. 2MB. Format: JPG/PNG.</small>
                            <div class="mt-2" id="foto-preview-wrap" style="display:none;">
                                <img id="foto-preview" src="" class="rounded" style="max-height:80px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Alamat --}}
                <div class="tab-pane-step" id="step-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" id="f-alamat" rows="2" placeholder="Jalan, nomor rumah, dll."></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">RT</label>
                            <input type="text" class="form-control" id="f-rt" maxlength="4" placeholder="001">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">RW</label>
                            <input type="text" class="form-control" id="f-rw" maxlength="4" placeholder="001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Desa/Kelurahan</label>
                            <input type="text" class="form-control" id="f-desa_kelurahan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kecamatan</label>
                            <input type="text" class="form-control" id="f-kecamatan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kab/Kota</label>
                            <input type="text" class="form-control" id="f-kabupaten_kota">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provinsi</label>
                            <input type="text" class="form-control" id="f-provinsi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode Pos</label>
                            <input type="text" class="form-control" id="f-kode_pos" maxlength="6" placeholder="Contoh: 62211">
                        </div>
                        <div class="col-12"><hr class="my-1"><small class="text-muted">Koordinat (opsional — untuk peta)</small></div>
                        <div class="col-md-6">
                            <label class="form-label">Lintang (Latitude)</label>
                            <input type="text" class="form-control" id="f-lintang" placeholder="-7.123456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bujur (Longitude)</label>
                            <input type="text" class="form-control" id="f-bujur" placeholder="112.123456">
                        </div>
                    </div>
                </div>

                {{-- Step 3: Orang Tua --}}
                <div class="tab-pane-step" id="step-3">
                    <div class="accordion ortu-accordion" id="accordionOrtu">
                        @foreach([['ayah','Ayah','bi-person-fill','primary'],['ibu','Ibu','bi-person-heart-fill','danger'],['wali','Wali','bi-people-fill','warning']] as [$tipe, $label, $icon, $color])
                        <div class="accordion-item mb-2 border rounded">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $tipe !== 'ayah' ? 'collapsed' : '' }} fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#ortu-{{ $tipe }}">
                                    <i class="bi {{ $icon }} text-{{ $color }} me-2"></i> Data {{ $label }}
                                </button>
                            </h2>
                            <div id="ortu-{{ $tipe }}" class="accordion-collapse collapse {{ $tipe === 'ayah' ? 'show' : '' }}" data-bs-parent="#accordionOrtu">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nama {{ $label }}</label>
                                            <input type="text" class="form-control" id="f-{{ $tipe }}-nama" placeholder="Nama lengkap {{ strtolower($label) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">NIK {{ $label }}</label>
                                            <input type="text" class="form-control" id="f-{{ $tipe }}-nik" placeholder="16 digit NIK" maxlength="16">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Pekerjaan</label>
                                            <input type="text" class="form-control" id="f-{{ $tipe }}-pekerjaan" placeholder="PNS, Wiraswasta, dll.">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Penghasilan / Bulan</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="f-{{ $tipe }}-penghasilan" min="0" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">No. HP</label>
                                            <input type="text" class="form-control" id="f-{{ $tipe }}-no_hp" placeholder="08123456789">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Pendidikan Terakhir</label>
                                            <select class="form-select" id="f-{{ $tipe }}-pendidikan">
                                                <option value="">— Pilih —</option>
                                                @foreach(['SD','SMP','SMA/SMK','D1/D2/D3','S1','S2','S3','Tidak Sekolah'] as $p)
                                                    <option value="{{ $p }}">{{ $p }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Kebutuhan Khusus</label>
                                            <select class="form-select" id="f-{{ $tipe }}-kebutuhan_khusus">
                                                <option value="">Tidak Ada</option>
                                                <option value="Tunanetra">Tunanetra</option>
                                                <option value="Tunarungu">Tunarungu</option>
                                                <option value="Tunagrahita">Tunagrahita</option>
                                                <option value="Tunadaksa">Tunadaksa</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 4: Data Lain (Periodik + Kontak + Dokumen) --}}
                <div class="tab-pane-step" id="step-4">
                    <div class="row g-3">
                        <div class="col-12"><p class="fw-bold text-muted small mb-1"><i class="bi bi-activity me-1"></i> Data Periodik (Kesehatan)</p></div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tinggi Badan (cm)</label>
                            <input type="number" class="form-control" id="f-tinggi_badan" placeholder="155">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Berat Badan (kg)</label>
                            <input type="number" class="form-control" id="f-berat_badan" placeholder="45">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Lingkar Kepala (cm)</label>
                            <input type="number" class="form-control" id="f-lingkar_kepala" placeholder="52">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jumlah Saudara</label>
                            <input type="number" class="form-control" id="f-jumlah_saudara" min="0" placeholder="2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jarak Rumah ke Sekolah (km)</label>
                            <input type="number" step="0.1" class="form-control" id="f-jarak_rumah" placeholder="3.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Waktu Tempuh (menit)</label>
                            <input type="number" class="form-control" id="f-waktu_tempuh" placeholder="15">
                        </div>
                        <div class="col-12 mt-2"><hr class="my-1"><p class="fw-bold text-muted small mb-1"><i class="bi bi-telephone me-1"></i> Kontak Peserta</p></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">No. HP Peserta</label>
                            <input type="text" class="form-control" id="f-no_hp" placeholder="08123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Peserta</label>
                            <input type="email" class="form-control" id="f-email" placeholder="peserta@email.com">
                        </div>
                        <div class="col-12 mt-2"><hr class="my-1"><p class="fw-bold text-muted small mb-1"><i class="bi bi-file-earmark-text me-1"></i> Dokumen Pribadi</p></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">No. KIP</label>
                            <input type="text" class="form-control" id="f-no_kip" placeholder="Nomor Kartu Indonesia Pintar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">No. PKH</label>
                            <input type="text" class="form-control" id="f-no_pkh" placeholder="Nomor Program Keluarga Harapan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">No. KITAS</label>
                            <input type="text" class="form-control" id="f-no_kitas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">No. Paspor</label>
                            <input type="text" class="form-control" id="f-no_paspor">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light justify-content-between">
                <div>
                    <button class="btn btn-secondary btn-sm" id="btn-step-prev" style="display:none;">
                        <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary btn-sm" id="btn-step-next">
                        Selanjutnya <i class="bi bi-chevron-right ms-1"></i>
                    </button>
                    <button class="btn btn-success btn-sm d-none" id="btn-step-submit">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================================= --}}
{{-- MODAL: Import CSV                                                        --}}
{{-- ======================================================================= --}}
<div class="modal fade" id="modal-import-csv" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title text-white mb-0"><i class="bi bi-upload me-1"></i> Import Data Peserta dari CSV</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Panduan --}}
                <div class="alert alert-info alert-sm d-flex align-items-start gap-2 py-2">
                    <i class="bi bi-info-circle-fill mt-1"></i>
                    <div>
                        <strong>Format CSV Dapodik:</strong> Pastikan file CSV menggunakan header standar Dapodik.<br>
                        <small>Baris yang NISN/NIK-nya sudah ada akan di-<em>skip</em> otomatis (proses idempotent).</small><br>
                        <a href="#" class="fw-bold text-info">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template CSV
                        </a>
                    </div>
                </div>

                {{-- Form Upload --}}
                <div id="import-form-area">
                    <label class="form-label fw-bold">Pilih File CSV <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="csv-file-input" accept=".csv,.txt">
                    <small class="text-muted">Maks. 5MB. Format: .csv</small>

                    {{-- Progress Bar --}}
                    <div class="mt-3 d-none" id="import-progress-wrap">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-primary">Sedang memproses...</small>
                            <small id="import-progress-pct">0%</small>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                id="import-progress-bar" style="width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- Hasil Import --}}
                <div id="import-result-area" class="d-none mt-3">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="text-center p-2 rounded bg-success bg-opacity-10 border border-success">
                                <div class="fw-bold fs-4 text-success" id="res-success">0</div>
                                <small class="text-muted">Berhasil</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded bg-warning bg-opacity-10 border border-warning">
                                <div class="fw-bold fs-4 text-warning" id="res-skip">0</div>
                                <small class="text-muted">Di-skip</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded bg-danger bg-opacity-10 border border-danger">
                                <div class="fw-bold fs-4 text-danger" id="res-error">0</div>
                                <small class="text-muted">Gagal</small>
                            </div>
                        </div>
                    </div>
                    <div id="import-result-box" class="d-none">
                        <p class="fw-bold small mb-1 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Detail Error / Skip:</p>
                        <div id="import-error-list"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-success btn-sm" id="btn-do-import">
                    <i class="bi bi-upload me-1"></i> Proses Import
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // -----------------------------------------------------------------
    // DataTable
    // -----------------------------------------------------------------
    let filterTimer;
    const table = $('#peserta-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.peserta.list') }}",
            data: function(d) {
                d.nama       = $('#filter-nama').val();
                d.agama      = $('#filter-agama').val();
                d.kecamatan  = $('#filter-kecamatan').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',        orderable: false, searchable: false },
            { data: 'foto_avatar',        orderable: false, searchable: false },
            { data: 'nama_lengkap',       name: 'nama_lengkap' },
            { data: 'nisn',               name: 'nisn' },
            { data: 'nik',                name: 'nik' },
            { data: 'agama',              name: 'agama' },
            { data: 'kecamatan',          orderable: false, searchable: false },
            { data: 'jumlah_pendaftaran', orderable: false, searchable: false, className: 'text-center' },
            { data: 'action',             orderable: false, searchable: false },
        ],
        language: {
            emptyTable:     'Tidak ada data',
            info:           'Menampilkan _START_ - _END_ dari _TOTAL_ peserta',
            infoEmpty:      'Menampilkan 0 - 0 dari 0 peserta',
            infoFiltered:   '(difilter dari _MAX_ total data)',
            lengthMenu:     'Tampilkan _MENU_ data',
            loadingRecords: 'Memuat...',
            processing:     'Memproses...',
            search:         'Cari:',
            zeroRecords:    'Data tidak ditemukan',
            paginate: { first:'Pertama', last:'Terakhir', next:'Berikut', previous:'Sebelumnya' }
        },
        order: [[2, 'asc']],
        pageLength: 25,
    });

    // Filter handlers dengan debounce
    $('#filter-nama, #filter-kecamatan').on('input', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => table.ajax.reload(), 500);
    });
    $('#filter-agama').on('change', () => table.ajax.reload());
    $('#btn-reset-filter').on('click', function() {
        $('#filter-nama, #filter-kecamatan').val('');
        $('#filter-agama').val('');
        table.ajax.reload();
    });

    // -----------------------------------------------------------------
    // Multi-Step Form State
    // -----------------------------------------------------------------
    let currentStep = 1;
    const totalSteps = 4;
    let pesertaId    = null;
    let formData     = {};      // JS object yang menyimpan state antar tab

    function goToStep(step) {
        // Sembunyikan semua panel
        $('.tab-pane-step').removeClass('active');
        $(`#step-${step}`).addClass('active');
        currentStep = step;

        // Update wizard indicator
        $('.step-wizard-item').each(function() {
            const s = parseInt($(this).data('step'));
            $(this).removeClass('active done');
            if (s === step)  $(this).addClass('active');
            if (s <  step)   $(this).addClass('done');
        });

        // Tombol navigasi
        $('#btn-step-prev').toggle(step > 1);
        if (step < totalSteps) {
            $('#btn-step-next').removeClass('d-none');
            $('#btn-step-submit').addClass('d-none');
        } else {
            $('#btn-step-next').addClass('d-none');
            $('#btn-step-submit').removeClass('d-none');
        }
    }

    function validateStep(step) {
        let valid = true;
        if (step === 1) {
            const fields = [
                { id: '#f-nama_lengkap',  label: 'Nama Lengkap' },
                { id: '#f-jenis_kelamin', label: 'Jenis Kelamin' },
                { id: '#f-tempat_lahir',  label: 'Tempat Lahir' },
                { id: '#f-tanggal_lahir', label: 'Tanggal Lahir' },
                { id: '#f-agama',         label: 'Agama' },
            ];
            fields.forEach(f => {
                const el = $(f.id);
                el.removeClass('is-invalid');
                if (!el.val().trim()) {
                    el.addClass('is-invalid');
                    el.siblings('.invalid-feedback').text(`${f.label} wajib diisi.`);
                    valid = false;
                }
            });

            // Validasi NISN (10 digit jika diisi)
            const nisn = $('#f-nisn').val().trim();
            if (nisn && (!/^\d{10}$/.test(nisn))) {
                $('#f-nisn').addClass('is-invalid');
                valid = false;
                Swal.fire('Validasi', 'NISN harus 10 digit angka.', 'warning');
            }
            // Validasi NIK (16 digit jika diisi)
            const nik = $('#f-nik').val().trim();
            if (nik && (!/^\d{16}$/.test(nik))) {
                $('#f-nik').addClass('is-invalid');
                valid = false;
                Swal.fire('Validasi', 'NIK harus 16 digit angka.', 'warning');
            }
        }
        return valid;
    }

    function collectFormData() {
        return {
            peserta: {
                nama_lengkap:     $('#f-nama_lengkap').val(),
                jenis_kelamin:    $('#f-jenis_kelamin').val(),
                agama:            $('#f-agama').val(),
                nisn:             $('#f-nisn').val() || null,
                nik:              $('#f-nik').val() || null,
                no_kk:            $('#f-no_kk').val() || null,
                tempat_lahir:     $('#f-tempat_lahir').val(),
                tanggal_lahir:    $('#f-tanggal_lahir').val(),
                kebutuhan_khusus: $('#f-kebutuhan_khusus').val() || null,
            },
            alamat: {
                alamat:         $('#f-alamat').val(),
                rt:             $('#f-rt').val(),
                rw:             $('#f-rw').val(),
                desa_kelurahan: $('#f-desa_kelurahan').val(),
                kecamatan:      $('#f-kecamatan').val(),
                kabupaten_kota: $('#f-kabupaten_kota').val(),
                provinsi:       $('#f-provinsi').val(),
                kode_pos:       $('#f-kode_pos').val(),
                lintang:        $('#f-lintang').val() || null,
                bujur:          $('#f-bujur').val() || null,
            },
            orang_tua: [
                buildOrtu('ayah'), buildOrtu('ibu'), buildOrtu('wali'),
            ].filter(o => o.nama),          // skip jika nama kosong
            periodik: {
                tinggi_badan:   $('#f-tinggi_badan').val() || null,
                berat_badan:    $('#f-berat_badan').val() || null,
                lingkar_kepala: $('#f-lingkar_kepala').val() || null,
                jarak_rumah:    $('#f-jarak_rumah').val() || null,
                waktu_tempuh:   $('#f-waktu_tempuh').val() || null,
                jumlah_saudara: $('#f-jumlah_saudara').val() || null,
            },
            kontak: {
                no_hp: $('#f-no_hp').val() || null,
                email: $('#f-email').val() || null,
            },
            dokumen_pribadi: {
                no_kip:    $('#f-no_kip').val() || null,
                no_pkh:    $('#f-no_pkh').val() || null,
                no_kitas:  $('#f-no_kitas').val() || null,
                no_paspor: $('#f-no_paspor').val() || null,
            },
        };
    }

    function buildOrtu(tipe) {
        return {
            tipe:             tipe,
            nama:             $(`#f-${tipe}-nama`).val().trim(),
            nik:              $(`#f-${tipe}-nik`).val() || null,
            pekerjaan:        $(`#f-${tipe}-pekerjaan`).val() || null,
            penghasilan:      $(`#f-${tipe}-penghasilan`).val() || null,
            pendidikan:       $(`#f-${tipe}-pendidikan`).val() || null,
            no_hp:            $(`#f-${tipe}-no_hp`).val() || null,
            kebutuhan_khusus: $(`#f-${tipe}-kebutuhan_khusus`).val() || null,
        };
    }

    function resetForm() {
        pesertaId = null;
        currentStep = 1;
        $('input[id^="f-"], select[id^="f-"], textarea[id^="f-"]').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('#foto-preview-wrap').hide();
        goToStep(1);
    }

    function fillForm(d) {
        const p = d.tab_pribadi   || {};
        const a = d.tab_alamat    || {};
        const o = d.tab_orang_tua || {};
        const pr = d.tab_periodik  || {};
        const k = d.tab_kontak    || {};
        const dok = d.tab_dokumen  || {};

        // Tab 1
        $('#f-nama_lengkap').val(p.nama_lengkap || '');
        $('#f-jenis_kelamin').val(p.jenis_kelamin || '');
        $('#f-agama').val(p.agama || '');
        $('#f-nisn').val(p.nisn || '');
        $('#f-nik').val(p.nik || '');
        $('#f-no_kk').val(p.no_kk || '');
        $('#f-tempat_lahir').val(p.tempat_lahir || '');
        // Konversi DD/MM/YYYY → YYYY-MM-DD untuk input[type=date]
        if (p.tanggal_lahir) {
            const parts = p.tanggal_lahir.split('/');
            if (parts.length === 3) {
                $('#f-tanggal_lahir').val(`${parts[2]}-${parts[1]}-${parts[0]}`);
            }
        }
        $('#f-kebutuhan_khusus').val(p.kebutuhan_khusus || '');
        // Guard: hanya tampilkan preview jika path adalah relatif storage (bukan temp/absolute path Windows)
        if (p.foto && !p.foto.includes(':') && !p.foto.includes('\\') && !p.foto.startsWith('/tmp')) {
            $('#foto-preview').attr('src', '/storage/' + p.foto);
            $('#foto-preview-wrap').show();
        } else {
            $('#foto-preview-wrap').hide();
        }

        // Tab 2
        $('#f-alamat').val(a.alamat || '');
        $('#f-rt').val(a.rt || '');
        $('#f-rw').val(a.rw || '');
        $('#f-desa_kelurahan').val(a.desa_kelurahan || '');
        $('#f-kecamatan').val(a.kecamatan || '');
        $('#f-kabupaten_kota').val(a.kabupaten_kota || '');
        $('#f-provinsi').val(a.provinsi || '');
        $('#f-kode_pos').val(a.kode_pos || '');
        $('#f-lintang').val(a.lintang || '');
        $('#f-bujur').val(a.bujur || '');

        // Tab 3: Orang Tua
        ['ayah','ibu','wali'].forEach(tipe => {
            const ot = o[tipe] || {};
            $(`#f-${tipe}-nama`).val(ot.nama || '');
            $(`#f-${tipe}-nik`).val(ot.nik || '');
            $(`#f-${tipe}-pekerjaan`).val(ot.pekerjaan || '');
            $(`#f-${tipe}-penghasilan`).val(ot.penghasilan || '');
            $(`#f-${tipe}-pendidikan`).val(ot.pendidikan || '');
            $(`#f-${tipe}-no_hp`).val(ot.no_hp || '');
            $(`#f-${tipe}-kebutuhan_khusus`).val(ot.kebutuhan_khusus || '');
        });

        // Tab 4
        $('#f-tinggi_badan').val(pr.tinggi_badan || '');
        $('#f-berat_badan').val(pr.berat_badan || '');
        $('#f-lingkar_kepala').val(pr.lingkar_kepala || '');
        $('#f-jarak_rumah').val(pr.jarak_rumah || '');
        $('#f-waktu_tempuh').val(pr.waktu_tempuh || '');
        $('#f-jumlah_saudara').val(pr.jumlah_saudara || '');
        $('#f-no_hp').val(k.no_hp || '');
        $('#f-email').val(k.email || '');
        $('#f-no_kip').val(dok.no_kip || '');
        $('#f-no_pkh').val(dok.no_pkh || '');
        $('#f-no_kitas').val(dok.no_kitas || '');
        $('#f-no_paspor').val(dok.no_paspor || '');
    }

    // -----------------------------------------------------------------
    // Buka Modal Tambah
    // -----------------------------------------------------------------
    $('#btn-tambah-peserta').on('click', function() {
        resetForm();
        $('#modal-peserta-title').html('<i class="bi bi-person-plus-fill me-1"></i> Tambah Data Peserta');
        $('#modal-peserta').modal('show');
    });

    // -----------------------------------------------------------------
    // Navigasi Step
    // -----------------------------------------------------------------
    $('#btn-step-next').on('click', function() {
        if (!validateStep(currentStep)) return;
        if (currentStep < totalSteps) goToStep(currentStep + 1);
    });
    $('#btn-step-prev').on('click', function() {
        if (currentStep > 1) goToStep(currentStep - 1);
    });

    // -----------------------------------------------------------------
    // Foto Preview
    // -----------------------------------------------------------------
    $('#f-foto').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#foto-preview').attr('src', e.target.result);
                $('#foto-preview-wrap').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // -----------------------------------------------------------------
    // Submit Form (Tambah / Edit)
    // -----------------------------------------------------------------
    $('#btn-step-submit').on('click', function() {
        const payload = collectFormData();
        const isEdit  = pesertaId !== null;
        const url     = isEdit
            ? `{{ rtrim(route('admin.peserta.index'), '/') }}/${pesertaId}`
            : `{{ route('admin.peserta.store') }}`;

        // Gunakan FormData untuk mendukung file upload foto
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        if (isEdit) fd.append('_method', 'PUT');

        // Append JSON payload sebagai nested fields
        function appendNested(obj, prefix) {
            for (const [k, v] of Object.entries(obj)) {
                const key = prefix ? `${prefix}[${k}]` : k;
                if (v !== null && v !== undefined && v !== '') {
                    if (Array.isArray(v)) {
                        v.forEach((item, i) => appendNested(item, `${key}[${i}]`));
                    } else if (typeof v === 'object') {
                        appendNested(v, key);
                    } else {
                        fd.append(key, v);
                    }
                }
            }
        }
        appendNested(payload, '');

        // Foto file
        const fotoFile = $('#f-foto')[0].files[0];
        if (fotoFile) fd.append('peserta[foto]', fotoFile);

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url, method: 'POST', data: fd,
            processData: false, contentType: false,
            success: function(res) {
                if (res.status === 200 || res.status === 201) {
                    $('#modal-peserta').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Berhasil!', res.message, 'success');
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan server.';
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Data');
            }
        });
    });

    // -----------------------------------------------------------------
    // Buka Modal Edit
    // -----------------------------------------------------------------
    $('#peserta-table').on('click', '.btn-edit-peserta', function() {
        const id = $(this).data('id');
        resetForm();                    // ← reset dulu (set pesertaId = null, clear fields)
        pesertaId = id;                 // ← baru assign id SETELAH reset agar tidak di-overwrite
        $('#modal-peserta-title').html('<i class="bi bi-pencil me-1"></i> Edit Data Peserta');

        $.get(`{{ rtrim(route('admin.peserta.index'), '/') }}/${id}`, function(res) {
            if (res.status === 200 && res.data) {
                fillForm(res.data);
                $('#modal-peserta').modal('show');
            } else {
                Swal.fire('Error', 'Gagal memuat data peserta.', 'error');
            }
        }, 'json');
    });

    // -----------------------------------------------------------------
    // Hapus Peserta
    // -----------------------------------------------------------------
    $('#peserta-table').on('click', '.btn-delete-peserta', function() {
        const id   = $(this).data('id');
        const nama = $(this).data('nama');
        Swal.fire({
            title: 'Hapus Peserta?',
            html: `Data <strong>${nama}</strong> akan dihapus permanen.<br><small class="text-muted">Pastikan tidak ada pendaftaran aktif.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus!',
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ rtrim(route('admin.peserta.index'), '/') }}/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.status === 200) {
                            table.ajax.reload(null, false);
                            Swal.fire('Dihapus!', res.message, 'success');
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error');
                    }
                });
            }
        });
    });

    // -----------------------------------------------------------------
    // Import CSV
    // -----------------------------------------------------------------
    $('#btn-import-csv').on('click', function() {
        // Reset modal
        $('#csv-file-input').val('');
        $('#import-progress-wrap').addClass('d-none');
        $('#import-result-area').addClass('d-none');
        $('#import-result-box').addClass('d-none');
        $('#import-error-list').html('');
        $('#import-form-area').show();
        $('#btn-do-import').show();
        $('#modal-import-csv').modal('show');
    });

    $('#btn-do-import').on('click', function() {
        const fileInput = $('#csv-file-input')[0];
        if (!fileInput.files.length) {
            Swal.fire('Pilih File', 'Silakan pilih file CSV terlebih dahulu.', 'warning');
            return;
        }

        const fd = new FormData();
        fd.append('csv_file', fileInput.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        // Tampilkan progress bar (simulasi)
        $('#import-progress-wrap').removeClass('d-none');
        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + Math.random() * 15, 85);
            $('#import-progress-bar').css('width', pct + '%');
            $('#import-progress-pct').text(Math.round(pct) + '%');
        }, 300);

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        $.ajax({
            url: "{{ route('admin.peserta.import-csv') }}",
            method: 'POST', data: fd,
            processData: false, contentType: false,
            success: function(res) {
                clearInterval(ticker);
                $('#import-progress-bar').css('width', '100%');
                $('#import-progress-pct').text('100%');

                $('#res-success').text(res.success_count || 0);
                $('#res-skip').text(res.skip_count || 0);
                $('#res-error').text(res.error_count || 0);
                $('#import-result-area').removeClass('d-none');

                if (res.errors && res.errors.length) {
                    $('#import-result-box').removeClass('d-none');
                    const html = res.errors.map(e =>
                        `<div class="err-item"><span class="badge bg-secondary me-1">Baris ${e.row}</span>${e.message}</div>`
                    ).join('');
                    $('#import-error-list').html(html);
                }

                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                clearInterval(ticker);
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat upload.';
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Proses Import');
                setTimeout(() => $('#import-progress-wrap').addClass('d-none'), 1000);
            }
        });
    });

    // -----------------------------------------------------------------
    // Export CSV
    // -----------------------------------------------------------------
    $('#btn-export-csv').on('click', function() {
        const params = new URLSearchParams({
            nama:       $('#filter-nama').val(),
            agama:      $('#filter-agama').val(),
            kecamatan:  $('#filter-kecamatan').val(),
        }).toString();

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengekspor...');

        // Buka di tab baru agar download berjalan di background
        const link = document.createElement('a');
        link.href = `{{ route('admin.peserta.export-csv') }}?${params}`;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => {
            btn.prop('disabled', false).html('<i class="bi bi-download me-1"></i> Export CSV');
        }, 2000);
    });

    // Init
    goToStep(1);
});
</script>
@endpush
