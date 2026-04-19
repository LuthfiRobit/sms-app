<?php

namespace Tests\Unit\Services\Transaksi;

use Tests\TestCase;
use App\Services\Transaksi\PembayaranService;
use App\Repositories\Transaksi\PembayaranPpdbRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Ppdb\BiayaRegistrasiRepositoryInterface;
use App\Services\NotifikasiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Models\Transaksi\PembayaranPpdb as Pembayaran;
use App\Models\Transaksi\Pendaftaran;
use App\Models\Ppdb\BiayaRegistrasi;
use Mockery;
use Illuminate\Support\Facades\DB;

class PembayaranServiceTest extends TestCase
{
    protected $service;
    protected $pembayaranRepoMock;
    protected $pendaftaranRepoMock;
    protected $biayaRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pembayaranRepoMock = Mockery::mock(PembayaranPpdbRepositoryInterface::class);
        $this->pendaftaranRepoMock = Mockery::mock(PendaftaranRepositoryInterface::class);
        $this->biayaRepoMock = Mockery::mock(BiayaRegistrasiRepositoryInterface::class);
        
        DB::shouldReceive('transaction')->andReturnUsing(function ($closure) {
            return $closure();
        });

        $this->service = new PembayaranService(
            $this->pembayaranRepoMock,
            $this->pendaftaranRepoMock,
            $this->biayaRepoMock,
            Mockery::mock(NotifikasiService::class)->shouldIgnoreMissing(),
            Mockery::mock(LogActivityService::class)->shouldIgnoreMissing(),
            Mockery::mock(ResponseService::class)
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cannot_pay_draft_pendaftaran()
    {
        $pendaftaran = new Pendaftaran(['id' => 1, 'no_pendaftaran' => '123', 'status' => Pendaftaran::STATUS_DRAFT]);

        $this->pendaftaranRepoMock->shouldReceive('findById')->once()->andReturn($pendaftaran);

        $result = $this->service->createSnapToken(1, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Pembayaran hanya bisa dilakukan setelah pendaftaran di-submit', $result['message']);
    }

    public function test_idempotent_snap_token_creation()
    {
        $pendaftaran = new Pendaftaran(['id' => 1, 'status' => Pendaftaran::STATUS_VERIFIKASI]);

        $existingPayment = new Pembayaran([
            'id' => 1, 
            'pendaftaran_id' => 1,
            'snap_token' => 'EXISTING_TOKEN',
            'status' => Pembayaran::STATUS_PENDING,
            'amount' => 100000,
            'order_id' => '123'
        ]);

        $this->pendaftaranRepoMock->shouldReceive('findById')->once()->andReturn($pendaftaran);
        $this->pembayaranRepoMock->shouldReceive('all')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection([$existingPayment]));

        $result = $this->service->createSnapToken(1, 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('EXISTING_TOKEN', $result['data']['snap_token']);
    }

    public function test_webhook_signature_validation_fails_if_invalid()
    {
        config(['midtrans.server_key' => 'server-key-123']);
        $payload = [
            'order_id' => 'ORD-123',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'invalid-signature'
        ];
        
        $result = $this->service->handleCallback($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid signature. Callback rejected.', $result['message']);
    }

    public function test_duplicate_webhook_not_processed_twice()
    {
        config(['midtrans.server_key' => 'server-key-123']);
        $payload = [
            'order_id' => 'ORD-123',
            'status_code' => '200',
            'gross_amount' => '10000.00',
        ];
        $signature = hash('sha512', 'ORD-12320010000.00server-key-123');
        $payload['signature_key'] = $signature;
        $payload['transaction_status'] = 'settlement';
        
        $pembayaran = new Pembayaran([
            'id' => 1, 
            'order_id' => 'ORD-123',
            'status' => Pembayaran::STATUS_PAID
        ]);

        $this->pembayaranRepoMock->shouldReceive('findByOrderId')->once()->andReturn($pembayaran);

        $result = $this->service->handleCallback($payload);

        $this->assertTrue($result['success']); 
        $this->assertStringContainsString('Callback sudah diproses sebelumnya', $result['message']);
    }
}
