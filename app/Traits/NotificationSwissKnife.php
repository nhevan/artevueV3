<?php

namespace App\Traits;

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
}