<?php

namespace App\Models\Transaksi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilSeleksi extends Model
{
    protected $table = 'hasil_seleksi';

    public const STATUS_LULUS = 'lulus';
    public const STATUS_TIDAK_LULUS = 'tidak_lulus';
    public const STATUS_CADANGAN = 'cadangan';

    protected $fillable = [
        'pendaftaran_id',
        'total_nilai',
        'status_kelulusan',
        'peringkat',
        'reviewer_id',
        'waktu_pengumuman',
        'catatan',
    ];

    protected $casts = [
        'total_nilai' => 'decimal:2',
        'waktu_pengumuman' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'id_user');
    }
}
