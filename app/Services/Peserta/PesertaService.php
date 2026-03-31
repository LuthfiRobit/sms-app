<?php

namespace App\Services\Peserta;

use Exception;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Peserta\PesertaAlamatRepositoryInterface;
use App\Repositories\Peserta\PesertaOrangTuaRepositoryInterface;
use App\Repositories\Peserta\PesertaPeriodikRepositoryInterface;
use App\Repositories\Peserta\PesertaKontakRepositoryInterface;
use App\Repositories\Peserta\PesertaDokumenPribadiRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ResponseService;

/**
 * PesertaService
 *
 * Orkestrator CRUD multi-tabel untuk entitas Peserta sesuai standar Dapodik Kemdikbud.
 * Mengelola 6 tabel secara atomik dalam satu DB::transaction():
 *   1. peserta              — Data pribadi inti
 *   2. peserta_alamat       — Alamat domisili
 *   3. peserta_orang_tua    — Ayah, Ibu, Wali (3 baris per peserta)
 *   4. peserta_periodik     — Data periodik (TB, BB, jarak, saudara)
 *   5. peserta_kontak       — Nomor HP & email
 *   6. peserta_dokumen_pribadi — Nomor KIP, PKH, KK, KITAS, paspor
 */
class PesertaService
{
    /**
     * Header CSV standar Dapodik Kemdikbud.
     * Urutan HARUS dijaga agar kompatibel dengan tool import Kemdikbud.
     */
    protected const DAPODIK_CSV_HEADERS = [
        'NISN',
        'NIK',
        'Nama Lengkap',
        'Jenis Kelamin',
        'Tempat Lahir',
        'Tanggal Lahir',       // Format: DD/MM/YYYY
        'Agama',
        'Alamat',
        'RT',
        'RW',
        'Desa/Kelurahan',
        'Kecamatan',
        'Kab/Kota',
        'Provinsi',
        'Kode Pos',
        'No HP',
        'Email',
        'Nama Ayah',
        'Pekerjaan Ayah',
        'Nama Ibu',
        'Pekerjaan Ibu',
        'Tinggi Badan',
        'Berat Badan',
        'Jarak Rumah',
        'Jumlah Saudara',
    ];

    public function __construct(
        protected PesertaRepositoryInterface             $pesertaRepo,
        protected PesertaAlamatRepositoryInterface       $alamatRepo,
        protected PesertaOrangTuaRepositoryInterface     $orangTuaRepo,
        protected PesertaPeriodikRepositoryInterface     $periodikRepo,
        protected PesertaKontakRepositoryInterface       $kontakRepo,
        protected PesertaDokumenPribadiRepositoryInterface $dokumenRepo,
        protected LogActivityService                     $logActivity,
        protected ResponseService                        $responseService,
    ) {}

    // =========================================================================
    // INDEX — DataTable dengan filter
    // =========================================================================

