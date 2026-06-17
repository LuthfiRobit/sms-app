{{-- ══ SECTION 3 — TOMBOL AKSI ══ --}}
<div class="bg-surface-container-lowest rounded-2xl soft-shadow p-5 flex flex-wrap items-center justify-between gap-3" id="actions-bar">

    <a href="{{ route('ppdb.pendaftaran.index') }}"
       class="flex items-center gap-2 border border-outline text-on-surface-variant text-label-md font-semibold py-3 px-5 rounded-xl hover:bg-surface-container transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Kembali
    </a>

    <div class="flex flex-wrap items-center gap-3">

        @if($isDraft)
            <button type="button"
                    onclick="bukaModalSubmit()"
                    class="bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md font-semibold py-3 px-8 rounded-xl flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Kirim Pendaftaran
            </button>
        @endif

        @if(in_array($status, ['submit', 'verifikasi', 'lulus', 'daftar_ulang']))
            <a href="{{ route('ppdb.pembayaran.index', $pend?->id) }}"
               class="bg-amber-500 hover:bg-amber-600 text-white text-label-md font-semibold py-3 px-8 rounded-xl flex items-center gap-2 transition-colors shadow">
                <span class="material-symbols-outlined text-[18px]">credit_card</span>
                Bayar Pendaftaran
            </a>
        @endif

        @if($status === 'lulus')
            <a href="{{ route('ppdb.pengumuman.index') }}"
               class="bg-gradient-to-r from-green-600 to-green-700 text-white text-label-md font-semibold py-3 px-6 rounded-xl flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all">
                <span class="material-symbols-outlined text-[18px]">campaign</span>
                Lihat Pengumuman
            </a>
        @endif

        @if($status === 'daftar_ulang')
            <a href="{{ route('ppdb.daftar-ulang.index', $pend?->id) }}"
               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white text-label-md font-semibold py-3 px-6 rounded-xl flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all">
                <span class="material-symbols-outlined text-[18px]">autorenew</span>
                Daftar Ulang
            </a>
        @endif

    </div>
</div>
