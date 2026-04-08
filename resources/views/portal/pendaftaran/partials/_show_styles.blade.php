<style>
    /* ── Breadcrumb ── */
    .breadcrumb-ppdb {
        background: none;
        padding: 0;
        margin: 0;
        font-size: .8125rem;
    }

    .breadcrumb-ppdb .breadcrumb-item a {
        color: #16a34a;
        text-decoration: none;
    }

    .breadcrumb-ppdb .breadcrumb-item.active {
        color: #6b7280;
    }

    /* ── Page Header ── */
    .show-header {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
    }

    .show-no-label {
        display: block;
        font-size: .67rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .show-no-value {
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
        font-weight: 800;
        color: #111827;
        letter-spacing: 1.5px;
    }

    .show-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 8px;
    }

    .show-meta-item {
        display: inline-flex;
        align-items: center;
        font-size: .8375rem;
        color: #374151;
    }

    /* Progress Block */
    .show-progress-block {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #f3f4f6;
    }

    .show-progress-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .show-progress-title {
        font-size: .825rem;
        font-weight: 700;
        color: #374151;
    }

    .show-progress-pct {
        font-size: 1rem;
        font-weight: 800;
        color: #16a34a;
    }

    .show-progress-track {
        height: 8px;
        background: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .show-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #16a34a, #22c55e);
        border-radius: 4px;
        transition: width .8s ease;
    }

    .show-progress-subs {
        display: flex;
        gap: 20px;
        font-size: .78rem;
        color: #6b7280;
    }

    .show-progress-subs strong {
        color: #111827;
    }

    /* Status Boxes */
    .status-box {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 0;
    }

    .status-box--info {
        background: #eff6ff;
        border: 1.5px solid #93c5fd;
        color: #1e40af;
    }

    .status-box--success {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        color: #15803d;
    }

    .status-box--warning {
        background: #fffbeb;
        border: 1.5px solid #fcd34d;
        border-left: 5px solid #f59e0b;
        color: #92400e;
    }

    /* ── Show Section ── */
    .show-section {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
    }

    .show-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .show-section-title {
        font-weight: 700;
        color: #111827;
        font-size: .9rem;
        display: flex;
        align-items: center;
    }

    .show-section-body {
        padding: 20px 24px;
    }

    /* ── Autosave ── */
    .autosave-indicator {
        font-size: .75rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .autosave-indicator.saving {
        color: #f59e0b;
    }

    .autosave-indicator.saved {
        color: #16a34a;
    }

    .autosave-indicator.error {
        color: #ef4444;
    }

    /* ── Form Fields ── */
    .form-field-wrapper {
        margin-bottom: 18px;
    }

    .field-statis {
        background: #fafafa;
        border: 1px dashed #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .field-statis-val {
        font-size: .9rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }

    .field-statis-hint {
        font-size: .72rem;
        color: #9ca3af;
    }

    .field-statis-hint a {
        color: #16a34a;
    }

    .readonly-field {
        background: #f9fafb !important;
        color: #6b7280;
        cursor: not-allowed;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86efac;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
    }

    /* ── Dokumen Grid ── */
    .dokumen-grid {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .dok-card {
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .dok-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #f3f4f6;
        flex-wrap: wrap;
    }

    .dok-nama {
        font-size: .875rem;
        font-weight: 700;
        color: #111827;
    }

    .dok-keterangan {
        font-size: .78rem;
        color: #9ca3af;
        padding: 8px 16px 0;
    }

    /* Progress upload */
    .dok-upload-progress {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
    }

    .dok-progress-track {
        flex: 1;
        height: 5px;
        background: #f3f4f6;
        border-radius: 3px;
        overflow: hidden;
    }

    .dok-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #16a34a, #22c55e);
        border-radius: 3px;
        transition: width .2s;
    }

    .dok-progress-label {
        font-size: .75rem;
        font-weight: 700;
        color: #16a34a;
        min-width: 32px;
        text-align: right;
    }

    /* File row */
    .dok-file-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dok-file-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dok-file-name {
        font-size: .8375rem;
        font-weight: 600;
        color: #111827;
    }

    .dok-file-size {
        font-size: .72rem;
        color: #9ca3af;
    }

    .dok-file-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .dok-empty-readonly {
        padding: 14px 16px;
        font-size: .8rem;
        color: #9ca3af;
    }

    /* Dropzone */
    .dok-dropzone {
        margin: 12px 16px;
        border: 2px dashed #d1fae5;
        border-radius: 10px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }

    .dok-dropzone:hover,
    .dok-dropzone.dragover {
        border-color: #16a34a;
        background: #f0fdf4;
    }

    .dok-drop-icon {
        font-size: 2rem;
        color: #86efac;
        display: block;
        margin-bottom: 8px;
    }

    .dok-drop-text {
        font-size: .8375rem;
        color: #374151;
        margin-bottom: 4px;
    }

    .dok-drop-hint {
        font-size: .72rem;
        color: #9ca3af;
    }

    .file-input-hidden {
        display: none !important;
    }

    /* ── Actions Bar ── */
    .show-actions-bar {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e5e7eb;
        margin: 0 -20px;
        padding: 14px 20px;
    }

    .show-actions-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 100%;
    }

    @media(max-width:576px) {
        .show-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0;
        }
    }

    /* ── Modal Submit ── */
    .modal-submit {
        border-radius: 18px;
        overflow: hidden;
        border: none;
        box-shadow: 0 24px 64px rgba(0, 0, 0, .15);
    }

    .modal-submit-header {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-bottom: 1px solid #bbf7d0;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .modal-submit-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #16a34a, #059669);
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        flex-shrink: 0;
    }

    .submit-checklist {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 10px;
        padding: 12px 14px;
    }

    .ck-icon {
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .ck-label {
        font-size: .85rem;
        font-weight: 700;
        color: #111827;
    }

    .ck-sub {
        font-size: .75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .ck-pct {
        font-size: .875rem;
        font-weight: 800;
        color: #16a34a;
        margin-left: auto;
    }

    .submit-warning-box {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 12px;
        font-size: .82rem;
        color: #92400e;
    }

    .submit-notice {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: .79rem;
        color: #1e40af;
        margin-top: 4px;
    }

    .flex-1 {
        flex: 1;
    }

    /* ── Tombol Bayar Pendaftaran ── */
    .btn-pay-action {
        background: linear-gradient(135deg, #0f766e, #0d9488);
        color: #fff;
        border: none;
        border-radius: 12px;
        transition: all .2s ease;
        letter-spacing: .3px;
    }

    .btn-pay-action:hover {
        background: linear-gradient(135deg, #0d6b64, #0b8177);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(13, 148, 136, .35);
    }

    .btn-pay-action:active {
        transform: translateY(0);
    }

    /* ── Status box pembayaran ── */
    .status-box--payment {
        background: linear-gradient(135deg, #f0fdfa, #ccfbf1);
        border: 1.5px solid #5eead4;
        color: #0f766e;
    }

    .status-box--payment strong {
        color: #0f766e;
    }
</style>
