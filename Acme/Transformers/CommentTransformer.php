<?php

namespace Acme\Transformers;

/**
*
*/
class CommentTransformer extends Transformer
{
    public function transform($comment)
    {
    	$comment = [
                'id' => $comment['id'],
                'comment' => $comment['comment'],
                'user_id' => $comment['user_id'],
                'username' => $comment['commentor']['username'],
                'profile_picture' => $comment['commentor']['profile_picture'],
                'created_at' => $comment['created_at'],
            ];
        return $comment;
    }
}