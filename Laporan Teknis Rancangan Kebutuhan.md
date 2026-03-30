**Laporan Teknis: Rancangan Kebutuhan Data dan Arsitektur Database  
SISTEM PENERIMAAN PESERTA DIDIK BARU (PPDB) TAHUN PELAJARAN 2026/2027 Berbasis Laravel 12 & MySQL**

**Pendahuluan**

Sistem Penerimaan Peserta Didik Baru (PPDB) merupakan komponen vital dalam tata kelola pendidikan dasar dan menengah di Indonesia. Dengan semakin kompleksnya regulasi, kebutuhan integrasi data nasional (Dapodik), serta tuntutan fleksibilitas proses seleksi, perancangan arsitektur data dan database PPDB harus dilakukan secara sistematis, terstandar, dan adaptif terhadap perubahan kebijakan serta kebutuhan institusi. Laporan ini menyajikan rancangan lengkap kebutuhan data dan struktur tabel database untuk aplikasi PPDB tahun pelajaran 2026/2027 yang akan dibangun menggunakan Laravel 12 dan MySQL, dengan sistem RBAC (Role-Based Access Control) yang telah terintegrasi.

Fokus utama laporan ini adalah:

- Penyusunan daftar tabel, kolom, tipe data, contoh data, dan keterangan, dengan penekanan pada data peserta statis sesuai standar Dapodik.
- Simulasi proses konfigurasi awal oleh admin, mulai dari pengaturan tahun pelajaran hingga proses seleksi dan penilaian.
- Simulasi alur lengkap pendaftaran siswa dari awal hingga menjadi siswa tetap.

Seluruh desain mengacu pada praktik terbaik pengembangan aplikasi Laravel, standar Dapodik, serta kebutuhan fleksibilitas multi-jalur dan multi-tahun pelajaran.

**1\. Analisis Kebutuhan Data Peserta Statis (Standar Dapodik)**

**1.1. Standar Data Peserta Didik Dapodik**

Data peserta didik yang digunakan dalam PPDB harus mengacu pada standar Dapodik, baik dari sisi struktur maupun validasi. Berdasarkan dokumen resmi Dapodik dan SOP PPDB Kemdikbud, data peserta statis meliputi:

- **Data Pribadi:** NISN, NIK, nama lengkap, tempat/tanggal lahir, jenis kelamin, agama, kebutuhan khusus, alamat lengkap, koordinat, dsb.
- **Data Orang Tua/Wali:** Nama, pekerjaan, penghasilan, kebutuhan khusus, dsb.
- **Data Periodik:** Tinggi/berat badan, jarak dan waktu tempuh ke sekolah, jumlah saudara kandung.
- **Kontak:** Nomor telepon, email.
- **Data Beasiswa, Kesejahteraan, dan Dokumen Pendukung:** KIP, PKH, KITAS, paspor, dsb.

Validasi data peserta didik sangat ketat, terutama untuk variabel master seperti NISN, NIK, nama ibu kandung, dan koordinat.

**1.2. Implikasi pada Database PPDB**

Data statis peserta harus disimpan dalam tabel peserta yang terstruktur dan terstandar, sehingga memudahkan integrasi dengan Dapodik dan proses audit. Setiap perubahan data peserta harus tercatat dalam histori perubahan (audit log) untuk memenuhi aspek kepatuhan data (UU PDP).

**2\. Rancangan Arsitektur Database PPDB**

**2.1. Gambaran Umum Entitas dan Relasi**

Arsitektur database PPDB harus mendukung:

- Multi-tahun pelajaran dan multi-pembukaan PPDB per tahun.
- Multi-jalur pendaftaran dengan variasi alur, syarat, jadwal, dan biaya.
- Formulir pendaftaran yang terdiri dari data statis (Dapodik) dan data dinamis (custom per tahun/jalur).
- Proses seleksi, penilaian, dan pengumuman.
- Manajemen dokumen, pembayaran, notifikasi, dan audit log.

Secara konseptual, entitas utama meliputi: user, peserta, tahun pelajaran, pembukaan PPDB, jalur pendaftaran, jadwal, syarat, formulir, dokumen, biaya, pembayaran, seleksi, penilaian, hasil seleksi, pengumuman, kartu peserta, notifikasi, audit log, dan histori perubahan.

**2.1.1. Diagram ER (Entity Relationship)**

Secara ringkas, relasi utama adalah:

- Satu tahun pelajaran memiliki banyak pembukaan PPDB.
- Satu pembukaan PPDB memiliki banyak jalur pendaftaran.
- Satu jalur pendaftaran memiliki banyak jadwal, syarat, formulir, dan biaya.
- Satu peserta dapat mendaftar pada satu atau beberapa jalur (tergantung kebijakan).
- Setiap pendaftaran peserta terkait dengan dokumen, pembayaran, proses seleksi, dan hasil seleksi.

**2.2. Daftar Tabel Utama dan Penjelasan**

Berikut adalah tabel utama beserta struktur, tipe data, contoh data, dan keterangan. Tabel-tabel ini dirancang agar mudah diimplementasikan dengan Laravel migration dan Eloquent ORM.

**2.2.1. Tabel user (Akun Login & RBAC) (sudah ada tidak perlu dieksekusi)**

| **Kolom**  | **Tipe Data** | **Contoh Data**     | **Keterangan**        |
| ---------- | ------------- | ------------------- | --------------------- |
| id         | BIGINT, PK    | 1                   | Primary key           |
| name       | VARCHAR(100)  | "Admin PPDB"        | Nama lengkap          |
| email      | VARCHAR(100)  | <admin@ppdb.ac.id>  | Email unik            |
| password   | VARCHAR(255)  | hashed              | Password hash         |
| role_id    | BIGINT, FK    | 1                   | Relasi ke tabel roles |
| is_active  | BOOLEAN       | 1                   | Status aktif/nonaktif |
| created_at | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan   |
| updated_at | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update      |

