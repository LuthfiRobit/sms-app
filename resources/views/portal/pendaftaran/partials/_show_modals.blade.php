{{-- ══ MODAL: PREVIEW DOKUMEN (Custom Tailwind) ══ --}}
<div id="modalPreview"
     class="fixed inset-0 z-[9000] hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="preview-filename">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="tutupModalPreview()"></div>

    <div class="relative z-10 bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden w-full max-w-5xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-3 bg-surface-container-low border-b border-outline-variant flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">visibility</span>
                <h6 id="preview-filename" class="text-label-md font-bold text-on-surface">Preview</h6>
            </div>
            <button type="button" onclick="tutupModalPreview()"
                    class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div id="preview-body" class="flex-1 overflow-auto min-h-[200px]"></div>
    </div>
</div>

{{-- ══ MODAL: KONFIRMASI SUBMIT (Custom Tailwind) ══ --}}
<div id="modalSubmit"
     class="fixed inset-0 z-[9000] hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="modal-submit-title">

    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="tutupModalSubmit()"></div>

    <div class="relative z-10 bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden w-full max-w-[500px]">

        {{-- Header --}}
        <div class="flex items-start gap-4 p-6 bg-green-50 border-b-2 border-green-200">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center flex-shrink-0 shadow">
                <span class="material-symbols-outlined text-[24px] text-on-primary" style="font-variation-settings:'FILL' 1">send</span>
            </div>
            <div class="flex-1 min-w-0">
                <h5 id="modal-submit-title" class="text-headline-sm font-bold text-on-surface">Konfirmasi Pengiriman</h5>
                <p class="text-body-sm text-on-surface-variant mt-0.5">Pastikan semua data sudah terisi dengan benar.</p>
            </div>
            <button type="button" onclick="tutupModalSubmit()"
                    class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 flex flex-col gap-4">

            {{-- Checklist --}}
            <div class="flex flex-col gap-3">
                <div id="ck-formulir" class="flex items-center gap-3 bg-surface-container-low border border-outline-variant rounded-xl p-4 transition-colors">
                    <span id="ck-formulir-icon">
                        <span class="material-symbols-outlined text-[22px] text-on-surface-variant">circle</span>
                    </span>
                    <div class="flex-1">
                        <div class="text-label-md font-semibold text-on-surface">Data Formulir</div>
                        <div class="text-body-sm text-on-surface-variant" id="ck-formulir-sub">Memeriksa kelengkapan...</div>
                    </div>
                    <span class="text-label-md font-bold text-on-surface" id="ck-formulir-pct">0%</span>
                </div>
                <div id="ck-dokumen" class="flex items-center gap-3 bg-surface-container-low border border-outline-variant rounded-xl p-4 transition-colors">
                    <span id="ck-dokumen-icon">
                        <span class="material-symbols-outlined text-[22px] text-on-surface-variant">circle</span>
                    </span>
                    <div class="flex-1">
                        <div class="text-label-md font-semibold text-on-surface">Berkas Dokumen</div>
                        <div class="text-body-sm text-on-surface-variant" id="ck-dokumen-sub">Memeriksa dokumen wajib...</div>
                    </div>
                    <span class="text-label-md font-bold text-on-surface" id="ck-dokumen-pct">0%</span>
                </div>
            </div>

            {{-- Warning kekurangan --}}
            <div id="submit-warning-box" class="hidden flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                <span class="material-symbols-outlined text-amber-600 text-[22px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">warning</span>
                <div>
                    <strong class="block text-label-md font-bold text-amber-900 mb-1">Kelengkapan Belum Terpenuhi:</strong>
                    <ul class="text-body-sm text-amber-800 list-disc pl-4 flex flex-col gap-1" id="submit-kekurangan-list"></ul>
                </div>
            </div>

            {{-- Notice --}}
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                <span class="material-symbols-outlined text-blue-600 text-[20px] flex-shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">info</span>
                <p class="text-body-sm text-blue-800">Setelah dikirim, data pendaftaran <strong>akan dikunci</strong> dan tidak dapat diubah kembali.</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex gap-3 px-6 py-4 bg-surface-container-low border-t border-outline-variant">
            <button type="button" onclick="tutupModalSubmit()"
                    class="flex-1 border border-outline text-on-surface-variant text-label-md font-semibold py-3 px-4 rounded-xl hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Batal
            </button>
            <button type="button" id="btn-confirm-submit" onclick="doSubmit()"
                    class="flex-1 bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Kirim Sekarang
            </button>
        </div>
    </div>
</div>
