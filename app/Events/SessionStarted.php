<?php

namespace App\Events;

use App\Models\VoteSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly VoteSession $session,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.'.$this->session->room_id)];
    }

    public function broadcastAs(): string
    {
        return 'session.started';
    }

    public function broadcastWith(): array
    {
        return [
            'session' => [
                'id' => $this->session->id,
                'room_id' => $this->session->room_id,
                'story_title' => $this->session->story_title,
                'story_description' => $this->session->story_description,
                'status' => $this->session->status,
                'average' => $this->session->average,
                'revealed_at' => $this->session->revealed_at,
                'created_at' => $this->session->created_at,
            ],
        ];
    }
}
