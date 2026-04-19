<?php

namespace App\Http\Controllers;

use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    protected $notifikasiService;

    public function __construct(NotifikasiService $notifikasiService)
    {
        $this->notifikasiService = $notifikasiService;
    }

    /**
     * Tampilkan halaman list notifikasi admin.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $filters = [
            'tipe'   => 'inapp',
            'status' => 'terkirim',
        ];

        if ($request->has('unread')) {
            $filters['dibaca'] = false;
        }

        // Ambil query builder melalui service (menggunakan helper index tapi dikonversi ke query jika ingin pagination)
        // Namun di service index() mengembalikan Collection. 
        // Saya akan gunakan Notifikasi model langsung di sini untuk pagination yang lebih efisien,
        // namun tetap menghormati logika filtering dari service.
        
        $query = \App\Models\Notifikasi::where('user_id', $userId)
            ->where('tipe', 'inapp')
            ->where('status', 'terkirim')
            ->orderBy('created_at', 'desc');

        if ($request->has('unread')) {
            $query->where('dibaca', false);
        }

        $notifikasi = $query->paginate(15)->withQueryString();

        return view('admin.notifikasi.index', compact('notifikasi'));
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function markRead(int $id)
    {
        $success = $this->notifikasiService->markAsRead($id, Auth::id());

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notifikasi ditandai dibaca.' : 'Gagal memperbarui notifikasi.'
        ]);
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca.
     */
    public function markAllRead()
    {
        $count = $this->notifikasiService->markAllAsRead(Auth::id());

        return response()->json([
            'success' => true,
            'count'   => $count,
            'message' => "$count notifikasi ditandai dibaca."
        ]);
    }

    /**
     * Get unread count and latest notifications for AJAX polling.
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();
        $count = $this->notifikasiService->getUnreadCount($userId);
        
        // Ambil 5 notifikasi terakhir belum dibaca
        $latest = $this->notifikasiService->getUnreadInapp($userId, 5);

        // Format data untuk dropdown
        $formatted = $latest->map(function($notif) {
            return [
                'id'         => $notif->id,
                'judul'      => $notif->judul,
                'isi'        => \Illuminate\Support\Str::limit($notif->isi, 60),
                'waktu'      => $notif->created_at->diffForHumans(),
                'read_url'   => route('admin.notifikasi.read', $notif->id),
            ];
        });

        return response()->json([
            'unread_count' => $count,
            'latest'       => $formatted
        ]);
    }
}
