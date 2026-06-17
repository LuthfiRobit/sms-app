<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Mail\OtpVerifikasiMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create the required 'peserta' role
        Role::create([
            'name' => 'peserta',
            'display_name' => 'Peserta PPDB',
            'description' => 'Role for new student admission candidates'
        ]);
    }

    public function test_registration_with_otp_enabled()
    {
        Mail::fake();
        // Force OTP to be enabled for this test using Laravel's Env repository
        \Illuminate\Support\Env::getRepository()->set('PPDB_OTP_ENABLED', 'true');

        // 1. Submit Registration Form
        $response = $this->post(route('ppdb.register.post'), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'setuju_syarat' => 'on',
        ]);

        // Assert redirect to verify email route
        $response->assertRedirect(route('ppdb.verify-email'));

        // Assert user was created with status 'pending'
        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);

        // Assert user is logged in
        $this->assertAuthenticatedAs($user);

        // Assert OTP cache is set
        $cachedOtp = Cache::get('otp_' . $user->id_user);
        $this->assertNotNull($cachedOtp);
        $this->assertEquals(6, strlen($cachedOtp));

        // Assert email OTP was sent
        Mail::assertQueued(OtpVerifikasiMail::class, function ($mail) use ($user, $cachedOtp) {
            return $mail->hasTo($user->email) && $mail->otp === $cachedOtp;
        });

        // 2. Submit Verification Code (OTP)
        $verifyResponse = $this->post(route('ppdb.verify-email.post'), [
            'otp' => $cachedOtp,
        ]);

        // Assert redirect to dashboard after verification
        $verifyResponse->assertRedirect(route('ppdb.dashboard'));

        // Refresh user from database
        $user->refresh();

        // Assert status is now active
        $this->assertEquals('active', $user->status);

        // Assert cache OTP is cleared
        $this->assertNull(Cache::get('otp_' . $user->id_user));
    }

    public function test_registration_with_otp_disabled()
    {
        Mail::fake();
        // Force OTP to be disabled for this test using Laravel's Env repository
        \Illuminate\Support\Env::getRepository()->set('PPDB_OTP_ENABLED', 'false');

        // Submit Registration Form
        $response = $this->post(route('ppdb.register.post'), [
            'nama_lengkap' => 'Joko Susilo',
            'email' => 'joko@example.com',
            'no_hp' => '082345678901',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'setuju_syarat' => 'on',
        ]);

        // Assert redirect directly to dashboard
        $response->assertRedirect(route('ppdb.dashboard'));

        // Assert user was created with status 'active' directly
        $user = User::where('email', 'joko@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('active', $user->status);

        // Assert user is logged in
        $this->assertAuthenticatedAs($user);

        // Assert NO OTP cache is set
        $cachedOtp = Cache::get('otp_' . $user->id_user);
        $this->assertNull($cachedOtp);

        // Assert email OTP was NOT sent
        Mail::assertNotQueued(OtpVerifikasiMail::class);
    }

    public function test_registration_validation_errors()
    {
        $response = $this->post(route('ppdb.register.post'), [
            'nama_lengkap' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'abc',
        ]);

        $response->assertSessionHasErrors(['nama_lengkap', 'email', 'no_hp', 'password', 'setuju_syarat']);
    }

    public function test_invalid_otp_fails()
    {
        Mail::fake();
        \Illuminate\Support\Env::getRepository()->set('PPDB_OTP_ENABLED', 'true');

        // Register
        $this->post(route('ppdb.register.post'), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'setuju_syarat' => 'on',
        ]);

        $user = User::where('email', 'budi@example.com')->first();
        $cachedOtp = Cache::get('otp_' . $user->id_user);

        // Try submitting wrong OTP
        $verifyResponse = $this->post(route('ppdb.verify-email.post'), [
            'otp' => '000000', // incorrect OTP
        ]);

        $verifyResponse->assertSessionHas('error', 'Kode OTP salah. Periksa kembali email Anda.');
        $this->assertEquals('pending', $user->fresh()->status);
    }
}
