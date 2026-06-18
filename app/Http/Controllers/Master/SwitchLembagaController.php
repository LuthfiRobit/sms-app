<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class SwitchLembagaController extends Controller
{
    public function __construct(protected ResponseService $responseService)
    {
    }

    public function __invoke(Request $request)
    {
        $lembagaId = $request->input('lembaga_id');
        $user = auth()->user();
        $lembagaIds = $user->getLembagaIds();

        // Super admin boleh switch ke null (semua) atau ke lembaga manapun
        // Admin lembaga hanya boleh switch ke lembaganya sendiri
        if (! $user->isSuperAdmin() && $lembagaId && ! in_array($lembagaId, $lembagaIds)) {
            return $this->responseService->error('Tidak diizinkan mengakses lembaga ini.', 403);
        }

        session(['active_lembaga_id' => $lembagaId ?: null]);

        return $this->responseService->success(null, 'Lembaga aktif berhasil diubah');
    }
}
