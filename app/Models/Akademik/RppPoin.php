<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RppPoin extends Model
{
    protected $table = 'rpp_poin';

    protected $fillable = [
        'rpp_bagian_id', 'kode', 'label', 'tipe',
        'kolom1_label', 'kolom2_label', 'master_kategori',
        'is_required', 'urutan', 'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(RppBagian::class, 'rpp_bagian_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(RppPoinValue::class);
    }

    /** Daftar opsi untuk poin bertipe pilih_master, diambil dari kategori-nya di rpp_master_opsi. */
    public function opsiMaster()
    {
        if ($this->tipe !== 'pilih_master' || ! $this->master_kategori) {
            return collect();
        }

        return RppMasterOpsi::kategori($this->master_kategori)->aktif()->orderBy('urutan')->get();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