**Penjelasan:**  
Tabel ini menyimpan data akun login untuk seluruh pengguna sistem (admin, operator, reviewer, peserta). Integrasi RBAC dilakukan melalui relasi ke tabel roles dan permissions (mengacu pada paket Spatie Laravel Permission).

**2.2.2. Tabel roles & permissions (RBAC) (sudah ada tidak perlu dieksekusi)**

| **Kolom**  | **Tipe Data** | **Contoh Data** | **Keterangan** |
| ---------- | ------------- | --------------- | -------------- |
| id         | BIGINT, PK    | 1               | Primary key    |
| name       | VARCHAR(50)   | "admin"         | Nama role      |
| guard_name | VARCHAR(50)   | "web"           | Guard Laravel  |

| **Kolom**  | **Tipe Data** | **Contoh Data**  | **Keterangan**  |
| ---------- | ------------- | ---------------- | --------------- |
| id         | BIGINT, PK    | 1                | Primary key     |
| name       | VARCHAR(50)   | "manage_peserta" | Nama permission |
| guard_name | VARCHAR(50)   | "web"            | Guard Laravel   |

**Penjelasan:**  
RBAC memungkinkan pengaturan hak akses granular per fitur, sesuai kebutuhan aplikasi PPDB modern.

**2.2.3. Tabel peserta (Data Statis Dapodik) (Harus dipecah menjadi beberapa table sesuai dengan poin 1.1 Standar Data Peserta Didik Dapodik)**

| **Kolom**        | **Tipe Data** | **Contoh Data**     | **Keterangan**              |
| ---------------- | ------------- | ------------------- | --------------------------- |
| id               | BIGINT, PK    | 1                   | Primary key                 |
| user_id          | BIGINT, FK    | 2                   | Relasi ke tabel user        |
| nisn             | VARCHAR(10)   | "1234567890"        | NISN, unik nasional         |
| nik              | VARCHAR(16)   | "3216060000000001"  | NIK, unik nasional          |
| nama_lengkap     | VARCHAR(100)  | "Ahmad Zulkarnain"  | Nama lengkap                |
| jenis_kelamin    | ENUM(L/P)     | "L"                 | Laki-laki/Perempuan         |
| tempat_lahir     | VARCHAR(50)   | "Depok"             | Tempat lahir                |
| tanggal_lahir    | DATE          | 2010-12-29          | Tanggal lahir               |
| agama            | VARCHAR(20)   | "Islam"             | Agama                       |
| kebutuhan_khusus | VARCHAR(50)   | "Tidak Ada"         | Kebutuhan khusus            |
| alamat           | VARCHAR(255)  | "Jl. Merdeka No. 1" | Alamat lengkap              |
| desa_kelurahan   | VARCHAR(50)   | "Beji"              | Desa/Kelurahan              |
| kecamatan        | VARCHAR(50)   | "Beji"              | Kecamatan                   |
| kabupaten_kota   | VARCHAR(50)   | "Depok"             | Kabupaten/Kota              |
| provinsi         | VARCHAR(50)   | "Jawa Barat"        | Provinsi                    |
| rt               | VARCHAR(5)    | "001"               | RT                          |
| rw               | VARCHAR(5)    | "002"               | RW                          |
| dusun            | VARCHAR(50)   | "Dusun 1"           | Dusun                       |
| kode_pos         | VARCHAR(10)   | "16421"             | Kode pos                    |
| lintang          | DECIMAL(9,6)  | \-6.402484          | Koordinat lintang           |
| bujur            | DECIMAL(9,6)  | 106.794243          | Koordinat bujur             |
| nama_ayah        | VARCHAR(100)  | "Budi Santoso"      | Nama ayah kandung           |
| pekerjaan_ayah   | VARCHAR(50)   | "PNS"               | Pekerjaan ayah              |
| penghasilan_ayah | VARCHAR(20)   | "5.000.000"         | Penghasilan ayah            |
| nama_ibu         | VARCHAR(100)  | "Utami"             | Nama ibu kandung            |
| pekerjaan_ibu    | VARCHAR(50)   | "Ibu Rumah Tangga"  | Pekerjaan ibu               |
| penghasilan_ibu  | VARCHAR(20)   | "2.000.000"         | Penghasilan ibu             |
| nama_wali        | VARCHAR(100)  | "Siti Aminah"       | Nama wali                   |
| pekerjaan_wali   | VARCHAR(50)   | "Wiraswasta"        | Pekerjaan wali              |
| penghasilan_wali | VARCHAR(20)   | "1.500.000"         | Penghasilan wali            |
| no_kip           | VARCHAR(20)   | "KIP123456"         | Nomor KIP                   |
| no_pkh           | VARCHAR(20)   | "PKH654321"         | Nomor PKH                   |
| no_kk            | VARCHAR(16)   | "3216060000000002"  | Nomor KK                    |
| no_kitas         | VARCHAR(20)   | "-"                 | Nomor KITAS (jika ada)      |
| no_paspor        | VARCHAR(20)   | "-"                 | Nomor paspor (jika ada)     |
| tinggi_badan     | INT           | 165                 | Tinggi badan (cm)           |
| berat_badan      | INT           | 55                  | Berat badan (kg)            |
| lingkar_kepala   | INT           | 50                  | Lingkar kepala (cm)         |
| jarak_rumah      | DECIMAL(5,2)  | 2.50                | Jarak rumah ke sekolah (km) |
| waktu_tempuh     | INT           | 30                  | Waktu tempuh (menit)        |
| jumlah_saudara   | INT           | 2                   | Jumlah saudara kandung      |
| no_hp            | VARCHAR(15)   | "081234567890"      | Nomor HP                    |
| email            | VARCHAR(100)  | "<ahmad@email.com>" | Email                       |
| created_at       | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan         |
| updated_at       | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update            |

