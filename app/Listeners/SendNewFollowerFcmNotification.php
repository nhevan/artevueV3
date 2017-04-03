<?php

namespace App\Listeners;

use App\Events\NewFollower;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewFollowerFcmNotification
{
    use NotificationSwissKnife;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewFollower  $event
     * @return void
     */
    public function handle(NewFollower $event)
    {
        $this->sendFcmMessage($event->user_id, 'New Follower', Auth::user()->name.' started following you.');
    }
}
