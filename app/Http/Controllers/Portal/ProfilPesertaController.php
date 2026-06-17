<?php

namespace App\Http\Controllers\Portal;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Services\Peserta\PesertaProfileService;
use App\Services\PesertaAccountService;

/**
 * ProfilPesertaController
 *
 * Mengelola halaman profil peserta — data Dapodik multi-tabel dan akun (email/password).
 *
 * KEAMANAN: Semua operasi menggunakan $user->id_user (PK users) bukan auth()->id()
 * sehingga peserta hanya bisa mengubah datanya sendiri.
 *
 * Routes (prefix: ppdb.profil.*):
 *   GET  /profil          → index()
 *   PUT  /profil/akun     → updateAkun()
 *   PUT  /profil/dapodik  → updateDapodik()
 *   PUT  /profil/password → updatePassword()
 */
class ProfilPesertaController extends Controller
{
    public function __construct(
        protected PesertaProfileService $profileSvc,
        protected PesertaAccountService $accountSvc,
    ) {
    }

    // =========================================================================
    // INDEX — Tampilkan halaman profil
    // =========================================================================

    /**
     * Tampilkan halaman profil peserta multi-tab.
     *
     * Memanggil getOrCreatePeserta() terlebih dahulu untuk memastikan record
     * peserta dan sub-tabelnya sudah ada (idempotent bootstrap).
     */
    public function index(): View
    {
        $userId = auth()->user()->id_user;

        // Pastikan record peserta + sub-tabel dasar sudah ada
        $this->profileSvc->getOrCreatePeserta($userId);

        // Ambil profil lengkap beserta kelengkapan
        $profil = $this->profileSvc->getProfilLengkap($userId);

        return view('portal.profil.index', compact('profil'));
    }

    // =========================================================================
    // UPDATE AKUN — Update email & nama (data user, bukan Dapodik)
    // =========================================================================

