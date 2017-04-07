<?php

namespace Acme\Transformers;

/**
*
*/
class DiscoverUserTransformer extends Transformer
{
    public function transform($user_metadata)
    {
        $latest_posts = [];
        foreach ($user_metadata['latest3posts'] as $post) {
            $minified_post = [
                'id' => $post['id'],
                'image' => $post['image'],
            ];
            array_push($latest_posts, $minified_post);
        }

        $user_metadata = [
                'id' => $user_metadata['user']['id'],
                'username' => $user_metadata['user']['username'],
                'profile_picture' => $user_metadata['user']['profile_picture'],
                // 'score' => $user_metadata['post_count'] + $user_metadata['comment_count'] + $user_metadata['like_count'] + $user_metadata['pin_count'] + $user_metadata['message_count'] + $user_metadata['follower_count'] + $user_metadata['following_count'] + $user_metadata['tagged_count'],
                'latest_posts' => $latest_posts,
                'is_following' => 0
            ];
        return $user_metadata;
    }

}