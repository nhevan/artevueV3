<?php

namespace App\Jobs;

use App\Pin;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Acme\Transformers\ActivityTransformer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewPinNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    public $pin;
    public $notification_text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Pin $pin)
    {
        $this->pin = array_merge(['type' => 'pin'], $pin->load('post', 'user')->toArray());
        $this->prepareNotification();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target = $this->getOneSignalTarget($this->pin['post_owner_id']);

        $this->sendNotificationToSegment($this->notification_text, $this->pin, $target);
    }

    public function prepareNotification()
    {
        $transformer = new ActivityTransformer;
        $this->pin = $transformer->transform($this->pin);

        $this->notification_text = "{$this->pin['name']}({$this->pin['username']}) pinned your post.";
    }
}
