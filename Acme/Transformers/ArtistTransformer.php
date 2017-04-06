<?php

namespace Acme\Transformers;

/**
*
*/
class ArtistTransformer extends Transformer
{
    public function transform($artist)
    {
    	$artist = [
                'id' => $artist['id'],
                'name' => $artist['title'],
                // 'post_count' => $artist['post_count'],
            ];
        return $artist;
    }

	
}