<?php

namespace App\Http\Controllers;

use App\User;
use App\UserMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\DiscoverUserTransformer;

class DiscoverUsersController extends DiscoverController
{
	/**
     * Acme/Transformers/discoverUserTransformer
     * @var discoverUserTransformer
     */
    protected $discoverUserTransformer;

	public function __construct(DiscoverUserTransformer $discoverUserTransformer)
	{
		parent::__construct(new Request);
		$this->discoverUserTransformer = $discoverUserTransformer;
	}

    /**
     * returns new, highprofile, related and unfollowed users
     * @return [type] [description]
     */
    public function discoverUsers()
    {
    	$limit = 20;
        if ($this->userIsGuest()) {
            $all_users = User::all()->pluck('id');
            $undiscovered_users = $this->getPaginatedUsers($all_users, $limit);

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
}
