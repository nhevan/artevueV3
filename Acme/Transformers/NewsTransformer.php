<?php

namespace Acme\Transformers;

/**
*
*/
class NewsTransformer extends Transformer
{
    public function transform($news)
    {
    	$news = [
                'id' => $news['id'],
                'headline' => $news['headline'],
                'description' => $news['description'],
                'image' => $news['image'],
                'url' => $news['url'],
                'publish_date' => $news['publish_date'],
                'created_at' => $news['created_at'],
            ];
        return $news;
    }
}