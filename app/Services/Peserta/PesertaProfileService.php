<?php

namespace App\Services\Peserta;

use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Peserta\Peserta;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Peserta\PesertaAlamatRepositoryInterface;
use App\Repositories\Peserta\PesertaOrangTuaRepositoryInterface;
use App\Repositories\Peserta\PesertaPeriodikRepositoryInterface;
use App\Repositories\Peserta\PesertaKontakRepositoryInterface;
use App\Repositories\Peserta\PesertaDokumenPribadiRepositoryInterface;
use App\Services\LogActivityService;

/**
 * PesertaProfileService
 *
 * Service untuk konteks "peserta mengisi datanya sendiri" melalui portal peserta.
 * Berbeda dengan PesertaService (untuk admin), service ini:
 *  - Selalu mengidentifikasi peserta via $userId (bukan $pesertaId) untuk mencegah
 *    peserta mengakses/mengubah data orang lain.
 *  - Membatasi field yang boleh diubah peserta sendiri.
 *  - Menyediakan kemampuan mengukur kelengkapan profil (progress bar).
 *
 * Seluruh operasi write berjalan dalam DB::transaction() untuk atomicity.
 *
 * @package App\Services\Peserta
 */
class PesertaProfileService
{
    /**
     * Field yang BOLEH diupdate oleh peserta sendiri pada data pribadi.
     * Field sensitif (nisn, nik, no_kk) HANYA boleh diubah admin.
     */
    protected const FIELD_BOLEH_DIUPDATE_PESERTA = [
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'kebutuhan_khusus',
        'foto',
        'nisn', // Baru: Boleh diisi jika masih kosong
        'nik',  // Baru: Boleh diisi jika masih kosong
    ];

    /**
     * Field yang DITOLAK — hanya admin yang dapat mengubahnya.
     */
    protected const FIELD_HANYA_ADMIN = [
        'no_kk',
    ];

    /**
     * Tipe orang tua yang valid.
     */
    protected const TIPE_ORANG_TUA_VALID = ['ayah', 'ibu', 'wali'];

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    public function __construct(
        protected PesertaRepositoryInterface $pesertaRepo,
        protected PesertaAlamatRepositoryInterface $alamatRepo,
        protected PesertaOrangTuaRepositoryInterface $orangTuaRepo,
        protected PesertaPeriodikRepositoryInterface $periodikRepo,
        protected PesertaKontakRepositoryInterface $kontakRepo,
        protected PesertaDokumenPribadiRepositoryInterface $dokumenRepo,
        protected LogActivityService $logActivity,
    ) {
    }

    // =========================================================================
    // GET OR CREATE — Idempotent bootstrap profil peserta
    // =========================================================================

    /**
     * Mengambil atau membuat record Peserta beserta seluruh sub-tabel utama.
     *
     * Method ini IDEMPOTENT — aman dipanggil berkali-kali tanpa menghasilkan
     * duplikasi data. Sub-tabel yang di-bootstrap: peserta_alamat, peserta_kontak,
     * peserta_dokumen_pribadi (dengan firstOrCreate agar tidak duplikat).
     * Sub-tabel peserta_orang_tua dan peserta_periodik TIDAK di-inisialisasi di
     * sini karena memerlukan tipe/tahun pelajaran eksplisit.
     *
     * @param  int  $userId  ID user peserta ($user->id_user)
     * @return Peserta        Record peserta dengan semua relasi eager-loaded
     */
    public function getOrCreatePeserta(int $userId): Peserta
    {
        return DB::transaction(function () use ($userId) {
            // 1. Cek apakah sudah ada record peserta untuk user ini
            $peserta = $this->pesertaRepo->findByUserId($userId);

            if (!$peserta) {
                // Pre-fill nama_lengkap dari data user jika ada
                $user = User::find($userId);
                $peserta = $this->pesertaRepo->create([
                    'user_id' => $userId,
                    'nama_lengkap' => $user ? $user->name : 'Peserta Baru',
                ]);

                Log::info("PesertaProfileService: Membuat record Peserta baru untuk user_id={$userId}");
            }

            // 3. Bootstrap sub-tabel dengan firstOrCreate (idempotent)
            // PesertaAlamat
            $this->alamatRepo->upsert($peserta->id, []);

            // PesertaKontak — gunakan pola all()->first() untuk cek + create
            $kontak = $this->kontakRepo->all(['peserta_id' => $peserta->id])->first();
            if (!$kontak) {
                $this->kontakRepo->create(['peserta_id' => $peserta->id]);
            }

            // PesertaDokumenPribadi
            $dokumen = $this->dokumenRepo->all(['peserta_id' => $peserta->id])->first();
            if (!$dokumen) {
                $this->dokumenRepo->create(['peserta_id' => $peserta->id]);
            }

            // 4. Return peserta fresh dengan semua relasi eager-loaded
            return $this->loadFullRelations($userId);
        });
    }

