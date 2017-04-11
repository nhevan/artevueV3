<?php

namespace Acme\Transformers;

use App\Post;

/**
*
*/
class DiscoverUserTransformer extends Transformer
{
    public function transform($user_metadata)
    {
        $latest_posts = $this->getLatest3Posts($user_metadata['user']['id']);

        $user_metadata = [
                'id' => $user_metadata['user']['id'],
                'username' => $user_metadata['user']['username'],
                'profile_picture' => $user_metadata['user']['profile_picture'],
                'latest_posts' => $latest_posts,
                'is_following' => 0
            ];
        return $user_metadata;
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
}