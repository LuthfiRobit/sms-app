# Panduan Menjalankan Layanan Face Recognition (Absensi Guru)

Layanan ini adalah microservice Flask terpisah (Python) yang melakukan verifikasi wajah 1:1 (liveness + kecocokan wajah) untuk fitur Absen Masuk/Pulang guru di aplikasi mobile. Laravel (aplikasi ini) memanggilnya lewat HTTP — layanan ini **tidak** memakai database MySQL/SQLite milik Laravel, ia punya SQLite sendiri (`wajah_guru.db`) dan tidak perlu disentuh migrasinya.

Lokasi project: `/Users/reindrairawan/Projects/Laravel/sistem_absensi/flask-face-recognition`

## 1. Prasyarat sistem

Layanan ini bergantung pada `dlib`, yang perlu dikompilasi dari source saat instalasi — butuh compiler C++ dan CMake.

**macOS:**
```bash
brew install cmake pkg-config
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install -y build-essential cmake libopenblas-dev liblapack-dev gfortran
```

Kompilasi `dlib` bisa memakan waktu 5–10 menit tergantung kecepatan CPU — ini normal, bukan hang.

## 2. Install dependency Python

Virtual environment (`venv/`) untuk project ini **sudah ada** di repo. Jika perlu dibuat ulang dari nol:

```bash
cd /Users/reindrairawan/Projects/Laravel/sistem_absensi/flask-face-recognition
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

Kalau `venv/` sudah ada (seperti sekarang), cukup aktifkan saja:
```bash
cd /Users/reindrairawan/Projects/Laravel/sistem_absensi/flask-face-recognition
source venv/bin/activate
```

## 3. Konfigurasi environment variable

File `.env` **tidak otomatis dibaca** oleh aplikasi ini (tidak ada `python-dotenv`) — variabelnya harus benar-benar di-`export` ke shell sebelum menjalankan servernya. Ada dua variabel:

| Variabel | Wajib? | Kegunaan |
|---|---|---|
| `FACE_SERVICE_API_KEY` | **Wajib** | Kunci rahasia yang dicek lewat header `X-API-Key` di setiap request (kecuali `/health`). Kalau kosong, server menolak SEMUA request dengan error 500. |
| `FACE_TOLERANCE` | Opsional (default `0.5`) | Ambang batas kecocokan wajah — makin kecil makin ketat. Rentang wajar 0.45 (ketat) – 0.55 (longgar). |

**Penting:** nilai `FACE_SERVICE_API_KEY` di sini harus **identik** dengan `FACE_RECOGNITION_API_KEY` di file `.env` aplikasi Laravel ini (`/Users/reindrairawan/Projects/Laravel/sms-app/.env`) — kalau tidak sama, Laravel akan selalu dapat error koneksi/401 dari layanan ini.

Nilai yang sudah dipakai di `.env` Laravel saat ini adalah `test-secret-key-12345` (untuk pengembangan lokal) — pakai nilai yang sama:

```bash
export FACE_SERVICE_API_KEY=test-secret-key-12345
export FACE_TOLERANCE=0.5
```

Untuk produksi nanti, ganti dengan nilai acak yang panjang (`openssl rand -hex 32`) dan pastikan `.env` Laravel produksi ikut diperbarui dengan nilai yang sama.

## 4. Menjalankan server

**Mode pengembangan** (auto-restart tidak aktif, tapi cukup untuk testing lokal):
```bash
cd /Users/reindrairawan/Projects/Laravel/sistem_absensi/flask-face-recognition
source venv/bin/activate
export FACE_SERVICE_API_KEY=test-secret-key-12345
export FACE_TOLERANCE=0.5
python app.py
```
Server akan jalan di `http://0.0.0.0:5000` (bisa diakses via `http://127.0.0.1:5000` dari Laravel di mesin yang sama).

**Mode produksi** (pakai Gunicorn, lebih stabil untuk banyak request bersamaan):
```bash
gunicorn --workers 2 --threads 1 --timeout 60 --max-requests 200 --bind 0.0.0.0:5000 app:app
```

**Via Docker** (kalau lebih suka container):
```bash
docker build -t face-recognition-service .
docker run -d -p 5000:5000 \
  -e FACE_SERVICE_API_KEY=test-secret-key-12345 \
  -e FACE_TOLERANCE=0.5 \
  --name face-service --restart unless-stopped \
  face-recognition-service
docker logs -f face-service
```

## 5. Verifikasi server jalan dengan benar

```bash
curl http://127.0.0.1:5000/health
```
Respons yang diharapkan:
```json
{
  "status": "ok",
  "service": "Face Verification Microservice",
  "timestamp": "2026-07-06T...",
  "dataset": { "total_terdaftar": 0, "tolerance": 0.5 }
}
```

Kalau ini sudah muncul, sisi Laravel otomatis bisa memanggilnya selama `.env` Laravel punya:
```
FACE_RECOGNITION_ENABLED=true
FACE_RECOGNITION_BASE_URL=http://127.0.0.1:5000
FACE_RECOGNITION_API_KEY=test-secret-key-12345
```
(Nilai-nilai ini **sudah ada** di `.env` project ini — tidak perlu diubah untuk pengembangan lokal.)

## 6. Cara kerja & endpoint yang tersedia

Semua endpoint (kecuali `/health`) wajib header `X-API-Key: <FACE_SERVICE_API_KEY>`.

| Endpoint | Method | Kegunaan |
|---|---|---|
| `/health` | GET | Cek server hidup + jumlah wajah terdaftar |
| `/enroll` | POST | Daftarkan wajah referensi guru (1 foto) — dipanggil dari fitur "Daftar Wajah" di app mobile |
| `/verify` | POST | Verifikasi wajah + liveness (kedipan mata) dari video pendek — dipanggil setiap Absen Masuk/Pulang |
| `/enroll/<guru_id>` | DELETE | Hapus wajah referensi guru |

Video absen (~2 detik) dianalisis untuk mendeteksi kedipan mata (bukti "hidup", bukan foto/video diputar ulang), lalu wajah dari frame terbaik dibandingkan dengan foto referensi. Perlu diketahui: **Laravel memakai kebijakan permisif** — kalau layanan ini mati, guru belum enroll wajah, atau wajahnya tidak cocok, absen **tetap tercatat** (tidak diblokir), hasilnya cuma ditandai untuk direview admin. Ini didesain begitu supaya guru tidak pernah gagal absen gara-gara masalah teknis di luar kendalinya.

## 7. Troubleshooting

- **`pip install` gagal di `dlib`** → pastikan langkah 1 (cmake/build tools) sudah benar-benar terpasang, lalu coba lagi. Jangan skip langkah ini.
- **Laravel selalu dapat `layanan_error`** → jalankan `curl http://127.0.0.1:5000/health` dulu untuk pastikan servernya hidup; kalau hidup tapi tetap error, cek `FACE_SERVICE_API_KEY` di Flask sama persis dengan `FACE_RECOGNITION_API_KEY` di `.env` Laravel.
- **Verifikasi selalu `no_blink_detected`** → pastikan video absen berdurasi cukup (~2–3 detik) dan pencahayaan cukup terang; sistem butuh melihat mata berkedip di antara frame.
- **Server jalan tapi restart terus / tertutup saat terminal ditutup** → untuk pemakaian jangka panjang lokal, jalankan lewat `nohup` atau screen/tmux, atau langsung pakai Docker (langkah 4) supaya tetap hidup di background.
