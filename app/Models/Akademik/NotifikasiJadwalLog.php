<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiJadwalLog extends Model
{
    protected $table = 'notifikasi_jadwal_log';

    protected $fillable = [
        'jadwal_kbm_id', 'guru_id', 'tanggal', 'jenis', 'jumlah_token', 'sent_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sent_at' => 'datetime',
    ];

    public function jadwalKbm(): BelongsTo
    {
        return $this->belongsTo(JadwalKbm::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
