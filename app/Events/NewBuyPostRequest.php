<?php

namespace App\Events;

use App\Post;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewBuyPostRequest
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $post;
    public $interested_user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $interested_user, Post $post)
    {
        $this->post = $post;
        $this->interested_user = $interested_user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
