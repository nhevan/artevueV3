<?php

namespace App\Http\Controllers;

use App\User;
use App\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends ApiController
{
    protected $user;
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * returns a collection of users that my followers are interested in
     * @return [type] [description]
     */
    public function getFollowersFollowingUsers()
    {
    	$users_my_followers_are_following = $this->getIdsOfUsersMyFollowersAreFollowing();

    	return $users_my_followers_are_following;
    }

    public function getNotConectedFollowers()
    {
        $my_other_known_users = $this->getIdsOfUsersMyFollowersAreFollowing();
        
        $my_followers = $this->getMyFollowersIds();

        $all_known_users = $my_followers->merge($my_other_known_users)->all();

        $users = User::whereNotIn('id', $all_known_users)->where('id', '<>', Auth::user()->id)->pluck('id')->toArray();
        
        return $users;
    }

    /**
     * get a collection of ids of users that my followers are following
     * @return [type] [description]
     */
    public function getIdsOfUsersMyFollowersAreFollowing()
    {
    	$my_followers_ids = $this->getMyFollowersIds();

    	$users_my_followers_are_following = Follower::whereIn('follower_id', $my_followers_ids)->whereNotIn('user_id', $my_followers_ids)->get()->pluck('user_id');

		$users_my_followers_are_following = $this->excludeMyself($users_my_followers_are_following);    	

    	return $users_my_followers_are_following;
    }

    /**
     * get a collection of ids of all my followers
     * @return object eloquent collection object
     */
    public function getMyFollowersIds()
    {
    	return  $this->user->following->pluck('user_id');
    }

    /**
     * exclude myself from a colleciton of users ids
     * @param  [type] $users_collection [description]
     * @return [type]        [description]
     */
    public function excludeMyself($users_collection)
    {
    	$users_collection = $users_collection->reject(function ($id) {
		    return $id == $this->user->id;
		});

		return $users_collection->all();
    }

    /**
     * include myself from a colleciton of users ids
     * @param  [type] $users_collection [description]
     * @return [type]        [description]
     */
    public function includeMyself($users_collection)
    {
        $users_collection = $users_collection->push($this->user->id);

        return $users_collection->all();
    }
}