    /**
     * Update data akun dasar (nama & email).
     *
     * @return JsonResponse {success: bool, message: string}
     */
    public function updateAkun(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'nama_lengkap' => 'required|min:3|max:255',
            'email'        => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->ignore($user->id_user, 'id_user'),
            ],
            'no_hp'        => ['required', 'regex:/^[0-9]{10,15}$/'],
        ], [
            'nama_lengkap.required' => 'Nama wali murid wajib diisi.',
            'nama_lengkap.min'      => 'Nama wali murid minimal 3 karakter.',
            'email.required'        => 'Email wali murid wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah digunakan akun lain.',
            'no_hp.required'        => 'Nomor HP wali murid wajib diisi.',
            'no_hp.regex'           => 'Nomor HP harus 10-15 digit angka.',
        ]);

        try {
            $this->accountSvc->updateBasicProfile(
                $user,
                $request->input('nama_lengkap'),
                $request->input('email'),
                $request->input('no_hp'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Data akun berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // =========================================================================
    // UPDATE DAPODIK — Update data terpartisi per section
    // =========================================================================

    /**
     * Update salah satu section data Dapodik (pribadi/alamat/ayah/ibu/wali/periodik/kontak/dokumen).
     *
     * Request wajib menyertakan field `section` untuk menentukan tabel mana yang diupdate.
     * Setiap section memiliki aturan validasi yang berbeda.
     *
     * @return JsonResponse {success: bool, message: string, kelengkapan: {persen: int, item_kurang: string[]}}
     */
    public function updateDapodik(Request $request): JsonResponse
    {
        $userId  = auth()->user()->id_user;
        $section = $request->input('section');

        // Validasi per section
        $validationRules = $this->getValidationRules($section, $userId);
        if ($validationRules !== null) {
            $request->validate($validationRules['rules'], $validationRules['messages'] ?? []);
        }

        try {
            $data   = $request->except(['section', '_token', '_method']);
            $result = null;

            switch ($section) {
                case 'pribadi':
                    // Upload foto jika ada
                    $foto   = $request->hasFile('foto') ? $request->file('foto') : null;
                    $result = $this->profileSvc->updatePribadi($userId, $data, $foto);
                    break;

                case 'alamat':
                    $result = $this->profileSvc->updateAlamat($userId, $data);
                    break;

                case 'ayah':
                case 'ibu':
                case 'wali':
                    $result = $this->profileSvc->updateOrangTua($userId, $section, $data);
                    break;

                case 'periodik':
                    $result = $this->profileSvc->updatePeriodik($userId, $data);
                    break;

                case 'kontak':
                    $result = $this->profileSvc->updateKontak($userId, $data);
                    break;

                case 'dokumen':
                    $result = $this->profileSvc->updateDokumenPribadi($userId, $data);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => "Section '{$section}' tidak dikenali.",
                    ], 422);
            }

            // Hitung ulang kelengkapan setelah update
            $peserta     = $this->profileSvc->getOrCreatePeserta($userId);
            $kelengkapan = $this->profileSvc->hitungKelengkapan($peserta);

            return response()->json([
                'success'     => true,
                'message'     => $result['message'] ?? 'Data berhasil disimpan.',
                'kelengkapan' => $kelengkapan,
                'foto_url'    => $section === 'pribadi' && isset($result['data']->foto)
                    ? asset('storage/' . $result['data']->foto)
                    : null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // =========================================================================
    // UPDATE PASSWORD
    // =========================================================================

    /**
     * Ubah password peserta dengan verifikasi password lama.
     *
     * @return JsonResponse {success: bool, message: string}
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama' => 'required',
            'password'      => 'required|min:8|confirmed',
        ], [
            'password_lama.required'   => 'Password lama wajib diisi.',
            'password.required'        => 'Password baru wajib diisi.',
            'password.min'             => 'Password baru minimal 8 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            $this->accountSvc->changePassword(
                auth()->user(),
                $request->input('password_lama'),
                $request->input('password'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // =========================================================================
    // PRIVATE — Validation rules per section
    // =========================================================================

    /**
     * Mengembalikan rules dan messages validasi sesuai section.
     *
     * @param  string|null $section
     * @param  int|null    $userId  Dibutuhkan untuk ignore unique check
     * @return array{rules: array, messages: array}|null  null = tidak ada validasi
     */
    private function getValidationRules(?string $section, ?int $userId = null): ?array
    {
        $pesertaId = null;
        if ($userId) {
            $peserta   = $this->profileSvc->getOrCreatePeserta($userId);
            $pesertaId = $peserta->id;
        }

        return match ($section) {
            'pribadi' => [
                'rules' => [
                    'nama_lengkap'   => 'required|string|min:3|max:255',
                    'jenis_kelamin'  => 'nullable|in:L,P',
                    'tanggal_lahir'  => 'nullable|date|before:today',
                    'tempat_lahir'   => 'nullable|string|max:100',
                    'agama'          => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
                    'kebutuhan_khusus' => 'nullable|string|max:255',
                    'foto'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                    'nik'            => 'nullable|numeric|digits:16|unique:peserta,nik,' . $pesertaId,
                    'nisn'           => 'nullable|numeric|digits:10|unique:peserta,nisn,' . $pesertaId,
                ],
                'messages' => [
                    'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
                    'nama_lengkap.min'       => 'Nama lengkap minimal 3 karakter.',
                    'jenis_kelamin.in'       => 'Jenis kelamin harus L atau P.',
                    'tanggal_lahir.date'     => 'Format tanggal lahir tidak valid.',
                    'tanggal_lahir.before'   => 'Tanggal lahir harus sebelum hari ini.',
                    'foto.image'             => 'File harus berupa gambar.',
                    'foto.max'               => 'Ukuran foto maksimal 2MB.',
                    'nik.numeric'            => 'NIK harus berupa angka.',
                    'nik.digits'             => 'NIK harus 16 digit.',
                    'nik.unique'             => 'NIK sudah digunakan oleh pendaftar lain.',
                    'nisn.numeric'           => 'NISN harus berupa angka.',
                    'nisn.digits'            => 'NISN harus 10 digit.',
                    'nisn.unique'            => 'NISN sudah digunakan oleh pendaftar lain.',
                ],
            ],
            'alamat' => [
                'rules' => [
                    'alamat'         => 'required|string|max:500',
                    'kabupaten_kota' => 'required|string|max:100',
                    'provinsi'       => 'required|string|max:100',
                    'rt'             => 'nullable|string|max:5',
                    'rw'             => 'nullable|string|max:5',
                    'desa_kelurahan' => 'nullable|string|max:100',
                    'kecamatan'      => 'nullable|string|max:100',
                    'kode_pos'       => 'nullable|string|max:10',
                ],
                'messages' => [
                    'alamat.required'         => 'Alamat wajib diisi.',
                    'kabupaten_kota.required' => 'Kabupaten/Kota wajib diisi.',
                    'provinsi.required'       => 'Provinsi wajib diisi.',
                ],
            ],
            'ayah', 'ibu', 'wali' => [
                'rules' => [
                    'nama'         => 'required|string|max:255',
                    'nik'          => 'nullable|string|max:20',
                    'pekerjaan'    => 'nullable|string|max:100',
                    'penghasilan'  => 'nullable|string|max:50',
                    'pendidikan'   => 'nullable|string|max:50',
                    'no_hp'        => 'nullable|regex:/^[0-9]{8,15}$/',
                ],
                'messages' => [
                    'nama.required'    => 'Nama wajib diisi.',
                    'no_hp.regex'      => 'Nomor HP hanya boleh angka (8-15 digit).',
                ],
            ],
            'periodik' => [
                'rules' => [
                    'tinggi_badan'  => 'nullable|numeric|min:50|max:250',
                    'berat_badan'   => 'nullable|numeric|min:10|max:200',
                    'lingkar_kepala' => 'nullable|numeric|min:30|max:80',
                    'jarak_rumah'   => 'nullable|numeric|min:0',
                    'waktu_tempuh'  => 'nullable|integer|min:0',
                    'jumlah_saudara' => 'nullable|integer|min:0|max:20',
                ],
                'messages' => [
                    'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
                    'berat_badan.numeric'  => 'Berat badan harus berupa angka.',
                ],
            ],
            'kontak' => [
                'rules' => [
                    'no_hp'  => ['required', 'regex:/^[0-9]{10,15}$/'],
                    'email'  => 'nullable|email|max:255',
                ],
                'messages' => [
                    'no_hp.required' => 'Nomor HP wajib diisi.',
                    'no_hp.regex'    => 'Nomor HP harus 10-15 digit angka.',
                    'email.email'    => 'Format email tidak valid.',
                ],
            ],
            'dokumen' => [
                'rules' => [
                    'no_kip'    => 'nullable|string|max:30',
                    'no_pkh'    => 'nullable|string|max:30',
                    'no_kitas'  => 'nullable|string|max:30',
                    'no_paspor' => 'nullable|string|max:30',
                ],
                'messages' => [],
            ],
            default => null,
        };
    }
}
