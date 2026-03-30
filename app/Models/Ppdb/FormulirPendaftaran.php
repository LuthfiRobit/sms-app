<?php

namespace App\Models\Ppdb;

use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulirPendaftaran extends Model
{
    protected $table = 'formulir_pendaftaran';

    protected $fillable = [
        'jalur_pendaftaran_id',
        'tahun_pelajaran_id',
        'nama',
        'deskripsi',
        'tipe',
        'is_aktif',
    ];

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function formulirField(): HasMany
    {
        return $this->hasMany(FormulirField::class, 'formulir_pendaftaran_id');
    }
}
