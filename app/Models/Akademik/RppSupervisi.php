<?php

namespace App\Models\Akademik;

use App\Models\Master\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RppSupervisi extends Model
{
    protected $table = 'rpp_supervisi';

    protected $fillable = [
        'rpp_id', 'lembaga_id', 'tanggal_supervisi',
        'nama_supervisor', 'nip_supervisor', 'jabatan_supervisor',
        'catatan_perencanaan', 'rtl_perencanaan',
        'catatan_pelaksanaan', 'rtl_pelaksanaan',
        'catatan_asesmen', 'rtl_asesmen',
        'file_path', 'file_name', 'created_by',
    ];

    protected $casts = [
        'tanggal_supervisi' => 'date',
    ];

    public function rpp(): BelongsTo
    {
        return $this->belongsTo(Rpp::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function skor(): HasMany
    {
        return $this->hasMany(RppSupervisiSkor::class);
    }

    /**
     * Hitung hasil satu instrumen (perencanaan/pelaksanaan/asesmen): gabungan
     * kriteria dari config + skor yang sudah tersimpan, plus Jumlah/Skor
     * Total/% Capaian/Predikat — dipakai bareng oleh form (nilai lama),
     * halaman show, dan PDF supaya rumusnya cuma ada di satu tempat.
     */
    public function hasil(string $instrumen): array
    {
        $config = config("rpp_supervisi.instrumen.{$instrumen}");
        $skorByKode = $this->skor->where('instrumen', $instrumen)->keyBy('kode');

        $kriteria = collect($config['kriteria'])->map(function ($k) use ($skorByKode) {
            $baris = $skorByKode->get($k['kode']);

            return [
                'kode' => $k['kode'],
                'tahap' => $k['tahap'],
                'teks' => $k['teks'],
                'skor' => $baris?->skor,
                'catatan' => $baris?->catatan,
            ];
        });

        $jumlahItem = $kriteria->count();
        $skorTotal = $kriteria->sum(fn ($k) => $k['skor'] ?? 0);
        $skorMaksimal = $jumlahItem * 3;
        $persenCapaian = $skorMaksimal > 0 ? round(($skorTotal / $skorMaksimal) * 100, 1) : 0;

        $predikat = 'Kurang';
        foreach ($config['predikat'] as $ambangBatas => $label) {
            if ($persenCapaian >= $ambangBatas) {
                $predikat = $label;
                break;
            }
        }

        return [
            'label' => $config['label'],
            'kriteria' => $kriteria,
            'jumlah_item' => $jumlahItem,
            'skor_total' => $skorTotal,
            'skor_maksimal' => $skorMaksimal,
            'persen_capaian' => $persenCapaian,
            'predikat' => $predikat,
        ];
    }
}
