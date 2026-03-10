<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function update(User $user, Room $room): bool
    {
        return $user->id === $room->host_id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->id === $room->host_id;
    }
}
