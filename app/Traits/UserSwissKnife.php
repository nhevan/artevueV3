<?php

namespace App\Traits;

use App\Follower;
use GuzzleHttp\Client;

trait UserSwissKnife{
	
	/**
	 * Removes a follower from the users follower list
	 * @param  [type]  $user_id [description]
     * @param  [type]  $follower_id [description]
	 * @return [type] [description]
	 */
	public function removeFromFollowerList($user_id, $follower_id)
	{
		if ($this->isExistingFollower($user_id, $follower_id)) {
        	$this->removeFollower($user_id, $follower_id);
        }
	}

	/**
     * checks if the given user is following the follower_id
     * @param  [type]  $user_id [description]
     * @param  [type]  $follower_id [description]
     * @return boolean          [description]
     */
    public function isExistingFollower($user_id, $follower_id)
    {
    	return Follower::where(['user_id' => $user_id, 'follower_id' => $follower_id, 'is_still_following' => 1])->first();
    }

    /**
     * remove the follower from the given user
     * @param  [type]  $user_id [description]
     * @param  [type]  $follower_id [description]
     * @return [type]          [description]
     */
    public function removeFollower($user_id, $follower_id)
    {
    	$follower = Follower::where(['user_id' => $user_id, 'follower_id' => $follower_id])->first();
    	$follower->is_still_following = 0;
    	$follower->save();
    }

    /**
     * fetch a users email address using facebook access token
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function fetchUserEmailFromFBAccessToken($access_token)
    {
        $client = new Client();
        
        try {
            $response = $client->get("https://graph.facebook.com/v2.10/me?fields=name,email&access_token={$access_token}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * fetch a users instagram id using instagram access token
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function fetchUserInstagramId($access_token)
    {
        $client = new Client();
        
        try {
            $response = $client->get("https://api.instagram.com/v1/users/self/?access_token={$access_token}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }
}