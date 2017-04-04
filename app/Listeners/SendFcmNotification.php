<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFcmNotification
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
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $this->sendFcmMessage($event->message->receiver, 'New Message', $event->message->receiver->username.' : '.$event->message->message);
    }
}
