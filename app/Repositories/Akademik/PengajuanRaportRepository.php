<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\PengajuanRaport;
use App\Models\Akademik\RaportAbsensiRekap;
use App\Models\Akademik\RaportNilai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PengajuanRaportRepository implements PengajuanRaportRepositoryInterface
{
    public function __construct(
        protected PengajuanRaport $model,
        protected RaportNilai $nilaiModel,
        protected RaportAbsensiRekap $absensiRekapModel,
    ) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model
            ->with([
                'rombel:id,nama,tingkat,wali_kelas',
                'semester:id,nama',
                'lembaga:id,nama',
                'tahunPelajaran:id,nama',
            ]);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        return $query;
    }

    public function findById(int $id, array $with = []): ?object
    {
        $defaultWith = [
            'rombel',
            'semester',
            'tahunPelajaran',
            'lembaga',
            'raportNilai.mataPelajaran',
            'raportNilai.peserta',
            'absensiRekap.peserta',
        ];

        $relations = empty($with) ? $defaultWith : $with;

        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    public function getNilaiByPengajuan(int $pengajuanId): Collection
    {
        return $this->nilaiModel
            ->with([
                'peserta:id,nama_lengkap',
                'mataPelajaran:id,nama,kode,urutan',
            ])
            ->where('pengajuan_raport_id', $pengajuanId)
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'raport_nilai.mata_pelajaran_id')
            ->orderBy('mata_pelajaran.urutan')
            ->orderBy('mata_pelajaran.nama')
            ->select('raport_nilai.*')
            ->get();
    }

    public function getAbsensiRekap(int $pengajuanId): Collection
    {
        return $this->absensiRekapModel
            ->with(['peserta:id,nama_lengkap'])
            ->where('pengajuan_raport_id', $pengajuanId)
            ->get();
    }

    public function upsertNilai(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        RaportNilai::upsert(
            $rows,
            ['pengajuan_raport_id', 'peserta_id', 'mata_pelajaran_id'],
            ['nilai_harian', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'predikat', 'catatan_guru']
        );
    }

    public function upsertAbsensiRekap(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        RaportAbsensiRekap::upsert(
            $rows,
            ['pengajuan_raport_id', 'peserta_id'],
            ['hadir', 'sakit', 'izin', 'alpa']
        );
    }
}
