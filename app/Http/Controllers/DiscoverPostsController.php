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
	/**
	 * contains the weight distribution values for likes, pin, chronology etc
	 * @var array
	 */
	protected $weights;

	/**
	 * contains all ids of the authenticated users followers (also includes the authenticated user id)
	 * @var array
	 */
	protected $me_and_my_follower_ids = [];

	/**
	 * does some bootstrap work for the controller
	 */
	public function __construct()
	{
		parent::__construct(new Request);
		
		$this->weights = $this->getPostWeightDistributionSettings();
	}

	/**
	 * fetches all keys that has a matching string of 'weight_distribution'
	 * @return array a array of key value pairs for related settings
	 */
	public static function getPostWeightDistributionSettings()
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
        	$this->me_and_my_follower_ids = [];
            $undiscovered_posts = $this->getPaginatedPosts($limit);

            return $this->respondWithPagination($undiscovered_posts, new PostTransformer);
        }
        $this->user = Auth::user();
        $this->me_and_my_follower_ids = $this->includeMyself($this->getMyFollowersIds());

		$undiscovered_posts = $this->getPaginatedPosts($limit);

        $this->trackAction(Auth::user(), "Explore Posts");

		return $this->respondWithPagination($undiscovered_posts, new PostTransformer);
    }

    /**
     * returns a paginated list of posts of given set of users
     * @param  [type] $user_ids [description]
     * @param  [type] $limit                            [description]
     * @return [type]                                   [description]
     */
    public function getPaginatedPosts($limit)
    {
        $undiscovered_posts = $this->undiscoveredPosts();

        $sorted_posts = $this->sortPostsByRelevancy($undiscovered_posts);
        $paginated_posts = $this->getPaginated($sorted_posts, $limit);
        
        return $paginated_posts;
    }

    /**
     * fetches a collection of posts that does not belong to any of the followers of the authenticated users
     * @param  [type] $user_ids [description]
     * @return [type]           [description]
     */
    private function undiscoveredPosts()
    {
        $posts = Post::select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))
            ->whereNotIn('owner_id', $this->me_and_my_follower_ids)
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
        
        $post['score'] += - ($hours_till_posted) * ( 1 - $weight['chronological_weight_distribution'] );
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

        return $difference;
    }
}
