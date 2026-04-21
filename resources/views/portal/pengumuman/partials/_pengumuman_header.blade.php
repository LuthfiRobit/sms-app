@php
    $firstPendaftaran = $pendaftaranList->first();
    $statusFirst = $firstPendaftaran->status ?? 'draft';
    
    $statusMap = [
        'lulus'        => ['label' => 'CALON SISWA',   'icon' => 'bi-award-fill'],
        'daftar_ulang' => ['label' => 'DAFTAR ULANG',  'icon' => 'bi-check-circle-fill'],
        'siswa_tetap'  => ['label' => 'SISWA AKTIF',   'icon' => 'bi-mortarboard-fill'],
        'default'      => ['label' => 'PENDAFTAR',     'icon' => 'bi-person-badge-fill'],
    ];
    $stInfo = $statusMap[$statusFirst] ?? $statusMap['default'];
@endphp

<div class="profile-greeting-card">
    <div class="profile-greeting-inner">
        <!-- Profil Siswa -->
        <div class="profile-user-side">
            <div class="foto-wrapper">
                <img src="{{ auth()->user()->peserta && auth()->user()->peserta->foto ? Storage::url(auth()->user()->peserta->foto) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=16a34a&color=fff' }}" 
                     alt="Avatar">
            </div>
            <div class="profile-info">
                <div class="profile-status-badge">
                    <i class="bi {{ $stInfo['icon'] }}"></i>
                    <span>{{ $stInfo['label'] }}</span>
                </div>
                <h1 class="profile-name">{{ auth()->user()->name }}</h1>
                <div class="profile-meta text-white opacity-75 d-flex gap-3 small">
                    <span><i class="bi bi-hash"></i> ID #{{ auth()->user()->id }}</span>
                    <span><i class="bi bi-calendar2-check"></i> PPDB 2026/2027</span>
                </div>
            </div>
        </div>

        <!-- Quick Summary -->
        <div class="pendaftaran-summary-side">
            <div class="summary-item mb-3 border-bottom border-white border-opacity-10 pb-2">
                <div class="summary-label">Status Terkini</div>
                <div class="summary-val">{{ str_replace('_', ' ', strtoupper($statusFirst)) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Pendaftaran</div>
                <div class="summary-val">{{ $pendaftaranList->count() }} Jalur</div>
            </div>
        </div>
    </div>
</div>
