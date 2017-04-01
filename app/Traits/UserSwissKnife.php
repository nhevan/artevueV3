<?php

namespace App\Traits;

use App\Follower;

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
}