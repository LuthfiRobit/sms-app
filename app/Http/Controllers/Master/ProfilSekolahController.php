<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\ProfilSekolahService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use App\Models\Master\ProfilSekolah;
use Illuminate\Http\Request;

class ProfilSekolahController extends Controller
{
    protected $profilSekolahService;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        ProfilSekolahService $profilSekolahService,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->profilSekolahService = $profilSekolahService;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Access Profil Sekolah Form', 'Membuka halaman pengaturan profil sekolah');
        $profil = ProfilSekolah::first();
        return view('admin.master.profil-sekolah.index', compact('profil'));
    }

    public function store(Request $request)
    {
        $profil = ProfilSekolah::first();
        $id = $profil ? $profil->id : null;

        $request->validate([
            'npsn' => ['required', 'string', 'max:8', $id ? 'unique:profil_sekolah,npsn,' . $id : 'unique:profil_sekolah,npsn'],
            'nss' => 'nullable|string|max:12',
            'nama_sekolah' => 'required|string|max:100',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'bentuk_pendidikan' => 'required|string|max:20',
            'alamat' => 'required|string',
            'kode_pos' => 'required|string|max:5',
            'telepon' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'website' => 'nullable|string|max:100',
            'kepala_sekolah' => 'required|string|max:100',
            'nip_kepsek' => 'nullable|string|max:18',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $this->profilSekolahService->updateOrCreateProfilSekolah($request->all());
            $this->logActivityService->log('Update Profil Sekolah', 'Memperbarui data profil sekolah');
            return $this->responseService->success(null, 'Profil Sekolah berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Profil Sekolah: ' . $e->getMessage());
        }
    }
}
