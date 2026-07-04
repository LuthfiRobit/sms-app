<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiAbsensiLog extends Model
{
    protected $table = 'notifikasi_absensi_log';

    protected $fillable = [
        'guru_id', 'tanggal', 'jenis', 'jumlah_token', 'sent_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sent_at' => 'datetime',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
