<?php

namespace Acme\Transformers;

use App\Pin;
use App\Like;
use App\Comment;
use App\Follower;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class PostTransformer extends Transformer
{
    protected $comment_count = 0;
    public function transform($post)
    {
        $is_following = $this->isFollowing($post['owner_id']);
        $is_liked = $this->isPostLiked($post['id']);
        $is_pinned = $this->isPostPinned($post['id']);
        $comments = $this->fetchComments($post['id']);

        $tags = [];
        if($post['tags'] != null){
            foreach ($post['tags'] as $tag) {
                $tmp = [
                    'user_id' => $tag['user_id'],
                    'username' => $tag['username'],
                    'x' => $tag['x'],
                    'y' => $tag['y']
                ];
                array_push($tags, $tmp);
            }
        }

    	$transformatted_post = [
                'id' => $post['id'],
                'image' => $post['image'],
                'description' => $post['description'],
                'hashtags' => $post['hashtags'],
                'aspect_ratio' => $post['aspect_ratio'],
                'price' => $post['price'],
                'artist_id' => $post['artist']['id'],
                'artist' => $post['artist']['title'],

                'has_buy_btn' => $post['has_buy_btn'],
                'is_public' => $post['is_public'],
                'is_gallery_item' => $post['is_gallery_item'],
                'is_locked' => $post['is_locked'],
                'sequence' => $post['sequence'],

                'google_place_id' => $post['google_place_id'],
                'address' => $post['address'],
                'address_title' => $post['address_title'],

                'pin_count' => $post['pin_count'],
                'comment_count' => $this->comment_count,
                'like_count' => $post['like_count'],

                'created_at' => $post['created_at'],

                'owner' => [
                	'id' => $post['owner_id'],
                    'name' => $post['owner']['name'],
                	'username' => $post['owner']['username'],
                	'profile_picture' => $post['owner']['profile_picture'],
                    'is_following' => $is_following
                ],
                'is_liked' => $is_liked,
                'is_pinned' => $is_pinned,
                'tagged_users' => $tags,
                'comments' => $comments
            ];

            if (array_key_exists('score', $post)) {
                $transformatted_post['score'] = $post['score'];
            }
        return $transformatted_post;
    }

    /**
     * checks whether the authenticated user is following given user
     * @param  [type]  $user_id [description]
     * @return boolean          [description]
     */
    public function isFollowing($user_id)
    {
        if (Auth::check()) {
            return !! Follower::where(['user_id' => $user_id, 'follower_id' => Auth::user()->id, 'is_still_following' => 1])->first();
        }
        return false;
    }

    /**
     * checks if a post is liked by the current user
     * @param  [type]  $post_id [description]
     * @return boolean          [description]
     */
    public function isPostLiked($post_id)
    {
        if (Auth::check()) {
        	$is_liked = Like::where(['user_id' => Auth::user()->id, 'post_id' => $post_id])->first();
        }else{
            $is_liked = false;
        }

        if ($is_liked) {
            return 1;
        }
        return 0;
    }

    /**
     * checks if a post is pinned by the current user
     * @param  [type]  $post_id [description]
     * @return boolean          [description]
     */
    public function isPostPinned($post_id)
    {
        if (Auth::check()) {
            $is_pinned = Pin::where(['user_id' => Auth::user()->id, 'post_id' => $post_id])->first();
        }else{
            $is_pinned = false;
        }
        if ($is_pinned) {
            return 1;
        }
        return 0;
    }

    /**
     * fetches the latest three comments of a given post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function fetchComments($post_id)
    {
        $comment_count = Comment::where('post_id', $post_id)->count();
        $this->comment_count = $comment_count;

        $comments = Comment::where('post_id', $post_id)
                    ->with(['commentor' => function($query){
                        return $query->select('id', 'username');
                    }]);

        if ($comment_count <= 3) {
            return $comments->get()->toArray();
        }
        
        return $comments->take(3)->get()->sortByDesc('id')->values()->all();
    }
	
}