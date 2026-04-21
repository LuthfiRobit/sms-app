@extends('layouts.portal')

@section('title', 'Pilih Jalur Pendaftaran — PPDB')

@push('styles')
    <style>
        /* PILIH JALUR PENDAFTARAN - PAGE STYLES Halaman untuk memilih jalur pendaftaran PPDB Design mengikuti system dari portal & dashboard */

        /* PAGE HEADER CARD - Adopsi style pendaftaran-greeting-card */
        .pendaftaran-greeting-card {
            background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
            border-radius: var(--radius-xl);
            padding: clamp(1.5rem, 3vw, 2.5rem);
            box-shadow: var(--shadow-primary);
            position: relative;
            overflow: hidden;
            margin-bottom: clamp(1.5rem, 3vw, 2rem);
        }

        /* Decorative circles */
        .pendaftaran-greeting-card::before,
        .pendaftaran-greeting-card::after {
            content: '';
            position: absolute;
            border-radius: var(--radius-full);
            opacity: 0.08;
            pointer-events: none;
            background: #fff;
        }

        .pendaftaran-greeting-card::before {
            width: 320px;
            height: 320px;
            top: -120px;
            right: -60px;
        }

        .pendaftaran-greeting-card::after {
            width: 200px;
            height: 200px;
            bottom: -80px;
            right: 100px;
        }

        /* Inner container */
        .pendaftaran-greeting-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        /* LEFT SECTION - Info Pendaftaran */
        .pendaftaran-info-section {
            flex: 1;
            min-width: 280px;
        }

        .pendaftaran-header-content {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .pendaftaran-header-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .pendaftaran-header-text {
            flex: 1;
        }

        .pendaftaran-page-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 900;
            color: #fff;
            margin: 0 0 0.5rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            line-height: 1.2;
        }

        .pendaftaran-page-subtitle {
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
            line-height: 1.5;
        }

        .pendaftaran-actions {
            margin-top: 1.25rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-pendaftaran-saya {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            color: #fff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .btn-pendaftaran-saya:hover {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff !important;
            transform: translateY(-2px);
        }

        /* RIGHT SECTION - User Profile Info */
        .user-profile-section {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            min-width: 280px;
        }

        .user-foto-wrapper {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
        }

        .user-foto-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--radius-full);
            box-shadow: 0 0 0 3px #fff, 0 0 0 5px rgba(255, 255, 255, 0.3);
        }

        .user-info-text {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 0.375rem;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .user-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-status-badge i {
            font-size: 0.6rem;
        }

        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            .pendaftaran-greeting-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .pendaftaran-info-section,
            .user-profile-section {
                min-width: 0;
                width: 100%;
            }

            .user-profile-section {
                justify-content: center;
            }
        }

        @media (max-width: 575.98px) {
            .pendaftaran-header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .pendaftaran-actions {
                width: 100%;
            }

            .btn-pendaftaran-saya {
                width: 100%;
                justify-content: center;
            }

            .user-profile-section {
                flex-direction: column;
                text-align: center;
            }
        }

        /* PROFIL WARNING - Mengadopsi profile-alert dari dashboard Alert untuk profil yang belum lengkap */
        .profil-warning-card {
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
            margin-bottom: clamp(1.5rem, 3vw, 2rem);
        }

        .profil-warning-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .profil-warning-icon {
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

        .profil-warning-body {
            flex: 1;
            min-width: 0;
        }

        .profil-warning-title {
            font-weight: 700;
            color: #92400e;
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profil-warning-desc {
            font-size: 0.875rem;
            color: #78350f;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .profil-kekurangan-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .profil-kekurangan-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            background: rgba(251, 191, 36, 0.15);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: #78350f;
            font-weight: 500;
        }

        .kekurangan-icon {
            flex-shrink: 0;
        }

        .btn-lengkapi {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .btn-lengkapi:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Mobile: Full width button */
        @media (max-width: 575.98px) {
            .profil-warning-card {
                flex-direction: column;
            }

            .btn-lengkapi {
                width: 100%;
                justify-content: center;
            }
        }

        /* EMPTY STATE - Tidak ada jalur */
        .no-jalur-card {
            padding: clamp(2.5rem, 5vw, 4rem) 1.5rem;
            text-align: center;
            max-width: 520px;
            margin: 0 auto;
            background: var(--color-surface);
            border-radius: var(--radius-xl);
            border: 2px dashed var(--color-border);
            box-shadow: var(--shadow-sm);
        }

        .no-jalur-card>div:first-child {
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

        .no-jalur-card h5 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.75rem;
        }

        .no-jalur-card p {
            font-size: 0.95rem;
            color: var(--color-text-muted);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        /* SECTION HEADING */
        .section-heading-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .section-heading {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-heading i {
            font-size: 1.25rem;
            color: var(--color-primary);
        }

        .section-heading .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
        }

        .warning-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.625rem 1rem;
            background: #fffbeb;
            color: #92400e;
            border: 1.5px solid #fde68a;
            border-radius: var(--radius-md);
            font-size: 0.8125rem;
            font-weight: 600;
        }

        /* JALUR GRID - Card layout untuk jalur pendaftaran */
        .jalur-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 575.98px) {
            .jalur-grid {
                grid-template-columns: 1fr;
            }
        }

        /* JALUR CARD - Individual card per jalur */
        .jalur-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--color-border);
            overflow: hidden;
            transition: all var(--transition-normal);
            display: flex;
            flex-direction: column;
        }

        .jalur-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .jalur-card--disabled {
            opacity: 0.7;
        }

        .jalur-card--disabled:hover {
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        /* Card Header dengan gradient */
        .jalur-card-header {
            padding: 1.5rem;
            position: relative;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            min-height: 140px;
        }

        .jalur-header-content {
            flex: 1;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        .jalur-kode-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-sm);
            margin-bottom: 0.75rem;
            backdrop-filter: blur(4px);
        }

        .jalur-nama {
            font-size: 1.0625rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 0.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .jalur-tahun {
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-weight: 500;
        }

        .jalur-status-badge-wrapper {
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .jalur-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 700;
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            white-space: nowrap;
        }

        .chip-tersedia {
            background: rgba(16, 185, 129, 0.25);
            color: #ecfdf5;
            border: 1.5px solid rgba(16, 185, 129, 0.4);
        }

        .chip-sudah {
            background: rgba(59, 130, 246, 0.25);
            color: #dbeafe;
            border: 1.5px solid rgba(59, 130, 246, 0.4);
        }

        .chip-penuh {
            background: rgba(239, 68, 68, 0.25);
            color: #fee2e2;
            border: 1.5px solid rgba(239, 68, 68, 0.4);
        }

        .chip-tutup {
            background: rgba(156, 163, 175, 0.25);
            color: #f3f4f6;
            border: 1.5px solid rgba(156, 163, 175, 0.4);
        }

        /* Card Body */
        .jalur-card-body {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .jalur-desc {
            font-size: 0.875rem;
            color: var(--color-text-muted);
            line-height: 1.6;
            margin: 0;
        }

        /* Info Grid - 3 kolom info */
        .jalur-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1rem;
            background: var(--color-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--color-border-light);
        }

        @media (max-width: 575.98px) {
            .jalur-info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
        }

        .jalur-info-item {
            text-align: center;
        }

        .jalur-info-label {
            font-size: 0.68rem;
            color: var(--color-text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            margin-bottom: 0.375rem;
        }

        .jalur-info-val {
            font-size: 0.85rem;
            color: var(--color-text);
            font-weight: 700;
            line-height: 1.2;
        }

        .hampir-penuh-text {
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        /* Kuota Progress Bar */
        .kuota-progress-wrapper {
            margin-top: 0.5rem;
        }

        .kuota-progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .kuota-progress-track {
            height: 8px;
            background: var(--color-border-light);
            border-radius: var(--radius-full);
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .kuota-progress-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .kuota-progress-fill::after {
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

        /* Kuota Jurusan List */
        .kuota-jurusan-list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .kuota-jurusan-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem 0.875rem;
            background: var(--color-bg);
            border-radius: var(--radius-sm);
            font-size: 0.8125rem;
            border: 1px solid var(--color-border-light);
        }

        .kj-nama {
            color: var(--color-text);
            font-weight: 500;
            flex: 1;
        }

        .kj-sisa {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            background: var(--color-primary-light);
            color: var(--color-primary-hover);
            border-radius: var(--radius-sm);
        }

        /* Accordion untuk detail tambahan */
        .jalur-accordion {
            border: none;
        }

        .jalur-acc-item {
            border: 1px solid var(--color-border-light) !important;
            border-radius: var(--radius-md) !important;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .jalur-acc-item:last-child {
            margin-bottom: 0;
        }

        .jalur-acc-btn {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--color-text);
            background: var(--color-bg) !important;
            padding: 0.75rem 1rem;
            border: none;
            transition: all var(--transition-fast);
        }

        .jalur-acc-btn:not(.collapsed) {
            color: var(--color-primary-hover);
            background: var(--color-primary-50) !important;
        }

        .jalur-acc-btn:hover {
            background: var(--color-primary-light) !important;
        }

        .jalur-acc-btn::after {
            width: 16px;
            height: 16px;
            background-size: 16px;
        }

        .jalur-acc-body {
            padding: 1rem;
            background: var(--color-surface);
            border-top: 1px solid var(--color-border-light);
        }

        /* Syarat List */
        .syarat-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .syarat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0;
            border-bottom: 1px solid var(--color-border-light);
            font-size: 0.8125rem;
            color: var(--color-text);
        }

        .syarat-item:last-child {
            border-bottom: none;
        }

        .syarat-item i {
            color: var(--color-primary);
        }

        .syarat-item .ms-auto {
            flex-shrink: 0;
        }

        /* Jadwal List */
        .jadwal-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .jadwal-item {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--color-bg);
            border-radius: var(--radius-md);
            font-size: 0.8125rem;
            border: 1px solid var(--color-border-light);
        }

        .jadwal-item--aktif {
            background: var(--color-primary-50);
            border: 1.5px solid var(--color-primary-light);
        }

        .jadwal-tipe-badge {
            display: inline-block;
            background: var(--color-border);
            color: var(--color-text);
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-sm);
            flex-shrink: 0;
        }

        .jadwal-item--aktif .jadwal-tipe-badge {
            background: var(--color-primary-light);
            color: var(--color-primary-hover);
        }

        .jadwal-tanggal {
            font-size: 0.78rem;
            color: var(--color-text-muted);
            flex: 1;
            font-weight: 500;
        }

        /* Card Footer dengan action button */
        .jalur-card-footer {
            padding: 1.25rem 1.5rem;
            background: var(--color-bg);
            border-top: 1px solid var(--color-border-light);
        }

        .btn-jalur-action {
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-jalur-action:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-success.btn-jalur-action {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border: none;
            box-shadow: var(--shadow-primary);
        }

        .btn-success.btn-jalur-action:hover {
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.35);
        }

        .btn-outline-secondary.btn-jalur-action {
            border-width: 1.5px;
        }

        /* MODAL KONFIRMASI Modal untuk konfirmasi pilihan jalur */
        .modal-konfirmasi {
            border: none;
            border-radius: var(--radius-2xl);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .modal-konfirmasi-header {
            background: linear-gradient(135deg, var(--color-primary-50), var(--color-primary-light));
            border-bottom: 2px solid var(--color-primary-light);
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .modal-konfirmasi-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: var(--shadow-primary);
        }

        .modal-konfirmasi-header h5 {
            flex: 1;
            margin: 0.5rem 0 0;
            font-weight: 700;
            color: var(--color-text);
        }

        .modal-konfirmasi .modal-body {
            padding: 1.5rem;
        }

        .modal-jalur-info {
            background: var(--color-bg);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .modal-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .modal-info-label {
            font-size: 0.8125rem;
            color: var(--color-text-subtle);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
        }

        .modal-info-val {
            font-size: 0.9375rem;
            color: var(--color-text);
            font-weight: 700;
            text-align: right;
        }

        .modal-warning-box {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.8125rem;
            color: #1e40af;
            line-height: 1.6;
        }

        .modal-warning-box i {
            flex-shrink: 0;
            font-size: 1.125rem;
        }

        .modal-checkbox-wrapper {
            background: var(--color-surface);
            border: 2px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 1.25rem;
        }

        .modal-checkbox-wrapper .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            border-width: 2px;
            cursor: pointer;
        }

        .modal-checkbox-wrapper .form-check-input:checked {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .modal-checkbox-wrapper .form-check-label {
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--color-text);
            cursor: pointer;
            user-select: none;
        }

        .modal-konfirmasi-footer {
            background: var(--color-bg);
            border-top: 1px solid var(--color-border);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-konfirmasi-footer .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: var(--radius-md);
        }

        /* RESPONSIVE UTILITIES */
        @media (max-width: 575.98px) {
            .modal-konfirmasi-footer {
                flex-direction: column-reverse;
            }

            .modal-konfirmasi-footer .btn {
                width: 100%;
            }
        }

        /* ACCESSIBILITY ENHANCEMENTS */
        .btn-jalur-action:focus-visible,
        .jalur-acc-btn:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .modal-checkbox-wrapper .form-check-input:focus-visible {
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {

            .jalur-card,
            .btn-jalur-action,
            .kuota-progress-fill,
            .profil-warning-icon {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
@endpush

@section('content')

    @php
        $jalurList = $jalur['data'] ?? [];
        $profilCukup = $cekProfil['cukup'] ?? false;
        $kekurangan = $cekProfil['kekurangan'] ?? [];

        /**
         * Warna header card per index (siklus 6 warna)
         */
        $cardColors = [
            ['bg' => '#15803d', 'light' => '#dcfce7', 'border' => '#86efac'],
            ['bg' => '#0369a1', 'light' => '#dbeafe', 'border' => '#93c5fd'],
            ['bg' => '#7c3aed', 'light' => '#ede9fe', 'border' => '#c4b5fd'],
            ['bg' => '#b45309', 'light' => '#fef3c7', 'border' => '#fcd34d'],
            ['bg' => '#be185d', 'light' => '#fce7f3', 'border' => '#f9a8d4'],
            ['bg' => '#0f766e', 'light' => '#ccfbf1', 'border' => '#5eead4'],
        ];

        $bulanIndo = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agt',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];
        $fmtDate = function ($d) use ($bulanIndo) {
            if (!$d)
                return '—';
            $dt = $d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d);
            return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
        };
        $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
    @endphp

    {{-- PAGE HEADER - Mengadopsi greeting-card dari dashboard Sisi kiri: Info halaman | Sisi kanan: Info profil user --}}
    <div class="pendaftaran-greeting-card">
        <div class="pendaftaran-greeting-inner">

            {{-- LEFT SECTION: Header Info --}}
            <div class="pendaftaran-info-section">
                <div class="pendaftaran-header-content">
                    <div class="pendaftaran-header-icon" aria-hidden="true">
                        <i class="bi bi-signpost-2-fill"></i>
                    </div>

                    <div class="pendaftaran-header-text">
                        <h1 class="pendaftaran-page-title">Pilih Jalur Pendaftaran</h1>
                        <p class="pendaftaran-page-subtitle">
                            Pilih jalur yang sesuai dengan kelayakan Anda
                        </p>
                    </div>
                </div>

                <div class="pendaftaran-actions">
                    <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-pendaftaran-saya">
                        <i class="bi bi-arrow-left me-2"></i>
                        Pendaftaran Saya
                    </a>
                </div>
            </div>

            {{-- RIGHT SECTION: User Profile --}}
            @php $user = auth()->user(); @endphp
            <div class="user-profile-section">
                <div class="user-foto-wrapper">
                    <img src="{{ $user->peserta?->foto
        ? asset('storage/' . $user->peserta->foto)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=16a34a&color=fff&bold=true&rounded=true' }}"
                        alt="Foto {{ $user->name }}">
                </div>

                <div class="user-info-text">
                    <div class="user-name">{{ $user->name }}</div>

                    <div class="user-email">
                        <i class="bi bi-envelope-fill me-1"></i>
                        <span>{{ $user->email }}</span>
                    </div>

                    <span class="user-status-badge">
                        <i class="bi bi-circle-fill"></i>
                        Akun Aktif
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close notification"></button>
        </div>
    @endif

    {{-- ⚠️ PROFIL BELUM CUKUP - Warning alert Muncul jika profil belum lengkap untuk mendaftar --}}
    @if(!$profilCukup)
        <div class="profil-warning-card" role="alert">
            <div class="profil-warning-icon" aria-hidden="true">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div class="profil-warning-body">
                <h2 class="profil-warning-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning" aria-hidden="true"></i>
                    Lengkapi Data Profil Sebelum Mendaftar
                </h2>
                <p class="profil-warning-desc">
                    Sistem membutuhkan data profil yang lengkap untuk memastikan pendaftaran Anda
                    valid dan sesuai standar Dapodik. Harap lengkapi data berikut terlebih dahulu:
                </p>
                <ul class="profil-kekurangan-list">
                    @foreach($kekurangan as $item)
                        <li class="profil-kekurangan-item">
                            <span class="kekurangan-icon">
                                <i class="bi bi-x-circle-fill text-danger" aria-hidden="true"></i>
                            </span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('ppdb.profil.index') }}" class="btn btn-warning btn-lengkapi">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    Lengkapi Profil Sekarang
                </a>
            </div>
        </div>
    @endif

    {{-- KONTEN JALUR PENDAFTARAN Grid card untuk setiap jalur yang tersedia --}}
    @if(empty($jalurList))
        {{-- Empty state - Tidak ada jalur tersedia --}}
        <div class="no-jalur-card">
            <div aria-hidden="true">📋</div>
            <h2>Belum Ada Jalur Pendaftaran Aktif</h2>
            <p>
                Saat ini belum ada jalur pendaftaran yang terbuka.<br>
                Pantau terus informasi pembukaan PPDB.
            </p>
            <a href="{{ route('ppdb.dashboard') }}" class="btn btn-outline-success">
                <i class="bi bi-house" aria-hidden="true"></i>
                Kembali ke Dashboard
            </a>
        </div>
    @else
        {{-- Section heading --}}
        <div class="section-heading-row">
            <h2 class="section-heading">
                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>
                Pilih Jalur yang Sesuai
                <span class="badge bg-success-subtle text-success">
                    {{ count($jalurList) }} Jalur
                </span>
            </h2>
            @if(!$profilCukup)
                <div class="warning-info-badge">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    Lengkapi profil untuk dapat mendaftar
                </div>
            @endif
        </div>

        {{-- Grid jalur --}}
        <div class="jalur-grid">
            @foreach($jalurList as $idx => $item)
                @php
                    $jalur = $item['jalur'];
                    $pembukaan = $item['pembukaan'];
                    $tahun = $item['tahun_pelajaran'];
                    $jadwalList = $item['jadwal'] ?? collect();
                    $syaratList = $item['syarat'] ?? collect();
                    $kuotaJurusan = $item['kuota_jurusan'] ?? collect();
                    $biayaList = $item['biaya'] ?? collect();
                    $kuotaTersedia = $item['kuota_tersedia'] ?? 0;
                    $sudahDaftar = $item['sudah_daftar'] ?? false;
                    $bisaDaftar = $item['bisa_daftar'] ?? false;
                    $jadwalAktif = $item['jadwal_aktif'] ?? null;
                    $pendAktif = $item['pendaftaran_aktif'] ?? null;

                    // Total kuota dari kuota jurusan
                    $totalKuota = $kuotaJurusan->sum('kuota');
                    $totalTerisi = $kuotaJurusan->sum('terisi');
                    $pctTerisi = $totalKuota > 0 ? round(($totalTerisi / $totalKuota) * 100) : 0;
                    $hampirPenuh = $kuotaTersedia > 0 && $kuotaTersedia < 10;

                    // Biaya utama (ambil pertama jika ada)
                    $biayaUtama = $biayaList->first();
                    $biayaNominal = $biayaUtama?->nominal ?? 0;

                    // Warna kartu
                    $color = $cardColors[$idx % count($cardColors)];
                    $cardId = 'jalur-' . $jalur->id;

                    // State tombol
                    $canSelect = $bisaDaftar && $profilCukup;
                    $isDisabled = !$bisaDaftar || !$profilCukup;
                @endphp

                <article class="jalur-card {{ $isDisabled && !$sudahDaftar ? 'jalur-card--disabled' : '' }}" id="{{ $cardId }}"
                    aria-labelledby="{{ $cardId }}-title">

                    {{-- Card Header --}}
                    <div class="jalur-card-header" style="background: {{ $color['bg'] }};">
                        <div class="jalur-header-content">
                            <div class="jalur-kode-badge">{{ $jalur->kode_jalur }}</div>
                            <h3 class="jalur-nama" id="{{ $cardId }}-title">{{ $jalur->nama }}</h3>
                            <div class="jalur-tahun">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $tahun?->nama ?? $pembukaan?->nama }}
                            </div>
                        </div>
                        <div class="jalur-status-badge-wrapper">
                            @if($sudahDaftar)
                                <span class="jalur-status-chip chip-sudah">
                                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                    Sudah Daftar
                                </span>
                            @elseif($kuotaTersedia <= 0)
                                <span class="jalur-status-chip chip-penuh">
                                    <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                                    Kuota Penuh
                                </span>
                            @elseif(!$jadwalAktif)
                                <span class="jalur-status-chip chip-tutup">
                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                    Di Luar Jadwal
                                </span>
                            @else
                                <span class="jalur-status-chip chip-tersedia">
                                    <i class="bi bi-circle-fill" aria-hidden="true" style="font-size:0.5rem;"></i>
                                    Tersedia
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="jalur-card-body">

                        {{-- Deskripsi --}}
                        @if($jalur->deskripsi)
                            <p class="jalur-desc">{{ Str::limit($jalur->deskripsi, 120) }}</p>
                        @endif

                        {{-- Info Grid: Kuota | Sisa | Biaya --}}
                        <div class="jalur-info-grid">
                            <div class="jalur-info-item">
                                <div class="jalur-info-label">Total Kuota</div>
                                <div class="jalur-info-val">{{ number_format($totalKuota) }} siswa</div>
                            </div>
                            <div class="jalur-info-item">
                                <div class="jalur-info-label">Sisa Kuota</div>
                                <div
                                    class="jalur-info-val {{ $kuotaTersedia <= 0 ? 'text-danger' : ($hampirPenuh ? 'text-warning' : 'text-success') }}">
                                    {{ number_format($kuotaTersedia) }} siswa
                                    @if($hampirPenuh && $kuotaTersedia > 0)
                                        <div class="hampir-penuh-text">
                                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                            Hampir penuh!
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="jalur-info-item">
                                <div class="jalur-info-label">Biaya</div>
                                <div class="jalur-info-val text-primary">
                                    @if($biayaNominal > 0)
                                        {{ $fmtRupiah($biayaNominal) }}
                                    @else
                                        <span class="text-success">Gratis</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar Kuota Terisi --}}
                        <div class="kuota-progress-wrapper">
                            <div class="kuota-progress-label">
                                <span class="text-muted" style="font-size:0.75rem;">Kuota Terisi</span>
                                <span class="text-muted fw-bold" style="font-size:0.75rem;">{{ $pctTerisi }}%</span>
                            </div>
                            <div class="kuota-progress-track">
                                <div class="kuota-progress-fill"
                                    style="width: {{ $pctTerisi }}%; background: linear-gradient(90deg, {{ $color['bg'] }}, {{ $color['border'] }});"
                                    role="progressbar" aria-valuenow="{{ $pctTerisi }}" aria-valuemin="0" aria-valuemax="100"
                                    aria-label="Kuota terisi {{ $pctTerisi }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Accordion: Detail tambahan (Kuota Jurusan, Syarat, Jadwal, Biaya) --}}
                        <div class="accordion jalur-accordion" id="acc-{{ $cardId }}">

                            {{-- Kuota Jurusan --}}
                            @if($kuotaJurusan->isNotEmpty())
                                <div class="accordion-item jalur-acc-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed jalur-acc-btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#kuota-{{ $cardId }}" aria-expanded="false"
                                            aria-controls="kuota-{{ $cardId }}">
                                            <i class="bi bi-diagram-3 me-2" aria-hidden="true"></i>
                                            Kuota Per Jurusan
                                        </button>
                                    </h4>
                                    <div id="kuota-{{ $cardId }}" class="accordion-collapse collapse"
                                        data-bs-parent="#acc-{{ $cardId }}">
                                        <div class="accordion-body jalur-acc-body">
                                            <div class="kuota-jurusan-list">
                                                @foreach($kuotaJurusan as $kj)
                                                    <div class="kuota-jurusan-item">
                                                        <div class="kj-nama">{{ $kj->jurusan?->nama ?? '—' }}</div>
                                                        <div class="kj-sisa">
                                                            Sisa {{ number_format($kj->kuota - $kj->terisi) }} /
                                                            {{ number_format($kj->kuota) }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Syarat --}}
                            @if($syaratList->isNotEmpty())
                                <div class="accordion-item jalur-acc-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed jalur-acc-btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#syarat-{{ $cardId }}" aria-expanded="false"
                                            aria-controls="syarat-{{ $cardId }}">
                                            <i class="bi bi-file-earmark-check me-2" aria-hidden="true"></i>
                                            Syarat Pendaftaran ({{ $syaratList->count() }})
                                        </button>
                                    </h4>
                                    <div id="syarat-{{ $cardId }}" class="accordion-collapse collapse"
                                        data-bs-parent="#acc-{{ $cardId }}">
                                        <div class="accordion-body jalur-acc-body">
                                            <ul class="syarat-list">
                                                @foreach($syaratList as $srt)
                                                    <li class="syarat-item">
                                                        <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                                                        <span>{{ $srt->nama }}</span>
                                                        @if($srt->wajib)
                                                            <span class="badge bg-danger-subtle text-danger ms-auto">Wajib</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary ms-auto">Opsional</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Jadwal --}}
                            @if($jadwalList->isNotEmpty())
                                <div class="accordion-item jalur-acc-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed jalur-acc-btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#jadwal-{{ $cardId }}" aria-expanded="false"
                                            aria-controls="jadwal-{{ $cardId }}">
                                            <i class="bi bi-calendar-range me-2" aria-hidden="true"></i>
                                            Jadwal Pelaksanaan
                                        </button>
                                    </h4>
                                    <div id="jadwal-{{ $cardId }}" class="accordion-collapse collapse"
                                        data-bs-parent="#acc-{{ $cardId }}">
                                        <div class="accordion-body jalur-acc-body">
                                            <div class="jadwal-list">
                                                @foreach($jadwalList as $jdw)
                                                    @php
                                                        $isAktif = $jadwalAktif && $jadwalAktif->id == $jdw->id;
                                                    @endphp
                                                    <div class="jadwal-item {{ $isAktif ? 'jadwal-item--aktif' : '' }}">
                                                        <span class="jadwal-tipe-badge">{{ ucfirst($jdw->tipe) }}</span>
                                                        <span class="jadwal-tanggal">
                                                            {{ $fmtDate($jdw->mulai) }} — {{ $fmtDate($jdw->selesai) }}
                                                        </span>
                                                        @if($isAktif)
                                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Biaya --}}
                            @if($biayaList->isNotEmpty())
                                <div class="accordion-item jalur-acc-item">
                                    <h4 class="accordion-header">
                                        <button class="accordion-button collapsed jalur-acc-btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#biaya-{{ $cardId }}" aria-expanded="false"
                                            aria-controls="biaya-{{ $cardId }}">
                                            <i class="bi bi-cash-stack me-2" aria-hidden="true"></i>
                                            Rincian Biaya
                                        </button>
                                    </h4>
                                    <div id="biaya-{{ $cardId }}" class="accordion-collapse collapse"
                                        data-bs-parent="#acc-{{ $cardId }}">
                                        <div class="accordion-body jalur-acc-body">
                                            <ul class="syarat-list">
                                                @foreach($biayaList as $by)
                                                    <li class="syarat-item">
                                                        <i class="bi bi-currency-dollar text-primary" aria-hidden="true"></i>
                                                        <span>{{ $by->nama }}</span>
                                                        <span class="ms-auto fw-bold text-primary">{{ $fmtRupiah($by->nominal) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="jalur-card-footer">
                        @if($sudahDaftar)
                            <a href="{{ route('ppdb.pendaftaran.show', $pendAktif?->id) }}"
                                class="btn btn-outline-secondary btn-jalur-action">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                Lihat Pendaftaran
                            </a>
                        @elseif($canSelect)
                            <button type="button" class="btn btn-success btn-jalur-action" onclick="bukaModalKonfirmasi(this)"
                                data-jalur-id="{{ $jalur->id }}" data-jalur-nama="{{ $jalur->nama }}"
                                data-biaya="{{ $biayaNominal > 0 ? $fmtRupiah($biayaNominal) : 'Gratis' }}"
                                data-syarat-count="{{ $syaratList->count() }}">
                                <i class="bi bi-check-circle" aria-hidden="true"></i>
                                Pilih Jalur Ini
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-secondary btn-jalur-action" disabled>
                                @if(!$profilCukup)
                                    <i class="bi bi-lock" aria-hidden="true"></i>
                                    Lengkapi Profil Dulu
                                @elseif($kuotaTersedia <= 0)
                                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    Kuota Penuh
                                @else
                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                    Di Luar Jadwal
                                @endif
                            </button>
                        @endif
                    </div>

                </article>
            @endforeach
        </div>
    @endif

    {{-- MODAL KONFIRMASI PILIHAN JALUR Modal untuk konfirmasi sebelum submit pilihan jalur --}}
    <div class="modal fade" id="modalKonfirmasiJalur" tabindex="-1" aria-labelledby="modalKonfirmasiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-konfirmasi">

                {{-- Modal Header --}}
                <div class="modal-konfirmasi-header">
                    <div class="modal-konfirmasi-icon" aria-hidden="true">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalKonfirmasiLabel">Konfirmasi Pilihan Jalur</h5>
                        <p class="text-muted mb-0" style="font-size:0.8125rem;">
                            Pastikan pilihan Anda sudah sesuai
                        </p>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body">

                    {{-- Info Jalur --}}
                    <div class="modal-jalur-info">
                        <div class="modal-info-row">
                            <span class="modal-info-label">Jalur</span>
                            <span class="modal-info-val" id="modal-jalur-nama">—</span>
                        </div>
                        <div class="modal-info-row">
                            <span class="modal-info-label">Biaya</span>
                            <span class="modal-info-val" id="modal-biaya">—</span>
                        </div>
                        <div class="modal-info-row">
                            <span class="modal-info-label">Syarat</span>
                            <span class="modal-info-val" id="modal-syarat-count">—</span>
                        </div>
                    </div>

                    {{-- Warning Box --}}
                    <div class="modal-warning-box">
                        <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                        <div>
                            Setelah memilih jalur ini, Anda akan diarahkan untuk melengkapi
                            formulir pendaftaran. Pastikan data yang Anda isi sudah benar.
                        </div>
                    </div>

                    {{-- Checkbox Konfirmasi --}}
                    <div class="modal-checkbox-wrapper">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkKonfirmasi"
                                onchange="toggleSubmitBtn(this)">
                            <label class="form-check-label" for="checkKonfirmasi">
                                Saya sudah yakin dengan pilihan jalur ini dan siap melanjutkan
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-konfirmasi-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <form action="{{ route('ppdb.pendaftaran.store') }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="jalur_pendaftaran_id" id="hidden-jalur-id" value="">
                        <button type="submit" class="btn btn-success" id="btnSubmitKonfirmasi" disabled>
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            Ya, Lanjutkan
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /**
         * Buka Modal Konfirmasi — isi data dari atribut tombol yang diklik
         */
        function bukaModalKonfirmasi(btn) {
            const jalurId = btn.dataset.jalurId;
            const jalurNama = btn.dataset.jalurNama;
            const biaya = btn.dataset.biaya;
            const syaratCount = btn.dataset.syaratCount;

            // Isi data modal
            document.getElementById('modal-jalur-nama').textContent = jalurNama;
            document.getElementById('modal-biaya').textContent = biaya;
            document.getElementById('modal-syarat-count').textContent =
                syaratCount + ' dokumen wajib';

            // Set hidden input
            document.getElementById('hidden-jalur-id').value = jalurId;

            // Reset checkbox & tombol submit
            const checkbox = document.getElementById('checkKonfirmasi');
            checkbox.checked = false;
            document.getElementById('btnSubmitKonfirmasi').disabled = true;

            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiJalur'));
            modal.show();
        }

        /**
         * Toggle tombol submit berdasarkan state checkbox konfirmasi
         */
        function toggleSubmitBtn(checkbox) {
            document.getElementById('btnSubmitKonfirmasi').disabled = !checkbox.checked;
        }

        /**
         * Animate kuota progress bars saat halaman load
         */
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.kuota-progress-fill').forEach(function (bar) {
                const w = bar.style.width;
                bar.style.width = '0%';
                setTimeout(function () { bar.style.width = w; }, 300);
            });

            // Initialize Bootstrap tooltips if any
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
        });
    </script>
@endpush