<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogActivityController extends Controller
{
    protected $logActivityService;
    protected $responseService;

    public function __construct(
        LogActivityService $logActivityService,
        ResponseService $responseService
    ) {
        $this->logActivityService = $logActivityService;
        $this->responseService = $responseService;
    }

    /**
     * Display activity log index page
     */
    public function index()
    {
        $this->logActivityService->log('Access Activity Log Index', 'Membuka halaman dashboard log aktivitas');
        return view('admin.system.log-activity.index');
    }

    /**
     * List activity logs for DataTables
     */
    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->logActivityService->getDatatablesData();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function($row){
                    return $row->user ? $row->user->name : 'System';
                })
                ->addColumn('created_at_formatted', function($row){
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('action_btn', function($row){
                    $btn = '<button type="button" class="btn btn-outline-primary btn-sm btn-show" data-id="'.$row->id_log_activity.'">';
                    $btn .= '<i class="bi bi-eye"></i> Detail</button>';
                    return $btn;
                })
                ->rawColumns(['action_btn'])
                ->make(true);
        }
    }

    /**
     * Show activity log detail
     */
    public function show($id)
    {
        try {
            $log = $this->logActivityService->getLogById($id);
            $log->user_name = $log->user ? $log->user->name : 'System';
            $log->formatted_date = $log->created_at->format('d F Y H:i:s');
            
            return $this->responseService->success($log);
        } catch (\Exception $e) {
            return $this->responseService->error('Log aktivitas tidak ditemukan');
        }
    }

    /**
     * Delete all activity logs
     */
    public function deleteAll()
    {
        try {
            $this->logActivityService->deleteAllLogs();
            $this->logActivityService->log('Delete All Activity Logs', 'Mengahpus semua data log aktivitas');
            
            return $this->responseService->success(null, 'Semua log aktivitas berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus log aktivitas: ' . $e->getMessage());
        }
    }
}
