<style>
    /* ═══════════════════════════════════════════════════════════════════════
           🎨 DESIGN SYSTEM — LP Ma'arif NU Kraksaan
           NU Green #00843D · Gold #C8952A · Modern Islamic
           ═══════════════════════════════════════════════════════════════════════ */
    :root {
        /* LP Ma'arif NU Brand Colors */
        --color-primary:        #00843D;   /* NU Green */
        --color-primary-hover:  #006B31;   /* NU Green Dark */
        --color-primary-dark:   #004E24;   /* NU Green Darker — header/sidebar */
        --color-primary-light:  #E8F5EE;   /* NU Green Light */
        --color-primary-50:     #F0FAF4;   /* NU Green Lightest */
        --color-gold:           #C8952A;   /* Gold Accent */
        --color-gold-hover:     #A87520;   /* Gold Dark */
        --color-gold-light:     #FDF3DC;   /* Gold Lightest */

        /* Neutrals — Dark forest-green tint for text */
        --color-text:           #1A2E1F;
        --color-text-muted:     #6B7280;
        --color-text-subtle:    #9CA3AF;
        --color-border:         #E5E7EB;
        --color-border-light:   #F3F4F6;
        --color-bg:             #F7FAF8;   /* Warm off-white */
        --color-surface:        #FFFFFF;

        /* Semantic */
        --color-success: #00843D;
        --color-danger:  #EF4444;
        --color-warning: #C8952A;
        --color-info:    #3B82F6;

        /* Shadows */
        --shadow-xs:      0 1px 2px rgba(0,0,0,.05);
        --shadow-sm:      0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
        --shadow-md:      0 4px 16px rgba(0,0,0,.06), 0 2px 4px rgba(0,0,0,.04);
        --shadow-lg:      0 8px 32px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.04);
        --shadow-xl:      0 12px 48px rgba(0,0,0,.1),  0 4px 12px rgba(0,0,0,.05);
        --shadow-primary: 0 4px 14px rgba(0,132,61,.25);
        --shadow-gold:    0 4px 14px rgba(200,149,42,.22);

        /* Border Radius */
        --radius-sm:   6px;
        --radius-md:   10px;
        --radius-lg:   14px;
        --radius-xl:   18px;
        --radius-2xl:  24px;
        --radius-full: 9999px;

        /* Transitions */
        --transition-fast:   .15s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: .22s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow:   .35s cubic-bezier(0.4, 0, 0.2, 1);

        /* Layout */
        --navbar-height:        68px;
        --navbar-height-mobile: 60px;
        --container-max:        1280px;
        --spacing-container:    clamp(1rem, 4vw, 2rem);
        --spacing-section:      clamp(2rem, 6vw, 4rem);
    }

    /* ── GLOBAL RESET ───────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        background-color: var(--color-bg);
        /* Islamic geometric star lattice — subtle background texture */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cg fill='none' stroke='%2300843D' stroke-opacity='0.055' stroke-width='0.7'%3E%3Cpath d='M30 4L56 30L30 56L4 30Z'/%3E%3Cpath d='M30 16L44 30L30 44L16 30Z'/%3E%3Cpath d='M4 30L56 30M30 4L30 56M10 10L50 50M50 10L10 50'/%3E%3C/g%3E%3C/svg%3E");
        color: var(--color-text);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* ── NAVBAR ─────────────────────────────────────────────────────────────── */
    .navbar-portal {
        background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 60%, #00994A 100%);
        min-height: var(--navbar-height-mobile);
        box-shadow: 0 1px 0 rgba(255,255,255,.08), var(--shadow-primary);
        position: sticky;
        top: 0;
        z-index: 1040;
        transition: all var(--transition-normal);
        backdrop-filter: blur(8px);
    }

    @media (min-width: 768px) {
        .navbar-portal { min-height: var(--navbar-height); }
    }

    .navbar-portal.scrolled {
        box-shadow: 0 4px 24px rgba(0,132,61,.38);
    }

    .navbar-portal .container {
        max-width: var(--container-max);
        padding-left: var(--spacing-container);
        padding-right: var(--spacing-container);
    }

    /* ── BRAND / LOGO ───────────────────────────────────────────────────────── */
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

    .navbar-portal .navbar-brand:hover { opacity: 0.9; }

    /* Logo image container */
    .brand-logo-wrap {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.3);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 3px;
        transition: all var(--transition-fast);
    }

    .navbar-portal .navbar-brand:hover .brand-logo-wrap {
        background: rgba(255,255,255,.25);
        transform: scale(1.05);
    }

    .brand-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    /* Fallback icon (shown if logo fails to load) */
    .brand-icon {
        width: 38px;
        height: 38px;
        background: rgba(255,255,255,.18);
        border: 1.5px solid rgba(255,255,255,.3);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        transition: all var(--transition-fast);
    }

    .navbar-portal .navbar-brand:hover .brand-icon {
        background: rgba(255,255,255,.25);
        transform: scale(1.05);
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }

    .brand-text-primary {
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        font-weight: 800;
    }

    .brand-text-sub {
        font-size: clamp(0.5rem, 1.4vw, 0.6rem);
        font-weight: 400;
        opacity: .85;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    /* Gold ornament line under brand — subtle Islamic touch */
    .brand-text::after {
        content: '';
        display: block;
        width: 24px;
        height: 1.5px;
        background: var(--color-gold);
        margin-top: 2px;
        opacity: 0.8;
    }

    /* ── HAMBURGER ──────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-toggler {
        border: 1.5px solid rgba(255,255,255,.4);
        padding: .35rem .55rem;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
        background: transparent;
    }

    .navbar-portal .navbar-toggler:hover {
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.6);
    }

    .navbar-portal .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(255,255,255,.25);
        outline: none;
    }

    .navbar-portal .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255%2C255%2C255%2C0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2.5' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        width: 24px;
        height: 24px;
    }

    /* ── NAVBAR COLLAPSE ────────────────────────────────────────────────────── */
    .navbar-portal .navbar-collapse {
        transition: height var(--transition-normal);
    }

    @media (max-width: 767.98px) {
        .navbar-portal .navbar-collapse {
            background: rgba(255,255,255,.08);
            backdrop-filter: blur(12px);
            border-radius: var(--radius-md);
            margin-top: 0.75rem;
            padding: 0.75rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.15);
        }
    }

    /* ── NAV LINKS ──────────────────────────────────────────────────────────── */
    .navbar-portal .navbar-nav { gap: 0.25rem; }

    @media (min-width: 768px) { .navbar-portal .navbar-nav { gap: 0.5rem; } }

    .navbar-portal .nav-link {
        color: rgba(255,255,255,.88) !important;
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
        background: rgba(255,255,255,.15);
        outline: none;
    }

    .navbar-portal .nav-link:hover i { transform: scale(1.1); }

    .navbar-portal .nav-link.active {
        background: rgba(255,255,255,.22);
        color: #fff !important;
        font-weight: 600;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
    }

    @media (max-width: 767.98px) {
        .navbar-portal .nav-item { width: 100%; }
        .navbar-portal .nav-link { width: 100%; justify-content: flex-start; }
    }

    /* ── USER DROPDOWN ──────────────────────────────────────────────────────── */
    .user-toggle {
        color: rgba(255,255,255,.95) !important;
        font-size: .875rem;
        font-weight: 500;
        padding: .4rem .75rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.15);
        transition: all var(--transition-fast);
        white-space: nowrap;
    }

    .user-toggle:hover {
        background: rgba(255,255,255,.15);
        border-color: rgba(255,255,255,.25);
        color: #fff !important;
    }

    .user-toggle:focus-visible {
        outline: 2px solid rgba(255,255,255,.5);
        outline-offset: 2px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, rgba(255,255,255,.25), rgba(255,255,255,.15));
        border: 1.5px solid rgba(255,255,255,.3);
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

    .user-toggle[aria-expanded="true"] .user-caret { transform: rotate(180deg); }

    /* ── DROPDOWN MENU ──────────────────────────────────────────────────────── */
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
        from { opacity: 0; transform: translateY(-8px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

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

    .navbar-portal .dropdown-item.text-danger:hover { background: #fee2e2; color: #dc2626; }
    .navbar-portal .dropdown-item.text-danger:hover i { color: #dc2626; }

    .navbar-portal .dropdown-item[type="submit"] {
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    /* ── REGISTER CTA BUTTON ────────────────────────────────────────────────── */
    .btn-nav-register {
        background: rgba(255,255,255,.95);
        color: var(--color-primary) !important;
        font-size: .875rem;
        font-weight: 700;
        padding: .5rem 1.15rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all var(--transition-fast);
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        border: 1px solid rgba(255,255,255,1);
        letter-spacing: .2px;
    }

    .btn-nav-register:hover {
        background: #fff;
        color: var(--color-primary-hover) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,.18);
    }

    .btn-nav-register:active { transform: translateY(0); }

    @media (max-width: 767.98px) {
        .btn-nav-register {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }
    }

    /* ── FLASH ALERTS ───────────────────────────────────────────────────────── */
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
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .alert i { font-size: 1.25rem; flex-shrink: 0; }
    .alert span { flex: 1; }
    .alert .btn-close { padding: 0.5rem; opacity: 0.6; transition: opacity var(--transition-fast); }
    .alert .btn-close:hover { opacity: 1; }

    .alert-success { background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #065f46; }
    .alert-danger  { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #991b1b; }
    .alert-warning { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #92400e; }
    .alert-info    { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; }

    /* ── PORTAL CARD ────────────────────────────────────────────────────────── */
    .portal-card {
        background: var(--color-surface);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        border: none;
        border-top: 3px solid var(--color-gold);
        overflow: hidden;
    }

    /* Gold ornament divider inside card headers */
    .portal-card .card-header::after {
        content: '✦ ✦ ✦';
        display: block;
        font-size: 0.55rem;
        letter-spacing: 6px;
        color: var(--color-gold);
        opacity: 0.65;
        margin-top: 10px;
        text-align: center;
    }

    /* ── MAIN CONTENT ───────────────────────────────────────────────────────── */
    .portal-main {
        flex: 1;
        padding-top: clamp(1.5rem, 4vw, 3rem);
        padding-bottom: clamp(2rem, 6vw, 4rem);
    }

    .portal-main .container {
        max-width: var(--container-max);
        padding-left: var(--spacing-container);
        padding-right: var(--spacing-container);
    }

    /* Skip to content — accessibility */
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

    .skip-to-content:focus { top: 0; }

    /* ── FOOTER ─────────────────────────────────────────────────────────────── */
    .portal-footer {
        background: linear-gradient(180deg, transparent 0%, rgba(0,132,61,0.04) 100%);
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
        font-size: 1rem;
        color: var(--color-text);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-brand img {
        width: 32px;
        height: 32px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .portal-footer p {
        margin: 0;
        font-size: .875rem;
        color: var(--color-text-muted);
    }

    .footer-contact {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
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

    .footer-contact a:hover { color: var(--color-primary); }

    .footer-contact span {
        font-size: .8rem;
        opacity: 0.8;
    }

    @media (max-width: 575.98px) { .footer-contact { margin-top: 1.5rem; } }

    @media (min-width: 768px) { .footer-contact { align-items: flex-end; } }

    /* Footer gold ornament divider */
    .footer-ornam {
        text-align: center;
        color: var(--color-gold);
        opacity: 0.5;
        font-size: 0.55rem;
        letter-spacing: 8px;
        margin-bottom: 1.25rem;
    }

    /* ── UTILITY CLASSES ────────────────────────────────────────────────────── */
    .text-primary-nu { color: var(--color-primary) !important; }
    .text-gold       { color: var(--color-gold) !important; }
    .bg-primary-nu   { background-color: var(--color-primary) !important; }
    .bg-gold         { background-color: var(--color-gold) !important; }
    .text-balance    { text-wrap: balance; }

    /* Bootstrap btn-success override to NU Green */
    .btn-success {
        --bs-btn-bg: var(--color-primary);
        --bs-btn-border-color: var(--color-primary);
        --bs-btn-hover-bg: var(--color-primary-hover);
        --bs-btn-hover-border-color: var(--color-primary-hover);
        --bs-btn-active-bg: var(--color-primary-dark);
        --bs-btn-active-border-color: var(--color-primary-dark);
        --bs-btn-focus-shadow-rgb: 0, 132, 61;
    }

    .btn-outline-success {
        --bs-btn-color: var(--color-primary);
        --bs-btn-border-color: var(--color-primary);
        --bs-btn-hover-bg: var(--color-primary);
        --bs-btn-hover-border-color: var(--color-primary);
    }

    /* Gold accent button */
    .btn-gold {
        background: var(--color-gold);
        border-color: var(--color-gold);
        color: #fff;
        font-weight: 600;
    }

    .btn-gold:hover {
        background: var(--color-gold-hover);
        border-color: var(--color-gold-hover);
        color: #fff;
    }

    /* Form focus rings — NU Green */
    .form-control:focus {
        border-color: rgba(0,132,61,.5);
        box-shadow: 0 0 0 3px rgba(0,132,61,.15);
    }

    .form-check-input:checked {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }

    /* Info box used in profil */
    .info-box {
        background: var(--color-primary-50);
        border: 1px solid var(--color-primary-light);
        border-radius: var(--radius-md);
        padding: .75rem 1rem;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: .875rem;
        color: var(--color-primary-dark);
    }

    .info-box i { color: var(--color-primary); flex-shrink: 0; margin-top: 1px; }

    /* Focus visible — accessibility */
    *:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
