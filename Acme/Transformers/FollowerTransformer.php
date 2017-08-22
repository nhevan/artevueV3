<?php

namespace Acme\Transformers;

use App\Follower;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class FollowerTransformer extends Transformer
{
    public function transform($follower)
    {
    	$is_following = $this->getIsFollowing($follower);
    	
        return [
                'user_id' => $follower['follower_id'],
                'name' => $follower['follower_detail']['name'],
                'username' => $follower['follower_detail']['username'],
                'profile_picture' => $follower['follower_detail']['profile_picture'],

                'pin_count' => $follower['pin_count'],
                'like_count' => $follower['like_count'],
                'comment_count' => $follower['comment_count'],
                'message_count' => $follower['message_count'],

                'is_following' => $is_following,
            ];
    }

    /**
     * returns true if the api requesting user is following the follower
     * @param  [type] $follower [description]
     * @return [type]           [description]
     */
    public function getIsFollowing($follower)
    {
        if (Auth::check()) {
            return !! Follower::where('user_id', $follower['follower_id'])->where('follower_id', Auth::user()->id)->where('is_still_following', 1)->first();
        }
    	
        return false;
	}
}