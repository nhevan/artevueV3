<?php

namespace App\Http\Controllers;

use App\User;
use App\Follower;
use App\Events\NewFollower;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use App\Jobs\SendNewFollowerNotification;
use Acme\Transformers\FollowerTransformer;
use Acme\Transformers\FollowingTransformer;
use Illuminate\Http\Response as IlluminateResponse;

class FollowersController extends ApiController
{
	use CounterSwissKnife, NotificationSwissKnife;

    protected $follower;
    protected $request;
    
    /**
     * Acme/Transformers/followerTransformer
     * @var followerTransformer
     */
    protected $followerTransformer;

    public function __construct(Follower $follower, FollowerTransformer $followerTransformer, Request $request)
    {
        $this->follower = $follower;
        $this->followerTransformer = $followerTransformer;
        $this->request = $request;
    }

    /**
     * Fetches all the followers of a given User
     * @param  [type]  $user_id [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getUserFollowers($user_id, Request $request)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        $followers = $this->getPaginatedFollowers($user->id, $request);        

        return $this->respondWithPagination($followers, $this->followerTransformer);
    }

    /**
     * Fetches all the followers of the current user
     * @param  [type]  $user_id [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getMyFollowers(Request $request)
    {
        $followers = $this->getPaginatedFollowers(Auth::user()->id, $request);        

        return $this->respondWithPagination($followers, $this->followerTransformer);
    }

    /**
     * returns paginated result of followers of a given user
     * @param  integer $user_id [description]
     * @param  Request $request [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getPaginatedFollowers($user_id, Request $request)
    {
        $limit = 10;
        if((int)$request->limit <= 30) $limit = (int)$request->limit ?: $limit;
        $followers = $this->follower->where('user_id', $user_id)->where('is_still_following', 1)->with('followerDetail')->paginate($limit);

        return $followers;
    }


	/**
     * swaps a follower status
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store($user_id, Request $request)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        if ($this->isExistingFollower($user_id)) {
        	return $this->removeFollower($user_id);
        }
        
        $this->trackAction(Auth::user(), "New Follower");

        return $this->startFollowing($user_id);
    }

    /**
     * checks if the current user is following the given User
     * @param  [type]  $user_id [description]
     * @return boolean          [description]
     */
    public function isExistingFollower($user_id)
    {
    	return $this->follower->where(['user_id' => $user_id, 'follower_id' => Auth::user()->id, 'is_still_following' => 1])->first();
    }

    /**
     * remove the given user from current users followers list
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function removeFollower($user_id)
    {
    	$follower = $this->follower->where(['user_id' => $user_id, 'follower_id' => Auth::user()->id])->first();
    	$follower->is_still_following = 0;
    	$follower->save();

        $this->trackAction(Auth::user(), "Remove Follower");

    	$this->decrementFollowingCount(Auth::user()->id);
    	$this->decrementFollowerCount($user_id);
    	return $this->respond(['message' => Auth::user()->name.' stopped following a user.']);
    }

    /**
     * add the given user to current user's followers list
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function startFollowing($user_id)
    {
    	if (Auth::user()->id == $user_id) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError(['message'=>'User can not follow him/herself']);
    	}
    	$was_once_follower = $this->follower->where(['user_id' => $user_id, 'follower_id' => Auth::user()->id])->first();
    	if ($was_once_follower) {
    		$was_once_follower->is_still_following = 1;
    		$was_once_follower->save();
    	}else{
    		$this->follower->create(['user_id' => $user_id, 'follower_id' => Auth::user()->id]);
    	}

    	$this->incrementFollowingCount(Auth::user()->id);
    	$this->incrementFollowerCount($user_id);

        dispatch(new SendNewFollowerNotification($user_id, Auth::user()->name));
    	return $this->respond(['message' => Auth::user()->name.' started following a user.']);
    }

    /**
     * fetches all the following users. these users can be used as taggable users
     * @return [type] [description]
     */
    public function getFollowingUsers(Request $request)
    {
        $followings = $request->user()->following->load('user')->toArray();
        return $this->respondAsTransformattedArray($followings, new FollowingTransformer);
    }

    /**
     * fetches all the following users of a given user
     * @return [type] [description]
     */
    public function getFollowingUsersByUser($user_id, Request $request)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }   
        $followings = $this->follower->where('follower_id', $user_id)->where('is_still_following', 1)->latest()->with('user')->paginate(20);
        return $this->respondWithPagination($followings, new FollowingTransformer);
    }
}
