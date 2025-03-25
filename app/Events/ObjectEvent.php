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

class ObjectEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $_objects;
    private $_eventType;
    private $_data;
    /**
     * Create a new event instance.
     */
    public function __construct($objects, $eventType, $data)
    {
        $this->_objects = $objects;
        $this->_eventType = $eventType;
        $this->_data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastAs() : string{
        if($this->_eventType == "channels"){
            return "channels";
        }
        return '*';
    }

    public function broadcastWith(): array
    {
        return [
            'data' => $this->_data,
        ];
    }

    public function broadcastOn()
    {
        return collect($this->_objects)->map(function ($object) {
            return "$object->member_type.$object->member_id";
        })->toArray();
    }
}
