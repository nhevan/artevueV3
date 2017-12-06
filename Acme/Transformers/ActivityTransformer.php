<?php

namespace Acme\Transformers;

use App\Follower;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class ActivityTransformer extends Transformer
{
    public function transform($activity)
    {
        if($activity['type'] == 'like') return $this->likeType($activity);
        if($activity['type'] == 'comment') return $this->commentType($activity);
        if($activity['type'] == 'pin') return $this->pinType($activity);
        if($activity['type'] == 'following') return $this->followingType($activity);
    }

    /**
     * formats a like type activity
     * @param  [type]  $activity [description]
     * @return boolean           [description]
     */
    public function likeType($activity)
    {
        $is_following = $this->isFollowing($activity['user_id']);
        $like = [
                'type' => $activity['type'],
                'user_id' => $activity['user_id'],
                'username' => $activity['user']['username'],
                'name' => $activity['user']['name'],
                'profile_picture' => $activity['user']['profile_picture'],
                'post_id' => $activity['post']['id'],
                'post_owner_id' => $activity['post']['owner_id'],
                'image' => $activity['post']['image'],
                'is_following' => $is_following,
                'created_at' => $activity['created_at'],
            ];
        return $like;
    }

    /**
     * formats a comment type activity
     * @param  [type]  $activity [description]
     * @return boolean           [description]
     */
    public function commentType($activity)
    {
        $is_following = $this->isFollowing($activity['user_id']);

        $comment = [
                'type' => $activity['type'],
                'comment' => $activity['comment'],
                'user_id' => $activity['user_id'],
                'username' => $activity['commentor']['username'],
                'name' => $activity['commentor']['name'],
                'profile_picture' => $activity['commentor']['profile_picture'],
                'post_id' => $activity['post']['id'],
                'post_owner_id' => $activity['post']['owner_id'],
                'image' => $activity['post']['image'],
                'is_following' => $is_following,
                'created_at' => $activity['created_at'],
            ];
        return $comment;
    }

    /**
     * formats a pin type activity
     * @param  [type]  $activity [description]
     * @return boolean           [description]
     */
    public function pinType($activity)
    {
        $is_following = $this->isFollowing($activity['user_id']);

        $pin = [
                'type' => $activity['type'],
                'user_id' => $activity['user_id'],
                'username' => $activity['user']['username'],
                'profile_picture' => $activity['user']['profile_picture'],
                'post_id' => $activity['post']['id'],
                'image' => $activity['post']['image'],
                'is_following' => $is_following,
                'created_at' => $activity['created_at'],
            ];
        return $pin;
    }

    /**
     * formats a following type activity
     * @param  [type] $activity [description]
     * @return [type]           [description]
     */
    public function followingType($activity)
    {
        if($this->isFollowerActivty($activity['user_id'])){
            $is_following = $this->isFollowing($activity['user_id']);
        }else{
            $is_following = $this->isFollowing($activity['follower_id']);
        }

        $following = [
                'type' => $activity['type'],
                'follower_id' => $activity['follower_id'],
                'follower_username' => $activity['follower_detail']['username'],
                'follower_name' => $activity['follower_detail']['name'],
                'user_id' => $activity['user_id'],
                'username' => $activity['user']['username'],
                'profile_picture' => $activity['follower_detail']['profile_picture'],
                'is_following' => $is_following,
                'created_at' => $activity['created_at'],
            ];
        return $following;
    }

    /**
     * checks of we are transforming a follower type event for the user or for follower activities
     * @param  [type]  $user_id [description]
     * @return boolean          [description]
     */
    public function isFollowerActivty($user_id)
    {
        return $user_id != Auth::user()->id;
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