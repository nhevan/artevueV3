<?php

namespace App\Traits;

use App\User;

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

        $data = $data->toArray();
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
        $gcm_registration_key = $receiver->gcm_registration_key;
        $params = [
            'registration_ids' => [$gcm_registration_key],
            'notification' => array('body'=>$message, 'title'=>$title,'sound'=>'default','content_available'=>1,'type'=>1),
            'priority' => "high"
        ];
        
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
}