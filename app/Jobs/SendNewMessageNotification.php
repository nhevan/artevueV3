<?php

namespace App\Jobs;

use App\Message;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendFcmMessage($this->message->receiver, 'New Message', $this->message->sender->username.' : '.$this->message->message);

        $this->sendPusherNotification('message_channel', 'personal-messaging', $this->message);
        $this->sendPusherNotification($this->message->receiver_id.'-message-channel', 'new-message', $this->message, ['type'=>'new message']);
    }
}