**Penjelasan:**  
Tabel ini wajib mengikuti standar Dapodik untuk memastikan integrasi dan validasi data nasional. Kolom-kolom tambahan dapat disesuaikan sesuai kebutuhan institusi, namun kolom utama harus tetap ada.

**2.2.4. Tabel tahun_pelajaran**

| **Kolom**  | **Tipe Data** | **Contoh Data**             | **Keterangan**                  |
| ---------- | ------------- | --------------------------- | ------------------------------- |
| id         | BIGINT, PK    | 1                           | Primary key                     |
| kode_tahun | VARCHAR(9)    | "2026/2027"                 | Kode tahun pelajaran            |
| nama       | VARCHAR(50)   | "Tahun Pelajaran 2026/2027" | Nama tahun pelajaran            |
| mulai      | DATE          | 2026-07-01                  | Tanggal mulai tahun pelajaran   |
| selesai    | DATE          | 2027-06-30                  | Tanggal selesai tahun pelajaran |
| status     | ENUM          | "aktif"/"nonaktif"          | Status tahun pelajaran          |
| created_at | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp pembuatan             |
| updated_at | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp update                |

**Penjelasan:**  
Mendukung multi-tahun pelajaran dan pengelolaan status aktif/nonaktif. Satu tahun pelajaran dapat memiliki beberapa pembukaan PPDB.

**2.2.5. Tabel semester**

| **Kolom**          | **Tipe Data** | **Contoh Data**     | **Keterangan**                  |
| ------------------ | ------------- | ------------------- | ------------------------------- |
| id                 | BIGINT, PK    | 1                   | Primary key                     |
| tahun_pelajaran_id | BIGINT, FK    | 1                   | Relasi ke tabel tahun_pelajaran |
| nama               | VARCHAR(20)   | "Semester Ganjil"   | Nama semester                   |
| semester_ke        | INT           | 1                   | Angka semester (1 atau 2)       |
| mulai              | DATE          | 2026-07-01          | Tanggal mulai semester          |
| selesai            | DATE          | 2026-12-31          | Tanggal selesai semester        |
| status             | ENUM          | "aktif"/"nonaktif"  | Status semester                 |
| created_at         | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan             |
| updated_at         | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update                |

**Penjelasan:**  
Tabel semester berelasi dengan tahun pelajaran dan digunakan untuk membagi periode kegiatan akademik. Semester ganjil dan genap dalam satu tahun pelajaran dapat dikelola secara terpisah, termasuk untuk kegiatan belajar mengajar, penjadwalan, dan pelaporan.

**2.2.6. Tabel pembukaan_ppdb**

| **Kolom**          | **Tipe Data** | **Contoh Data**           | **Keterangan**                  |
| ------------------ | ------------- | ------------------------- | ------------------------------- |
| id                 | BIGINT, PK    | 1                         | Primary key                     |
| tahun_pelajaran_id | BIGINT, FK    | 1                         | Relasi ke tabel tahun_pelajaran |
| nama               | VARCHAR(100)  | "Gelombang 1"             | Nama pembukaan PPDB             |
| deskripsi          | TEXT          | "Pendaftaran gelombang 1" | Deskripsi pembukaan PPDB        |
| mulai              | DATE          | 2026-03-01                | Tanggal mulai pembukaan         |
| selesai            | DATE          | 2026-04-30                | Tanggal selesai pembukaan       |
| status             | ENUM          | "buka"/"tutup"            | Status pembukaan                |
| created_at         | TIMESTAMP     | 2026-03-23 01:45:00       | Timestamp pembuatan             |
| updated_at         | TIMESTAMP     | 2026-03-23 01:45:00       | Timestamp update                |

**Penjelasan:**  
Mendukung beberapa pembukaan PPDB dalam satu tahun pelajaran (multi-gelombang).

**2.2.7. Tabel jalur_pendaftaran**

| **Kolom**         | **Tipe Data** | **Contoh Data**         | **Keterangan**                     |
| ----------------- | ------------- | ----------------------- | ---------------------------------- |
| id                | BIGINT, PK    | 1                       | Primary key                        |
| pembukaan_ppdb_id | BIGINT, FK    | 1                       | Relasi ke tabel pembukaan_ppdb     |
| kode_jalur        | VARCHAR(20)   | "prestasi"              | Kode jalur (prestasi, zonasi, dll) |
| nama              | VARCHAR(50)   | "Jalur Prestasi"        | Nama jalur pendaftaran             |
| deskripsi         | TEXT          | "Jalur khusus prestasi" | Deskripsi jalur                    |
| kuota             | INT           | 50                      | Kuota peserta jalur ini            |
| urutan            | INT           | 1                       | Urutan tampil                      |
| status            | ENUM          | "aktif"/"nonaktif"      | Status jalur                       |
| created_at        | TIMESTAMP     | 2026-03-23 01:45:00     | Timestamp pembuatan                |
| updated_at        | TIMESTAMP     | 2026-03-23 01:45:00     | Timestamp update                   |

**Penjelasan:**  
Setiap pembukaan PPDB dapat memiliki beberapa jalur pendaftaran dengan kuota dan alur berbeda.

**2.2.8. Tabel jadwal_pendaftaran**

