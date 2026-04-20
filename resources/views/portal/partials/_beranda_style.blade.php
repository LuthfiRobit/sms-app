<style>
    /* BERANDA / DASHBOARD STYLES Menggunakan design tokens dari portal.blade.php untuk konsistensi */

    /* HERO SECTION - First Impression Full-height hero dengan gradient background dan floating shapes */
    .hero-section {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent-teal) 100%);
        position: relative;
        overflow: hidden;
        padding: 3rem 0 4rem;
        margin-top: -1.5rem;
        /* Negative margin untuk seamless connection dengan navbar */
    }

    /* Decorative shapes untuk visual interest tanpa mengganggu readability */
    .hero-bg-shapes {
        position: absolute;
        inset: 0;
        opacity: 0.08;
        overflow: hidden;
    }

    .hero-bg-shapes .shape {
        position: absolute;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.4);
    }

    .shape-1 {
        width: 400px;
        height: 400px;
        top: -200px;
        left: -100px;
        filter: blur(80px);
    }

    .shape-2 {
        width: 300px;
        height: 300px;
        bottom: -150px;
        right: -50px;
        filter: blur(60px);
    }

    .shape-3 {
        width: 200px;
        height: 200px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        filter: blur(40px);
    }

    /* Hero container dengan min-height yang nyaman */
    .min-vh-hero {
        min-height: clamp(400px, 50vh, 600px);
    }

    /* Status badge di atas heading - sangat prominent */
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.875rem;
        font-weight: 600;
        color: #fff;
        box-shadow: var(--shadow-sm);
        animation: badgePulse 2s ease-in-out infinite;
    }

    @keyframes badgePulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }
    }

    /* Status dots - animated untuk menarik perhatian */
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: var(--radius-full);
        display: inline-block;
        flex-shrink: 0;
    }

    .status-buka {
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
        animation: statusBlink 1.5s ease-in-out infinite;
    }

    .status-tutup {
        background: #ef4444;
        opacity: 0.7;
    }

    @keyframes statusBlink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    /* Hero title dengan gradient text */
    .hero-title {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 900;
        color: #fff;
        line-height: 1.15;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }

    .text-gradient {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
    }

    /* Hero subtitle dengan backdrop untuk readability */
    .hero-subtitle {
        font-size: clamp(0.95rem, 2vw, 1.125rem);
        color: rgba(255, 255, 255, 0.92);
        line-height: 1.7;
        margin-bottom: 2rem;
        max-width: 560px;
        backdrop-filter: blur(2px);
    }

    /* Action buttons container */
    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    /* Primary CTA - sangat prominent dengan shadow besar */
    .btn-hero-primary {
        background: #fff;
        color: var(--color-primary) !important;
        font-size: 1rem;
        font-weight: 700;
        padding: 0.875rem 1.75rem;
        border-radius: var(--radius-lg);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border: 2px solid transparent;
        transition: all var(--transition-normal);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        color: var(--color-primary-hover) !important;
    }

    .btn-hero-primary:active {
        transform: translateY(-1px);
    }

    /* Secondary CTA */
    .btn-hero-secondary {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(8px);
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        border-radius: var(--radius-lg);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        transition: all var(--transition-normal);
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }

    /* Outline button untuk less prominent actions */
    .btn-hero-outline {
        background: transparent;
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 0.875rem 1.5rem;
        border-radius: var(--radius-lg);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        transition: all var(--transition-fast);
    }

    .btn-hero-outline:hover {
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.08);
    }

    /* Hero illustration dengan floating animation */
    .hero-illustration {
        filter: drop-shadow(0 8px 32px rgba(0, 0, 0, 0.12));
        animation: heroFloat 4s ease-in-out infinite;
    }

    @keyframes heroFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-16px);
        }
    }

    /* Mobile: Stack buttons vertically */
    @media (max-width: 575.98px) {
        .hero-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-hero-primary,
        .btn-hero-secondary,
        .btn-hero-outline {
            width: 100%;
            justify-content: center;
        }
    }

    /* STATUS SECTION - Info Cards
                                           3 cards: Status PPDB, Periode Pendaftaran, Jalur Tersedia */
    .section-status {
        background: var(--color-primary-50);
        padding: 3rem 0;
    }

    /* Info card dengan left border accent untuk visual hierarchy */
    .info-card {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid transparent;
        transition: all var(--transition-normal);
        border: 1px solid var(--color-border-light);
    }

    .info-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
        border-left-width: 6px;
    }

    /* Color variants untuk different semantic meanings */
    .info-card-primary {
        border-left-color: var(--color-primary);
    }

    .info-card-warning {
        border-left-color: var(--color-warning);
    }

    .info-card-info {
        border-left-color: var(--color-info);
    }

    /* Icon dengan subtle background */
    .info-card-icon {
        font-size: 2rem;
        color: var(--color-primary);
        line-height: 1;
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primary-light);
        border-radius: var(--radius-md);
        transition: all var(--transition-fast);
    }

    .info-card:hover .info-card-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .info-card-icon.text-warning {
        color: var(--color-warning);
        background: #fef3c7;
    }

    .info-card-icon.text-info {
        color: var(--color-info);
        background: #dbeafe;
    }

    /* Info card text hierarchy */
    .info-card-label {
        font-size: 0.75rem;
        color: var(--color-text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .info-card-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--color-text);
        line-height: 1.3;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .info-card-sub {
        font-size: 0.85rem;
        color: var(--color-text-muted);
        line-height: 1.4;
    }

    /*  SECTION HEADERS - Consistent styling untuk semua section */
    .section-badge {
        display: inline-block;
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        padding: 0.375rem 0.875rem;
        border-radius: var(--radius-full);
        margin-bottom: 1rem;
        border: 1px solid rgba(22, 163, 74, 0.2);
    }

    .section-title {
        font-size: clamp(1.75rem, 4vw, 2.25rem);
        font-weight: 800;
        color: var(--color-text);
        margin-bottom: 0.75rem;
        letter-spacing: -0.02em;
    }

    .section-subtitle {
        color: var(--color-text-muted);
        max-width: 600px;
        margin: 0 auto;
        font-size: 1rem;
        line-height: 1.6;
    }

    /* JALUR PENDAFTARAN - Cards untuk setiap jalur */
    .jalur-card {
        background: var(--color-surface);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    /* Top accent bar untuk visual hierarchy */
    .jalur-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
    }

    .jalur-card:hover {
        border-color: var(--color-primary);
        box-shadow: var(--shadow-lg);
        transform: translateY(-6px);
    }

    /* Jalur card header dengan kode dan status badge */
    .jalur-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .jalur-kode {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--color-text-subtle);
        background: var(--color-border-light);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-sm);
    }

    /* Badge untuk status aktif */
    .badge.bg-light-success {
        background: var(--color-primary-light) !important;
        color: var(--color-primary-hover) !important;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-sm);
    }

    /* Jalur content */
    .jalur-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--color-text);
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .jalur-desc {
        font-size: 0.9rem;
        color: var(--color-text-muted);
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    /* Metadata items dengan icons */
    .jalur-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-border-light);
    }

    .jalur-meta-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 0.875rem;
        color: var(--color-text);
    }

    .jalur-meta-item i {
        color: var(--color-primary);
        font-size: 1rem;
        width: 20px;
        flex-shrink: 0;
    }

    /* LANGKAH PENDAFTARAN - Step-by-step guide */
    .section-steps {
        background: var(--color-bg);
        padding: 4rem 0;
    }

    .step-card {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        padding: 2rem 1.5rem;
        position: relative;
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-sm);
        border: 2px solid transparent;
        height: 100%;
    }

    .step-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
        border-color: var(--color-primary-light);
    }

    /* Large step number sebagai watermark */
    .step-num {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 3.5rem;
        font-weight: 900;
        color: var(--color-primary-50);
        line-height: 1;
        opacity: 0.6;
    }

    /* Icon container dengan colored background */
    .step-icon {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 1.25rem;
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-fast);
    }

    .step-card:hover .step-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    .bg-light-primary {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
        color: #1e40af !important;
    }

    .bg-light-success {
        background: linear-gradient(135deg, var(--color-primary-light), #bbf7d0) !important;
        color: var(--color-primary-hover) !important;
    }

    .bg-light-warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
        color: #92400e !important;
    }

    .bg-light-info {
        background: linear-gradient(135deg, #e0f2fe, #bae6fd) !important;
        color: #075985 !important;
    }

    /* Step text */
    .step-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 0.75rem;
        text-align: center;
    }

    .step-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        line-height: 1.6;
        margin: 0;
        text-align: center;
    }

    /*  TIMELINE AGENDA - Vertical timeline dengan status indicators */
    .timeline {
        position: relative;
        padding-left: 2.5rem;
    }

    /* Vertical line */
    .timeline::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--color-border), transparent);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 1rem;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    /* Timeline dot/icon indicator */
    .timeline-dot {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--color-text-subtle);
        background: var(--color-surface);
        border: 3px solid var(--color-border);
        border-radius: var(--radius-full);
        z-index: 1;
        transition: all var(--transition-fast);
    }

    /* Active state - prominent dengan color dan animation */
    .timeline-aktif .timeline-dot {
        color: var(--color-primary);
        border-color: var(--color-primary);
        background: var(--color-primary-light);
        box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.1);
        animation: timelinePulse 2s ease-in-out infinite;
    }

    @keyframes timelinePulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    /* Completed state - muted */
    .timeline-selesai .timeline-dot {
        color: #9ca3af;
        border-color: #d1d5db;
    }

    /* Timeline content card */
    .timeline-content {
        background: var(--color-surface);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-xs);
    }

    .timeline-content:hover {
        box-shadow: var(--shadow-sm);
        transform: translateX(4px);
    }

    /* Active timeline item dengan accent background */
    .timeline-aktif .timeline-content {
        border-color: var(--color-primary);
        background: var(--color-primary-50);
        box-shadow: var(--shadow-sm);
    }

    /* Completed timeline item dengan reduced opacity */
    .timeline-selesai .timeline-content {
        opacity: 0.65;
        border-style: dashed;
    }

    /* Timeline text elements */
    .timeline-date {
        font-size: 0.75rem;
        color: var(--color-text-subtle);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 0.5rem;
        display: block;
    }

    .timeline-title {
        font-weight: 700;
        color: var(--color-text);
        font-size: 1.05rem;
        margin-bottom: 0.5rem;
    }

    .timeline-jalur {
        font-size: 0.75rem;
        color: var(--color-text-muted);
        background: var(--color-border-light);
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        display: inline-block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .timeline-keterangan {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        line-height: 1.5;
        margin: 0;
    }

    /* AGENDA NAVIGATION - Vertical Tabs style from Profile page */
    .agenda-nav {
        display: flex;
        flex-direction: column;
        gap: 6px;
        background: #fdfdfd;
        padding: 1rem;
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-xs);
    }

    .agenda-nav-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: none;
        background: transparent;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--color-text-subtle);
        cursor: pointer;
        transition: all var(--transition-normal);
        width: 100%;
        text-align: left;
        position: relative;
        overflow: hidden;
    }

    .agenda-nav-item:hover {
        background: var(--color-primary-light);
        color: var(--color-primary-hover);
    }

    .agenda-nav-item.active {
        background: #dcfce7;
        color: var(--color-primary-hover);
        box-shadow: inset 4px 0 0 var(--color-primary);
    }

    .agenda-nav-item .nav-icon {
        margin-right: 12px;
        font-size: 1.1rem;
        transition: transform 0.3s;
    }

    .agenda-nav-item.active .nav-icon {
        transform: scale(1.1);
    }

    .agenda-pane {
        display: none;
        animation: paneFadeIn 0.4s ease-out;
    }

    .agenda-pane.active {
        display: block;
    }

    @keyframes paneFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile Adjustments for Agenda Nav */
    @media (max-width: 991.98px) {
        .agenda-nav {
            flex-direction: row;
            overflow-x: auto;
            padding: 0.75rem;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .agenda-nav-item {
            width: auto;
            min-width: fit-content;
            padding: 10px 15px;
            font-size: 0.85rem;
        }

        .agenda-nav-item.active {
            box-shadow: inset 0 -3px 0 var(--color-primary);
        }
    }

    /* Mobile: Reduce padding untuk better space utilization */
    @media (max-width: 575.98px) {
        .timeline {
            padding-left: 2rem;
        }

        .timeline-dot {
            left: -1.75rem;
            width: 22px;
            height: 22px;
            font-size: 0.95rem;
        }
    }

    /* CTA SECTION - Final call-to-action */
    .cta-section {
        background: linear-gradient(135deg, #064e3b 0%, var(--color-primary-hover) 100%);
        padding: 4rem 0;
        position: relative;
        overflow: hidden;
    }

    /* Decorative overlay */
    .cta-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 30% 50%, rgba(255, 255, 255, 0.05), transparent);
        pointer-events: none;
    }

    .cta-card {
        max-width: 700px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .cta-title {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 900;
        color: #fff;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .cta-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.05rem;
        margin-bottom: 2rem;
        line-height: 1.7;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
    }

    /* CTA buttons inherit dari hero buttons dengan slight adjustments */
    .cta-section .btn-hero-primary {
        background: #fff;
        color: var(--color-primary) !important;
    }

    .cta-section .btn-hero-secondary {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
    }

    /* Mobile: Stack CTA buttons */
    @media (max-width: 575.98px) {
        .cta-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .cta-section .btn-hero-primary,
        .cta-section .btn-hero-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    /* UTILITY & RESPONSIVE HELPERS */

    /* Smooth scrolling untuk anchor links */
    html {
        scroll-behavior: smooth;
    }

    /* Responsive spacing helpers */
    @media (max-width: 767.98px) {

        .section-status,
        .section-steps {
            padding: 2.5rem 0;
        }

        .cta-section {
            padding: 3rem 0;
        }
    }

    /* Loading state untuk dynamic content */
    @keyframes shimmer {
        0% {
            background-position: -100% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    .skeleton-loader {
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: var(--radius-sm);
    }

    /* Print styles - untuk cetak halaman */
    @media print {

        .hero-section,
        .cta-section {
            background: none !important;
            color: #000 !important;
        }

        .btn-hero-primary,
        .btn-hero-secondary,
        .btn-hero-outline {
            display: none;
        }
    }
</style>