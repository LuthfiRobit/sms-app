<?php

namespace App\Models\Peserta;

use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaPeriodik extends Model
{
    protected $table = 'peserta_periodik';

    protected $fillable = [
        'peserta_id',
        'tahun_pelajaran_id',
        'tinggi_badan',
        'berat_badan',
        'lingkar_kepala',
        'jarak_rumah',
        'waktu_tempuh',
        'jumlah_saudara',
    ];

    /**
     * Get the peserta that owns the periodic parameter details.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    /**
     * Get the tahun pelajaran associated with the record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }
}
