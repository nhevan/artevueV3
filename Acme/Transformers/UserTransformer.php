<?php

namespace Acme\Transformers;

use App\User;

/**
*
*/
class UserTransformer extends Transformer
{
    public function transform($user)
    {
    	$user['is_following'] = 0;
    	$user['is_blocked'] = 0;

        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'],
                'biography' => $user['biography'],
                'website' => $user['website'],

                'user_type' => $user['usertype']['title'],

                'post_count' => $user['metadata']['post_count'],
                'comment_count' => $user['metadata']['comment_count'],
                'like_count' => $user['metadata']['like_count'],
                'pin_count' => $user['metadata']['pin_count'],
                'message_count' => $user['metadata']['message_count'],
                'follower_count' => $user['metadata']['follower_count'],
                'following_count' => $user['metadata']['following_count'],
                'tagged_count' => $user['metadata']['tagged_count'],
            	'is_notification_enabled' => $user['metadata']['is_notification_enabled'],
            	'is_account_private' => $user['metadata']['is_account_private'],
            	'is_save_to_phone' => $user['metadata']['is_save_to_phone'],

            	'is_following' => $user['is_following'],
            	'is_blocked' => $user['is_blocked'],
            ];
    }
}