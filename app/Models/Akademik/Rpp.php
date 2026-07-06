<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\ModelPembelajaran;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rpp extends Model
{
    protected $table = 'rpp';

    protected $fillable = [
        'lembaga_id', 'guru_id', 'mata_pelajaran_id', 'tahun_pelajaran_id', 'semester_id',
        'model_pembelajaran_id', 'fase_kelas', 'materi', 'alokasi_waktu',
        'status', 'catatan_revisi', 'diverifikasi_by', 'diverifikasi_at',
        'file_path', 'file_name',
    ];

    protected $casts = [
        'diverifikasi_at' => 'datetime',
    ];

    public static function statusBadge(string $status): array
    {
        return match ($status) {
            'pending'   => ['class' => 'warning text-dark', 'label' => 'Menunggu Verifikasi'],
            'disetujui' => ['class' => 'success',           'label' => 'Disetujui'],
            'ditolak'   => ['class' => 'danger',            'label' => 'Ditolak'],
            default     => ['class' => 'secondary',         'label' => ucfirst($status)],
        };
    }

    public function lembaga(): BelongsTo          { return $this->belongsTo(Lembaga::class); }
    public function guru(): BelongsTo             { return $this->belongsTo(Guru::class); }
    public function mataPelajaran(): BelongsTo    { return $this->belongsTo(MataPelajaran::class); }
    public function tahunPelajaran(): BelongsTo   { return $this->belongsTo(TahunPelajaran::class); }
    public function semester(): BelongsTo         { return $this->belongsTo(Semester::class); }
    public function modelPembelajaran(): BelongsTo { return $this->belongsTo(ModelPembelajaran::class); }
    public function diverifikasiOleh(): BelongsTo { return $this->belongsTo(User::class, 'diverifikasi_by', 'id_user'); }

    public function nilaiPoin(): HasMany
    {
        return $this->hasMany(RppPoinValue::class);
    }

    public function inti(): HasMany
    {
        return $this->hasMany(RppInti::class)->orderBy('urutan');
    }

    public function submateri(): HasMany
    {
        return $this->hasMany(RppSubmateri::class)->orderBy('urutan');
    }

    /**
     * Cari nilai poin tertentu dari relasi nilaiPoin yang sudah di-load —
     * dipakai form edit & PDF supaya lookup poin->nilai tidak N+1 query.
     */
    public function nilaiUntuk(RppPoin $poin): ?RppPoinValue
    {
        return $this->nilaiPoin->firstWhere('rpp_poin_id', $poin->id);
    }
}
