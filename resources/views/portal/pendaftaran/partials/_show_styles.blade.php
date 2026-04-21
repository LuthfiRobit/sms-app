<style>
    /* ═══════════════════════════════════════════════════════════════════════
       📝 PENDAFTARAN SHOW STYLES
       Layout: Header card + Navigation (kiri) + Content (kanan)
       Mengadopsi design tokens dari portal.blade.php dan profil.blade.php
       ═══════════════════════════════════════════════════════════════════════ */

    /* ── BREADCRUMB ── */
    .breadcrumb-ppdb {
        background: none;
        padding: 0;
        margin: 0;
        font-size: .8125rem;
    }

    .breadcrumb-ppdb .breadcrumb-item a {
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-ppdb .breadcrumb-item.active {
        color: var(--color-text-muted);
    }

    /* ─────────────────────────────────────────────────────────────────────
       🎯 PROFILE GREETING CARD (Header)
       ───────────────────────────────────────────────────────────────────── */
    .profile-greeting-card {
        background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.5rem, 3vw, 2.5rem);
        box-shadow: var(--shadow-primary);
        position: relative;
        overflow: hidden;
        margin-bottom: clamp(1.5rem, 3vw, 2rem);
    }

    /* Decorative circles */
    .profile-greeting-card::before,
    .profile-greeting-card::after {
        content: '';
        position: absolute;
        border-radius: var(--radius-full);
        opacity: 0.08;
        pointer-events: none;
        background: #fff;
    }

    .profile-greeting-card::before {
        width: 320px;
        height: 320px;
        top: -120px;
        right: -60px;
    }

    .profile-greeting-card::after {
        width: 200px;
        height: 200px;
        bottom: -80px;
        right: 100px;
    }

    .profile-greeting-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    /* 👤 RIGHT SIDE: Profil Siswa (Reverse Order by flex) */
    .profile-user-side {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        order: 2;
        /* Di kanan */
    }

    .foto-wrapper {
        width: 100px;
        height: 100px;
        flex-shrink: 0;
    }

    .foto-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: var(--radius-full);
        box-shadow: 0 0 0 4px #fff, 0 0 0 6px rgba(255, 255, 255, 0.3);
    }

    .profile-info {
        flex: 1;
        min-width: 0;
    }

    .profile-name {
        font-size: clamp(1.25rem, 2.5vw, 1.75rem);
        font-weight: 900;
        color: #fff;
        margin: 0 0 0.5rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        line-height: 1.2;
    }

    .profile-email {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.375rem 0.875rem;
        border-radius: var(--radius-full);
        border: 1.5px solid rgba(255, 255, 255, 0.3);
    }

    /* 📊 LEFT SIDE: Info Pendaftaran (profile-completion style) */
    .pendaftaran-summary-side {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        min-width: 320px;
        order: 1;
        /* Di kiri */
    }

    .completion-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 0.75rem;
    }

    .completion-label-wrapper {
        display: flex;
        flex-direction: column;
    }

    .completion-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.85);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.25rem;
    }

    .pendaftaran-no-val {
        font-family: 'Courier New', monospace;
        font-size: 1.125rem;
        font-weight: 900;
        color: #fff;
        letter-spacing: 1px;
    }

    .completion-percent {
        font-size: 1.75rem;
        font-weight: 950;
        color: #fff;
        line-height: 1;
    }

    .profil-progress-track {
        height: 10px;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.2);
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .profil-progress-fill {
        height: 100%;
        background: #fff;
        border-radius: var(--radius-full);
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    /* Shimmer effect pada progres bar */
    .profil-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: progressShimmer 2s infinite;
    }

    @keyframes progressShimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .jalur-info-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
    }

    .jalur-info-row i {
        font-size: 1rem;
        color: #fff;
    }

    /* 🧩 MAIN CONTENT AREA - Sidebar + Cards */
    .profil-sidebar {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        padding: 1.25rem;
        position: sticky;
        top: 1rem;
    }

    .profil-nav {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .profil-nav-item {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        border: 1.5px solid transparent;
        background: transparent;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text);
        text-decoration: none;
        transition: all var(--transition-fast);
        gap: 0.75rem;
    }

    .profil-nav-item:hover {
        background: var(--color-primary-50);
        color: var(--color-primary-hover);
        transform: translateX(4px);
    }

    .profil-nav-item.active {
        background: linear-gradient(135deg, var(--color-primary-light), #bbf7d0);
        color: var(--color-primary-hover);
        font-weight: 700;
        border-color: var(--color-primary);
        box-shadow: var(--shadow-sm);
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

    /* ── CTA Button (Sync with Profile) ── */
    .btn-daftar {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.875rem 1.25rem;
        width: 100%;
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-daftar:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(22, 163, 74, 0.35);
    }

    .btn-daftar:active {
        transform: translateY(0);
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

    /* ── STATUS BOXES ── */
    .status-box {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.25rem;
    }

    .status-box--info {
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        color: #1e40af;
    }

    .status-box--success {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        color: #15803d;
    }

    .status-box--warning {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        color: #92400e;
    }

    .status-box--payment {
        background: #f0fdfa;
        border: 1.5px solid #99f6e4;
        color: #0f766e;
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
        bottom: 1rem;
        z-index: 100;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(12px);
        border: 1.5px solid #e5e7eb;
        border-radius: var(--radius-lg);
        padding: 12px 20px;
        margin-top: 2rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .show-actions-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    /* Standardizing all buttons to be smaller and uniform */
    .show-actions-inner .btn {
        padding: 0.625rem 1.5rem !important;
        font-size: 0.875rem !important;
        border-radius: var(--radius-md) !important;
        font-weight: 700 !important;
        height: auto !important;
    }

    @media(max-width:576px) {
        .show-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0;
            border-radius: 0;
            border-left: none;
            border-right: none;
            border-bottom: none;
        }
    }

    /* ── Modal Custom Styling ── */
    .modal-content {
        border: none;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
    }

    .modal-submit-header {
        padding: 1.5rem 1.75rem;
        background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
        border-bottom: 2px solid var(--color-border-light);
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .modal-submit-icon {
        width: 52px;
        height: 52px;
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .submit-checklist {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        border: 1.5px solid var(--color-border);
        background: var(--color-surface);
        transition: all var(--transition-fast);
    }

    .checklist-item:hover {
        border-color: var(--color-primary);
        background: var(--color-primary-50);
        transform: translateY(-2px);
    }

    .ck-icon {
        font-size: 1.35rem;
    }

    .ck-label {
        font-weight: 800;
        font-size: 0.9rem;
        color: var(--color-text);
        line-height: 1.2;
    }

    .ck-sub {
        font-size: 0.75rem;
        color: var(--color-text-muted);
    }

    .ck-pct {
        font-weight: 900;
        font-size: 0.95rem;
        color: var(--color-primary);
        margin-left: auto;
    }

    .submit-warning-box {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        color: #92400e;
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }

    .submit-notice {
        background: var(--color-primary-50);
        border: 1px solid var(--color-primary-light);
        border-radius: var(--radius-md);
        padding: 0.875rem 1.125rem;
        color: var(--color-primary-hover);
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .profile-greeting-inner {
            flex-direction: column;
            align-items: stretch;
        }

        .pendaftaran-summary-side,
        .profile-user-side {
            width: 100%;
            order: unset;
        }

        .profile-user-side {
            justify-content: center;
        }
    }

    /* Mobile: Horizontal scrolling nav (Sync with Profile) */
    @media (max-width: 767.98px) {
        .profil-sidebar {
            position: static;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .profil-nav {
            flex-direction: row;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: stretch;
        }

        .profil-nav-item {
            flex: 1 1 0;
            flex-direction: column;
            justify-content: center;
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
            gap: 0.5rem;
            transform: none !important;
            text-align: center;
            min-width: 0;
        }

        .profil-nav-item i {
            width: auto;
            font-size: 1.25rem;
        }

        .nav-label {
            font-size: 0.7rem;
            white-space: nowrap;
            text-align: center;
        }

        .tab-dot {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .profile-user-side {
            flex-direction: column;
            text-align: center;
        }

        .show-section-body {
            padding: 1.25rem 1rem;
        }

        .pendaftaran-summary-side {
            min-width: 0;
        }
    }
</style>