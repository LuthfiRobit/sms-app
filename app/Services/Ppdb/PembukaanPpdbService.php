<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\BiayaRegistrasi;
use App\Models\Ppdb\FormulirField;
use App\Models\Ppdb\FormulirPendaftaran;
use App\Models\Ppdb\JadwalPendaftaran;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\Ppdb\SyaratPendaftaran;
use App\Repositories\Ppdb\PembukaanPpdbRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembukaanPpdbService
{
    public function __construct(
        protected PembukaanPpdbRepositoryInterface $pembukaanRepo,
        protected LogActivityService               $logActivity,
        protected ResponseService                  $response
    ) {}

    // =========================================================================
    // INDEX — DataTable
    // =========================================================================

    /**
     * Menyediakan data untuk Yajra DataTable pembukaan PPDB.
     *
     * Mengembalikan Builder query (dipakai oleh DataTables::of() di Controller)
     * dibungkus dalam array standar ResponseService agar konsisten.
     *
     * @param  array $filters  Kolom-filter opsional, mis. ['tahun_pelajaran_id' => 1]
     * @return array           ['success', 'message', 'data' => Builder]
     */
    public function index(array $filters = []): array
    {
        try {
            $query = $this->pembukaanRepo->datatable($filters)
                         ->with('tahunPelajaran');   // eager untuk kolom nama tahun

            return [
                'success' => true,
                'message' => 'Data pembukaan PPDB berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[PembukaanPpdbService::index] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data pembukaan PPDB.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // STORE
    // =========================================================================

    /**
     * Membuat pembukaan PPDB baru.
     *
     * Aturan bisnis:
     * - Satu tahun pelajaran hanya boleh memiliki SATU pembukaan dengan status "buka"
     *   pada waktu yang sama. Namun pada saat create, status default adalah "tutup",
     *   sehingga validasi duplikasi buka dilakukan via toggleStatus.
     * - Validasi duplikasi per tahun_pelajaran (boleh lebih dari satu rekord "tutup",
     *   tapi nama/periode harus berbeda — biarkan Controller / FormRequest menangani).
     *
     * @param  array $data    Data tervalidasi dari Controller (FormRequest)
     * @param  int   $userId  ID user yang melakukan aksi (untuk log)
     * @return array
     */
    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Paksa status 'tutup' saat pertama dibuat agar tidak langsung buka
            // tanpa melalui proses validasi one-active-at-a-time
            $data['status'] = $data['status'] ?? 'tutup';

            // Jika ternyata request ingin langsung buka, periksa dulu
            if ($data['status'] === 'buka') {
                $konflik = $this->pembukaanRepo->all([
                    'lembaga_id'         => $data['lembaga_id'],
                    'tahun_pelajaran_id' => $data['tahun_pelajaran_id'],
                    'status'             => 'buka',
                ]);

                if ($konflik->isNotEmpty()) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Lembaga ini sudah memiliki pembukaan PPDB yang berstatus "buka" pada tahun pelajaran yang sama. Tutup pembukaan tersebut terlebih dahulu.',
                        'data'    => null,
                    ];
                }
            }

            $pembukaan = $this->pembukaanRepo->create($data);

            $this->logActivity->log(
                'Membuka PPDB',
                "Membuka PPDB: {$pembukaan->nama} (Tahun Pelajaran ID: {$pembukaan->tahun_pelajaran_id})"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pembukaan PPDB berhasil dibuat.',
                'data'    => $pembukaan->load('tahunPelajaran'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PembukaanPpdbService::store] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat pembukaan PPDB: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    /**
     * Menampilkan detail satu pembukaan PPDB beserta relasi jalur pendaftaran.
     *
     * @param  int   $id
     * @return array
     */
    public function show(int $id): array
    {
        try {
            $pembukaan = $this->pembukaanRepo->findById($id, [
                'lembaga',
                'tahunPelajaran',
                'jalurPendaftaran',
                'jalurPendaftaran.jadwalPendaftaran',
            ]);

            if (! $pembukaan) {
                return [
                    'success' => false,
                    'message' => "Pembukaan PPDB dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail pembukaan PPDB berhasil diambil.',
                'data'    => $pembukaan,
            ];
        } catch (Exception $e) {
            Log::error('[PembukaanPpdbService::show] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil detail pembukaan PPDB.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    /**
     * Memperbarui data pembukaan PPDB.
     *
     * Catatan: perubahan status "buka/tutup" sebaiknya via toggleStatus().
     * Jika field status dikirim & hendak diubah ke "buka", validasi tetap dijalankan.
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
            $pembukaan = $this->pembukaanRepo->findById($id);

            if (! $pembukaan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Pembukaan PPDB dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Validasi bisnis: jika mengubah status jadi "buka" lewat update
            if (isset($data['status']) && $data['status'] === 'buka' && $pembukaan->status !== 'buka') {
                $konflik = $this->pembukaanRepo->all([
                    'lembaga_id'         => $pembukaan->lembaga_id,
                    'tahun_pelajaran_id' => $pembukaan->tahun_pelajaran_id,
                    'status'             => 'buka',
                ]);

                if ($konflik->isNotEmpty()) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Sudah ada pembukaan PPDB yang berstatus "buka" pada tahun pelajaran yang sama.',
                        'data'    => null,
                    ];
                }
            }

            $updated = $this->pembukaanRepo->update($id, $data);

            $this->logActivity->log(
                'Update Pembukaan PPDB',
                "Memperbarui data Pembukaan PPDB: {$updated->nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pembukaan PPDB berhasil diperbarui.',
                'data'    => $updated->load('tahunPelajaran'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PembukaanPpdbService::update] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memperbarui pembukaan PPDB: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    /**
     * Menghapus (soft delete) pembukaan PPDB.
     *
     * Aturan bisnis:
     * - Pembukaan yang memiliki jalur pendaftaran AKTIF tidak boleh dihapus.
     *   Admin harus menonaktifkan semua jalur terlebih dahulu.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $pembukaan = $this->pembukaanRepo->findById($id, ['jalurPendaftaran']);

            if (! $pembukaan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Pembukaan PPDB dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Cek apakah ada jalur yang masih aktif
            $jalurAktif = $pembukaan->jalurPendaftaran->where('status', 'aktif');

            if ($jalurAktif->isNotEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Pembukaan PPDB tidak dapat dihapus karena masih memiliki {$jalurAktif->count()} jalur pendaftaran yang aktif. Nonaktifkan terlebih dahulu.",
                    'data'    => null,
                ];
            }

            $nama = $pembukaan->nama;
            $this->pembukaanRepo->delete($id);

            $this->logActivity->log(
                'Hapus Pembukaan PPDB',
                "Menghapus Pembukaan PPDB: {$nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Pembukaan PPDB \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PembukaanPpdbService::destroy] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus pembukaan PPDB: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // TOGGLE STATUS
    // =========================================================================

    /**
     * Mengubah status pembukaan PPDB antara "buka" dan "tutup".
     *
     * Aturan bisnis (one-active-at-a-time):
     * - Hanya boleh ada SATU pembukaan dengan status "buka" dalam satu tahun pelajaran
     *   pada waktu yang sama.
     * - Jika status saat ini "tutup" dan hendak dibuka, sistem akan mencari pembukaan
     *   lain yang sedang "buka" di tahun pelajaran yang sama. Jika ditemukan, operasi
     *   ditolak dengan pesan informatif.
     * - Jika status saat ini "buka", maka langsung ditutup tanpa validasi tambahan.
     * - Saat menutup pembukaan, seluruh jalur pendaftaran yang aktif di dalamnya
     *   TIDAK otomatis dinonaktifkan (kebijakan UI — admin harus menutup jalur manual).
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function toggleStatus(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $pembukaan = $this->pembukaanRepo->findById($id, ['tahunPelajaran']);

            if (! $pembukaan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Pembukaan PPDB dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $statusLama = $pembukaan->status;
            $statusBaru = $statusLama === 'buka' ? 'tutup' : 'buka';

            // Validasi one-active-at-a-time per lembaga hanya saat HENDAK membuka
            if ($statusBaru === 'buka') {
                $konflik = $this->pembukaanRepo->all([
                    'lembaga_id'         => $pembukaan->lembaga_id,
                    'tahun_pelajaran_id' => $pembukaan->tahun_pelajaran_id,
                    'status'             => 'buka',
                ])->where('id', '!=', $id);

                if ($konflik->isNotEmpty()) {
                    $konflikNama = $konflik->first()->nama;
                    DB::rollBack();

                    return [
                        'success' => false,
                        'message' => "Tidak dapat membuka PPDB \"{$pembukaan->nama}\" karena \"{$konflikNama}\" sedang berstatus buka pada lembaga yang sama. Tutup pembukaan tersebut terlebih dahulu.",
                        'data'    => null,
                    ];
                }
            }

            // Lakukan toggle langsung pada model (tanpa memanggil repository toggleStatus
            // yang tidak mengembalikan model ter-update)
            $this->pembukaanRepo->update($id, ['status' => $statusBaru]);

            $this->logActivity->log(
                'Toggle Status Pembukaan PPDB',
                "Mengubah status Pembukaan PPDB \"{$pembukaan->nama}\" dari \"{$statusLama}\" menjadi \"{$statusBaru}\""
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Status Pembukaan PPDB \"{$pembukaan->nama}\" berhasil diubah menjadi \"{$statusBaru}\".",
                'data'    => $this->pembukaanRepo->findById($id, ['lembaga', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PembukaanPpdbService::toggleStatus] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengubah status pembukaan PPDB: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // DUPLIKASI
    // =========================================================================

    /**
     * Menduplikasi seluruh konfigurasi pembukaan PPDB ke lembaga lain.
     *
     * Yang di-clone: pembukaan → jalur → jadwal, syarat, formulir+field, biaya.
     * Yang tidak di-clone: kuota_jurusan (jurusan berbeda antar lembaga).
     *
     * Jadwal di-shift secara proporsional berdasarkan selisih tanggal mulai
     * antara sumber dan target agar timeline tetap masuk akal.
     */
    public function duplikasi(int $sourceId, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Load source dengan semua relasi yang perlu di-clone
            $source = $this->pembukaanRepo->findById($sourceId, [
                'jalurPendaftaran',
                'jalurPendaftaran.jadwalPendaftaran',
                'jalurPendaftaran.syaratPendaftaran',
                'jalurPendaftaran.formulirPendaftaran.formulirField',
                'jalurPendaftaran.biayaRegistrasi',
            ]);

            if (! $source) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Pembukaan sumber tidak ditemukan.', 'data' => null];
            }

            // Hitung offset hari untuk shift jadwal
            $sourceMulai = Carbon::parse($source->mulai);
            $targetMulai = Carbon::parse($data['mulai']);
            $offsetHari  = $sourceMulai->diffInDays($targetMulai, false);

            // Cek tidak ada konflik: target lembaga + TA yang sama sudah buka
            if (($data['status'] ?? 'tutup') === 'buka') {
                $konflik = $this->pembukaanRepo->all([
                    'lembaga_id'         => $data['lembaga_id'],
                    'tahun_pelajaran_id' => $data['tahun_pelajaran_id'],
                    'status'             => 'buka',
                ]);
                if ($konflik->isNotEmpty()) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Lembaga tujuan sudah memiliki pembukaan aktif pada tahun pelajaran yang sama.', 'data' => null];
                }
            }

            // 1. Buat pembukaan baru
            $newPembukaan = $this->pembukaanRepo->create([
                'lembaga_id'         => $data['lembaga_id'],
                'tahun_pelajaran_id' => $data['tahun_pelajaran_id'],
                'nama'               => $data['nama'],
                'deskripsi'          => $source->deskripsi,
                'mulai'              => $data['mulai'],
                'selesai'            => $data['selesai'],
                'status'             => $data['status'] ?? 'tutup',
            ]);

            $jalurCount    = 0;
            $jadwalCount   = 0;
            $syaratCount   = 0;
            $formulirCount = 0;
            $biayaCount    = 0;

            // 2. Clone setiap jalur
            foreach ($source->jalurPendaftaran as $jalur) {
                $newJalur = JalurPendaftaran::create([
                    'pembukaan_ppdb_id' => $newPembukaan->id,
                    'kode_jalur'        => $jalur->kode_jalur,
                    'nama'              => $jalur->nama,
                    'deskripsi'         => $jalur->deskripsi,
                    'kuota'             => $jalur->kuota,
                    'urutan'            => $jalur->urutan,
                    'status'            => $jalur->status,
                ]);
                $jalurCount++;

                // 2a. Clone jadwal — geser tanggal sesuai offset
                foreach ($jalur->jadwalPendaftaran as $jadwal) {
                    JadwalPendaftaran::create([
                        'jalur_pendaftaran_id' => $newJalur->id,
                        'nama'                 => $jadwal->nama,
                        'tipe'                 => $jadwal->tipe,
                        'mulai'                => Carbon::parse($jadwal->mulai)->addDays($offsetHari),
                        'selesai'              => Carbon::parse($jadwal->selesai)->addDays($offsetHari),
                        'status'               => $jadwal->status,
                        'keterangan'           => $jadwal->keterangan,
                    ]);
                    $jadwalCount++;
                }

                // 2b. Clone syarat
                foreach ($jalur->syaratPendaftaran as $syarat) {
                    SyaratPendaftaran::create([
                        'jalur_pendaftaran_id' => $newJalur->id,
                        'tahun_pelajaran_id'   => $data['tahun_pelajaran_id'],
                        'nama'                 => $syarat->nama,
                        'tipe'                 => $syarat->tipe,
                        'wajib'                => $syarat->wajib,
                        'keterangan'           => $syarat->keterangan,
                        'urutan'               => $syarat->urutan,
                    ]);
                    $syaratCount++;
                }

                // 2c. Clone formulir + field
                foreach ($jalur->formulirPendaftaran as $formulir) {
                    $newFormulir = FormulirPendaftaran::create([
                        'jalur_pendaftaran_id' => $newJalur->id,
                        'tahun_pelajaran_id'   => $data['tahun_pelajaran_id'],
                        'nama'                 => $formulir->nama,
                        'deskripsi'            => $formulir->deskripsi,
                        'tipe'                 => $formulir->tipe,
                        'is_aktif'             => $formulir->is_aktif,
                    ]);
                    $formulirCount++;

                    foreach ($formulir->formulirField as $field) {
                        FormulirField::create([
                            'formulir_pendaftaran_id' => $newFormulir->id,
                            'kode_field'              => $field->kode_field,
                            'label'                   => $field->label,
                            'tipe_field'              => $field->tipe_field,
                            'is_required'             => $field->is_required,
                            'is_statis'               => $field->is_statis,
                            'dapodik_key'             => $field->dapodik_key,
                            'urutan'                  => $field->urutan,
                            'opsi'                    => $field->opsi,
                        ]);
                    }
                }

                // 2d. Clone biaya
                foreach ($jalur->biayaRegistrasi as $biaya) {
                    BiayaRegistrasi::create([
                        'jalur_pendaftaran_id' => $newJalur->id,
                        'tahun_pelajaran_id'   => $data['tahun_pelajaran_id'],
                        'nama'                 => $biaya->nama,
                        'nominal'              => $biaya->nominal,
                        'deskripsi'            => $biaya->deskripsi,
                        'is_aktif'             => $biaya->is_aktif,
                    ]);
                    $biayaCount++;
                }
            }

            $this->logActivity->log(
                'Duplikasi Pembukaan PPDB',
                "Menduplikasi \"{$source->nama}\" → \"{$newPembukaan->nama}\" (lembaga_id: {$data['lembaga_id']}). " .
                "{$jalurCount} jalur, {$jadwalCount} jadwal, {$syaratCount} syarat, {$formulirCount} formulir, {$biayaCount} biaya."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Berhasil menduplikasi ke \"{$newPembukaan->nama}\" — {$jalurCount} jalur, {$jadwalCount} jadwal, {$syaratCount} syarat, {$biayaCount} biaya disalin. Kuota jurusan perlu diisi manual.",
                'data'    => $newPembukaan->load(['lembaga', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PembukaanPpdbService::duplikasi] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menduplikasi: ' . $e->getMessage(), 'data' => null];
        }
    }
}
