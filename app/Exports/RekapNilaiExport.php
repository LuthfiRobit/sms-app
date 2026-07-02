<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapNilaiExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected Collection $siswa,
        protected Collection $mapel,
        protected ?object    $rombel,
        protected ?object    $semester,
        protected ?object    $tahun,
    ) {}

    public function title(): string
    {
        return 'Rekap Nilai';
    }

    public function headings(): array
    {
        $heads = ['No', 'Nama Siswa'];
        foreach ($this->mapel as $m) {
            $heads[] = $m->nama;
        }
        $heads[] = 'Rata-rata';
        return $heads;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->siswa as $s) {
            $row = [$s->no_absen, $s->nama];
            foreach ($this->mapel as $m) {
                $row[] = $s->nilai[$m->id]->akhir ?? '-';
            }
            $row[] = $s->rata_rata ? round($s->rata_rata, 1) : '-';
            $rows[] = $row;
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