    // =========================================================================
    // GET PROFIL LENGKAP — Data lengkap beserta kelengkapan
    // =========================================================================

    /**
     * Mengambil profil lengkap peserta beserta semua relasi dan persentase kelengkapan.
     *
     * @param  int  $userId  ID user peserta ($user->id_user)
     * @return array{
     *   peserta: Peserta,
     *   alamat: \App\Models\Peserta\PesertaAlamat|null,
     *   orang_tua: array{ayah: mixed, ibu: mixed, wali: mixed},
     *   periodik: \App\Models\Peserta\PesertaPeriodik|null,
     *   kontak: \App\Models\Peserta\PesertaKontak|null,
     *   dokumen_pribadi: \App\Models\Peserta\PesertaDokumenPribadi|null,
     *   kelengkapan: array{persen: int, item_kurang: string[]}
     * }
     *
     * @throws Exception Jika peserta tidak ditemukan
     */
    public function getProfilLengkap(int $userId): array
    {
        $peserta = $this->loadFullRelations($userId);

        $getOrangTua = fn(string $tipe) => $peserta->orangTua->firstWhere('tipe', $tipe);

        return [
            'peserta' => $peserta,
            'alamat' => $peserta->alamat,
            'orang_tua' => [
                'ayah' => $getOrangTua('ayah'),
                'ibu' => $getOrangTua('ibu'),
                'wali' => $getOrangTua('wali'),
            ],
            'periodik' => $peserta->periodik,
            'kontak' => $peserta->kontak,
            'dokumen_pribadi' => $peserta->dokumenPribadi,
            'kelengkapan' => $this->hitungKelengkapan($peserta),
        ];
    }

    // =========================================================================
    // UPDATE PRIBADI — Update data pribadi dengan pembatasan field
    // =========================================================================

