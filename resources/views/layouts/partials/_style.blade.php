<style>
    /* ═══════════════════════════════════════════════════════════════════════
           🎨 DESIGN SYSTEM TOKENS
           Sentralisasi semua nilai desain agar konsisten di seluruh halaman
           ═══════════════════════════════════════════════════════════════════════ */
    :root {
        /* 🎨 Brand Colors - Green palette untuk tema pendidikan yang fresh */
        --color-primary: #16a34a;
        --color-primary-hover: #15803d;
        --color-primary-light: #dcfce7;
        --color-primary-50: #f0fdf4;
        --color-accent: #059669;
        --color-accent-teal: #0d9488;

        /* 🌑 Neutrals - Hierarchical text colors */
        --color-text: #111827;
        --color-text-muted: #6b7280;
        --color-text-subtle: #9ca3af;
        --color-border: #e5e7eb;
        --color-border-light: #f3f4f6;
        --color-bg: #f8fafc;
        --color-surface: #ffffff;

        /* 💡 Semantic Colors */
        --color-success: #10b981;
        --color-danger: #ef4444;
        --color-warning: #f59e0b;
        --color-info: #3b82f6;

        /* 🎭 Shadows - Subtle elevation system */
        --shadow-xs: 0 1px 2px rgba(0, 0, 0, .05);
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .06), 0 2px 4px rgba(0, 0, 0, .04);
        --shadow-lg: 0 8px 32px rgba(0, 0, 0, .08), 0 2px 8px rgba(0, 0, 0, .04);
        --shadow-xl: 0 12px 48px rgba(0, 0, 0, .1), 0 4px 12px rgba(0, 0, 0, .05);
        --shadow-primary: 0 4px 14px rgba(22, 163, 74, .22);

        /* 📐 Border Radius - Consistent rounding */
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 14px;
        --radius-xl: 18px;
        --radius-2xl: 24px;
        --radius-full: 9999px;

        /* ⚡ Transitions - Smooth animations */
        --transition-fast: .15s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: .22s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: .35s cubic-bezier(0.4, 0, 0.2, 1);

        /* 📏 Layout Measurements */
        --navbar-height: 64px;
        --navbar-height-mobile: 58px;
        --footer-height: auto;
        --container-max: 1280px;

        /* 📱 Responsive Spacing */
        --spacing-container: clamp(1rem, 4vw, 2rem);
        --spacing-section: clamp(2rem, 6vw, 4rem);
    }

    /* GLOBAL STYLES */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        background-color: var(--color-bg);
        /* Subtle dot pattern untuk texture visual tanpa mengganggu */
        background-image: radial-gradient(circle, rgba(209, 213, 219, 0.3) 1px, transparent 1px);
        background-size: 24px 24px;
        color: var(--color-text);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        line-height: 1.6;
        overflow-x: hidden;
        /* Prevent horizontal scroll */
    }

    /* NAVBAR - SUPER RESPONSIVE
           Navbar yang sticky dengan gradient, responsive di semua device */
    .navbar-portal {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent-teal) 100%);
        min-height: var(--navbar-height-mobile);
        box-shadow: 0 1px 0 rgba(255, 255, 255, .08), var(--shadow-primary);
        position: sticky;
        top: 0;
        z-index: 1040;
        transition: all var(--transition-normal);
        backdrop-filter: blur(8px);
    }

    @media (min-width: 768px) {
        .navbar-portal {
            min-height: var(--navbar-height);
        }
    }

    /* Shadow enhancement saat scroll untuk depth perception */
    .navbar-portal.scrolled {
        box-shadow: 0 4px 24px rgba(22, 163, 74, .35);
    }

    /* Container navbar dengan max-width dan padding responsive */
    .navbar-portal .container {
        max-width: var(--container-max);
        padding-left: var(--spacing-container);
        padding-right: var(--spacing-container);
    }

    /* ─────────────────────────────────────────────────────────────────────
           BRAND - Logo & Text
           ───────────────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-brand {
        color: #fff !important;
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: -.5px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        padding: 0.5rem 0;
        transition: opacity var(--transition-fast);
    }

    .navbar-portal .navbar-brand:hover {
        opacity: 0.9;
    }

    .brand-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, .18);
        border: 1.5px solid rgba(255, 255, 255, .3);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        transition: all var(--transition-fast);
    }

    .navbar-portal .navbar-brand:hover .brand-icon {
        background: rgba(255, 255, 255, .25);
        transform: scale(1.05);
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .brand-text-primary {
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        font-weight: 800;
    }

    .brand-text-sub {
        font-size: clamp(0.55rem, 1.5vw, 0.65rem);
        font-weight: 400;
        opacity: .85;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    /* ─────────────────────────────────────────────────────────────────────
           HAMBURGER MENU BUTTON - Mobile Toggle
           ───────────────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-toggler {
        border: 1.5px solid rgba(255, 255, 255, .4);
        padding: .35rem .55rem;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
        background: transparent;
    }

    .navbar-portal .navbar-toggler:hover {
        background: rgba(255, 255, 255, .1);
        border-color: rgba(255, 255, 255, .6);
    }

    .navbar-portal .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        outline: none;
    }

    .navbar-portal .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255%2C255%2C255%2C0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2.5' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        width: 24px;
        height: 24px;
    }

    /* ─────────────────────────────────────────────────────────────────────
           NAVBAR COLLAPSE - Mobile Menu Container
           Perbaikan spacing dan transition untuk mobile menu
           ───────────────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-collapse {
        /* Spacing yang cukup di mobile saat expanded */
        transition: height var(--transition-normal);
    }

    @media (max-width: 767.98px) {
        .navbar-portal .navbar-collapse {
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(12px);
            border-radius: var(--radius-md);
            margin-top: 0.75rem;
            padding: 0.75rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .15);
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
           NAV LINKS - Menu Items
           ───────────────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-nav {
        gap: 0.25rem;
    }

    @media (min-width: 768px) {
        .navbar-portal .navbar-nav {
            gap: 0.5rem;
        }
    }

    .navbar-portal .nav-link {
        color: rgba(255, 255, 255, .88) !important;
        font-size: .875rem;
        font-weight: 500;
        padding: .5rem .85rem;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .navbar-portal .nav-link i {
        font-size: 1rem;
        transition: transform var(--transition-fast);
    }

    .navbar-portal .nav-link:hover,
    .navbar-portal .nav-link:focus-visible {
        color: #fff !important;
        background: rgba(255, 255, 255, .15);
        outline: none;
    }

    .navbar-portal .nav-link:hover i {
        transform: scale(1.1);
    }

    .navbar-portal .nav-link.active {
        background: rgba(255, 255, 255, .22);
        color: #fff !important;
        font-weight: 600;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
    }

    /* Mobile: Full width nav items untuk better touch targets */
    @media (max-width: 767.98px) {
        .navbar-portal .nav-item {
            width: 100%;
        }

        .navbar-portal .nav-link {
            width: 100%;
            justify-content: flex-start;
        }
    }

    /* ─────────────────────────────────────────────────────────────────────
           USER MENU DROPDOWN - Authenticated User
           ───────────────────────────────────────────────────────────────────── */
    .user-toggle {
        color: rgba(255, 255, 255, .95) !important;
        font-size: .875rem;
        font-weight: 500;
        padding: .4rem .75rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .15);
        transition: all var(--transition-fast);
        white-space: nowrap;
    }

    .user-toggle:hover {
        background: rgba(255, 255, 255, .15);
        border-color: rgba(255, 255, 255, .25);
        color: #fff !important;
    }

    .user-toggle:focus-visible {
        outline: 2px solid rgba(255, 255, 255, .5);
        outline-offset: 2px;
    }

    /* Avatar circle dengan initial */
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, rgba(255, 255, 255, .25), rgba(255, 255, 255, .15));
        border: 1.5px solid rgba(255, 255, 255, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .user-caret {
        font-size: .7rem;
        transition: transform var(--transition-fast);
    }

    .user-toggle[aria-expanded="true"] .user-caret {
        transform: rotate(180deg);
    }

    /* ─────────────────────────────────────────────────────────────────────
           DROPDOWN MENU - User Actions
           ───────────────────────────────────────────────────────────────────── */
    .navbar-portal .dropdown-menu {
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-xl);
        padding: 8px;
        min-width: 240px;
        margin-top: 8px !important;
        animation: menuSlideIn .2s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: top right;
    }

    @keyframes menuSlideIn {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Dropdown header - email display */
    .dropdown-email {
        display: block;
        padding: .5rem .75rem;
        font-size: .8rem;
        color: var(--color-text-muted);
        font-weight: 500;
        word-break: break-word;
    }

    .navbar-portal .dropdown-divider {
        margin: 6px 0;
        border-color: var(--color-border-light);
    }

    /* Dropdown items dengan hover effect */
    .navbar-portal .dropdown-item {
        font-size: .875rem;
        padding: .65rem .75rem;
        border-radius: var(--radius-sm);
        color: var(--color-text);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all var(--transition-fast);
        font-weight: 500;
    }

    .navbar-portal .dropdown-item i {
        font-size: 1.1rem;
        width: 20px;
        color: var(--color-text-muted);
        transition: all var(--transition-fast);
    }

    .navbar-portal .dropdown-item:hover {
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
    }

    .navbar-portal .dropdown-item:hover i {
        color: var(--color-primary-hover);
        transform: scale(1.1);
    }

    .navbar-portal .dropdown-item.text-danger:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .navbar-portal .dropdown-item.text-danger:hover i {
        color: #dc2626;
    }

    /* Logout button styled as dropdown item */
    .navbar-portal .dropdown-item[type="submit"] {
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    /* ─────────────────────────────────────────────────────────────────────
           REGISTER BUTTON - CTA Button di Navbar
           ───────────────────────────────────────────────────────────────────── */
    .btn-nav-register {
        background: rgba(255, 255, 255, .95);
        color: var(--color-primary) !important;
        font-size: .875rem;
        font-weight: 600;
        padding: .5rem 1.15rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all var(--transition-fast);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        border: 1px solid rgba(255, 255, 255, 1);
    }

    .btn-nav-register:hover {
        background: #fff;
        color: var(--color-primary-hover) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .18);
    }

    .btn-nav-register:active {
        transform: translateY(0);
    }

    /* Mobile: Full width button untuk better UX */
    @media (max-width: 767.98px) {
        .btn-nav-register {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }
    }

    /* FLASH MESSAGES - Alert Notifications
           Alert yang muncul setelah action (success, error, warning, info) */
    .alert {
        border-radius: var(--radius-lg);
        border: none;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: .9rem;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
        animation: alertSlideDown .3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes alertSlideDown {
        from {
            opacity: 0;
            transform: translateY(-12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert i {
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .alert span {
        flex: 1;
    }

    .alert .btn-close {
        padding: 0.5rem;
        opacity: 0.6;
        transition: opacity var(--transition-fast);
    }

    .alert .btn-close:hover {
        opacity: 1;
    }

    /* Alert variants dengan warna yang sesuai semantic */
    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        color: #92400e;
    }

    .alert-info {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #1e40af;
    }

    /* MAIN CONTENT AREA
           Container utama untuk semua content halaman */
    .portal-main {
        flex: 1;
        /* Padding responsive: lebih kecil di mobile, lebih besar di desktop */
        padding-top: clamp(1.5rem, 4vw, 3rem);
        padding-bottom: clamp(2rem, 6vw, 4rem);
    }

    .portal-main .container {
        max-width: var(--container-max);
        padding-left: var(--spacing-container);
        padding-right: var(--spacing-container);
    }

    /* Skip to main content link untuk accessibility */
    .skip-to-content {
        position: absolute;
        top: -40px;
        left: 0;
        background: var(--color-primary);
        color: white;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 0 0 var(--radius-sm) 0;
        z-index: 1050;
    }

    .skip-to-content:focus {
        top: 0;
    }

    /* FOOTER
           Footer dengan info kontak dan copyright */
    .portal-footer {
        background: linear-gradient(180deg, transparent 0%, rgba(22, 163, 74, 0.03) 100%);
        border-top: 1px solid var(--color-border);
        padding: 2rem 0;
        margin-top: auto;
        color: var(--color-text-muted);
        font-size: .9rem;
    }

    .portal-footer .container {
        max-width: var(--container-max);
        padding-left: var(--spacing-container);
        padding-right: var(--spacing-container);
    }

    .footer-brand {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--color-text);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer-brand span {
        font-size: 1.5rem;
    }

    .portal-footer p {
        margin: 0;
        font-size: .875rem;
        color: var(--color-text-muted);
    }

    .footer-contact {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .footer-contact a {
        color: var(--color-text-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color var(--transition-fast);
        font-size: .875rem;
    }

    .footer-contact a:hover {
        color: var(--color-primary);
    }

    .footer-contact span {
        font-size: .8rem;
        opacity: 0.8;
    }

    /* Mobile: Stack vertically, Desktop: Align to end */
    @media (max-width: 575.98px) {
        .footer-contact {
            margin-top: 1.5rem;
        }
    }

    @media (min-width: 768px) {
        .footer-contact {
            align-items: flex-end;
        }
    }

    /* UTILITY CLASSES
           Helper classes untuk spacing, text, dan layout */

    /* Text utilities */
    .text-balance {
        text-wrap: balance;
    }

    /* Focus visible untuk accessibility */
    *:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    /* Smooth loading state */
    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>