<?php

namespace App\Http\Controllers;

use App\User;
use App\BlockedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedUsersController extends ApiController
{
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

        $this->removeBlockedUserFromFollowerList($user_id); //unimplemented
        $this->removeALlPinsOfBlockedUser($user_id); //unimplemented

        return $this->blockUser($user_id);
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
    public function blockUser($user_id)
    {
    	$this->blockedUser->create(['user_id' => Auth::user()->id, 'blocked_user_id' => $user_id]);

    	return $this->respond(['message' => Auth::user()->name.' blocked a user.']);
    }

    /**
     * removes all pins {posts pinned by current user that was created by blocked user and Vice Versa}
     * @return [type] [description]
     */
    public function removeALlPinsOfBlockedUser($user_id)
    {
    	return true;
    }

    /**
     * remove the users from each others followers list
     * @return [type] [description]
     */
    public function removeBlockedUserFromFollowerList($user_id)
    {
    	return true;
    }
}