| **Kolom**            | **Tipe Data** | **Contoh Data**      | **Keterangan**                    |
| -------------------- | ------------- | -------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                    | Primary key                       |
| jalur_pendaftaran_id | BIGINT, FK    | 1                    | Relasi ke tabel jalur_pendaftaran |
| nama                 | VARCHAR(100)  | "Pendaftaran Online" | Nama jadwal                       |
| mulai                | DATETIME      | 2026-03-01 08:00:00  | Tanggal mulai jadwal              |
| selesai              | DATETIME      | 2026-03-31 23:59:59  | Tanggal selesai jadwal            |
| status               | ENUM          | "aktif"/"nonaktif"   | Status jadwal                     |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00  | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00  | Timestamp update                  |

**Penjelasan:**  
Jadwal dapat diatur fleksibel per jalur dan tahun, mendukung slot waktu berbeda untuk setiap proses (pendaftaran, seleksi, pengumuman, dsb).

**2.2.9. Tabel syarat_pendaftaran**

| **Kolom**            | **Tipe Data** | **Contoh Data**     | **Keterangan**                    |
| -------------------- | ------------- | ------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                   | Primary key                       |
| jalur_pendaftaran_id | BIGINT, FK    | 1                   | Relasi ke tabel jalur_pendaftaran |
| tahun_pelajaran_id   | BIGINT, FK    | 1                   | Relasi ke tabel tahun_pelajaran   |
| nama                 | VARCHAR(100)  | "Scan Ijazah"       | Nama syarat                       |
| tipe                 | ENUM          | "dokumen"/"isian"   | Tipe syarat (dokumen/isian)       |
| wajib                | BOOLEAN       | 1                   | Wajib/tidak                       |
| keterangan           | TEXT          | "Ijazah SMP/MTs"    | Keterangan syarat                 |
| urutan               | INT           | 1                   | Urutan tampil                     |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update                  |

**Penjelasan:**  
Syarat dapat dikonfigurasi per jalur dan tahun, baik berupa dokumen upload maupun isian data. Mendukung validasi wajib/tidak wajib.

**2.2.10. Tabel formulir_pendaftaran & formulir_field**

| **Kolom**            | **Tipe Data** | **Contoh Data**                  | **Keterangan**                    |
| -------------------- | ------------- | -------------------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                                | Primary key                       |
| jalur_pendaftaran_id | BIGINT, FK    | 1                                | Relasi ke tabel jalur_pendaftaran |
| tahun_pelajaran_id   | BIGINT, FK    | 1                                | Relasi ke tabel tahun_pelajaran   |
| nama                 | VARCHAR(100)  | "Formulir Jalur Prestasi"        | Nama formulir                     |
| deskripsi            | TEXT          | "Formulir khusus jalur prestasi" | Deskripsi formulir                |
| tipe                 | ENUM          | "statis"/"dinamis"               | Tipe formulir                     |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00              | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00              | Timestamp update                  |

| **Kolom**   | **Tipe Data** | **Contoh Data**                        | **Keterangan**                |
| ----------- | ------------- | -------------------------------------- | ----------------------------- |
| id          | BIGINT, PK    | 1                                      | Primary key                   |
| formulir_id | BIGINT, FK    | 1                                      | Relasi ke tabel formulir      |
| kode_field  | VARCHAR(50)   | "prestasi_akademik"                    | Kode field                    |
| label       | VARCHAR(100)  | "Prestasi Akademik"                    | Label field                   |
| tipe_field  | ENUM          | "text"/"number"/"date"/"select"/"file" | Tipe field                    |
| is_required | BOOLEAN       | 1                                      | Wajib/tidak                   |
| is_statis   | BOOLEAN       | 0                                      | Apakah field statis (Dapodik) |
| urutan      | INT           | 1                                      | Urutan tampil                 |
| opsi        | TEXT (JSON)   | '\["Juara 1","Juara 2"\]'              | Opsi untuk select/multiselect |
| created_at  | TIMESTAMP     | 2026-03-23 01:45:00                    | Timestamp pembuatan           |
| updated_at  | TIMESTAMP     | 2026-03-23 01:45:00                    | Timestamp update              |

**Penjelasan:**  
Formulir dapat terdiri dari field statis (mengacu Dapodik) dan dinamis (custom per jalur/tahun). Field dinamis mendukung conditional logic, validasi, dan branching sesuai kebutuhan.

**2.2.11. Tabel pendaftaran**

| **Kolom**            | **Tipe Data** | **Contoh Data**                                                                  | **Keterangan**                    |
| -------------------- | ------------- | -------------------------------------------------------------------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                                                                                | Primary key                       |
| peserta_id           | BIGINT, FK    | 1                                                                                | Relasi ke tabel peserta           |
| jalur_pendaftaran_id | BIGINT, FK    | 1                                                                                | Relasi ke tabel jalur_pendaftaran |
| tahun_pelajaran_id   | BIGINT, FK    | 1                                                                                | Relasi ke tabel tahun_pelajaran   |
| status               | ENUM          | "draft"/"submit"/"verifikasi"/"lulus"/"tidak_lulus"/"daftar_ulang"/"siswa_tetap" | Status proses                     |
| tanggal_daftar       | DATETIME      | 2026-03-10 09:00:00                                                              | Tanggal pendaftaran               |
| no_pendaftaran       | VARCHAR(20)   | "PPDB20260001"                                                                   | Nomor pendaftaran unik            |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00                                                              | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00                                                              | Timestamp update                  |

**Penjelasan:**  
Tabel ini merekam setiap proses pendaftaran peserta pada jalur/tahun tertentu, termasuk status dan nomor pendaftaran unik.

