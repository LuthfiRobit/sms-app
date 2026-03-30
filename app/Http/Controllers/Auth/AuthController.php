<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\LogActivityService;
use App\Services\ResponseService;

class AuthController extends Controller
{
    protected $logActivityService;
    protected $responseService;

    public function __construct(LogActivityService $logActivityService, ResponseService $responseService)
    {
        $this->logActivityService = $logActivityService;
        $this->responseService = $responseService;
    }

    public function loginView()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $ip = $request->ip();
        $throttleKey = 'login:' . $ip;

        // 1. Rate Limiting
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $remainingSeconds = RateLimiter::availableIn($throttleKey);

            return $this->responseService->error(
                'Terlalu banyak percobaan login. Coba lagi dalam ' . $remainingSeconds . ' detik.',
                429
            );
        }

        // 2. Validate input
        try {
            $validated = $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseService->response($e->errors(), 422, 'Validation failed');
        }

        $loginInput = $validated['login'];

        // 3. Find user by email or username
        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        // 4. Check if user exists and is active
        if ($user) {
            if ($user->status !== 'active') {
                return $this->responseService->error(
                    'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    403
                );
            }

            // 5. Verify password
            if (Hash::check($validated['password'], $user->password)) {
                Auth::login($user, $request->boolean('remember'));
                RateLimiter::clear($throttleKey);

                $this->logActivityService->log('User Login', "User {$user->username} logged in.");

                return $this->responseService->success([
                    'redirect' => route('admin.dashboard')
                ], 'Login berhasil');
            }
        }

        // 6. Login failed
        RateLimiter::hit($throttleKey);
        $this->logActivityService->log('Failed Login', "Gagal login untuk input: {$loginInput} dari IP: {$ip}");

        return $this->responseService->error('Email/Username atau Password salah.', 401);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $this->logActivityService->log('User Logout', "User {$user->username} logged out.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }
}
