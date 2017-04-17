<?php

namespace App\Jobs;

use App\Post;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPostDeletedNotification implements ShouldQueue
{
    protected $post;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendFcmMessage($this->post->owner, 'Post Deleted', "You deleted one of your post", "normal");
        // $this->sendPusherNotification('post_deleted', 'general-notification', ["message" => "A post has been deleted."]);
    }
}
