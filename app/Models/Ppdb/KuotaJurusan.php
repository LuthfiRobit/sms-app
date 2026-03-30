<?php

namespace App\Models\Ppdb;

use App\Models\Master\Jurusan;
use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaJurusan extends Model
{
    protected $table = 'kuota_jurusan';

    protected $fillable = [
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'jurusan_id',
        'kuota',
        'terisi',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function getSisaKuotaAttribute()
    {
        return max(0, $this->kuota - $this->terisi);
    }
}
