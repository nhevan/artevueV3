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
	 * more likes means adding more score to a post
	 * @var integer
	 */
	protected $like_unit = 1;

	/**
	 * more hours old means deducting more scores from a post
	 * @var integer
	 */
	protected $chronological_unit = -1;

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
            $trending_posts = $this->getPaginatedPosts($limit);

            return $this->respondWithPagination($trending_posts, new PostTransformer);
        }
        $this->user = Auth::user();
        $this->me_and_my_follower_ids = $this->includeMyself($this->getMyFollowersIds());

		$trending_posts = $this->getPaginatedPosts($limit);

        $this->trackAction(Auth::user(), "Explore Posts");

		return $this->respondWithPagination($trending_posts, new PostTransformer);
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
        $this->assignChronologyRelevancy($post);
        $this->assignLikeRelevancy($post);

        //Sho wants to cosider like and chronological order only, so ignoring pin relevancy for now
        // $this->assignPinRelevancy($post);

        return $post;
    }

    /**
     * assigns chronology relevancy to a post
     * @param  [type] &$post  [description]
     * @param  [type] $weight [description]
     * @return [type]         [description]
     */
    private function assignChronologyRelevancy(&$post)
    {
        $hours_till_posted = $this->getHoursTillPosted($post['created_at']);

        $chronological_score = $this->calculateChronologicalScore($hours_till_posted);

        $post['score'] += $chronological_score;
    }

    /**
     * returns the chronological score depending on the no of hours
     * @param  number $hours no of hours ago the poat was posted
     * @return float       [description]
     */
    public function calculateChronologicalScore($hours_till_posted)
    {
    	$chronological_unit_weight = $this->calculateUnitWeight($this->weights['chronological_weight_distribution'], $this->chronological_unit);

        $chronological_score = $hours_till_posted * $chronological_unit_weight;

        return $chronological_score;
    }

    /**
     * returns the unit weight
     * @param  [type] $weight the weight distribution of a characteristic
     * @param  [type] $unit   unit value of thay characteristic
     * @return [type]         [description]
     */
    public function calculateUnitWeight($weight, $unit)
    {
    	// return $this->algorithmV1($weight, $unit);
    	return $this->algorithmV2($weight, $unit);
    }

    /**
     * simpler algorithm which plainly uses the weight distribution
     * @param  [type] $weight [description]
     * @param  [type] $unit   [description]
     * @return [type]         [description]
     */
    public function algorithmV1($weight, $unit)
    {
    	return $weight * $unit;
    }

    /**
     * slightly advanced algoritm using better weight management
     * @param  [type] $weight [description]
     * @param  [type] $unit   [description]
     * @return [type]         [description]
     */
    public function algorithmV2($weight, $unit)
    {
    	return ( $weight / (1 - $weight)) * $unit;
    }

    /**
     * assigns like relevancy to a post
     * @param  [type] &$post  [description]
     * @param  [type] $weight [description]
     * @return [type]         [description]
     */
    private function assignLikeRelevancy(&$post)
    {
    	$like_score = $this->calculateLikeScore($post['like_count']);
        $post['score'] += $like_score;
    }

    public function calculateLikeScore($like_count)
    {
    	$like_unit_weight = $this->calculateUnitWeight($this->weights['like_weight_distribution'], $this->like_unit);

        $like_score = $like_count * $like_unit_weight;

    	return $like_score;
    }

    /**
     * assingns Pin Relevancy to a post
     * @param  [type] &$post [description]
     * @return [type]        [description]
     */
    private function assignPinRelevancy(&$post)
    {
        $post['score'] += $post['pin_count'] * $this->weights['pin_weight_distribution'];
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
