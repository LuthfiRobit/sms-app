@extends('layouts.portal')

@section('title', 'Profil Saya')

@section('content')

@php
    $peserta    = $profil['peserta'];
    $alamat     = $profil['alamat'];
    $ortu       = $profil['orang_tua'];
    $periodik   = $profil['periodik'];
    $kontak     = $profil['kontak'];
    $dokumen    = $profil['dokumen_pribadi'];
    $klp        = $profil['kelengkapan'];
    $persen     = $klp['persen'];
    $user       = auth()->user();

    // Indikator warna per tab berdasarkan kelengkapan
    $tabStatus = [
        'akun'    => !empty($user->name) && !empty($user->email),
        'pribadi' => !empty($peserta->nama_lengkap) && !empty($peserta->jenis_kelamin) && !empty($peserta->tanggal_lahir),
        'alamat'  => !empty($alamat?->alamat) && !empty($alamat?->kabupaten_kota),
        'ortu'    => (!empty($ortu['ayah']?->nama) || !empty($ortu['ibu']?->nama)),
        'periodik'=> !empty($periodik?->tinggi_badan) && !empty($periodik?->berat_badan),
        'kontak'  => !empty($kontak?->no_hp),
        'dokumen' => !empty($dokumen?->no_kip) || !empty($dokumen?->no_pkh),
    ];

    $progressColor = $persen < 50 ? 'danger' : ($persen < 80 ? 'warning' : 'success');
@endphp

{{-- Toast Container --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="ppdb-toast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="ppdb-toast-msg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="row g-4">

{{-- ═══════════════════════════════════════════════════
     SIDEBAR KIRI
═══════════════════════════════════════════════════ --}}
<div class="col-lg-3 col-md-4">
    <div class="profil-sidebar sticky-md-top" style="top:80px">

        {{-- Foto Profil --}}
        <div class="text-center mb-3">
            <div class="foto-wrapper mx-auto position-relative" style="width:120px;height:120px">
                <img id="foto-preview"
                     src="{{ $peserta->foto ? asset('storage/'.$peserta->foto) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=16a34a&color=fff&bold=true&rounded=true' }}"
                     class="rounded-circle w-100 h-100 object-fit-cover border border-3 border-white shadow"
                     alt="Foto Profil">
                <label for="input-foto" class="foto-overlay" title="Ganti Foto">
                    <i class="bi bi-camera-fill"></i>
                </label>
                <input type="file" id="input-foto" accept="image/*" class="d-none">
            </div>
            <div class="mt-2 fw-bold text-dark" style="font-size:.95rem">{{ $user->name }}</div>
            <div class="text-muted" style="font-size:.78rem">{{ $user->email }}</div>
            <span class="badge mt-1 {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                <i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle"></i>
                {{ $user->status === 'active' ? 'Aktif' : ucfirst($user->status) }}
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-3 px-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size:.78rem;font-weight:600;color:#374151">Kelengkapan Profil</span>
                <span id="persen-label" class="fw-bold text-{{ $progressColor }}" style="font-size:.88rem">{{ $persen }}%</span>
            </div>
            <div class="progress" style="height:10px;border-radius:8px;background:#e5e7eb">
                <div id="sidebar-progress-bar"
                     class="progress-bar bg-{{ $progressColor }} progress-bar-striped"
                     role="progressbar"
                     style="width:{{ $persen }}%;border-radius:8px;transition:width .6s ease">
                </div>
            </div>
            @if(!empty($klp['item_kurang']))
            <div class="mt-1" style="font-size:.7rem;color:#6b7280">
                Belum: {{ implode(', ', array_slice($klp['item_kurang'], 0, 3)) }}{{ count($klp['item_kurang']) > 3 ? '...' : '' }}
            </div>
            @endif
        </div>

        {{-- Nav Tab Vertikal --}}
        <nav class="profil-nav">
            @php
            $tabs = [
                ['id'=>'akun',    'icon'=>'bi-person-gear',    'label'=>'Akun & Keamanan',  'key'=>'akun'],
                ['id'=>'pribadi', 'icon'=>'bi-person-vcard',   'label'=>'Data Pribadi',      'key'=>'pribadi'],
                ['id'=>'alamat',  'icon'=>'bi-geo-alt',         'label'=>'Alamat',            'key'=>'alamat'],
                ['id'=>'ortu',    'icon'=>'bi-people',          'label'=>'Orang Tua',         'key'=>'ortu'],
                ['id'=>'periodik','icon'=>'bi-heart-pulse',     'label'=>'Data Periodik',     'key'=>'periodik'],
                ['id'=>'kontak',  'icon'=>'bi-telephone',       'label'=>'Kontak',            'key'=>'kontak'],
                ['id'=>'dokumen', 'icon'=>'bi-file-earmark-text','label'=>'Dokumen Pribadi', 'key'=>'dokumen'],
            ];
            @endphp

            @foreach($tabs as $tab)
            <button class="profil-nav-item {{ $loop->first ? 'active' : '' }}"
                    data-tab="{{ $tab['id'] }}"
                    id="nav-{{ $tab['id'] }}">
                <i class="bi {{ $tab['icon'] }} me-2"></i>
                <span class="flex-grow-1 text-start">{{ $tab['label'] }}</span>
                <span class="tab-dot {{ $tabStatus[$tab['key']] ? 'dot-ok' : 'dot-miss' }}"
                      id="dot-{{ $tab['id'] }}"></span>
            </button>
            @endforeach
        </nav>

        {{-- Cek Kelayakan Daftar --}}
        <div class="mt-3 px-1">
            <a href="{{ route('ppdb.pendaftaran.index') }}"
               class="btn btn-success btn-sm w-100 fw-semibold">
                <i class="bi bi-file-earmark-plus me-1"></i>Mulai Pendaftaran
            </a>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     KONTEN KANAN — TAB PANES
═══════════════════════════════════════════════════ --}}
<div class="col-lg-9 col-md-8">
<div id="profil-content">

