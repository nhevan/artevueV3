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
                'meta' => [
                	'test_gallery_name' => $user['metadata']['is_notification_enabled'],
                ]
            ];
    }
}