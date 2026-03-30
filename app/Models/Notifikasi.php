<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'tipe',
        'judul',
        'isi',
        'status',
        'dibaca',
        'waktu_kirim',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'waktu_kirim' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->where('dibaca', false);
    }

    public function scopeByTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }
}
