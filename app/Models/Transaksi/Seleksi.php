<?php

namespace App\Models\Transaksi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seleksi extends Model
{
    protected $table = 'seleksi';

    protected $fillable = [
        'pendaftaran_id',
        'reviewer_id',
        'model_penilaian',
        'nilai',
        'bobot',
        'keterangan',
        'waktu_nilai',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'bobot' => 'decimal:2',
        'waktu_nilai' => 'datetime',
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
