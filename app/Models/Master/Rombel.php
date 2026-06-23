<?php

namespace App\Models\Master;

use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rombel extends Model
{
    use HasFactory;

    protected $table = 'rombel';

    protected $fillable = [
        'lembaga_id',
        'tahun_pelajaran_id',
        'jurusan_id',
        'tingkat',
        'nama',
        'wali_kelas',
        'kapasitas',
        'status',
    ];

    protected $casts = [
        'tingkat' => 'integer',
        'kapasitas' => 'integer',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function scopeByLembaga(Builder $query, ?int $lembagaId): Builder
    {
        return $lembagaId ? $query->where('lembaga_id', $lembagaId) : $query;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
