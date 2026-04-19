<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbNotifikasiMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param string $judul        Judul notifikasi (dijadikan subject email)
     * @param string $body         Isi pesan HTML/plain
     * @param string $event        Nama event (untuk context & styling)
     * @param string $namaPenerima Nama lengkap penerima email
     */
    public function __construct(
        public readonly string $judul,
        public readonly string $body,
        public readonly string $event,
        public readonly string $namaPenerima,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->judul,
            from: new \Illuminate\Mail\Mailables\Address(
                address: config('mail.from.address', 'noreply@ppdb-smk.sch.id'),
                name: 'PPDB SMK',
            ),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ppdb-notifikasi',
            with: [
                'judul'        => $this->judul,
                'body'         => $this->body,
                'event'        => $this->event,
                'namaPenerima' => $this->namaPenerima,
                'tahun'        => now()->year,
                'iconClass'    => $this->resolveIconClass(),
                'colorScheme'  => $this->resolveColorScheme(),
            ],
        );
    }

    /**
     * Tentukan ikon berdasarkan tipe event.
     */
    private function resolveIconClass(): string
    {
        return match ($this->event) {
            'pendaftaran_submit'    => '📋',
            'pembayaran_success'    => '✅',
            'verifikasi_approve'    => '✔️',
            'verifikasi_reject'     => '⚠️',
            'pengumuman_lulus'      => '🎉',
            'pengumuman_tidak_lulus'=> '📄',
            'daftar_ulang_reminder' => '🔔',
            'admin_pendaftaran_baru'=> '📬',
            default                 => 'ℹ️',
        };
    }

    /**
     * Tentukan skema warna berdasarkan tipe event.
     * Return array [primary, accent] sebagai hex color.
     */
    private function resolveColorScheme(): array
    {
        return match ($this->event) {
            'pendaftaran_submit'    => ['#3B82F6', '#DBEAFE'], // Blue
            'pembayaran_success'    => ['#10B981', '#D1FAE5'], // Green
            'verifikasi_approve'    => ['#059669', '#ECFDF5'], // Emerald
            'verifikasi_reject'     => ['#F59E0B', '#FEF3C7'], // Amber
            'pengumuman_lulus'      => ['#7C3AED', '#EDE9FE'], // Violet
            'pengumuman_tidak_lulus'=> ['#6B7280', '#F3F4F6'], // Gray
            'daftar_ulang_reminder' => ['#EF4444', '#FEE2E2'], // Red
            'admin_pendaftaran_baru'=> ['#0EA5E9', '#E0F2FE'], // Sky
            default                 => ['#3B82F6', '#DBEAFE'],
        };
    }
}
