# Panduan Menjalankan Aplikasi (Development)

## 1. Perintah utama: `composer dev`

```bash
composer dev
```

Perintah ini menjalankan **4 proses sekaligus** di satu terminal (pakai `concurrently`), masing-masing diberi label warna:

| Proses | Perintah asli | Fungsi |
|---|---|---|
| `server` | `php artisan serve` | Web server aplikasi (`http://127.0.0.1:8000`) |
| `queue`  | `php artisan queue:listen --queue=default,notifikasi --tries=1 --timeout=0` | **Queue worker** — memproses pekerjaan latar belakang (lihat §2) |
| `logs`   | `php artisan pail --timeout=0` | Live log viewer di terminal |
| `vite`   | `npm run dev` | Build & hot-reload aset frontend (CSS/JS) |

Tutup dengan `Ctrl+C` sekali untuk mematikan keempatnya bersamaan (`--kill-others`).

## 2. Kenapa proses `queue` itu penting

Beberapa fitur di aplikasi ini **tidak mengerjakan tugasnya secara langsung** saat sebuah aksi dilakukan — sebaliknya, aksi itu hanya "menitipkan pekerjaan" ke tabel `jobs` di database, dan pekerjaan itu baru benar-benar dijalankan oleh proses `queue` di atas. Ini disebut *queue* (antrian), dipakai supaya aplikasi tetap terasa cepat (tidak menunggu proses lambat seperti kirim email selesai dulu).

Fitur yang masih bergantung pada proses `queue` ini:
- **Notifikasi PPDB** (email/in-app ke pendaftar) — antrian `notifikasi`.

**Akibatnya kalau proses `queue` tidak berjalan** (atau berjalan tapi tidak menyertakan nama antrian yang benar): aplikasi tetap akan bilang **"Berhasil"** ketika tombol kirim ditekan (karena pekerjaannya memang berhasil "dititipkan"), TAPI pesannya tidak pernah benar-benar terkirim — hanya menumpuk diam-diam di tabel `jobs`.

> **Catatan tentang notifikasi WhatsApp absensi** (tombol "Selesai Mengajar & Kirim Notifikasi" di aplikasi mobile guru): fitur ini **sengaja TIDAK memakai antrian** (dikirim langsung/synchronous saat tombol ditekan) — persis karena masalah di atas pernah membuat notifikasi silently tertahan tanpa terkirim padahal aplikasi melaporkan "berhasil". Jadi fitur ini akan selalu langsung terkirim tanpa bergantung proses `queue` sama sekali, dengan konsekuensi guru menunggu beberapa detik lebih lama saat menekan tombolnya (pesan dikirim satu per satu ke tiap wali murid sebelum respons "Berhasil" muncul).

## 3. Cara memastikan semuanya berjalan dengan benar

1. Jalankan `composer dev` dari root project, biarkan terminalnya tetap terbuka selama Anda memakai aplikasi (jangan ditutup).
2. Perhatikan baris berlabel **`queue`** di output terminal — harus tampil dan tidak menunjukkan error saat start.
3. Setelah memicu notifikasi PPDB (email/in-app), perhatikan baris `queue` di terminal — akan muncul log seperti:
   ```
   queue | App\Jobs\KirimNotifikasiJob ... RUNNING
   queue | App\Jobs\KirimNotifikasiJob ... DONE
   ```
   Kalau baris ini TIDAK muncul beberapa detik setelahnya, berarti proses `queue` tidak sedang berjalan atau tidak mendengarkan antrian yang benar.

## 4. Troubleshooting — cek/bersihkan pekerjaan yang tertahan

Cek apakah ada pekerjaan yang masih menumpuk (belum diproses):
```bash
php artisan tinker --execute="
\Illuminate\Support\Facades\DB::table('jobs')->selectRaw('queue, count(*) as jml')->groupBy('queue')->get()->each(function(\$r){ echo \$r->queue.': '.\$r->jml.PHP_EOL; });
"
```
Kalau ada angka di baris `notifikasi`, berarti ada notifikasi PPDB yang tertahan.

**Proses pekerjaan yang tertahan itu sekarang juga** (tanpa perlu restart `composer dev`, cukup sekali jalan lalu berhenti otomatis setelah antrian kosong):
```bash
php artisan queue:work --queue=default,notifikasi --stop-when-empty --tries=1
```

Cek apakah ada pekerjaan yang **gagal** (dicoba tapi error):
```bash
php artisan tinker --execute="echo \Illuminate\Support\Facades\DB::table('failed_jobs')->count();"
```
Kalau ada yang gagal, lihat detail errornya di tabel `failed_jobs` (kolom `exception`) atau di log (`storage/logs/laravel.log`).

Kalau **notifikasi WhatsApp absensi** gagal terkirim, itu bukan soal antrian (fitur ini synchronous) — cek langsung di log `storage/logs/laravel.log`, cari `[KirimAbsensiWhatsappJob]`, atau cek status device Fonnte di menu admin System → WhatsApp.

## 5. Untuk server produksi (bukan development)

`composer dev` **hanya untuk pengembangan di komputer lokal** — proses `queue:listen` di dalamnya akan berhenti begitu terminal ditutup. Di server produksi, proses queue harus dijalankan **permanen** dan otomatis restart kalau crash, biasanya lewat [Supervisor](http://supervisord.org/):

```ini
[program:sms-app-queue]
command=php /path/ke/project/artisan queue:work --queue=default,notifikasi --tries=1 --timeout=60
autostart=true
autorestart=true
numprocs=1
user=www-data
```

Pastikan daftar `--queue=...` di server produksi selalu sinkron dengan daftar yang dipakai di `composer dev` (lihat §1) setiap kali menambah fitur baru yang memakai antrian bernama baru.
