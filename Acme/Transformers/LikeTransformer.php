<?php

namespace Acme\Transformers;

use App\Follower;
use Illuminate\Support\Facades\Auth;


/**
*
*/
class LikeTransformer extends Transformer
{
    public function transform($like)
    {
        $is_following = $this->isFollowing($like['user_id']);

    	$like = [
                'id' => $like['id'],
                'user_id' => $like['user_id'],
                'username' => $like['user']['username'],
                'profile_picture' => $like['user']['profile_picture'],
                'is_following' => $is_following,
            ];
        return $like;
    }

    /**
     * checks whether the authenticated user is following given user
     * @param  [type]  $user_id [description]
     * @return boolean          [description]
     */
    public function isFollowing($user_id)
    {
        $is_following = Follower::where(['user_id' => $user_id, 'follower_id' => Auth::user()->id, 'is_still_following' => 1])->first();
        if ($is_following) {
            return 1;
        }
        return 0;
    }
}