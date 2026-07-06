<?php

namespace App\Services\Akademik;

use App\Models\Akademik\RppPoinValue;
use App\Repositories\Akademik\RppBagianRepositoryInterface;
use App\Repositories\Akademik\RppMasterOpsiRepositoryInterface;
use App\Repositories\Akademik\RppPoinRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\QueryException;
use RuntimeException;

/**
 * Mengelola STRUKTUR templat RPP (bagian, poin, opsi master) — bukan data RPP
 * guru itu sendiri (lihat RppService untuk itu). Ini yang bikin poin RPP
 * dinamis: admin bisa tambah/ubah/nonaktifkan/hapus dari sini tanpa migrasi.
 */
class RppTemplateService
{
    public function __construct(
        protected RppBagianRepositoryInterface $bagianRepo,
        protected RppPoinRepositoryInterface $poinRepo,
        protected RppMasterOpsiRepositoryInterface $opsiRepo,
        protected LogActivityService $logActivity,
    ) {}

    // ── Bagian ───────────────────────────────────────────────────────────────

    public function semuaBagian(): mixed
    {
        return $this->bagianRepo->all();
    }

    public function storeBagian(array $data): object
    {
        $bagian = $this->bagianRepo->create($data);
        $this->logActivity->log('Tambah Bagian RPP', "Bagian '{$bagian->nama}' ditambahkan.");

        return $bagian;
    }

    public function updateBagian(int $id, array $data): object
    {
        $bagian = $this->bagianRepo->update($id, $data);
        $this->logActivity->log('Update Bagian RPP', "Bagian '{$bagian->nama}' diperbarui.");

        return $bagian;
    }

    public function destroyBagian(int $id): void
    {
        try {
            $this->bagianRepo->delete($id);
            $this->logActivity->log('Hapus Bagian RPP', "Bagian RPP #{$id} dihapus.");
        } catch (QueryException) {
            throw new RuntimeException('Bagian ini masih punya poin yang sudah dipakai RPP. Nonaktifkan poinnya dulu, atau pindahkan ke bagian lain.');
        }
    }

    // ── Poin ─────────────────────────────────────────────────────────────────

    public function storePoin(array $data): object
    {
        $poin = $this->poinRepo->create($data);
        $this->logActivity->log('Tambah Poin RPP', "Poin '{$poin->label}' ditambahkan.");

        return $poin;
    }

    public function updatePoin(int $id, array $data): object
    {
        $poin = $this->poinRepo->update($id, $data);
        $this->logActivity->log('Update Poin RPP', "Poin '{$poin->label}' diperbarui.");

        return $poin;
    }

    public function destroyPoin(int $id): void
    {
        try {
            $poin = $this->poinRepo->findById($id);
            $this->poinRepo->delete($id);
            $this->logActivity->log('Hapus Poin RPP', "Poin '{$poin?->label}' dihapus.");
        } catch (QueryException) {
            throw new RuntimeException('Poin ini sudah punya data RPP. Nonaktifkan saja, jangan dihapus, supaya data lama tidak hilang.');
        }
    }

    public function reorderPoin(array $urutanByid): void
    {
        $this->poinRepo->reorder($urutanByid);
    }

    // ── Opsi Master (pilih_master) ──────────────────────────────────────────

    public function opsiByKategori(string $kategori): mixed
    {
        return $this->opsiRepo->byKategori($kategori);
    }

    public function storeOpsi(array $data): object
    {
        return $this->opsiRepo->create($data);
    }

    public function updateOpsi(int $id, array $data): object
    {
        return $this->opsiRepo->update($id, $data);
    }

    public function destroyOpsi(int $id): void
    {
        // Tidak ada FK sungguhan dari rpp_poin_value ke rpp_master_opsi (id-nya
        // cuma disimpan longgar di dalam value_json poin bertipe pilih_master),
        // jadi proteksi hapus harus dicek manual lewat isi JSON-nya.
        $masihDipakai = RppPoinValue::whereJsonContains('value_json', $id)->exists();

        if ($masihDipakai) {
            throw new RuntimeException('Opsi ini sudah dipilih di salah satu RPP. Nonaktifkan saja, jangan dihapus.');
        }

        $this->opsiRepo->delete($id);
    }
}
