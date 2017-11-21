<?php

namespace Acme\Transformers;

use App\Pin;

/**
*
*/
class GalleryTransformer extends Transformer
{
    public function transform($gallery)
    {
        $first_four_pins = $this->fetchFirstPins($gallery['id'], 4);

    	$transformatted_gallery = [
                'id' => $gallery['id'],
                'user_id' => $gallery['user_id'],
                'sequence' => $gallery['sequence'],
                'name' => $gallery['name'],
                'description' => $gallery['description'],
                'email' => $gallery['email'],
                'website' => $gallery['website'],
                'is_private' => $gallery['is_private'],
                'first_four_pins' => $first_four_pins
            ];

        return $transformatted_gallery;
    }

    /**
     * fetches the first pins of a given gallery
     * @param  [type] $gallery_id [description]
     * @param  [type] $no_of_pins No of pins that the function will return
     * @return [type]          [description]
     */
    public function fetchFirstPins($gallery_id, $no_of_pins)
    {
        $pins = Pin::where('gallery_id', $gallery_id);

        return $pins->orderBy('sequence')->take($no_of_pins)->get();
    }
	
}