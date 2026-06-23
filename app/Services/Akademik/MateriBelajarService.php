<?php

namespace App\Services\Akademik;

use App\Repositories\Akademik\MateriBelajarRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MateriBelajarService
{
    public function __construct(
        protected MateriBelajarRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): mixed
    {
        $filters = $lembagaId ? ['lembaga_id' => $lembagaId] : [];
        return $this->repo->datatable($filters);
    }

    public function store(array $data, ?UploadedFile $file): object
    {
        if ($file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->store('akademik/materi', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $originalName;
        }

        $record = $this->repo->create($data);
        $this->logActivity->log('Tambah Materi Belajar', "Materi '{$record->judul}' ditambahkan.");

        return $record;
    }

    public function update(int $id, array $data, ?UploadedFile $file): object
    {
        if ($file) {
            // Delete old file if exists
            $existing = $this->repo->findById($id);
            if ($existing && $existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $originalName = $file->getClientOriginalName();
            $path = $file->store('akademik/materi', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $originalName;
        }

        $record = $this->repo->update($id, $data);
        $this->logActivity->log('Update Materi Belajar', "Materi '{$record->judul}' diperbarui.");

        return $record;
    }

    public function destroy(int $id): void
    {
        $record = $this->repo->findById($id);

        if ($record && $record->file_path) {
            Storage::disk('public')->delete($record->file_path);
        }

        $judul = $record?->judul ?? $id;
        $this->repo->delete($id);
        $this->logActivity->log('Hapus Materi Belajar', "Materi '{$judul}' dihapus.");
    }
}
