<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiGuruExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(protected Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Rekap Absensi Guru';
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Guru', 'Lembaga', 'Tanggal', 'Jam Masuk', 'Jam Pulang',
            'Status', 'Jarak Masuk (m)', 'Jarak Pulang (m)', 'Lokasi Palsu', 'Koreksi Manual',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->guru?->nama_lengkap ?? '—',
            $row->lembaga?->nama ?? '—',
            $row->tanggal?->format('d/m/Y'),
            $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '—',
            $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '—',
            ucfirst($row->status),
            $row->jarak_masuk_m ?? '—',
            $row->jarak_pulang_m ?? '—',
            $row->flag_mock_location ? 'Ya' : 'Tidak',
            $row->is_koreksi_manual ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
