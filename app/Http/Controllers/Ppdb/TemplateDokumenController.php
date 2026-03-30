<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\Ppdb\TemplateDokumenService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TemplateDokumenController extends Controller
{
    protected $templateService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        TemplateDokumenService $templateService,
        LogActivityService     $logActivity,
        ResponseService        $responseService
    ) {
        $this->templateService = $templateService;
        $this->logActivity     = $logActivity;
        $this->responseService = $responseService;
    }

    /**
     * Tampilkan halaman daftar template dokumen.
     */
    public function index()
    {
        $this->logActivity->log('View Template Dokumen', 'Membuka halaman Template Dokumen PPDB');
        return view('admin.ppdb.template-dokumen.index');
    }

    /**
     * DataTables AJAX — ambil semua template.
     */
    public function list(Request $request)
    {
        $result = $this->templateService->index();

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('tipe_badge', function ($row) {
                if ($row->tipe === 'pengumuman') {
                    return '<span class="badge bg-primary"><i class="bi bi-megaphone me-1"></i>Pengumuman</span>';
                }
                return '<span class="badge bg-info text-white"><i class="bi bi-person-badge me-1"></i>Kartu Peserta</span>';
            })
            ->addColumn('file_link', function ($row) {
                if (!$row->file_template) {
                    return '<span class="text-muted fst-italic">Tidak ada file</span>';
                }
                $url = asset('storage/' . $row->file_template);
                return '<a href="' . $url . '" target="_blank" class="btn btn-link btn-sm p-0 text-danger">'
                    . '<i class="bi bi-file-earmark-pdf-fill me-1"></i>Preview PDF</a>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->is_aktif
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                    : '<span class="badge bg-secondary">Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';

                // Preview
                if ($row->file_template) {
                    $url = asset('storage/' . $row->file_template);
                    $btn .= '<a href="' . $url . '" target="_blank" class="btn btn-outline-danger" title="Preview PDF">'
                        . '<i class="bi bi-eye"></i></a>';
                }

                // Set Aktif
                if (auth()->user()->hasPermissionTo('admin.ppdb.template.update') && !$row->is_aktif) {
                    $btn .= '<button type="button" class="btn btn-outline-success btn-set-aktif" '
                        . 'data-id="' . $row->id . '" title="Jadikan Aktif">'
                        . '<i class="bi bi-check-circle"></i></button>';
                }

                // Edit
                if (auth()->user()->hasPermissionTo('admin.ppdb.template.update')) {
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit" '
                        . 'data-id="' . $row->id . '" title="Edit">'
                        . '<i class="bi bi-pencil"></i></button>';
                }

                // Hapus
                if (auth()->user()->hasPermissionTo('admin.ppdb.template.destroy')) {
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete" '
                        . 'data-id="' . $row->id . '" title="Hapus">'
                        . '<i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['tipe_badge', 'file_link', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Upload template dokumen baru.
     * Validasi: mimes:pdf, max:5MB.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama'      => 'required|string|max:100',
            'tipe'      => 'required|in:pengumuman,kartu_peserta',
            'file'      => 'required|file|mimes:pdf|max:5120',
            'deskripsi' => 'nullable|string',
            'is_aktif'  => 'nullable|boolean',
        ]);

        $validatedData['is_aktif'] = $request->boolean('is_aktif');

        $userId = auth()->id() ?? 0;
        $result = $this->templateService->store($validatedData, $request->file('file'), $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    /**
     * Detail satu template (untuk isi form edit pre-populate).
     */
    public function show($id)
    {
        $result = $this->templateService->show((int) $id);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    /**
     * Update template (multipart/form-data jika ada file baru).
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama'      => 'required|string|max:100',
            'tipe'      => 'required|in:pengumuman,kartu_peserta',
            'file'      => 'nullable|file|mimes:pdf|max:5120',
            'deskripsi' => 'nullable|string',
            'is_aktif'  => 'nullable|boolean',
        ]);

        $validatedData['is_aktif'] = $request->boolean('is_aktif');

        $userId = auth()->id() ?? 0;
        $result = $this->templateService->update(
            (int) $id,
            $validatedData,
            $request->hasFile('file') ? $request->file('file') : null,
            $userId
        );

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    /**
     * Hapus template + file dari storage.
     */
    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->templateService->destroy((int) $id, $userId);

        if ($result['success']) {
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    /**
     * Jadikan template aktif (nonaktifkan yang lain dengan tipe sama).
     */
    public function setAktif($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->templateService->setAktif((int) $id, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
