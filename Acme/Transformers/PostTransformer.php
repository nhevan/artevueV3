<?php

namespace Acme\Transformers;

use Illuminate\Support\Facades\Auth;

/**
*
*/
class PostTransformer extends Transformer
{
    public function transform($post)
    {
        return [
                'id' => $post['id'],

            ];
    }
	
}