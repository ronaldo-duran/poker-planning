<?php

namespace App\Repositories;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomRepository implements RoomRepositoryInterface
{
    public function findById(int $id): ?Room
    {
        return Room::with(['host', 'users'])->find($id);
    }

    public function findByCode(string $code): ?Room
    {
        return Room::with(['host', 'users'])->where('code', $code)->first();
    }

    public function create(array $data): Room
    {
        return Room::create($data);
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);

        return $room->fresh();
    }

    public function delete(Room $room): bool
    {
        return $room->delete();
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Room::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->orWhere('host_id', $userId)
            ->with('host')
            ->latest()
            ->paginate($perPage);
    }
}
