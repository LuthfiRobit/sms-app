<div class="profile-greeting-card">
    <div class="profile-greeting-inner">
        <!-- Profil Siswa -->
        <div class="profile-user-side">
            <div class="foto-wrapper">
                <img src="{{ $peserta && $peserta->foto ? Storage::url($peserta->foto) : 'https://ui-avatars.com/api/?name='.urlencode($namaPeserta).'&background=16a34a&color=fff' }}" 
                     alt="Avatar">
            </div>
            <div class="profile-info">
                <div class="profile-status-badge">
                    <i class="bi bi-person-check-fill"></i>
                    <span>{{ $statusPendaftaran === 'siswa_tetap' ? 'SISWA AKTIF' : 'CALON SISWA' }}</span>
                </div>
                <h1 class="profile-name">{{ $namaPeserta }}</h1>
                <div class="profile-meta text-white opacity-75 d-flex gap-3 small">
                    <span><i class="bi bi-hash"></i> {{ $noPendaftaran }}</span>
                    <span><i class="bi bi-calendar2-check"></i> PPDB {{ $namaTahun }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Summary -->
        <div class="pendaftaran-summary-side">
            <div class="summary-item mb-3 border-bottom border-white border-opacity-10 pb-2">
                <div class="summary-label">Status Daftar Ulang</div>
                <div class="summary-val">
                    @if($statusPendaftaran === 'siswa_tetap')
                        SELESAI
                    @elseif($statusPendaftaran === 'daftar_ulang')
                        DIPROSES
                    @else
                        DIPERLUKAN
                    @endif
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Jalur Seleksi</div>
                <div class="summary-val">{{ $namaJalur }}</div>
            </div>
        </div>
    </div>
</div>
