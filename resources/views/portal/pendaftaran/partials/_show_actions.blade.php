{{-- ══ SECTION 3 — TOMBOL AKSI ══ --}}
<div class="show-actions-bar {{ $isDraft ? '' : 'd-none' }}" id="actions-bar">
    @if($isDraft)
        <div class="show-actions-inner">
            <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-light px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <button type="button" class="btn btn-success btn-lg px-5 fw-bold" onclick="bukaModalSubmit()">
                <i class="bi bi-send me-2"></i>Kirim Pendaftaran
            </button>
        </div>
    @endif
</div>
