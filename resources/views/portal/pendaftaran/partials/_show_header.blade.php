{{-- PROFILE GREETING CARD (Header Pendaftaran) --}}
<div class="profile-greeting-card">
    <div class="profile-greeting-inner">

        {{-- LEFT SIDE: Kelengkapan & Informasi Pendaftaran --}}
        <div class="pendaftaran-summary-side">
            <div class="completion-header">
                <div class="completion-label-wrapper">
                    <span class="completion-label">No. Pendaftaran</span>
                    <span class="pendaftaran-no-val">{{ $pend?->no_pendaftaran }}</span>
                </div>
                <div class="text-end">
                    <span class="completion-label d-block">Kelengkapan</span>
                    <span class="completion-percent" id="main-pct">{{ $totalPersen }}%</span>
                </div>
            </div>

            <div class="profil-progress-track">
                <div class="profil-progress-fill" id="main-bar" style="width:{{ $totalPersen }}%" role="progressbar"
                    aria-valuenow="{{ $totalPersen }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>

            <div class="jalur-info-row">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>{{ $jalur?->nama ?? '—' }}</span>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>{{ $fmtTgl($pend?->tanggal_daftar ?? $pend?->created_at) }}</span>
                </div>
            </div>

            {{-- Hidden stats for JS validation --}}
            <div class="d-none">
                <span id="formulir-pct">{{ $prog['formulir']['persen'] }}%</span>
                <span id="dokumen-pct">{{ $prog['dokumen']['persen'] }}%</span>
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
                    <span class="badge bg-{{ $st['color'] }} px-3 py-2"
                        style="border-radius: var(--radius-full); border: 1.5px solid rgba(255,255,255,0.3);">
                        <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>