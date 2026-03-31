<?php

namespace App\Services\Peserta;

use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Peserta\PesertaAlamatRepositoryInterface;
use App\Repositories\Peserta\PesertaOrangTuaRepositoryInterface;
use App\Repositories\Peserta\PesertaPeriodikRepositoryInterface;
use App\Repositories\Peserta\PesertaKontakRepositoryInterface;
use App\Repositories\Peserta\PesertaDokumenPribadiRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Models\Peserta\Peserta;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PesertaService
{
    protected $pesertaRepo;
    protected $alamatRepo;
    protected $orangTuaRepo;
    protected $periodikRepo;
    protected $kontakRepo;
    protected $dokumenRepo;
    protected $logActivity;
    protected $response;

    public function __construct(
        PesertaRepositoryInterface $pesertaRepo,
        PesertaAlamatRepositoryInterface $alamatRepo,
        PesertaOrangTuaRepositoryInterface $orangTuaRepo,
        PesertaPeriodikRepositoryInterface $periodikRepo,
        PesertaKontakRepositoryInterface $kontakRepo,
        PesertaDokumenPribadiRepositoryInterface $dokumenRepo,
        LogActivityService $logActivity,
        ResponseService $response
    ) {
        $this->pesertaRepo = $pesertaRepo;
        $this->alamatRepo = $alamatRepo;
        $this->orangTuaRepo = $orangTuaRepo;
        $this->periodikRepo = $periodikRepo;
        $this->kontakRepo = $kontakRepo;
        $this->dokumenRepo = $dokumenRepo;
        $this->logActivity = $logActivity;
        $this->response = $response;
    }

    /**
     * 1. Mendapatkan daftar peserta untuk DataTable dengan filter
     * Filter: nama, nisn, kecamatan, agama
     *
     * @param array $filters
     * @return array
     */
    public function index(array $filters = []): array
    {
        try {
            $query = Peserta::query()
                ->leftJoin('peserta_alamat', 'peserta.id', '=', 'peserta_alamat.peserta_id')
                ->select([
                    'peserta.id',
                    'peserta.user_id',
                    'peserta.nisn',
                    'peserta.nik',
                    'peserta.nama_lengkap',
                    'peserta.jenis_kelamin',
                    'peserta.agama',
                    'peserta_alamat.kecamatan'
                ]);

            if (!empty($filters['nama'])) {
                $query->where('peserta.nama_lengkap', 'like', '%' . $filters['nama'] . '%');
            }
            if (!empty($filters['nisn'])) {
                $query->where('peserta.nisn', 'like', '%' . $filters['nisn'] . '%');
            }
            if (!empty($filters['kecamatan'])) {
                $query->where('peserta_alamat.kecamatan', 'like', '%' . $filters['kecamatan'] . '%');
            }
            if (!empty($filters['agama'])) {
                $query->where('peserta.agama', $filters['agama']);
            }

            $dataTable = DataTables::of($query)
                ->addIndexColumn()
                ->make(true);

            return $this->response->success('Data berhasil dimuat', $dataTable->original);
        } catch (Exception $e) {
            Log::error('PesertaService@index: ' . $e->getMessage());
            return $this->response->error('Gagal mengambil data peserta: ' . $e->getMessage());
        }
    }

    /**
     * 2. Store Peserta: Create peserta harus membuat semua tabel terkait dalam satu transaksi
     *
     * @param array $data Structure: {peserta:{}, alamat:{}, orang_tua:[], periodik:{}, kontak:{}, dokumen_pribadi:{}}
     * @param int $userId ID User yang melakukan aksi (admin)
     * @return array
     */
    public function store(array $data, int $userId): array
    {
        try {
            $pesertaData = $data['peserta'] ?? [];

            // Validasi NISN & NIK Unik secara global, case-insensitive
            if (!empty($pesertaData['nisn'])) {
                $isNisnExist = Peserta::where(DB::raw('LOWER(nisn)'), strtolower($pesertaData['nisn']))->exists();
                if ($isNisnExist) return $this->response->error('NISN sudah terdaftar.', 400);
            }

            if (!empty($pesertaData['nik'])) {
                $isNikExist = Peserta::where(DB::raw('LOWER(nik)'), strtolower($pesertaData['nik']))->exists();
                if ($isNikExist) return $this->response->error('NIK sudah terdaftar.', 400);
            }

            DB::beginTransaction();

            $peserta = $this->pesertaRepo->create($pesertaData);

            if (!empty($data['alamat'])) {
                $alamatData = $data['alamat'];
                $alamatData['peserta_id'] = $peserta->id;
                $this->alamatRepo->create($alamatData);
            }

            if (!empty($data['orang_tua']) && is_array($data['orang_tua'])) {
                foreach ($data['orang_tua'] as $ortu) {
                    $ortu['peserta_id'] = $peserta->id;
                    $this->orangTuaRepo->create($ortu);
                }
            }

            if (!empty($data['periodik'])) {
                $periodikData = $data['periodik'];
                $periodikData['peserta_id'] = $peserta->id;
                $this->periodikRepo->create($periodikData);
            }

            if (!empty($data['kontak'])) {
                $kontakData = $data['kontak'];
                $kontakData['peserta_id'] = $peserta->id;
                $this->kontakRepo->create($kontakData);
            }

            if (!empty($data['dokumen_pribadi'])) {
                $dokumenData = $data['dokumen_pribadi'];
                $dokumenData['peserta_id'] = $peserta->id;
                $this->dokumenRepo->create($dokumenData);
            }

            DB::commit();

            $peserta->load(['alamat', 'orangTua', 'periodik', 'kontak', 'dokumenPribadi']);
            $this->logActivity->record("Menambah peserta: {$peserta->nama_lengkap}", $userId);

            return $this->response->success('Data peserta berhasil disimpan.', $peserta->toArray());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PesertaService@store: ' . $e->getMessage());
            return $this->response->error('Gagal menyimpan data peserta: ' . $e->getMessage());
        }
    }

    /**
     * 3. Menampilkan detail peserta dengan SEMUA relasi eager loaded
     *
     * @param int $id
     * @return array
     */
    public function show(int $id): array
    {
        try {
            $peserta = Peserta::with([
                'alamat', 'orangTua', 'periodik', 'kontak', 'dokumenPribadi'
            ])->find($id);

            if (!$peserta) {
                return $this->response->error('Peserta tidak ditemukan.', 404);
            }

            return $this->response->success('Detail peserta berhasil dimuat.', $peserta->toArray());
        } catch (Exception $e) {
            Log::error('PesertaService@show: ' . $e->getMessage());
            return $this->response->error('Gagal memuat detail peserta: ' . $e->getMessage());
        }
    }

    /**
     * 4. Update data peserta dan semua sub-tabel dalam transaksi
     *
     * @param int $id
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function update(int $id, array $data, int $userId): array
    {
        try {
            $peserta = Peserta::find($id);
            if (!$peserta) {
                return $this->response->error('Peserta tidak ditemukan.', 404);
            }

            // Validasi: Jika peserta sudah memiliki akun user, hanya superadmin yang bisa edit.
            if (!empty($peserta->user_id)) {
                $currentUser = auth()->user();
                if (!$currentUser || !$currentUser->hasRole('superadmin')) {
                    return $this->response->error('Data peserta yang telah memiliki akun, tidak boleh diedit kecuali oleh Admin Superadmin.', 403);
                }
            }

            $pesertaData = $data['peserta'] ?? [];

            // Validasi Unique NIK/NISN (exclude current id)
            if (!empty($pesertaData['nisn'])) {
                $isNisnExist = Peserta::where(DB::raw('LOWER(nisn)'), strtolower($pesertaData['nisn']))
                    ->where('id', '!=', $id)->exists();
                if ($isNisnExist) return $this->response->error('NISN sudah digunakan oleh peserta lain.', 400);
            }

            if (!empty($pesertaData['nik'])) {
                $isNikExist = Peserta::where(DB::raw('LOWER(nik)'), strtolower($pesertaData['nik']))
                    ->where('id', '!=', $id)->exists();
                if ($isNikExist) return $this->response->error('NIK sudah digunakan oleh peserta lain.', 400);
            }

            DB::beginTransaction();

            if (!empty($pesertaData)) {
                $this->pesertaRepo->update($id, $pesertaData);
            }

            if (isset($data['alamat'])) {
                // Update or create based on participants ID
                $this->alamatRepo->updateOrCreate(['peserta_id' => $id], $data['alamat']);
            }

            if (isset($data['orang_tua']) && is_array($data['orang_tua'])) {
                foreach ($data['orang_tua'] as $ortu) {
                    if (!empty($ortu['tipe'])) {
                        $this->orangTuaRepo->updateOrCreate(
                            ['peserta_id' => $id, 'tipe' => $ortu['tipe']],
                            $ortu
                        );
                    }
                }
            }

            if (isset($data['periodik'])) {
                $this->periodikRepo->updateOrCreate(['peserta_id' => $id], $data['periodik']);
            }

            if (isset($data['kontak'])) {
                $this->kontakRepo->updateOrCreate(['peserta_id' => $id], $data['kontak']);
            }

            if (isset($data['dokumen_pribadi'])) {
                $this->dokumenRepo->updateOrCreate(['peserta_id' => $id], $data['dokumen_pribadi']);
            }

            DB::commit();

            $peserta->refresh();
            $peserta->load(['alamat', 'orangTua', 'periodik', 'kontak', 'dokumenPribadi']);

            $this->logActivity->record("Mengupdate peserta: {$peserta->nama_lengkap}", $userId);

            return $this->response->success('Data peserta berhasil diperbarui.', $peserta->toArray());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PesertaService@update: ' . $e->getMessage());
            return $this->response->error('Gagal memperbarui peserta: ' . $e->getMessage());
        }
    }

    /**
     * 5. Hapus peserta dan cek pendaftaran aktif. Soft delete tabel peserta.
     *
     * @param int $id
     * @param int $userId
     * @return array
     */
    public function destroy(int $id, int $userId): array
    {
        try {
            $peserta = Peserta::find($id);
            if (!$peserta) {
                return $this->response->error('Peserta tidak ditemukan.', 404);
            }

            // Cek tidak ada pendaftaran aktif sebelum hapus
            // Asumsi tabel 'pendaftaran' memiliki 'peserta_id' dan status bukan ['batal', 'gagal'] menandakan aktif.
            $hasActiveRegistration = DB::table('pendaftaran')
                ->where('peserta_id', $id)
                ->whereNotIn('status', ['batal', 'gagal'])
                ->exists();

            if ($hasActiveRegistration) {
                return $this->response->error('Peserta tidak dapat dihapus karena memiliki riwayat pendaftaran aktif.', 400);
            }

            $nama = $peserta->nama_lengkap;

            // Soft delete hanya di tabel peserta
            $this->pesertaRepo->delete($id);

            $this->logActivity->record("Menghapus peserta: {$nama}", $userId);

            return $this->response->success('Peserta berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('PesertaService@destroy: ' . $e->getMessage());
            return $this->response->error('Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    /**
     * 6. Return formatted data untuk tampilan multi-tab admin
     *
     * @param int $id
     * @return array
     */
    public function getDetailForAdmin(int $id): array
    {
        $result = $this->show($id);
        if (!$result['status']) {
            return $result;
        }

        $p = $result['data'];
        $ortuList = collect($p['orang_tua'] ?? []);

        $formatted = [
            'tab_pribadi' => [
                'nisn' => $p['nisn'] ?? '-',
                'nik' => $p['nik'] ?? '-',
                'nama' => $p['nama_lengkap'] ?? '-',
                'jenis_kelamin' => $p['jenis_kelamin'] ?? '-',
                'tempat_tanggal_lahir' => ($p['tempat_lahir'] ?? '-') . ', ' . ($p['tanggal_lahir'] ?? '-'),
                'agama' => $p['agama'] ?? '-',
                'kebutuhan_khusus' => $p['kebutuhan_khusus'] ?? '-',
                'no_kk' => $p['no_kk'] ?? '-',
            ],
            'tab_alamat' => [
                'alamat' => $p['alamat']['alamat'] ?? '-',
                'rt_rw' => ($p['alamat']['rt'] ?? '-') . '/' . ($p['alamat']['rw'] ?? '-'),
                'dusun' => $p['alamat']['dusun'] ?? '-',
                'desa_kelurahan' => $p['alamat']['desa_kelurahan'] ?? '-',
                'kecamatan' => $p['alamat']['kecamatan'] ?? '-',
                'kabupaten_kota' => $p['alamat']['kabupaten_kota'] ?? '-',
                'provinsi' => $p['alamat']['provinsi'] ?? '-',
                'kode_pos' => $p['alamat']['kode_pos'] ?? '-',
                'koordinat' => ($p['alamat']['lintang'] ?? '-') . ', ' . ($p['alamat']['bujur'] ?? '-')
            ],
            'tab_orang_tua' => [
                'ayah' => $ortuList->firstWhere('tipe', 'ayah') ?? null,
                'ibu' => $ortuList->firstWhere('tipe', 'ibu') ?? null,
                'wali' => $ortuList->firstWhere('tipe', 'wali') ?? null,
            ],
            'tab_periodik' => [
                'tinggi_badan' => $p['periodik']['tinggi_badan'] ?? '-',
                'berat_badan' => $p['periodik']['berat_badan'] ?? '-',
                'jarak_rumah' => $p['periodik']['jarak_rumah'] ?? '-',
                'waktu_tempuh' => $p['periodik']['waktu_tempuh'] ?? '-',
                'jumlah_saudara' => $p['periodik']['jumlah_saudara'] ?? '-',
            ],
            'tab_kontak' => [
                'no_hp' => $p['kontak']['no_hp'] ?? '-',
                'email' => $p['kontak']['email'] ?? '-',
            ],
            'tab_dokumen' => [
                'no_kip' => $p['dokumen_pribadi']['no_kip'] ?? '-',
                'no_pkh' => $p['dokumen_pribadi']['no_pkh'] ?? '-',
                'no_kitas' => $p['dokumen_pribadi']['no_kitas'] ?? '-',
                'no_paspor' => $p['dokumen_pribadi']['no_paspor'] ?? '-',
                'foto' => $p['foto'] ?? null,
            ]
        ];

        return $this->response->success('Detail peserta terformat berhasil dimuat.', $formatted);
    }

    /**
     * 7. Proses import CSV Dapodik
     *
     * @param string $filePath
     * @param int $userId
     * @return array {success_count, error_count, errors: [{row, message}]}
     */
    public function importFromCsv(string $filePath, int $userId): array
    {
        try {
            if (!file_exists($filePath) || !is_readable($filePath)) {
                return $this->response->error('File CSV tidak dapat dialokasikan.');
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            $handle = fopen($filePath, 'r');
            // Skip Header
            fgetcsv($handle, 1000, ',');

            $rowIndex = 2; // Default line index if skipping header
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 25) {
                    $errorCount++;
                    $errors[] = ['row' => $rowIndex, 'message' => 'Format kolom tidak lengkap (membutuhkan 25 kolom)'];
                    $rowIndex++;
                    continue;
                }

                $nisn = trim($row[0]);
                $nik = trim($row[1]);
                $nama = trim($row[2]);

                // Idempotent: skip baris yang NISN/NIK sudah ada
                $exist = Peserta::where(DB::raw('LOWER(nisn)'), strtolower($nisn))
                    ->orWhere(DB::raw('LOWER(nik)'), strtolower($nik))
                    ->exists();

                if ($exist) {
                    $errorCount++;
                    $errors[] = ['row' => $rowIndex, 'message' => "NISN {$nisn} atau NIK {$nik} sudah terdaftar, baris dilewati."];
                    Log::warning("Import CSV: Skipped row {$rowIndex} (NISN/NIK exists).");
                    $rowIndex++;
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Try parsing date safely
                    $dob = null;
                    if (!empty(trim($row[5]))) {
                        try {
                            $dob = Carbon::createFromFormat('d/m/Y', trim($row[5]))->format('Y-m-d');
                        } catch (Exception $exDate) {
                            $dob = null; // fallback or report error
                        }
                    }

                    $peserta = $this->pesertaRepo->create([
                        'nisn' => $nisn,
                        'nik' => $nik,
                        'nama_lengkap' => $nama,
                        'jenis_kelamin' => trim($row[3]),
                        'tempat_lahir' => trim($row[4]),
                        'tanggal_lahir' => $dob,
                        'agama' => trim($row[6]),
                    ]);

                    $this->alamatRepo->create([
                        'peserta_id' => $peserta->id,
                        'alamat' => trim($row[7]),
                        'rt' => trim($row[8]),
                        'rw' => trim($row[9]),
                        'desa_kelurahan' => trim($row[10]),
                        'kecamatan' => trim($row[11]),
                        'kabupaten_kota' => trim($row[12]),
                        'provinsi' => trim($row[13]),
                        'kode_pos' => trim($row[14]),
                    ]);

                    $this->kontakRepo->create([
                        'peserta_id' => $peserta->id,
                        'no_hp' => trim($row[15]),
                        'email' => trim($row[16]),
                    ]);

                    if (!empty(trim($row[17]))) {
                        $this->orangTuaRepo->create([
                            'peserta_id' => $peserta->id,
                            'tipe' => 'ayah',
                            'nama' => trim($row[17]),
                            'pekerjaan' => trim($row[18]),
                        ]);
                    }

                    if (!empty(trim($row[19]))) {
                        $this->orangTuaRepo->create([
                            'peserta_id' => $peserta->id,
                            'tipe' => 'ibu',
                            'nama' => trim($row[19]),
                            'pekerjaan' => trim($row[20]),
                        ]);
                    }

                    $this->periodikRepo->create([
                        'peserta_id' => $peserta->id,
                        'tinggi_badan' => trim($row[21]),
                        'berat_badan' => trim($row[22]),
                        'jarak_rumah' => trim($row[23]),
                        'jumlah_saudara' => trim($row[24]),
                    ]);

                    $this->dokumenRepo->create(['peserta_id' => $peserta->id]);

                    DB::commit();
                    $successCount++;
                } catch (Exception $ex) {
                    DB::rollBack();
                    $errorCount++;
                    $errors[] = ['row' => $rowIndex, 'message' => $ex->getMessage()];
                    Log::error("Import CSV Error row {$rowIndex}: " . $ex->getMessage());
                }

                $rowIndex++;
            }

            fclose($handle);

            if ($successCount > 0) {
                $this->logActivity->record("Mengimport {$successCount} data peserta dari CSV", $userId);
            }

            return $this->response->success('Proses import CSV berhasil.', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            Log::error('PesertaService@importFromCsv: ' . $e->getMessage());
            return $this->response->error('Sistem gagal memproses import CSV: ' . $e->getMessage());
        }
    }

    /**
     * 8. Export format CSV kompatibel dengan format Dapodik Kemdikbud
     *
     * @param array $filters
     * @return string Path absolut file CSV yang dihasilkan
     */
    public function exportToCsv(array $filters = []): string
    {
        try {
            $pesertaQuery = Peserta::with(['alamat', 'orangTua', 'periodik', 'kontak']);
            
            if (!empty($filters['nama'])) {
                $pesertaQuery->where('nama_lengkap', 'like', '%' . $filters['nama'] . '%');
            }
            if (!empty($filters['nisn'])) {
                $pesertaQuery->where('nisn', 'like', '%' . $filters['nisn'] . '%');
            }
            if (!empty($filters['agama'])) {
                $pesertaQuery->where('agama', $filters['agama']);
            }
            if (!empty($filters['kecamatan'])) {
                $pesertaQuery->whereHas('alamat', function($q) use ($filters) {
                    $q->where('kecamatan', 'like', '%' . $filters['kecamatan'] . '%');
                });
            }

            $pesertaList = $pesertaQuery->get();

            $fileName = 'dapodik_export_peserta_' . date('Ymd_His') . '.csv';
            // Simpan pada isolated storage framework
            $filePath = storage_path('app/public/' . $fileName);

            $handle = fopen($filePath, 'w');
            
            // Header standar Dapodik
            fputcsv($handle, [
                'NISN', 'NIK', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir (DD/MM/YYYY)',
                'Agama', 'Alamat', 'RT', 'RW', 'Desa/Kelurahan', 'Kecamatan', 'Kab/Kota', 'Provinsi', 'Kode Pos',
                'No HP', 'Email', 'Nama Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Pekerjaan Ibu',
                'Tinggi Badan', 'Berat Badan', 'Jarak Rumah', 'Jumlah Saudara'
            ]);

            foreach ($pesertaList as $p) {
                $ortu = collect($p->orangTua);
                $ayah = $ortu->firstWhere('tipe', 'ayah');
                $ibu = $ortu->firstWhere('tipe', 'ibu');
                
                fputcsv($handle, [
                    $p->nisn,
                    $p->nik,
                    $p->nama_lengkap,
                    $p->jenis_kelamin,
                    $p->tempat_lahir,
                    $p->tanggal_lahir ? Carbon::parse($p->tanggal_lahir)->format('d/m/Y') : '',
                    $p->agama,
                    $p->alamat->alamat ?? '',
                    $p->alamat->rt ?? '',
                    $p->alamat->rw ?? '',
                    $p->alamat->desa_kelurahan ?? '',
                    $p->alamat->kecamatan ?? '',
                    $p->alamat->kabupaten_kota ?? '',
                    $p->alamat->provinsi ?? '',
                    $p->alamat->kode_pos ?? '',
                    $p->kontak->no_hp ?? '',
                    $p->kontak->email ?? '',
                    $ayah->nama ?? '',
                    $ayah->pekerjaan ?? '',
                    $ibu->nama ?? '',
                    $ibu->pekerjaan ?? '',
                    $p->periodik->tinggi_badan ?? '',
                    $p->periodik->berat_badan ?? '',
                    $p->periodik->jarak_rumah ?? '',
                    $p->periodik->jumlah_saudara ?? ''
                ]);
            }

            fclose($handle);
            
            if (auth()->check()) {
                $this->logActivity->record("Mengexport data ke CSV Dapodik", auth()->id());
            }

            return $filePath;
        } catch (Exception $e) {
            Log::error('PesertaService@exportToCsv: ' . $e->getMessage());
            return '';
        }
    }
}
