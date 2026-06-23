<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalKbm extends Model
{
    protected $table = 'jadwal_kbm';

    protected $fillable = [
        'lembaga_id', 'tahun_pelajaran_id', 'rombel_id',
        'guru_id', 'mata_pelajaran_id',
        'hari', 'jam_mulai', 'jam_selesai', 'jam_ke', 'ruangan',
    ];

    public function lembaga(): BelongsTo       { return $this->belongsTo(Lembaga::class); }
    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }
    public function rombel(): BelongsTo        { return $this->belongsTo(Rombel::class); }
    public function guru(): BelongsTo          { return $this->belongsTo(Guru::class); }
    public function mataPelajaran(): BelongsTo { return $this->belongsTo(MataPelajaran::class); }
}
