<?php

namespace Acme\Transformers;

use Illuminate\Support\Facades\Auth;

/**
*
*/
class UserSearchTransformer extends Transformer
{
    public function transform($user)
    {
        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'],
            ];
    }
}