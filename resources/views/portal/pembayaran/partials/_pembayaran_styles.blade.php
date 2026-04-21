<style>
    /* ═══════════════════════════════════════════════════════════════════════
       💳 PEMBAYARAN STYLES
       Layout: Header card + Navigation (kiri) + Content (kanan)
       Mengadopsi design tokens dari refaktor_view.md
       ═══════════════════════════════════════════════════════════════════════ */

    /* ── PROFILE GREETING CARD (Header) ── */
    .profile-greeting-card {
        background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.5rem, 3vw, 2.5rem);
        box-shadow: var(--shadow-primary);
        position: relative;
        overflow: hidden;
        margin-bottom: clamp(1.5rem, 3vw, 2rem);
    }

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

    /* 📊 LEFT SIDE: Ringkasan Tagihan (profile-completion style) */
    .pendaftaran-summary-side {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.75rem;
        min-width: 340px;
        order: 1;
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

    /* Progress bar (bisa dipakai untuk sisa waktu jika ada, atau progress pembayaran) */
    .profil-progress-track {
        height: 10px;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.2);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .profil-progress-fill {
        height: 100%;
        background: #fff;
        border-radius: var(--radius-full);
        transition: width 0.8s;
        position: relative;
        overflow: hidden;
    }

    .profil-progress-fill::after {
        content: '';
        position: absolute;
        inset: 0;
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

    /* 👤 RIGHT SIDE: Profil Siswa */
    .profile-user-side {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        order: 2;
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

    /* ── Side Nav ── */
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

    /* ── CTA Button ── */
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

    /* ── Cards ── */
    .profil-card {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .profil-card-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
        border-bottom: 2px solid var(--color-border-light);
        font-weight: 700;
        color: var(--color-primary-hover);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .profil-card-body {
        padding: 1.75rem 2rem;
    }

    /* ── Biaya & Info Grid ── */
    .info-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-val {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--color-text);
    }

    .biaya-list {
        background: #f9fafb;
        border-radius: var(--radius-md);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .biaya-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .biaya-row:last-child {
        margin-bottom: 0;
    }

    .biaya-nama-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--color-text);
    }

    .biaya-nama-desc {
        font-size: 0.75rem;
        color: var(--color-text-muted);
    }

    .biaya-nominal {
        font-weight: 800;
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .biaya-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.25rem;
        border-top: 2px dashed var(--color-border-light);
    }

    .biaya-total-label {
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--color-text);
        text-transform: uppercase;
    }

    .biaya-total-nominal {
        font-size: 1.85rem;
        font-weight: 950;
        color: var(--color-primary);
        letter-spacing: -0.5px;
    }

    /* ── Methods ── */
    .btn-pay-midtrans {
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        padding: 0.875rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .btn-pay-midtrans:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        filter: brightness(1.15);
    }

    .rekening-item {
        display: flex;
        gap: 0.5rem;
        border: 2px solid var(--color-border);
        border-radius: var(--radius-md);
        background: #fff;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .rekening-item:hover {
        border-color: var(--color-primary);
        background: var(--color-primary-50);
        box-shadow: var(--shadow-sm);
    }

    .rek-bank {
        width: 50px;
        height: 32px;
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        font-weight: 900;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .rek-no {
        font-family: 'Courier New', monospace;
        font-weight: 800;
        font-size: 1rem;
        color: var(--color-text);
        letter-spacing: 1px;
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 4px;
    }

    /* ── Scrollable Rekening ── */
    .rekening-scroll-container {
        max-height: 420px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .rekening-scroll-container::-webkit-scrollbar {
        width: 6px;
    }

    .rekening-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .rekening-scroll-container::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .rekening-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }

    .file-drop-zone {
        position: relative;
        border: 2.5px dashed var(--color-primary);
        border-radius: var(--radius-lg);
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--color-primary-50);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
    }

    .file-drop-zone:hover {
        border-color: var(--color-primary-hover);
        background: #f0fdf4;
        transform: scale(1.01);
    }

    .file-drop-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    /* ── Status Banners ── */
    .status-banner {
        text-align: center;
        padding: 2.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
    }

    .status-banner--success {
        background: #f0fdf4;
        border: 2px solid #86efac;
        color: #15803d;
    }

    .status-banner--warning {
        background: #fffbeb;
        border: 2px solid #fde68a;
        color: #92400e;
    }

    /* Mobile */
    @media (min-width: 768px) {
        .border-md-end {
            border-right: 2px solid var(--color-border-light) !important;
        }
    }

    @media (max-width: 767.98px) {
        .border-mobile-bottom {
            border-bottom: 2px solid var(--color-border-light) !important;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
    }

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

    @media (max-width: 767.98px) {
        .profil-nav {
            flex-direction: row;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .profil-nav-item {
            flex: 1;
            flex-direction: column;
            text-align: center;
            font-size: 0.7rem;
            padding: 0.75rem 0.25rem;
        }

        .profil-nav-item i {
            font-size: 1.2rem;
        }
    }
</style>