<?php

namespace App\Traits;

use App\UserMetadata;
use App\MessageParticipant;

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

    /**
     * increments a users message_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function incrementMessageCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->message_count = $metadata->message_count + 1;
        return $metadata->save();
    }

    public function updateParticipantsTable($sender_id, $receiver_id, $last_message_id)
    {
        $participant_ids = [$sender_id, $receiver_id];
        $is_existing = MessageParticipant::whereIN('participant_one',$participant_ids)->whereIN('participant_two',$participant_ids)->first();
        if (!$is_existing) {
            MessageParticipant::create(['participant_one' => $sender_id, 'participant_two' => $receiver_id, 'last_message_id' => $last_message_id]);
            return 0;
        }

        $is_existing->last_message_id = $last_message_id;
        $is_existing->total_messages = $is_existing->total_messages + 1;

        $is_existing->save();
    }
}