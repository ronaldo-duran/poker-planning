<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    $room = Room::find($roomId);
    if ($room && $room->users()->where('user_id', $user->id)->exists()) {
        return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar];
    }

    return false;
});
