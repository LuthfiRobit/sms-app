<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ $judul }}</title>
    <style>
        /* ===== RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        /* ===== BASE ===== */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            line-height: 1.6;
        }

        /* ===== WRAPPER ===== */
        .email-wrapper {
            width: 100%;
            background-color: #F8FAFC;
            padding: 32px 16px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        /* ===== HEADER ===== */
        .email-header {
            background: linear-gradient(135deg, {{ $colorScheme[0] }} 0%, {{ $colorScheme[0] }}CC 100%);
            padding: 40px 40px 32px;
            text-align: center;
        }

        .school-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.95);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .event-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
            line-height: 1;
        }

        .email-header h1 {
            color: #FFFFFF;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.3;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }

        /* ===== BODY ===== */
        .email-body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 16px;
            color: #475569;
            margin-bottom: 8px;
        }

        .greeting strong {
            color: #1E293B;
            font-weight: 600;
        }

        .divider {
            border: none;
            border-top: 1px solid #E2E8F0;
            margin: 24px 0;
        }

        .message-box {
            background-color: {{ $colorScheme[1] }};
            border-left: 4px solid {{ $colorScheme[0] }};
            border-radius: 0 8px 8px 0;
            padding: 20px 24px;
            font-size: 15px;
            color: #334155;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .info-table tr td {
            padding: 10px 0;
            border-bottom: 1px solid #F1F5F9;
            font-size: 14px;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-label {
            color: #94A3B8;
            font-weight: 500;
            width: 40%;
        }

        .info-value {
            color: #1E293B;
            font-weight: 600;
        }

        /* ===== CTA BUTTON ===== */
        .cta-wrapper {
            text-align: center;
            margin: 28px 0;
        }

        .cta-button {
            display: inline-block;
            background: {{ $colorScheme[0] }};
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px {{ $colorScheme[0] }}66;
            transition: all 0.2s;
        }

        /* ===== NOTICE BOX ===== */
        .notice-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 16px 20px;
            font-size: 13px;
            color: #64748B;
            line-height: 1.6;
        }

        .notice-box strong {
            color: #475569;
        }

        /* ===== FOOTER ===== */
        .email-footer {
            background-color: #F1F5F9;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #E2E8F0;
        }

        .footer-logo {
            font-size: 16px;
            font-weight: 700;
            color: {{ $colorScheme[0] }};
            margin-bottom: 8px;
        }

        .footer-text {
            font-size: 12px;
            color: #94A3B8;
            line-height: 1.6;
        }

        .footer-text a {
            color: {{ $colorScheme[0] }};
            text-decoration: none;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid #E2E8F0;
            margin: 12px 0;
        }

        /* ===== RESPONSIVE ===== */
        @media only screen and (max-width: 600px) {
            .email-header { padding: 28px 24px 22px; }
            .email-body { padding: 24px 24px; }
            .email-footer { padding: 20px 24px; }
            .email-header h1 { font-size: 18px; }
            .cta-button { padding: 12px 28px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">

            {{-- ===== HEADER ===== --}}
            <div class="email-header">
                <div class="school-badge">PPDB SMK · TA {{ $tahun }}/{{ $tahun + 1 }}</div>
                <span class="event-icon">{{ $iconClass }}</span>
                <h1>{{ $judul }}</h1>
            </div>

            {{-- ===== BODY ===== --}}
            <div class="email-body">

                <p class="greeting">
                    Halo, <strong>{{ $namaPenerima }}</strong> 👋
                </p>

                <hr class="divider">

                <div class="message-box">
                    {!! nl2br(e($body)) !!}
                </div>

                {{-- Info table jika ada data konteks --}}
                @if (!empty($data) && is_array($data))
                    @php
                        $displayData = array_filter($data, fn($v) => !is_array($v) && !is_null($v) && $v !== '');
                        $labels = [
                            'no_pendaftaran'  => 'No. Pendaftaran',
                            'nama_peserta'    => 'Nama Peserta',
                            'jalur'           => 'Jalur Pendaftaran',
                            'jurusan'         => 'Program Keahlian',
                            'status'          => 'Status',
                            'nominal'         => 'Nominal Pembayaran',
                            'catatan'         => 'Catatan Verifikasi',
                            'tanggal'         => 'Tanggal',
                        ];
                    @endphp
                    @if (!empty($displayData))
                        <table class="info-table">
                            @foreach($displayData as $key => $value)
                                @if(array_key_exists($key, $labels))
                                    <tr>
                                        <td class="info-label">{{ $labels[$key] }}</td>
                                        <td class="info-value">{{ $value }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    @endif
                @endif

                {{-- CTA Button --}}
                <div class="cta-wrapper">
                    <a href="{{ config('app.url') }}/portal/dashboard" class="cta-button">
                        Lihat Status Pendaftaran →
                    </a>
                </div>

                <div class="notice-box">
                    <strong>⚠️ Penting:</strong> Email ini dikirim secara otomatis oleh sistem PPDB.
                    Mohon <strong>jangan membalas</strong> email ini. Jika ada pertanyaan,
                    hubungi panitia PPDB melalui halaman portal atau datang langsung ke sekolah.
                </div>

            </div>

            {{-- ===== FOOTER ===== --}}
            <div class="email-footer">
                <div class="footer-logo">🏫 PPDB SMK</div>
                <hr class="footer-divider">
                <p class="footer-text">
                    Sistem Penerimaan Murid Baru (PPDB) Online<br>
                    Email ini ditujukan khusus untuk <strong>{{ $namaPenerima }}</strong><br>
                    <br>
                    Jika Anda menerima email ini secara tidak sengaja, abaikan saja.<br>
                    &copy; {{ $tahun }} PPDB SMK · Semua hak dilindungi.
                </p>
            </div>

        </div>

        {{-- Sub-footer --}}
        <p style="text-align:center; font-size:11px; color:#B0BAC9; margin-top:16px; padding: 0 16px;">
            Email ini dihasilkan oleh sistem otomatis PPDB SMK.
            Dikirim pada {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY · HH:mm') }} WIB.
        </p>
    </div>
</body>
</html>
