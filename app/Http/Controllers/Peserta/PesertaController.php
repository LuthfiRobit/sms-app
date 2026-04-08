<?php

namespace App\Http\Controllers\Peserta;

use Exception;
use App\Http\Controllers\Controller;
use App\Services\Peserta\PesertaService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class PesertaController extends Controller
{
    public function __construct(
        protected PesertaService    $pesertaService,
        protected ResponseService   $responseService,
        protected LogActivityService $logActivity,
    ) {}

    // -------------------------------------------------------------------------
    // INDEX — Halaman daftar peserta
    // -------------------------------------------------------------------------

    public function index()
    {
        $this->logActivity->log('Akses Menu Peserta', 'Membuka halaman manajemen data peserta');
        return view('admin.peserta.index');
    }

    // -------------------------------------------------------------------------
    // LIST — DataTable endpoint (AJAX)
    // -------------------------------------------------------------------------

    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['nama', 'nisn', 'kecamatan', 'agama']);

        $query = $this->pesertaService->index($filters)
            ->withCount('pendaftaran')
            ->with(['alamat', 'kontak']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto_avatar', function ($row) {
                // Guard: tolak path absolut Windows/temp yang tersimpan salah di DB
                $isValidPath = $row->foto
                    && !str_contains($row->foto, ':')       // bukan C:\...
                    && !str_contains($row->foto, '\\')     // bukan backslash Windows
                    && !str_starts_with($row->foto, '/tmp') // bukan /tmp Linux
                    && !str_starts_with($row->foto, 'C')   // safety
                    && strlen($row->foto) < 200;            // panjang wajar

                $src = $isValidPath
                    ? asset('storage/' . $row->foto)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($row->nama_lengkap) . '&size=40&background=4680ff&color=fff&rounded=true';
                return '<img src="' . $src . '" class="rounded-circle" width="40" height="40" style="object-fit:cover;" alt="foto">';
            })
            ->addColumn('kecamatan', fn ($row) => $row->alamat?->kecamatan ?? '<span class="text-muted">—</span>')
            ->addColumn('jumlah_pendaftaran', function ($row) {
                $count = $row->pendaftaran_count ?? 0;
                $badge = $count > 0
                    ? '<span class="badge bg-primary">' . $count . '</span>'
                    : '<span class="badge bg-secondary">0</span>';
                return $badge;
            })
            ->addColumn('action', function ($row) {
                $btn  = '<div class="d-flex gap-1">';

                if (auth()->user()->hasPermissionTo('admin.peserta.show')) {
                    $btn .= '<a href="' . route('admin.peserta.show', $row->id) . '" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('admin.peserta.update')) {
                    $btn .= '<button class="btn btn-sm btn-outline-primary btn-edit-peserta" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermissionTo('admin.peserta.destroy')) {
                    $btn .= '<button class="btn btn-sm btn-outline-danger btn-delete-peserta" data-id="' . $row->id . '" data-nama="' . e($row->nama_lengkap) . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['foto_avatar', 'kecamatan', 'jumlah_pendaftaran', 'action'])
            ->make(true);
    }

    // -------------------------------------------------------------------------
    // STORE — Simpan peserta baru
    // -------------------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        // Validasi field wajib di Tab 1 (Data Pribadi)
        $request->validate([
            'peserta.nama_lengkap'  => 'required|string|max:100',
            'peserta.jenis_kelamin' => 'required|in:L,P',
            'peserta.tempat_lahir'  => 'required|string|max:100',
            'peserta.tanggal_lahir' => 'required|date',
            'peserta.agama'         => 'required|string|max:30',
            // SECURITY: NIK 16 digit numerik, NISN 10 digit numerik
            'peserta.nisn'          => ['nullable', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'peserta.nik'           => ['nullable', 'digits:16', 'regex:/^[0-9]{16}$/'],
            'peserta.no_kk'         => ['nullable', 'digits:16', 'regex:/^[0-9]{16}$/'],
            // SECURITY: File upload — hanya format gambar yang aman, max 2MB
            'peserta.foto'          => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        // Handle upload foto — gunakan file() langsung tanpa nested merge
        // agar tidak terjadi konflik antara string path dan UploadedFile object
        $fotoFile = $request->file('peserta')['foto'] ?? null;
        $fotoPath = null;
        if ($fotoFile && $fotoFile->isValid()) {
            $fotoPath = $fotoFile->store('peserta/foto', 'public');
        }

        try {
            // Inject foto path ke payload peserta jika ada upload
            $data = $request->only(['peserta', 'alamat', 'orang_tua', 'periodik', 'kontak', 'dokumen_pribadi']);
            if ($fotoPath) {
                $data['peserta']['foto'] = $fotoPath;
            }
            $result = $this->pesertaService->store($data, auth()->id());
            // LOG: Store Peserta
            $nama = auth()->user()->name ?? 'Admin';
            $namaPeserta = $data['peserta']['nama_lengkap'] ?? '-';
            $this->logActivity->log(
                "Admin {$nama} store Peserta",
                "Admin {$nama} store Peserta: {$namaPeserta}"
            );
            return $this->responseService->success($result, 'Peserta berhasil ditambahkan.');
        } catch (Exception $e) {
            return $this->responseService->error('Gagal menambahkan peserta: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // SHOW — Detail peserta (halaman detail multi-tab)
    // -------------------------------------------------------------------------

    public function show(int $id)
    {
        // Jika request AJAX → return JSON untuk modal edit
        if (request()->expectsJson() || request()->ajax()) {
            try {
                $data = $this->pesertaService->getDetailForAdmin($id);
                return $this->responseService->success($data);
            } catch (Exception $e) {
                return $this->responseService->error('Peserta tidak ditemukan.', 404);
            }
        }

        // Request normal → halaman detail
        try {
            $detail = $this->pesertaService->getDetailForAdmin($id);
            return view('admin.peserta.detail', compact('detail'));
        } catch (Exception $e) {
            abort(404, 'Peserta tidak ditemukan.');
        }
    }

    // -------------------------------------------------------------------------
    // UPDATE — Perbarui data peserta
    // -------------------------------------------------------------------------

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'peserta.nama_lengkap'  => 'sometimes|required|string|max:100',
            'peserta.jenis_kelamin' => 'sometimes|required|in:L,P',
            'peserta.tanggal_lahir' => 'sometimes|required|date',
            // SECURITY: NIK 16 digit numerik, NISN 10 digit numerik
            'peserta.nisn'          => ['nullable', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'peserta.nik'           => ['nullable', 'digits:16', 'regex:/^[0-9]{16}$/'],
            'peserta.no_kk'         => ['nullable', 'digits:16', 'regex:/^[0-9]{16}$/'],
            // SECURITY: File upload — hanya format gambar yang aman, max 2MB
            'peserta.foto'          => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        // Handle upload foto update
        $fotoFile = $request->file('peserta')['foto'] ?? null;
        $fotoPath = null;
        if ($fotoFile && $fotoFile->isValid()) {
            $fotoPath = $fotoFile->store('peserta/foto', 'public');
        }

        // Cek apakah user adalah admin/superadmin
        $user    = auth()->user();
        $isAdmin = method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['superadmin', 'admin'])
            : ($user->hasRole('superadmin') || $user->hasRole('admin'));

        try {
            $data = $request->only(['peserta', 'alamat', 'orang_tua', 'periodik', 'kontak', 'dokumen_pribadi']);
            if ($fotoPath) {
                $data['peserta']['foto'] = $fotoPath;
            }
            $result = $this->pesertaService->update(
                $id,
                $data,
                auth()->id(),
                $isAdmin
            );
            // LOG: Update Peserta
            $nama = auth()->user()->name ?? 'Admin';
            $namaPeserta = $data['peserta']['nama_lengkap'] ?? "ID #{$id}";
            $this->logActivity->log(
                "Admin {$nama} update Peserta",
                "Admin {$nama} update Peserta: {$namaPeserta} (ID #{$id})"
            );
            return $this->responseService->success($result, 'Data peserta berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->responseService->error('Gagal memperbarui peserta: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // DESTROY — Hapus peserta (soft delete)
    // -------------------------------------------------------------------------

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->pesertaService->destroy($id, auth()->id());
            // LOG: Destroy Peserta
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} destroy Peserta",
                "Admin {$nama} destroy Peserta: ID #{$id}"
            );
            return $this->responseService->success(null, $result['message']);
        } catch (Exception $e) {
            return $this->responseService->error('Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // IMPORT CSV — Upload & proses file CSV format Dapodik
    // -------------------------------------------------------------------------

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
        ], [
            'csv_file.required' => 'File CSV wajib dipilih.',
            'csv_file.mimes'    => 'Format file harus CSV.',
            'csv_file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Simpan file sementara di storage/app/imports
            $file     = $request->file('csv_file');
            $filePath = $file->storeAs('imports/peserta', 'import_' . now()->format('YmdHis') . '.csv');
            $fullPath = storage_path('app/' . $filePath);

            $result = $this->pesertaService->importFromCsv($fullPath, auth()->id());

            // Hapus file sementara setelah import
            @unlink($fullPath);

            return response()->json([
                'status'        => 200,
                'message'       => "Import selesai: {$result['success_count']} berhasil, {$result['skip_count']} di-skip, {$result['error_count']} gagal.",
                'success_count' => $result['success_count'],
                'skip_count'    => $result['skip_count'],
                'error_count'   => $result['error_count'],
                'errors'        => $result['errors'],
            ]);
        } catch (Exception $e) {
            return $this->responseService->error('Gagal memproses import: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // EXPORT CSV — Generate & download file CSV format Dapodik
    // -------------------------------------------------------------------------

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['nama', 'nisn', 'kecamatan', 'agama']);

        $filePath = $this->pesertaService->exportToCsv($filters);
        $filename = 'data_peserta_dapodik_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filePath) {
            readfile($filePath);
            // Hapus file setelah dikirim
            @unlink($filePath);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