    /**
     * Memperbarui data pribadi peserta.
     *
     * Hanya field yang diizinkan yang akan diproses. Field sensitif (nisn, nik, no_kk)
     * akan ditolak dengan Exception jika ditemukan dalam $data.
     * Jika $foto diberikan, file akan diupload ke storage/public/peserta/foto/{userId}.{ext}
     * dan file lama akan dihapus.
     *
     * @param  int               $userId  ID user peserta ($user->id_user)
     * @param  array             $data    Data yang akan diupdate (hanya field yang diizinkan)
     * @param  UploadedFile|null $foto    File foto baru (opsional)
     * @return array{success: bool, message: string, data: Peserta}
     *
     * @throws Exception Jika field terlarang ditemukan atau peserta tidak ada
     */
    public function updatePribadi(int $userId, array $data, ?UploadedFile $foto = null): array
    {
        $peserta = $this->getOrCreatePeserta($userId);

        // Guard: tolak field yang hanya boleh diubah admin
        $fieldTerlarang = array_intersect(array_keys($data), self::FIELD_HANYA_ADMIN);
        if (!empty($fieldTerlarang)) {
            throw new Exception(
                'Field berikut hanya dapat diubah oleh admin: ' . implode(', ', $fieldTerlarang)
            );
        }

        // Logic khusus: NISN & NIK hanya boleh diisi jika record di DB masih kosong
        foreach (['nisn', 'nik'] as $field) {
            if (isset($data[$field]) && !empty($peserta->$field)) {
                // Jika user mencoba mengirim data yang sudah ada isinya
                if ($data[$field] != $peserta->$field) {
                    throw new Exception(
                        ucfirst($field) . " sudah terdaftar dan tidak dapat diubah oleh peserta."
                    );
                }
            }
        }

        return DB::transaction(function () use ($userId, $data, $foto, $peserta) {
            // Filter hanya field yang diizinkan
            $allowedData = array_intersect_key($data, array_flip(self::FIELD_BOLEH_DIUPDATE_PESERTA));

            // Handle upload foto
            if ($foto) {
                // Hapus foto lama jika ada
                if ($peserta->foto && Storage::disk('public')->exists($peserta->foto)) {
                    Storage::disk('public')->delete($peserta->foto);
                }

                // Simpan foto baru dengan nama {userId}.{ext}
                $ext = $foto->getClientOriginalExtension();
                $fotoPath = $foto->storeAs(
                    'peserta/foto',
                    "{$userId}.{$ext}",
                    'public'
                );

                $allowedData['foto'] = $fotoPath;
            }

            // Update via repository jika ada data yang valid
            if (!empty($allowedData)) {
                $this->pesertaRepo->update($peserta->id, $allowedData);
            }

            $this->logActivity->log(
                'Peserta update data pribadi',
                "User ID {$userId} memperbarui data pribadi peserta."
            );

            return [
                'success' => true,
                'message' => 'Data pribadi berhasil diperbarui.',
                'data' => $this->loadFullRelations($userId),
            ];
        });
    }

    // =========================================================================
    // UPDATE ALAMAT
    // =========================================================================

    /**
     * Memperbarui atau membuat data alamat peserta.
     *
     * Menggunakan repository upsert (updateOrCreate berdasarkan peserta_id)
     * sehingga aman dipanggil baik saat pertama kali maupun saat update.
     *
     * @param  int   $userId  ID user peserta ($user->id_user)
     * @param  array $data    Data alamat (alamat, desa_kelurahan, kecamatan, kabupaten_kota, dll)
     * @return array{success: bool, message: string, data: \App\Models\Peserta\PesertaAlamat}
     *
     * @throws Exception Jika peserta tidak ditemukan
     */
    public function updateAlamat(int $userId, array $data): array
    {
        $peserta = $this->getOrCreatePeserta($userId);

        return DB::transaction(function () use ($userId, $peserta, $data) {
            $alamat = $this->alamatRepo->upsert($peserta->id, $data);

            $this->logActivity->log(
                'Peserta update alamat',
                "User ID {$userId} memperbarui data alamat."
            );

            return [
                'success' => true,
                'message' => 'Data alamat berhasil diperbarui.',
                'data' => $alamat,
            ];
        });
    }

    // =========================================================================
    // UPDATE ORANG TUA
    // =========================================================================

    /**
     * Memperbarui atau membuat data orang tua / wali berdasarkan tipe.
     *
     * @param  int    $userId  ID user peserta ($user->id_user)
     * @param  string $tipe    Tipe orang tua: 'ayah', 'ibu', atau 'wali'
     * @param  array  $data    Data orang tua (nama, nik, pekerjaan, penghasilan, dll)
     * @return array{success: bool, message: string, data: \App\Models\Peserta\PesertaOrangTua}
     *
     * @throws Exception Jika tipe tidak valid atau peserta tidak ditemukan
     */
    public function updateOrangTua(int $userId, string $tipe, array $data): array
    {
        if (!in_array($tipe, self::TIPE_ORANG_TUA_VALID, true)) {
            throw new Exception(
                "Tipe orang tua tidak valid: '{$tipe}'. Gunakan: " .
                implode(', ', self::TIPE_ORANG_TUA_VALID)
            );
        }

        $peserta = $this->getOrCreatePeserta($userId);

        return DB::transaction(function () use ($userId, $peserta, $tipe, $data) {
            // Hapus 'tipe' dari $data jika di-pass (akan diset via parameter)
            unset($data['tipe'], $data['peserta_id']);

            $orangTua = $this->orangTuaRepo->upsertByTipe($peserta->id, $tipe, $data);

            $this->logActivity->log(
                'Peserta update orang tua',
                "User ID {$userId} memperbarui data orang tua ({$tipe})."
            );

            return [
                'success' => true,
                'message' => "Data {$tipe} berhasil diperbarui.",
                'data' => $orangTua,
            ];
        });
    }

