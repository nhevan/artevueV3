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
    	$this->user = Auth::user();
    	$limit = 20;

		$users_my_followers_are_following = $this->getFollowersFollowingUsers();
        $followers_not_connected_to_me = $this->getNotConectedFollowers();
        $merged_users = array_merge($users_my_followers_are_following, $followers_not_connected_to_me);
        // var_dump($merged);
        // exit();
		$undiscovered_users = $this->getPaginatedUsers($merged_users, $limit);

		return $this->respondWithPagination($undiscovered_users, $this->discoverUserTransformer);
    }

    /**
     * returns a undiscover collection of posts
     * @return [type] [description]
     */
    public function discoverPosts()
    {
    	$this->user = Auth::user();
    	$limit = 20;

		$users_my_followers_are_following = $this->getFollowersFollowingUsers();
        $followers_not_connected_to_me = $this->getNotConectedFollowers();

        $merged_users = array_merge($users_my_followers_are_following, $followers_not_connected_to_me);
        
		$undiscovered_posts = $this->getPaginatedPosts($merged_users, $limit);

		return $this->respondWithPagination($undiscovered_posts, new PostTransformer);
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
     * returns a paginated list of posts of given set of users
     * @param  [type] $user_ids [description]
     * @param  [type] $limit                            [description]
     * @return [type]                                   [description]
     */
    public function getPaginatedPosts($user_ids, $limit)
    {
        $weight = [
            'follower_like_count' => .25,
            'chronological' => .25,
            'like_count' => .40,
            'pin_count' => .10
        ];

    	$posts = Post::select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))
            ->whereIn('owner_id', $user_ids)
            // ->latest()
            ->with('owner', 'tags', 'artist')
            ->get();

        $posts->map(function ($post) use($weight) {
            $hours_till_posted = $this->getHoursTillPosted($post['created_at']);
            $post['score'] = ( 1/$hours_till_posted ) * $weight['chronological'];
            $post['score'] += $post['like_count'] * $weight['like_count'];
            $post['score'] += $post['pin_count'] * $weight['pin_count'];
            // $post['score'] = $post['total_count'] / $hours_till_posted;
            return $post;
        });

        $posts = $posts->sortByDesc('score')->values()->all();
        
        $paginated_result = $this->getPaginated($posts, $limit);
        
        return $paginated_result;
    }

    /**
     * fetch the number hours from till and when the post was actually created
     * @param  [type] $created_at [description]
     * @return [type]             [description]
     */
    public function getHoursTillPosted($created_at)
    {
        $now = Carbon::now();
        $posted_at = Carbon::createFromFormat('Y-m-d H:i:s', $created_at);

        $difference = $posted_at->diffInHours($now);
        if ($difference) {
            return $difference;
        }
        return 1;
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
