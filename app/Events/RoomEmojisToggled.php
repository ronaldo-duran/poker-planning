<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomEmojisToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Room $room,
        public readonly bool $emojisBlocked,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.'.$this->room->id)];
    }

    public function broadcastAs(): string
    {
        return 'room.emojis_toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->room->id,
            'emojis_blocked' => $this->emojisBlocked,
        ];
    }
}
