<?php

namespace App\Http\Controllers;

use App\Post;
use App\Follower;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\PostTransformer;

class DiscoverPostsController extends DiscoverController
{
    /**
     * returns a undiscovered collection of posts
     * @return [type] [description]
     */
    public function discoverPosts()
    {
    	$limit = 20;
        if ($this->userIsGuest()) {
            $my_followers_ids = $this->getAutoFollowersArray();
            $users_my_followers_are_following = Follower::whereIn('follower_id', $my_followers_ids)->whereNotIn('user_id', $my_followers_ids)->get()->pluck('user_id');

            $undiscovered_posts = $this->getPaginatedPosts($users_my_followers_are_following, $limit);

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
        $weight = [
            'follower_like_count' => .25,
            'chronological' => 1.75,
            'like_count' => .30,
            'pin_count' => .10,
        ];

        $this->assignChronologyRelevancy($post, $weight);
        $this->assignLikeRelevancy($post, $weight);
        $this->assignPinRelevancy($post, $weight);

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
        $constant = 72; //a post with 1 like and 72 hours old is equivalent in score to a post that has been recently created

        $hours_till_posted = $this->getHoursTillPosted($post['created_at']);
        $post['score'] = ( 1/ ( $hours_till_posted / $constant ) ) * $weight['chronological'];
    }

    /**
     * assigns like relevancy to a post
     * @param  [type] &$post  [description]
     * @param  [type] $weight [description]
     * @return [type]         [description]
     */
    private function assignLikeRelevancy(&$post, $weight)
    {
        $post['score'] += $post['like_count'] * $weight['like_count'];
    }

    /**
     * assingns Pin Relevancy to a post
     * @param  [type] &$post [description]
     * @return [type]        [description]
     */
    private function assignPinRelevancy(&$post, $weight)
    {
        $post['score'] += $post['pin_count'] * $weight['pin_count'];
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
}
