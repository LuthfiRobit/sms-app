<?php

namespace App\Models\ProgramKerja;

use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramKerja extends Model
{
    protected $table = 'program_kerja';

    protected $fillable = [
        'lembaga_id',
        'tahun_pelajaran_id',
        'bidang',
        'nama_program',
        'deskripsi',
        'tujuan',
        'status',
        'dibuat_oleh',
        'diajukan_at',
        'catatan_pengajuan',
        'diverifikasi_by',
        'diverifikasi_at',
        'catatan_verifikasi',
        'disetujui_by',
        'disetujui_at',
        'catatan_approval',
        'catatan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'diajukan_at'     => 'datetime',
            'diverifikasi_at' => 'datetime',
            'disetujui_at'    => 'datetime',
            'status'          => 'string',
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

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }

    public function diverifikasiBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_by', 'id_user');
    }

    public function disetujuiBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_by', 'id_user');
    }

    public function kegiatanProgramKerja(): HasMany
    {
        return $this->hasMany(KegiatanProgramKerja::class, 'program_kerja_id')
            ->orderBy('urutan')
            ->orderBy('nama_kegiatan');
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanProgramKerja::class, 'program_kerja_id')
            ->orderBy('urutan')
            ->orderBy('nama_kegiatan');
    }

    // -------------------------------------------------------------------------
    // Static Config
    // -------------------------------------------------------------------------

    public static function statusConfig(): array
    {
        return [
            'draft'        => ['class' => 'secondary', 'label' => 'Draft'],
            'diajukan'     => ['class' => 'primary',   'label' => 'Diajukan'],
            'diverifikasi' => ['class' => 'info',      'label' => 'Diverifikasi'],
            'ditolak'      => ['class' => 'danger',    'label' => 'Ditolak'],
            'disetujui'    => ['class' => 'warning',   'label' => 'Disetujui'],
            'aktif'        => ['class' => 'success',   'label' => 'Aktif'],
            'selesai'      => ['class' => 'dark',      'label' => 'Selesai'],
        ];
    }

    public static function bidangConfig(): array
    {
        return [
            'kesiswaan' => 'Kesiswaan',
            'sarpras'   => 'Sarana & Prasarana',
            'humas'     => 'Humas & Kehumasan',
            'kurikulum' => 'Kurikulum',
            'umum'      => 'Administrasi Umum',
        ];
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Percentage of kegiatan with status_kegiatan = 'selesai' out of total kegiatan.
     */
    public function getProgressAttribute(): int
    {
        $kegiatan = $this->kegiatan;

        $total = $kegiatan->count();

        if ($total === 0) {
            return 0;
        }

        $selesai = $kegiatan->where('status_kegiatan', 'selesai')->count();

        return (int) round(($selesai / $total) * 100);
    }

    /**
     * Sum of all kegiatan->anggaran.
     */
    public function getTotalAnggaranAttribute(): float
    {
        return (float) $this->kegiatan->sum('anggaran');
    }

    /**
     * Sum of all kegiatan->realisasi_anggaran (non-null values only).
     */
    public function getTotalRealisasiAnggaranAttribute(): float
    {
        return (float) $this->kegiatan->whereNotNull('realisasi_anggaran')->sum('realisasi_anggaran');
    }
}
