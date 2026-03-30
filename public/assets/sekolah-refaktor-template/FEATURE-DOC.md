# FEATURE DOCUMENTATION - SIMS TERPADU
## Sistem Informasi Manajemen Sekolah Terpadu

**Versi**: 1.0  
**Tanggal**: 27 Januari 2026  
**Status**: Development

---

## 📋 Daftar Isi

1. [Dashboard](#1-dashboard)
2. [Data Master](#2-data-master)
   - [A. Master Identitas & Wilayah](#a-master-identitas--wilayah)
   - [B. Master Organisasi & Akademik](#b-master-organisasi--akademik)
   - [C. Master Sarana & Jadwal](#c-master-sarana--jadwal)
3. [Akademik](#3-akademik)
4. [Keuangan](#4-keuangan)
5. [Kepegawaian](#5-kepegawaian)
6. [Kesiswaan](#6-kesiswaan)

---

## 1. Dashboard

### 📊 Dashboard Akademik
**File**: `index.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Dashboard utama yang menampilkan ringkasan data akademik sekolah.

**Fitur**:
- Ringkasan statistik akademik
- Grafik dan visualisasi data
- Quick access ke menu utama

---

### 💰 Dashboard Keuangan
**File**: `dashboard-keuangan.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Dashboard khusus untuk monitoring keuangan sekolah.

**Fitur**:
- Ringkasan keuangan
- Grafik pemasukan dan pengeluaran
- Status pembayaran

---

## 2. Data Master

### A. Master Identitas & Wilayah

#### 🏫 Profil Sekolah
**File**: `identitas-sekolah.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola informasi profil dan identitas sekolah.

**Fitur**:
- Form data sekolah (nama, NPSN, alamat, dll)
- Upload logo sekolah
- Informasi kontak dan akreditasi
- Data kepala sekolah

---

#### 📅 Tahun Ajaran & Semester
**File**: `identitas-tahun-ajaran.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data tahun ajaran dan semester aktif.

**Fitur**:
- **Summary Cards**: Total Periode, Periode Aktif, Tahun Ajaran, Semester
- **DataTables**: Daftar periode tahun ajaran dengan informasi lengkap
- **CRUD Operations**: Tambah, Edit, Hapus periode
- **Modal Form**: Form input tahun ajaran, semester, tanggal mulai/selesai, status
- **Status Management**: Aktif/Nonaktif periode

**Data yang Dikelola**:
- Tahun ajaran (misal: 2024/2025)
- Semester (Ganjil/Genap)
- Tanggal mulai dan selesai
- Status aktif/nonaktif
- Keterangan

---

#### 📚 Kurikulum
**File**: `identitas-kurikulum.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data kurikulum yang digunakan sekolah.

**Fitur**:
- **Summary Cards**: Total Kurikulum, Kurikulum Aktif, Mapel Terkait, Tingkat Terkait
- **DataTables**: Daftar kurikulum dengan detail
- **CRUD Operations**: Tambah, Edit, Hapus kurikulum
- **Modal Form**: Form input nama kurikulum, tahun berlaku, jenjang, status
- **Badge System**: Visual indicator untuk jenis kurikulum

**Data yang Dikelola**:
- Nama kurikulum (K13, Merdeka, KTSP, dll)
- Tahun berlaku
- Jenjang pendidikan
- Status implementasi
- Keterangan

---

#### 🗺️ Referensi Wilayah
**File**: `identitas-wilayah.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data referensi wilayah (Provinsi, Kota/Kabupaten, Kecamatan, Kelurahan).

**Fitur**:
- **Summary Cards**: Total Provinsi, Kota/Kab, Kecamatan, Kelurahan
- **Hierarchical DataTables**: Tabel dengan struktur hierarki wilayah
- **Search & Filter**: Pencarian berdasarkan nama wilayah
- **Badge System**: Kode wilayah dengan badge berwarna
- **Responsive Design**: Tampilan optimal di berbagai perangkat

**Data yang Dikelola**:
- Provinsi
- Kota/Kabupaten
- Kecamatan
- Kelurahan/Desa
- Kode wilayah

---

### B. Master Organisasi & Akademik

#### 🎓 Jurusan / Peminatan
**File**: `master-jurusan.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data jurusan atau peminatan yang tersedia di sekolah (IPA, IPS, Bahasa, Agama, dll).

**Fitur**:
- **Summary Cards**: 
  - Total Jurusan (8)
  - Jurusan Aktif (7)
  - Total Siswa (856)
  - Total Rombel (24)
- **Filter Row**: Filter berdasarkan Jenis Jurusan dan Status
- **DataTables**: Tabel interaktif dengan kolom:
  - Checkbox untuk bulk action
  - Aksi (Edit, Hapus)
  - Kode jurusan
  - Nama jurusan
  - Jenis (badge berwarna)
  - Jumlah rombel
  - Jumlah siswa
  - Status (Aktif/Nonaktif)
- **CRUD Operations**: Tambah, Edit, Hapus jurusan
- **Modal Form**: Form dengan field:
  - Kode jurusan
  - Nama jurusan
  - Jenis jurusan (dropdown)
  - Status (dropdown)
  - Keterangan (textarea)
- **Choices.js Integration**: Dropdown yang lebih interaktif
- **SweetAlert2**: Konfirmasi dan notifikasi yang menarik

**Sample Data**:
- IPA (Ilmu Pengetahuan Alam) - 3 rombel, 96 siswa
- IPS (Ilmu Pengetahuan Sosial) - 3 rombel, 90 siswa
- Bahasa (Bahasa dan Budaya) - 2 rombel, 60 siswa
- Agama (Keagamaan) - 2 rombel, 58 siswa
- Dan lainnya

**Relasi**:
- Terkait dengan: `master-jenis-jurusan.html`, `master-kelas.html`, `master-mapping-jurusan.html`

---

#### 📋 Jenis Jurusan
**File**: `master-jenis-jurusan.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman supporting master untuk mengelola jenis/kategori jurusan (Umum, Kejuruan, Keagamaan, dll).

**Fitur**:
- **Summary Cards**:
  - Total Jenis (5)
  - Jenis Aktif (5)
  - Total Jurusan (8)
  - Kelengkapan (100%)
- **DataTables**: Tabel dengan kolom:
  - Checkbox
  - Aksi (Edit, Hapus)
  - Kode jenis
  - Nama jenis
  - Jumlah jurusan
  - Status
- **CRUD Operations**: Tambah, Edit, Hapus jenis jurusan
- **Modal Form**: Form input kode, nama, status, keterangan
- **Badge Colors**: Warna berbeda untuk setiap jenis jurusan
- **Auto-counting**: Otomatis menghitung jumlah jurusan per jenis

**Sample Data**:
- Umum (IPA, IPS, Bahasa) - 3 jurusan
- Kejuruan (TKJ, RPL, MM) - 3 jurusan
- Keagamaan (Agama) - 1 jurusan
- Olahraga (Olahraga) - 1 jurusan

**Relasi**:
- Parent untuk: `master-jurusan.html`

---

#### 📊 Tingkat Kelas
**File**: `master-tingkat.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data tingkat kelas (VII, VIII, IX untuk SMP atau X, XI, XII, XIII untuk SMA/SMK).

**Fitur**:
- **Summary Cards**:
  - Total Tingkat (7)
  - Tingkat Aktif (6)
  - Total Rombel (24)
  - Total Siswa (720)
- **DataTables**: Tabel dengan kolom:
  - Checkbox
  - Aksi (Edit, Hapus)
  - Kode tingkat
  - Nama tingkat
  - Jenjang (SMP/SMA/SMK)
  - Urutan
  - Jumlah rombel
  - Jumlah siswa
  - Status
- **CRUD Operations**: Tambah, Edit, Hapus tingkat
- **Modal Form**: Form dengan field:
  - Kode tingkat
  - Nama tingkat
  - Jenjang (dropdown: SMP, SMA, SMK)
  - Urutan (number input untuk sorting)
  - Status (dropdown)
  - Keterangan (textarea)
- **Ordering System**: Urutan tingkat dapat diatur
- **Multi-jenjang Support**: Mendukung SMP, SMA, dan SMK

**Sample Data**:
- VII (SMP) - Urutan 1 - 4 rombel, 120 siswa
- VIII (SMP) - Urutan 2 - 4 rombel, 120 siswa
- IX (SMP) - Urutan 3 - 4 rombel, 120 siswa
- X (SMA) - Urutan 4 - 4 rombel, 120 siswa
- XI (SMA) - Urutan 5 - 4 rombel, 120 siswa
- XII (SMA) - Urutan 6 - 4 rombel, 120 siswa

**Relasi**:
- Terkait dengan: `master-kelas.html`, `master-mapping-jurusan.html`

---

#### 🏛️ Rombel (Kelas)
**File**: `master-kelas.html`  
**Status**: ✅ Implemented (Updated)

**Deskripsi**:
Halaman untuk mengelola data rombongan belajar (kelas) dengan informasi wali kelas dan kapasitas siswa.

**Fitur**:
- **Summary Cards**: Total Kelas, Kelas Aktif, Total Siswa, Rata-rata per Kelas
- **Filter Options**: Filter berdasarkan tingkat, jurusan, status
- **DataTables**: Tabel dengan kolom:
  - Checkbox
  - Aksi (Lihat Detail, Edit, Hapus)
  - Tingkat (badge)
  - Nama kelas
  - Wali kelas (dengan avatar dan NIP)
  - Kapasitas siswa
  - Status
- **CRUD Operations**: Tambah, Edit, Hapus, Lihat Detail kelas
- **Modal Form**: Form input tingkat, nama kelas, wali kelas, kapasitas
- **Teacher Display**: Avatar dengan nama dan NIP wali kelas
- **Responsive Layout**: Optimal di berbagai ukuran layar

**Sample Data**:
- VII-A (Wali: Budi Santoso, S.Pd - NIP: 1001) - 32 siswa
- VII-B (Wali: Siti Aminah, M.Pd - NIP: 1002) - 30 siswa
- Dan seterusnya

**Relasi**:
- Terkait dengan: `master-tingkat.html`, `master-jurusan.html`, `master-guru.html`

---

#### 📖 Mata Pelajaran
**File**: `master-mapel.html`  
**Status**: ✅ Implemented (Updated)

**Deskripsi**:
Halaman untuk mengelola data mata pelajaran dengan informasi kelompok mapel, JJM (Jumlah Jam Mengajar), dan guru pengampu.

**Fitur**:
- **Summary Cards**: Total Mapel (18), Kelompok A (8), Kelompok B (6), Kelompok C (4)
- **Filter Options**: Filter berdasarkan kelompok mapel dan status
- **DataTables**: Tabel dengan kolom:
  - Checkbox
  - Aksi (Edit, Atur Guru, Hapus)
  - Kode mapel
  - Nama mata pelajaran
  - Kelompok (badge: Wajib A, Kewilayahan B, Mulok)
  - JJM (badge dengan jumlah jam pelajaran)
  - Guru pengampu (avatar dengan nama dan NIP)
  - Status
- **CRUD Operations**: Tambah, Edit, Hapus mata pelajaran
- **Modal Form**: Form dengan field:
  - Kode mapel
  - Nama mata pelajaran
  - Kelompok mapel (dropdown)
  - JJM (number input)
  - Guru pengampu (multi-select dengan Choices.js)
  - Status
  - Keterangan
- **Teacher Display Improvement**: 
  - Avatar size: `avtar-s` (lebih besar)
  - Nama guru: Bold, line pertama
  - NIP: Text kecil muted, line kedua
- **DataTables Layout Fix**: Layout proporsional dengan:
  - Dropdown "Tampilkan X entri" di kiri atas
  - Search box di kanan atas
  - Info text di kiri bawah
  - Pagination di kanan bawah
- **Multi-select Teachers**: Satu mapel bisa diampu beberapa guru

**Sample Data**:
- MTK - Matematika (Wajib A, 5 JP) - Budi Santoso, S.Pd (NIP: 1001)
- IND - Bahasa Indonesia (Wajib A, 4 JP) - Siti Aminah, M.Pd (NIP: 1002)
- ENG - Bahasa Inggris (Wajib A, 4 JP) - Rina Handayani, S.Pd (NIP: 1003)
- IPA - Ilmu Pengetahuan Alam (Wajib A, 5 JP) - Dwi Wahyuni, M.Si (NIP: 1004)
- PJOK - Pendidikan Jasmani (Kewilayahan B, 3 JP) - Agus Hermawan, S.Pd (NIP: 1005)
- BJW - Bahasa Jawa (Mulok, 2 JP) - Suparno, S.Pd (NIP: 1006)

**Recent Updates**:
1. **Teacher Column Enhancement**: Improved display format matching `master-kelas.html` standard
2. **DataTables Layout Fix**: Removed custom `dom` configuration for proportional layout

**Relasi**:
- Terkait dengan: `master-guru.html`, `master-mapping-jurusan.html`

---

#### 🔗 Mapping Jurusan ↔ Mapel
**File**: `master-mapping-jurusan.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola pemetaan antara jurusan/peminatan dengan mata pelajaran yang relevan. Menentukan mapel apa saja yang diajarkan di jurusan tertentu.

**Fitur**:
- **Summary Cards**:
  - Total Jurusan (8)
  - Total Mapel (45)
  - Total Mapping (156)
  - Kelengkapan (92%)
- **Filter Row**: Filter berdasarkan Jenis Jurusan dan Kelompok Mapel
- **DataTables**: Tabel dengan kolom:
  - Checkbox
  - Aksi (Edit, Hapus)
  - Jurusan/Peminatan
  - Jenis jurusan (badge)
  - Mata pelajaran
  - Kelompok mapel (badge)
  - Tingkat kelas
  - Status
- **CRUD Operations**: Tambah, Edit, Hapus mapping
- **Modal Form**: Form dengan field:
  - Jurusan/Peminatan (dropdown)
  - Mata Pelajaran (multi-select dengan Choices.js, grouped by Kelompok A, B, C)
  - Kelompok Mapel (dropdown)
  - Status (dropdown)
  - Tingkat Kelas (multi-select: X, XI, XII, XIII)
  - Keterangan (textarea)
- **Choices.js Multi-select**: 
  - Mata pelajaran dengan grouping
  - Tingkat kelas multiple selection
  - Search enabled untuk kemudahan
- **Grouped Options**: Mapel dikelompokkan berdasarkan kategori
- **Badge System**: Visual indicator untuk jenis dan kelompok

**Sample Data**:
- IPA → Matematika Peminatan, Fisika, Kimia, Biologi (Kelompok C) - Tingkat X, XI, XII
- IPS → Geografi, Sosiologi, Ekonomi (Kelompok C) - Tingkat X, XI, XII
- Bahasa → Bahasa dan Sastra Indonesia, Bahasa dan Sastra Inggris (Kelompok C) - Tingkat X, XI, XII

**Keunggulan**:
- Multi-select untuk mapel dan tingkat
- Grouping mapel untuk kemudahan pemilihan
- Visual yang informatif dengan badge
- Filter untuk pencarian cepat

**Relasi**:
- Menghubungkan: `master-jurusan.html` ↔ `master-mapel.html`
- Terkait dengan: `master-tingkat.html`

---

### C. Master Sarana & Jadwal

#### 🏢 Sarana & Jadwal
**File**: `[Belum dibuat]`  
**Status**: ⏳ Pending

**Deskripsi**:
Halaman untuk mengelola data sarana prasarana dan jadwal penggunaan.

**Fitur yang Direncanakan**:
- Data ruangan
- Data fasilitas
- Jadwal penggunaan
- Maintenance tracking

---

## 3. Akademik

### 📅 Jadwal Pelajaran
**File**: `akademik-jadwal.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola jadwal pelajaran.

---

### 📝 Jurnal Mengajar
**File**: `jurnal-mengajar.html`, `jurnal-mengajar-isi.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mencatat jurnal kegiatan mengajar guru.

---

### 📊 Absensi Siswa
**File**: `absensi-siswa.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mencatat kehadiran siswa.

---

### 📈 Rapor
**File**: `akademik-rapor.html`  
**Status**: ⏳ Pending

**Deskripsi**:
Halaman untuk mengelola nilai dan rapor siswa.

---

## 4. Keuangan

**Status**: ⏳ Pending

Modul untuk mengelola keuangan sekolah termasuk:
- Pembayaran SPP
- Kas sekolah
- Laporan keuangan

---

## 5. Kepegawaian

### 👨‍🏫 Master Guru
**File**: `master-guru.html`, `master-guru-tambah.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data guru dan staff.

---

## 6. Kesiswaan

### 👨‍🎓 Master Siswa
**File**: `master-siswa.html`, `master-siswa-tambah.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola data siswa.

---

### 📋 PPDB (Rekrutmen)
**File**: `ppdb-rekrutmen.html`  
**Status**: ✅ Implemented

**Deskripsi**:
Halaman untuk mengelola penerimaan peserta didik baru.

---

## 📊 Ringkasan Status Implementasi

### ✅ Completed (Implemented)
- Dashboard (Akademik & Keuangan)
- Master Identitas & Wilayah (4 halaman)
- Master Organisasi & Akademik (6 halaman)
- Akademik (3 halaman)
- Kepegawaian (2 halaman)
- Kesiswaan (3 halaman)

**Total**: 20 halaman

### ⏳ Pending
- Master Sarana & Jadwal
- Akademik Rapor
- Modul Keuangan (lengkap)

**Total**: 3+ modul

---

## 🎨 Standar Desain & Teknologi

### UI Framework
- **Bootstrap 5**: Framework CSS utama
- **Bootstrap Icons**: Icon library
- **Custom CSS**: `custom-style.css` untuk styling tambahan

### JavaScript Libraries
- **jQuery**: DOM manipulation
- **DataTables**: Tabel interaktif dengan sorting, searching, pagination
- **SweetAlert2**: Modal konfirmasi dan notifikasi yang menarik
- **Choices.js**: Enhanced select/multi-select dropdown

### Design Principles
- **Responsive Design**: Optimal di desktop, tablet, dan mobile
- **Consistent Layout**: Semua halaman mengikuti template yang sama
- **User-Friendly**: Interface yang intuitif dan mudah digunakan
- **Informative**: Summary cards untuk overview cepat
- **Professional**: Badge system dan color coding yang konsisten

### DataTables Standard
- **Layout**: Default proportional layout
  - Top Left: "Tampilkan X entri" dropdown
  - Top Right: "Cari:" search box
  - Bottom Left: "Menampilkan X sampai Y dari Z entri" info
  - Bottom Right: Pagination controls
- **Language**: Bahasa Indonesia
- **Features**: Sorting, searching, pagination, responsive

### Modal Forms
- **Validation**: Client-side validation
- **Choices.js**: Enhanced dropdowns
- **SweetAlert2**: Konfirmasi sebelum delete
- **Responsive**: Mobile-friendly forms

---

## 🔗 Relasi Antar Halaman

```
master-jenis-jurusan.html
    ↓ (parent)
master-jurusan.html
    ↓ (relasi)
master-mapping-jurusan.html ← master-mapel.html
    ↓ (relasi)         ↑
master-tingkat.html ───┘
    ↓ (relasi)
master-kelas.html ← master-guru.html
```

---

## 📝 Catatan Pengembangan

### Best Practices
1. **Konsistensi**: Semua halaman master mengikuti pola yang sama
2. **Reusability**: Component yang dapat digunakan kembali
3. **Maintainability**: Kode yang mudah dipelihara dan dikembangkan
4. **Documentation**: Setiap fitur terdokumentasi dengan baik
5. **User Experience**: Fokus pada kemudahan penggunaan

### Naming Convention
- **File HTML**: `kategori-nama.html` (lowercase, hyphen-separated)
- **ID Elements**: `camelCase` (contoh: `tableJurusan`, `modalMapel`)
- **CSS Classes**: `kebab-case` (contoh: `stat-card-sm`, `filter-row`)

### Data Flow
1. **Master Data**: Jenis Jurusan → Jurusan → Mapping → Kelas
2. **Academic Flow**: Tingkat → Kelas → Jadwal → Jurnal → Absensi
3. **Personnel**: Guru → Wali Kelas → Pengampu Mapel

---

## 🚀 Roadmap

### Phase 1: Master Data (✅ Completed)
- ✅ Identitas & Wilayah
- ✅ Organisasi & Akademik

### Phase 2: Academic Module (🔄 In Progress)
- ✅ Jadwal
- ✅ Jurnal
- ✅ Absensi
- ⏳ Rapor

### Phase 3: Supporting Modules (⏳ Pending)
- ⏳ Sarana & Jadwal
- ⏳ Keuangan
- ⏳ Reporting

---

## 📞 Informasi Kontak

Untuk pertanyaan atau saran terkait dokumentasi ini, silakan hubungi tim pengembang.

---

**Terakhir Diperbarui**: 27 Januari 2026  
**Versi Dokumen**: 1.0  
**Status Proyek**: Active Development
