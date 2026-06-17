{{-- ══ STATUS NOTIFICATION BOXES ══ --}}
@if($isDraft && $pend?->catatan_verifikasi)
    <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-amber-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">update</span>
        <div>
            <strong class="block text-label-md font-bold text-amber-900 mb-1">Pendaftaran Dikembalikan untuk Diperbaiki</strong>
            <p class="text-body-sm text-amber-800">Catatan admin: <em>{{ $pend->catatan_verifikasi }}</em></p>
        </div>
    </div>

@elseif($isDraft)
    <div class="flex items-start gap-4 bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-blue-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">info</span>
        <div>
            <strong class="block text-label-md font-bold text-blue-900 mb-1">Lengkapi Formulir dan Dokumen</strong>
            <p class="text-body-sm text-blue-800">Setelah semua data lengkap, klik tombol <strong>Kirim Pendaftaran</strong> di bawah.</p>
        </div>
    </div>

@elseif($status === 'submit')
    <div class="flex items-start gap-4 bg-green-50 border border-green-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-green-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">send</span>
        <div>
            <strong class="block text-label-md font-bold text-green-900 mb-1">Pendaftaran Berhasil Dikirim</strong>
            <p class="text-body-sm text-green-800">Sedang menunggu verifikasi oleh admin. Pantau halaman ini secara berkala.</p>
        </div>
    </div>
    <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-amber-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">credit_card</span>
        <div>
            <strong class="block text-label-md font-bold text-amber-900 mb-1">Lakukan Pembayaran Biaya Registrasi</strong>
            <p class="text-body-sm text-amber-800">Sambil menunggu verifikasi, Anda sudah dapat melakukan pembayaran biaya registrasi. Gunakan tombol <strong>Bayar Pendaftaran</strong> di bawah.</p>
        </div>
    </div>

@elseif($status === 'verifikasi')
    <div class="flex items-start gap-4 bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-blue-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">manage_search</span>
        <div>
            <strong class="block text-label-md font-bold text-blue-900 mb-1">Sedang Diverifikasi</strong>
            <p class="text-body-sm text-blue-800">Admin sedang memeriksa kelengkapan data dan dokumen Anda.</p>
        </div>
    </div>
    <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <span class="material-symbols-outlined text-amber-600 text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">credit_card</span>
        <div>
            <strong class="block text-label-md font-bold text-amber-900 mb-1">Segera Selesaikan Pembayaran</strong>
            <p class="text-body-sm text-amber-800">Selesaikan pembayaran biaya registrasi agar proses seleksi dapat berjalan. Gunakan tombol <strong>Bayar Pendaftaran</strong> di bawah.</p>
        </div>
    </div>

@elseif(in_array($status, ['lulus', 'daftar_ulang', 'siswa_tetap', 'tidak_lulus']))
    <div class="flex items-start gap-4 {{ $status === 'tidak_lulus' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }} border rounded-2xl p-5">
        <span class="material-symbols-outlined {{ $status === 'tidak_lulus' ? 'text-red-500' : 'text-green-600' }} text-[24px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">{{ $status === 'tidak_lulus' ? 'cancel' : 'emoji_events' }}</span>
        <div>
            <strong class="block text-label-md font-bold {{ $status === 'tidak_lulus' ? 'text-red-900' : 'text-green-900' }} mb-1">{{ $st['label'] }}</strong>
            <p class="text-body-sm {{ $status === 'tidak_lulus' ? 'text-red-800' : 'text-green-800' }}">
                <a href="{{ route('ppdb.pengumuman.index') }}" class="font-semibold underline">Lihat Pengumuman Resmi →</a>
            </p>
        </div>
    </div>
@endif
