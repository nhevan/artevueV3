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
            $undiscovered_users = $this->getPaginatedUsers($this->getUndiscoveredUsers(), $limit);

            return $this->respondWithPagination($undiscovered_users, $this->discoverUserTransformer);
        }

        $this->user = Auth::user();
		$paginated_users = $this->getPaginatedUsers($this->getUndiscoveredUsers(), $limit);

        $this->trackAction(Auth::user(), "Explore Users");

		return $this->respondWithPagination($paginated_users, $this->discoverUserTransformer);
    }

    /**
     * [getPaginatedUsers of users]
     * @param  [type] $user_ids [description]
     * @param  [type] $limit                            [description]
     * @return [type]                                   [description]
     */
    public function getPaginatedUsers($user_ids, $limit)
    {
    	return UserMetadata::select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`+`message_count`+`follower_count`+`following_count`+`post_count`+`tagged_count`) as total_count"))
    		->whereIn('user_id', $user_ids)
    		->orderBy('total_count', 'DESC')
    		->with('user')
    		->paginate($limit);
    }

    /**
     * returns an array of ids of undiscovered users
     * @return array array containing ids
     */
    public function getUndiscoveredUsers()
    {
        $my_followers = $this->includeMyself( $this->getMyFollowersIds() );
        $users = User::whereNotIn('id', $my_followers)->pluck('id')->toArray();
        
        return $users;
    }
}
