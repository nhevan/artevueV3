<?php

namespace Acme\Transformers;

use App\UserType;

/**
*
*/
class UserTypeTransformer extends Transformer
{
    public function transform($userType)
    {
        return [
                'id' => $userType['id'],
                'title' => $userType['title'],
                'description' => $userType['description'],
            ];
    }
}