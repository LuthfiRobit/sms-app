<?php

namespace App\Models\Kinerja;

use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KpiIndikator extends Model
{
    protected $table = 'kpi_indikator';

    protected $fillable = [
        'lembaga_id',
        'tahun_pelajaran_id',
        'nama_indikator',
        'deskripsi',
        'kategori',
        'satuan',
        'target',
        'sumber_data',
        'is_auto',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_auto' => 'boolean',
            'target'  => 'float',
            'urutan'  => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(KpiRealisasi::class, 'kpi_indikator_id');
    }

    public function latestRealisasi(): HasOne
    {
        return $this->hasOne(KpiRealisasi::class, 'kpi_indikator_id')->latest();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Pencapaian percentage: latestRealisasi / target * 100, or 0 if none.
     */
    public function getPencapaianAttribute(): float
    {
        if (! $this->relationLoaded('latestRealisasi')) {
            $this->load('latestRealisasi');
        }

        $realisasi = $this->latestRealisasi;

        if (! $realisasi || $this->target == 0) {
            return 0.0;
        }

        return (float) round(($realisasi->nilai_realisasi / $this->target) * 100, 2);
    }

    /**
     * Traffic-light status based on pencapaian percentage.
     * >= 90 → hijau, >= 70 → kuning, else → merah
     */
    public function getTrafficLightAttribute(): string
    {
        if (! $this->relationLoaded('latestRealisasi')) {
            $this->load('latestRealisasi');
        }

        if (! $this->latestRealisasi) {
            return 'tanpa_data';
        }

        $pencapaian = $this->pencapaian;

        if ($pencapaian >= 90) {
            return 'hijau';
        }

        if ($pencapaian >= 70) {
            return 'kuning';
        }

        return 'merah';
    }

    // -------------------------------------------------------------------------
    // Static Config
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // Additional Accessors
    // -------------------------------------------------------------------------

    public function getKategoriLabelAttribute(): string
    {
        return static::kategoriConfig()[$this->kategori]['label'] ?? ucfirst((string) $this->kategori);
    }

    public function getTrafficLightBadgeAttribute(): string
    {
        $tl = $this->traffic_light;

        if (! $this->latestRealisasi) {
            return '<span class="badge bg-secondary">Belum Ada Data</span>';
        }

        return match ($tl) {
            'hijau'  => '<span class="badge bg-success">Baik</span>',
            'kuning' => '<span class="badge bg-warning text-dark">Cukup</span>',
            'merah'  => '<span class="badge bg-danger">Kurang</span>',
            default  => '<span class="badge bg-secondary">—</span>',
        };
    }

    public function getIsAutoBadgeAttribute(): string
    {
        return $this->is_auto
            ? '<span class="badge bg-info text-white"><i class="bi bi-lightning-fill me-1"></i>Otomatis</span>'
            : '<span class="badge bg-light text-secondary border">Manual</span>';
    }

    // -------------------------------------------------------------------------
    // Static Config
    // -------------------------------------------------------------------------

    public static function kategoriConfig(): array
    {
        return [
            'akademik'      => ['label' => 'Akademik',            'color' => 'primary',   'icon' => 'bi-book'],
            'ppdb'          => ['label' => 'PPDB',                'color' => 'info',      'icon' => 'bi-person-plus'],
            'program_kerja' => ['label' => 'Program Kerja',       'color' => 'warning',   'icon' => 'bi-briefcase'],
            'kesiswaan'     => ['label' => 'Kesiswaan',           'color' => 'success',   'icon' => 'bi-people'],
            'sarpras'       => ['label' => 'Sarana & Prasarana',  'color' => 'secondary', 'icon' => 'bi-building'],
            'humas'         => ['label' => 'Humas & Kehumasan',   'color' => 'purple',    'icon' => 'bi-megaphone'],
            'umum'          => ['label' => 'Umum',                'color' => 'dark',      'icon' => 'bi-grid'],
        ];
    }
}
