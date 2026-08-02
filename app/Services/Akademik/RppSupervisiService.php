<?php

namespace App\Services\Akademik;

use App\Models\Akademik\Rpp;
use App\Models\Akademik\RppSupervisi;
use App\Models\Master\ProfilSekolah;
use App\Repositories\Akademik\RppSupervisiRepositoryInterface;
use App\Services\LogActivityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Mengelola sesi Instrumen Supervisi RPP (Perencanaan/Pelaksanaan/Asesmen
 * Pembelajaran, skor 0-3 per kriteria). Kriteria & ambang predikat berasal
 * dari config('rpp_supervisi') — lihat RppSupervisi::hasil() untuk kalkulasinya.
 */
class RppSupervisiService
{
    public function __construct(
        protected RppSupervisiRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): mixed
    {
        $filters = $lembagaId ? ['lembaga_id' => $lembagaId] : [];

        return $this->repo->datatable($filters);
    }

    public function find(int $id): RppSupervisi
    {
        $supervisi = $this->repo->findById($id);

        if (! $supervisi) {
            throw new RuntimeException('Data supervisi RPP tidak ditemukan.');
        }

        return $supervisi;
    }

    public function store(array $data): RppSupervisi
    {
        return DB::transaction(function () use ($data) {
            $rpp = Rpp::find($data['rpp_id']);

            if (! $rpp) {
                throw new RuntimeException('RPP yang akan disupervisi tidak ditemukan.');
            }

            $supervisi = $this->repo->create([
                ...$this->onlyHeaderFields($data),
                'rpp_id' => $rpp->id,
                'lembaga_id' => $rpp->lembaga_id,
                'created_by' => auth()->user()->id_user,
            ]);

            $this->syncSkor($supervisi, $data['skor'] ?? []);
            $this->generateDanSimpanPdf($supervisi);

            $this->logActivity->log('Tambah Supervisi RPP', "Supervisi ditambahkan untuk RPP \"{$rpp->materi}\".");

            return $supervisi->fresh(['rpp', 'skor']);
        });
    }

    public function update(int $id, array $data): RppSupervisi
    {
        return DB::transaction(function () use ($id, $data) {
            $supervisi = $this->find($id);
            $supervisi->update($this->onlyHeaderFields($data));

            $this->syncSkor($supervisi, $data['skor'] ?? []);
            $this->generateDanSimpanPdf($supervisi);

            $this->logActivity->log('Update Supervisi RPP', "Supervisi RPP \"{$supervisi->rpp?->materi}\" (ID #{$id}) diperbarui.");

            return $supervisi->fresh(['rpp', 'skor']);
        });
    }

    public function destroy(int $id): void
    {
        $supervisi = $this->repo->findById($id);

        if ($supervisi && $supervisi->file_path) {
            Storage::disk('public')->delete($supervisi->file_path);
        }

        $materi = $supervisi?->rpp?->materi ?? (string) $id;
        $this->repo->delete($id);
        $this->logActivity->log('Hapus Supervisi RPP', "Supervisi RPP \"{$materi}\" (ID #{$id}) dihapus.");
    }

    /** @param array<string,mixed> $data Payload form — buang 'rpp_id'/'skor' yang ditangani terpisah. */
    protected function onlyHeaderFields(array $data): array
    {
        return collect($data)->only([
            'tanggal_supervisi', 'nama_supervisor', 'nip_supervisor', 'jabatan_supervisor',
            'catatan_perencanaan', 'rtl_perencanaan',
            'catatan_pelaksanaan', 'rtl_pelaksanaan',
            'catatan_asesmen', 'rtl_asesmen',
        ])->all();
    }

    /** @param array<string,array<string,array{skor?:int,catatan?:string}>> $skorInput [instrumen][kode] => nilai */
    protected function syncSkor(RppSupervisi $supervisi, array $skorInput): void
    {
        foreach (config('rpp_supervisi.instrumen') as $instrumen => $config) {
            foreach ($config['kriteria'] as $kriteria) {
                $baris = $skorInput[$instrumen][$kriteria['kode']] ?? null;

                if (! $baris || ! isset($baris['skor'])) {
                    continue;
                }

                $supervisi->skor()->updateOrCreate(
                    ['instrumen' => $instrumen, 'kode' => $kriteria['kode']],
                    ['skor' => (int) $baris['skor'], 'catatan' => $baris['catatan'] ?? null]
                );
            }
        }
    }

    // ── PDF ──────────────────────────────────────────────────────────────────

    protected function generateDanSimpanPdf(RppSupervisi $supervisi): void
    {
        $supervisi->load(['rpp.guru', 'rpp.mataPelajaran', 'rpp.tahunPelajaran', 'rpp.semester', 'rpp.lembaga', 'skor']);
        $profilSekolah = ProfilSekolah::first();

        $hasil = [
            'perencanaan' => $supervisi->hasil('perencanaan'),
            'pelaksanaan' => $supervisi->hasil('pelaksanaan'),
            'asesmen' => $supervisi->hasil('asesmen'),
        ];

        $pdf = Pdf::loadView('pdf.akademik.supervisi-rpp', compact('supervisi', 'profilSekolah', 'hasil'))
            ->setPaper('A4', 'portrait');

        if ($supervisi->file_path) {
            Storage::disk('public')->delete($supervisi->file_path);
        }

        $path = 'akademik/supervisi-rpp/supervisi-'.$supervisi->id.'-'.time().'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $supervisi->update([
            'file_path' => $path,
            'file_name' => 'Supervisi RPP - '.$supervisi->rpp->materi.'.pdf',
        ]);
    }
}
