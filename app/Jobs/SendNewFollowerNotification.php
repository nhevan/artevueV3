<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewFollowerNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationSwissKnife;

    protected $user_id;
    protected $follower_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $follower_name)
    {
        // $user = User::find($user_id);
        $this->user_id = $user_id;
        $this->follower_name = $follower_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);

        $this->sendFcmMessage($user, 'New Follower', $this->follower_name.' started following you.');
    }
}
