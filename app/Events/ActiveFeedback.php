<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiveFeedback implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $_objectId;
    private $_objectType;
    private $_feedback;

    private $eventType;

    /**
     * Create a new event instance.
     */
    public function __construct($objectId, $objectType, $feedback, $eventType)
    {
        $this->_objectId = $objectId;
        $this->_objectType = $objectType;
        $this->_feedback = $feedback;
        $this->eventType = $eventType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return ['private-feedback.'. $this->_objectType .'.'. $this->_objectId];
    }

    public function broadcastWith(): array
    {
        return [
            'data' => $this->_feedback,
        ];
    }

    public function broadcastAs(): string
    {
        return match ($this->eventType) {
            'new' => 'new',
            'update' => 'update',
            'delete' => 'delete',
            default => 'default'
        };
    }
}
