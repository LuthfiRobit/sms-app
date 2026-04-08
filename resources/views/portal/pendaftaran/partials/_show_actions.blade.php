{{-- ══ SECTION 3 — TOMBOL AKSI ══ --}}

@if($isDraft)
{{-- Status DRAFT: tombol submit --}}
<div class="show-actions-bar" id="actions-bar">
    <div class="show-actions-inner">
        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-light px-4">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
        <button type="button" class="btn btn-success btn-lg px-5 fw-bold" onclick="bukaModalSubmit()">
            <i class="bi bi-send me-2"></i>Kirim Pendaftaran
        </button>
    </div>
</div>

@else
{{-- Status SUDAH SUBMIT atau lebih: tombol Bayar + Kembali --}}
<div class="show-actions-bar" id="actions-bar">
    <div class="show-actions-inner">
        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-light px-4">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>

        @if(in_array($status, ['submit', 'verifikasi', 'lulus', 'daftar_ulang']))
        <a href="{{ route('ppdb.pembayaran.index', $pend?->id) }}"
           class="btn btn-pay-action btn-lg px-5 fw-bold">
            <i class="bi bi-credit-card-2-front me-2"></i>Bayar Pendaftaran
        </a>
        @endif

        @if($status === 'lulus')
        <a href="{{ route('ppdb.pengumuman.index') }}"
           class="btn btn-success btn-lg px-4 fw-bold">
            <i class="bi bi-megaphone me-2"></i>Lihat Pengumuman
        </a>
        @endif

        @if($status === 'daftar_ulang')
        <a href="{{ route('ppdb.daftar-ulang.index', $pend?->id) }}"
           class="btn btn-primary btn-lg px-4 fw-bold">
            <i class="bi bi-arrow-repeat me-2"></i>Daftar Ulang
        </a>
        @endif
    </div>
</div>
@endif
