<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Master\TahunPelajaran;
use App\Models\Ppdb\FormulirPendaftaran;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\FormulirPendaftaranService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FormulirPendaftaranController extends Controller
{
    protected FormulirPendaftaranService $formulirService;
    protected LogActivityService         $logActivity;
    protected ResponseService            $responseService;

    public function __construct(
        FormulirPendaftaranService $formulirService,
        LogActivityService         $logActivity,
        ResponseService            $responseService
    ) {
        $this->formulirService = $formulirService;
        $this->logActivity     = $logActivity;
        $this->responseService = $responseService;
    }

    // =========================================================================
    // INDEX — Halaman list formulir
    // =========================================================================

    public function index()
    {
        $this->logActivity->log('View Formulir Pendaftaran', 'Membuka halaman Formulir Pendaftaran PPDB');

        $activeLembagaId  = app('active_lembaga_id');
        $jalurPendaftaran = JalurPendaftaran::with('pembukaanPpdb')
            ->when($activeLembagaId, fn ($q) => $q->whereHas('pembukaanPpdb', fn ($q2) => $q2->where('lembaga_id', $activeLembagaId)))
            ->orderBy('id', 'desc')
            ->get();

        $tahunPelajaran = TahunPelajaran::orderBy('nama', 'desc')->get();

        return view('admin.ppdb.formulir.index', compact('jalurPendaftaran', 'tahunPelajaran'));
    }

    // =========================================================================
    // LIST — DataTables AJAX endpoint
    // =========================================================================

    public function list(Request $request)
    {
        $jalurId = $request->get('jalur_pendaftaran_id');
        $tahunId = $request->get('tahun_pelajaran_id');

        $result = $this->formulirService->index(
            $jalurId ? (int) $jalurId : null,
            $tahunId ? (int) $tahunId : null
        );

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('jalur', fn($row) => $row->jalurPendaftaran?->nama ?? '-')
            ->addColumn('tahun', fn($row) => $row->tahunPelajaran?->nama ?? '-')
            ->addColumn('jumlah_field', fn($row) => $row->formulir_field_count ?? 0)
            ->addColumn('status_aktif', function ($row) {
                return $row->is_aktif
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';

                if (auth()->user()->hasPermissionTo('admin.ppdb.formulir.update')) {
                    $builderUrl = route('admin.ppdb.formulir.builder', $row->id);
                    $btn .= '<a href="' . $builderUrl . '" class="btn btn-sm btn-primary" title="Builder Formulir"><i class="bi bi-tools"></i></a>';
                    $toggleClass = $row->is_aktif ? 'btn-warning' : 'btn-success';
                    $toggleIcon  = $row->is_aktif ? 'bi-toggle-off' : 'bi-toggle-on';
                    $toggleTitle = $row->is_aktif ? 'Nonaktifkan' : 'Aktifkan';
                    $btn .= '<button type="button" class="btn btn-sm ' . $toggleClass . ' btn-toggle-aktif" data-id="' . $row->id . '" title="' . $toggleTitle . '"><i class="bi ' . $toggleIcon . '"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit" data-id="' . $row->id . '" title="Edit Header"><i class="bi bi-pencil"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.formulir.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_aktif', 'action'])
            ->make(true);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'tahun_pelajaran_id'   => 'required|exists:tahun_pelajaran,id',
            'nama'                 => 'required|string|max:255',
            'deskripsi'            => 'nullable|string',
            'tipe'                 => 'nullable|string|max:50',
        ]);

        $result = $this->formulirService->store($validated, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(int $id): JsonResponse
    {
        $result = $this->formulirService->show($id);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe'      => 'nullable|string|max:50',
        ]);

        $result = $this->formulirService->update($id, $validated, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(int $id): JsonResponse
    {
        $result = $this->formulirService->destroy($id, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success(null, $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // TOGGLE AKTIF
    // =========================================================================

    public function toggleAktif(int $id): JsonResponse
    {
        $result = $this->formulirService->toggleAktif($id, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // BUILDER — Halaman form builder
    // =========================================================================

    public function builder(int $id)
    {
        $formulir = FormulirPendaftaran::with(['jalurPendaftaran.pembukaanPpdb', 'tahunPelajaran', 'formulirField'])
            ->findOrFail($id);

        $this->logActivity->log(
            'View Builder Formulir',
            "Membuka Form Builder: \"{$formulir->nama}\" (ID: {$id})"
        );

        $dapodikOptions = $this->formulirService->getDapodikFieldOptions();

        return view('admin.ppdb.formulir.builder', [
            'formulir'        => $formulir,
            'dapodikGrouped'  => $dapodikOptions['data']['grouped'] ?? [],
            'dapodikFlat'     => $dapodikOptions['data']['flat']    ?? [],
        ]);
    }

    // =========================================================================
    // FIELD — Add Field
    // =========================================================================

    public function addField(Request $request, int $formulirId): JsonResponse
    {
        $validated = $request->validate([
            'kode_field'  => 'nullable|string|max:100|alpha_dash',
            'label'       => 'required|string|max:255',
            'tipe_field'  => 'required|in:text,number,date,select,radio,file,textarea',
            'is_required' => 'nullable|boolean',
            'is_statis'   => 'nullable|boolean',
            'dapodik_key' => 'nullable|string|max:100',
            'opsi'        => 'nullable|array',
            'opsi.*'      => 'nullable|string',
        ]);

        $result = $this->formulirService->addField($formulirId, $validated, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // FIELD — Update Field
    // =========================================================================

    public function updateField(Request $request, int $fieldId): JsonResponse
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'tipe_field'  => 'required|in:text,number,date,select,radio,file,textarea',
            'is_required' => 'nullable|boolean',
            'is_statis'   => 'nullable|boolean',
            'dapodik_key' => 'nullable|string|max:100',
            'opsi'        => 'nullable|array',
            'opsi.*'      => 'nullable|string',
        ]);

        $result = $this->formulirService->updateField($fieldId, $validated, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // FIELD — Delete Field
    // =========================================================================

    public function deleteField(int $fieldId): JsonResponse
    {
        $result = $this->formulirService->deleteField($fieldId, auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success(null, $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // FIELD — Reorder Fields
    // =========================================================================

    public function reorderFields(Request $request, int $formulirId): JsonResponse
    {
        $validated = $request->validate([
            'urutan_ids'   => 'required|array|min:1',
            'urutan_ids.*' => 'required|integer',
        ]);

        $result = $this->formulirService->reorderFields($formulirId, $validated['urutan_ids'], auth()->id() ?? 0);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // GET FIELDS — untuk preview formulir
    // =========================================================================

    public function getFieldsForPendaftaran(int $formulirId): JsonResponse
    {
        $result = $this->formulirService->getFieldsForPendaftaran($formulirId);

        return $result['success']
            ? $this->responseService->success($result['data'], $result['message'])
            : $this->responseService->error($result['message']);
    }

    // =========================================================================
    // SHOW FIELD — untuk modal edit di builder
    // =========================================================================

    public function showField(int $fieldId): JsonResponse
    {
        $field = \App\Models\Ppdb\FormulirField::find($fieldId);

        if (! $field) {
            return $this->responseService->error("Field dengan ID {$fieldId} tidak ditemukan.", 404);
        }

        return $this->responseService->success($field, 'Data field berhasil diambil.');
    }
}
