<?php

namespace App\Models\Ppdb;

use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembukaanPpdb extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembukaan_ppdb';

    protected $fillable = [
        'lembaga_id',
        'tahun_pelajaran_id',
        'nama',
        'deskripsi',
        'mulai',
        'selesai',
        'status',
    ];

    protected $casts = [
        'mulai' => 'date',
        'selesai' => 'date',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function jalurPendaftaran(): HasMany
    {
        return $this->hasMany(JalurPendaftaran::class, 'pembukaan_ppdb_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'buka')
                     ->whereDate('selesai', '>=', now());
    }
}
