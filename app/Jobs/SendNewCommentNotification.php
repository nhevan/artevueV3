<?php

namespace App\Jobs;

use App\User;
use App\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Acme\Transformers\ActivityTransformer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewCommentNotification implements ShouldQueue
{
    public $comment;
    public $notification_text;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = array_merge(['type' => 'comment'], $comment->load('post', 'commentor')->toArray());
        $this->prepareNotification();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target = $this->getOneSignalTarget($this->comment['post_owner_id']);

        $this->sendNotificationToSegment($this->notification_text, $this->comment, $target);
    }

    public function prepareNotification()
    {
        $transformer = new ActivityTransformer;
        $this->comment = $transformer->transform($this->comment);

        $this->notification_text = "{$this->comment['name']}({$this->comment['username']}) commented on your post.";
    }
}
