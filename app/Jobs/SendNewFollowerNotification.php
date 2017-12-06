<?php

namespace App\Jobs;

use App\User;
use App\Follower;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Acme\Transformers\ActivityTransformer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewFollowerNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    public $follow;
    public $notification_text;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Follower $follow)
    {
        $this->follow = array_merge(['type' => 'following'], $follow->load('followerDetail', 'user')->toArray());
        
        $this->prepareNotification();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target = $this->getOneSignalTarget($this->follow['user_id']);
        $this->sendNotificationToSegment($this->notification_text, $this->follow, $target);
    }

    public function prepareNotification()
    {
        $transformer = new ActivityTransformer;
        $this->follow = $transformer->transform($this->follow);
        
        $this->notification_text = "{$this->follow['follower_name']}({$this->follow['follower_username']}) started following you.";
    }
}
