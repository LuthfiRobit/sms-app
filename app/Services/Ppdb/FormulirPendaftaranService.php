<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\FormulirField;
use App\Models\Ppdb\FormulirPendaftaran;
use App\Models\Transaksi\Pendaftaran;
use App\Models\Transaksi\PendaftaranFieldValue;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FormulirPendaftaranService
{
    // =========================================================================
    // KONSTANTA — Mapping Dapodik Key ke properti Model Peserta dan relasi tabel
    // =========================================================================

    /**
     * Daftar lengkap field statis yang tersedia untuk di-mapping ke data Dapodik.
     *
     * Format setiap nilai:
     *  - label  : Label ramah pengguna yang tampil di form
     *  - tipe   : Tipe HTML input (text, date, select, textarea, number)
     *  - table  : Tabel Eloquent tempat data diambil (peserta, peserta_alamat, dll.)
     *  - opsi   : (opsional) Array opsi default untuk tipe select/radio
     *
     * @var array<string, array{label: string, tipe: string, table: string, opsi?: array}>
     */
    public const DAPODIK_FIELDS = [
        // --- Tabel: peserta ---
        'nama_lengkap'      => ['label' => 'Nama Lengkap',          'tipe' => 'text',     'table' => 'peserta'],
        'nisn'              => ['label' => 'NISN',                   'tipe' => 'text',     'table' => 'peserta'],
        'nik'               => ['label' => 'NIK',                    'tipe' => 'text',     'table' => 'peserta'],
        'tempat_lahir'      => ['label' => 'Tempat Lahir',           'tipe' => 'text',     'table' => 'peserta'],
        'tanggal_lahir'     => ['label' => 'Tanggal Lahir',          'tipe' => 'date',     'table' => 'peserta'],
        'jenis_kelamin'     => ['label' => 'Jenis Kelamin',          'tipe' => 'select',   'table' => 'peserta',
                                'opsi'  => ['Laki-laki', 'Perempuan']],
        'agama'             => ['label' => 'Agama',                  'tipe' => 'select',   'table' => 'peserta',
                                'opsi'  => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']],
        'kebutuhan_khusus'  => ['label' => 'Kebutuhan Khusus',       'tipe' => 'select',   'table' => 'peserta',
                                'opsi'  => ['Tidak Ada', 'Tuna Netra', 'Tuna Rungu', 'Tuna Grahita', 'Tuna Daksa', 'Tuna Laras', 'Tuna Ganda']],
        'no_kk'             => ['label' => 'No. Kartu Keluarga',     'tipe' => 'text',     'table' => 'peserta'],
        'foto'              => ['label' => 'Foto Peserta',            'tipe' => 'file',     'table' => 'peserta'],

        // --- Tabel: peserta_alamat ---
        'alamat'            => ['label' => 'Alamat Lengkap',          'tipe' => 'textarea', 'table' => 'peserta_alamat'],
        'desa_kelurahan'    => ['label' => 'Desa / Kelurahan',         'tipe' => 'text',     'table' => 'peserta_alamat'],
        'kecamatan'         => ['label' => 'Kecamatan',                'tipe' => 'text',     'table' => 'peserta_alamat'],
        'kabupaten_kota'    => ['label' => 'Kabupaten / Kota',         'tipe' => 'text',     'table' => 'peserta_alamat'],
        'provinsi'          => ['label' => 'Provinsi',                 'tipe' => 'text',     'table' => 'peserta_alamat'],
        'rt'                => ['label' => 'RT',                       'tipe' => 'text',     'table' => 'peserta_alamat'],
        'rw'                => ['label' => 'RW',                       'tipe' => 'text',     'table' => 'peserta_alamat'],
        'kode_pos'          => ['label' => 'Kode Pos',                 'tipe' => 'text',     'table' => 'peserta_alamat'],

        // --- Tabel: peserta_kontak ---
        'no_hp'             => ['label' => 'No. HP / WhatsApp',        'tipe' => 'text',     'table' => 'peserta_kontak'],
        'email'             => ['label' => 'Email',                    'tipe' => 'text',     'table' => 'peserta_kontak'],

        // --- Tabel: peserta_orang_tua ---
        'nama_ayah'         => ['label' => 'Nama Ayah',                'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'nama_ibu'          => ['label' => 'Nama Ibu',                 'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'nik_ayah'          => ['label' => 'NIK Ayah',                 'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'nik_ibu'           => ['label' => 'NIK Ibu',                  'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'pekerjaan_ayah'    => ['label' => 'Pekerjaan Ayah',           'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'pekerjaan_ibu'     => ['label' => 'Pekerjaan Ibu',            'tipe' => 'text',     'table' => 'peserta_orang_tua'],
        'penghasilan_ayah'  => ['label' => 'Penghasilan Ayah',         'tipe' => 'select',   'table' => 'peserta_orang_tua',
                                'opsi'  => ['< Rp 500.000', 'Rp 500.000 - Rp 1.000.000', 'Rp 1.000.000 - Rp 2.000.000', 'Rp 2.000.000 - Rp 5.000.000', '> Rp 5.000.000']],
        'penghasilan_ibu'   => ['label' => 'Penghasilan Ibu',          'tipe' => 'select',   'table' => 'peserta_orang_tua',
                                'opsi'  => ['< Rp 500.000', 'Rp 500.000 - Rp 1.000.000', 'Rp 1.000.000 - Rp 2.000.000', 'Rp 2.000.000 - Rp 5.000.000', '> Rp 5.000.000']],
        'no_hp_ortu'        => ['label' => 'No. HP Orang Tua',         'tipe' => 'text',     'table' => 'peserta_orang_tua'],

        // --- Tabel: peserta_periodik ---
        'tinggi_badan'      => ['label' => 'Tinggi Badan (cm)',         'tipe' => 'number',   'table' => 'peserta_periodik'],
        'berat_badan'       => ['label' => 'Berat Badan (kg)',          'tipe' => 'number',   'table' => 'peserta_periodik'],
        'jarak_rumah'       => ['label' => 'Jarak Rumah ke Sekolah (km)','tipe' => 'number',  'table' => 'peserta_periodik'],
        'jumlah_saudara'    => ['label' => 'Jumlah Saudara',            'tipe' => 'number',   'table' => 'peserta_periodik'],
    ];

    // =========================================================================
    // KONSTRUKTOR
    // =========================================================================

    public function __construct(
        protected FormulirPendaftaranRepositoryInterface $formulirRepo,
        protected LogActivityService                     $logActivity,
    ) {}

    // =========================================================================
    // 1. INDEX — DataTable Builder
    // =========================================================================

    /**
     * Menyediakan Builder query untuk Yajra DataTable formulir pendaftaran.
     * Difilter berdasarkan jalur_pendaftaran_id dan/atau tahun_pelajaran_id.
     *
     * Setiap baris data diperkaya dengan jumlah field yang dimiliki formulir
     * (sub-select count) agar controller/view tidak perlu N+1 query.
     *
     * @param  int|null $jalurId  Filter berdasarkan jalur pendaftaran (opsional)
     * @param  int|null $tahunId  Filter berdasarkan tahun pelajaran (opsional)
     * @return array{success: bool, message: string, data: mixed}
     */
    public function index(?int $jalurId = null, ?int $tahunId = null): array
    {
        try {
            $filters = [];

            if ($jalurId) {
                $filters['jalur_pendaftaran_id'] = $jalurId;
            }

            if ($tahunId) {
                $filters['tahun_pelajaran_id'] = $tahunId;
            }

            $query = $this->formulirRepo->datatable($filters)
                ->with(['jalurPendaftaran', 'tahunPelajaran'])
                ->withCount('formulirField');

            return [
                'success' => true,
                'message' => 'Data formulir pendaftaran berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[FormulirPendaftaranService::index] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data formulir pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 2. STORE
    // =========================================================================

    /**
     * Membuat formulir pendaftaran baru.
     *
     * Aturan bisnis:
     * - Hanya boleh ada SATU formulir per kombinasi (jalur_pendaftaran_id + tahun_pelajaran_id).
     * - Formulir baru dibuat dengan is_aktif = false secara default.
     *   Aktivasi hanya bisa dilakukan setelah minimal 1 field ditambahkan
     *   (lihat method toggleAktif).
     *
     * @param  array $data   Harus mengandung: jalur_pendaftaran_id, tahun_pelajaran_id, nama
     * @param  int   $userId
     * @return array
     */
    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Aturan: 1 formulir per jalur per tahun
            $existing = $this->formulirRepo->all([
                'jalur_pendaftaran_id' => $data['jalur_pendaftaran_id'],
                'tahun_pelajaran_id'   => $data['tahun_pelajaran_id'],
            ]);

            if ($existing->isNotEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Sudah terdapat formulir pendaftaran untuk jalur dan tahun pelajaran ini. Setiap jalur hanya boleh memiliki satu formulir per tahun pelajaran.',
                    'data'    => null,
                ];
            }

            // Formulir baru selalu nonaktif — diaktifkan setelah field ditambahkan
            $data['is_aktif'] = false;

            $formulir = $this->formulirRepo->create($data);

            $this->logActivity->log(
                'Tambah Formulir Pendaftaran',
                "Membuat Formulir Pendaftaran: \"{$formulir->nama}\" (ID: {$formulir->id}) untuk jalur ID {$formulir->jalur_pendaftaran_id}, tahun pelajaran ID {$formulir->tahun_pelajaran_id}."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Formulir pendaftaran berhasil dibuat. Tambahkan field untuk dapat mengaktifkannya.',
                'data'    => $formulir->load(['jalurPendaftaran', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::store] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat formulir pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 3. SHOW
    // =========================================================================

    /**
     * Menampilkan detail formulir beserta seluruh field terurut (ASC urutan).
     *
     * Field sudah di-sort ASC berdasarkan kolom `urutan` melalui global scope
     * pada model FormulirField, sehingga tidak perlu explicit orderBy di sini.
     *
     * @param  int $id
     * @return array
     */
    public function show(int $id): array
    {
        try {
            $formulir = $this->formulirRepo->findWithFields($id);

            if (! $formulir) {
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail formulir pendaftaran berhasil diambil.',
                'data'    => $formulir->load(['jalurPendaftaran', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            Log::error('[FormulirPendaftaranService::show] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil detail formulir pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 4. UPDATE
    // =========================================================================

    /**
     * Memperbarui data header formulir (nama, deskripsi, tipe).
     *
     * Catatan:
     * - Field `is_aktif` TIDAK dapat diubah melalui method ini.
     *   Gunakan `toggleAktif()` agar validasi minimal-1-field selalu diterapkan.
     * - Kolom jalur_pendaftaran_id dan tahun_pelajaran_id tidak bisa diubah
     *   karena menyangkut integritas relasi; buat formulir baru jika diperlukan.
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
            $formulir = $this->formulirRepo->findById($id);

            if (! $formulir) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Hapus field yang tidak boleh diubah via update biasa
            unset($data['is_aktif'], $data['jalur_pendaftaran_id'], $data['tahun_pelajaran_id']);

            $updated = $this->formulirRepo->update($id, $data);

            $this->logActivity->log(
                'Update Formulir Pendaftaran',
                "Memperbarui Formulir Pendaftaran: \"{$updated->nama}\" (ID: {$id})."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Formulir pendaftaran berhasil diperbarui.',
                'data'    => $updated->load(['jalurPendaftaran', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::update] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memperbarui formulir pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 5. DESTROY
    // =========================================================================

    /**
     * Menghapus formulir pendaftaran beserta seluruh field-nya.
     *
     * Aturan bisnis (hard block):
     * - Formulir TIDAK BOLEH dihapus jika jalur pendaftarannya sudah memiliki
     *   data pendaftaran yang berstatus "submit" atau lebih lanjut
     *   (verifikasi, lulus, dll.). Status "draft" masih diizinkan.
     * - Penghapusan field dilakukan di dalam transaksi yang sama.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $formulir = $this->formulirRepo->findWithFields($id);

            if (! $formulir) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Cek pendaftaran yang sudah submit di jalur ini
            $submittedCount = Pendaftaran::where('jalur_pendaftaran_id', $formulir->jalur_pendaftaran_id)
                ->whereNotIn('status', [Pendaftaran::STATUS_DRAFT])
                ->count();

            if ($submittedCount > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir \"{$formulir->nama}\" tidak dapat dihapus karena sudah terdapat {$submittedCount} pendaftaran yang telah disubmit pada jalur ini. Nonaktifkan formulir jika ingin menghentikan penerimaan.",
                    'data'    => null,
                ];
            }

            $namaFormulir = $formulir->nama;
            $jumlahField  = $formulir->formulirField->count();

            // Hapus semua field terlebih dahulu (cascade manual)
            FormulirField::where('formulir_pendaftaran_id', $id)->delete();

            $this->formulirRepo->delete($id);

            $this->logActivity->log(
                'Hapus Formulir Pendaftaran',
                "Menghapus Formulir Pendaftaran: \"{$namaFormulir}\" (ID: {$id}) beserta {$jumlahField} field."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Formulir pendaftaran \"{$namaFormulir}\" dan {$jumlahField} field-nya berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::destroy] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus formulir pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 6. TOGGLE AKTIF
    // =========================================================================

    /**
     * Mengaktifkan atau menonaktifkan formulir pendaftaran.
     *
     * Aturan bisnis:
     * - Formulir HANYA bisa diaktifkan (is_aktif = true) jika sudah memiliki
     *   minimal 1 field. Ini adalah gate penting untuk mencegah form kosong.
     * - Jika sedang aktif, toggle akan menonaktifkan tanpa syarat.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function toggleAktif(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $formulir = $this->formulirRepo->findWithFields($id);

            if (! $formulir) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $statusBaru = ! $formulir->is_aktif;

            // Validasi: tidak bisa diaktifkan jika belum ada field
            if ($statusBaru === true && $formulir->formulirField->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir \"{$formulir->nama}\" tidak dapat diaktifkan karena belum memiliki field. Tambahkan minimal 1 field terlebih dahulu.",
                    'data'    => null,
                ];
            }

            $updated = $this->formulirRepo->update($id, ['is_aktif' => $statusBaru]);

            $keterangan = $statusBaru ? 'diaktifkan' : 'dinonaktifkan';
            $this->logActivity->log(
                'Toggle Status Formulir Pendaftaran',
                "Formulir Pendaftaran \"{$formulir->nama}\" (ID: {$id}) berhasil {$keterangan}."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Formulir pendaftaran \"{$formulir->nama}\" berhasil {$keterangan}.",
                'data'    => $updated->load(['jalurPendaftaran', 'tahunPelajaran', 'formulirField']),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::toggleAktif] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengubah status formulir pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 7. ADD FIELD
    // =========================================================================

    /**
     * Menambahkan field baru ke formulir pendaftaran yang sudah ada.
     *
     * Validasi:
     * 1. Formulir harus ada.
     * 2. kode_field harus unik dalam satu formulir (case-insensitive, auto-slugify jika kosong).
     * 3. Jika is_statis = true, dapodik_key wajib diisi dan harus valid (ada di DAPODIK_FIELDS).
     * 4. Jika is_statis = false, dapodik_key harus null/kosong.
     * 5. Field tipe select/radio: opsi harus array, minimal 2 item.
     * 6. urutan baru ditetapkan sebagai max(urutan) + 1 untuk append otomatis.
     *
     * Format $fieldData yang diharapkan:
     * [
     *   'kode_field'  => string,
     *   'label'       => string,
     *   'tipe_field'  => 'text'|'number'|'date'|'select'|'radio'|'textarea'|'file',
     *   'is_required' => bool,
     *   'is_statis'   => bool,
     *   'dapodik_key' => string|null,
     *   'opsi'        => array|null,      // hanya untuk select/radio
     * ]
     *
     * @param  int   $formulirId
     * @param  array $fieldData
     * @param  int   $userId
     * @return array
     */
    public function addField(int $formulirId, array $fieldData, int $userId): array
    {
        DB::beginTransaction();
        try {
            $formulir = $this->formulirRepo->findById($formulirId);

            if (! $formulir) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$formulirId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Auto-generate kode_field jika tidak disediakan
            $kodeField = Str::slug($fieldData['kode_field'] ?? $fieldData['label'], '_');

            // Validasi keunikan kode_field dalam formulir ini
            $duplikat = FormulirField::where('formulir_pendaftaran_id', $formulirId)
                ->where('kode_field', $kodeField)
                ->exists();

            if ($duplikat) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Kode field \"{$kodeField}\" sudah digunakan dalam formulir ini. Gunakan kode yang berbeda.",
                    'data'    => null,
                ];
            }

            // Validasi field statis
            $isStatis  = (bool) ($fieldData['is_statis'] ?? false);
            $dapodikKey = $fieldData['dapodik_key'] ?? null;

            if ($isStatis) {
                if (empty($dapodikKey)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Field statis harus memiliki dapodik_key yang diisi.',
                        'data'    => null,
                    ];
                }

                if (! array_key_exists($dapodikKey, self::DAPODIK_FIELDS)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "dapodik_key \"{$dapodikKey}\" tidak valid. Gunakan salah satu dari: " . implode(', ', array_keys(self::DAPODIK_FIELDS)) . '.',
                        'data'    => null,
                    ];
                }
            } else {
                // Field dinamis tidak boleh memiliki dapodik_key
                $dapodikKey = null;
            }

            // Validasi opsi untuk field select/radio
            $tipeField = $fieldData['tipe_field'];
            $opsi      = null;

            if (in_array($tipeField, ['select', 'radio'])) {
                // Untuk field STATIS: auto-inject opsi dari DAPODIK_FIELDS jika tidak dikirim frontend.
                // Ini menangani kasus drag field Dapodik bertipe select/radio dari palette
                // yang tidak memiliki UI input opsi.
                if ($isStatis && $dapodikKey && isset(self::DAPODIK_FIELDS[$dapodikKey]['opsi'])) {
                    $fieldData['opsi'] = $fieldData['opsi'] ?? self::DAPODIK_FIELDS[$dapodikKey]['opsi'];
                }

                if (empty($fieldData['opsi']) || ! is_array($fieldData['opsi']) || count($fieldData['opsi']) < 2) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Field dengan tipe \"{$tipeField}\" harus memiliki minimal 2 pilihan opsi.",
                        'data'    => null,
                    ];
                }
                $opsi = array_values($fieldData['opsi']);
            }


            // Tentukan urutan: append ke akhir daftar
            $maxUrutan = FormulirField::where('formulir_pendaftaran_id', $formulirId)
                ->max('urutan') ?? 0;

            $field = FormulirField::create([
                'formulir_pendaftaran_id' => $formulirId,
                'kode_field'              => $kodeField,
                'label'                   => $fieldData['label'],
                'tipe_field'              => $tipeField,
                'is_required'             => (bool) ($fieldData['is_required'] ?? false),
                'is_statis'               => $isStatis,
                'dapodik_key'             => $dapodikKey,
                'urutan'                  => $maxUrutan + 1,
                'opsi'                    => $opsi,
            ]);

            $this->logActivity->log(
                'Tambah Field Formulir',
                "Menambahkan field \"{$field->label}\" (kode: {$kodeField}) ke Formulir \"{$formulir->nama}\" (ID: {$formulirId}). Tipe: {$tipeField}, Statis: " . ($isStatis ? 'Ya' : 'Tidak') . '.'
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Field \"{$field->label}\" berhasil ditambahkan ke formulir.",
                'data'    => $field,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::addField] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan field: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 8. UPDATE FIELD
    // =========================================================================

    /**
     * Memperbarui konfigurasi sebuah field formulir.
     *
     * Catatan:
     * - kode_field TIDAK bisa diubah setelah dibuat karena bisa merusak referensi.
     * - Jika tipe berubah dari/ke select|radio, validasi opsi diterapkan ulang.
     * - Jika is_statis berubah, validasi dapodik_key diterapkan ulang.
     *
     * @param  int   $fieldId
     * @param  array $data
     * @param  int   $userId
     * @return array
     */
    public function updateField(int $fieldId, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $field = FormulirField::find($fieldId);

            if (! $field) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Field dengan ID {$fieldId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // kode_field dan urutan tidak diubah via method ini
            unset($data['kode_field'], $data['urutan'], $data['formulir_pendaftaran_id']);

            // Validasi field statis jika is_statis atau dapodik_key diubah
            $isStatis   = isset($data['is_statis']) ? (bool) $data['is_statis'] : $field->is_statis;
            $dapodikKey = $data['dapodik_key'] ?? $field->dapodik_key;

            if ($isStatis) {
                if (empty($dapodikKey)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Field statis harus memiliki dapodik_key yang diisi.',
                        'data'    => null,
                    ];
                }

                if (! array_key_exists($dapodikKey, self::DAPODIK_FIELDS)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "dapodik_key \"{$dapodikKey}\" tidak valid.",
                        'data'    => null,
                    ];
                }

                $data['dapodik_key'] = $dapodikKey;
            } else {
                $data['dapodik_key'] = null;
            }

            // Validasi opsi untuk tipe select/radio
            $tipeField = $data['tipe_field'] ?? $field->tipe_field;

            if (in_array($tipeField, ['select', 'radio'])) {
                $opsiInput = $data['opsi'] ?? $field->opsi;
                if (empty($opsiInput) || ! is_array($opsiInput) || count($opsiInput) < 2) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Field dengan tipe \"{$tipeField}\" harus memiliki minimal 2 pilihan opsi.",
                        'data'    => null,
                    ];
                }
                $data['opsi'] = array_values($opsiInput);
            } elseif (isset($data['tipe_field']) && ! in_array($data['tipe_field'], ['select', 'radio'])) {
                // Jika tipe berubah menjadi bukan select/radio, hapus opsi
                $data['opsi'] = null;
            }

            $field->update($data);
            $field->refresh();

            $this->logActivity->log(
                'Update Field Formulir',
                "Memperbarui field \"{$field->label}\" (ID: {$fieldId}) pada Formulir ID {$field->formulir_pendaftaran_id}."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Field \"{$field->label}\" berhasil diperbarui.",
                'data'    => $field,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::updateField] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memperbarui field: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 9. DELETE FIELD
    // =========================================================================

    /**
     * Menghapus field dari formulir.
     *
     * Hard block: field TIDAK BOLEH dihapus jika sudah ada data pengisian
     * (records di tabel pendaftaran_field_value) yang merujuk ke field ini.
     * Ini untuk menjaga integritas data histori pendaftar.
     *
     * @param  int $fieldId
     * @param  int $userId
     * @return array
     */
    public function deleteField(int $fieldId, int $userId): array
    {
        DB::beginTransaction();
        try {
            $field = FormulirField::find($fieldId);

            if (! $field) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Field dengan ID {$fieldId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Cek apakah ada data yang sudah diisi di field ini
            $jumlahData = PendaftaranFieldValue::where('formulir_field_id', $fieldId)->count();

            if ($jumlahData > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Field \"{$field->label}\" tidak dapat dihapus karena sudah terdapat {$jumlahData} data pengisian dari pendaftar.",
                    'data'    => null,
                ];
            }

            $labelField   = $field->label;
            $formulirId   = $field->formulir_pendaftaran_id;
            $field->delete();

            // Re-normalisasi urutan agar tidak ada gap setelah penghapusan
            $this->renormalizeUrutan($formulirId);

            $this->logActivity->log(
                'Hapus Field Formulir',
                "Menghapus field \"{$labelField}\" (ID: {$fieldId}) dari Formulir ID {$formulirId}."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Field \"{$labelField}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::deleteField] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus field: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 10. REORDER FIELDS
    // =========================================================================

    /**
     * Memperbarui urutan tampil field-field dalam formulir (drag-and-drop).
     *
     * Cara kerja:
     * - Menerima array ID field yang sudah diurutkan oleh user.
     * - Setiap ID di-assign nilai urutan = (index + 1) mulai dari 1.
     * - Semua ID field dalam $urutanIds HARUS milik formulir yang sama.
     *
     * Format $urutanIds:
     *   [3, 1, 5, 2, 4]  // IDs field dalam urutan yang diinginkan
     *
     * @param  int   $formulirId  ID formulir yang field-nya akan di-reorder
     * @param  array $urutanIds   Array ID FormulirField dalam urutan baru
     * @param  int   $userId
     * @return array
     */
    public function reorderFields(int $formulirId, array $urutanIds, int $userId): array
    {
        DB::beginTransaction();
        try {
            $formulir = $this->formulirRepo->findById($formulirId);

            if (! $formulir) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$formulirId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            if (empty($urutanIds)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Daftar urutan field tidak boleh kosong.',
                    'data'    => null,
                ];
            }

            // Validasi: semua ID harus milik formulir_id yang sama
            $jumlahValid = FormulirField::where('formulir_pendaftaran_id', $formulirId)
                ->whereIn('id', $urutanIds)
                ->count();

            if ($jumlahValid !== count($urutanIds)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Terdapat ID field yang tidak valid atau bukan milik formulir ini.',
                    'data'    => null,
                ];
            }

            // Update urutan tiap field sesuai posisi dalam array
            foreach ($urutanIds as $index => $fieldId) {
                FormulirField::where('id', $fieldId)
                    ->where('formulir_pendaftaran_id', $formulirId)
                    ->update(['urutan' => $index + 1]);
            }

            $this->logActivity->log(
                'Reorder Field Formulir',
                "Mengurutkan ulang " . count($urutanIds) . " field pada Formulir \"{$formulir->nama}\" (ID: {$formulirId})."
            );

            DB::commit();

            // Kembalikan fields dengan urutan terbaru
            $fields = FormulirField::where('formulir_pendaftaran_id', $formulirId)
                ->orderBy('urutan')
                ->get();

            return [
                'success' => true,
                'message' => 'Urutan field berhasil diperbarui.',
                'data'    => $fields,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[FormulirPendaftaranService::reorderFields] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengubah urutan field: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 11. GET FIELDS FOR PENDAFTARAN (Dynamic Form Renderer)
    // =========================================================================

    /**
     * Mengembalikan daftar field yang siap di-render sebagai form HTML pendaftaran.
     *
     * Output setiap field dalam format yang sudah dinormalisasi:
     * [
     *   'id'           => int,
     *   'kode_field'   => string,
     *   'label'        => string,
     *   'tipe_field'   => string,
     *   'is_required'  => bool,
     *   'is_statis'    => bool,
     *   'dapodik_key'  => string|null,
     *   'opsi_parsed'  => array|null,   // untuk select/radio: array of string options
     *   'urutan'       => int,
     *   'placeholder'  => string,       // auto-generated dari label
     *   'help_text'    => string|null,  // konteks tambahan untuk field Dapodik
     *   'dapodik_meta' => array|null,   // metadata Dapodik (label, tipe, table) jika statis
     * ]
     *
     * @param  int $formulirId
     * @return array
     */
    public function getFieldsForPendaftaran(int $formulirId): array
    {
        try {
            $formulir = $this->formulirRepo->findWithFields($formulirId);

            if (! $formulir) {
                return [
                    'success' => false,
                    'message' => "Formulir pendaftaran dengan ID {$formulirId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            if (! $formulir->is_aktif) {
                return [
                    'success' => false,
                    'message' => 'Formulir pendaftaran ini belum diaktifkan.',
                    'data'    => null,
                ];
            }

            $fieldsFormatted = $formulir->formulirField->map(function (FormulirField $field) {
                // Ambil metadata Dapodik jika field statis
                $dapodikMeta = null;
                $tipeField   = $field->tipe_field;
                $opsiParsed  = $field->opsi; // sudah di-cast ke array oleh model

                if ($field->is_statis && $field->dapodik_key && array_key_exists($field->dapodik_key, self::DAPODIK_FIELDS)) {
                    $dapodikMeta = self::DAPODIK_FIELDS[$field->dapodik_key];

                    // Gunakan tipe dari Dapodik config jika tipe_field field = 'text' (fallback)
                    if ($tipeField === 'text' && $dapodikMeta['tipe'] !== 'text') {
                        $tipeField = $dapodikMeta['tipe'];
                    }

                    // Merge opsi default Dapodik jika field tidak memiliki opsi custom
                    if (empty($opsiParsed) && isset($dapodikMeta['opsi'])) {
                        $opsiParsed = $dapodikMeta['opsi'];
                    }
                }

                return [
                    'id'           => $field->id,
                    'kode_field'   => $field->kode_field,
                    'label'        => $field->label,
                    'tipe_field'   => $tipeField,
                    'is_required'  => $field->is_required,
                    'is_statis'    => $field->is_statis,
                    'dapodik_key'  => $field->dapodik_key,
                    'opsi_parsed'  => $opsiParsed,
                    'urutan'       => $field->urutan,
                    'placeholder'  => 'Masukkan ' . strtolower($field->label),
                    'help_text'    => $field->is_statis
                                        ? 'Data ini akan disimpan ke profil peserta Dapodik Anda.'
                                        : null,
                    'dapodik_meta' => $dapodikMeta,
                ];
            })->values()->toArray();

            return [
                'success'  => true,
                'message'  => 'Field formulir berhasil diambil.',
                'formulir' => [
                    'id'      => $formulir->id,
                    'nama'    => $formulir->nama,
                    'deskripsi' => $formulir->deskripsi,
                ],
                'data'     => $fieldsFormatted,
            ];
        } catch (Exception $e) {
            Log::error('[FormulirPendaftaranService::getFieldsForPendaftaran] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil field formulir: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 12. GET DAPODIK FIELD OPTIONS
    // =========================================================================

    /**
     * Mengembalikan seluruh mapping dapodik_key yang tersedia beserta metadata-nya.
     *
     * Digunakan oleh form builder admin untuk menampilkan dropdown pemilihan
     * field statis Dapodik saat membuat/mengedit field formulir.
     *
     * Format kembalian:
     * [
     *   'nama_lengkap' => [
     *     'key'   => 'nama_lengkap',
     *     'label' => 'Nama Lengkap',
     *     'tipe'  => 'text',
     *     'table' => 'peserta',
     *     'opsi'  => null,
     *   ],
     *   ...
     * ]
     *
     * @return array
     */
    public function getDapodikFieldOptions(): array
    {
        try {
            $options = [];

            foreach (self::DAPODIK_FIELDS as $key => $meta) {
                $options[$key] = array_merge(['key' => $key], $meta);
            }

            // Kelompokkan per tabel untuk tampilan yang lebih terstruktur di UI
            $grouped = [];
            foreach ($options as $key => $meta) {
                $grouped[$meta['table']][$key] = $meta;
            }

            return [
                'success' => true,
                'message' => count($options) . ' opsi field Dapodik tersedia.',
                'data'    => [
                    'flat'    => $options,
                    'grouped' => $grouped,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[FormulirPendaftaranService::getDapodikFieldOptions] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil opsi field Dapodik.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Re-normalisasi kolom `urutan` agar selalu sequential (1, 2, 3, ...)
     * tanpa gap. Dipanggil setelah penghapusan field.
     *
     * @param  int $formulirId
     * @return void
     */
    private function renormalizeUrutan(int $formulirId): void
    {
        $fields = FormulirField::where('formulir_pendaftaran_id', $formulirId)
            ->orderBy('urutan')
            ->get();

        foreach ($fields as $index => $field) {
            $field->update(['urutan' => $index + 1]);
        }
    }
}
