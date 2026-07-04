# Hak Akses Pengguna Mobile (Aplikasi Guru)

Dokumen ini menjelaskan siapa yang boleh login ke aplikasi mobile (React Native — Marifat Mobile Guru), data apa saja yang bisa mereka lihat/ubah, dan batasan yang ditegakkan server terhadap setiap aksi. Semua batasan di bawah diberlakukan di sisi **backend** (bukan hanya UI mobile) — endpoint API akan menolak permintaan yang melanggarnya meski request dibuat langsung ke API tanpa lewat aplikasi.

Referensi kode: `routes/api.php`, `app/Http/Controllers/Api/*`, `app/Services/Akademik/*Service.php`.

## 1. Siapa yang bisa login

| Syarat | Keterangan |
|---|---|
| Punya akun `users` aktif | `status = active`; login pakai email **atau** username + password |
| Akun tertaut ke satu record `guru` | Login ditolak (403) jika `user->guru` kosong — role admin/staf yang tidak punya record guru **tidak bisa** masuk ke aplikasi mobile |
| Satu device aktif per akun | Login baru mencabut semua token Sanctum lama milik user tsb (`$user->tokens()->delete()`) — login di HP baru otomatis logout dari HP lama |

Aplikasi mobile ini **khusus guru**. Tidak ada peran "siswa", "orang tua", atau "admin" di dalamnya — semua endpoint mengasumsikan pengguna adalah guru yang sedang login.

## 2. Mekanisme otentikasi

- **Laravel Sanctum**, Bearer token (`Authorization: Bearer <token>`), bukan sesi cookie.
- Semua route `v1/*` kecuali `POST /login` wajib token valid (middleware `auth:sanctum`).
- Token bersifat **full-access** (tidak pakai Sanctum abilities/scope) — pembatasan akses dilakukan lewat query scoping di setiap service, bukan lewat token ability.
- Logout mencabut token yang sedang dipakai (`currentAccessToken()->delete()`), bukan semua token.

## 3. Prinsip scoping: "milik sendiri saja"

Setiap endpoint me-resolve identitas guru dari token (`$request->user()->guru`), lalu memfilter/validasi data terhadap `guru_id` tersebut. Guru **tidak bisa** melihat atau mengubah data guru lain, lembaga lain, atau kelas yang bukan jadwalnya — walau ID-nya ditebak manual di request.

| Data | Cara scoping | Efek jika dilanggar |
|---|---|---|
| Profil (`/me`) | Ambil `guru` dari user yang login | Tidak ada parameter ID — mustahil intip profil guru lain |
| Absensi guru (`/absensi-guru/*`) | Semua query pakai `guru_id` milik sendiri | Tidak ada endpoint untuk lihat absensi guru lain |
| Jadwal (`/jadwal/*`) | `JadwalKbm::where('guru_id', $guru->id)` | Guru hanya melihat jam mengajarnya sendiri, lintas lembaga sekalipun (kolom `lembaga_id` ikut ke jadwal) |
| Kelas (`/kelas/{jadwal}/*`) | `resolveJadwal()`: `abort_if($jadwal->guru_id !== $guru->id, 403, 'Jadwal ini bukan milik Anda.')` | Request ke `jadwal` ID milik guru lain → **403 Forbidden**, bukan data kosong (mencegah kebocoran lewat status code pun tidak — pesan generik) |
| Siswa dalam kelas | Diturunkan dari `rombel_id` milik `$jadwal` yang sudah divalidasi | Guru hanya bisa melihat/absen siswa di rombel sesuai jadwalnya, tidak bisa pilih rombel bebas |

## 4. Absensi Guru (masuk/pulang) — Fase 1

Endpoint: `GET today`, `POST masuk`, `POST pulang` (`AbsensiGuruService`).

| Kontrol | Detail |
|---|---|
| **Geofencing** | Jarak GPS ke titik lembaga dihitung **di server** (Haversine), bukan dipercaya dari HP. Ditolak jika di luar `radius_meter` lembaga (default 100m) — pesan mencantumkan jarak aktual |
| **Anti mock-location** | Field `is_mock` dari HP (deteksi fake-GPS native) — jika `true`, absen ditolak total |
| **Wajib selfie** | `selfie` wajib, image, maks 5MB — disimpan per guru per sesi (`absensi-guru/{guru_id}/masuk|pulang`) |
| **Satu kali per hari** | Masuk ditolak jika sudah absen masuk hari itu; pulang ditolak jika belum absen masuk atau sudah absen pulang |
| **Status otomatis** | "Hadir" vs "Terlambat" dihitung server dari `jam_masuk_batas` lembaga — guru tidak mengisi status sendiri |

