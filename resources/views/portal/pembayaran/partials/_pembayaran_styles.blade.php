<style>
    /* ═══════════════════════════════════════════════════════
       💳 PEMBAYARAN — Design Refresh
       ═══════════════════════════════════════════════════════ */

    /* ── HERO HEADER ── */
    .pay-hero {
        background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 60%, #00a84f 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.5rem, 3vw, 2.25rem) clamp(1.5rem, 3vw, 2.5rem);
        box-shadow: var(--shadow-primary);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    .pay-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='0.06' stroke-width='0.8'%3E%3Cpath d='M30 2L58 30L30 58L2 30Z'/%3E%3Cpath d='M30 15L45 30L30 45L15 30Z'/%3E%3Cline x1='0' y1='30' x2='60' y2='30'/%3E%3Cline x1='30' y1='0' x2='30' y2='60'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }

    .pay-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    /* LEFT: Amount + Meta */
    .pay-hero-amount-side {
        flex: 1;
        min-width: 220px;
    }

    .pay-hero-label {
        font-size: 0.68rem;
        font-weight: 800;
        color: rgba(255,255,255,.7);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 0.35rem;
    }

    .pay-hero-amount {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 950;
        color: #fff;
        line-height: 1;
        margin-bottom: 0.75rem;
        letter-spacing: -1px;
    }

    .pay-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .pay-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.3rem 0.75rem;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.22);
        backdrop-filter: blur(6px);
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255,255,255,.92);
    }

    /* CENTER: Status pill */
    .pay-hero-status-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .pay-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.25rem;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 700;
        border: 2px solid rgba(255,255,255,.3);
    }

    .pay-status-pill.unpaid {
        background: rgba(255,230,100,.2);
        color: #fde68a;
        border-color: rgba(255,230,100,.3);
    }

    .pay-status-pill.paid {
        background: rgba(134,239,172,.2);
        color: #86efac;
        border-color: rgba(134,239,172,.3);
    }

    .pay-status-pill.pending {
        background: rgba(251,191,36,.2);
        color: #fbbf24;
        border-color: rgba(251,191,36,.3);
    }

    .pay-status-pill.failed {
        background: rgba(248,113,113,.2);
        color: #f87171;
        border-color: rgba(248,113,113,.3);
    }

    /* Progress bar */
    .pay-progress-track {
        height: 6px;
        background: rgba(255,255,255,.18);
        border-radius: var(--radius-full);
        overflow: hidden;
        margin-top: 0.75rem;
        width: 120px;
    }

    .pay-progress-fill {
        height: 100%;
        background: #fff;
        border-radius: var(--radius-full);
        transition: width .8s cubic-bezier(0.4,0,0.2,1);
    }

    /* RIGHT: User info */
    .pay-hero-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pay-avatar {
        width: 72px;
        height: 72px;
        flex-shrink: 0;
        border-radius: var(--radius-full);
        object-fit: cover;
        box-shadow: 0 0 0 3px #fff, 0 0 0 5px rgba(255,255,255,.25);
    }

    .pay-user-info .pay-user-name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 .25rem;
    }

    .pay-user-info .pay-user-email {
        font-size: 0.78rem;
        color: rgba(255,255,255,.75);
        margin-bottom: .5rem;
    }

    /* ── SIDEBAR ── */
    .pay-sidebar {
        position: sticky;
        top: 1rem;
    }

    .pay-sidebar-card {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .pay-sidebar-title {
        padding: .875rem 1.25rem;
        background: var(--color-primary-50);
        border-bottom: 1px solid var(--color-border-light);
        font-size: .7rem;
        font-weight: 800;
        color: var(--color-primary-hover);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .pay-steps {
        display: flex;
        flex-direction: column;
        padding: .75rem;
        gap: .25rem;
    }

    .pay-step-item {
        display: flex;
        align-items: center;
        gap: .875rem;
        padding: .75rem .875rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all var(--transition-fast);
        cursor: pointer;
    }

    .pay-step-item:hover {
        background: var(--color-primary-50);
        transform: translateX(3px);
    }

    .pay-step-item.active {
        background: linear-gradient(135deg, var(--color-primary-light) 0%, #bbf7d0 100%);
        box-shadow: var(--shadow-sm);
    }

    .pay-step-num {
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        border-radius: var(--radius-full);
        background: var(--color-border);
        color: var(--color-text-muted);
        font-size: .7rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-fast);
    }

    .pay-step-item.active .pay-step-num {
        background: var(--color-primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,132,61,.3);
    }

    .pay-step-label {
        font-size: .82rem;
        font-weight: 600;
        color: var(--color-text-muted);
        line-height: 1.2;
    }

    .pay-step-item.active .pay-step-label {
        color: var(--color-primary-hover);
    }

    .pay-step-sub {
        font-size: .68rem;
        font-weight: 500;
        color: var(--color-text-subtle);
        margin-top: .1rem;
    }

    .pay-sidebar-back {
        padding: .75rem;
        border-top: 1px solid var(--color-border-light);
    }

    .btn-back-to-detail {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        width: 100%;
        padding: .75rem;
        background: transparent;
        border: 1.5px solid var(--color-border);
        border-radius: var(--radius-md);
        font-size: .82rem;
        font-weight: 700;
        color: var(--color-text-muted);
        text-decoration: none;
        transition: all var(--transition-fast);
    }

    .btn-back-to-detail:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
        background: var(--color-primary-50);
    }

    /* ── SECTION CARDS ── */
    .pay-card {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .pay-card-header {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
        border-bottom: 1.5px solid var(--color-border-light);
    }

    .pay-card-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .pay-card-title {
        font-size: .95rem;
        font-weight: 800;
        color: var(--color-primary-hover);
        margin: 0;
    }

    .pay-card-body {
        padding: 1.5rem;
    }

    /* ── STATUS BANNER ── */
    .pay-status-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
    }

    .pay-status-banner.success {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
    }

    .pay-status-banner.warning {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
    }

    .pay-status-banner.danger {
        background: #fef2f2;
        border: 1.5px solid #fca5a5;
    }

    .pay-status-icon {
        font-size: 1.75rem;
        flex-shrink: 0;
        line-height: 1;
    }

    .pay-status-banner.success .pay-status-icon { color: #16a34a; }
    .pay-status-banner.warning .pay-status-icon { color: #d97706; }
    .pay-status-banner.danger  .pay-status-icon { color: #dc2626; }

    .pay-status-banner .psb-title {
        font-weight: 800;
        font-size: .9rem;
        color: var(--color-text);
        margin-bottom: .2rem;
    }

    .pay-status-banner .psb-desc {
        font-size: .8rem;
        color: var(--color-text-muted);
        margin: 0;
    }

    /* ── RINCIAN BIAYA ── */
    .pay-invoice-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: var(--color-bg);
        border-radius: var(--radius-md);
        border: 1.5px solid var(--color-border-light);
    }

    .pay-invoice-name {
        font-weight: 700;
        font-size: .9rem;
        color: var(--color-text);
    }

    .pay-invoice-desc {
        font-size: .75rem;
        color: var(--color-text-muted);
        margin-top: .15rem;
    }

    .pay-invoice-amount {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--color-primary);
        white-space: nowrap;
    }

    .pay-total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 0 0;
        margin-top: 1rem;
        border-top: 2px dashed var(--color-border);
    }

    .pay-total-label {
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--color-text-muted);
    }

    .pay-total-amount {
        font-size: 2rem;
        font-weight: 950;
        color: var(--color-primary);
        letter-spacing: -1px;
        line-height: 1;
    }

    /* Detail grid */
    .pay-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .pay-detail-item {
        padding: .875rem 1rem;
        background: var(--color-bg);
        border-radius: var(--radius-md);
        border: 1.5px solid var(--color-border-light);
    }

    .pay-detail-key {
        font-size: .65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--color-text-subtle);
        margin-bottom: .3rem;
    }

    .pay-detail-val {
        font-size: .875rem;
        font-weight: 700;
        color: var(--color-text);
    }

    /* ── MIDTRANS CARD ── */
    .pay-midtrans-card {
        border: 2px solid #dbeafe;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
        margin-bottom: 1.25rem;
    }

    .pay-midtrans-header {
        display: flex;
        align-items: center;
        gap: .875rem;
        margin-bottom: 1rem;
    }

    .pay-midtrans-logo {
        width: 44px;
        height: 44px;
        background: #2563eb;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37,99,235,.3);
    }

    .pay-midtrans-title {
        font-size: .95rem;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: .15rem;
    }

    .pay-midtrans-sub {
        font-size: .75rem;
        color: #3b82f6;
    }

    .pay-method-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: 1.25rem;
    }

    .pay-method-badge {
        padding: .25rem .625rem;
        background: #fff;
        border: 1.5px solid #bfdbfe;
        border-radius: var(--radius-full);
        font-size: .68rem;
        font-weight: 700;
        color: #1d4ed8;
    }

    .btn-pay-midtrans {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: #fff;
        font-weight: 800;
        font-size: .95rem;
        border: none;
        border-radius: var(--radius-md);
        transition: all var(--transition-normal);
        box-shadow: 0 4px 16px rgba(37,99,235,.3);
        cursor: pointer;
    }

    .btn-pay-midtrans:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(37,99,235,.4);
        filter: brightness(1.08);
    }

    .btn-pay-midtrans:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    /* VA number display */
    .pay-va-box {
        background: #fff;
        border: 1.5px solid #bfdbfe;
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-top: .75rem;
    }

    .pay-va-bank {
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1d4ed8;
        margin-bottom: .5rem;
    }

    .pay-va-number {
        font-family: 'Courier New', monospace;
        font-size: 1.4rem;
        font-weight: 900;
        color: #1e3a8a;
        letter-spacing: 2px;
    }

    .pay-va-expiry {
        font-size: .75rem;
        color: #dc2626;
        font-weight: 600;
        margin-top: .5rem;
    }

    /* ── TRANSFER MANUAL ── */
    .pay-divider-text {
        text-align: center;
        position: relative;
        margin: 1.5rem 0;
    }

    .pay-divider-text::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--color-border);
    }

    .pay-divider-text span {
        position: relative;
        background: #fff;
        padding: 0 .75rem;
        font-size: .72rem;
        font-weight: 700;
        color: var(--color-text-subtle);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .rek-bank-card {
        background: #fff;
        border: 2px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: .875rem 1rem;
        margin-bottom: .75rem;
        transition: all var(--transition-fast);
        cursor: default;
    }

    .rek-bank-card:hover {
        border-color: var(--color-primary);
        box-shadow: var(--shadow-sm);
        transform: translateX(2px);
    }

    .rek-bank-badge {
        display: inline-flex;
        align-items: center;
        padding: .25rem .625rem;
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        font-size: .68rem;
        font-weight: 900;
        border-radius: var(--radius-sm);
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .rek-an {
        font-size: .72rem;
        color: var(--color-text-subtle);
        font-weight: 600;
    }

    .rek-number {
        font-family: 'Courier New', monospace;
        font-size: 1rem;
        font-weight: 800;
        color: var(--color-text);
        letter-spacing: 1px;
    }

    /* ── FILE UPLOAD ── */
    .pay-upload-zone {
        position: relative;
        border: 2.5px dashed var(--color-primary);
        border-radius: var(--radius-lg);
        padding: 2rem 1.5rem;
        text-align: center;
        background: var(--color-primary-50);
        transition: all var(--transition-normal);
        cursor: pointer;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .pay-upload-zone:hover {
        border-color: var(--color-primary-hover);
        background: #e8f5ee;
        transform: scale(1.01);
    }

    .pay-upload-zone.dragover {
        border-color: var(--color-primary-hover);
        background: #dcfce7;
        transform: scale(1.02);
    }

    .pay-upload-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }

    .pay-upload-icon {
        font-size: 2.25rem;
        color: var(--color-primary);
        margin-bottom: .5rem;
    }

    .pay-upload-title {
        font-weight: 700;
        color: var(--color-primary-hover);
        font-size: .875rem;
        margin-bottom: .25rem;
    }

    .pay-upload-hint {
        font-size: .72rem;
        color: var(--color-text-muted);
    }

    .pay-upload-preview {
        display: none;
        flex-direction: column;
        align-items: center;
    }

    /* ── SUBMIT BUTTON ── */
    .btn-submit-payment {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .625rem;
        padding: .9375rem 1.5rem;
        background: linear-gradient(135deg, var(--color-primary) 0%, #00a84f 100%);
        color: #fff;
        font-weight: 800;
        font-size: .9rem;
        border: none;
        border-radius: var(--radius-md);
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-primary);
        cursor: pointer;
    }

    .btn-submit-payment:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,132,61,.35);
        filter: brightness(1.07);
    }

    .btn-submit-payment:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    /* ── PANDUAN GUIDE ITEMS ── */
    .pay-guide-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--color-border-light);
    }

    .pay-guide-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .pay-guide-num {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: var(--radius-full);
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        font-size: .75rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: .1rem;
    }

    .pay-guide-title {
        font-size: .875rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: .25rem;
    }

    .pay-guide-desc {
        font-size: .8rem;
        color: var(--color-text-muted);
        line-height: 1.5;
    }

    /* ── KONTAK BANTUAN CARD ── */
    .pay-help-box {
        background: linear-gradient(135deg, var(--color-gold-light) 0%, #fffbeb 100%);
        border: 1.5px solid #fde68a;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        text-align: center;
    }

    .pay-help-box .help-icon {
        width: 52px;
        height: 52px;
        background: var(--color-gold-light);
        border: 2px solid #fde68a;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--color-gold);
        margin: 0 auto 1rem;
    }

    .pay-help-box .help-title {
        font-weight: 800;
        color: var(--color-text);
        margin-bottom: .35rem;
        font-size: .95rem;
    }

    .pay-help-box .help-desc {
        font-size: .78rem;
        color: var(--color-text-muted);
        margin-bottom: 1rem;
    }

    .btn-wa {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .625rem 1.5rem;
        background: #25d366;
        color: #fff;
        font-weight: 700;
        font-size: .82rem;
        border-radius: var(--radius-full);
        text-decoration: none;
        transition: all var(--transition-fast);
        box-shadow: 0 3px 10px rgba(37,211,102,.3);
    }

    .btn-wa:hover {
        color: #fff;
        background: #1ebe57;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(37,211,102,.4);
    }

    /* ── ERROR SCREEN ── */
    .pay-error-screen {
        text-align: center;
        padding: 3rem 2rem;
        background: #fff;
        border-radius: var(--radius-xl);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .pay-error-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 991.98px) {
        .pay-hero-inner { flex-direction: column; }
        .pay-hero-user  { justify-content: flex-start; }
        .pay-sidebar    { position: static; margin-bottom: 1.25rem; }
        .pay-steps      { flex-direction: row; overflow-x: auto; gap: .375rem; }
        .pay-step-item  { flex-direction: column; text-align: center; min-width: 80px; padding: .625rem .5rem; }
        .pay-step-label { font-size: .7rem; }
        .pay-step-sub   { display: none; }
    }

    @media (max-width: 575.98px) {
        .pay-card-body { padding: 1rem; }
        .pay-detail-grid { grid-template-columns: 1fr; }
        .pay-total-amount { font-size: 1.65rem; }
    }
</style>
