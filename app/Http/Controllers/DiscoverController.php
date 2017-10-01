<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Follower;
use Carbon\Carbon;
use App\UserMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\PostTransformer;
use Acme\Transformers\DiscoverUserTransformer;

class DiscoverController extends ApiController
{
    protected $user;
    protected $request;
    
    /**
     * Acme/Transformers/discoverUserTransformer
     * @var discoverUserTransformer
     */
    protected $discoverUserTransformer;

    public function __construct(DiscoverUserTransformer $discoverUserTransformer, Request $request)
    {
    	$this->discoverUserTransformer = $discoverUserTransformer;
        $this->request = $request;
    }

    /**
     * returns new, highprofile, related and unfollowed users
     * @return [type] [description]
     */
    public function discoverUsers()
    {
    	$limit = 20;
        if ($this->userIsGuest()) {
            $my_followers_ids = $this->getAutoFollowersArray();
            $users_my_followers_are_following = Follower::whereIn('follower_id', $my_followers_ids)->whereNotIn('user_id', $my_followers_ids)->get()->pluck('user_id');

            $undiscovered_users = $this->getPaginatedUsers($users_my_followers_are_following, $limit);

            return $this->respondWithPagination($undiscovered_users, $this->discoverUserTransformer);
        }

        $this->user = Auth::user();

		$users_my_followers_are_following = $this->getFollowersFollowingUsers();
        $followers_not_connected_to_me = $this->getNotConectedFollowers();
        $merged_users = array_merge($users_my_followers_are_following, $followers_not_connected_to_me);

		$undiscovered_users = $this->getPaginatedUsers($merged_users, $limit);

        $this->trackAction(Auth::user(), "Explore Users");

		return $this->respondWithPagination($undiscovered_users, $this->discoverUserTransformer);
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
        // var_dump($my_followers);
        // exit();

        $all_known_users = $my_followers->merge($my_other_known_users)->all();

        $users = User::whereNotIn('id', $all_known_users)->where('id', '<>', Auth::user()->id)->pluck('id')->toArray();
        
        return $users;
    }

    /**
     * [getPaginatedUsers of users]
     * @param  [type] $user_ids [description]
     * @param  [type] $limit                            [description]
     * @return [type]                                   [description]
     */
    public function getPaginatedUsers($user_ids, $limit)
    {
    	return UserMetadata::select(
    		DB::raw("*, (`like_count`+`pin_count`+`comment_count`+`message_count`+`follower_count`+`following_count`+`post_count`+`tagged_count`) as total_count"))
    		->whereIn('user_id', $user_ids)
    		->orderBy('total_count', 'DESC')
    		->with('user')
    		->paginate($limit);
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
     * @return [type] [description]
     */
    public function getMyFollowersIds()
    {
    	return $this->user->following->pluck('user_id');
    }

    /**
     * exclude myself from a colleciton of users ids
     * @param  [type] $users [description]
     * @return [type]        [description]
     */
    public function excludeMyself($users)
    {
    	$users = $users->reject(function ($id) {
		    return $id == $this->user->id;
		});

		return $users->all();
    }
}
