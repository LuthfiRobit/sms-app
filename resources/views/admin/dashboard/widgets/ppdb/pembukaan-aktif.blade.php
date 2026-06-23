<div class="d-flex flex-column gap-3 h-100">

    {{-- Pembayaran Manual Menunggu --}}
    @if($pendingPayment->isNotEmpty())
    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title text-warning">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Konfirmasi Transfer Manual
                </div>
                <div class="dash-card-sub">Bukti upload menunggu verifikasi</div>
            </div>
            <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-sm btn-outline-warning">
                Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="dash-card-body p-0">
            <ul class="dash-pay-list">
                @foreach($pendingPayment as $pay)
                <li class="dash-pay-item">
                    <div class="dash-pay-avatar">
                        {{ strtoupper(substr($pay->pendaftaran?->peserta?->nama_lengkap ?? 'P', 0, 1)) }}
                    </div>
                    <div class="dash-pay-info">
                        <div class="dash-pay-name">{{ $pay->pendaftaran?->peserta?->nama_lengkap ?? '—' }}</div>
                        <div class="dash-pay-meta">
                            Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            &middot; {{ $pay->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('admin.pembayaran.show', $pay->id) }}"
                       class="btn btn-xs btn-warning text-dark fw-bold">Verifikasi</a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Pembukaan PPDB Aktif --}}
    <div class="dash-card flex-grow-1">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">PPDB Sedang Buka</div>
                <div class="dash-card-sub">{{ $pembukaanAktif->count() }} pembukaan aktif</div>
            </div>
        </div>
        <div class="dash-card-body p-0">
            @if($pembukaanAktif->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                    Tidak ada PPDB yang sedang buka
                </div>
            @else
                <ul class="dash-pembukaan-list">
                    @foreach($pembukaanAktif as $pb)
                    <li class="dash-pembukaan-item">
                        <div class="dash-pembukaan-jenis">
                            <span class="badge" style="background:var(--color-primary);font-size:.65rem">
                                {{ $pb->lembaga?->jenis ?? '?' }}
                            </span>
                        </div>
                        <div class="dash-pembukaan-info">
                            <div class="dash-pembukaan-nama">{{ $pb->lembaga?->nama ?? '—' }}</div>
                            <div class="dash-pembukaan-meta">
                                {{ $pb->jalur_pendaftaran_count }} jalur
                                @if($pb->selesai)
                                    &middot; tutup {{ \Carbon\Carbon::parse($pb->selesai)->isoFormat('D MMM') }}
                                @endif
                            </div>
                        </div>
                        <span class="dash-buka-badge">Buka</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>
