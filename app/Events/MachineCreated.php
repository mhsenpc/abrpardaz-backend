<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MachineCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $machine_id;
    public $machine_name;
    private $user_id;

    /**
     * Create a new event instance.
     *
     * @param $machine
     */
    public function __construct($machine)
    {
        $this->machine_id = $machine->id;
        $this->machine_name = $machine->name;
        $this->user_id = $machine->user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user-'.$this->user_id);
    }

    public function broadcastAs()
    {
        return 'server.created';
    }
}
