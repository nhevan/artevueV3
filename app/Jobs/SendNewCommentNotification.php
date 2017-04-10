<?php

namespace App\Jobs;

use App\User;
use App\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewCommentNotification implements ShouldQueue
{
    protected $comment;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment->load('post', 'commentor');
        $post_owner_id = $this->comment->post->owner_id;
        $commentor_name = $this->comment->commentor->name;
        $owner = User::find($post_owner_id);

        // if(Auth::user()->id != $post_owner_id)
        $this->sendFcmMessage($owner, 'New Comment', $commentor_name.' commented on your post.');
        
        $this->sendPusherNotification($post_owner_id.'-activity-channel', 'all-activities', [$post_owner_id, $commentor_name]);
        $this->sendPusherNotificationToAllFollowersOfAUser($this->comment->user_id);
    }
}
