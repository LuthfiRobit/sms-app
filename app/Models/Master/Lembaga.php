<?php

namespace App\Models\Master;

use App\Models\User;
use App\Models\Ppdb\PembukaanPpdb;
use App\Models\Transaksi\Pendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lembaga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lembaga';

    protected $fillable = [
        'kode',
        'nama',
        'npsn',
        'jenis',
        'alamat',
        'telepon',
        'email',
        'kepala_sekolah',
        'logo',
        'status',
        'urutan',
        'latitude',
        'longitude',
        'radius_meter',
        'jam_masuk_batas',
    ];

    public function pembukaanPpdb(): HasMany
    {
        return $this->hasMany(PembukaanPpdb::class, 'lembaga_id');
    }

    public function jurusan(): HasMany
    {
        return $this->hasMany(Jurusan::class, 'lembaga_id');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'lembaga_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_lembaga', 'lembaga_id', 'user_id', 'id', 'id_user');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
