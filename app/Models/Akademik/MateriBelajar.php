<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriBelajar extends Model
{
    protected $table = 'materi_belajar';

    protected $fillable = [
        'lembaga_id', 'guru_id', 'mata_pelajaran_id', 'rombel_id',
        'tahun_pelajaran_id', 'pertemuan_ke', 'judul', 'deskripsi',
        'file_path', 'file_name', 'url_eksternal', 'tanggal', 'status',
        'catatan_revisi', 'diverifikasi_by', 'diverifikasi_at',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'diverifikasi_at' => 'datetime',
        'pertemuan_ke'    => 'integer',
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

    public function lembaga(): BelongsTo        { return $this->belongsTo(Lembaga::class); }
    public function guru(): BelongsTo           { return $this->belongsTo(Guru::class); }
    public function mataPelajaran(): BelongsTo  { return $this->belongsTo(MataPelajaran::class); }
    public function rombel(): BelongsTo         { return $this->belongsTo(Rombel::class); }
    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }
    public function diverifikasiOleh(): BelongsTo { return $this->belongsTo(User::class, 'diverifikasi_by', 'id_user'); }
}