**2.2.12. Tabel pendaftaran_field_value**

| **Kolom**         | **Tipe Data** | **Contoh Data**      | **Keterangan**                 |
| ----------------- | ------------- | -------------------- | ------------------------------ |
| id                | BIGINT, PK    | 1                    | Primary key                    |
| pendaftaran_id    | BIGINT, FK    | 1                    | Relasi ke tabel pendaftaran    |
| formulir_field_id | BIGINT, FK    | 1                    | Relasi ke tabel formulir_field |
| value             | TEXT          | "Juara 1 Matematika" | Nilai isian field              |
| created_at        | TIMESTAMP     | 2026-03-23 01:45:00  | Timestamp pembuatan            |
| updated_at        | TIMESTAMP     | 2026-03-23 01:45:00  | Timestamp update               |

**Penjelasan:**  
Menyimpan nilai field dinamis per pendaftaran, mendukung skema dynamic form yang scalable.

**2.2.13. Tabel dokumen_peserta**

| **Kolom**         | **Tipe Data** | **Contoh Data**             | **Keterangan**                     |
| ----------------- | ------------- | --------------------------- | ---------------------------------- |
| id                | BIGINT, PK    | 1                           | Primary key                        |
| pendaftaran_id    | BIGINT, FK    | 1                           | Relasi ke tabel pendaftaran        |
| syarat_id         | BIGINT, FK    | 1                           | Relasi ke tabel syarat_pendaftaran |
| nama_file         | VARCHAR(255)  | "ijazah_ahmad.pdf"          | Nama file dokumen                  |
| path_file         | VARCHAR(255)  | "/uploads/ijazah_ahmad.pdf" | Path file di storage               |
| status_verifikasi | ENUM          | "pending"/"valid"/"invalid" | Status verifikasi dokumen          |
| keterangan        | TEXT          | "Ijazah asli"               | Keterangan tambahan                |
| created_at        | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp upload                   |
| updated_at        | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp update                   |

**Penjelasan:**  
Mendukung upload, validasi, dan audit dokumen peserta. Validasi file dilakukan pada sisi backend dan frontend untuk keamanan.

**2.2.14. Tabel biaya_registrasi**

| **Kolom**            | **Tipe Data** | **Contoh Data**                    | **Keterangan**                    |
| -------------------- | ------------- | ---------------------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                                  | Primary key                       |
| jalur_pendaftaran_id | BIGINT, FK    | 1                                  | Relasi ke tabel jalur_pendaftaran |
| tahun_pelajaran_id   | BIGINT, FK    | 1                                  | Relasi ke tabel tahun_pelajaran   |
| nama                 | VARCHAR(100)  | "Biaya Jalur Prestasi"             | Nama biaya                        |
| nominal              | DECIMAL(12,2) | 250000.00                          | Nominal biaya                     |
| deskripsi            | TEXT          | "Biaya pendaftaran jalur prestasi" | Deskripsi biaya                   |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00                | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00                | Timestamp update                  |

**Penjelasan:**  
Biaya dapat dikonfigurasi berbeda per jalur dan tahun, mendukung model pricing dinamis.

**2.2.15. Tabel pembayaran_ppdb**

| **Kolom**      | **Tipe Data** | **Contoh Data**                     | **Keterangan**                     |
| -------------- | ------------- | ----------------------------------- | ---------------------------------- |
| id             | BIGINT, PK    | 1                                   | Primary key                        |
| pendaftaran_id | BIGINT, FK    | 1                                   | Relasi ke tabel pendaftaran        |
| biaya_id       | BIGINT, FK    | 1                                   | Relasi ke tabel biaya_registrasi   |
| metode         | VARCHAR(50)   | "midtrans"                          | Metode pembayaran                  |
| status         | ENUM          | "pending"/"paid"/"expired"/"failed" | Status pembayaran                  |
| snap_token     | VARCHAR(36)   | "UUID"                              | Token pembayaran (Midtrans/Xendit) |
| amount         | DECIMAL(12,2) | 250000.00                           | Jumlah bayar                       |
| waktu_bayar    | DATETIME      | 2026-03-15 10:00:00                 | Waktu pembayaran                   |
| bukti_bayar    | VARCHAR(255)  | "bukti_ahmad.jpg"                   | Bukti transfer/manual              |
| keterangan     | TEXT          | "Pembayaran via VA"                 | Keterangan tambahan                |
| created_at     | TIMESTAMP     | 2026-03-23 01:45:00                 | Timestamp pembuatan                |
| updated_at     | TIMESTAMP     | 2026-03-23 01:45:00                 | Timestamp update                   |

**Penjelasan:**  
Integrasi dengan payment gateway (Midtrans/Xendit) menggunakan snap_token dan callback. Mendukung status pembayaran otomatis/manual.

**2.2.16. Tabel seleksi & penilaian**

| **Kolom**       | **Tipe Data** | **Contoh Data**     | **Keterangan**                    |
| --------------- | ------------- | ------------------- | --------------------------------- |
| id              | BIGINT, PK    | 1                   | Primary key                       |
| pendaftaran_id  | BIGINT, FK    | 1                   | Relasi ke tabel pendaftaran       |
| reviewer_id     | BIGINT, FK    | 3                   | Relasi ke tabel user (reviewer)   |
| model_penilaian | VARCHAR(50)   | "tes_tulis"         | Model penilaian (tes, portofolio) |
| nilai           | DECIMAL(5,2)  | 85.50               | Nilai seleksi                     |
| bobot           | DECIMAL(3,2)  | 0.60                | Bobot penilaian                   |
| keterangan      | TEXT          | "Nilai tes tulis"   | Keterangan tambahan               |
| waktu_nilai     | DATETIME      | 2026-04-01 14:00:00 | Waktu penilaian                   |
| created_at      | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan               |
| updated_at      | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update                  |

