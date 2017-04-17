<?php

namespace App\Traits;

use App\User;
use App\Follower;
use Illuminate\Support\Facades\Auth;

trait NotificationSwissKnife{

    /**
     * sends a pusher notification
     * @param  [type] $channel         [description]
     * @param  [type] $event           [description]
     * @param  [type] $data            [description]
     * @param  array  $additional_data [description]
     * @return [type]                  [description]
     */
    public function sendPusherNotification($channel, $event, $data, $additional_data = [])
    {
        $app_id = config('broadcasting.connections.pusher.app_id');
        $app_key = config('broadcasting.connections.pusher.key');
        $app_secret = config('broadcasting.connections.pusher.secret');
        $options = config('broadcasting.connections.pusher.options');

        $pusher = new \Pusher( $app_key, $app_secret, $app_id, $options );

        if (!is_array($data)) {
            $data = $data->toArray();
        }
        $data = array_merge($data, $additional_data);

        $pusher->trigger($channel, $event, $data);
    }

    /**
     * sends a FCM Message
     * @param  User   $receiver [description]
     * @param  [type] $title    [description]
     * @param  [type] $message  [description]
     * @return GuzzleHttp\Psr7\Response           [description]
     */
    public function sendFcmMessage(User $receiver, $title, $message)
    {

        $receiver->load('metadata');
        $gcm_registration_key = $receiver->gcm_registration_key;
        $params = [
            'registration_ids' => [$gcm_registration_key],
            'notification' => array('body'=>$message, 'title'=>$title,'sound'=>'default','content_available'=>1,'type'=>1),
            'priority' => "high"
        ];
        if($receiver->metadata->is_notification_enabled)
            $response = $this->postToFCM($params);
    }

    /**
     * makes a POST call to FCM server using guzzle
     * @param  [type] $params [description]
     * @return GuzzleHttp\Psr7\Response         [description]
     */
    public function postToFCM($params)
    {
        $serverKey = config('app.fcm_server_key');
        $client = new \GuzzleHttp\client();
        $response = $client->request('POST','https://fcm.googleapis.com/fcm/send', 
            [
                'headers' => [
                    'Authorization' => 'key='.$serverKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => $params
            ]
        );
        return $response;
    }

    /**
     * sends activity push notification to all followers of a given user
     * @param  [type] $user_id [description]
     * @param  array  $data    [description]
     * @return [type]          [description]
     */
    public function sendPusherNotificationToAllFollowersOfAUser($user_id, $data = [])
    {
        $event = 'all-activities';

        $follower_ids = Follower::where('user_id', $user_id)->pluck('follower_id')->toArray();

        foreach ($follower_ids as $follower_id) {
            $channel = $follower_id.'-activity-channel';
            $this->sendPusherNotification($channel, $event, $data);
        }
    }

    /**
     * sends a action to mixpanel
     * @param  User    $user   [description]
     * @param  string  $action actions like "New Like", "PDF Generate Request" etc.
     * @param  array  $properties additional properties like profile_id, age, gender to pass along with the event
     * @param  integer $ip     [description]
     */
    public function sendMixpanelAction(User $user, $action, array $properties = [], $ip = 0)
    {
        $token = config('app.mixpanel_project_token');

        $mp = \Mixpanel::getInstance($token);
        $mp->people->set($user->id, array(
            '$name'       => $user->name,
            '$email'      => $user->email,
            '$username'   => $user->username,
        ), $ip, $ignore_time = true);

        $mp->identify($user->id);

        $mp->track($action, $properties);
    }
}