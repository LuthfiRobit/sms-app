<?php

namespace App\Models\Peserta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaAlamat extends Model
{
    protected $table = 'peserta_alamat';

    protected $fillable = [
        'peserta_id',
        'alamat',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'rt',
        'rw',
        'dusun',
        'kode_pos',
        'lintang',
        'bujur',
    ];

    protected $casts = [
        'lintang' => 'decimal:6',
        'bujur' => 'decimal:6',
    ];

    /**
     * Get the peserta that owns the alamat.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }
}
