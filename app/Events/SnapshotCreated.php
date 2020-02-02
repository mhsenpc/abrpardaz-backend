<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SnapshotCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $snapshot_id;
    private $user_id;

    /**
     * Create a new event instance.
     *
     * @param $user_id
     * @param $snapshot_id
     */
    public function __construct($user_id, $snapshot_id)
    {
        $this->user_id = $user_id;
        $this->snapshot_id = $snapshot_id;
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
        return 'snapshot.created';
    }
}
