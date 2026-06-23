<?php

namespace App\Models\Akademik;

use App\Models\Master\Lembaga;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanRaport extends Model
{
    protected $table = 'pengajuan_raport';

    protected $fillable = [
        'lembaga_id',
        'rombel_id',
        'semester_id',
        'tahun_pelajaran_id',
        'dibuat_oleh',
        'status',
        'catatan_pengajuan',
        'catatan_verifikasi',
        'catatan_approval',
        'diajukan_at',
        'diverifikasi_at',
        'disetujui_at',
        'diverifikasi_by',
        'disetujui_by',
    ];

    protected $casts = [
        'diajukan_at'     => 'datetime',
        'diverifikasi_at' => 'datetime',
        'disetujui_at'    => 'datetime',
        'status'          => 'string',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_by', 'id_user');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_by', 'id_user');
    }

    public function raportNilai(): HasMany
    {
        return $this->hasMany(RaportNilai::class, 'pengajuan_raport_id');
    }

    public function absensiRekap(): HasMany
    {
        return $this->hasMany(RaportAbsensiRekap::class, 'pengajuan_raport_id');
    }

    public static function statusBadge(string $status): array
    {
        return match ($status) {
            'draft'        => ['class' => 'secondary', 'label' => 'Draft'],
            'diajukan'     => ['class' => 'primary',   'label' => 'Diajukan'],
            'diverifikasi' => ['class' => 'info',      'label' => 'Diverifikasi'],
            'ditolak'      => ['class' => 'danger',    'label' => 'Ditolak'],
            'disetujui'    => ['class' => 'success',   'label' => 'Disetujui'],
            default        => ['class' => 'secondary', 'label' => ucfirst($status)],
        };
    }
}
