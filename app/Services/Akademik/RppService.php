<?php

namespace App\Services\Akademik;

use App\Models\Akademik\Rpp;
use App\Models\Akademik\RppBagian;
use App\Models\Akademik\RppInti;
use App\Models\Akademik\RppPoin;
use App\Models\Akademik\RppPoinValue;
use App\Models\Master\ModelPembelajaranSintaks;
use App\Repositories\Akademik\RppRepositoryInterface;
use App\Services\LogActivityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Mengelola DATA RPP milik guru (lihat RppTemplateService untuk struktur
 * bagian/poin-nya). Isi tiap poin disimpan lewat rpp_poin_value (EAV) sesuai
 * definisi RppPoin yang aktif saat data disimpan — bukan kolom tetap.
 */
class RppService
{
    public function __construct(
        protected RppRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): mixed
    {
        $filters = $lembagaId ? ['lembaga_id' => $lembagaId] : [];

        return $this->repo->datatable($filters);
    }

    public function find(int $id): Rpp
    {
        $rpp = $this->repo->findById($id);

        if (! $rpp) {
            throw new RuntimeException('RPP tidak ditemukan.');
        }

        return $rpp;
    }

    public function store(array $data): Rpp
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = 'pending';
            $rpp = $this->repo->create($data);

            $this->syncPoinValues($rpp, $data['poin'] ?? []);
            $this->syncInti($rpp, $data['inti'] ?? []);
            $this->syncSubmateri($rpp, $data['submateri'] ?? '');
            $this->generateDanSimpanPdf($rpp);

            $this->logActivity->log('Tambah RPP', "RPP '{$rpp->materi}' ditambahkan.");

