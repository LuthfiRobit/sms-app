<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\TemplateDokumen;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TemplateDokumenService
{
    public function __construct(
        protected LogActivityService $logActivity,
        protected ResponseService    $response
    ) {}

    // =========================================================================
    // INDEX — Tampilkan semua template
    // =========================================================================

    /**
     * Mengembalikan semua template dokumen dengan info aktif/tidak.
     *
     * @return array
     */
    public function index(): array
    {
        try {
            $templates = TemplateDokumen::orderBy('tipe')->orderByDesc('is_aktif')->orderByDesc('created_at')->get();

            return [
                'success' => true,
                'message' => 'Data template dokumen berhasil diambil.',
                'data'    => $templates,
            ];
        } catch (Exception $e) {
            Log::error('[TemplateDokumenService::index] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data template.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // STORE — Upload PDF dan simpan record
    // =========================================================================

    /**
     * Upload file PDF, simpan dengan nama terstruktur, buat record database.
     * Jika is_aktif = true, nonaktifkan template lain dengan tipe yang sama.
     *
     * Naming convention file: template_{tipe}_{timestamp}.pdf
     *
     * @param  array        $data
     * @param  UploadedFile $file
     * @param  int          $userId
     * @return array
     */
    public function store(array $data, UploadedFile $file, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Simpan file dengan nama terstruktur
            $fileName = 'template_' . $data['tipe'] . '_' . time() . '.pdf';
            $filePath = Storage::disk('public')->putFileAs('ppdb/templates', $file, $fileName);

            if (!$filePath) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Gagal mengupload file template.',
                    'data'    => null,
                ];
            }

            // Jika is_aktif = true, nonaktifkan template lain dengan tipe yang sama
            $isAktif = (bool) ($data['is_aktif'] ?? false);
            if ($isAktif) {
                TemplateDokumen::where('tipe', $data['tipe'])->update(['is_aktif' => false]);
            }

            $template = TemplateDokumen::create([
                'nama'          => $data['nama'],
                'tipe'          => $data['tipe'],
                'file_template' => $filePath,
                'deskripsi'     => $data['deskripsi'] ?? null,
                'is_aktif'      => $isAktif,
            ]);

            $this->logActivity->log(
                'Upload Template Dokumen',
                "Mengupload Template: \"{$template->nama}\" (Tipe: {$template->tipe}) — File: {$fileName}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Template dokumen berhasil diupload.',
                'data'    => $template,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[TemplateDokumenService::store] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan template: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // SHOW — Detail template
    // =========================================================================

    /**
     * @param  int $id
     * @return array
     */
    public function show(int $id): array
    {
        try {
            $template = TemplateDokumen::find($id);
            if (!$template) {
                return [
                    'success' => false,
                    'message' => "Template dokumen dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail template berhasil diambil.',
                'data'    => $template,
            ];
        } catch (Exception $e) {
            Log::error('[TemplateDokumenService::show] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil detail template.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // UPDATE — Update data, ganti file jika ada yang baru
    // =========================================================================

    /**
     * Memperbarui data template. Jika ada file baru, hapus file lama dari storage.
     *
     * @param  int              $id
     * @param  array            $data
     * @param  UploadedFile|null $file
     * @param  int              $userId
     * @return array
     */
    public function update(int $id, array $data, ?UploadedFile $file, int $userId): array
    {
        DB::beginTransaction();
        try {
            $template = TemplateDokumen::find($id);
            if (!$template) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Template dokumen dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Jika ada file baru, hapus file lama dan upload yang baru
            if ($file) {
                // Hapus file lama dari storage jika ada
                if ($template->file_template && Storage::disk('public')->exists($template->file_template)) {
                    Storage::disk('public')->delete($template->file_template);
                }

                $fileName = 'template_' . $data['tipe'] . '_' . time() . '.pdf';
                $filePath = Storage::disk('public')->putFileAs('ppdb/templates', $file, $fileName);

                if (!$filePath) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Gagal mengupload file template baru.',
                        'data'    => null,
                    ];
                }

                $data['file_template'] = $filePath;
            }

            // Jika is_aktif = true, nonaktifkan template lain dengan tipe yang sama
            $isAktif = (bool) ($data['is_aktif'] ?? false);
            $data['is_aktif'] = $isAktif;

            if ($isAktif) {
                TemplateDokumen::where('tipe', $data['tipe'])->where('id', '!=', $id)->update(['is_aktif' => false]);
            }

            $template->update($data);

            $this->logActivity->log(
                'Update Template Dokumen',
                "Memperbarui Template: \"{$template->nama}\" (Tipe: {$template->tipe})"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Template dokumen berhasil diperbarui.',
                'data'    => $template->fresh(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[TemplateDokumenService::update] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memperbarui template: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // DESTROY — Hapus file dari storage + record di database
    // =========================================================================

    /**
     * Menghapus template: file dari Storage::disk('public') + record database.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $template = TemplateDokumen::find($id);
            if (!$template) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Template dokumen dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $nama = $template->nama;

            // Hapus file dari storage
            if ($template->file_template && Storage::disk('public')->exists($template->file_template)) {
                Storage::disk('public')->delete($template->file_template);
            }

            $template->delete();

            $this->logActivity->log(
                'Hapus Template Dokumen',
                "Menghapus Template: \"{$nama}\""
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Template \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[TemplateDokumenService::destroy] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus template: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // SET AKTIF — Aktifkan template ini, nonaktifkan yang lain dengan tipe sama
    // =========================================================================

    /**
     * Mengaktifkan satu template dan menonaktifkan semua template lain
     * yang memiliki tipe yang sama.
     *
     * @param  int $id
     * @param  int $userId
     * @return array
     */
    public function setAktif(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $template = TemplateDokumen::find($id);
            if (!$template) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Template dokumen dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Nonaktifkan semua template dengan tipe yang sama
            TemplateDokumen::where('tipe', $template->tipe)->update(['is_aktif' => false]);

            // Aktifkan template ini
            $template->update(['is_aktif' => true]);

            $tipeLabel = $template->tipe === 'pengumuman' ? 'Pengumuman' : 'Kartu Peserta';

            $this->logActivity->log(
                'Set Aktif Template Dokumen',
                "Mengaktifkan Template: \"{$template->nama}\" sebagai template {$tipeLabel} aktif"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Template \"{$template->nama}\" berhasil diaktifkan sebagai template {$tipeLabel}.",
                'data'    => $template->fresh(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[TemplateDokumenService::setAktif] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengaktifkan template: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
