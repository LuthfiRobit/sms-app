<?php

namespace Tests\Unit\Services\Transaksi;

use Tests\TestCase;
use App\Services\Transaksi\PendaftaranService;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranFieldValueRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use App\Repositories\Ppdb\SyaratPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\JadwalPendaftaranRepositoryInterface;
use App\Services\NotifikasiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Models\Transaksi\Pendaftaran;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\Peserta\Peserta;
use Mockery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PendaftaranServiceTest extends TestCase
{
    protected $service;
    protected $pendaftaranRepoMock;
    protected $jadwalRepoMock;
    protected $kuotaRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pendaftaranRepoMock = Mockery::mock(PendaftaranRepositoryInterface::class);
        $this->jadwalRepoMock = Mockery::mock(JadwalPendaftaranRepositoryInterface::class);
        $this->kuotaRepoMock = Mockery::mock(KuotaJurusanRepositoryInterface::class);
        
        // Mock DB facade methods that are called
        DB::shouldReceive('transaction')->andReturnUsing(function ($closure) {
            return $closure();
        });

        $this->service = new PendaftaranService(
            $this->pendaftaranRepoMock,
            Mockery::mock(PendaftaranFieldValueRepositoryInterface::class),
            Mockery::mock(DokumenPesertaRepositoryInterface::class),
            Mockery::mock(SyaratPendaftaranRepositoryInterface::class),
            Mockery::mock(FormulirPendaftaranRepositoryInterface::class),
            $this->kuotaRepoMock,
            $this->jadwalRepoMock,
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

    public function test_can_create_pendaftaran()
    {
        $jadwalModel = new \App\Models\Ppdb\JadwalPendaftaran();
        $this->jadwalRepoMock->shouldReceive('findAktifByJalurAndTipe')->once()->andReturn($jadwalModel);
        $this->kuotaRepoMock->shouldReceive('all')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection([]));
        
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('whereIn')->andReturnSelf();
        $queryMock->shouldReceive('first')->andReturn(null);
        $this->pendaftaranRepoMock->shouldReceive('datatable')->andReturn($queryMock);
        
        // bypass generateNoPendaftaranUnik logic by mocking directly if we need to or just let it generate (but it tries to hit Repo)
        $this->pendaftaranRepoMock->shouldReceive('generateNoPendaftaran')->andReturn('PPDB202600001');
        $this->pendaftaranRepoMock->shouldReceive('findByNoPendaftaran')->andReturn(null);

        $pendaftaran = new Pendaftaran(['no_pendaftaran' => 'PPDB202600001']);
        $pendaftaran->setRelation('peserta', new Peserta());
        $pendaftaran->setRelation('jalurPendaftaran', new JalurPendaftaran());
        $pendaftaran->setRelation('tahunPelajaran', new \stdClass());
        
        $this->pendaftaranRepoMock->shouldReceive('create')->once()->andReturn($pendaftaran);

        $result = $this->service->store(1, 1, 1, 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('PPDB202600001', $result['data']->no_pendaftaran);
    }

    public function test_cannot_double_register_same_jalur()
    {
        $jadwalModel = new \App\Models\Ppdb\JadwalPendaftaran();
        $this->jadwalRepoMock->shouldReceive('findAktifByJalurAndTipe')->once()->andReturn($jadwalModel);
        $this->kuotaRepoMock->shouldReceive('all')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection([]));

        $existing = new Pendaftaran(['no_pendaftaran' => 'REG-1']);
        
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('whereIn')->andReturnSelf();
        $queryMock->shouldReceive('first')->andReturn($existing);
        $this->pendaftaranRepoMock->shouldReceive('datatable')->andReturn($queryMock);

        $result = $this->service->store(1, 1, 1, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Peserta sudah memiliki pendaftaran aktif', $result['message']);
    }

    public function test_submit_requires_complete_documents_and_forms()
    {
        $pendaftaran = new Pendaftaran(['status' => Pendaftaran::STATUS_DRAFT]);
        $pendaftaran->id = 1;
        $pendaftaran->jalur_pendaftaran_id = 1;
        $this->pendaftaranRepoMock->shouldReceive('findById')->atLeast()->once()->andReturn($pendaftaran);

        // To naturally trigger validation errors, we mock the repo it relies on.
        $syaratRepo = Mockery::mock(SyaratPendaftaranRepositoryInterface::class);
        $syaratRepo->shouldReceive('all')->andReturn(new \Illuminate\Database\Eloquent\Collection([new \App\Models\Ppdb\SyaratPendaftaran(['nama' => 'Syarat 1', 'wajib' => true])]));

        $formulirRepo = Mockery::mock(FormulirPendaftaranRepositoryInterface::class);
        $formulirRepo->shouldReceive('all')->andReturn(new \Illuminate\Database\Eloquent\Collection());

        $dokumenRepo = Mockery::mock(DokumenPesertaRepositoryInterface::class);
        $dokumenRepo->shouldReceive('findByPendaftaranId')->andReturn(new \Illuminate\Database\Eloquent\Collection([])); // Empty to trigger "syarat wajib" missing error

        // Since it's private, we just test the real service without partial mock
        // Instead of mocking PendaftaranService, we use the real one.
        $this->service = new PendaftaranService(
            $this->pendaftaranRepoMock,
            Mockery::mock(PendaftaranFieldValueRepositoryInterface::class)->shouldIgnoreMissing(),
            $dokumenRepo,
            $syaratRepo,
            $formulirRepo,
            $this->kuotaRepoMock,
            $this->jadwalRepoMock,
            Mockery::mock(NotifikasiService::class)->shouldIgnoreMissing(),
            Mockery::mock(LogActivityService::class)->shouldIgnoreMissing(),
            Mockery::mock(ResponseService::class)
        );

        $result = $this->service->submit(1, 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Pendaftaran belum dapat disubmit karena ada kelengkapan yang belum terpenuhi.', $result['message']);
    }

    public function test_verifikasi_approve_changes_status()
    {
        $pendaftaran = new Pendaftaran(['id' => 1, 'no_pendaftaran' => 'P-1', 'status' => Pendaftaran::STATUS_SUBMIT]);
        $this->pendaftaranRepoMock->shouldReceive('findById')->atLeast()->once()->andReturn($pendaftaran);

        $this->pendaftaranRepoMock->shouldReceive('updateStatus')
            ->with(1, Pendaftaran::STATUS_VERIFIKASI, Mockery::type('array'))
            ->once()
            ->andReturn(true);

        $result = $this->service->verifikasi(1, 'approve', null, 2);

        $this->assertTrue($result['success']);
    }

    public function test_verifikasi_reject_resets_to_draft()
    {
        $pendaftaran = new Pendaftaran(['id' => 1, 'no_pendaftaran' => 'P-1', 'status' => Pendaftaran::STATUS_SUBMIT]);
        $this->pendaftaranRepoMock->shouldReceive('findById')->atLeast()->once()->andReturn($pendaftaran);

        $this->pendaftaranRepoMock->shouldReceive('updateStatus')
            ->with(1, Pendaftaran::STATUS_DRAFT, Mockery::hasKey('catatan_verifikasi'))
            ->once()
            ->andReturn(true);

        $result = $this->service->verifikasi(1, 'reject', 'Berkas buram', 2);

        $this->assertTrue($result['success']);
    }
}