**Penjelasan:**  
Mendukung multi-model penilaian, multi-reviewer, dan audit histori penilaian. Bobot dan aturan seleksi dapat dikonfigurasi per jalur/tahun.

**2.2.17. Tabel hasil_seleksi**

| **Kolom**        | **Tipe Data** | **Contoh Data**       | **Keterangan**              |
| ---------------- | ------------- | --------------------- | --------------------------- |
| id               | BIGINT, PK    | 1                     | Primary key                 |
| pendaftaran_id   | BIGINT, FK    | 1                     | Relasi ke tabel pendaftaran |
| total_nilai      | DECIMAL(5,2)  | 90.00                 | Total nilai akhir           |
| status_kelulusan | ENUM          | "lulus"/"tidak_lulus" | Status kelulusan            |
| peringkat        | INT           | 5                     | Peringkat peserta           |
| reviewer_id      | BIGINT, FK    | 3                     | Reviewer utama              |
| waktu_pengumuman | DATETIME      | 2026-04-10 10:00:00   | Waktu pengumuman            |
| created_at       | TIMESTAMP     | 2026-03-23 01:45:00   | Timestamp pembuatan         |
| updated_at       | TIMESTAMP     | 2026-03-23 01:45:00   | Timestamp update            |

**Penjelasan:**  
Menyimpan hasil akhir seleksi, status kelulusan, dan peringkat peserta.

**2.2.18. Tabel kuota_jurusan**

| **Kolom**            | **Tipe Data** | **Contoh Data**     | **Keterangan**                    |
| -------------------- | ------------- | ------------------- | --------------------------------- |
| id                   | BIGINT, PK    | 1                   | Primary key                       |
| tahun_pelajaran_id   | BIGINT, FK    | 1                   | Relasi ke tabel tahun_pelajaran   |
| jalur_pendaftaran_id | BIGINT, FK    | 1                   | Relasi ke tabel jalur_pendaftaran |
| jurusan_id           | BIGINT, FK    | 2                   | Relasi ke tabel jurusan           |
| kuota                | INT           | 30                  | Kuota jurusan/jalur/tahun         |
| created_at           | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp pembuatan               |
| updated_at           | TIMESTAMP     | 2026-03-23 01:45:00 | Timestamp update                  |

**Penjelasan:**  
Mendukung manajemen kuota per jurusan, jalur, dan tahun pelajaran.

**2.2.19. Tabel template_pengumuman & template_kartu_peserta**

| **Kolom**     | **Tipe Data** | **Contoh Data**             | **Keterangan**      |
| ------------- | ------------- | --------------------------- | ------------------- |
| id            | BIGINT, PK    | 1                           | Primary key         |
| nama          | VARCHAR(100)  | "Template Lulus"            | Nama template       |
| tipe          | ENUM          | "pengumuman"/"kartu"        | Tipe template       |
| file_template | VARCHAR(255)  | "template_lulus.pdf"        | File template PDF   |
| deskripsi     | TEXT          | "Template pengumuman lulus" | Deskripsi template  |
| created_at    | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp pembuatan |
| updated_at    | TIMESTAMP     | 2026-03-23 01:45:00         | Timestamp update    |

**Penjelasan:**  
Mendukung pengelolaan template pengumuman dan kartu peserta (PDF generation).

**2.2.20. Tabel notifikasi**

| **Kolom**   | **Tipe Data** | **Contoh Data**        | **Keterangan**       |
| ----------- | ------------- | ---------------------- | -------------------- |
| id          | BIGINT, PK    | 1                      | Primary key          |
| user_id     | BIGINT, FK    | 2                      | Relasi ke tabel user |
| tipe        | ENUM          | "email"/"sms"/"inapp"  | Tipe notifikasi      |
| judul       | VARCHAR(100)  | "Pengumuman Kelulusan" | Judul notifikasi     |
| isi         | TEXT          | "Selamat, Anda lulus"  | Isi pesan notifikasi |
| status      | ENUM          | "terkirim"/"gagal"     | Status pengiriman    |
| waktu_kirim | DATETIME      | 2026-04-10 10:00:00    | Waktu pengiriman     |
| created_at  | TIMESTAMP     | 2026-03-23 01:45:00    | Timestamp pembuatan  |
| updated_at  | TIMESTAMP     | 2026-03-23 01:45:00    | Timestamp update     |

**Penjelasan:**  
Mendukung notifikasi multi-channel (email, SMS, in-app) untuk status pendaftaran, pengumuman, dsb.

**2.2.21. Tabel audit_log & histori_perubahan (bisa disesuaikan dengan yang sudah diimplementasi pada system berjalan atau tidak perlu sama sekali)**

| **Kolom**  | **Tipe Data** | **Contoh Data**            | **Keterangan**         |
| ---------- | ------------- | -------------------------- | ---------------------- |
| id         | BIGINT, PK    | 1                          | Primary key            |
| user_id    | BIGINT, FK    | 2                          | Relasi ke tabel user   |
| tabel      | VARCHAR(50)   | "peserta"                  | Nama tabel yang diubah |
| aksi       | ENUM          | "insert"/"update"/"delete" | Jenis aksi             |
| data_lama  | JSON          | {...}                      | Data sebelum perubahan |
| data_baru  | JSON          | {...}                      | Data setelah perubahan |
| waktu_aksi | DATETIME      | 2026-03-23 01:45:00        | Waktu aksi             |
| keterangan | TEXT          | "Update NIK"               | Keterangan tambahan    |

