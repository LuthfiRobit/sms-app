<?php

namespace App\Models\Akademik;

use App\Models\Master\MataPelajaran;
use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaportNilai extends Model
{
    protected $table = 'raport_nilai';

    protected $fillable = [
        'pengajuan_raport_id',
        'peserta_id',
        'mata_pelajaran_id',
        'nilai_harian',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
        'catatan_guru',
    ];

    protected $casts = [
        'nilai_harian' => 'float',
        'nilai_uts'    => 'float',
        'nilai_uas'    => 'float',
        'nilai_akhir'  => 'float',
    ];

    public function pengajuanRaport(): BelongsTo
    {
        return $this->belongsTo(PengajuanRaport::class, 'pengajuan_raport_id');
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Hitung predikat berdasarkan nilai_akhir.
     */
    public static function hitungPredikat(float $nilaiAkhir): string
    {
        if ($nilaiAkhir >= 86) return 'A';
        if ($nilaiAkhir >= 71) return 'B';
        if ($nilaiAkhir >= 56) return 'C';
        if ($nilaiAkhir >= 46) return 'D';
        return 'E';
    }
}
