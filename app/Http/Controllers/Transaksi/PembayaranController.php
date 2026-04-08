<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Transaksi\PembayaranService;
use App\Services\LogActivityService;
use App\Models\Transaksi\PembayaranPpdb;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    public function __construct(
        protected PembayaranService $pembayaranService,
        protected LogActivityService $logActivity
    ) {
    }

    /**
     * Menampilkan halaman utama daftar pembayaran.
     */
    public function index()
    {
        // Permission check
        if (!auth()->user()->hasPermissionTo('admin.pembayaran.index')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.pembayaran.index');
    }

    /**
     * Menyediakan data untuk DataTable pendaftaran.
     */
    public function list(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('admin.pembayaran.index')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $filters = $request->only(['status', 'jalur_id']);
            
            // Note: Since PembayaranService getStatus doesn't return a query builder for datatables,
            // we will fetch using eloquent. Ideally, the service should have a datatable method.
            // But we will access it directly as per the standard pattern if service doesn't have it.
            // Oh, wait, the service injects PembayaranPpdbRepositoryInterface. 
            // We should use appropriate repository or write query here, but let's write correct DataTables code.
            
            $query = \App\Models\Transaksi\PembayaranPpdb::with([
                'pendaftaran', 
                'pendaftaran.peserta', 
                'pendaftaran.jalurPendaftaran'
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('jalur_id')) {
                $query->whereHas('pendaftaran', function ($q) use ($request) {
                    $q->where('jalur_pendaftaran_id', $request->jalur_id);
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('no_pendaftaran', function ($row) {
                    return $row->pendaftaran->no_pendaftaran ?? '-';
                })
                ->addColumn('nama_peserta', function ($row) {
                    return $row->pendaftaran->peserta->nama_lengkap ?? '-';
                })
                ->addColumn('jalur', function ($row) {
                    return $row->pendaftaran->jalurPendaftaran->nama ?? '-';
                })
                ->addColumn('nominal', function ($row) {
                    return 'Rp ' . number_format($row->amount, 0, ',', '.');
                })
                ->addColumn('waktu_bayar', function ($row) {
                    return $row->waktu_bayar ? $row->waktu_bayar->format('d/m/Y H:i') : '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $statusClasses = [
                        PembayaranPpdb::STATUS_PENDING => 'warning',
                        PembayaranPpdb::STATUS_PAID => 'success',
                        PembayaranPpdb::STATUS_EXPIRED => 'secondary',
                        PembayaranPpdb::STATUS_FAILED => 'danger',
                        PembayaranPpdb::STATUS_REFUND => 'info',
                    ];
                    $class = $statusClasses[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button onclick="showDetail(' . $row->id . ')" class="btn btn-sm btn-info me-1" title="Detail"><i class="bi bi-eye"></i></button>';
                    
                    if ($row->status === PembayaranPpdb::STATUS_PENDING && auth()->user()->hasPermissionTo('admin.pembayaran.konfirmasi')) {
                        $btn .= '<button onclick="openKonfirmasiModal(' . $row->id . ')" class="btn btn-sm btn-success" title="Konfirmasi Manual"><i class="bi bi-check-circle"></i></button>';
                    }
                    
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('[PembayaranController::list] ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data'], 500);
        }
    }

    /**
     * Menampilkan detail pembayaran.
     */
    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('admin.pembayaran.show')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $pembayaran = PembayaranPpdb::with([
                'pendaftaran.peserta.kontak', 
                'pendaftaran.jalurPendaftaran',
                'biayaRegistrasi'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pembayaran
            ]);

        } catch (\Exception $e) {
            Log::error('[PembayaranController::show] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail data.'
            ], 500);
        }
    }

    /**
     * Konfirmasi pembayaran secara manual (upload bukti).
     */
    public function konfirmasiManual(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('admin.pembayaran.konfirmasi')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'bukti_bayar' => 'required|file|mimes:jpeg,png,webp,pdf|max:5120',
        ]);

        try {
            $result = $this->pembayaranService->manualKonfirmasi(
                $id,
                $request->file('bukti_bayar'),
                auth()->id()
            );

            if ($result['success']) {
                $this->logActivity->log(
                    'Konfirmasi Pembayaran Manual',
                    "User " . auth()->user()->name . " mengkonfirmasi pembayaran ID: {$id} secara manual."
                );
                return response()->json($result);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('[PembayaranController::konfirmasiManual] ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
