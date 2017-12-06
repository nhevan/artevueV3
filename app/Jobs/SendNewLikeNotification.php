<?php

namespace App\Jobs;

use App\Like;
use App\User;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Acme\Transformers\ActivityTransformer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewLikeNotification implements ShouldQueue
{
    public $like;
    public $notification_text;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Like $like)
    {
        $this->like = array_merge(['type' => 'like'], $like->load('post', 'user')->toArray());
        $this->prepareNotification();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target = $this->getOneSignalTarget($this->like['post_owner_id']);

        $this->sendNotificationToSegment($this->notification_text, $this->like, $target);
    }

    public function prepareNotification()
    {
        $transformer = new ActivityTransformer;
        $this->like = $transformer->transform($this->like);

        $this->notification_text = "{$this->like['name']}({$this->like['username']}) liked your post.";
    }
}
