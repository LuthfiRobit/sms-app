<?php

namespace App\Services\Master;

use App\Models\Master\ProfilSekolah;
use App\Repositories\Master\ProfilSekolahRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilSekolahService
{
    protected $profilSekolahRepository;

    public function __construct(ProfilSekolahRepositoryInterface $profilSekolahRepository)
    {
        $this->profilSekolahRepository = $profilSekolahRepository;
    }

    public function updateOrCreateProfilSekolah(array $data)
    {
        $profil = ProfilSekolah::first();
        
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old logo if exists
            if ($profil && $profil->logo) {
                $this->deleteLogo($profil->logo);
            }
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        if ($profil) {
            $profil->update($data);
            return $profil;
        }

        return ProfilSekolah::create($data);
    }

    private function uploadLogo($file)
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/logos'), $fileName);
        return 'uploads/logos/' . $fileName;
    }

    private function deleteLogo($path)
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
