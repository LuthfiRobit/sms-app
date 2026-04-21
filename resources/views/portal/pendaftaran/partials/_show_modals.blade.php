{{-- ══ MODAL: PREVIEW DOKUMEN ══ --}}
<div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fw-bold" id="preview-filename">Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2" id="preview-body" style="min-height:200px;"></div>
        </div>
    </div>
</div>

{{-- ══ MODAL: KONFIRMASI SUBMIT ══ --}}
<div class="modal fade" id="modalSubmit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content overflow-hidden">
            <div class="modal-header modal-submit-header border-0">
                <div class="modal-submit-icon"><i class="bi bi-send-fill"></i></div>
                <div>
                    <h5 class="modal-title fw-900 mb-0">Konfirmasi Pengiriman</h5>
                    <p class="text-muted mb-0" style="font-size:0.8rem;">Pastikan semua data sudah terisi dengan benar.</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Checklist --}}
                <div class="submit-checklist">
                    <div class="checklist-item" id="ck-formulir">
                        <span class="ck-icon" id="ck-formulir-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-grow-1">
                            <div class="ck-label">Data Formulir</div>
                            <div class="ck-sub" id="ck-formulir-sub">Memeriksa kelengkapan...</div>
                        </div>
                        <span class="ck-pct" id="ck-formulir-pct">0%</span>
                    </div>
                    <div class="checklist-item" id="ck-dokumen">
                        <span class="ck-icon" id="ck-dokumen-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-grow-1">
                            <div class="ck-label">Berkas Dokumen</div>
                            <div class="ck-sub" id="ck-dokumen-sub">Memeriksa dokumen wajib...</div>
                        </div>
                        <span class="ck-pct" id="ck-dokumen-pct">0%</span>
                    </div>
                </div>

                {{-- Warning kekurangan --}}
                <div id="submit-warning-box" class="submit-warning-box d-none">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>
                        <strong class="d-block mb-1">Kelengkapan Belum Terpenuhi:</strong>
                        <ul class="mb-0 ps-3" id="submit-kekurangan-list"></ul>
                    </div>
                </div>

                <div class="submit-notice">
                    <i class="bi bi-info-circle-fill me-2 fs-6"></i>
                    <span>Setelah dikirim, data pendaftaran <strong>akan dikunci</strong> dan tidak dapat diubah kembali.</span>
                </div>
            </div>
            <div class="modal-footer p-4 bg-light border-0 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: var(--radius-md);">
                    <i class="bi bi-arrow-left me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success flex-grow-1 py-2 fw-bold shadow-sm" id="btn-confirm-submit" onclick="doSubmit()" style="border-radius: var(--radius-md);">
                    <i class="bi bi-send-check-fill me-2"></i>Kirim Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ TOAST NOTIFIKASI ══ --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="ppdbToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="ppdbToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
