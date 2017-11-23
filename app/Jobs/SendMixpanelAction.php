<?php

namespace App\Jobs;

use App\User;
use App\MixpanelActions;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMixpanelAction implements ShouldQueue
{
    protected $user;
    protected $action;
    protected $properties;
    protected $ip;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $action, array $properties = [], $ip = 0)
    {

        $this->user = $user;
        $this->action = $action;
        $this->properties = $properties;
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendMixpanelAction();
        MixpanelActions::create([
            'user_id' => $this->user->id,
            'action' => $this->action,
            'parameters' => json_encode($this->properties),
            'ip' => $this->ip
        ]);
    }


    /**
     * sends a action to mixpanel
     * @param  User    $user   [description]
     * @param  string  $action actions like "New Like", "PDF Generate Request" etc.
     * @param  array  $properties additional properties like profile_id, age, gender to pass along with the event
     * @param  integer $ip     [description]
     */
    public function sendMixpanelAction()
    {
        $token = config('app.mixpanel_project_token');

        $mp = \Mixpanel::getInstance($token);
        $mp->people->set($this->user->id, array(
            '$name'       => $this->user->name,
            '$email'      => $this->user->email,
            '$username'   => $this->user->username,
        ), $this->ip, $ignore_time = true);

        $mp->identify($this->user->id);

        $mp->track($this->action, $this->properties);
    }
}