**Penjelasan:**  
Audit log wajib untuk kepatuhan data (UU PDP), tracking perubahan, dan forensik keamanan.

**3\. Simulasi Proses Konfigurasi Awal oleh Admin**

**3.1. Langkah-Langkah Konfigurasi**

- **Admin Login:**  
   Admin masuk ke dashboard menggunakan akun dengan role "admin".
- **Pengaturan Tahun Pelajaran:**  
   Admin menambah tahun pelajaran baru (misal: 2026/2027), mengisi tanggal mulai/selesai, dan mengaktifkan status.
- **Pengaturan Semester:**  
   Admin menambah semester (ganjil dan genap) yang berelasi dengan tahun pelajaran aktif, mengatur tanggal mulai/selesai masing-masing semester.
- **Membuat Pembukaan PPDB:**  
   Untuk tahun pelajaran aktif, admin menambah satu atau beberapa pembukaan PPDB (misal: Gelombang 1, Gelombang 2), mengatur tanggal mulai/selesai, dan status.
- **Konfigurasi Jalur Pendaftaran:**  
   Pada setiap pembukaan PPDB, admin menambah jalur pendaftaran (misal: Prestasi, Zonasi, Afirmasi, Mutasi), mengisi kuota, deskripsi, dan status.
- **Pengaturan Jadwal:**  
   Untuk setiap jalur, admin mengatur jadwal pendaftaran, seleksi, pengumuman, dan daftar ulang sesuai kebutuhan.
- **Konfigurasi Syarat Pendaftaran:**  
   Admin menambah syarat pendaftaran per jalur/tahun, baik berupa dokumen upload (ijazah, KK, KIP, dsb) maupun isian data (misal: nomor SKL, prestasi).
- **Desain Formulir Pendaftaran:**  
   Admin mengatur field formulir, memilih field statis (mengacu Dapodik) dan menambah field dinamis sesuai kebutuhan jalur/tahun (misal: portofolio, pilihan jurusan, dsb). Field dapat diatur required/optional, tipe data, dan validasi.
- **Pengaturan Biaya Registrasi:**  
   Admin menentukan biaya registrasi per jalur/tahun, mengisi nominal, deskripsi, dan status.
- **Upload Template Pengumuman & Kartu Peserta:**  
   Admin mengunggah template PDF pengumuman kelulusan dan kartu peserta, yang akan digunakan untuk generate dokumen otomatis.
- **Pengaturan Kuota Jurusan:**  
   Admin mengisi kuota per jurusan/program keahlian untuk setiap jalur dan tahun pelajaran.
- **Konfigurasi Notifikasi:**  
   Admin mengatur template notifikasi (email, SMS, in-app) untuk status pendaftaran, pengumuman, dsb.
- **Audit & Review:**  
   Semua konfigurasi tercatat dalam audit log, dan dapat direview oleh admin superuser untuk kepatuhan.

**3.2. Penjelasan Proses**

Proses konfigurasi awal sangat krusial untuk memastikan seluruh alur pendaftaran berjalan sesuai kebijakan institusi dan regulasi nasional. Dengan desain database yang modular, admin dapat dengan mudah menambah/mengubah jalur, syarat, biaya, dan formulir tanpa perlu perubahan kode aplikasi. Setiap perubahan konfigurasi langsung tercatat dalam audit log untuk kebutuhan audit dan forensik.

**4\. Simulasi Alur Lengkap Proses Pendaftaran Siswa**

**4.1. Alur Proses (End-to-End)**

- **Siswa Membuat Akun:**  
   Siswa mengakses portal PPDB, mengisi data dasar (nama, email, password), dan melakukan verifikasi email/OTP.
- **Login & Lengkapi Data Statis:**  
   Setelah login, siswa melengkapi data statis sesuai Dapodik (NISN, NIK, data orang tua, alamat, dsb). Sistem melakukan validasi otomatis ke database Dapodik/EMIS jika terintegrasi.
- **Pilih Jalur Pendaftaran:**  
   Siswa memilih jalur pendaftaran yang tersedia dan aktif sesuai jadwal. Sistem menampilkan kuota, syarat, dan biaya masing-masing jalur.
- **Mengisi Formulir Pendaftaran:**  
   Siswa mengisi formulir pendaftaran yang terdiri dari field statis (Dapodik) dan dinamis (custom per jalur/tahun). Field dinamis dapat berupa isian teks, select, upload file, dsb. Validasi dilakukan secara real-time (frontend) dan backend.
- **Upload Dokumen Syarat:**  
   Siswa mengunggah dokumen persyaratan (ijazah, KK, KIP, dsb) sesuai syarat jalur. Sistem melakukan validasi tipe file, ukuran, dan keaslian dokumen.
- **Pembayaran Biaya Registrasi:**  
   Siswa melakukan pembayaran biaya registrasi sesuai jalur/tahun. Sistem men-generate snap_token (Midtrans/Xendit), dan siswa diarahkan ke payment gateway. Status pembayaran diupdate otomatis via callback.
- **Verifikasi & Validasi Admin:**  
   Admin/operator melakukan verifikasi data dan dokumen peserta. Jika ada kekurangan, siswa mendapat notifikasi untuk perbaikan.
- **Proses Seleksi & Penilaian:**  
   Setelah pendaftaran ditutup, proses seleksi dilakukan sesuai model penilaian (tes tulis, portofolio, wawancara, dsb). Reviewer menginput nilai, sistem menghitung total nilai dan peringkat otomatis berdasarkan bobot.
