<?php

namespace App\Models\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPendaftaran extends Model
{
    protected $table = 'jadwal_pendaftaran';

    protected $fillable = [
        'jalur_pendaftaran_id',
        'nama',
        'tipe',
        'mulai',
        'selesai',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
    ];

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }
}
