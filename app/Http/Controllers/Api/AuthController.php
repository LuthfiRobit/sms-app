<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected ResponseService $response) {}

    /**
     * Login guru untuk aplikasi mobile. Menerima email/username + password,
     * mengembalikan Bearer token (Sanctum) beserta profil guru.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',   // email atau username
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['login'])
            ->orWhere('username', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/username atau password salah.'],
            ]);
        }

        if (isset($user->status) && $user->status !== 'active') {
            return $this->response->error('Akun Anda tidak aktif. Hubungi admin.', 403);
        }

        $guru = $user->guru()->with('lembaga:id,nama,kode,jenis')->first();

        if (! $guru) {
            return $this->response->error('Akun ini tidak tertaut ke data guru manapun.', 403);
        }

        // Satu device = satu token; cabut token lama supaya tidak menumpuk.
        $user->tokens()->delete();
        $token = $user->createToken('mobile-guru')->plainTextToken;

        return $this->response->success([
            'token' => $token,
            'guru' => $this->guruResource($guru),
        ], 'Login berhasil.');
    }

    /** Profil guru yang sedang login. */
    public function me(Request $request)
    {
        $guru = $request->user()->guru()->with('lembaga:id,nama,kode,jenis')->first();

        if (! $guru) {
            return $this->response->error('Akun ini tidak tertaut ke data guru manapun.', 403);
        }

        return $this->response->success($this->guruResource($guru), 'OK');
    }

    /** Logout — cabut token aktif. */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->response->success(null, 'Logout berhasil.');
    }

    private function guruResource($guru): array
    {
        return [
            'id' => $guru->id,
            'nama' => $guru->nama,
            'nama_lengkap' => $guru->nama_lengkap,
            'nip' => $guru->nip,
            'email' => $guru->email,
            'no_hp' => $guru->no_hp,
            'foto' => $guru->foto,
            'lembaga' => $guru->lembaga ? [
                'id' => $guru->lembaga->id,
                'nama' => $guru->lembaga->nama,
                'kode' => $guru->lembaga->kode,
                'jenis' => $guru->lembaga->jenis,
            ] : null,
        ];
    }
}
