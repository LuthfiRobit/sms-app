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

/**
 * Satu baris = satu skor dari satu sesi/tugas — lihat catatan di migrasi
 * `create_nilai_harian_log_table`. `Nilai::nilai_harian` adalah rata-rata
 * dari baris-baris ini untuk (peserta, mapel, semester) yang sama.
 */
class NilaiHarianLog extends Model
{
    protected $table = 'nilai_harian_log';

    protected $fillable = [
        'lembaga_id', 'rombel_id', 'peserta_id', 'mata_pelajaran_id',
        'semester_id', 'tahun_pelajaran_id',
        'tanggal', 'pertemuan_ke', 'keterangan', 'nilai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai' => 'float',
    ];

    public function lembaga(): BelongsTo { return $this->belongsTo(Lembaga::class); }
    public function rombel(): BelongsTo { return $this->belongsTo(Rombel::class); }
    public function peserta(): BelongsTo { return $this->belongsTo(Peserta::class); }
    public function mataPelajaran(): BelongsTo { return $this->belongsTo(MataPelajaran::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }
}