{{-- ══════════════════════════════════
     TAB 1 — Akun & Keamanan
══════════════════════════════════ --}}
<div class="tab-pane-profil active" id="pane-akun">
    <div class="profil-card mb-4">
        <div class="profil-card-header">
            <i class="bi bi-person-gear me-2 text-success"></i>Informasi Akun
        </div>
        <div class="profil-card-body">
            <form id="form-akun" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control"
                               value="{{ $user->name }}" required minlength="3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="{{ $user->email }}" required>
                    </div>
                    <div class="col-12">
                        <div class="info-box">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            <span>Terdaftar sejak <strong>{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</strong> &nbsp;|&nbsp;
                            Status: <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($user->status) }}</span></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save" id="btn-save-akun">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-shield-lock me-2 text-warning"></i>Ubah Password
        </div>
        <div class="profil-card-body">
            <form id="form-password" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Password Lama <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_lama" class="form-control" id="inp-pw-lama">
                            <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-lama"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="inp-pw-baru" minlength="8">
                            <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-baru"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Konfirmasi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" id="inp-pw-conf">
                            <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-conf"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning fw-semibold btn-save" id="btn-save-pw">
                            <span class="btn-text"><i class="bi bi-key me-1"></i>Ubah Password</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 2 — Data Pribadi
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-pribadi">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-person-vcard me-2 text-success"></i>Data Pribadi (Dapodik)
        </div>
        <div class="profil-card-body">
            <form id="form-pribadi" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="pribadi">

                {{-- Foto Upload --}}
                <div class="mb-4 d-flex align-items-center gap-3">
                    <img id="foto-preview-tab"
                         src="{{ $peserta->foto ? asset('storage/'.$peserta->foto) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=16a34a&color=fff&bold=true&rounded=true' }}"
                         class="rounded-circle border border-2 border-success object-fit-cover"
                         style="width:80px;height:80px" alt="Foto">
                    <div>
                        <label class="form-label fw-semibold mb-1 d-block">Foto Profil</label>
                        <input type="file" name="foto" id="input-foto-tab" accept="image/*" class="form-control form-control-sm" style="max-width:260px">
                        <div class="form-text">JPG/PNG/WebP, maks 2MB</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control"
                               value="{{ $peserta->nama_lengkap }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" id="jk-l"
                                       {{ $peserta->jenis_kelamin === 'L' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-l">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" id="jk-p"
                                       {{ $peserta->jenis_kelamin === 'P' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-p">Perempuan</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control"
                               value="{{ $peserta->tempat_lahir }}" placeholder="Kota tempat lahir">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control"
                               value="{{ $peserta->tanggal_lahir?->format('Y-m-d') }}"
                               max="{{ now()->subDay()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Agama</label>
                        <select name="agama" class="form-select">
                            <option value="">-- Pilih Agama --</option>
                            @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $ag)
                            <option value="{{ $ag }}" {{ $peserta->agama === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kebutuhan Khusus</label>
                        <input type="text" name="kebutuhan_khusus" class="form-control"
                               value="{{ $peserta->kebutuhan_khusus }}" placeholder="Kosongkan jika tidak ada">
                    </div>

                    {{-- Readonly field admin-only --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-2">
                            NIK
                            <span class="badge bg-secondary" data-bs-toggle="tooltip" title="Hanya dapat diubah oleh Admin">🔒 Hanya Admin</span>
                        </label>
                        <input type="text" class="form-control bg-light" value="{{ $peserta->nik ?? '—' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-2">
                            NISN
                            <span class="badge bg-secondary" data-bs-toggle="tooltip" title="Hanya dapat diubah oleh Admin">🔒 Hanya Admin</span>
                        </label>
                        <input type="text" class="form-control bg-light" value="{{ $peserta->nisn ?? '—' }}" readonly>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Data Pribadi</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 3 — Alamat
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-alamat">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-geo-alt me-2 text-success"></i>Data Alamat
        </div>
        <div class="profil-card-body">
            <form id="form-alamat" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="alamat">
                <input type="hidden" name="lintang" id="inp-lintang" value="{{ $alamat?->lintang }}">
                <input type="hidden" name="bujur"   id="inp-bujur"   value="{{ $alamat?->bujur }}">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control" rows="2" required
                                  placeholder="Jl. nama jalan, nomor rumah">{{ $alamat?->alamat }}</textarea>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">RT</label>
                        <input type="text" name="rt" class="form-control" value="{{ $alamat?->rt }}" placeholder="001">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">RW</label>
                        <input type="text" name="rw" class="form-control" value="{{ $alamat?->rw }}" placeholder="002">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Desa/Kelurahan</label>
                        <input type="text" name="desa_kelurahan" class="form-control" value="{{ $alamat?->desa_kelurahan }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kecamatan</label>
                        <input type="text" name="kecamatan" class="form-control" value="{{ $alamat?->kecamatan }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Kabupaten/Kota <span class="text-danger">*</span></label>
                        <input type="text" name="kabupaten_kota" class="form-control" value="{{ $alamat?->kabupaten_kota }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Provinsi <span class="text-danger">*</span></label>
                        <input type="text" name="provinsi" class="form-control" value="{{ $alamat?->provinsi }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Kode Pos</label>
                        <input type="text" name="kode_pos" class="form-control" value="{{ $alamat?->kode_pos }}" maxlength="10">
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" id="btn-gps" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-geo me-1"></i>Gunakan Lokasi GPS
                            </button>
                            <span id="gps-status" class="text-muted" style="font-size:.8rem"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Alamat</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 4 — Orang Tua
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-ortu">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-people me-2 text-success"></i>Data Orang Tua / Wali
            <small class="text-muted fw-normal ms-1">(minimal isi Ayah atau Ibu)</small>
        </div>
        <div class="profil-card-body p-0">
            <div class="accordion accordion-flush" id="accordionOrtu">
                @foreach([
                    ['key'=>'ayah', 'label'=>'Data Ayah',         'icon'=>'bi-person',     'data'=>$ortu['ayah']],
                    ['key'=>'ibu',  'label'=>'Data Ibu',           'icon'=>'bi-person',     'data'=>$ortu['ibu']],
                    ['key'=>'wali', 'label'=>'Data Wali (Opsional)','icon'=>'bi-person-check','data'=>$ortu['wali']],
                ] as $idx => $ot)
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }} fw-semibold"
                                type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $ot['key'] }}">
                            <i class="bi {{ $ot['icon'] }} me-2 text-success"></i>
                            {{ $ot['label'] }}
                            @if(!empty($ot['data']?->nama))
                                <span class="badge bg-success ms-2" style="font-size:.65rem">Terisi</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse-{{ $ot['key'] }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}">
                        <div class="accordion-body">
                            <form id="form-{{ $ot['key'] }}" novalidate>
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="section" value="{{ $ot['key'] }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama {{ ucfirst($ot['key']) }} {{ $ot['key'] !== 'wali' ? '<span class="text-danger">*</span>' : '' }}</label>
                                        <input type="text" name="nama" class="form-control"
                                               value="{{ $ot['data']?->nama }}"
                                               {{ $ot['key'] !== 'wali' ? 'required' : '' }}
                                               placeholder="Nama lengkap {{ $ot['key'] }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">NIK {{ ucfirst($ot['key']) }}</label>
                                        <input type="text" name="nik" class="form-control"
                                               value="{{ $ot['data']?->nik }}" maxlength="16" placeholder="16 digit NIK">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Pekerjaan</label>
                                        <input type="text" name="pekerjaan" class="form-control"
                                               value="{{ $ot['data']?->pekerjaan }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Penghasilan / Bulan</label>
                                        <select name="penghasilan" class="form-select">
                                            <option value="">-- Pilih Rentang --</option>
                                            @foreach(['< 500.000','500.000 - 1.000.000','1.000.001 - 2.000.000','2.000.001 - 5.000.000','> 5.000.000'] as $ph)
                                            <option value="{{ $ph }}" {{ $ot['data']?->penghasilan === $ph ? 'selected' : '' }}>Rp {{ $ph }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Pendidikan Terakhir</label>
                                        <select name="pendidikan" class="form-select">
                                            <option value="">-- Pilih --</option>
                                            @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3','Tidak Sekolah'] as $pdd)
                                            <option value="{{ $pdd }}" {{ $ot['data']?->pendidikan === $pdd ? 'selected' : '' }}>{{ $pdd }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nomor HP</label>
                                        <input type="text" name="no_hp" class="form-control input-phone"
                                               value="{{ $ot['data']?->no_hp }}" placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-save btn-sm">
                                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Data {{ ucfirst($ot['key']) }}</span>
                                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 5 — Data Periodik
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-periodik">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-heart-pulse me-2 text-success"></i>Data Periodik
        </div>
        <div class="profil-card-body">
            <form id="form-periodik" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="periodik">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tinggi Badan (cm)</label>
                        <input type="number" name="tinggi_badan" class="form-control"
                               value="{{ $periodik?->tinggi_badan }}" min="50" max="250" step="0.1" placeholder="170">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Berat Badan (kg)</label>
                        <input type="number" name="berat_badan" class="form-control"
                               value="{{ $periodik?->berat_badan }}" min="10" max="200" step="0.1" placeholder="60">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lingkar Kepala (cm)</label>
                        <input type="number" name="lingkar_kepala" class="form-control"
                               value="{{ $periodik?->lingkar_kepala }}" min="30" max="80" step="0.1" placeholder="54">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jarak Rumah ke Sekolah (km)</label>
                        <input type="number" name="jarak_rumah" class="form-control"
                               value="{{ $periodik?->jarak_rumah }}" min="0" step="0.1" placeholder="5.5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Waktu Tempuh (menit)</label>
                        <input type="number" name="waktu_tempuh" class="form-control"
                               value="{{ $periodik?->waktu_tempuh }}" min="0" placeholder="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jumlah Saudara</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="stepNumber('jumlah_saudara',-1)">−</button>
                            <input type="number" name="jumlah_saudara" id="jumlah_saudara" class="form-control text-center"
                                   value="{{ $periodik?->jumlah_saudara ?? 0 }}" min="0" max="20">
                            <button type="button" class="btn btn-outline-secondary" onclick="stepNumber('jumlah_saudara',1)">+</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Data Periodik</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 6 — Kontak
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-kontak">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-telephone me-2 text-success"></i>Data Kontak
        </div>
        <div class="profil-card-body">
            <form id="form-kontak" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="kontak">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" name="no_hp" class="form-control input-phone"
                                   value="{{ $kontak?->no_hp }}" required placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="form-text">Hanya angka, 10-15 digit</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Kontak</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                   value="{{ $kontak?->email }}" placeholder="email@contoh.com">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Kontak</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════
     TAB 7 — Dokumen Pribadi
══════════════════════════════════ --}}
<div class="tab-pane-profil d-none" id="pane-dokumen">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-file-earmark-text me-2 text-success"></i>Dokumen Pribadi
        </div>
        <div class="profil-card-body">
            <div class="info-box mb-3">
                <i class="bi bi-info-circle text-info me-2"></i>
                Isi nomor dokumen yang dimiliki. Dokumen yang tidak dimiliki dapat dikosongkan.
            </div>
            <form id="form-dokumen" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="dokumen">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            Nomor KIP
                            <i class="bi bi-question-circle text-muted" data-bs-toggle="tooltip"
                               title="Kartu Indonesia Pintar — diberikan kepada siswa kurang mampu"></i>
                        </label>
                        <input type="text" name="no_kip" class="form-control"
                               value="{{ $dokumen?->no_kip }}" maxlength="30" placeholder="Nomor KIP (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            Nomor PKH
                            <i class="bi bi-question-circle text-muted" data-bs-toggle="tooltip"
                               title="Program Keluarga Harapan — program bantuan sosial pemerintah"></i>
                        </label>
                        <input type="text" name="no_pkh" class="form-control"
                               value="{{ $dokumen?->no_pkh }}" maxlength="30" placeholder="Nomor PKH (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            Nomor KITAS
                            <i class="bi bi-question-circle text-muted" data-bs-toggle="tooltip"
                               title="Kartu Izin Tinggal Terbatas — untuk WNA"></i>
                        </label>
                        <input type="text" name="no_kitas" class="form-control"
                               value="{{ $dokumen?->no_kitas }}" maxlength="30" placeholder="Nomor KITAS (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            Nomor Paspor
                            <i class="bi bi-question-circle text-muted" data-bs-toggle="tooltip"
                               title="Nomor paspor untuk WNA atau peserta yang memiliki paspor"></i>
                        </label>
                        <input type="text" name="no_paspor" class="form-control"
                               value="{{ $dokumen?->no_paspor }}" maxlength="30" placeholder="Nomor Paspor (opsional)">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1"></i>Simpan Dokumen</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</div>{{-- #profil-content --}}
</div>
</div>{{-- .row --}}

@endsection

@push('styles')
<style>
/* ── Sidebar ────────────────────────────────── */
.profil-sidebar {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
    padding: 24px 16px;
}
.foto-wrapper { cursor: pointer; }
.foto-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.38);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.3rem;
    opacity: 0; transition: opacity .2s;
    cursor: pointer;
}
.foto-wrapper:hover .foto-overlay { opacity: 1; }

/* ── Nav Vertikal ─────────────────────────── */
.profil-nav { display: flex; flex-direction: column; gap: 3px; }
.profil-nav-item {
    display: flex; align-items: center;
    padding: 9px 12px;
    border: none; background: transparent;
    border-radius: 10px;
    font-size: .845rem; font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: background .18s, color .18s;
    width: 100%;
}
.profil-nav-item:hover { background: #f0fdf4; color: #15803d; }
.profil-nav-item.active { background: #dcfce7; color: #15803d; font-weight: 700; }

/* Tab dots */
.tab-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: background .3s;
}
.dot-ok   { background: #16a34a; }
.dot-miss { background: #fca5a5; }

/* ── Cards ────────────────────────────────── */
.profil-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    overflow: hidden;
}
.profil-card-header {
    padding: 14px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 700; font-size: .875rem; color: #374151;
    display: flex; align-items: center;
}
.profil-card-body { padding: 22px 24px; }

/* ── Form Controls ────────────────────────── */
.form-control, .form-select {
    border-radius: 8px;
    font-size: .875rem;
    border: 1.5px solid #d1d5db;
    transition: border-color .2s, box-shadow .2s;
}
.form-control:focus, .form-select:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,.12);
}
.form-label { font-size: .82rem; margin-bottom: .35rem; color: #374151; }

/* ── Info Box ─────────────────────────────── */
.info-box {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .8125rem;
    color: #166534;
    display: flex; align-items: center;
}

/* ── Buttons ──────────────────────────────── */
.btn-save {
    min-width: 160px;
    font-weight: 600;
    border-radius: 8px;
    font-size: .875rem;
}

/* ── Responsive: mobile stacks ───────────── */
@media (max-width: 767px) {
    .profil-sidebar { margin-bottom: 0; }
    .profil-nav { flex-direction: row; flex-wrap: wrap; gap: 4px; }
    .profil-nav-item { flex: 1 1 auto; justify-content: center; padding: 7px 8px; font-size: .75rem; }
    .profil-nav-item span:not(.tab-dot) { display: none; }
    .profil-nav-item i { margin: 0 !important; font-size: 1.1rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    /* ─── Config ────────────────────────────────────────── */
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const LS_KEY  = 'ppdb_profil_tab';
    const URL_DAPODIK  = "{{ route('ppdb.profil.dapodik') }}";
    const URL_AKUN     = "{{ route('ppdb.profil.akun') }}";
    const URL_PASSWORD = "{{ route('ppdb.profil.password') }}";

    /* ─── Toast Helper ──────────────────────────────────── */
    const toastEl  = document.getElementById('ppdb-toast');
    const toastMsg = document.getElementById('ppdb-toast-msg');
    const bsToast  = new bootstrap.Toast(toastEl, { delay: 3500 });

    function showToast(msg, ok = true) {
        toastEl.className = 'toast align-items-center border-0 text-white ' +
            (ok ? 'bg-success' : 'bg-danger');
        toastMsg.textContent = msg;
        bsToast.show();
    }

    /* ─── Tab Navigation ────────────────────────────────── */
    const navBtns  = document.querySelectorAll('.profil-nav-item');
    const panes    = document.querySelectorAll('.tab-pane-profil');

    function switchTab(id) {
        navBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === id));
        panes.forEach(p  => p.classList.toggle('d-none',  p.id !== 'pane-' + id));
        localStorage.setItem(LS_KEY, id);
    }

    navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

    // Restore saved tab
    const saved = localStorage.getItem(LS_KEY);
    if (saved && document.getElementById('pane-' + saved)) switchTab(saved);

    /* ─── Progress Updater ──────────────────────────────── */
    function updateProgress(klp) {
        if (!klp) return;
        const bar   = document.getElementById('sidebar-progress-bar');
        const label = document.getElementById('persen-label');
        if (bar)   { bar.style.width = klp.persen + '%'; }
        if (label) { label.textContent = klp.persen + '%'; }
    }

    /* ─── Tab Dot Updater ───────────────────────────────── */
    const dotMap = {
        pribadi : 'pribadi',
        alamat  : 'alamat',
        ayah    : 'ortu',
        ibu     : 'ortu',
        wali    : 'ortu',
        periodik: 'periodik',
        kontak  : 'kontak',
        dokumen : 'dokumen',
    };
    function markDotOk(section) {
        const tabId = dotMap[section] || section;
        const dot   = document.getElementById('dot-' + tabId);
        if (dot) { dot.className = 'tab-dot dot-ok'; }
    }

    /* ─── Button Loading State ──────────────────────────── */
    function setLoading(btn, loading) {
        btn.querySelector('.btn-text').classList.toggle('d-none', loading);
        btn.querySelector('.btn-spinner').classList.toggle('d-none', !loading);
        btn.disabled = loading;
    }

    /* ─── Generic AJAX POST ─────────────────────────────── */
    async function ajaxForm(url, formData) {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        return res.json();
    }

    /* ─── Dapodik Forms ─────────────────────────────────── */
    const dapodikForms = ['pribadi','alamat','ayah','ibu','wali','periodik','kontak','dokumen'];

    dapodikForms.forEach(section => {
        const form = document.getElementById('form-' + section);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('.btn-save');
            setLoading(btn, true);

            const fd = new FormData(form);
            // Spoof PUT
            fd.set('_method', 'PUT');

            try {
                const json = await ajaxForm(URL_DAPODIK, fd);
                if (json.success) {
                    showToast(json.message);
                    markDotOk(section);
                    updateProgress(json.kelengkapan);
                    // Update foto jika ada
                    if (json.foto_url) {
                        document.getElementById('foto-preview').src     = json.foto_url;
                        document.getElementById('foto-preview-tab').src = json.foto_url;
                    }
                } else {
                    showToast(json.message || 'Gagal menyimpan.', false);
                }
            } catch (err) {
                showToast('Terjadi kesalahan jaringan.', false);
            } finally {
                setLoading(btn, false);
            }
        });
    });

    /* ─── Akun Form ─────────────────────────────────────── */
    document.getElementById('form-akun').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btn-save-akun');
        setLoading(btn, true);
        const fd = new FormData(e.target);
        fd.set('_method', 'PUT');
        try {
            const json = await ajaxForm(URL_AKUN, fd);
            showToast(json.message, json.success);
            if (json.success) markDotOk('akun');
        } catch { showToast('Kesalahan jaringan.', false); }
        finally   { setLoading(btn, false); }
    });

    /* ─── Password Form ─────────────────────────────────── */
    document.getElementById('form-password').addEventListener('submit', async (e) => {
        e.preventDefault();
        const pw   = document.getElementById('inp-pw-baru').value;
        const conf = document.getElementById('inp-pw-conf').value;
        if (pw !== conf) { showToast('Konfirmasi password tidak cocok.', false); return; }

        const btn = document.getElementById('btn-save-pw');
        setLoading(btn, true);
        const fd = new FormData(e.target);
        fd.set('_method', 'PUT');
        try {
            const json = await ajaxForm(URL_PASSWORD, fd);
            showToast(json.message, json.success);
            if (json.success) e.target.reset();
        } catch { showToast('Kesalahan jaringan.', false); }
        finally   { setLoading(btn, false); }
    });

    /* ─── Foto Preview via FileReader ───────────────────── */
    function bindFotoPreview(inputId, previewId) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        inp.addEventListener('change', function () {
            if (!this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById('foto-preview').src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        });
    }
    bindFotoPreview('input-foto',     'foto-preview');
    bindFotoPreview('input-foto-tab', 'foto-preview-tab');

    // Sidebar overlay foto → syncs to tab input
    document.getElementById('input-foto').addEventListener('change', function () {
        const tabInput = document.getElementById('input-foto-tab');
        if (tabInput && this.files[0]) {
            const dt = new DataTransfer();
            dt.items.add(this.files[0]);
            tabInput.files = dt.files;
        }
    });

    /* ─── GPS Button ────────────────────────────────────── */
    const btnGps    = document.getElementById('btn-gps');
    const gpsStatus = document.getElementById('gps-status');
    if (btnGps) {
        btnGps.addEventListener('click', () => {
            if (!navigator.geolocation) {
                gpsStatus.textContent = 'Browser tidak mendukung GPS.';
                return;
            }
            gpsStatus.textContent = 'Mendapatkan lokasi...';
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('inp-lintang').value = pos.coords.latitude.toFixed(7);
                document.getElementById('inp-bujur').value   = pos.coords.longitude.toFixed(7);
                gpsStatus.textContent = `✓ Lokasi: ${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
            }, () => {
                gpsStatus.textContent = 'Gagal mendapatkan lokasi. Izinkan akses GPS.';
            });
        });
    }

    /* ─── Phone Input: strip non-digit ─────────────────── */
    document.querySelectorAll('.input-phone').forEach(inp => {
        inp.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });

    /* ─── Toggle Password Visibility ───────────────────── */
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', function () {
            const inp = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });

    /* ─── Number Stepper ────────────────────────────────── */
    window.stepNumber = function (id, delta) {
        const inp = document.getElementById(id);
        if (!inp) return;
        const val = parseInt(inp.value || 0) + delta;
        inp.value = Math.max(parseInt(inp.min || 0), Math.min(parseInt(inp.max || 999), val));
    };

    /* ─── Bootstrap Tooltips ────────────────────────────── */
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

})();
</script>
@endpush