            return $rpp->fresh();
        });
    }

    public function update(int $id, array $data): Rpp
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->find($id);

            // Edit setelah pernah diverifikasi mereset ke pending — mirror
            // MateriBelajarController::update() (materi_belajar juga begitu).
            if ($existing->status !== 'pending') {
                $data['status'] = 'pending';
                $data['catatan_revisi'] = null;
                $data['diverifikasi_by'] = null;
                $data['diverifikasi_at'] = null;
            }

            $rpp = $this->repo->update($id, $data);

            if ((int) $existing->model_pembelajaran_id !== (int) ($data['model_pembelajaran_id'] ?? $existing->model_pembelajaran_id)) {
                $rpp->inti()->delete();
            }

            $this->syncPoinValues($rpp, $data['poin'] ?? []);
            $this->syncInti($rpp, $data['inti'] ?? []);
            $this->syncSubmateri($rpp, $data['submateri'] ?? '');
            $this->generateDanSimpanPdf($rpp);

            $this->logActivity->log('Update RPP', "RPP '{$rpp->materi}' diperbarui.");

            return $rpp->fresh();
        });
    }

    /**
     * Salin RPP jadi draft baru — guru mulai dari konten yang sudah ada
     * (mis. RPP tahun lalu) daripada mengisi dari kosong. Tahun ajaran &
     * semester SENGAJA jadi parameter (bukan ikut nilai sumbernya) karena
     * kasus paling umum adalah menyalin RPP tahun lalu untuk tahun baru.
     * Status selalu direset ke 'pending' walau sumbernya sudah disetujui —
     * konten hasil salinan tetap perlu diverifikasi ulang.
     */
    public function duplicate(int $id, int $tahunPelajaranId, int $semesterId): Rpp
    {
        return DB::transaction(function () use ($id, $tahunPelajaranId, $semesterId) {
            $sumber = $this->find($id);
            $sumber->load('nilaiPoin', 'inti', 'submateri');

            $salinan = $this->repo->create([
                'lembaga_id' => $sumber->lembaga_id,
                'guru_id' => $sumber->guru_id,
                'mata_pelajaran_id' => $sumber->mata_pelajaran_id,
                'tahun_pelajaran_id' => $tahunPelajaranId,
                'semester_id' => $semesterId,
                'model_pembelajaran_id' => $sumber->model_pembelajaran_id,
                'fase_kelas' => $sumber->fase_kelas,
                'materi' => $sumber->materi.' (Salinan)',
                'alokasi_waktu' => $sumber->alokasi_waktu,
                'status' => 'pending',
            ]);

            foreach ($sumber->nilaiPoin as $nilai) {
                RppPoinValue::create([
                    'rpp_id' => $salinan->id,
                    'rpp_poin_id' => $nilai->rpp_poin_id,
                    'value_teks' => $nilai->value_teks,
                    'value_json' => $nilai->value_json,
                ]);
            }

            foreach ($sumber->inti as $inti) {
                RppInti::create([
                    'rpp_id' => $salinan->id,
                    'model_pembelajaran_sintaks_id' => $inti->model_pembelajaran_sintaks_id,
                    'konten' => $inti->konten,
                    'urutan' => $inti->urutan,
                ]);
            }

            foreach ($sumber->submateri as $submateri) {
                $salinan->submateri()->create([
                    'teks' => $submateri->teks,
                    'urutan' => $submateri->urutan,
                ]);
            }

            $this->generateDanSimpanPdf($salinan);

            $this->logActivity->log('Duplikat RPP', "RPP '{$sumber->materi}' (ID #{$sumber->id}) diduplikat menjadi '{$salinan->materi}' (ID #{$salinan->id}).");

            return $salinan->fresh();
        });
    }

    public function destroy(int $id): void
    {
        $rpp = $this->repo->findById($id);

        if ($rpp && $rpp->file_path) {
            Storage::disk('public')->delete($rpp->file_path);
        }

        $materi = $rpp?->materi ?? (string) $id;
        // rpp_poin_value & rpp_inti ikut terhapus otomatis lewat cascadeOnDelete().
        $this->repo->delete($id);
        $this->logActivity->log('Hapus RPP', "RPP '{$materi}' dihapus.");
    }

    public function verifikasi(int $id, string $aksi, ?string $catatan): Rpp
    {
        $rpp = $this->find($id);

        if ($rpp->status !== 'pending') {
            throw new RuntimeException('RPP ini sudah diverifikasi sebelumnya.');
        }

        $rpp->update([
            'status' => $aksi,
            'catatan_revisi' => $catatan,
            'diverifikasi_by' => auth()->user()->id_user,
            'diverifikasi_at' => now(),
        ]);

        $aksiLabel = $aksi === 'disetujui' ? 'menyetujui' : 'menolak';
        $this->logActivity->log('Verifikasi RPP', "Admin {$aksiLabel} RPP \"{$rpp->materi}\" (ID #{$id}).");

        return $rpp;
    }

    // ── Sinkronisasi isi poin & inti ─────────────────────────────────────────

    /** @param array<int,mixed> $poinInput [rpp_poin_id => nilai mentah dari form] */
    protected function syncPoinValues(Rpp $rpp, array $poinInput): void
    {
        $poinAktif = RppPoin::aktif()->get();

        foreach ($poinAktif as $poin) {
            // Blok Inti tidak lewat rpp_poin_value — ditangani syncInti().
            if ($poin->tipe === 'model_pembelajaran') {
                continue;
            }

            if (! array_key_exists($poin->id, $poinInput)) {
                continue;
            }

            $raw = $poinInput[$poin->id];

            [$valueTeks, $valueJson] = match ($poin->tipe) {
                'teks', 'teks_panjang' => [is_string($raw) ? trim($raw) : null, null],
                'daftar_poin' => [null, $this->parseDaftarPoin($raw)],
                'pasangan_kolom' => [null, $this->parsePasanganKolom($raw)],
                'pilih_master' => [null, array_values(array_filter((array) $raw, fn ($v) => $v !== null && $v !== ''))],
                default => [null, null],
            };

            RppPoinValue::updateOrCreate(
                ['rpp_id' => $rpp->id, 'rpp_poin_id' => $poin->id],
                ['value_teks' => $valueTeks, 'value_json' => $valueJson]
            );
        }
    }

    /** @param array<int,mixed> $intiInput [model_pembelajaran_sintaks_id => baris teks per sintaks] */
    protected function syncInti(Rpp $rpp, array $intiInput): void
    {
        $sintaksAktif = ModelPembelajaranSintaks::where('model_pembelajaran_id', $rpp->model_pembelajaran_id)->get();

        // Buang baris rpp_inti yang sintaks-nya sudah tidak cocok dengan model
        // terpilih (mis. guru ganti Model Pembelajaran saat edit).
        $rpp->inti()->whereNotIn('model_pembelajaran_sintaks_id', $sintaksAktif->pluck('id'))->delete();

        foreach ($sintaksAktif as $sintaks) {
            RppInti::updateOrCreate(
                ['rpp_id' => $rpp->id, 'model_pembelajaran_sintaks_id' => $sintaks->id],
                ['konten' => $this->parseDaftarPoin($intiInput[$sintaks->id] ?? []), 'urutan' => $sintaks->urutan]
            );
        }
    }

    /**
     * Submateri — daftar tak terbatas, satu input per baris (bukan textarea
     * "satu per baris" seperti daftar_poin, supaya submateri yang panjang
     * tidak berisiko kepotong kalau guru salah pencet Enter). Diganti
     * seluruhnya tiap simpan (bukan diff) karena tidak ada relasi lain yang
     * mereferensikan baris submateri per-ID (beda dari sintaks/inti).
     */
    protected function syncSubmateri(Rpp $rpp, mixed $raw): void
    {
        $rpp->submateri()->delete();

        $items = array_values(array_filter(array_map('trim', (array) $raw), fn ($v) => $v !== ''));

        foreach ($items as $i => $teks) {
            $rpp->submateri()->create(['teks' => $teks, 'urutan' => $i + 1]);
        }
    }

    /** Textarea "satu poin per baris" ATAU array yang sudah terpecah -> array bersih tanpa baris kosong. */
    protected function parseDaftarPoin(mixed $raw): array
    {
        $items = is_array($raw) ? $raw : explode("\n", (string) $raw);

        return array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));
    }

    /** Baris {kolom1,kolom2} berulang -> array bersih, baris yang dua kolomnya kosong dibuang. */
    protected function parsePasanganKolom(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->filter(fn ($row) => is_array($row) && (trim($row['kolom1'] ?? '') !== '' || trim($row['kolom2'] ?? '') !== ''))
            ->map(fn ($row) => ['kolom1' => trim($row['kolom1'] ?? ''), 'kolom2' => trim($row['kolom2'] ?? '')])
            ->values()
            ->all();
    }

    // ── PDF ──────────────────────────────────────────────────────────────────

    protected function generateDanSimpanPdf(Rpp $rpp): void
    {
        $rpp->load([
            'guru', 'mataPelajaran', 'tahunPelajaran', 'semester', 'lembaga',
            'modelPembelajaran.sintaks', 'nilaiPoin.poin', 'inti.sintaks', 'submateri',
        ]);

        $bagianList = RppBagian::aktif()
            ->with(['poin' => fn ($q) => $q->aktif()->orderBy('urutan')])
            ->orderBy('urutan')
            ->get();

        $pdf = Pdf::loadView('pdf.akademik.rpp', ['rpp' => $rpp, 'bagianList' => $bagianList])
            ->setPaper('A4', 'portrait');

        if ($rpp->file_path) {
            Storage::disk('public')->delete($rpp->file_path);
        }

        $path = 'akademik/rpp/rpp-'.$rpp->id.'-'.time().'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $rpp->update([
            'file_path' => $path,
            'file_name' => 'RPP - '.$rpp->materi.'.pdf',
        ]);
    }
}
