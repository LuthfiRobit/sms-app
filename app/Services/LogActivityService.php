<?php

namespace App\Services;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class LogActivityService
{
    /**
     * Menyimpan log aktivitas ke database
     *
     * @param string $action Jenis aktivitas (Create, Update, Delete, dll)
     * @param string|null $description Deskripsi tambahan
     */
    public function log(string $action, ?string $description = null)
    {
        LogActivity::create([
            'user_id'     => Auth::check() ? Auth::user()->id_user : 0,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->header('user-agent')
        ]);
    }

    /**
     * Mengambil data activity log untuk datatable
     */
    public function getDatatablesData()
    {
        return LogActivity::with('user')->orderBy('created_at', 'desc');
    }

    /**
     * Mengambil detail activity log berdasarkan id
     */
    public function getLogById(int $id)
    {
        return LogActivity::with('user')->findOrFail($id);
    }

    /**
     * Menghapus semua activity log
     */
    public function deleteAllLogs()
    {
        return LogActivity::truncate();
    }
}
