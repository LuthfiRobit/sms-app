<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use App\Services\LogActivityService;

class PermissionSyncController extends Controller
{
    protected $logActivityService;

    public function __construct(LogActivityService $logActivityService)
    {
        $this->logActivityService = $logActivityService;
    }

    /**
     * Trigger permission sync command
     *
     * @return JsonResponse
     */
    public function sync(): JsonResponse
    {
        try {
            Artisan::call('permission:sync');
            $this->logActivityService->log('Sync Permissions', 'Sinkronisasi permissions dari route');
            
            return response()->json([
                'status' => 200,
                'message' => 'Permissions synchronized successfully',
                // 'output' => Artisan::output() // optional debug output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to synchronize permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}
