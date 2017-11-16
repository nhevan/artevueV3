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
     * returns an array of facebook uid of all common friends
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function fetchUserFriendsSocialIdsFromFacebook($access_token)
    {
        $client = new Client();
        
        try {
            $response = $client->get("https://graph.facebook.com/v2.10/me/friends?access_token={$access_token}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }
        $fb_response_object = json_decode($response->getBody());
        // dd($fb_response_object);
        return array_column($fb_response_object->data, 'id');
    }

    /**
     * returns an array of instagram uid of all followed users
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function fetchUserFriendsSocialIdsFromInstagram($access_token)
    {
        $client = new Client();
        
        try {
            $response = $client->get("https://api.instagram.com/v1/users/self/follows?access_token={$access_token}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }
        $ig_response_object = json_decode($response->getBody());
        $follows_ids = array_column($ig_response_object->data, 'id');

        try {
            $response = $client->get("https://api.instagram.com/v1/users/self/followed-by?access_token={$access_token}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }
        $ig_response_object = json_decode($response->getBody());
        $followed_by_ids = array_column($ig_response_object->data, 'id');

        $all_known_user_ids_from_instagram = array_unique(array_merge($followed_by_ids, $follows_ids));
        
        return $all_known_user_ids_from_instagram;
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