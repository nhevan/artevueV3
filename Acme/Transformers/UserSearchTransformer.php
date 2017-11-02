<?php

namespace Acme\Transformers;

use App\Post;
use App\Follower;
use Illuminate\Support\Facades\Auth;

/**
*
*/
class UserSearchTransformer extends Transformer
{
    public function transform($user)
    {
        $latest_posts = $this->getLatest3Posts($user['id']);
        $post_count = $this->getPostCount($user['id']);
        $is_following = $this->isFollowing($user['id']);

        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'],
                'user_type_id' => $user['user_type_id'],
                'latest_posts' => $latest_posts,
                'total_posts' => $post_count,
                'is_following' => $is_following
            ];
    }

    /**
     * fetches the latest 3 posts of a given user
     * @param  integer $user_id [description]
     * @return array $latest_posts
     */
    public function getLatest3Posts($user_id)
    {
        $latest_posts = Post::where('owner_id', $user_id)->select(['id', 'image'])->latest()->limit(3)->get()->toArray();

        return $latest_posts;
    }

    /**
     * fetches the total post count of a given user
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getPostCount($user_id)
    {
        return Post::where('owner_id', $user_id)->count();
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
}