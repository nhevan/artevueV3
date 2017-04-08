<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessagesReadNotification implements ShouldQueue
{
    protected $data;
    protected $friend_id;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $friend_id)
    {
        //
        $this->data = $data;
        $this->friend_id = $friend_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendPusherNotification($this->friend_id.'-message-read-channel','message-read', $this->data);
    }
}
