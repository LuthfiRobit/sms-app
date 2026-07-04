<?php

namespace App\Models\Akademik;

use App\Models\Master\Lembaga;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class KalenderLibur extends Model
{
    protected $table = 'kalender_libur';

    protected $fillable = [
        'lembaga_id', 'tanggal', 'keterangan', 'jenis', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }

    /**
     * Apakah tanggal ini hari libur bagi lembaga tsb (Minggu selalu libur,
     * ditambah baris kalender_libur khusus lembaga atau global/lembaga_id=null).
     *
     * Aturan Minggu sengaja ditaruh di sini, bukan di WaktuSekolah::hari(),
     * karena itu konsep berbeda: jadwal kelas vs kewajiban hadir kerja.
     */
    public static function isLibur(string $tanggal, ?int $lembagaId): bool
    {
        if (Carbon::parse($tanggal)->dayOfWeekIso === 7) {
            return true;
        }

        return static::whereDate('tanggal', $tanggal)
            ->where(fn ($q) => $q->whereNull('lembaga_id')->orWhere('lembaga_id', $lembagaId))
            ->exists();
    }
}
