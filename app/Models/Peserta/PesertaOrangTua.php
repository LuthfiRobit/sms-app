<?php

namespace App\Models\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaOrangTua extends Model
{
    protected $table = 'peserta_orang_tua';

    const TIPE_AYAH = 'ayah';
    const TIPE_IBU = 'ibu';
    const TIPE_WALI = 'wali';

    protected $fillable = [
        'peserta_id',
        'tipe',
        'nama',
        'nik',
        'pekerjaan',
        'penghasilan',
        'pendidikan',
        'kebutuhan_khusus',
        'no_hp',
    ];

    /**
     * Get the peserta that owns the records of parents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    /**
     * Scope a query to only include ayah.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAyah(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_AYAH);
    }

    /**
     * Scope a query to only include ibu.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIbu(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_IBU);
    }

    /**
     * Scope a query to only include wali.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWali(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_WALI);
    }
}
