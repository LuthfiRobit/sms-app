<style>
    /* DASHBOARD SISWA STYLES
    Personal dashboard dengan information hierarchy yang jelas
    Menggunakan design tokens dari portal.blade.php */

    /* GREETING CARD - Welcome message dengan user info
    Gradient card dengan decorative elements */
    .greeting-card {
        background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 3vw, 2rem);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: var(--shadow-primary);
        position: relative;
        overflow: hidden;
        min-height: 120px;
    }

    /* Decorative circles untuk visual depth */
    .greeting-card::before,
    .greeting-card::after {
        content: '';
        position: absolute;
        border-radius: var(--radius-full);
        opacity: 0.08;
        pointer-events: none;
        background: #fff;
    }

    .greeting-card::before {
        width: 280px;
        height: 280px;
        top: -100px;
        right: -40px;
    }

    .greeting-card::after {
        width: 180px;
        height: 180px;
        bottom: -70px;
        right: 80px;
    }

    /* Left section dengan avatar dan info */
    .greeting-left {
        display: flex;
        align-items: center;
        gap: 1.125rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex: 1;
        min-width: 0;
    }

    /* Avatar circle dengan initial */
    .greeting-avatar {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.14));
        border: 2.5px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 900;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all var(--transition-normal);
    }

    .greeting-avatar:hover {
        transform: scale(1.05) rotate(-5deg);
    }

    /* User info text */
    .greeting-info {
        flex: 1;
        min-width: 0;
    }

    .greeting-time {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.82);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 0.25rem;
        font-weight: 600;
        display: block;
    }

    .greeting-name {
        font-size: clamp(1.125rem, 2.5vw, 1.375rem);
        font-weight: 800;
        color: #fff;
        margin: 0 0 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        line-height: 1.2;
        word-break: break-word;
    }

    /* Status badges dalam greeting */
    .status-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-full);
        gap: 0.375rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .status-badge i {
        font-size: 0.85rem;
    }

    .status-active {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-info {
        background: rgba(255, 255, 255, 0.18);
        color: #d1fae5;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .status-warning {
        background: rgba(251, 191, 36, 0.3);
        color: #fef3c7;
        border: 1px solid rgba(251, 191, 36, 0.4);
    }

    /* Right section dengan CTA button */
    .greeting-right {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .greeting-right .btn {
        border-color: rgba(255, 255, 255, 0.5);
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.625rem 1.25rem;
        border-width: 1.5px;
        backdrop-filter: blur(4px);
        transition: all var(--transition-fast);
    }

    .greeting-right .btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Mobile: Stack greeting vertically */
    @media (max-width: 767.98px) {
        .greeting-card {
            flex-direction: column;
            text-align: center;
        }

        .greeting-left {
            flex-direction: column;
            text-align: center;
            width: 100%;
        }

        .greeting-right {
            width: 100%;
        }

        .greeting-right .btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* PROFILE ALERT - Warning untuk profil belum lengkap
    Amber-colored alert dengan progress bar */
    .profile-alert {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #fbbf24;
        border-left-width: 5px;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-normal);
    }

    .profile-alert:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    /* Alert icon dengan animated attention effect */
    .profile-alert-icon {
        font-size: 2rem;
        color: #f59e0b;
        flex-shrink: 0;
        line-height: 1;
        animation: iconPulse 2s ease-in-out infinite;
    }

    @keyframes iconPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.08);
        }
    }

    /* Alert content */
    .profile-alert-body {
        flex: 1;
        min-width: 0;
    }

    .profile-alert-title {
        font-weight: 700;
        color: #92400e;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .profile-alert-desc {
        font-size: 0.875rem;
        color: #78350f;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    /* Progress bar untuk kelengkapan profil */
    .profile-progress-track {
        height: 10px;
        background: rgba(251, 191, 36, 0.2);
        border-radius: var(--radius-full);
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 0.75rem;
    }

    .profile-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        border-radius: var(--radius-full);
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(245, 158, 11, 0.5);
        position: relative;
        overflow: hidden;
    }

    /* Shimmer effect pada progress bar */
    .profile-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    /* Aspek kelengkapan dots */
    .profile-aspek-dots {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .aspek-dot {
        width: 28px;
        height: 28px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: all var(--transition-fast);
        border: 1.5px solid transparent;
    }

    .aspek-dot:hover {
        transform: scale(1.15);
    }

    .aspek-dot.complete {
        background: #16a34a;
        color: #fff;
        border-color: #15803d;
    }

    .aspek-dot.incomplete {
        background: #fef3c7;
        color: #78350f;
        border-color: #fde68a;
    }

    /* PORTAL CARD - Container untuk konten dashboard
    Reusable card component */
    .portal-card {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
        overflow: hidden;
        transition: all var(--transition-normal);
    }

    .portal-card:hover {
        box-shadow: var(--shadow-md);
    }

    /* Card header dengan gradient accent */
    .card-header-portal {
        background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
        border-bottom: 2px solid var(--color-border-light);
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .card-header-portal>span {
        font-weight: 700;
        color: var(--color-text);
        font-size: 1.05rem;
        display: flex;
        align-items: center;
    }

    .card-header-portal i {
        color: var(--color-primary);
    }

    /* INFO CARDS - Stats dan informasi penting
    Small cards untuk menampilkan data krusial */
    .info-card-dash {
        background: var(--color-surface);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        height: 100%;
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }

    .info-card-dash::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
    }

    .info-card-dash:hover {
        border-color: var(--color-primary-light);
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    /* Info card header */
    .info-card-dash-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .info-card-dash-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: all var(--transition-fast);
    }

    .info-card-dash:hover .info-card-dash-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    .bg-primary-light {
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
    }

    .bg-info-light {
        background: #dbeafe;
        color: #1e40af;
    }

    .bg-warning-light {
        background: #fef3c7;
        color: #92400e;
    }

    /* Info card title */
    .info-card-dash-title {
        font-size: 0.8rem;
        color: var(--color-text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    /* Info card value - data utama */
    .info-card-dash-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--color-text);
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    /* Info card description */
    .info-card-dash-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        line-height: 1.5;
    }

    .info-card-dash-desc strong {
        color: var(--color-text);
        font-weight: 600;
    }

    /* COUNTDOWN TIMER - Urgency indicator */
    .countdown-timer {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin-top: 0.75rem;
    }

    .countdown-unit {
        text-align: center;
    }

    .countdown-value {
        display: block;
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--color-primary);
        background: var(--color-primary-light);
        border-radius: var(--radius-md);
        padding: 0.5rem 0.75rem;
        min-width: 50px;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .countdown-label {
        display: block;
        font-size: 0.7rem;
        color: var(--color-text-muted);
        margin-top: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* QUICK ACTIONS - Shortcut buttons */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.125rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.9rem;
        transition: all var(--transition-fast);
        text-decoration: none;
        border-width: 1.5px;
    }

    .quick-action-btn i {
        font-size: 1.15rem;
        transition: all var(--transition-fast);
    }

    .quick-action-btn:hover {
        transform: translateX(4px);
    }

    .quick-action-btn:hover i {
        transform: scale(1.15);
    }

    /* PENDAFTARAN LIST - Registration cards */
    .pendaftaran-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pendaftaran-card {
        background: var(--color-surface);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        transition: all var(--transition-normal);
        flex-wrap: wrap;
    }

    .pendaftaran-card:hover {
        border-color: var(--color-primary);
        box-shadow: var(--shadow-md);
        transform: translateX(4px);
    }

    /* Left section - nomor dan metadata */
    .pendaftaran-card-left {
        flex: 1;
        min-width: 0;
    }

    .pendaftaran-no {
        margin-bottom: 0.75rem;
    }

    .no-label {
        display: block;
        font-size: 0.75rem;
        color: var(--color-text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .no-value {
        display: block;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--color-primary);
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
    }

    /* Metadata dengan icons */
    .pendaftaran-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--color-text-muted);
    }

    .pendaftaran-meta>span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .pendaftaran-meta i {
        color: var(--color-primary);
    }

    /* Right section - status dan action */
    .pendaftaran-card-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
    }

    .pendaftaran-card-right .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.875rem;
        font-weight: 600;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    /* Mobile: Stack pendaftaran card */
    @media (max-width: 767.98px) {
        .pendaftaran-card {
            flex-direction: column;
            align-items: stretch;
        }

        .pendaftaran-card-right {
            align-items: stretch;
        }

        .pendaftaran-card-right .btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* EMPTY STATE - No data placeholder */
    .empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        max-width: 480px;
        margin: 0 auto;
    }

    .empty-state-icon {
        margin-bottom: 1.5rem;
        animation: emptyFloat 3s ease-in-out infinite;
    }

    @keyframes emptyFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 0.75rem;
    }

    .empty-state-desc {
        font-size: 0.95rem;
        color: var(--color-text-muted);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .empty-state-desc strong {
        color: var(--color-text);
        font-weight: 600;
    }

    /* RESPONSIVE UTILITIES */
    @media (max-width: 991.98px) {
        .info-card-dash-value {
            font-size: 1.5rem;
        }

        .countdown-value {
            font-size: 1.5rem;
            min-width: 45px;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-portal {
            flex-direction: column;
            align-items: stretch;
        }

        .card-header-portal .btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* UTILITY CLASSES */
    .text-monospace {
        font-family: 'Courier New', Courier, monospace;
    }

    /* Responsive margin helpers */
    .mb-dash {
        margin-bottom: clamp(1.5rem, 3vw, 2rem);
    }

    /* Prevent text overflow */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>