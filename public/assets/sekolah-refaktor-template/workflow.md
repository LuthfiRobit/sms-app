# Workflow Refaktor Interface: Sekolah Refactor -> Organisasi Template (Architect Version)

Dokumen ini adalah **Instruksi Teknis Mutlak** bagi AI Executor. Tujuannya adalah migrasi UI parsial dari file legacy (root folder) ke struktur `organisasi-template` tanpa merusak logika bisnis.

## 1. Prinsip Utama (Non-Negotiable)
1.  **Skeleton Template**: Seluruh file harus menggunakan struktur HTML `organisasi-template` (Header, Sidebar, Footer).
2.  **Logic Preservation**: DILARANG MENGHAPUS script logika bisnis (AJAX, Event Listeners).
3.  **Library Compatibility**:
    *   **WAJIB** Pertahankan: `jQuery` (Versi Lama/CDN), `DataTables` (CSS/JS), `SweetAlert`, `Toast`.
    *   **WAJIB** Hapus: `Boxicons` (Ganti dengan icon template).
4.  **Asset Source**: Semua styling/script visual WAJIB mengambil dari `organisasi-template/assets/`.

## 2. Mapping Assets (CSS & JS)

Setiap file hasil refaktor **HARUS** memuat resource berikut di `<head>` dan sebelum `</body>`.

### A. Head Section (Urutan Wajib)
1.  **Meta Tags**: Copy dari Template Baru.
2.  **Favicon**: `organisasi-template/assets/images/favicon.svg`
3.  **Fonts (Google)**: `Roboto` logic.
4.  **Template Icons** (Gunakan path relatif):
    ```html
    <link rel="stylesheet" href="organisasi-template/assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="organisasi-template/assets/fonts/feather.css" />
    <link rel="stylesheet" href="organisasi-template/assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="organisasi-template/assets/fonts/material.css" />
    ```
5.  **Template Styles**:
    ```html
    <link rel="stylesheet" href="organisasi-template/assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="organisasi-template/assets/css/style-preset.css" />
    <link rel="stylesheet" href="organisasi-template/assets/css/custom-style.css" />
    ```
6.  **Legacy Libraries (Inject Disini)**:
    *   DataTables CSS: `<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />`
    *   *Jangan load Boxicons.*

### B. Body Scripts (Urutan Wajib)
Letakkan tepat sebelum `</body>`:

1.  **Legacy JQUERY (Priortas Tertinggi)**:
    *   Wajib load jQuery **sebelum** script template jika script lama membutuhkannya.
    *   `<script src="https://code.jquery.com/jquery-3.5.1.js"></script>`
2.  **Template Plugins**:
    ```html
    <script src="organisasi-template/assets/js/plugins/popper.min.js"></script>
    <script src="organisasi-template/assets/js/plugins/simplebar.min.js"></script>
    <script src="organisasi-template/assets/js/plugins/bootstrap.min.js"></script>
    <script src="organisasi-template/assets/js/fonts/custom-font.js"></script>
    <script src="organisasi-template/assets/js/script.js"></script>
    <script src="organisasi-template/assets/js/theme.js"></script>
    <script src="organisasi-template/assets/js/plugins/feather.min.js"></script>
    ```
3.  **Legacy Libraries**:
    *   DataTables JS: `jquery.dataTables.min.js`, `dataTables.bootstrap5.min.js`.
    *   SweetAlert / Toast (Jika ada di file asli).
4.  **Legacy Page Script**:
    *   Block `<script>$(document).ready(...)</script>` dari file lama.

## 3. DOM Structure Migration

Struktur HTML harus diubah total mengikuti hierarki ini:

```html
<body>
    <!-- 1. Loader (Optional) -->
    <div class="loader-bg">...</div>

    <!-- 2. Sidebar (Navigasi) -->
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
             <div class="m-header">
                 <!-- LOGO -->
             </div>
             <div class="navbar-content">
                 <!-- MENU ITEMS (Lihat Section 6) -->
                 <ul class="pc-navbar">...</ul>
             </div>
        </div>
    </nav>

    <!-- 3. Header (Topbar) -->
    <header class="pc-header">
         <!-- Gunakan Header template standart (profil, notif) -->
    </header>

    <!-- 4. Main Content Wrapper -->
    <div class="pc-container">
        <div class="pc-content">
            
            <!-- ====== INJECT KONTEN LAMA DISINI ====== -->
            <!-- Tips: Bungkus konten lama dengan div.row jika perlu -->
            <!-- Pastikan class container-xxl lama dihapus, ganti wrapper ini -->
            
            <div class="page-header">...</div> <!-- Jika ada -->
            <div class="row">
                 <!-- Copy Paste Tables/Cards/Forms dari Legacy File -->
            </div>
            
            <!-- ======================================= -->

        </div>
    </div>

    <!-- 5. Footer -->
    <footer class="pc-footer">...</footer>

    <!-- Scripts -->
</body>
```

## 4. Consistency Guide & Menu Migration

### Mapping Icon
Karena Boxicons dihapus, ganti class icon pada Menu Sidebar dan Konten:
*   `bx bx-home` -> `ti ti-dashboard` / `ph-duotone ph-house`
*   `bx bx-user` -> `ti ti-user`
*   `bx bx-book` -> `ti ti-book`
*   `bx bx-cog` -> `ti ti-settings`
*   `bx bx-log-out` -> `ti ti-logout`

### Struktur Menu Sidebar Legacy (Referensi Lama)
*Gunakan Section 6 untuk struktur menu baru.*

## 5. Instruksi Eksekusi

