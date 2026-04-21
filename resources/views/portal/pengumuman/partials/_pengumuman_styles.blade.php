<style>
    /* ═══════════════════════════════════════════════════════════════════════
       📢 PENGUMUMAN STYLES
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

    /* ── Timeline Styles ── */
    .timeline-container {
        padding: 1rem 0;
        overflow-x: auto;
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    .timeline-container::before {
        content: ""; position: absolute; top: 32px; left: 5%; right: 5%;
        height: 6px; background: #f1f5f9; z-index: 1; border-radius: var(--radius-full);
    }
    .timeline-step {
        position: relative; z-index: 2; display: flex; flex-direction: column;
        align-items: center; width: 100%; min-width: 120px; text-align: center;
    }
    .timeline-icon {
        width: 48px; height: 48px; border-radius: var(--radius-full);
        background: #fff; border: 3px solid #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 0.9rem; margin-bottom: 12px;
        color: #94a3b8; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .timeline-step.active .timeline-icon {
        background: var(--color-primary); border-color: var(--color-primary); color: #fff;
        transform: scale(1.15); box-shadow: 0 0 0 6px var(--color-primary-light);
    }
    .timeline-step.completed .timeline-icon { background: var(--color-primary-hover); border-color: var(--color-primary-hover); color: #fff; }
    .timeline-label { font-size: 0.85rem; font-weight: 700; color: var(--color-text); }
    .timeline-desc { font-size: 0.7rem; color: var(--color-text-muted); }

    /* ── Decision Cards ── */
    .decision-card {
        border-radius: var(--radius-lg);
        padding: clamp(1.5rem, 3vw, 2.25rem);
        text-align: center;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        margin-top: 0.5rem;
        box-shadow: inset 0 0 80px rgba(255,255,255,0.5);
    }
    .decision-card--success {
        background: #f0fdf4; border-color: #86efac; color: #15803d;
    }
    .decision-card--danger {
        background: #fef2f2; border-color: #fecaca; color: #991b1b;
    }
    .decision-card--info {
        background: #eff6ff; border-color: #bfdbfe; color: #1e40af;
    }

    .lulus-title { font-family: var(--font-accent); font-size: clamp(1.75rem, 4vw, 2.75rem); font-weight: 900; margin-bottom: 0.5rem; }
    
    .stats-grid { display: flex; justify-content: center; gap: 1rem; margin: 1.25rem 0; }
    .stats-item {
        background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(4px);
        padding: 0.75rem 1.5rem; border-radius: var(--radius-lg);
        border: 1.5px solid rgba(255, 255, 255, 0.5); box-shadow: var(--shadow-sm);
    }
    .stats-val { font-size: 1.5rem; font-weight: 900; color: var(--color-text); display: block; }
    .stats-label { font-size: 0.7rem; font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px; }

    /* Confetti */
    .confetti-canvas { position: absolute; inset: 0; pointer-events: none; z-index: 5; }

    /* ── Empty State ── */
    .empty-announcement { text-align: center; padding: 4rem 2rem; }
    .empty-img { width: 220px; margin-bottom: 2rem; animation: float 6s ease-in-out infinite; }
    @keyframes float { 0% { transform: translateY(0); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0); } }

    /* Mobile */
    @media (max-width: 991.98px) {
        .profile-greeting-inner { flex-direction: column; align-items: stretch; }
    }
</style>
