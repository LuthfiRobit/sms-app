<style>
    /* ═══════════════════════════════════════════════════════════════════════
       🎓 DAFTAR ULANG STYLES
       Layout: Header card + Navigation (kiri) + Content (kanan)
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
    .profile-greeting-card::before { width: 320px; height: 320px; top: -120px; right: -60px; }
    .profile-greeting-card::after  { width: 200px; height: 200px; bottom: -80px; right: 100px; }

    .profile-greeting-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    /* 👤 LEFT SIDE: Profil Siswa */
    .profile-user-side {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .foto-wrapper { width: 100px; height: 100px; flex-shrink: 0; }
    .foto-wrapper img {
        width: 100%; height: 100%; object-fit: cover;
        border-radius: var(--radius-full);
        box-shadow: 0 0 0 4px #fff, 0 0 0 6px rgba(255, 255, 255, 0.3);
    }
    .profile-name {
        font-size: clamp(1.25rem, 2.5vw, 1.75rem);
        font-weight: 900; color: #fff; margin: 0 0 0.5rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    .profile-status-badge {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px);
        color: #fff; font-size: 0.75rem; font-weight: 600;
        padding: 0.375rem 0.875rem; border-radius: var(--radius-full);
        border: 1.5px solid rgba(255, 255, 255, 0.3);
    }

    /* 📊 RIGHT SIDE: Quick Info */
    .pendaftaran-summary-side {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.75rem;
        min-width: 280px;
    }
    .summary-label { font-size: 0.7rem; font-weight: 800; color: rgba(255, 255, 255, 0.85); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; }
    .summary-val { font-size: 1.25rem; font-weight: 900; color: #fff; }

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
    .profil-nav-item {
        display: flex; align-items: center; padding: 0.875rem 1rem;
        border: 1.5px solid transparent; background: transparent;
        border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500;
        color: var(--color-text); text-decoration: none; transition: all 0.2s;
        gap: 0.75rem; margin-bottom: 0.375rem;
    }
    .profil-nav-item:hover { background: var(--color-primary-50); color: var(--color-primary-hover); transform: translateX(4px); }
    .profil-nav-item.active {
        background: linear-gradient(135deg, var(--color-primary-light), #bbf7d0);
        color: var(--color-primary-hover); font-weight: 700;
        border-color: var(--color-primary); box-shadow: var(--shadow-sm);
    }

    /* ── Portal Cards ── */
    .portal-card {
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-normal);
        overflow: hidden;
        margin-bottom: clamp(1.5rem, 3vw, 2.5rem);
    }
    .portal-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md), var(--shadow-primary);
    }

    .card-header-portal {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(to right, #f8fafc, #ffffff);
        border-bottom: 2px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .card-header-portal span {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 800;
        color: var(--color-text);
        font-size: 0.95rem;
    }
    .card-header-portal i {
        color: var(--color-primary);
        font-size: 1.2rem;
    }

    /* ── Countdown Styles ── */
    .du-countdown-wrap {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #86efac;
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .du-countdown-label {
        font-size: .8rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--color-primary-hover); margin-bottom: .75rem;
    }
    .du-countdown-timer { display: flex; gap: 1rem; align-items: center; }
    .du-countdown-number { font-size: 2rem; font-weight: 900; color: var(--color-primary-hover); line-height: 1; display: block; }
    .du-countdown-sep { font-size: 2rem; font-weight: 900; color: #86efac; margin-top: -6px; }
    .du-countdown-unit-label { font-size: .65rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; text-align: center; }

    /* ── Confirmation Styles ── */
    .du-confirm-section { border: 2px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem; background: #fafafa; }
    .du-checkbox-wrap {
        display: flex; align-items: flex-start; gap: 12px; padding: 1rem;
        background: #fff; border: 1.5px solid var(--color-border);
        border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s; margin-bottom: 1.25rem;
    }
    .du-checkbox-wrap:hover { border-color: var(--color-primary); background: var(--color-primary-50); }
    .du-checkbox-wrap:has(input:checked) { border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-light); }
    .du-checkbox-wrap input[type="checkbox"] { width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0; margin-top: 1px; accent-color: var(--color-primary); cursor: pointer; }
    .du-checkbox-label { font-size: .9rem; color: var(--color-text); line-height: 1.5; user-select: none; cursor: pointer; }

    .du-btn-confirm {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
        border: none; border-radius: var(--radius-md); padding: .875rem 2rem;
        font-size: 1rem; font-weight: 700; color: #fff; width: 100%;
        cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-primary);
        display: flex; align-items: center; justify-content: center; gap: .6rem;
    }
    .du-btn-confirm:disabled { opacity: .4; cursor: not-allowed; box-shadow: none; transform: none !important; }
    .du-btn-confirm:not(:disabled):hover { transform: translateY(-2px); box-shadow: var(--shadow-md), var(--shadow-primary); }

    /* ── Celebration State ── */
    .du-celebration-header {
        background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
        padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden;
    }
    .confetti-overlay { position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 10; }
    .confetti-piece { position: absolute; width: 9px; height: 9px; border-radius: 2px; animation: confetti-rain 3s ease-out infinite; opacity: 0; }
    @keyframes confetti-rain {
        0%   { transform: translateY(-30px) rotate(0deg);   opacity: 1; }
        100% { transform: translateY(600px) rotate(720deg); opacity: 0; }
    }

    .du-tile { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: var(--radius-md); padding: 0.85rem 1.2rem; text-align: center; backdrop-filter: blur(8px); }
    .du-tile-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: .85; color: #fff; font-weight: 800; }
    .du-tile-value { font-size: 1rem; font-weight: 900; color: #fff; margin-top: 2px; }

    /* ── Alert Styles ── */
    .du-alert { padding: 1.25rem; border-radius: var(--radius-lg); border: 2px solid transparent; display: flex; align-items: flex-start; gap: 1rem; }
    .du-alert-success { background: #f0fdf4; border-color: #86efac; color: #166534; }
    .du-alert-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .du-alert-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .du-alert-icon { font-size: 1.5rem; line-height: 1; flex-shrink: 0; }

    /* ── Toast ── */
    .du-toast-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: .75rem; pointer-events: none; }
    .du-toast {
        background: var(--color-dark); color: #fff; padding: .85rem 1.25rem;
        border-radius: var(--radius-md); font-size: .88rem; font-weight: 600;
        box-shadow: var(--shadow-lg); transform: translateX(120%);
        transition: transform .35s cubic-bezier(.4,0,.2,1); pointer-events: auto; max-width: 360px;
    }
    .du-toast.show { transform: translateX(0); }
    .du-toast-success { background: var(--color-primary-hover); }
    .du-toast-error   { background: var(--color-danger); }

    .du-pulse-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--color-primary); animation: pulse-dot 1.5s ease-in-out infinite; }
    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4); }
        50%       { box-shadow: 0 0 0 8px rgba(22, 163, 74, 0); }
    }

    @media (max-width: 767.98px) {
        .du-countdown-timer { gap: 0.5rem; }
        .du-countdown-number { font-size: 1.5rem; }
        .du-countdown-sep { font-size: 1.5rem; }
    }
</style>
