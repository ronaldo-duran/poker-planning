<?php

namespace App\Repositories\Contracts;

use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoomRepositoryInterface
{
    public function findById(int $id): ?Room;

    public function findByCode(string $code): ?Room;

    public function create(array $data): Room;

    public function update(Room $room, array $data): Room;

    public function delete(Room $room): bool;

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator;
}