    // =========================================================================
    // UPDATE PERIODIK
    // =========================================================================

    /**
     * Memperbarui atau membuat data periodik peserta (tinggi badan, berat badan, dll).
     *
     * tahun_pelajaran_id diambil otomatis dari TahunPelajaran yang berstatus aktif.
     * Jika tidak ada tahun pelajaran aktif, data tetap disimpan tanpa tahun_pelajaran_id.
     *
     * @param  int   $userId  ID user peserta ($user->id_user)
     * @param  array $data    Data periodik (tinggi_badan, berat_badan, lingkar_kepala, dll)
     * @return array{success: bool, message: string, data: \App\Models\Peserta\PesertaPeriodik}
     *
     * @throws Exception Jika peserta tidak ditemukan
     */
    public function updatePeriodik(int $userId, array $data): array
    {
        $peserta = $this->getOrCreatePeserta($userId);

        return DB::transaction(function () use ($userId, $peserta, $data) {
            // Ambil tahun pelajaran aktif secara otomatis
            $tahunPelajaran = TahunPelajaran::where('status', 'aktif')->first();
            if ($tahunPelajaran) {
                $data['tahun_pelajaran_id'] = $tahunPelajaran->id;
            }

            // Hapus key yang tidak relevan
            unset($data['peserta_id']);

            // Upsert periodik (cek existing dulu)
            $existing = $this->periodikRepo->all(['peserta_id' => $peserta->id])->first();

            if ($existing) {
                $periodik = $this->periodikRepo->update($existing->id, $data);
            } else {
                $periodik = $this->periodikRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data)
                );
            }

            $this->logActivity->log(
                'Peserta update periodik',
                "User ID {$userId} memperbarui data periodik."
            );

            return [
                'success' => true,
                'message' => 'Data periodik berhasil diperbarui.',
                'data' => $periodik,
            ];
        });
    }

    // =========================================================================
    // UPDATE KONTAK
    // =========================================================================

    /**
     * Memperbarui atau membuat data kontak peserta (no_hp, email).
     *
     * @param  int   $userId  ID user peserta ($user->id_user)
     * @param  array $data    Data kontak (no_hp, email)
     * @return array{success: bool, message: string, data: \App\Models\Peserta\PesertaKontak}
     *
     * @throws Exception Jika peserta tidak ditemukan
     */
    public function updateKontak(int $userId, array $data): array
    {
        $peserta = $this->getOrCreatePeserta($userId);

        return DB::transaction(function () use ($userId, $peserta, $data) {
            unset($data['peserta_id']);

            $existing = $this->kontakRepo->all(['peserta_id' => $peserta->id])->first();

            if ($existing) {
                $kontak = $this->kontakRepo->update($existing->id, $data);
            } else {
                $kontak = $this->kontakRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data)
                );
            }

            $this->logActivity->log(
                'Peserta update kontak',
                "User ID {$userId} memperbarui data kontak."
            );

            return [
                'success' => true,
                'message' => 'Data kontak berhasil diperbarui.',
                'data' => $kontak,
            ];
        });
    }

    // =========================================================================
    // UPDATE DOKUMEN PRIBADI
    // =========================================================================

    /**
     * Memperbarui atau membuat data dokumen pribadi peserta (KIP, PKH, KITAS, paspor).
     *
     * @param  int   $userId  ID user peserta ($user->id_user)
     * @param  array $data    Data dokumen (no_kip, no_pkh, no_kitas, no_paspor)
     * @return array{success: bool, message: string, data: \App\Models\Peserta\PesertaDokumenPribadi}
     *
     * @throws Exception Jika peserta tidak ditemukan
     */
    public function updateDokumenPribadi(int $userId, array $data): array
    {
        $peserta = $this->getOrCreatePeserta($userId);

        return DB::transaction(function () use ($userId, $peserta, $data) {
            unset($data['peserta_id']);

            $existing = $this->dokumenRepo->all(['peserta_id' => $peserta->id])->first();

            if ($existing) {
                $dokumen = $this->dokumenRepo->update($existing->id, $data);
            } else {
                $dokumen = $this->dokumenRepo->create(
                    array_merge(['peserta_id' => $peserta->id], $data)
                );
            }

            $this->logActivity->log(
                'Peserta update dokumen pribadi',
                "User ID {$userId} memperbarui data dokumen pribadi."
            );

            return [
                'success' => true,
                'message' => 'Data dokumen pribadi berhasil diperbarui.',
                'data' => $dokumen,
            ];
        });
    }

    // =========================================================================
    // HITUNG KELENGKAPAN — Kalkulasi progress profil
    // =========================================================================

    /**
     * Menghitung persentase kelengkapan profil peserta.
     *
     * Bobot per kelompok:
     *   - Pribadi  : nama_lengkap, nik, tanggal_lahir, jenis_kelamin  → 30%
     *   - Alamat   : alamat, kabupaten_kota                            → 20%
     *   - Orang Tua: min 1 dari ayah/ibu dengan nama terisi            → 20%
     *   - Kontak   : no_hp                                             → 15%
     *   - Periodik : tinggi_badan, berat_badan                         → 15%
     *
     * @param  Peserta $peserta  Record peserta dengan relasi eager-loaded
     * @return array{persen: int, item_kurang: string[]}
     */
    public function hitungKelengkapan(Peserta $peserta): array
    {
        $itemKurang = [];
        $persenTotal = 0;

        // ── Pribadi (30%) ──────────────────────────────────────────────────
        $fieldPribadi = [
            'nama_lengkap' => 'Nama Lengkap',
            'nik' => 'NIK',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
        ];

        $bobotPribadiPerField = 30 / count($fieldPribadi); // 7.5% per field
        foreach ($fieldPribadi as $field => $label) {
            if (!empty($peserta->$field)) {
                $persenTotal += $bobotPribadiPerField;
            } else {
                $itemKurang[] = $label;
            }
        }

        // ── Alamat (20%) ───────────────────────────────────────────────────
        $fieldAlamat = [
            'alamat' => 'Alamat Lengkap',
            'kabupaten_kota' => 'Kabupaten/Kota',
        ];

        $bobotAlamatPerField = 20 / count($fieldAlamat); // 10% per field
        $alamat = $peserta->alamat;
        foreach ($fieldAlamat as $field => $label) {
            if ($alamat && !empty($alamat->$field)) {
                $persenTotal += $bobotAlamatPerField;
            } else {
                $itemKurang[] = $label;
            }
        }

        // ── Orang Tua (20%) ────────────────────────────────────────────────
        // Cukup min 1 dari ayah atau ibu dengan nama terisi
        $ayah = $peserta->orangTua?->firstWhere('tipe', 'ayah');
        $ibu = $peserta->orangTua?->firstWhere('tipe', 'ibu');

        $adaOrangTua = ($ayah && !empty($ayah->nama)) || ($ibu && !empty($ibu->nama));
        if ($adaOrangTua) {
            $persenTotal += 20;
        } else {
            $itemKurang[] = 'Data Orang Tua (minimal Ayah atau Ibu)';
        }

        // ── Kontak (15%) ───────────────────────────────────────────────────
        $kontak = $peserta->kontak;
        if ($kontak && !empty($kontak->no_hp)) {
            $persenTotal += 15;
        } else {
            $itemKurang[] = 'Nomor HP';
        }

        // ── Periodik (15%) ─────────────────────────────────────────────────
        $fieldPeriodik = [
            'tinggi_badan' => 'Tinggi Badan',
            'berat_badan' => 'Berat Badan',
        ];

        $bobotPeriodikPerField = 15 / count($fieldPeriodik); // 7.5% per field
        $periodik = $peserta->periodik;
        foreach ($fieldPeriodik as $field => $label) {
            if ($periodik && !empty($periodik->$field)) {
                $persenTotal += $bobotPeriodikPerField;
            } else {
                $itemKurang[] = $label;
            }
        }

        return [
            'persen' => (int) round($persenTotal),
            'item_kurang' => $itemKurang,
        ];
    }

    // =========================================================================
    // IS PROFIL CUKUP — Validasi kelayakan untuk mendaftar
    // =========================================================================

    /**
     * Memeriksa apakah profil peserta sudah memenuhi syarat minimum untuk mendaftar.
     *
     * Persyaratan minimum pendaftaran:
     *   - nama_lengkap terisi
     *   - nik terisi
     *   - tanggal_lahir terisi
     *   - kabupaten_kota (alamat) terisi
     *   - no_hp (kontak) terisi
     *   - Minimal 1 orang tua (ayah atau ibu) dengan nama terisi
     */
    public function isProfilCukupUntukDaftar(int $userId): array
    {
        $peserta = $this->pesertaRepo->findByUserId($userId);

        if (!$peserta) {
            return [
                'cukup' => false,
                'kekurangan' => ['Profil Anda belum dibuat. Silakan lengkapi profil terlebih dahulu.'],
                'missing_record' => true,
            ];
        }

        $kekurangan = [];

        // Cek data pribadi minimum
        if (empty($peserta->nama_lengkap)) {
            $kekurangan[] = 'Nama Lengkap belum diisi';
        }
        if (empty($peserta->nik)) {
            $kekurangan[] = 'NIK belum diisi';
        }
        if (empty($peserta->tanggal_lahir)) {
            $kekurangan[] = 'Tanggal Lahir belum diisi';
        }

        // Cek alamat minimum (kabupaten_kota)
        if (!$peserta->alamat || empty($peserta->alamat->kabupaten_kota)) {
            $kekurangan[] = 'Kabupaten/Kota pada data alamat belum diisi';
        }

        // Cek kontak (no_hp)
        if (!$peserta->kontak || empty($peserta->kontak->no_hp)) {
            $kekurangan[] = 'Nomor HP belum diisi';
        }

        // Cek minimal 1 orang tua dengan nama terisi
        $ayah = $peserta->orangTua?->firstWhere('tipe', 'ayah');
        $ibu = $peserta->orangTua?->firstWhere('tipe', 'ibu');

        $adaOrangTua = ($ayah && !empty($ayah->nama)) || ($ibu && !empty($ibu->nama));
        if (!$adaOrangTua) {
            $kekurangan[] = 'Data Orang Tua (minimal Ayah atau Ibu) belum terisi';
        }

        return [
            'cukup' => empty($kekurangan),
            'kekurangan' => $kekurangan,
            'missing_record' => false,
        ];
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Mengambil record Peserta dengan semua relasi eager-loaded berdasarkan userId.
     *
     * Digunakan secara internal oleh semua method yang membutuhkan data peserta lengkap.
     * Pemanggil bertanggung jawab memastikan getOrCreatePeserta() sudah dipanggil
     * sebelumnya agar record selalu ada.
     *
     * @param  int  $userId  ID user peserta ($user->id_user)
     * @return Peserta        Instance Peserta dengan relasi: alamat, orangTua, periodik, kontak, dokumenPribadi
     *
     * @throws Exception Jika peserta dengan userId tersebut tidak ditemukan
     */
    protected function loadFullRelations(int $userId): Peserta
    {
        $peserta = $this->pesertaRepo->findByUserId($userId);

        if (!$peserta) {
            throw new Exception(
                "Peserta untuk user ID {$userId} tidak ditemukan. " .
                "Pastikan getOrCreatePeserta() dipanggil sebelumnya."
            );
        }

        // Eager load semua relasi sekaligus
        $peserta->load([
            'alamat',
            'orangTua',
            'periodik',
            'kontak',
            'dokumenPribadi',
        ]);

        return $peserta;
    }
}
