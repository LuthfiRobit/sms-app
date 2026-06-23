<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'lembaga_id', 'rombel_id', 'guru_id', 'mata_pelajaran_id',
        'tanggal', 'jam_ke', 'keterangan',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function lembaga(): BelongsTo        { return $this->belongsTo(Lembaga::class); }
    public function rombel(): BelongsTo         { return $this->belongsTo(Rombel::class); }
    public function guru(): BelongsTo           { return $this->belongsTo(Guru::class); }
    public function mataPelajaran(): BelongsTo  { return $this->belongsTo(MataPelajaran::class); }
    public function detail(): HasMany           { return $this->hasMany(AbsensiDetail::class); }
}
