<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DeviceTokenRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function __construct(
        protected DeviceTokenRepositoryInterface $repo,
        protected ResponseService $response,
    ) {}

    /** Daftarkan/segarkan Expo push token perangkat guru yang sedang login. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:512|starts_with:ExponentPushToken[',
            'platform' => 'nullable|in:android,ios,web',
        ]);

        $this->repo->register($request->user()->id_user, $data['token'], $data['platform'] ?? null);

        return $this->response->success(null, 'Token perangkat terdaftar.');
    }

    /** Hapus token perangkat (dipanggil saat logout dari perangkat ini). */
    public function destroy(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);

        $this->repo->forget($data['token']);

        return $this->response->success(null, 'Token perangkat dihapus.');
    }
}
