<?php

namespace App\Models\Ppdb;

use App\Models\Master\TahunPelajaran;
use App\Models\Transaksi\PembayaranPpdb;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiayaRegistrasi extends Model
{
    protected $table = 'biaya_registrasi';

    protected $fillable = [
        'jalur_pendaftaran_id',
        'tahun_pelajaran_id',
        'nama',
        'nominal',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function pembayaranPpdb(): HasMany
    {
        return $this->hasMany(PembayaranPpdb::class, 'biaya_registrasi_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }
}
