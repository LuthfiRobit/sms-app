<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerifikasiMail;
use App\Services\LogActivityService;
use App\Services\PesertaAccountService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthPesertaController extends Controller
{
    public function __construct(
        protected PesertaAccountService $pesertaAccountService,
        protected LogActivityService    $logActivity,
    ) {}

    // =========================================================================
    // REGISTER
    // =========================================================================

    /**
     * Tampilkan form registrasi peserta.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (auth()->check() && $this->pesertaAccountService->isPeserta(auth()->user())) {
            return redirect()->route('ppdb.dashboard');
        }

        return view('portal.auth.register');
    }

    /**
     * Proses registrasi akun baru peserta.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_lengkap'  => ['required', 'string', 'min:3', 'max:100'],
            'email'         => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'      => ['required', 'min:8', 'confirmed'],
            'setuju_syarat' => ['required', 'accepted'],
        ], [
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'       => 'Nama minimal 3 karakter.',
            'email.unique'           => 'Email sudah terdaftar. Silakan login.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'password.min'           => 'Password minimal 8 karakter.',
            'setuju_syarat.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        try {
            $user = $this->pesertaAccountService->createPesertaAccount(
                namaLengkap: $request->nama_lengkap,
                email:        $request->email,
                password:     $request->password,
                status:       'pending',
            );

            // Login otomatis setelah registrasi
            auth()->login($user);

            // Generate OTP 6 digit dengan zero-padding
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Simpan OTP ke cache, expire 10 menit
            Cache::put('otp_' . $user->id_user, $otp, now()->addMinutes(10));

            // Kirim email OTP via queue
            Mail::to($user->email)->queue(new OtpVerifikasiMail($otp, $user->name));

            // Log aktivitas
            $this->logActivity->log('Registrasi akun peserta PPDB', 'Email: ' . $user->email);

            return redirect()->route('ppdb.verify-email')
                ->with('success', 'Akun berhasil dibuat! Kode OTP sudah dikirim ke email Anda.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // VERIFY EMAIL (OTP)
    // =========================================================================

    /**
     * Tampilkan halaman verifikasi OTP.
     */
    public function showVerifyEmail(): View|RedirectResponse
    {
        if (!auth()->check() || !$this->pesertaAccountService->isPeserta(auth()->user())) {
            return redirect()->route('ppdb.login');
        }

        if (auth()->user()->status === 'active') {
            return redirect()->route('ppdb.dashboard');
        }

        // Masking email: tampilkan sebagian saja agar aman
        $maskedEmail = Str::mask(auth()->user()->email, '*', 3, -6);

        return view('portal.auth.verify-email', compact('maskedEmail'));
    }

    /**
     * Verifikasi kode OTP yang diinput peserta.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size'     => 'Kode OTP harus 6 digit.',
        ]);

        $user      = auth()->user();
        $cachedOtp = Cache::get('otp_' . $user->id_user);

        if (is_null($cachedOtp)) {
            return redirect()->back()
                ->with('error', 'Kode OTP sudah expired. Gunakan tombol "Kirim Ulang Kode".');
        }

        if ($request->otp !== $cachedOtp) {
            return redirect()->back()
                ->with('error', 'Kode OTP salah. Periksa kembali email Anda.');
        }

        // OTP cocok — aktifkan akun
        $this->pesertaAccountService->activateAccount($user);
        Cache::forget('otp_' . $user->id_user);

        // Regenerate session untuk keamanan pasca verifikasi
        Session::regenerate();

        $this->logActivity->log('Verifikasi email peserta PPDB', 'Email: ' . $user->email);

        return redirect()->route('ppdb.dashboard')
            ->with('success', 'Akun berhasil diverifikasi! Selamat datang di portal PPDB.');
    }

    /**
     * Kirim ulang kode OTP (dengan rate limiting 3x per 5 menit).
     */
    public function resendOtp(): RedirectResponse
    {
        if (!auth()->check() || !$this->pesertaAccountService->isPeserta(auth()->user())) {
            return redirect()->route('ppdb.login');
        }

        $user = auth()->user();
        $key  = 'resend_otp_' . $user->id_user;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return redirect()->back()
                ->with('error', "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.");
        }

        RateLimiter::hit($key, 300); // 5 menit

        // Generate OTP baru
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp_' . $user->id_user, $otp, now()->addMinutes(10));

        Mail::to($user->email)->queue(new OtpVerifikasiMail($otp, $user->name));

        return redirect()->back()
            ->with('success', 'Kode OTP baru sudah dikirim ke email Anda.');
    }

    // =========================================================================
    // LOGIN / LOGOUT
    // =========================================================================

    /**
     * Tampilkan halaman login portal peserta.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (
            auth()->check() &&
            $this->pesertaAccountService->isPeserta(auth()->user()) &&
            auth()->user()->status === 'active'
        ) {
            return redirect()->route('ppdb.dashboard');
        }

        return view('portal.auth.login');
    }

    /**
     * Proses login peserta.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'Email atau password salah.');
        }

        $user = Auth::user();

        // Pastikan akun adalah role peserta
        if (!$this->pesertaAccountService->isPeserta($user)) {
            Auth::logout();
            return redirect()->route('ppdb.login')
                ->with('error', 'Akun ini bukan akun peserta PPDB.');
        }

        // Handle berdasarkan status akun
        if ($user->status === 'pending') {
            return redirect()->route('ppdb.verify-email')
                ->with('info', 'Silakan verifikasi email Anda terlebih dahulu.');
        }

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('ppdb.login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi admin sekolah.');
        }

        Session::regenerate();

        $this->logActivity->log('Login portal peserta PPDB', 'Email: ' . $user->email);

        return redirect()->route('ppdb.dashboard');
    }

    /**
     * Logout peserta dari portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->logActivity->log('Logout portal peserta PPDB', 'Email: ' . (auth()->user()?->email ?? '-'));

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppdb.login')
            ->with('success', 'Anda berhasil logout.');
    }
}
