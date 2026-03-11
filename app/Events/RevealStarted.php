<?php

namespace App\Events;

use App\Models\VoteSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RevealStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly VoteSession $session,
        public readonly array $votes,
        public readonly ?float $average,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('room.'.$this->session->room_id)];
    }

    public function broadcastAs(): string
    {
        return 'reveal.started';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'votes' => $this->votes,
            'average' => $this->average,
        ];
    }
}
