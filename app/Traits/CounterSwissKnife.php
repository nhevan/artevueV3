<?php

namespace App\Traits;

use App\Pin;
use App\Tag;
use App\Like;
use App\Post;
use App\Artist;
use App\Comment;
use App\Follower;
use App\UserMetadata;
use App\MessageParticipant;
use Illuminate\Support\Facades\Auth;

trait CounterSwissKnife{

    /**
     * increments a users tagged_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function incrementUserTaggedCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->tagged_count = $metadata->tagged_count + 1;
        return $metadata->save();
    }

    /**
     * decrement a users tagged_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function decrementUserTaggedCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata->tagged_count)
            $metadata->tagged_count = $metadata->tagged_count - 1;
        return $metadata->save();
    }

    /**
     * increments a users post_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function incrementUserPostCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->post_count = $metadata->post_count + 1;
        return $metadata->save();
    }

    /**
     * decrement a users post_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function decrementUserPostCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata && $metadata->post_count){
            $metadata->post_count = $metadata->post_count - 1;
            
            return $metadata->save();
        }
    }

    /**
     * increment a users pin_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function incrementUserPinCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->pin_count = $metadata->pin_count + 1;
        return $metadata->save();
    }

    /**
     * decrement a users pin_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function decrementUserPinCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata->pin_count)
            $metadata->pin_count = $metadata->pin_count - 1;
        return $metadata->save();
    }
    /**
     * increment a users like_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function incrementUserLikeCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->like_count = $metadata->like_count + 1;
        return $metadata->save();
    }

    /**
     * decrement a users like_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function decrementUserLikeCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata->like_count)
            $metadata->like_count = $metadata->like_count - 1;
        return $metadata->save();
    }

    /**
     * increment a users comment_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function incrementUserCommentCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        $metadata->comment_count = $metadata->comment_count + 1;
        return $metadata->save();
    }

    /**
     * decrement a users comment_count metadata value
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function decrementUserCommentCount($user_id)
    {
        $metadata = UserMetadata::where( [ 'user_id' => $user_id ] )->first();
        if($metadata && $metadata->comment_count){
            $metadata->comment_count = $metadata->comment_count - 1;
            
            return $metadata->save();
        }
    }

    /**
     * decreases pin count of all users who pinned the given post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementUserPinCountWhoPinnedThisPost($post_id)
    {
        $pins = Pin::where(['post_id'=>$post_id])->get();
        foreach ($pins as $pin) {
            $this->decrementUserPinCount($pin->user_id);
        }
    }

    /**
     * decrease like count for all users who liked a given post, as the post is being deleted
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementUserLikeCountWhoLikedThisPost($post_id)
    {
        $likes = Like::where(['post_id'=>$post_id])->get();
        foreach ($likes as $like) {
            $this->decrementUserLikeCount($like->user_id);
        }
    }

    /**
     * decrease comment count for all users who commented on the given post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementUserCommentCountWhoCommentedOnThisPost($post_id)
    {
        $comments = Comment::where(['post_id'=>$post_id])->get();
        foreach ($comments as $comment) {
            $this->decrementUserCommentCount($comment->user_id);
        }
    }

    /**
     * decrease tag count for all users that were tagged on this post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementUserTagCountWhoWereTaggedOnThisPost($post_id)
    {
        $tags = Tag::where(['post_id'=>$post_id])->get();
        foreach ($tags as $tag) {
            $this->decrementUserTaggedCount($tag->user_id);
        }
    }

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

    /**
     * updates pin_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function updatePinCountInFollowersTable($post_owner_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $post_owner_id)->first();
        if ($is_existing) {
            $is_existing->pin_count = $is_existing->pin_count + 1;
            return $is_existing->save();
        }
    }
    /**
     * updates like_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function updateLikeCountInFollowersTable($post_owner_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $post_owner_id)->first();
        if ($is_existing) {
            $is_existing->like_count = $is_existing->like_count + 1;
            return $is_existing->save();
        }
    }

    /**
     * decrement like_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function decrementLikeCountInFollowersTable($post_owner_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $post_owner_id)->first();
        if ($is_existing && $is_existing->like_count >= 1) {
            $is_existing->like_count = $is_existing->like_count - 1;
            return $is_existing->save();
        }
    }

    /**
     * decreases the post_count of an artist
     * @param  [type] $artist_id [description]
     * @return [type]            [description]
     */
    public function decreasePreviousArtistPostCount($artist_id)
    {
        $artist = Artist::find($artist_id);
        if ($artist) {
            $artist->post_count = $artist->post_count - 1;
            return $artist->save();
        }
    }

    /**
     * increment comment_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function incrementCommentCountInFollowersTable($post_owner_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $post_owner_id)->first();
        if ($is_existing) {
            $is_existing->comment_count = $is_existing->comment_count + 1;
            return $is_existing->save();
        }
    }

    /**
     * decrement comment_count in followers table IF follower exist else do nothing
     * @return [type] [description]
     */
    public function deccrementCommentCountInFollowersTable($post_owner_id)
    {
        $is_existing = Follower::where('follower_id',Auth::user()->id)->where('user_id', $post_owner_id)->first();
        if ($is_existing) {
            $is_existing->comment_count = $is_existing->comment_count - 1;
            return $is_existing->save();
        }
    }

    /**
     * increments posts pin count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function incrementPostPinCount($post_id)
    {
        $post = Post::find($post_id);
        $post->pin_count = $post->pin_count + 1;
        return $post->save();
    }

    /**
     * decrement posts pin count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementPostPinCount($post_id)
    {
        $post = Post::find($post_id);
        if($post->pin_count)
            $post->pin_count = $post->pin_count - 1;
        return $post->save();
    }
    /**
     * increments posts like count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function incrementPostLikeCount($post_id)
    {
        $post = Post::find($post_id);
        $post->like_count = $post->like_count + 1;
        return $post->save();
    }

    /**
     * decrement posts like count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementPostLikeCount($post_id)
    {
        $post = Post::find($post_id);
        if($post->like_count)
            $post->like_count = $post->like_count - 1;
        return $post->save();
    }

    /**
     * increments posts comment count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function incrementPostCommentCount($post_id)
    {
        $post = Post::find($post_id);
        $post->comment_count = $post->comment_count + 1;
        return $post->save();
    }

    /**
     * decrement posts comment count
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function decrementPostCommentCount($post_id)
    {
        $post = Post::find($post_id);
        if($post->comment_count)
            $post->comment_count = $post->comment_count - 1;
        return $post->save();
    }
}