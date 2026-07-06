<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class RppMasterOpsi extends Model
{
    protected $table = 'rpp_master_opsi';

    protected $fillable = ['kategori', 'nama', 'urutan', 'status'];

    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
