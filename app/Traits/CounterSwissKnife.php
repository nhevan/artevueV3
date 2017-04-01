<?php

namespace App\Traits;

use App\UserMetadata;

trait CounterSwissKnife{

    /**
     * increments a users following_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function incrementFollowingCount($user_id)
    {
    	$metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
    	$metadata->following_count = $metadata->following_count + 1;
    	return $metadata->save();
    }

    /**
     * decrements a users following_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function decrementFollowingCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata->following_count)
            $metadata->following_count = $metadata->following_count - 1;
        return $metadata->save();
    }

    /**
     * increments a users follower_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function incrementFollowerCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->follower_count = $metadata->follower_count + 1;
        return $metadata->save();
    }

    /**
     * decrements a users follower_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function decrementFollowerCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata->follower_count)
            $metadata->follower_count = $metadata->follower_count - 1;
        return $metadata->save();
    }
}