<?php

namespace Acme\Transformers;

/**
*
*/
class EventTransformer extends Transformer
{
    public function transform($event)
    {
    	$event = [
                'id' => $event['id'],
                'headline' => $event['headline'],
                'description' => $event['description'],
                'location' => $event['location'],
                'city' => $event['city'],
                'image' => $event['image'],
                'url' => $event['url'],
                'start_date' => $event['start_date'],
                'end_date' => $event['end_date'],
                'publish_date' => $event['publish_date'],
                'created_at' => $event['created_at'],
            ];
        return $event;
    }
}