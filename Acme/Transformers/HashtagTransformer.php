<?php

namespace Acme\Transformers;

/**
*
*/
class HashtagTransformer extends Transformer
{
    public function transform($hashtag)
    {
    	$hashtag = [
                'id' => $hashtag['id'],
                'hashtag' => $hashtag['hashtag'],
                'use_count' => $hashtag['use_count'],
                'created_at' => $hashtag['created_at']
            ];
        return $hashtag;
    }
}