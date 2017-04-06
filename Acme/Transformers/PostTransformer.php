<?php

namespace Acme\Transformers;

use App\Pin;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class PostTransformer extends Transformer
{
    public function transform($post)
    {
    	$is_liked = $this->isPostLiked($post['id']);
    	$is_pinned = $this->isPostPinned($post['id']);

        $tags = [];
        if($post['tags'] != null){
            foreach ($post['tags'] as $tag) {
                $tmp = [
                    'user_id' => $tag['user_id'],
                    'username' => $tag['username']
                ];
                array_push($tags, $tmp);
            }
        }

    	$post = [
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

                'address_title' => $post['address_title'],

                'pin_count' => $post['pin_count'],
                'comment_count' => $post['comment_count'],
                'like_count' => $post['like_count'],

                'created_at' => $post['created_at'],

                'owner' => [
                	'id' => $post['owner_id'],
                	'username' => $post['owner']['username'],
                	'profile_picture' => $post['owner']['profile_picture'],
                ],
                'is_liked' => $is_liked,
                'is_pinned' => $is_pinned,
                'tagged_users' => $tags
            ];
        return $post;
    }

    /**
     * checks if a post is liked by the current user
     * @param  [type]  $post_id [description]
     * @return boolean          [description]
     */
    public function isPostLiked($post_id)
    {
    	return 0;
    }

    /**
     * checks if a post is pinned by the current user
     * @param  [type]  $post_id [description]
     * @return boolean          [description]
     */
    public function isPostPinned($post_id)
    {
        $is_pinned = Pin::where(['user_id' => Auth::user()->id, 'post_id' => $post_id])->first();
        if ($is_pinned) {
            return 1;
        }
        return 0;
    }
	
}