    /**
     * Mengambil query builder peserta untuk DataTable Yajra.
     *
     * Filter yang didukung:
     *  - nama      : pencarian LIKE pada nama_lengkap (case-insensitive)
     *  - nisn      : pencarian LIKE pada kolom nisn
     *  - kecamatan : join ke peserta_alamat, filter WHERE kecamatan LIKE
     *  - agama     : filter WHERE agama = value (exact match)
     *
     * @param  array  $filters  Associative array filter dari request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function index(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        // Gunakan datatable() dari repository sebagai base query
        $query = $this->pesertaRepo->datatable()
            ->with(['alamat', 'kontak', 'user']);

        // Filter: nama (LIKE case-insensitive)
        if (!empty($filters['nama'])) {
            $query->where('nama_lengkap', 'LIKE', '%' . $filters['nama'] . '%');
        }

        // Filter: nisn
        if (!empty($filters['nisn'])) {
            $query->where('nisn', 'LIKE', '%' . $filters['nisn'] . '%');
        }

        // Filter: kecamatan — join ke tabel peserta_alamat
        if (!empty($filters['kecamatan'])) {
            $query->whereHas('alamat', function ($q) use ($filters) {
                $q->where('kecamatan', 'LIKE', '%' . $filters['kecamatan'] . '%');
            });
        }

        // Filter: agama (exact match, case-insensitive via LOWER)
        if (!empty($filters['agama'])) {
            $query->whereRaw('LOWER(agama) = ?', [strtolower($filters['agama'])]);
        }

        return $query->latest('peserta.created_at');
    }

    // =========================================================================
    // STORE — Buat peserta baru beserta seluruh sub-tabel dalam satu transaksi
    // =========================================================================

    /**
     * Membuat peserta baru beserta seluruh record relasi dalam satu transaksi DB.
     *
     * Struktur $data yang diharapkan:
     * [
     *   'peserta'         => [ nisn, nik, nama_lengkap, jenis_kelamin, ... ],
     *   'alamat'          => [ alamat, rt, rw, desa_kelurahan, kecamatan, ... ],
     *   'orang_tua'       => [
     *                          [ tipe => 'ayah', nama => '...', ... ],
     *                          [ tipe => 'ibu',  nama => '...', ... ],
     *                          [ tipe => 'wali', nama => '...', ... ],
     *                        ],
     *   'periodik'        => [ tinggi_badan, berat_badan, jarak_rumah, ... ],
     *   'kontak'          => [ no_hp, email ],
     *   'dokumen_pribadi' => [ no_kip, no_pkh, no_kitas, no_paspor ],
     * ]
     *
     * @param  array  $data    Payload lengkap dari controller
     * @param  int    $userId  ID user yang melakukan action (untuk log)
     * @return array           Peserta beserta semua relasi
     *
     * @throws \Exception  Jika NISN/NIK duplikat atau terjadi error database
     */
    public function store(array $data, int $userId): array
    {
        // --- Validasi NISN & NIK unik sebelum transaksi ---
        $this->assertNisnUnique($data['peserta']['nisn'] ?? null);
        $this->assertNikUnique($data['peserta']['nik'] ?? null);

        return DB::transaction(function () use ($data, $userId) {
            // 1. Buat record peserta utama
            $peserta = $this->pesertaRepo->create($data['peserta']);

            // 2. Buat peserta_alamat
            if (!empty($data['alamat'])) {
                $this->alamatRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data['alamat'])
                );
            }

            // 3. Buat peserta_orang_tua (ayah, ibu, wali)
            if (!empty($data['orang_tua'])) {
                foreach ($data['orang_tua'] as $orangTua) {
                    $this->orangTuaRepo->create(
                        array_merge(['peserta_id' => $peserta->id], $orangTua)
                    );
                }
            }

            // 4. Buat peserta_periodik
            if (!empty($data['periodik'])) {
                $this->periodikRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data['periodik'])
                );
            }

            // 5. Buat peserta_kontak
            if (!empty($data['kontak'])) {
                $this->kontakRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data['kontak'])
                );
            }

            // 6. Buat peserta_dokumen_pribadi
            if (!empty($data['dokumen_pribadi'])) {
                $this->dokumenRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data['dokumen_pribadi'])
                );
            }

            // Catat aktivitas
            $this->logActivity->log(
                'Create Peserta',
                "Menambah peserta: {$peserta->nama_lengkap} (NISN: {$peserta->nisn})"
            );

            // Return peserta dengan semua relasi eager-loaded
            return $this->loadFullRelations($peserta->id);
        });
    }

    // =========================================================================
    // SHOW — Detail peserta dengan semua relasi
    // =========================================================================

    /**
     * Mengambil detail peserta beserta seluruh relasi (eager loaded).
     *
     * @param  int  $id  Primary key peserta
     * @return array     Peserta dengan relasi: user, alamat, orangTua, periodik, kontak, dokumenPribadi
     *
     * @throws \Exception  Jika peserta tidak ditemukan
     */
    public function show(int $id): array
    {
        return $this->loadFullRelations($id);
    }

    // =========================================================================
    // UPDATE — Update semua sub-tabel dalam satu transaksi
    // =========================================================================

    /**
     * Memperbarui peserta beserta semua record relasi dalam satu transaksi DB.
     *
     * Aturan bisnis:
     * - Jika peserta sudah memiliki user_id (akun terhubung), update HANYA boleh
     *   dilakukan oleh admin/superadmin. Pengecekan permission dilakukan di Controller,
     *   namun method ini menerima flag $isAdmin untuk safety layer kedua.
     * - NISN & NIK harus tetap unik (tidak clash dengan peserta lain).
     *
     * Struktur $data sama dengan store(), semua key bersifat opsional (partial update).
     *
     * @param  int    $id           Primary key peserta
     * @param  array  $data         Payload update dari controller
     * @param  int    $userId       ID user yang melakukan action
     * @param  bool   $isAdmin      Apakah user adalah admin/superadmin
     * @return array                Peserta terupdate dengan semua relasi
     *
     * @throws \Exception  Jika data tidak boleh diedit atau terjadi konflik unik
     */
    public function update(int $id, array $data, int $userId, bool $isAdmin = false): array
    {
        $peserta = $this->pesertaRepo->findById($id);
        if (!$peserta) {
            throw new Exception("Peserta dengan ID {$id} tidak ditemukan.");
        }

        // Guard: peserta yang sudah punya akun user hanya boleh diedit admin
        if ($peserta->user_id && !$isAdmin) {
            throw new Exception(
                "Peserta ini sudah memiliki akun. Hanya admin yang dapat mengedit data."
            );
        }

        // Validasi NISN unik jika ada perubahan
        if (!empty($data['peserta']['nisn']) && $data['peserta']['nisn'] !== $peserta->nisn) {
            $this->assertNisnUnique($data['peserta']['nisn'], $id);
        }

        // Validasi NIK unik jika ada perubahan
        if (!empty($data['peserta']['nik']) && $data['peserta']['nik'] !== $peserta->nik) {
            $this->assertNikUnique($data['peserta']['nik'], $id);
        }

        return DB::transaction(function () use ($id, $data, $peserta) {
            // 1. Update record peserta utama
            if (!empty($data['peserta'])) {
                $this->pesertaRepo->update($id, $data['peserta']);
            }

            // 2. Upsert peserta_alamat (create jika belum ada, update jika sudah ada)
            if (!empty($data['alamat'])) {
                $this->alamatRepo->upsert($id, $data['alamat']);
            }

            // 3. Upsert orang tua per tipe (ayah / ibu / wali)
            if (!empty($data['orang_tua'])) {
                foreach ($data['orang_tua'] as $orangTua) {
                    $tipe = $orangTua['tipe'] ?? null;
                    if ($tipe) {
                        $this->orangTuaRepo->upsertByTipe($id, $tipe, $orangTua);
                    }
                }
            }

            // 4. Upsert peserta_periodik
            if (!empty($data['periodik'])) {
                $existing = $this->periodikRepo->all(['peserta_id' => $id])->first();
                if ($existing) {
                    $this->periodikRepo->update($existing->id, $data['periodik']);
                } else {
                    $this->periodikRepo->create(array_merge(['peserta_id' => $id], $data['periodik']));
                }
            }

            // 5. Upsert peserta_kontak
            if (!empty($data['kontak'])) {
                $existing = $this->kontakRepo->all(['peserta_id' => $id])->first();
                if ($existing) {
                    $this->kontakRepo->update($existing->id, $data['kontak']);
                } else {
                    $this->kontakRepo->create(array_merge(['peserta_id' => $id], $data['kontak']));
                }
            }

            // 6. Upsert peserta_dokumen_pribadi
            if (!empty($data['dokumen_pribadi'])) {
                $existing = $this->dokumenRepo->all(['peserta_id' => $id])->first();
                if ($existing) {
                    $this->dokumenRepo->update($existing->id, $data['dokumen_pribadi']);
                } else {
                    $this->dokumenRepo->create(array_merge(['peserta_id' => $id], $data['dokumen_pribadi']));
                }
            }

            // Refresh peserta untuk mendapatkan nama terbaru di log
            $updated = $this->pesertaRepo->findById($id);

            $this->logActivity->log(
                'Update Peserta',
                "Mengupdate peserta: {$updated->nama_lengkap} (ID: {$id})"
            );

            return $this->loadFullRelations($id);
        });
    }

    // =========================================================================
    // DESTROY — Soft delete peserta (cek tidak ada pendaftaran aktif)
    // =========================================================================

    /**
     * Menghapus peserta dengan soft delete (hanya tabel peserta).
     *
     * Child records (peserta_alamat, dll) mengikuti via ON DELETE CASCADE pada
     * level database, sesuai desain migrasi. Soft delete di tabel peserta
     * membuat seluruh data tetap tersimpan untuk keperluan audit.
     *
     * Pengecekan sebelum hapus:
     * - Tidak boleh ada pendaftaran yang masih aktif (status != 'ditolak' && != 'tidak_lulus')
     *
     * @param  int  $id      Primary key peserta
     * @param  int  $userId  ID user yang melakukan action
     * @return array         Pesan sukses
     *
     * @throws \Exception  Jika ada pendaftaran aktif atau peserta tidak ditemukan
     */
    public function destroy(int $id, int $userId): array
    {
        $peserta = $this->pesertaRepo->findById($id, ['pendaftaran']);
        if (!$peserta) {
            throw new Exception("Peserta dengan ID {$id} tidak ditemukan.");
        }

        // Cek pendaftaran aktif: status selain 'ditolak' dan 'tidak_lulus'
        $statusAkhir = ['ditolak', 'tidak_lulus', 'batal'];
        $pendaftaranAktif = $peserta->pendaftaran
            ->whereNotIn('status', $statusAkhir)
            ->count();

        if ($pendaftaranAktif > 0) {
            throw new Exception(
                "Peserta masih memiliki {$pendaftaranAktif} pendaftaran aktif. " .
                "Selesaikan atau batalkan pendaftaran terlebih dahulu."
            );
        }

        return DB::transaction(function () use ($id, $peserta) {
            $nama = $peserta->nama_lengkap;

            // Soft delete hanya pada tabel peserta
            // Child tables mengikuti via database CASCADE (hard delete child)
            $this->pesertaRepo->delete($id);

            $this->logActivity->log(
                'Delete Peserta',
                "Menghapus peserta: {$nama} (ID: {$id})"
            );

            return ['message' => "Peserta {$nama} berhasil dihapus."];
        });
    }

    // =========================================================================
    // GET DETAIL FOR ADMIN — Format multi-tab untuk tampilan admin
    // =========================================================================

    /**
     * Mengambil dan memformat data peserta untuk tampilan admin multi-tab.
     *
     * Return array dengan key per tab:
     *   - tab_pribadi    : Data identitas inti peserta
     *   - tab_alamat     : Data alamat domisili
     *   - tab_orang_tua  : Data ayah, ibu, wali masing-masing terpisah
     *   - tab_periodik   : Data kesehatan & kondisi fisik
     *   - tab_kontak     : Nomor HP & email
     *   - tab_dokumen    : Nomor dokumen (KIP, PKH, dll)
     *
     * @param  int  $id  Primary key peserta
     * @return array     Data terformat per tab
     *
     * @throws \Exception  Jika peserta tidak ditemukan
     */
    public function getDetailForAdmin(int $id): array
    {
        $peserta = $this->pesertaRepo->findById($id, [
            'user',
            'alamat',
            'orangTua',
            'periodik',
            'kontak',
            'dokumenPribadi',
            'pendaftaran',
        ]);

        if (!$peserta) {
            throw new Exception("Peserta dengan ID {$id} tidak ditemukan.");
        }

        // Helper: format orang tua berdasarkan tipe
        $getOrangTua = fn (string $tipe) => $peserta->orangTua
            ->firstWhere('tipe', $tipe);

        return [
            // --- Tab Pribadi ---
            'tab_pribadi' => [
                'id'               => $peserta->id,
                'user_id'          => $peserta->user_id,
                'akun_terhubung'   => $peserta->user_id
                    ? ($peserta->user->name ?? 'Akun Aktif')
                    : null,
                'nisn'             => $peserta->nisn,
                'nik'              => $peserta->nik,
                'nama_lengkap'     => $peserta->nama_lengkap,
                'jenis_kelamin'    => $peserta->jenis_kelamin,
                'tempat_lahir'     => $peserta->tempat_lahir,
                'tanggal_lahir'    => $peserta->tanggal_lahir?->format('d/m/Y'),
                'agama'            => $peserta->agama,
                'kebutuhan_khusus' => $peserta->kebutuhan_khusus,
                'no_kk'            => $peserta->no_kk,
                'foto'             => $peserta->foto,
                'created_at'       => $peserta->created_at?->format('d/m/Y H:i'),
            ],

            // --- Tab Alamat ---
            'tab_alamat' => $peserta->alamat ? [
                'id'               => $peserta->alamat->id,
                'alamat'           => $peserta->alamat->alamat,
                'rt'               => $peserta->alamat->rt,
                'rw'               => $peserta->alamat->rw,
                'dusun'            => $peserta->alamat->dusun,
                'desa_kelurahan'   => $peserta->alamat->desa_kelurahan,
                'kecamatan'        => $peserta->alamat->kecamatan,
                'kabupaten_kota'   => $peserta->alamat->kabupaten_kota,
                'provinsi'         => $peserta->alamat->provinsi,
                'kode_pos'         => $peserta->alamat->kode_pos,
                'lintang'          => $peserta->alamat->lintang,
                'bujur'            => $peserta->alamat->bujur,
            ] : null,

            // --- Tab Orang Tua ---
            'tab_orang_tua' => [
                'ayah' => $this->formatOrangTua($getOrangTua('ayah')),
                'ibu'  => $this->formatOrangTua($getOrangTua('ibu')),
                'wali' => $this->formatOrangTua($getOrangTua('wali')),
            ],

            // --- Tab Periodik ---
            'tab_periodik' => $peserta->periodik ? [
                'id'             => $peserta->periodik->id,
                'tinggi_badan'   => $peserta->periodik->tinggi_badan,
                'berat_badan'    => $peserta->periodik->berat_badan,
                'lingkar_kepala' => $peserta->periodik->lingkar_kepala,
                'jarak_rumah'    => $peserta->periodik->jarak_rumah,    // dalam km
                'waktu_tempuh'   => $peserta->periodik->waktu_tempuh,   // dalam menit
                'jumlah_saudara' => $peserta->periodik->jumlah_saudara,
                'tahun_pelajaran_id' => $peserta->periodik->tahun_pelajaran_id,
            ] : null,

            // --- Tab Kontak ---
            'tab_kontak' => $peserta->kontak ? [
                'id'     => $peserta->kontak->id,
                'no_hp'  => $peserta->kontak->no_hp,
                'email'  => $peserta->kontak->email,
            ] : null,

            // --- Tab Dokumen ---
            'tab_dokumen' => $peserta->dokumenPribadi ? [
                'id'        => $peserta->dokumenPribadi->id,
                'no_kip'    => $peserta->dokumenPribadi->no_kip,
                'no_pkh'    => $peserta->dokumenPribadi->no_pkh,
                'no_kitas'  => $peserta->dokumenPribadi->no_kitas,
                'no_paspor' => $peserta->dokumenPribadi->no_paspor,
            ] : null,

            // --- Metadata tambahan ---
            'meta' => [
                'total_pendaftaran'  => $peserta->pendaftaran->count(),
                'pendaftaran_aktif'  => $peserta->pendaftaran
                    ->whereNotIn('status', ['ditolak', 'tidak_lulus', 'batal'])
                    ->count(),
            ],
        ];
    }

    // =========================================================================
    // IMPORT FROM CSV — Import data peserta dari file CSV format Dapodik
    // =========================================================================

    /**
     * Mengimpor data peserta dari file CSV berformat Dapodik Kemdikbud.
     *
     * Fitur:
     * - Idempotent: aman dijalankan berkali-kali. Baris yang NISN/NIK-nya sudah
     *   ada di database akan di-skip (tidak update, tidak error fatal).
     * - Setiap baris diproses dalam transaksi terpisah agar baris lain tetap
     *   tersimpan meski ada yang gagal.
     * - Error per baris dicatat dan dikembalikan dalam response.
     *
     * Format CSV yang diharapkan (lihat DAPODIK_CSV_HEADERS):
     * NISN, NIK, Nama Lengkap, Jenis Kelamin, Tempat Lahir, Tanggal Lahir (DD/MM/YYYY),
     * Agama, Alamat, RT, RW, Desa/Kelurahan, Kecamatan, Kab/Kota, Provinsi, Kode Pos,
     * No HP, Email, Nama Ayah, Pekerjaan Ayah, Nama Ibu, Pekerjaan Ibu,
     * Tinggi Badan, Berat Badan, Jarak Rumah, Jumlah Saudara
     *
     * @param  string  $filePath  Path absolut ke file CSV
     * @param  int     $userId    ID user yang melakukan import
     * @return array  [
     *                  'success_count' => int,
     *                  'error_count'   => int,
     *                  'skip_count'    => int,
     *                  'errors'        => [ [ 'row' => int, 'message' => string ], ... ]
     *                ]
     *
     * @throws \Exception  Jika file tidak ditemukan atau tidak bisa dibuka
     */
    public function importFromCsv(string $filePath, int $userId): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("File CSV tidak ditemukan atau tidak dapat dibaca: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Gagal membuka file CSV: {$filePath}");
        }

        $result = [
            'success_count' => 0,
            'error_count'   => 0,
            'skip_count'    => 0,
            'errors'        => [],
        ];

        $rowNumber  = 0;
        $headerRead = false;

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                // Skip baris header
                if (!$headerRead) {
                    $headerRead = true;
                    continue;
                }

                // Skip baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                // Pastikan jumlah kolom sesuai
                if (count($row) < count(self::DAPODIK_CSV_HEADERS)) {
                    $result['error_count']++;
                    $result['errors'][] = [
                        'row'     => $rowNumber,
                        'message' => 'Jumlah kolom tidak sesuai format Dapodik. '
                            . 'Diharapkan ' . count(self::DAPODIK_CSV_HEADERS)
                            . ' kolom, ditemukan ' . count($row) . ' kolom.',
                    ];
                    continue;
                }

                // Map kolom CSV ke variabel (sesuai urutan DAPODIK_CSV_HEADERS)
                [
                    $nisn, $nik, $namaLengkap, $jenisKelamin, $tempatLahir,
                    $tanggalLahir, $agama, $alamat, $rt, $rw,
                    $desaKelurahan, $kecamatan, $kabupatenKota, $provinsi, $kodePos,
                    $noHp, $email, $namaAyah, $pekerjaanAyah, $namaIbu,
                    $pekerjaanIbu, $tinggiBadan, $beratBadan, $jarakRumah, $jumlahSaudara,
                ] = array_map('trim', $row);

                // Idempotency: skip jika NISN atau NIK sudah ada
                if ($nisn && $this->pesertaRepo->findByNisn($nisn)) {
                    $result['skip_count']++;
                    $result['errors'][] = [
                        'row'     => $rowNumber,
                        'message' => "NISN '{$nisn}' sudah terdaftar, baris di-skip.",
                    ];
                    continue;
                }

                if ($nik && $this->pesertaRepo->findByNik($nik)) {
                    $result['skip_count']++;
                    $result['errors'][] = [
                        'row'     => $rowNumber,
                        'message' => "NIK '{$nik}' sudah terdaftar, baris di-skip.",
                    ];
                    continue;
                }

                // Parse tanggal lahir dari format DD/MM/YYYY ke Y-m-d
                $tanggalLahirParsed = null;
                if ($tanggalLahir) {
                    $parts = explode('/', $tanggalLahir);
                    if (count($parts) === 3) {
                        $tanggalLahirParsed = sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
                    }
                }

                // Proses import satu baris dalam transaksi terpisah
                try {
                    DB::transaction(function () use (
                        $nisn, $nik, $namaLengkap, $jenisKelamin, $tempatLahir,
                        $tanggalLahirParsed, $agama, $alamat, $rt, $rw,
                        $desaKelurahan, $kecamatan, $kabupatenKota, $provinsi, $kodePos,
                        $noHp, $email, $namaAyah, $pekerjaanAyah, $namaIbu,
                        $pekerjaanIbu, $tinggiBadan, $beratBadan, $jarakRumah, $jumlahSaudara
                    ) {
                        // 1. Peserta utama
                        $peserta = $this->pesertaRepo->create([
                            'nisn'          => $nisn ?: null,
                            'nik'           => $nik ?: null,
                            'nama_lengkap'  => $namaLengkap,
                            'jenis_kelamin' => strtolower($jenisKelamin) === 'l'
                                ? 'L'
                                : (strtolower($jenisKelamin) === 'p' ? 'P' : $jenisKelamin),
                            'tempat_lahir'  => $tempatLahir,
                            'tanggal_lahir' => $tanggalLahirParsed,
                            'agama'         => ucfirst(strtolower($agama)),
                        ]);

                        // 2. Alamat
                        $this->alamatRepo->create([
                            'peserta_id'     => $peserta->id,
                            'alamat'         => $alamat,
                            'rt'             => $rt,
                            'rw'             => $rw,
                            'desa_kelurahan' => $desaKelurahan,
                            'kecamatan'      => $kecamatan,
                            'kabupaten_kota' => $kabupatenKota,
                            'provinsi'       => $provinsi,
                            'kode_pos'       => $kodePos,
                        ]);

                        // 3. Orang tua — ayah
                        if ($namaAyah) {
                            $this->orangTuaRepo->create([
                                'peserta_id' => $peserta->id,
                                'tipe'       => 'ayah',
                                'nama'       => $namaAyah,
                                'pekerjaan'  => $pekerjaanAyah,
                            ]);
                        }

                        // 4. Orang tua — ibu
                        if ($namaIbu) {
                            $this->orangTuaRepo->create([
                                'peserta_id' => $peserta->id,
                                'tipe'       => 'ibu',
                                'nama'       => $namaIbu,
                                'pekerjaan'  => $pekerjaanIbu,
                            ]);
                        }

                        // 5. Periodik
                        $this->periodikRepo->create([
                            'peserta_id'     => $peserta->id,
                            'tinggi_badan'   => is_numeric($tinggiBadan) ? (float) $tinggiBadan : null,
                            'berat_badan'    => is_numeric($beratBadan)  ? (float) $beratBadan  : null,
                            'jarak_rumah'    => is_numeric($jarakRumah)  ? (float) $jarakRumah  : null,
                            'jumlah_saudara' => is_numeric($jumlahSaudara) ? (int) $jumlahSaudara : null,
                        ]);

                        // 6. Kontak
                        if ($noHp || $email) {
                            $this->kontakRepo->create([
                                'peserta_id' => $peserta->id,
                                'no_hp'      => $noHp,
                                'email'      => $email,
                            ]);
                        }
                    });

                    $result['success_count']++;
                } catch (Throwable $e) {
                    // Satu baris gagal → catat, lanjutkan ke baris berikutnya
                    $result['error_count']++;
                    $result['errors'][] = [
                        'row'     => $rowNumber,
                        'message' => $e->getMessage(),
                    ];

                    Log::warning("PesertaService::importFromCsv — Baris {$rowNumber} gagal: " . $e->getMessage());
                }
            }
        } finally {
            fclose($handle);
        }

        // Log ringkasan import
        $this->logActivity->log(
            'Import CSV Peserta',
            "Import CSV selesai: {$result['success_count']} berhasil, "
            . "{$result['skip_count']} di-skip, {$result['error_count']} error."
        );

        return $result;
    }

    // =========================================================================
    // EXPORT TO CSV — Generate file CSV format Dapodik
    // =========================================================================

    /**
     * Mengekspor data peserta ke file CSV berformat Dapodik Kemdikbud.
     *
     * File CSV disimpan di storage/app/exports/peserta/ dengan nama unik berbasis timestamp.
     * Gunakan PHP native fputcsv untuk kompatibilitas maksimal tanpa dependency tambahan.
     *
     * Filter yang didukung sama dengan method index().
     *
     * @param  array   $filters  Filter yang sama dengan method index()
     * @return string            Path absolut ke file CSV yang dihasilkan
     *
     * @throws \Exception  Jika direktori export tidak dapat dibuat atau file tidak dapat ditulis
     */
    public function exportToCsv(array $filters = []): string
    {
        // Tentukan direktori & nama file
        $exportDir  = storage_path('app/exports/peserta');
        $filename   = 'peserta_dapodik_' . now()->format('Ymd_His') . '.csv';
        $filePath   = $exportDir . DIRECTORY_SEPARATOR . $filename;

        // Buat direktori jika belum ada
        if (!is_dir($exportDir) && !mkdir($exportDir, 0755, true)) {
            throw new Exception("Gagal membuat direktori export: {$exportDir}");
        }

        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new Exception("Gagal membuat file CSV: {$filePath}");
        }

        try {
            // Tulis BOM UTF-8 agar Excel membaca encoding dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            // Tulis baris header
            fputcsv($handle, self::DAPODIK_CSV_HEADERS);

            // Ambil data dengan eager loading semua relasi yang dibutuhkan
            $query = $this->index($filters);
            $query->with(['alamat', 'orangTua', 'periodik', 'kontak'])
                ->chunk(500, function ($pesertaChunk) use ($handle) {
                    foreach ($pesertaChunk as $peserta) {
                        $alamat  = $peserta->alamat;
                        $periodik = $peserta->periodik;
                        $kontak  = $peserta->kontak;

                        $ayah = $peserta->orangTua->firstWhere('tipe', 'ayah');
                        $ibu  = $peserta->orangTua->firstWhere('tipe', 'ibu');

                        fputcsv($handle, [
                            $peserta->nisn                                   ?? '',
                            $peserta->nik                                    ?? '',
                            $peserta->nama_lengkap                           ?? '',
                            $peserta->jenis_kelamin                          ?? '',
                            $peserta->tempat_lahir                           ?? '',
                            $peserta->tanggal_lahir?->format('d/m/Y')       ?? '', // DD/MM/YYYY
                            $peserta->agama                                  ?? '',
                            $alamat?->alamat                                 ?? '',
                            $alamat?->rt                                     ?? '',
                            $alamat?->rw                                     ?? '',
                            $alamat?->desa_kelurahan                         ?? '',
                            $alamat?->kecamatan                              ?? '',
                            $alamat?->kabupaten_kota                         ?? '',
                            $alamat?->provinsi                               ?? '',
                            $alamat?->kode_pos                               ?? '',
                            $kontak?->no_hp                                  ?? '',
                            $kontak?->email                                  ?? '',
                            $ayah?->nama                                     ?? '',
                            $ayah?->pekerjaan                                ?? '',
                            $ibu?->nama                                      ?? '',
                            $ibu?->pekerjaan                                 ?? '',
                            $periodik?->tinggi_badan                         ?? '',
                            $periodik?->berat_badan                          ?? '',
                            $periodik?->jarak_rumah                          ?? '',
                            $periodik?->jumlah_saudara                       ?? '',
                        ]);
                    }
                });
        } finally {
            fclose($handle);
        }

        $this->logActivity->log(
            'Export CSV Peserta',
            "Export CSV peserta berhasil: {$filename}"
        );

        return $filePath;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Muat semua relasi peserta dan kembalikan sebagai array.
     *
     * @param  int  $pesertaId
     * @return array
     *
     * @throws \Exception  Jika peserta tidak ditemukan
     */
    private function loadFullRelations(int $pesertaId): array
    {
        $peserta = $this->pesertaRepo->findById($pesertaId, [
            'user',
            'alamat',
            'orangTua',
            'periodik',
            'kontak',
            'dokumenPribadi',
        ]);

        if (!$peserta) {
            throw new Exception("Peserta dengan ID {$pesertaId} tidak ditemukan.");
        }

        return $peserta->toArray();
    }

    /**
     * Validasi bahwa NISN belum digunakan oleh peserta lain.
     *
     * @param  string|null  $nisn       NISN yang akan dicek
     * @param  int|null     $exceptId   Kecualikan ID ini (untuk use case update)
     *
     * @throws \Exception  Jika NISN sudah terpakai
     */
    private function assertNisnUnique(?string $nisn, ?int $exceptId = null): void
    {
        if (empty($nisn)) {
            return; // NISN opsional, skip validasi jika kosong
        }

        $existing = $this->pesertaRepo->findByNisn($nisn);

        if ($existing && $existing->id !== $exceptId) {
            throw new Exception(
                "NISN '{$nisn}' sudah terdaftar atas nama: {$existing->nama_lengkap}."
            );
        }
    }

    /**
     * Validasi bahwa NIK belum digunakan oleh peserta lain.
     *
     * @param  string|null  $nik        NIK yang akan dicek
     * @param  int|null     $exceptId   Kecualikan ID ini (untuk use case update)
     *
     * @throws \Exception  Jika NIK sudah terpakai
     */
    private function assertNikUnique(?string $nik, ?int $exceptId = null): void
    {
        if (empty($nik)) {
            return; // NIK opsional, skip validasi jika kosong
        }

        $existing = $this->pesertaRepo->findByNik($nik);

        if ($existing && $existing->id !== $exceptId) {
            throw new Exception(
                "NIK '{$nik}' sudah terdaftar atas nama: {$existing->nama_lengkap}."
            );
        }
    }

    /**
     * Format satu record orang tua menjadi array (null-safe).
     *
     * @param  \App\Models\Peserta\PesertaOrangTua|null  $orangTua
     * @return array|null
     */
    private function formatOrangTua($orangTua): ?array
    {
        if (!$orangTua) {
            return null;
        }

        return [
            'id'               => $orangTua->id,
            'tipe'             => $orangTua->tipe,
            'nama'             => $orangTua->nama,
            'nik'              => $orangTua->nik,
            'pekerjaan'        => $orangTua->pekerjaan,
            'penghasilan'      => $orangTua->penghasilan,
            'pendidikan'       => $orangTua->pendidikan,
            'kebutuhan_khusus' => $orangTua->kebutuhan_khusus,
            'no_hp'            => $orangTua->no_hp,
        ];
    }
}
