<?php

namespace App\Services\Ppdb;

use App\Repositories\Ppdb\JalurPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\PembukaanPpdbRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JalurPendaftaranService
{
    public function __construct(
        protected JalurPendaftaranRepositoryInterface $jalurRepo,
        protected PembukaanPpdbRepositoryInterface    $pembukaanRepo,
        protected KuotaJurusanRepositoryInterface     $kuotaRepo,
        protected LogActivityService                  $logActivity,
        protected ResponseService                     $response
    ) {}

    // =========================================================================
    // INDEX — DataTable
    // =========================================================================

    /**
     * Menyediakan Builder query untuk Yajra DataTable jalur pendaftaran.
     *
     * Difilter wajib berdasarkan pembukaan_ppdb_id agar DataTable
     * hanya menampilkan jalur milik satu pembukaan tertentu.
     *
     * @param  int   $pembukaanId  ID pembukaan PPDB
     * @param  array $filters      Filter tambahan opsional
     * @return array               ['success', 'message', 'data' => Builder]
     */
    public function index(int $pembukaanId, array $filters = []): array
    {
        try {
            $filters['pembukaan_ppdb_id'] = $pembukaanId;

            $query = $this->jalurRepo->datatable($filters)
                         ->with('pembukaanPpdb');

            return [
                'success' => true,
                'message' => 'Data jalur pendaftaran berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[JalurPendaftaranService::index] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data jalur pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // STORE
    // =========================================================================

    /**
     * Membuat jalur pendaftaran baru dalam satu pembukaan PPDB.
     *
     * Aturan bisnis:
     * 1. PembukaanPpdb harus berstatus "buka". Jalur tidak bisa ditambahkan
     *    ke pembukaan yang sedang "tutup" karena tidak akan aktif.
     * 2. kode_jalur harus unik dalam satu pembukaan (kombinasi pembukaan_ppdb_id
     *    + kode_jalur harus belum ada di database).
     *
     * @param  array $data    Data tervalidasi (wajib ada: pembukaan_ppdb_id, kode_jalur, nama)
     * @param  int   $userId
     * @return array
     */
    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $pembukaan = $this->pembukaanRepo->findById((int) $data['pembukaan_ppdb_id']);

            if (! $pembukaan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Pembukaan PPDB yang dituju tidak ditemukan.',
                    'data'    => null,
                ];
            }

            // Aturan 1: pembukaan harus berstatus "buka"
            if ($pembukaan->status !== 'buka') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jalur pendaftaran hanya dapat ditambahkan jika Pembukaan PPDB \"{$pembukaan->nama}\" berstatus \"buka\".",
                    'data'    => null,
                ];
            }

            // Aturan 2: kode_jalur unik dalam satu pembukaan
            $duplikat = $this->jalurRepo->all([
                'pembukaan_ppdb_id' => $pembukaan->id,
                'kode_jalur'        => $data['kode_jalur'],
            ]);

            if ($duplikat->isNotEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Kode jalur \"{$data['kode_jalur']}\" sudah digunakan pada pembukaan PPDB ini.",
                    'data'    => null,
                ];
            }

            // Default status "aktif" saat dibuat (sudah dalam pembukaan yang buka)
            $data['status'] = $data['status'] ?? 'aktif';

            $jalur = $this->jalurRepo->create($data);

            $this->logActivity->log(
                'Tambah Jalur Pendaftaran',
                "Menambahkan Jalur Pendaftaran: {$jalur->nama} (Kode: {$jalur->kode_jalur}) pada Pembukaan \"{$pembukaan->nama}\""
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jalur pendaftaran berhasil dibuat.',
                'data'    => $jalur->load('pembukaanPpdb'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JalurPendaftaranService::store] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat jalur pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    /**
     * Menampilkan detail jalur pendaftaran beserta seluruh relasi terkait.
     *
     * Relasi yang di-eager load: jadwal, syarat, formulir (beserta field-nya),
     * biaya registrasi, dan kuota jurusan (beserta jurusan).
     *
     * @param  int $id
     * @return array
     */
    public function show(int $id): array
    {
        try {
            $jalur = $this->jalurRepo->findById($id, [
                'pembukaanPpdb',
                'jadwalPendaftaran',
                'syaratPendaftaran',
                'formulirPendaftaran',
                'formulirPendaftaran.formulirField',
                'biayaRegistrasi',
                'kuotaJurusan',
                'kuotaJurusan.jurusan',
            ]);

            if (! $jalur) {
                return [
                    'success' => false,
                    'message' => "Jalur pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail jalur pendaftaran berhasil diambil.',
                'data'    => $jalur,
            ];
        } catch (Exception $e) {
            Log::error('[JalurPendaftaranService::show] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil detail jalur pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    /**
     * Memperbarui data jalur pendaftaran.
     *
     * Validasi:
     * - Jika kode_jalur diubah, pastikan kode baru masih unik dalam pembukaan yang sama.
     * - Jalur hanya bisa diaktifkan jika PembukaanPpdb-nya berstatus "buka".
     *
     * @param  int   $id
     * @param  array $data
     * @param  int   $userId
     * @return array
     */
    public function update(int $id, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jalur = $this->jalurRepo->findById($id, ['pembukaanPpdb']);

            if (! $jalur) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jalur pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Validasi: jika kode jalur diubah, cek keunikan dalam pembukaan yang sama
            if (isset($data['kode_jalur']) && $data['kode_jalur'] !== $jalur->kode_jalur) {
                $duplikat = $this->jalurRepo->all([
                    'pembukaan_ppdb_id' => $jalur->pembukaan_ppdb_id,
                    'kode_jalur'        => $data['kode_jalur'],
                ]);

                if ($duplikat->isNotEmpty()) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Kode jalur \"{$data['kode_jalur']}\" sudah digunakan pada pembukaan PPDB ini.",
                        'data'    => null,
                    ];
                }
            }

            // Validasi: jalur hanya bisa diaktifkan jika pembukaan berstatus "buka"
            if (isset($data['status']) && $data['status'] === 'aktif') {
                if ($jalur->pembukaanPpdb?->status !== 'buka') {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Jalur tidak dapat diaktifkan karena Pembukaan PPDB \"{$jalur->pembukaanPpdb?->nama}\" tidak sedang berstatus \"buka\".",
                        'data'    => null,
                    ];
                }
            }

            $updated = $this->jalurRepo->update($id, $data);

            $this->logActivity->log(
                'Update Jalur Pendaftaran',
                "Memperbarui data Jalur Pendaftaran: {$updated->nama} (Kode: {$updated->kode_jalur})"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jalur pendaftaran berhasil diperbarui.',
                'data'    => $updated->load('pembukaanPpdb'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JalurPendaftaranService::update] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memperbarui jalur pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    /**
     * Menghapus (soft delete) jalur pendaftaran.
     *
     * Aturan bisnis:
     * - Jalur tidak boleh dihapus jika sudah ada rekord Pendaftaran yang merujuk
     *   ke jalur ini (baik yang masih aktif maupun sudah selesai).
     *   Ini untuk menjaga integritas data historis pendaftar.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jalur = $this->jalurRepo->findById($id, ['pendaftaran']);

            if (! $jalur) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jalur pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Cek apakah sudah ada pendaftaran di jalur ini
            $jumlahPendaftaran = $jalur->pendaftaran->count();

            if ($jumlahPendaftaran > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena sudah memiliki {$jumlahPendaftaran} data pendaftaran. Nonaktifkan jalur ini jika tidak ingin digunakan.",
                    'data'    => null,
                ];
            }

            $nama     = $jalur->nama;
            $kode     = $jalur->kode_jalur;
            $this->jalurRepo->delete($id);

            $this->logActivity->log(
                'Hapus Jalur Pendaftaran',
                "Menghapus Jalur Pendaftaran: {$nama} (Kode: {$kode})"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Jalur pendaftaran \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JalurPendaftaranService::destroy] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus jalur pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // SYNC KUOTA JURUSAN
    // =========================================================================

    /**
     * Sinkronisasi kuota jurusan untuk satu jalur pendaftaran (upsert strategy).
     *
     * Operasi ini bersifat "sync total":
     * - Setiap item dalam $kuotaData di-upsert berdasarkan kombinasi
     *   (jalur_pendaftaran_id + jurusan_id).
     * - Record kuota yang tidak ada dalam $kuotaData TIDAK dihapus
     *   (non-destructive sync) untuk mencegah kehilangan data historis terisi.
     *   Gunakan flag $hapusSisanya = true jika ingin sync destruktif.
     *
     * Format $kuotaData:
     * [
     *   ['jurusan_id' => 1, 'kuota' => 50],
     *   ['jurusan_id' => 2, 'kuota' => 30],
     * ]
     *
     * @param  int   $jalurId    ID jalur pendaftaran
     * @param  array $kuotaData  Array of ['jurusan_id' => int, 'kuota' => int]
     * @param  int   $userId
     * @return array
     */
    public function syncKuotaJurusan(int $jalurId, array $kuotaData, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jalur = $this->jalurRepo->findById($jalurId, ['pembukaanPpdb']);

            if (! $jalur) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jalur pendaftaran dengan ID {$jalurId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Validasi: jalur & pembukaan harus aktif/buka untuk mengubah kuota
            if ($jalur->pembukaanPpdb?->status !== 'buka') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Kuota jurusan tidak dapat diubah karena Pembukaan PPDB \"{$jalur->pembukaanPpdb?->nama}\" tidak sedang berstatus \"buka\".",
                    'data'    => null,
                ];
            }

            $hasil = [];

            foreach ($kuotaData as $item) {
                // Ambil kuota yang sudah ada berdasarkan jalur + jurusan
                $existing = $this->kuotaRepo->all([
                    'jalur_pendaftaran_id' => $jalurId,
                    'jurusan_id'           => $item['jurusan_id'],
                ]);

                if ($existing->isNotEmpty()) {
                    // UPDATE — pertahankan nilai 'terisi' yang sudah ada
                    $kuota  = $existing->first();
                    $hasil[] = $this->kuotaRepo->update($kuota->id, [
                        'kuota' => $item['kuota'],
                        // 'terisi' tidak diubah — hanya berubah lewat pendaftaran
                    ]);
                } else {
                    // CREATE — kuota baru, terisi dimulai dari 0
                    $hasil[] = $this->kuotaRepo->create([
                        'jalur_pendaftaran_id' => $jalurId,
                        'jurusan_id'           => $item['jurusan_id'],
                        'kuota'                => $item['kuota'],
                        'terisi'               => 0,
                    ]);
                }
            }

            $this->logActivity->log(
                'Sync Kuota Jurusan',
                "Menyinkronkan kuota jurusan untuk Jalur Pendaftaran \"{$jalur->nama}\" ({$jalurId}): " . count($hasil) . ' jurusan diproses.'
            );

            DB::commit();

            return [
                'success' => true,
                'message' => count($hasil) . ' kuota jurusan berhasil disinkronkan.',
                'data'    => $hasil,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JalurPendaftaranService::syncKuotaJurusan] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyinkronkan kuota jurusan: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
