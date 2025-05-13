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

    private $_extraData;
    /**
     * Create a new event instance.
     */
    public function __construct($objects, $eventType, $data, $extraData = [])
    {
        $this->_objects = $objects;
        $this->_eventType = $eventType;
        $this->_data = $data;
        $this->_extraData = $extraData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastAs() : string{

        return match ($this->_eventType) {
            'channels' => 'channels',
            default => '*',
        };
    }

    public function broadcastWith(): array
    {
        $data = ['data' => $this->_data, 'extra_data' => $this->_extraData];
        return $data;
    }

    public function broadcastOn()
    {
        return collect($this->_objects)->map(function ($object) {
            return "$object->member_type.$object->member_id";
        })->toArray();
    }
}
