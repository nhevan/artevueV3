<?php

namespace App\Listeners;

use App\Events\MessageSent;
use Illuminate\Support\Facades\App;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPusherNotification
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
        $this->sendPusherNotification('message_channel', 'personal-messaging', $event->message);
        $this->sendPusherNotification($event->message->receiver_id.'-message-channel', 'new-message', $event->message, ['type'=>'new message']);
    }
}
