<?php

namespace App\Services;

use App\Events\RoomStateChanged;
use App\Events\UserJoinedRoom;
use App\Events\UserLeftRoom;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Support\Str;

class RoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $roomRepository,
    ) {}

    public function createRoom(User $user, array $data): Room
    {
        $room = $this->roomRepository->create([
            'name' => $data['name'],
            'code' => $this->generateUniqueCode(),
            'logo' => $data['logo'] ?? null,
            'host_id' => $user->id,
            'card_config' => $data['card_config'] ?? [0, 1, 2, 3, 5, 8, 13, 21, '?'],
            'state' => 'waiting',
        ]);

        $room->users()->attach($user->id, ['role' => 'host', 'is_online' => true]);

        return $room->load('host', 'users');
    }

    public function joinRoom(User $user, string $code): Room
    {
        $room = $this->roomRepository->findByCode($code);

        if (! $room) {
            abort(404, 'Room not found.');
        }

        if (! $room->users()->where('user_id', $user->id)->exists()) {
            $room->users()->attach($user->id, ['role' => 'voter', 'is_online' => true]);
        } else {
            $room->users()->updateExistingPivot($user->id, ['is_online' => true]);
        }

        broadcast(new UserJoinedRoom($room, $user))->toOthers();

        return $room->load('host', 'users');
    }

    public function leaveRoom(User $user, Room $room): void
    {
        $room->users()->updateExistingPivot($user->id, ['is_online' => false]);
        broadcast(new UserLeftRoom($room, $user));
    }

    public function updateState(Room $room, string $state): Room
    {
        $updated = $this->roomRepository->update($room, ['state' => $state]);
        broadcast(new RoomStateChanged($room, $state));

        return $updated;
    }

    public function toggleEmojis(Room $room): Room
    {
        return $this->roomRepository->update($room, ['emojis_blocked' => ! $room->emojis_blocked]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Room::where('code', $code)->exists());

        return $code;
    }
}