Jalankan refactor file demi file dengan urutan:
1.  Baca file HTML target.
2.  Buat file baru di memori dengan skeleton `organisasi-template`.
3.  Salin Asset Links & Scripts (Section 2).
4.  Bangun Sidebar sesuai menu baru (Section 6).
5.  Inject Konten Utama ke `.pc-content` (Section 3).
6.  *Sanity Check*: Pastikan tidak ada `bx-` class yang tertinggal dan jQuery terload pertama.
7.  Simpan file (Overwrite).

## 6. Struktur Menu Baru (SIMS Terpadu)
*Berdasarkan ANALISA MENU-SIMS TERPADU.pdf (WAJIB SAMA)*

Gunakan struktur sidebar berikut untuk semua halaman. Set `active` dan `open` pada item yang sesuai dengan halaman saat ini.

1.  **Dashboard**
    *   Akademik (`index.html`) `<i class="bi bi-speedometer2"></i>`
    *   Keuangan (`dashboard-keuangan.html`) `<i class="bi bi-pie-chart-fill"></i>`

2.  **Data Master** (Header)
    *   **Master Identitas & Wilayah** (Submenu) `<i class="bi bi-building"></i>`
        *   Profil Sekolah (`#`)
        *   Tahun Ajaran & Semester (`#`)
        *   Kurikulum (`#`)
        *   Referensi Wilayah (`#`)
    *   **Master Org. & Akademik** (Submenu) `<i class="bi bi-mortarboard"></i>`
        *   Jurusan / Peminatan / Konsentrasi (`#`)
        *   Tingkat Kelas (`#`)
        *   Rombel (Kelas) (`master-kelas.html`)
        *   Mata Pelajaran (`master-mapel.html`)
        *   Mapping Jurusan <-> Mapel (`#`)
    *   **Master Sarana & Jadwal** (Submenu) `<i class="bi bi-calendar3"></i>`
        *   Gedung (`#`)
        *   Ruang Kelas / Lab / Bengkel (`#`)
        *   Slot Jam Pelajaran (`master-jam.html`)
        *   Kalender Akademik (`#`)
    *   **Master Kedisiplinan & Pres.** (Submenu) `<i class="bi bi-shield-shaded"></i>`
        *   Jenis Pelanggaran & Bobot Poin (`#`)
        *   Jenis Prestasi & Bobot Poin (`#`)
        *   Tindakan / Sanksi (`#`)
    *   **Master Keuangan** (Submenu) `<i class="bi bi-wallet2"></i>`
        *   Komponen Biaya (`#`)
        *   Kategori Biaya (`#`)
        *   Metode Pembayaran (`#`)
        *   Diskon & Beasiswa (`#`)

3.  **Manajemen Pengguna** (Header)
    *   Data GTK (`master-guru.html`) `<i class="bi bi-people-fill"></i>`
    *   Data Siswa (Induk) (`master-siswa.html`) `<i class="bi bi-person-badge-fill"></i>`
    *   Status Siswa (Mutasi/Lulus) (`#`) `<i class="bi bi-person-check-fill"></i>`
    *   Hak Akses & Role (`#`) `<i class="bi bi-shield-lock-fill"></i>`

4.  **Operasional** (Header)
    *   **PPDB** (Submenu) `<i class="bi bi-person-plus-fill"></i>`
        *   Pengaturan PPDB (`#`)
        *   Data Pendaftar (`ppdb-rekrutmen.html`)
        *   Verifikasi & Seleksi (`#`)
        *   Daftar Ulang (`#`)
    *   **Akademik** (Submenu) `<i class="bi bi-journal-text"></i>`
        *   Penjadwalan KBM (`akademik-jadwal.html`)
        *   Presensi Siswa (`absensi-siswa.html`)
        *   Presensi & Jurnal Guru (`jurnal-mengajar.html`)
        *   Nilai (Formatif & Sumatif) (`#`)
        *   E-Rapor (`akademik-rapor.html`)
    *   **Keuangan** (Submenu) `<i class="bi bi-bank"></i>`
        *   Tagihan Siswa (`#`)
        *   Transaksi Pembayaran (`keuangan-pembayaran.html`)
        *   Tunggakan (`#`)
        *   Laporan Keuangan & Arus Kas (`#`)
        *   Rekap Bulanan / Tahunan (`#`)
    *   **Kesiswaan** (Submenu) `<i class="bi bi-emoji-smile-fill"></i>`
        *   Profil & Riwayat Siswa (`#`)
        *   Kedisiplinan & SIP (`#`)
        *   Prestasi & Organisasi (`#`)
        *   Bimbingan Konseling (BK) (`#`)
    *   **Sarana Prasarana** (Submenu) `<i class="bi bi-box-seam-fill"></i>`
        *   Inventaris Barang (`sarpras-aset.html`)
        *   Inventaris Ruangan (`#`)
        *   Peminjaman & Pengembalian (`#`)
    *   **Kelulusan & Alumni** (Submenu) `<i class="bi bi-mortarboard-fill"></i>`
        *   Validasi Kelulusan (`akademik-kelulusan.html`)
        *   Status Lulus & Tracer Study (`#`)
        *   Database Alumni (`kesiswaan-alumni.html`)

5.  **Laporan & Pengaturan** (Header)
    *   **Laporan & Audit** (Submenu) `<i class="bi bi-file-earmark-bar-graph"></i>`
        *   Laporan Akademik (`#`)
        *   Laporan Kesiswaan (`#`)
        *   Laporan PPDB (`#`)
        *   Laporan Akreditasi / Dapodik (`#`)
        *   Log Aktivitas (Audit Trail) (`#`)
    *   **Pengaturan** (Submenu) `<i class="bi bi-gear-wide-connected"></i>`
        *   Konfigurasi Sistem (`pengaturan.html`)
        *   Advanced RBAC (`#`)
        *   Database Maintenance (`#`)

