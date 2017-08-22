<?php

namespace Acme\Transformers;

use App\Follower;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class FollowingTransformer extends Transformer
{
    public function transform($following)
    {
        $is_following = $this->isFollowing($following['user_id']);
    	$data = [
                'id' => $following['id'],
                'user_id' => $following['user_id'],
                'username' => $following['user']['username'],
                'profile_picture' => $following['user']['profile_picture'],
                'is_following' => $is_following
            ];
        return $data;
    }

    /**
     * checks whether the authenticated user is following given user
     * @param  [type]  $user_id [description]
     * @return boolean          [description]
     */
    public function isFollowing($user_id)
    {
        if (Auth::check()) {
            return !! Follower::where(['user_id' => $user_id, 'follower_id' => Auth::user()->id, 'is_still_following' => 1])->first();
        }

        return false;
    }
}