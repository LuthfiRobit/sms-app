<?php

namespace App\Models\Akademik;

use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    protected $table = 'nilai';

    protected $fillable = [
        'lembaga_id', 'rombel_id', 'peserta_id', 'mata_pelajaran_id',
        'semester_id', 'tahun_pelajaran_id',
        'nilai_harian', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'kkm', 'catatan',
    ];

    protected $casts = [
        'nilai_harian' => 'float', 'nilai_uts' => 'float',
        'nilai_uas'    => 'float', 'nilai_akhir' => 'float',
        'kkm'          => 'float',
    ];

    public function lembaga(): BelongsTo        { return $this->belongsTo(Lembaga::class); }
    public function rombel(): BelongsTo         { return $this->belongsTo(Rombel::class); }
    public function peserta(): BelongsTo        { return $this->belongsTo(Peserta::class); }
    public function mataPelajaran(): BelongsTo  { return $this->belongsTo(MataPelajaran::class); }
    public function semester(): BelongsTo       { return $this->belongsTo(Semester::class); }
    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }

    /** Nilai akhir = rata-rata tertimbang (40% harian, 30% UTS, 30% UAS). */
    public function hitungNilaiAkhir(): float
    {
        $h = $this->nilai_harian ?? 0;
        $u = $this->nilai_uts     ?? 0;
        $a = $this->nilai_uas     ?? 0;
        return round(($h * 0.4) + ($u * 0.3) + ($a * 0.3), 2);
    }
}