- **Pengumuman Hasil Seleksi:**  
   Siswa menerima notifikasi pengumuman kelulusan (email/SMS/in-app). Sistem meng-generate pengumuman dan kartu peserta (PDF) yang dapat diunduh.
- **Daftar Ulang & Konfirmasi Siswa Tetap:**  
   Siswa yang lulus melakukan daftar ulang (upload dokumen tambahan, pembayaran ulang jika ada). Setelah diverifikasi, status peserta diupdate menjadi "siswa tetap".
- **Integrasi ke Dapodik & Export Data:**  
   Data hasil PPDB di-export dalam format CSV sesuai standar Kemdikbud untuk integrasi ke Dapodik/EMIS.
- **Audit & Histori:**  
   Seluruh proses tercatat dalam audit log dan histori perubahan untuk kepatuhan dan monitoring.

**4.2. Penjelasan Alur**

Alur di atas dirancang agar fleksibel, adaptif terhadap perubahan kebijakan, dan mudah diintegrasikan dengan sistem nasional (Dapodik/EMIS). Validasi data dilakukan berlapis (frontend, backend, dan integrasi API), serta mendukung proses seleksi multi-tahap dan multi-model. Proses pembayaran terintegrasi dengan payment gateway nasional, mendukung otomatisasi status dan notifikasi.

**5\. Aspek Teknis Lanjutan**

**5.1. Indexing, Performa, dan Optimasi Query**

Untuk mendukung volume besar pendaftar, database harus dioptimalkan dengan indexing yang tepat. Penggunaan composite index pada kolom filter utama (misal: tahun_pelajaran_id, jalur_pendaftaran_id, status) sangat disarankan untuk mempercepat query filtering dan sorting. Pengujian performa query dan penggunaan EXPLAIN harus rutin dilakukan.

**5.2. Backup, Restore, dan Disaster Recovery**

Backup database harus mengikuti best practice 3-2-1 (3 salinan, 2 media, 1 offsite), dengan jadwal backup otomatis harian dan retention policy yang jelas. Restore harus diuji secara berkala (test restore) untuk memastikan integritas data. Dokumentasi prosedur backup/restore wajib tersedia dan mudah diakses oleh tim IT.

**5.3. Keamanan Aplikasi**

- **Validasi NIK/NISN:**  
   Validasi format dan keunikan NIK/NISN dilakukan otomatis, serta integrasi API ke Dapodik jika memungkinkan.
- **Upload Sanitization:**  
   File upload divalidasi tipe, ukuran, dan dilakukan sanitasi untuk mencegah malware.
- **Akses RBAC:**  
   Hak akses diatur granular per fitur dan data, menggunakan middleware Laravel Permission.
- **Audit Logging:**  
   Semua perubahan data sensitif tercatat di audit log.
- **Proteksi Data Pribadi:**  
   Data sensitif dienkripsi di database dan backup.

**5.4. Testing End-to-End**

Testing dilakukan dengan PHPUnit untuk unit test, Laravel Dusk untuk end-to-end test, dan skenario pendaftaran lengkap (happy path & edge case). Test coverage minimal 80% untuk modul utama.

**5.5. UI/UX & Aksesibilitas**

Formulir pendaftaran didesain mobile-first, mendukung dynamic form (conditional logic, branching, multi-step), dan aksesibilitas (WCAG compliance). Layout stabil saat field muncul/hilang, error message jelas, dan navigasi mudah.

**5.6. Integrasi API & Export Data**

- **Integrasi Dapodik/Backbone:**  
   Mendukung API untuk tarik data peserta dan push hasil PPDB ke Dapodik.
- **Export CSV:**  
   Data hasil PPDB dapat diexport dalam format CSV sesuai standar Kemdikbud untuk upload ke aplikasi Pelayanan Data.

**5.7. Migrasi & Seeding Data**

Migrasi database menggunakan Laravel migration, dengan seeder untuk data contoh peserta, jalur, syarat, dsb. Skrip migrasi dan seeder didokumentasikan dengan baik untuk deployment dan rollback.

**5.8. Referensi Proyek Open Source**

Beberapa proyek open-source PPDB berbasis Laravel dapat dijadikan referensi implementasi, seperti:

- [Paiiss/spmb-laravel](https://github.com/Paiiss/spmb-laravel): Mendukung pendaftaran online, upload dokumen, ujian online, penilaian, dan notifikasi (dapat diadaptasi untuk PPDB).
- [anglczbla/spmb-laravel](https://github.com/anglczbla/spmb-laravel): Fitur lengkap dari registrasi, verifikasi, pembayaran, hingga pengumuman (dapat diadaptasi untuk PPDB).

**6\. Penutup**

Rancangan kebutuhan data dan arsitektur database PPDB tahun pelajaran 2026/2027 di atas telah disusun dengan mengacu pada standar nasional (Dapodik), praktik terbaik pengembangan aplikasi Laravel, serta kebutuhan fleksibilitas multi-jalur dan multi-tahun pelajaran. Struktur tabel yang modular, dukungan dynamic form, integrasi payment gateway, serta fitur audit dan keamanan menjadikan sistem ini siap untuk diimplementasikan pada skala besar dan beragam skenario institusi pendidikan.

Dengan desain ini, proses konfigurasi oleh admin menjadi efisien, alur pendaftaran siswa terstruktur dan mudah dipantau, serta integrasi dengan sistem nasional dan pelaporan dapat dilakukan secara seamless. Seluruh proses didukung oleh audit log dan histori perubahan untuk memastikan kepatuhan dan transparansi.

Implementasi sistem ini diharapkan dapat meningkatkan efisiensi, transparansi, dan akuntabilitas proses PPDB di era digital, serta mendukung kebijakan satu data pendidikan nasional.