<?php

namespace App\Traits;

use App\Follower;
use App\UserMetadata;
use App\MessageParticipant;
use Illuminate\Support\Facades\Auth;

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
    public function incrementMessageCount($user_id, $count = 1)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        
        $metadata->message_count = $metadata->message_count + $count;
        
        return $metadata->save();
    }

    /**
     * decrement a users message_count metadata value
     * @param  [type]  $user_id [description]
     * @return [type]          [description]
     */
    public function decrementMessageCount($user_id, $count = 1)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        
        $metadata->message_count = $metadata->message_count - $count;
        
        return $metadata->save();
    }

    /**
     * updates total message_count in Message Participants table
     * @param  [type] $sender_id       [description]
     * @param  [type] $receiver_id     [description]
     * @param  [type] $last_message_id [description]
     * @return [type]                  [description]
     */
    public function updateTotalMessageCountInParticipantsTable($sender_id, $receiver_id, $last_message_id)
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

    /**
     * updates message_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function updateMessageCountInFollowersTable($follower_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $follower_id)->first();
        if ($is_existing) {
            $is_existing->message_count = $is_existing->message_count + 1;
            return $is_existing->save();
        }
    }
}