Guru **tidak bisa**: mengedit/menghapus riwayat absensinya sendiri (tidak ada endpoint update/destroy di mobile — itu hanya ada di panel admin), atau melihat rekap absensi guru lain.

## 5. Jadwal Mengajar — Fase 2

Endpoint: `GET jadwal/today`, `GET jadwal` (mingguan). Read-only, difilter `guru_id`. Setiap baris jadwal menyertakan flag `absensi_dibuat` (dipakai mobile untuk menandai kelas mana yang sudah/belum diproses hari itu) — ini murni indikator UI, bukan mekanisme kunci (kuncinya ada di Fase 3, lihat §6).

## 6. Masuk Kelas: Absensi Siswa → Materi → Nilai — Fase 3

Endpoint di bawah `kelas/{jadwal}/*`, semua mensyaratkan `jadwal.guru_id === guru login` (lihat §3).

| Endpoint | Akses | Catatan |
|---|---|---|
| `GET siswa` | Daftar siswa rombel + status hadir hari ini | Rombel diturunkan dari jadwal, bukan input bebas |
| `POST absensi` / `POST absensi/scan` | Simpan kehadiran siswa | Validasi tiap `peserta_id` harus anggota rombel tsb — siswa dari rombel lain ditolak (`RuntimeException` → 422) |
| `GET materi` | RPP + materi ajar milik guru untuk mapel jadwal ini | Hanya materi yang `status = disetujui` yang tampil — draft/belum disetujui admin tidak bocor ke mobile |
| `GET nilai` / `POST nilai` | **Terkunci** (HTTP 423) sampai sesi absensi siswa untuk rombel+mapel+tanggal hari ini dibuat | Ini aturan produk eksplisit: guru tidak bisa mengisi/lihat nilai sebelum mengabsen siswanya lebih dulu — dicek ulang di server tiap kali (`guardAbsensiSelesai()`), tidak bisa dilewati dari sisi klien |

**Kunci nilai ini berlaku per rombel+mapel+tanggal**, bukan per jadwal — begitu sesi absensi dibuat untuk kombinasi itu (lewat form manual atau scan QR), nilai langsung terbuka untuk jadwal manapun yang cocok kombinasinya hari itu.

## 7. Push Notification Token

`POST/DELETE device-token` — guru hanya bisa mendaftar/menghapus token perangkatnya sendiri (`$request->user()->id_user`). Dipakai untuk kirim notifikasi jadwal mengajar (`JadwalNotifikasiService` + `KirimNotifikasiJadwal` command, lewat Expo Push Service).

## 8. Ringkasan: yang TIDAK BISA dilakukan lewat mobile

- Login tanpa record `guru` yang tertaut (admin murni, staf TU, dll).
- Melihat/mengelola data guru, absensi, jadwal, atau kelas milik guru lain.
- Membuka atau submit nilai sebelum absensi siswa hari itu selesai.
- Absen masuk/pulang dari luar radius geofence lembaga, atau dengan mock location aktif.
- Absen dua kali (masuk/pulang) di hari yang sama.
- Mengedit riwayat absensi guru yang sudah tersimpan (read-only setelah submit — hanya admin panel yang punya akses koreksi, lewat menu Absensi Guru).
- Melihat materi ajar yang belum disetujui admin.
- Mengakses data lembaga lain — token tidak dibatasi per-lembaga secara eksplisit, tapi setiap query selalu diturunkan dari `guru_id` sehingga guru multi-lembaga (jika ada) tetap hanya melihat jadwalnya sendiri di lembaga manapun ia mengajar.

## 9. Celah/rekomendasi yang belum ditangani (untuk diketahui, belum diperbaiki)

- Tidak ada rate limiting eksplisit pada route `v1/*` di luar default Laravel — percobaan brute-force `POST login` tidak dibatasi throttle khusus.
- Token Sanctum bersifat full-access (tanpa `abilities`) — cukup aman selama setiap endpoint tetap disiplin melakukan scoping manual seperti saat ini, tapi tidak ada lapisan pertahanan kedua di level token jika suatu endpoint baru lupa menambahkan guard kepemilikan.
