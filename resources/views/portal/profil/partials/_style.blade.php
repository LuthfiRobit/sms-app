<style>
    /* PROFIL SISWA STYLES Layout: Profile card (atas) + Navigation (kiri) + Content (kanan) Menggunakan design tokens dari portal.blade.php */

    /* PROFILE GREETING CARD - Seperti dashboard greeting Full-width card dengan gradient background di atas */
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

    /* Inner container */
    .profile-greeting-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    /* FOTO PROFIL - dengan upload overlay */
    .foto-wrapper {
        width: 120px;
        height: 120px;
        position: relative;
        cursor: pointer;
        flex-shrink: 0;
    }

    .foto-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: var(--radius-full);
        box-shadow: 0 0 0 4px #fff, 0 0 0 6px rgba(255, 255, 255, 0.3);
        transition: all var(--transition-normal);
    }

    .foto-wrapper:hover img {
        box-shadow: 0 0 0 4px #fff, 0 0 0 8px rgba(255, 255, 255, 0.5);
        transform: scale(1.02);
    }

    .foto-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        opacity: 0;
        transition: opacity var(--transition-normal);
        cursor: pointer;
    }

    .foto-wrapper:hover .foto-overlay,
    .foto-wrapper:focus-within .foto-overlay {
        opacity: 1;
    }

    /* PROFILE INFO - Nama, email, status */
    .profile-info {
        flex: 1;
        min-width: 0;
    }

    .profile-name {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 900;
        color: #fff;
        margin: 0 0 0.5rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        line-height: 1.2;
    }

    .profile-email {
        font-size: clamp(0.9rem, 2vw, 1rem);
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .profile-email i {
        font-size: 1rem;
    }

    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        border: 1.5px solid rgba(255, 255, 255, 0.3);
    }

    .profile-status-badge i {
        font-size: 0.7rem;
    }

    /* PROGRESS KELENGKAPAN - di dalam greeting card */
    .profile-completion {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        min-width: 280px;
    }

    .completion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .completion-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.85);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .completion-percent {
        font-size: 1.5rem;
        font-weight: 900;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    .profil-progress-track {
        height: 10px;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.2);
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 0.75rem;
    }

    .profil-progress-fill {
        height: 100%;
        border-radius: var(--radius-full);
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    /* Shimmer effect */
    .profil-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
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

    .completion-missing {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.4;
    }

    .completion-missing i {
        color: #fbbf24;
        font-size: 0.85rem;
    }

    /* Mobile: Stack profile elements */
    @media (max-width: 767.98px) {
        .profile-greeting-inner {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
        }

        .profile-info {
            width: 100%;
        }

        .profile-completion {
            width: 100%;
            min-width: 0;
        }
    }

    /* NAVIGATION SIDEBAR - Tab navigation */
    .profil-sidebar {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        padding: 1.25rem;
        position: sticky;
        top: calc(var(--navbar-height) + 1rem);
    }

    /* Navigation list */
    .profil-nav {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        margin-bottom: 1.5rem;
    }

    .profil-nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0.875rem;
        border: none;
        background: transparent;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text);
        cursor: pointer;
        transition: all var(--transition-fast);
        width: 100%;
        text-align: left;
        gap: 0.75rem;
        border: 1.5px solid transparent;
    }

    .profil-nav-item:hover {
        background: var(--color-primary-50);
        color: var(--color-primary-hover);
        transform: translateX(3px);
        border-color: var(--color-primary-light);
    }

    .profil-nav-item.active {
        background: linear-gradient(135deg, var(--color-primary-light), #bbf7d0);
        color: var(--color-primary-hover);
        font-weight: 700;
        border-color: var(--color-primary);
        box-shadow: var(--shadow-sm);
    }

    .profil-nav-item i {
        width: 20px;
        text-align: center;
        flex-shrink: 0;
        font-size: 1.1rem;
        transition: all var(--transition-fast);
    }

    .profil-nav-item:hover i,
    .profil-nav-item.active i {
        transform: scale(1.15);
    }

    .nav-label {
        flex: 1;
    }

    /* Status dots */
    .tab-dot {
        width: 10px;
        height: 10px;
        border-radius: var(--radius-full);
        flex-shrink: 0;
        transition: all var(--transition-normal);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .dot-ok {
        background: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
    }

    .dot-miss {
        background: #fca5a5;
        box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.2);
    }

    /* CTA Button */
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
    }

    .btn-daftar:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(22, 163, 74, 0.35);
    }

    .btn-daftar:active {
        transform: translateY(0);
    }

    /* Mobile: Horizontal scrolling nav */
    @media (max-width: 767.98px) {
        .profil-sidebar {
            position: static;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .profil-nav {
            flex-direction: row;
            overflow-x: auto;
            gap: 0.5rem;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .profil-nav::-webkit-scrollbar {
            display: none;
        }

        .profil-nav-item {
            flex: 0 0 auto;
            flex-direction: column;
            justify-content: center;
            padding: 0.75rem;
            min-width: 75px;
            max-width: 80px;
            font-size: 0.75rem;
            gap: 0.5rem;
            transform: none !important;
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

    /* CONTENT CARDS */
    .profil-card {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        animation: fadeSlideIn 0.25s ease;
        margin-bottom: 1.5rem;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .profil-card-header {
        padding: 1.125rem 1.5rem;
        background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
        border-bottom: 2px solid var(--color-border-light);
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--color-primary-hover);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .profil-card-header i {
        font-size: 1.15rem;
    }

    .profil-card-body {
        padding: 1.75rem 2rem;
    }

    @media (max-width: 575.98px) {
        .profil-card-body {
            padding: 1.25rem 1rem;
        }
    }

    /* FORM CONTROLS */
    .form-control,
    .form-select {
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        border: 2px solid var(--color-border);
        color: var(--color-text);
        transition: all var(--transition-fast);
        padding: 0.625rem 0.875rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        outline: none;
    }

    .form-control.bg-light {
        color: var(--color-text-muted);
        cursor: not-allowed;
        background: var(--color-bg);
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--color-text);
    }

    .form-text {
        font-size: 0.8rem;
        color: var(--color-text-muted);
    }

    /* Input groups */
    .input-group .btn {
        border-radius: var(--radius-sm);
    }

    .input-group-text {
        background: var(--color-primary-50);
        border: 2px solid var(--color-border);
        color: var(--color-primary);
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* INFO BOX */
    .info-box {
        background: linear-gradient(135deg, var(--color-primary-50), #dcfce7);
        border: 1.5px solid #bbf7d0;
        border-radius: var(--radius-md);
        padding: 0.875rem 1rem;
        font-size: 0.85rem;
        color: #166534;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .info-box i {
        margin-top: 2px;
        flex-shrink: 0;
        font-size: 1.1rem;
        color: var(--color-primary);
    }

    /* BUTTONS */
    .btn-save {
        min-width: 160px;
        font-weight: 600;
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        padding: 0.625rem 1.5rem;
        transition: all var(--transition-fast);
    }

    .btn-save:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-save:active {
        transform: translateY(0);
    }

    @media (max-width: 575.98px) {
        .btn-save {
            width: 100%;
        }
    }

    /* ACCORDION (Orang Tua) */
    .accordion-button {
        font-weight: 600;
        font-size: 0.95rem;
        padding: 1rem 1.25rem;
    }

    .accordion-button:not(.collapsed) {
        background: var(--color-primary-50);
        color: var(--color-primary-hover);
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
    }

    .accordion-item {
        border-color: var(--color-border);
    }

    /* UTILITIES */
    .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-full);
    }

    /* Tab panes */
    .tab-pane-profil {
        display: none;
    }

    .tab-pane-profil:not(.d-none) {
        display: block;
    }
</style>