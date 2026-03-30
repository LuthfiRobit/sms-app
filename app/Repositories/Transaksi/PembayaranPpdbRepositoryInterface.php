<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\PembayaranPpdb;

interface PembayaranPpdbRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PembayaranPpdb;
    public function create(array $data): PembayaranPpdb;
    public function update(int $id, array $data): PembayaranPpdb;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findByOrderId(string $orderId): ?PembayaranPpdb;
    public function updateByOrderId(string $orderId, array $data): bool;
}
