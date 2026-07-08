<?php

namespace App\Models\Akademik;

use App\Models\Master\MataPelajaran;
use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlpaStreakNotifikasiLog extends Model
{
    protected $table = 'alpa_streak_notifikasi_log';

    protected $fillable = [
        'peserta_id', 'mata_pelajaran_id', 'streak_mulai_tanggal', 'streak_length_saat_kirim', 'sent_at',
    ];

    protected $casts = [
        'streak_mulai_tanggal' => 'date',
        'sent_at' => 'datetime',
    ];

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}
