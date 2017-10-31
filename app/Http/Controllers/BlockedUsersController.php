<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Post;
use App\User;
use App\BlockedUser;
use Illuminate\Http\Request;
use App\Traits\UserSwissKnife;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;

class BlockedUsersController extends ApiController
{
	use UserSwissKnife, CounterSwissKnife;

    protected $blockedUser;

    public function __construct(BlockedUser $blockedUser)
    {
        $this->blockedUser = $blockedUser;
    }

    /**
     * Swaps a user block status
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store($user_id, Request $request)
    {
    	$user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        if ($this->userIsBlocked($user_id)) {
        	return $this->unBlockUser($user_id);
        }

        $this->removeALlPinsOfBlockedUser($user_id);
        $this->removeBlockedUserFromFollowerList($user_id);


        return $this->blockUser($user);
    }

    /**
     * check if the given user is in current users block list
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function userIsBlocked($user_id)
    {
    	return $this->blockedUser->where(['user_id' => Auth::user()->id, 'blocked_user_id' => $user_id])->first();
    }

    /**
     * remove a user from current users block list
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function unBlockUser($user_id)
    {
    	$block = $this->blockedUser->where(['user_id' => Auth::user()->id, 'blocked_user_id' => $user_id])->first();
    	$block->delete();
    	
    	return $this->respond(['message' => Auth::user()->name.' unblocked a user.']);
    }

    /**
     * add a user to current users block list
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function blockUser(User $target)
    {
    	$this->blockedUser->create(['user_id' => Auth::user()->id, 'blocked_user_id' => $target->id]);

    	return $this->respond(['message' => Auth::user()->name.' blocked '.$target->name]);
    }

    /**
     * removes all pins {posts pinned by current user that was created by blocked user and Vice Versa}
     * @return [type] [description]
     */
    public function removeALlPinsOfBlockedUser($blocked_user_id)
    {
        $current_user_post_ids = $this->getUserPosts(Auth::user()->id);
        $this->removePinsOfPosts($current_user_post_ids, $blocked_user_id);

        $blocked_user_post_ids = $this->getUserPosts($blocked_user_id);
        $this->removePinsOfPosts($blocked_user_post_ids, Auth::user()->id);
    }

    /**
     * removes all pins of a user within a given set of post_ids
     * @param  array $post_ids contains an array of post ids
     * @param  integer $user_id  the user whose pins needs to be removed.
     * @return [type]           [description]
     */
    private function removePinsOfPosts($post_ids, $user_id)
    {
        $pins = Pin::whereIn('post_id', $post_ids)->where('user_id', $user_id)->get();
        foreach ($pins as $pin) {
            $this->decrementUserPinCount($user_id);
            $pin->delete();
        }
    }

    /**
     * get ids of all posts of a given user_id
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getUserPosts($user_id)
    {
        return Post::where('owner_id', $user_id)->pluck('id')->toArray();
    }

    /**
     * remove the users from each others followers list
     */
    public function removeBlockedUserFromFollowerList($user_id)
    {
    	$this->removeFromFollowerList(Auth::user()->id, $user_id);
    	$this->removeFromFollowerList($user_id, Auth::user()->id);
    }
}
