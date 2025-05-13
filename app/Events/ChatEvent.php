<?php

namespace App\Events;

use App\Models\ChatHistory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use function tests\data;

class ChatEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    private string $_eventType;
    private ChatHistory $_message;
    public function __construct($_eventType, ChatHistory $_message)
    {
        $this->_eventType = $_eventType;
        $this->_message = $_message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            'private-chat.'. $this->_message->channel_id,
        ];
    }

    public function broadcastWith(): array{
        return [
            'data' => $this->_message,
        ];
    }

    public function broadcastAs(){
        return $this->_eventType;
    }
}
