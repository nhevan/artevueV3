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
        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'],
                'user_type' => $user['user_type_id'],
            	'is_notification_enabled' => $user['metadata']['is_notification_enabled'],
            	'is_account_private' => $user['metadata']['is_account_private'],
            	'is_save_to_phone' => $user['metadata']['is_save_to_phone'],
            ];
    }
}