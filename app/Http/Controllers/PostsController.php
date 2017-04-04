<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Acme\Transformers\PostTransformer;

class PostsController extends ApiController
{
    protected $post;
    
    /**
     * Acme/Transformers/postTransformer
     * @var postTransformer
     */
    protected $postTransformer;

    public function __construct(Post $post, PostTransformer $postTransformer)
    {
        $this->post = $post;
        $this->postTransformer = $postTransformer;
    }

    public function tmp()
    {
    	return $this->post->all()->load('owner','artist');
    }
}
