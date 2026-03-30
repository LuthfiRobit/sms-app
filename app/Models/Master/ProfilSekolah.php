<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilSekolah extends Model
{
    use HasFactory;

    protected $table = 'profil_sekolah';

    protected $fillable = [
        'npsn',
        'nss',
        'nama_sekolah',
        'status_sekolah',
        'bentuk_pendidikan',
        'alamat',
        'desa_id',
        'kode_pos',
        'telepon',
        'email',
        'website',
        'kepala_sekolah',
        'nip_kepsek',
        'logo',
    ];

    /**
     * Relationship to Village (Phase 4)
     */
    public function village()
    {
        // This will be linked once the villages table is created
        return $this->belongsTo(\App\Models\Master\Village::class, 'desa_id');
    }
}
