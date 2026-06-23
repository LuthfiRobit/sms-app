<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerangkatMengajar extends Model
{
    protected $table = 'perangkat_mengajar';

    protected $fillable = [
        'lembaga_id', 'guru_id', 'mata_pelajaran_id',
        'tahun_pelajaran_id', 'semester_id',
        'jenis', 'judul', 'deskripsi',
        'file_path', 'file_name', 'status',
    ];

    public function lembaga(): BelongsTo        { return $this->belongsTo(Lembaga::class); }
    public function guru(): BelongsTo           { return $this->belongsTo(Guru::class); }
    public function mataPelajaran(): BelongsTo  { return $this->belongsTo(MataPelajaran::class); }
    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }
    public function semester(): BelongsTo       { return $this->belongsTo(Semester::class); }
}
