@extends('admin.layouts.app')
@section('title', 'Detail Peserta — ' . ($detail['tab_pribadi']['nama_lengkap'] ?? 'Tidak Diketahui'))

@push('styles')
<style>
    /* ---- Info Label ---- */
    .info-label { font-size:.75rem; color:#6c757d; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.15rem; }
    .info-value  { font-size:.95rem; color:#222; font-weight:500; }
    .info-value.empty { color:#adb5bd; font-style:italic; }

    /* ---- foto ---- */
    .foto-peserta-wrap { display:flex; flex-direction:column; align-items:center; gap:.75rem; }
    .foto-peserta { width:140px; height:140px; border-radius:50%; object-fit:cover;
        border:4px solid #4680ff; box-shadow:0 4px 16px rgba(70,128,255,.25); }
    .foto-peserta-default { background:linear-gradient(135deg,#4680ff,#6610f2);
        display:flex; align-items:center; justify-content:center; }
    .foto-peserta-default i { font-size:3.5rem; color:rgba(255,255,255,.85); }

    /* ---- ortu card ---- */
    .ortu-card { border-top:3px solid; }
    .ortu-card.ayah { border-top-color:#4680ff; }
    .ortu-card.ibu  { border-top-color:#dc3545; }
    .ortu-card.wali { border-top-color:#ffc107; }

    /* ---- Breadcrumb back ---- */
    .back-btn { display:inline-flex; align-items:center; gap:.4rem; font-size:.875rem; }

    /* ---- Riwayat badge ---- */
    .badge-status { font-size:.75rem; }

    /* ---- Maps embed ---- */
    .maps-frame { width:100%; height:280px; border:none; border-radius:.5rem; }
</style>
@endpush

@section('content')
@php
    $pribadi = $detail['tab_pribadi']  ?? [];
    $alamat  = $detail['tab_alamat']   ?? null;
    $ortu    = $detail['tab_orang_tua']?? [];
    $periodik= $detail['tab_periodik'] ?? null;
    $kontak  = $detail['tab_kontak']   ?? null;
    $dokumen = $detail['tab_dokumen']  ?? null;
    $meta    = $detail['meta']         ?? [];

    $nama    = $pribadi['nama_lengkap'] ?? '—';

    // Format tanggal lahir (Carbon)
    $tglLahirFormatted = '—';
    if (!empty($pribadi['tanggal_lahir'])) {
        try {
            $tglLahirFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $pribadi['tanggal_lahir'])
                ->locale('id')->isoFormat('D MMMM YYYY');
        } catch (\Exception $e) {
            $tglLahirFormatted = $pribadi['tanggal_lahir'];
        }
    }

    // Helper: rupiah
    function formatRp(?string $val): string {
        if (!$val) return '—';
        return 'Rp ' . number_format((float)$val, 0, ',', '.');
    }
@endphp

{{-- Breadcrumb --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <a href="{{ route('admin.peserta.index') }}" class="back-btn text-muted text-decoration-none">
            <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Daftar Peserta
        </a>
        <h5 class="mb-0 mt-1 text-primary fw-bold"><i class="bi bi-person-vcard-fill me-2"></i>{{ $nama }}</h5>
    </div>
    @if($meta['pendaftaran_aktif'] > 0)
        <span class="badge bg-primary fs-6">
            <i class="bi bi-file-text-fill me-1"></i> {{ $meta['pendaftaran_aktif'] }} Pendaftaran Aktif
        </span>
    @endif
</div>

{{-- Header Card: Foto + Info Singkat --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center mb-3 mb-md-0">
                <div class="foto-peserta-wrap">
                    @if(!empty($pribadi['foto']))
                        <img src="{{ asset('storage/' . $pribadi['foto']) }}" class="foto-peserta" alt="Foto {{ $nama }}">
                    @else
                        <div class="foto-peserta foto-peserta-default">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    @endif
                    <span class="badge {{ $pribadi['user_id'] ? 'bg-success' : 'bg-secondary' }} mt-1">
                        <i class="bi bi-{{ $pribadi['user_id'] ? 'link-45deg' : 'person-x' }} me-1"></i>
                        {{ $pribadi['user_id'] ? 'Akun Terhubung' : 'Belum Punya Akun' }}
                    </span>
                </div>
            </div>
            <div class="col-md-10">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-label">NISN</div>
                        <div class="info-value {{ empty($pribadi['nisn']) ? 'empty' : '' }}">
                            {{ $pribadi['nisn'] ?? 'Belum diisi' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">NIK</div>
                        <div class="info-value {{ empty($pribadi['nik']) ? 'empty' : '' }}">
                            {{ $pribadi['nik'] ?? 'Belum diisi' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value">
                            @if(($pribadi['jenis_kelamin'] ?? '') === 'L')
                                <i class="bi bi-gender-male text-primary me-1"></i> Laki-laki
                            @elseif(($pribadi['jenis_kelamin'] ?? '') === 'P')
                                <i class="bi bi-gender-female text-danger me-1"></i> Perempuan
                            @else
                                <span class="empty">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Agama</div>
                        <div class="info-value">{{ $pribadi['agama'] ?? '—' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Tempat, Tanggal Lahir</div>
                        <div class="info-value">{{ ($pribadi['tempat_lahir'] ?? '—') . ', ' . $tglLahirFormatted }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Kebutuhan Khusus</div>
                        <div class="info-value {{ empty($pribadi['kebutuhan_khusus']) ? 'empty' : '' }}">
                            {{ $pribadi['kebutuhan_khusus'] ?? 'Tidak Ada' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">No. KK</div>
                        <div class="info-value {{ empty($pribadi['no_kk']) ? 'empty' : '' }}">
                            {{ $pribadi['no_kk'] ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Terdaftar Sejak</div>
                        <div class="info-value">{{ $pribadi['created_at'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bootstrap Nav Tabs --}}
<div class="card shadow-sm">
    <div class="card-header p-0">
        <ul class="nav nav-tabs nav-tabs-primary" id="detailTabs" role="tablist">
            @foreach([
                ['pribadi',    'bi-person-fill',          'Data Pribadi'],
                ['alamat',     'bi-geo-alt-fill',          'Alamat'],
                ['ortu',       'bi-people-fill',           'Orang Tua'],
                ['periodik',   'bi-activity',              'Data Periodik'],
                ['kontak',     'bi-telephone-fill',        'Kontak'],
                ['dokumen',    'bi-file-earmark-text-fill','Dokumen'],
                ['riwayat',    'bi-clock-history',         'Riwayat Pendaftaran'],
            ] as [$key, $icon, $label])
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $key === 'pribadi' ? 'active' : '' }} py-3 px-3"
                    id="tab-{{ $key }}" data-bs-toggle="tab"
                    data-bs-target="#panel-{{ $key }}" type="button" role="tab">
                    <i class="bi {{ $icon }} me-1"></i>
                    <span class="d-none d-md-inline">{{ $label }}</span>
                </button>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body tab-content" id="detailTabContent">

        {{-- ============================================================ --}}
        {{-- Tab: Data Pribadi (sudah tampil di header card, redundant info) --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade show active" id="panel-pribadi" role="tabpanel" aria-labelledby="tab-pribadi">
            <div class="row g-4 py-2">
                <div class="col-md-3 text-center">
                    @if(!empty($pribadi['foto']))
                        <img src="{{ asset('storage/' . $pribadi['foto']) }}" class="img-thumbnail rounded" style="max-height:200px;object-fit:cover;" alt="Foto">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height:200px;">
                            <i class="bi bi-person-fill text-muted" style="font-size:4rem;"></i>
                        </div>
                    @endif
                    <p class="fw-bold mt-2 mb-0 text-primary">{{ $nama }}</p>
                    <small class="text-muted">{{ $pribadi['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        @foreach([
                            ['NISN',               $pribadi['nisn'] ?? null],
                            ['NIK',                $pribadi['nik'] ?? null],
                            ['Agama',              $pribadi['agama'] ?? null],
                            ['Tempat Lahir',       $pribadi['tempat_lahir'] ?? null],
                            ['Tanggal Lahir',      $tglLahirFormatted],
                            ['Kebutuhan Khusus',   $pribadi['kebutuhan_khusus'] ?? null],
                            ['No. KK',             $pribadi['no_kk'] ?? null],
                            ['Akun Pengguna',      $pribadi['akun_terhubung'] ?? null],
                        ] as [$lbl, $val])
                        <div class="col-md-4">
                            <div class="info-label">{{ $lbl }}</div>
                            <div class="info-value {{ empty($val) ? 'empty' : '' }}">{{ $val ?? '—' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Alamat                                                   --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-alamat" role="tabpanel" aria-labelledby="tab-alamat">
            @if($alamat)
            <div class="row g-3 py-2">
                <div class="col-12">
                    <div class="info-label">Alamat Lengkap</div>
                    <div class="info-value">{{ $alamat['alamat'] ?? '—' }}</div>
                </div>
                <div class="col-md-2">
                    <div class="info-label">RT</div>
                    <div class="info-value">{{ $alamat['rt'] ?? '—' }}</div>
                </div>
                <div class="col-md-2">
                    <div class="info-label">RW</div>
                    <div class="info-value">{{ $alamat['rw'] ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Desa / Kelurahan</div>
                    <div class="info-value">{{ $alamat['desa_kelurahan'] ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Kecamatan</div>
                    <div class="info-value">{{ $alamat['kecamatan'] ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Kabupaten / Kota</div>
                    <div class="info-value">{{ $alamat['kabupaten_kota'] ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Provinsi</div>
                    <div class="info-value">{{ $alamat['provinsi'] ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Kode Pos</div>
                    <div class="info-value">{{ $alamat['kode_pos'] ?? '—' }}</div>
                </div>

                {{-- Google Maps Embed (hanya jika koordinat tersedia) --}}
                @if(!empty($alamat['lintang']) && !empty($alamat['bujur']))
                <div class="col-12 mt-3">
                    <div class="info-label mb-2"><i class="bi bi-map-fill me-1"></i> Lokasi di Peta</div>
                    <iframe class="maps-frame"
                        src="https://maps.google.com/maps?q={{ $alamat['lintang'] }},{{ $alamat['bujur'] }}&z=16&output=embed"
                        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <small class="text-muted">
                        Koordinat: {{ $alamat['lintang'] }}, {{ $alamat['bujur'] }}
                    </small>
                </div>
                @else
                <div class="col-12">
                    <div class="alert alert-light border mt-2 py-2">
                        <i class="bi bi-map text-muted me-1"></i>
                        <small class="text-muted">Koordinat GPS belum tersedia. Isi lintang & bujur untuk menampilkan peta.</small>
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-geo-alt" style="font-size:3rem;"></i>
                <p class="mt-2">Data alamat belum tersedia.</p>
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Orang Tua (3 card side by side)                         --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-ortu" role="tabpanel" aria-labelledby="tab-ortu">
            <div class="row g-3 py-2">
                @foreach([
                    ['ayah','Ayah','primary','bi-person-fill'],
                    ['ibu','Ibu','danger','bi-person-heart-fill'],
                    ['wali','Wali','warning','bi-people-fill'],
                ] as [$tipe, $label, $color, $icon])
                @php $ot = $ortu[$tipe] ?? null; @endphp
                <div class="col-md-4">
                    <div class="card h-100 ortu-card {{ $tipe }}">
                        <div class="card-header bg-{{ $color }} bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                            <strong><i class="bi {{ $icon }} text-{{ $color }} me-1"></i> {{ $label }}</strong>
                            @if(auth()->user()->hasPermissionTo('admin.peserta.update'))
                            <button type="button" class="btn btn-sm btn-outline-{{ $color }} btn-edit-ortu-kontak"
                                data-tipe="{{ $tipe }}" data-label="{{ $label }}"
                                data-nama="{{ e($ot['nama'] ?? '') }}" data-nohp="{{ e($ot['no_hp'] ?? '') }}"
                                title="Edit Nama &amp; No. WhatsApp {{ $label }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($ot)
                            <div class="row g-2">
                                @php $rows = [
                                    ['Nama',           $ot['nama']],
                                    ['NIK',            $ot['nik']],
                                    ['Pekerjaan',      $ot['pekerjaan']],
                                    ['Penghasilan',    formatRp($ot['penghasilan'] ?? null)],
                                    ['Pendidikan',     $ot['pendidikan']],
                                    ['No. HP',         $ot['no_hp']],
                                    ['Kebutuhan Khusus', $ot['kebutuhan_khusus']],
                                ]; @endphp
                                @foreach($rows as [$lbl, $val])
                                <div class="col-12">
                                    <div class="info-label">{{ $lbl }}</div>
                                    <div class="info-value {{ empty($val) || $val === '—' ? 'empty' : '' }}">
                                        {{ empty($val) ? '—' : $val }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-person-x" style="font-size:2rem;"></i>
                                <p class="small mt-1 mb-0">Data {{ $label }} belum diisi.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Data Periodik                                            --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-periodik" role="tabpanel" aria-labelledby="tab-periodik">
            @if($periodik)
            <div class="row g-4 py-2">
                @foreach([
                    ['Tinggi Badan',   ($periodik['tinggi_badan']   ?? null) ? $periodik['tinggi_badan']   . ' cm'    : null, 'bi-arrows-vertical',    'primary'],
                    ['Berat Badan',    ($periodik['berat_badan']    ?? null) ? $periodik['berat_badan']    . ' kg'    : null, 'bi-speedometer2',       'success'],
                    ['Lingkar Kepala', ($periodik['lingkar_kepala'] ?? null) ? $periodik['lingkar_kepala'] . ' cm'    : null, 'bi-circle',             'info'],
                    ['Jarak ke Sekolah',($periodik['jarak_rumah']  ?? null) ? $periodik['jarak_rumah']    . ' km'    : null, 'bi-geo',                'warning'],
                    ['Waktu Tempuh',   ($periodik['waktu_tempuh']  ?? null) ? $periodik['waktu_tempuh']    . ' menit' : null, 'bi-clock',              'secondary'],
                    ['Jumlah Saudara', ($periodik['jumlah_saudara']?? null) ? $periodik['jumlah_saudara'] . ' orang' : null, 'bi-people',             'danger'],
                ] as [$lbl, $val, $icon, $color])
                <div class="col-md-4 col-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded border">
                        <div class="bg-{{ $color }} bg-opacity-10 rounded p-2">
                            <i class="bi {{ $icon }} text-{{ $color }} fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">{{ $lbl }}</div>
                            <div class="info-value {{ empty($val) ? 'empty' : '' }} fs-5">
                                {{ $val ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-activity" style="font-size:3rem;"></i>
                <p class="mt-2">Data periodik belum tersedia.</p>
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Kontak                                                   --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-kontak" role="tabpanel" aria-labelledby="tab-kontak">
            @if($kontak)
            <div class="row g-3 py-2">
                <div class="col-md-6">
                    <div class="card border p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi bi-telephone-fill text-primary fs-3"></i>
                            </div>
                            <div>
                                <div class="info-label">Nomor HP Peserta</div>
                                <div class="info-value {{ empty($kontak['no_hp']) ? 'empty' : '' }}">
                                    @if(!empty($kontak['no_hp']))
                                        <a href="tel:{{ $kontak['no_hp'] }}">{{ $kontak['no_hp'] }}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="bi bi-envelope-fill text-info fs-3"></i>
                            </div>
                            <div>
                                <div class="info-label">Email Peserta</div>
                                <div class="info-value {{ empty($kontak['email']) ? 'empty' : '' }}">
                                    @if(!empty($kontak['email']))
                                        <a href="mailto:{{ $kontak['email'] }}">{{ $kontak['email'] }}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-telephone-x" style="font-size:3rem;"></i>
                <p class="mt-2">Data kontak belum tersedia.</p>
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Dokumen Pribadi                                          --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-dokumen" role="tabpanel" aria-labelledby="tab-dokumen">
            @if($dokumen)
            <div class="row g-3 py-2">
                @foreach([
                    ['No. KIP',    $dokumen['no_kip']    ?? null, 'Kartu Indonesia Pintar',       'bi-card-text',   'success'],
                    ['No. PKH',    $dokumen['no_pkh']    ?? null, 'Program Keluarga Harapan',     'bi-house-heart', 'warning'],
                    ['No. KITAS',  $dokumen['no_kitas']  ?? null, 'Kartu Izin Tinggal Sementara', 'bi-passport',    'info'],
                    ['No. Paspor', $dokumen['no_paspor'] ?? null, 'Nomor Paspor',                 'bi-book',        'primary'],
                ] as [$lbl, $val, $desc, $icon, $color])
                <div class="col-md-6">
                    <div class="card border p-3 {{ $val ? '' : 'bg-light' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-{{ $color }} bg-opacity-10 rounded p-3">
                                <i class="bi {{ $icon }} text-{{ $color }} fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ $lbl }}</div>
                                <div class="info-value {{ empty($val) ? 'empty' : 'fs-5' }}">
                                    {{ $val ?? 'Tidak Ada' }}
                                </div>
                                <small class="text-muted">{{ $desc }}</small>
                            </div>
                            @if($val)
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            @else
                            <i class="bi bi-dash-circle text-muted fs-5"></i>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-x" style="font-size:3rem;"></i>
                <p class="mt-2">Data dokumen pribadi belum tersedia.</p>
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- Tab: Riwayat Pendaftaran                                      --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="panel-riwayat" role="tabpanel" aria-labelledby="tab-riwayat">
            <div class="py-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0 fw-bold">
                        Total: <span class="badge bg-primary">{{ $meta['total_pendaftaran'] ?? 0 }}</span> pendaftaran
                    </p>
                </div>

                @if(!empty($detail['pendaftaran']) && count($detail['pendaftaran']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>No. Pendaftaran</th>
                                <th>Jalur Pendaftaran</th>
                                <th>Tahun Pelajaran</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail['pendaftaran'] as $i => $daftar)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><code>{{ $daftar['no_pendaftaran'] ?? '—' }}</code></td>
                                <td>{{ $daftar['jalur_pendaftaran']['nama'] ?? '—' }}</td>
                                <td>{{ $daftar['tahun_pelajaran']['nama'] ?? '—' }}</td>
                                <td>{{ isset($daftar['created_at']) ? \Carbon\Carbon::parse($daftar['created_at'])->locale('id')->isoFormat('D MMM YYYY') : '—' }}</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'draft'        => ['warning',  'Draft'],
                                            'submit'       => ['info',     'Diajukan'],
                                            'verifikasi'   => ['primary',  'Verifikasi'],
                                            'lulus'        => ['success',  'Lulus'],
                                            'tidak_lulus'  => ['danger',   'Tidak Lulus'],
                                            'ditolak'      => ['danger',   'Ditolak'],
                                            'daftar_ulang' => ['success',  'Daftar Ulang'],
                                            'batal'        => ['secondary','Dibatalkan'],
                                        ];
                                        $st = $daftar['status'] ?? 'draft';
                                        [$badgeColor, $badgeLabel] = $statusMap[$st] ?? ['secondary', $st];
                                    @endphp
                                    <span class="badge badge-status bg-{{ $badgeColor }}">{{ $badgeLabel }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clock-history" style="font-size:3rem;"></i>
                    <p class="mt-2">Peserta belum pernah mendaftar.</p>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- end tab-content --}}
</div>{{-- end card --}}
@endsection

@push('scripts')
<script>
    // Aktifkan tab dari URL hash
    const hash = window.location.hash;
    if (hash) {
        const tabEl = document.querySelector(`button[data-bs-target="${hash.replace('#panel-', '#panel-')}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }

    // Update hash saat ganti tab
    document.querySelectorAll('#detailTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            const target = e.target.getAttribute('data-bs-target');
            history.replaceState(null, '', target.replace('#panel-', '#'));
        });
    });

    // Edit ringan Nama + No. WhatsApp orang tua (ayah/ibu/wali) — dipakai
    // terutama untuk melengkapi nomor WA wali murid tanpa buka form edit
    // peserta lengkap. Lihat PesertaController::updateOrangTuaKontak().
    $(document).on('click', '.btn-edit-ortu-kontak', function () {
        const tipe = $(this).data('tipe');
        const label = $(this).data('label');
        const namaLama = $(this).data('nama') || '';
        const noHpLama = $(this).data('nohp') || '';

        Swal.fire({
            title: `Edit Kontak ${label}`,
            html: `
                <div class="text-start">
                    <label class="form-label small fw-semibold mb-1">Nama</label>
                    <input type="text" id="swal-ortu-nama" class="form-control mb-3" value="${namaLama}" placeholder="Nama ${label}">
                    <label class="form-label small fw-semibold mb-1">No. WhatsApp</label>
                    <input type="text" id="swal-ortu-nohp" class="form-control" value="${noHpLama}" placeholder="08xxxxxxxxxx">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const nama = document.getElementById('swal-ortu-nama').value.trim();
                const noHp = document.getElementById('swal-ortu-nohp').value.trim();
                if (!nama) {
                    Swal.showValidationMessage('Nama wajib diisi.');
                    return false;
                }
                return { nama, no_hp: noHp };
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `{{ url('admin/peserta/'.$pribadi['id'].'/orang-tua') }}/${tipe}`,
                method: 'PUT',
                data: { _token: '{{ csrf_token() }}', ...result.value },
                success: function (res) {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                        : (xhr.responseJSON?.message || 'Gagal memperbarui kontak.');
                    Swal.fire('Gagal', msg, 'error');
                },
            });
        });
    });
</script>
@endpush
