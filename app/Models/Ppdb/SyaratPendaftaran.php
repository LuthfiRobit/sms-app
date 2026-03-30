<?php

namespace App\Models\Ppdb;

use App\Models\Master\TahunPelajaran;
use App\Models\Transaksi\DokumenPeserta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyaratPendaftaran extends Model
{
    protected $table = 'syarat_pendaftaran';

    protected $fillable = [
        'jalur_pendaftaran_id',
        'tahun_pelajaran_id',
        'nama',
        'tipe',
        'wajib',
        'keterangan',
        'urutan',
    ];

    protected $casts = [
        'wajib' => 'boolean',
    ];

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function dokumenPeserta(): HasMany
    {
        return $this->hasMany(DokumenPeserta::class, 'syarat_pendaftaran_id');
    }

    public function scopeWajib(Builder $query): Builder
    {
        return $query->where('wajib', true);
    }
}
