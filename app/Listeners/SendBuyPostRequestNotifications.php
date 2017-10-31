<?php

namespace App\Listeners;

use App\Message;
use App\Mail\BuyPostRequestMail;
use App\Events\NewBuyPostRequest;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendNewMessageNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBuyPostRequestNotifications implements ShouldQueue
{
    use InteractsWithQueue, CounterSwissKnife;

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
     * @param  NewBuyPostRequest  $event
     * @return void
     */
    public function handle(NewBuyPostRequest $event)
    {
        $this->sendMessageAndNotifications($event);
        Mail::to($event->post->owner->email)->queue(new BuyPostRequestMail($event));
    }

    /**
     * sends a message to the post owner letting him know that someone is interested in his/her artwork
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    private function sendMessageAndNotifications($event)
    {
        $message = new Message;
        $message->sender_id = $event->interested_user->id;
        $message->receiver_id = $event->post->owner->id;
        $message->message = "I would like to buy this art from you. This message should be more specific.";
        $message->is_post = $event->post->id;
        $message->url = $event->post->image;
        $message->save();

        $this->updateTotalMessageCountInParticipantsTable($event->interested_user->id, $event->post->owner->id, $message->id);

        dispatch(new SendNewMessageNotification($message));
    }
}
