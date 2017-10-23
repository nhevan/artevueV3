<?php

namespace App\Traits;

use App\User;
use App\Message;
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
    public function sendFcmMessage(User $receiver, $title, $message, $priority = "high")
    {

        $receiver->load('metadata');
        $gcm_registration_key = $receiver->gcm_registration_key;
        $params = [
            'registration_ids' => [$gcm_registration_key],
            'notification' => array('body'=>$message, 'title'=>$title,'sound'=>'default','content_available'=>1,'type'=>1),
            'priority' => $priority
        ];
        if($receiver->metadata && $receiver->metadata->is_notification_enabled)
            $response = $this->postToFCM($params);
    }

    /**
     * sends a notification to a segment via OneSignal
     * @param  [type] $content [description]
     * @param  string $segment [description]
     * @return [type]          [description]
     */
    public function sendNotificationToSegment( $en_notification_text, $data = [], $target_segment = 'All')
    {
        $app_id = config('broadcasting.connections.onesignal.app_id');
        $api_key = config('broadcasting.connections.onesignal.rest_api_key');

        $content = array(
            "en" => $en_notification_text
            );
        
        $fields = array(
            'app_id' => $app_id,
            'filters' => [
                [
                    "field" => "tag",
                    "key" => "channel",
                    "value" => $target_segment
                ]
            ],
            'data' => $data,
            'contents' => $content
        );
        
        $fields = json_encode($fields);
        // print("\nJSON ready to be sent:\n");
        // print($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   "Authorization: Basic {$api_key}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    /**
     * sends a new message notification via OneSignal
     * @param  Message $message [description]
     * @return [type]           [description]
     */
    public function sendNewMessageNotification(Message $message)
    {
        $target = "User-{$message->receiver->id}";
        $data = ['type' => 'message', 'sender' => $message->sender->username, 'user_id' => $message->sender->id, 'is_file' => $message->is_file, 'is_post' => $message->is_post, 'url' => $message->url ];

        $this->sendNotificationToSegment($message->message, $data, $target);

        $content = [
            "en" => $message->message
        ];

        $this->sendPusherNotification($target, 'New Message', [ 'contents' => $content ], [ 'data' => $data ]);
    }

    /**
     * sends generic notification to all or any specific user
     * @param  [type] $notification_text [description]
     * @param  [type] $user_id           [description]
     * @return [type]                    [description]
     */
    public function sendGenericNotification($notification_text, $user_id = null)
    {
        $target = "User-{$user_id}";
        $data = ['type' => 'general-notification'];

        if ($user_id) {
            $this->sendNotificationToSegment($notification_text, $data, [ $target ]);
        }else{
            $this->sendNotificationToSegment($notification_text, $data);
        }

        $content = [
            "en" => $notification_text
        ];
        $this->sendPusherNotification($target, 'General Notification', [ 'contents' => $content ], [ 'data' => $data ]);
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
}