<?php

namespace App\Models\Ppdb;

use App\Models\Transaksi\Pendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JalurPendaftaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jalur_pendaftaran';

    protected $fillable = [
        'pembukaan_ppdb_id',
        'kode_jalur',
        'nama',
        'deskripsi',
        'kuota',
        'urutan',
        'status',
    ];

    public function pembukaanPpdb(): BelongsTo
    {
        return $this->belongsTo(PembukaanPpdb::class, 'pembukaan_ppdb_id');
    }

    public function jadwalPendaftaran(): HasMany
    {
        return $this->hasMany(JadwalPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function syaratPendaftaran(): HasMany
    {
        return $this->hasMany(SyaratPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function formulirPendaftaran(): HasMany
    {
        return $this->hasMany(FormulirPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function biayaRegistrasi(): HasMany
    {
        return $this->hasMany(BiayaRegistrasi::class, 'jalur_pendaftaran_id');
    }

    public function kuotaJurusan(): HasMany
    {
        return $this->hasMany(KuotaJurusan::class, 'jalur_pendaftaran_id');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
