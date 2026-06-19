<div class="pay-hero">
    <div class="pay-hero-inner">

        {{-- LEFT: Amount --}}
        <div class="pay-hero-amount-side">
            <div class="pay-hero-label">Total Tagihan</div>
            <div class="pay-hero-amount">{{ $nominalFmt }}</div>
            <div class="pay-hero-meta">
                <span class="pay-hero-chip">
                    <i class="bi bi-diagram-3-fill"></i>{{ $namaJalur }}
                </span>
                <span class="pay-hero-chip">
                    <i class="bi bi-upc-scan"></i><span class="font-monospace">{{ $noPendaftaran }}</span>
                </span>
            </div>
        </div>

        {{-- CENTER: Status --}}
        <div class="pay-hero-status-wrap">
            @if(isset($pembayaran) && $pembayaran)
                @php
                    $pillMap = [
                        'paid'    => ['class' => 'paid',    'icon' => 'bi-check-circle-fill', 'text' => 'Lunas'],
                        'pending' => ['class' => 'pending', 'icon' => 'bi-hourglass-split',   'text' => 'Menunggu'],
                        'expired' => ['class' => 'failed',  'icon' => 'bi-clock-history',     'text' => 'Kedaluwarsa'],
                        'failed'  => ['class' => 'failed',  'icon' => 'bi-x-circle-fill',     'text' => 'Gagal'],
                    ];
                    $pill = $pillMap[$pembayaran->status] ?? ['class' => 'pending', 'icon' => 'bi-circle', 'text' => ucfirst($pembayaran->status)];
                @endphp
                <div class="pay-status-pill {{ $pill['class'] }}">
                    <i class="bi {{ $pill['icon'] }}"></i>
                    {{ $pill['text'] }}
                </div>
            @else
                <div class="pay-status-pill unpaid">
                    <i class="bi bi-clock"></i>Belum Bayar
                </div>
            @endif

            <div class="pay-progress-track">
                <div class="pay-progress-fill"
                     style="width: {{ (isset($pembayaran) && $pembayaran?->status === 'paid') ? '100%' : ((isset($pembayaran) && $pembayaran) ? '60%' : '20%') }}">
                </div>
            </div>
        </div>

        {{-- RIGHT: User --}}
        @php $user = auth()->user(); @endphp
        <div class="pay-hero-user">
            <img class="pay-avatar"
                 src="{{ $peserta?->foto
                    ? asset('storage/' . $peserta->foto)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=100&background=00843D&color=fff&bold=true&rounded=true' }}"
                 alt="{{ $user->name }}">
            <div class="pay-user-info">
                <div class="pay-user-name">{{ $user->name }}</div>
                <div class="pay-user-email">
                    <i class="bi bi-envelope-fill me-1"></i>{{ $user->email }}
                </div>
                <span class="pay-hero-chip" style="font-size:.72rem">
                    <i class="bi bi-circle-fill" style="font-size:.45rem"></i>Akun Aktif
                </span>
            </div>
        </div>

    </div>
</div>
