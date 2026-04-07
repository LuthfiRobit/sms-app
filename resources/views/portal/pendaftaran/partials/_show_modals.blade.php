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
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content modal-submit">
            <div class="modal-header modal-submit-header">
                <div class="modal-submit-icon"><i class="bi bi-send-fill"></i></div>
                <div>
                    <h5 class="modal-title fw-800 mb-0">Konfirmasi Pengiriman</h5>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Pastikan semua data sudah benar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                {{-- Checklist --}}
                <div class="submit-checklist">
                    <div class="checklist-item" id="ck-formulir">
                        <span class="ck-icon" id="ck-formulir-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-1">
                            <div class="ck-label">Formulir</div>
                            <div class="ck-sub" id="ck-formulir-sub">Memuat...</div>
                        </div>
                        <span class="ck-pct" id="ck-formulir-pct"></span>
                    </div>
                    <div class="checklist-item" id="ck-dokumen">
                        <span class="ck-icon" id="ck-dokumen-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-1">
                            <div class="ck-label">Dokumen Persyaratan</div>
                            <div class="ck-sub" id="ck-dokumen-sub">Memuat...</div>
                        </div>
                        <span class="ck-pct" id="ck-dokumen-pct"></span>
                    </div>
                </div>

                {{-- Warning kekurangan --}}
                <div id="submit-warning-box" class="submit-warning-box d-none">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    <div>
                        <strong>Ada kelengkapan yang belum terpenuhi:</strong>
                        <ul class="mb-0 mt-1" id="submit-kekurangan-list" style="font-size:.82rem;padding-left:16px;">
                        </ul>
                    </div>
                </div>

                <div class="submit-notice">
                    <i class="bi bi-shield-check text-info me-2"></i>
                    <small>Setelah dikirim, data formulir <strong>tidak dapat diubah</strong>. Periksa kembali sebelum
                        mengirim.</small>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Kembali Periksa
                </button>
                <button type="button" class="btn btn-success px-4 fw-bold" id="btn-confirm-submit" onclick="doSubmit()">
                    <i class="bi bi-send me-2"></i>Ya, Kirim Sekarang
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
