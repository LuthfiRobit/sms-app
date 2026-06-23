<?php

namespace App\Models\Akademik;

use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiDetail extends Model
{
    public $timestamps = false;
    protected $table = 'absensi_detail';

    protected $fillable = ['absensi_id', 'peserta_id', 'status', 'keterangan'];

    public function absensi(): BelongsTo { return $this->belongsTo(Absensi::class); }
    public function peserta(): BelongsTo { return $this->belongsTo(Peserta::class); }
}
