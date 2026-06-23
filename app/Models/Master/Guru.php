<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'lembaga_id',
        'nip',
        'nuptk',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'status',
    ];

    /** Nama lengkap dengan gelar, dipakai di dropdown wali kelas. */
    public function getNamaLengkapAttribute(): string
    {
        return trim(
            ($this->gelar_depan ? $this->gelar_depan . ' ' : '')
            . $this->nama
            . ($this->gelar_belakang ? ', ' . $this->gelar_belakang : '')
        );
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function scopeAktif($q)
    {
        return $q->where('status', 'aktif');
    }
}
