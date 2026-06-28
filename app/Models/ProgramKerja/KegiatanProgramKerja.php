<?php

namespace App\Models\ProgramKerja;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanProgramKerja extends Model
{
    protected $table = 'kegiatan_program_kerja';

    protected $fillable = [
        'program_kerja_id',
        'nama_kegiatan',
        'deskripsi',
        'penanggung_jawab',
        'target',
        'indikator',
        'anggaran',
        'bulan_mulai',
        'bulan_selesai',
        'realisasi_anggaran',
        'realisasi_persen',
        'status_kegiatan',
        'catatan_realisasi',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'anggaran'           => 'float',
            'realisasi_anggaran' => 'float',
            'realisasi_persen'   => 'integer',
            'bulan_mulai'        => 'integer',
            'bulan_selesai'      => 'integer',
            'urutan'             => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function programKerja(): BelongsTo
    {
        return $this->belongsTo(ProgramKerja::class, 'program_kerja_id');
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the Indonesian month name for a given month number (1–12).
     */
    public static function bulanNama(int $bulan): string
    {
        $bulanMap = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulanMap[$bulan] ?? '-';
    }

    public static function statusConfig(): array
    {
        return [
            'belum'      => ['class' => 'secondary', 'label' => 'Belum Mulai'],
            'proses'     => ['class' => 'warning',   'label' => 'Sedang Berjalan'],
            'selesai'    => ['class' => 'success',   'label' => 'Selesai'],
            'dibatalkan' => ['class' => 'danger',    'label' => 'Dibatalkan'],
        ];
    }
}
