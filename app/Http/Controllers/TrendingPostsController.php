<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Acme\Transformers\PostTransformer;

class TrendingPostsController extends ApiController
{
    public function trendingPosts()
    {
    	$limit = 21;
    	$posts = Post::trending()->with('owner', 'tags', 'artist')->paginate($limit);

        return $this->respondWithPagination($posts, new PostTransformer);
    }
}
