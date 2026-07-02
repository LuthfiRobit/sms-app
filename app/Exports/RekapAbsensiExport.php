<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected Collection $rekap,
        protected ?object    $rombel,
        protected string     $tanggalMulai,
        protected string     $tanggalAkhir,
    ) {}

    public function collection(): Collection
    {
        return $this->rekap;
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }

    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Total', '% Hadir'];
    }

    public function map($row): array
    {
        return [
            $row->no_absen,
            $row->nama,
            $row->hadir,
            $row->sakit,
            $row->izin,
            $row->alpa,
            $row->total,
            $row->persen_hadir . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
