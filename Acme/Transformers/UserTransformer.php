<?php

namespace Acme\Transformers;

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
            ];
    }
}