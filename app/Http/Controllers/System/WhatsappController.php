<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\Integrations\FonnteService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected FonnteService $fonnteService;

    protected LogActivityService $logActivityService;

    protected ResponseService $responseService;

    public function __construct(
        FonnteService $fonnteService,
        LogActivityService $logActivityService,
        ResponseService $responseService
    ) {
        $this->fonnteService = $fonnteService;
        $this->logActivityService = $logActivityService;
        $this->responseService = $responseService;
    }

    /**
     * Tampilkan halaman uji kirim WhatsApp Fonnte.
     */
    public function index()
    {
        $this->logActivityService->log('Access Whatsapp Test Index', 'Membuka halaman dashboard uji WhatsApp Fonnte');

        $config = [
            'token_exists' => ! empty(config('services.fonnte.token')),
            'sender' => config('services.fonnte.sender', 'Belum dikonfigurasi'),
        ];

        return view('admin.system.whatsapp.index', compact('config'));
    }

    /**
     * Dapatkan status koneksi device Fonnte secara asynchronous (AJAX).
     */
    public function status(): JsonResponse
    {
        $status = $this->fonnteService->checkDeviceStatus();

        if ($status['success']) {
            return $this->responseService->success($status['response'], 'Status device berhasil diambil');
        }

        return $this->responseService->error(
            $status['response']['reason'] ?? 'Gagal mengambil status device dari Fonnte',
            500,
            $status['response']
        );
    }

    /**
     * Jalankan proses pengiriman pesan uji WhatsApp Fonnte.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'recipient' => 'required|string|min:9',
            'message' => 'required|string',
        ], [
            'recipient.required' => 'Nomor tujuan wajib diisi.',
            'recipient.min' => 'Nomor tujuan minimal 9 digit.',
            'message.required' => 'Isi pesan wajib diisi.',
        ]);

        $recipient = $request->input('recipient');
        $message = $request->input('message');

        $result = $this->fonnteService->kirimPesan($recipient, $message);

        $statusStr = $result['success'] ? 'Sukses' : 'Gagal';
        $this->logActivityService->log(
            'Kirim Test WA',
            "Mengirim pesan WhatsApp ke {$recipient} - Status: {$statusStr}"
        );

        if ($result['success']) {
            return $this->responseService->success($result['response'], 'Pesan WhatsApp berhasil terkirim!');
        }

        $reason = 'Gagal mengirim WhatsApp';
        if (is_array($result['response']) && isset($result['response']['reason'])) {
            $reason = $result['response']['reason'];
        } elseif (is_string($result['response'])) {
            $reason = $result['response'];
        }

        return $this->responseService->error($reason, 400, $result['response']);
    }
}
