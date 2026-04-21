<div class="profile-greeting-card">
    <div class="profile-greeting-inner">

        {{-- LEFT SIDE: Ringkasan Tagihan --}}
        <div class="pendaftaran-summary-side">
            <div class="completion-header">
                <div class="completion-label-wrapper">
                    <span class="completion-label">Total Tagihan</span>
                    <span class="completion-percent">{{ $nominalFmt }}</span>
                </div>
                <div class="text-end">
                    <span class="completion-label d-block">Status</span>
                    @if(isset($pembayaran) && $pembayaran)
                        <span class="badge bg-{{ $sp['color'] }} rounded-pill px-3">
                            <i class="bi {{ $sp['icon'] }} me-1"></i>{{ $sp['label'] }}
                        </span>
                    @else
                        <span class="badge bg-warning rounded-pill px-3">
                            <i class="bi bi-hourglass-split me-1"></i>Belum Bayar
                        </span>
                    @endif
                </div>
            </div>

            <div class="profil-progress-track">
                <div class="profil-progress-fill" style="width: {{ (isset($pembayaran) && $pembayaran->status === 'paid') ? '100%' : '50%' }}"></div>
            </div>

            <div class="jalur-info-row d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 text-white opacity-90 small fw-600">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>{{ $namaJalur }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 text-white opacity-90 small fw-600">
                    <i class="bi bi-upc-scan"></i>
                    <span class="font-monospace">{{ $noPendaftaran }}</span>
                </div>
            </div>
        </div>

        {{-- RIGHT SIDE: Profil Siswa --}}
        @php $user = auth()->user(); @endphp
        <div class="profile-user-side">
            <div class="foto-wrapper">
                <img src="{{ $peserta?->foto
                    ? asset('storage/' . $peserta->foto)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=100&background=16a34a&color=fff&bold=true&rounded=true' }}"
                     alt="Foto {{ $user->name }}">
            </div>

            <div class="profile-info">
                <h1 class="profile-name">{{ $user->name }}</h1>

                <div class="profile-email">
                    <i class="bi bi-envelope-fill me-1"></i>
                    <span>{{ $user->email }}</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="profile-status-badge">
                        <i class="bi bi-circle-fill text-white opacity-75"></i>
                        Akun Aktif
                    </span>
                    <span class="badge bg-{{ $st['color'] }} px-3 py-2" style="border-radius: var(--radius-full); border: 1.5px solid rgba(255,255,255,0.3);">
                        <i class="bi {{ $st['icon'] ?? 'bi-person-badge' }} me-1"></i>{{ $st['label'] }}
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
