<?php

namespace Tests\Unit\Services\Ppdb;

use Tests\TestCase;
use App\Services\Ppdb\PembukaanPpdbService;
use App\Repositories\Ppdb\PembukaanPpdbRepositoryInterface;
use App\Models\Ppdb\PembukaanPpdb;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Mockery;
use Illuminate\Support\Facades\DB;

class PembukaanPpdbServiceTest extends TestCase
{
    protected $service;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repositoryMock = Mockery::mock(PembukaanPpdbRepositoryInterface::class);
        DB::shouldReceive('transaction')->andReturnSelf();
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();

        $this->service = new PembukaanPpdbService(
            $this->repositoryMock,
            Mockery::mock(LogActivityService::class)->shouldIgnoreMissing(),
            Mockery::mock(ResponseService::class)
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_pembukaan()
    {
        $data = [
            'tahun_pelajaran_id' => 1,
            'status' => 'tutup'
        ];

        $pembukaan = new PembukaanPpdb(['nama' => 'PPDB 2026', 'tahun_pelajaran_id' => 1]);
        $this->repositoryMock->shouldReceive('create')->once()->with($data)->andReturn($pembukaan);

        $result = $this->service->store($data, 1);

        $this->assertTrue($result['success']);
    }

    public function test_cannot_create_duplicate_active_pembukaan()
    {
        $data = [
            'tahun_pelajaran_id' => 1,
            'status' => 'buka'
        ];

        // existing buka
        $this->repositoryMock->shouldReceive('all')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection([new PembukaanPpdb()]));

        $result = $this->service->store($data, 1);

        $this->assertFalse($result['success']);
    }

    public function test_toggle_status_works()
    {
        $pembukaan = new PembukaanPpdb(['id' => 1, 'status' => 'aktif', 'tahun_pelajaran_id' => 1, 'nama' => 'Test']);
        
        $this->repositoryMock->shouldReceive('findById')->andReturn($pembukaan);
        $this->repositoryMock->shouldReceive('all')->andReturn(new \Illuminate\Database\Eloquent\Collection([])); // no conflicts
        $this->repositoryMock->shouldReceive('update')->once()->with(1, ['status' => 'buka'])->andReturn($pembukaan);

        $result = $this->service->toggleStatus(1, 1);

        $this->assertTrue($result['success']);
    }

    public function test_cannot_delete_pembukaan_with_active_jalur()
    {
        $pembukaan = Mockery::mock(PembukaanPpdb::class)->makePartial();
        $pembukaan->id = 1;
        $jalur = new \stdClass();
        $jalur->status = 'aktif';
        $pembukaan->setRelation('jalurPendaftaran', collect([$jalur]));

        $this->repositoryMock->shouldReceive('findById')->once()->andReturn($pembukaan);

        $result = $this->service->destroy(1, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('karena masih memiliki 1 jalur pendaftaran yang aktif', $result['message']);
    }
}
