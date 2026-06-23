<?php

namespace App\Models\Akademik;

use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaportAbsensiRekap extends Model
{
    protected $table = 'raport_absensi_rekap';

    protected $fillable = [
        'pengajuan_raport_id',
        'peserta_id',
        'hadir',
        'sakit',
        'izin',
        'alpa',
    ];

    protected $casts = [
        'hadir' => 'integer',
        'sakit' => 'integer',
        'izin'  => 'integer',
        'alpa'  => 'integer',
    ];

    public function pengajuanRaport(): BelongsTo
    {
        return $this->belongsTo(PengajuanRaport::class, 'pengajuan_raport_id');
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }

    public function getTotalAbsensiAttribute(): int
    {
        return $this->sakit + $this->izin + $this->alpa;
    }

    public function getKeteranganAttribute(): string
    {
        return "S:{$this->sakit} I:{$this->izin} A:{$this->alpa}";
    }
}
