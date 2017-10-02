<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Follower;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\PostTransformer;

class DiscoverPostsController extends DiscoverController
{
	protected $weights;

	public function __construct()
	{
		parent::__construct(new Request);
		
		$this->weights = $this->getWeightDistributionSettings();
	}

	/**
	 * fetches all keys that has a matching string of 'weight_distribution'
	 * @return array a array of key value pairs for related settings
	 */
	public static function getWeightDistributionSettings()
	{
		return Settings::where('key', 'like', '%weight_distribution%')->get()->pluck('value', 'key')->toArray();
	}


    /**
     * returns a undiscovered collection of posts
     * @return [type] [description]
     */
    public function discoverPosts()
    {
    	$limit = 20;
        if ($this->userIsGuest()) {
        	$users = User::all()->pluck('id');
            $undiscovered_posts = $this->getPaginatedPosts($users, $limit);

            return $this->respondWithPagination($undiscovered_posts, new PostTransformer);
        }
        $this->user = Auth::user();

		$users_my_followers_are_following = $this->getFollowersFollowingUsers();
        $followers_not_connected_to_me = $this->getNotConectedFollowers();

        $merged_users = array_merge($users_my_followers_are_following, $followers_not_connected_to_me);
        
		$undiscovered_posts = $this->getPaginatedPosts($merged_users, $limit);

        $this->trackAction(Auth::user(), "Explore Posts");

		return $this->respondWithPagination($undiscovered_posts, new PostTransformer);
    }

    /**
     * returns a paginated list of posts of given set of users
     * @param  [type] $user_ids [description]
     * @param  [type] $limit                            [description]
     * @return [type]                                   [description]
     */
    public function getPaginatedPosts($user_ids, $limit)
    {
        $posts_of_given_users = $this->usersPosts($user_ids);

        $sorted_posts = $this->sortPostsByRelevancy($posts_of_given_users);
        
        $paginated_posts = $this->getPaginated($sorted_posts, $limit);
        
        return $paginated_posts;
    }

    /**
     * fetches posts of a given set of users
     * @param  [type] $user_ids [description]
     * @return [type]           [description]
     */
    private function usersPosts($user_ids)
    {
        $posts = Post::select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))
            ->whereIn('owner_id', $user_ids)
            ->where('is_undiscoverable', false)
            ->with('owner', 'tags', 'artist')
            ->get();

        $posts->map(function ($post){
            return $this->assignPostRelevancy($post);
        });

        return $posts;
    }

    /**
     * sorts a collection of post by its score
     * @param  [type] $posts [description]
     * @return [type]        [description]
     */
    private function sortPostsByRelevancy($posts)
    {
        return $posts->sortByDesc('score')->values()->all();
    }

    /**
     * calculates the relevancy of a post
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    private function assignPostRelevancy($post)
    {
    	// dd()
        $this->assignChronologyRelevancy($post, $this->weights);
        $this->assignLikeRelevancy($post, $this->weights);
        $this->assignPinRelevancy($post, $this->weights);

        return $post;
    }

    /**
     * assigns chronology relevancy to a post
     * @param  [type] &$post  [description]
     * @param  [type] $weight [description]
     * @return [type]         [description]
     */
    private function assignChronologyRelevancy(&$post, $weight)
    {
        $hours_till_posted = $this->getHoursTillPosted($post['created_at']);
        
        $post['score'] += - ($hours_till_posted) * $weight['chronological_weight_distribution'];
    }

    /**
     * assigns like relevancy to a post
     * @param  [type] &$post  [description]
     * @param  [type] $weight [description]
     * @return [type]         [description]
     */
    private function assignLikeRelevancy(&$post, $weight)
    {
        $post['score'] += $post['like_count'] * $weight['like_weight_distribution'];
    }

    /**
     * assingns Pin Relevancy to a post
     * @param  [type] &$post [description]
     * @return [type]        [description]
     */
    private function assignPinRelevancy(&$post, $weight)
    {
        $post['score'] += $post['pin_count'] * $weight['pin_weight_distribution'];
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

        // return $difference;
        if ($difference) {
            return $difference;
        }
        return 1;
    }
}
