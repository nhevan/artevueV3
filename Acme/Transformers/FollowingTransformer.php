<?php

namespace Acme\Transformers;

/**
*
*/
class FollowingTransformer extends Transformer
{
    public function transform($following)
    {
    	$following = [
                'id' => $following['id'],
                'user_id' => $following['user_id'],
                'username' => $following['user']['username'],
                'profile_picture' => $following['user']['profile_picture']
            ];
        return $following;
    }
}