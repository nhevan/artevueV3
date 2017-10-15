<?php

namespace Acme\Transformers;

use App\Post;
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

        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'],
                'user_type_id' => $user['user_type_id'],
                'latest_posts' => $latest_posts,
                'total_posts' => $post_count
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
}