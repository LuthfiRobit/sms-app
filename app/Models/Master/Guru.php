<?php

namespace App\Models\Master;

use App\Models\Akademik\AbsensiGuru;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'lembaga_id',
        'user_id',
        'nip',
        'nuptk',
        'nama',
        'email',
        'no_hp',
        'foto',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'status',
    ];

    /** Supaya nama_lengkap ikut ter-serialize saat model dikembalikan sebagai JSON. */
    protected $appends = ['nama_lengkap'];

    /** Nama lengkap dengan gelar, dipakai di dropdown wali kelas. */
    public function getNamaLengkapAttribute(): string
    {
        return trim(
            ($this->gelar_depan ? $this->gelar_depan.' ' : '')
            .$this->nama
            .($this->gelar_belakang ? ', '.$this->gelar_belakang : '')
        );
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function absensiGuru(): HasMany
    {
        return $this->hasMany(AbsensiGuru::class);
    }

    public function scopeAktif($q)
    {
        return $q->where('status', 'aktif');
    }
}
