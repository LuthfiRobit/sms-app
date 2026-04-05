<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi PPDB</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, sans-serif;
            background-color: #f0fdf4;
            color: #1e293b;
            padding: 20px;
        }
        .email-wrapper {
            max-width: 560px;
            margin: 0 auto;
        }
        .email-card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
            padding: 32px 40px;
            text-align: center;
        }
        .email-header .logo-text {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .email-header .logo-subtitle {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            margin-top: 4px;
        }
        .email-body {
            padding: 40px 40px 32px;
        }
        .greeting {
            font-size: 16px;
            color: #374151;
            margin-bottom: 16px;
        }
        .greeting strong {
            color: #111827;
        }
        .description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 10px;
            text-align: center;
        }
        .otp-box {
            background: #f0fdf4;
            border: 2px dashed #16a34a;
            border-radius: 12px;
            padding: 20px 24px;
            text-align: center;
            margin-bottom: 8px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 14px;
            color: #15803d;
            font-variant-numeric: tabular-nums;
        }
        .otp-expire {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 32px;
        }
        .otp-expire span {
            color: #ef4444;
            font-weight: 600;
        }
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }
        .warning-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            margin-bottom: 24px;
        }
        .warning-box p {
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }
        .email-footer a {
            color: #16a34a;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-card">
            {{-- Header --}}
            <div class="email-header">
                <div class="logo-text">🎓 PPDB 2026/2027</div>
                <div class="logo-subtitle">Portal Penerimaan Peserta Didik Baru</div>
            </div>

            {{-- Body --}}
            <div class="email-body">
                <p class="greeting">
                    Halo, <strong>{{ $nama }}</strong>!
                </p>
                <p class="description">
                    Terima kasih telah mendaftar di Portal PPDB. Gunakan kode verifikasi berikut
                    untuk mengaktifkan akun Anda. Jangan berikan kode ini kepada siapapun.
                </p>

                <p class="otp-label">Kode Verifikasi OTP</p>
                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                <p class="otp-expire">
                    Kode ini berlaku selama <span>10 menit</span> dan hanya bisa digunakan sekali.
                </p>

                <hr class="divider">

                <div class="warning-box">
                    <p>
                        ⚠️ Jika Anda tidak melakukan pendaftaran di Portal PPDB, abaikan email ini.
                        Akun Anda tidak akan diaktifkan tanpa verifikasi.
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="email-footer">
                <p>
                    Email ini dikirim secara otomatis oleh sistem PPDB.<br>
                    Mohon jangan membalas email ini.
                </p>
                <p style="margin-top: 8px;">
                    &copy; {{ date('Y') }} Portal PPDB — Sistem Informasi Sekolah
                </p>
            </div>
        </div>
    </div>
</body>
</html>
