<?php

namespace App\Jobs;

use App\Like;
use Illuminate\Bus\Queueable;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewLikeNotification implements ShouldQueue
{
    protected $like;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Like $like)
    {
        //
        $this->like = $like;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->like->load('post', 'user');
        $post_owner_id = $this->like->post->owner_id;
        $liker_name = $this->like->user->name;
        
        $this->sendFcmMessage($post_owner_id, 'New Like', $liker_name.' liked your post.');
        $this->sendPusherNotification($post_owner_id.'-activity-channel', 'all-activities', [$post_owner_id, $liker_name]);
        $this->sendPusherNotificationToAllFollowersOfAUser($this->like->user->id);
    }
}
