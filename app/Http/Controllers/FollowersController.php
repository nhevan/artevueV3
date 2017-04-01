<?php

namespace App\Http\Controllers;

use App\User;
use App\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\FollowerTransformer;

class FollowersController extends ApiController
{
    protected $follower;
    
    /**
     * Acme/Transformers/followerTransformer
     * @var followerTransformer
     */
    protected $followerTransformer;

    public function __construct(Follower $follower, FollowerTransformer $followerTransformer)
    {
        $this->follower = $follower;
        $this->followerTransformer = $followerTransformer;
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
        $followers = $this->follower->where('user_id', $user_id)->with('followerDetail')->paginate($limit);

        return $followers;
    }
}
