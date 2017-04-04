<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    /**
     * list all posts of a user
     * @return [type] [description]
     */
    public function index(Request $request)
    {
		$owner_id = $request->owner_id ? (int)$request->owner_id : Auth::user()->id;
		$owner = User::find($owner_id);
        if (!$owner) {
            return $this->responseNotFound('User does not exist.');
        }
        
    	$posts = $owner->posts()->with('artist', 'owner')->orderBy('id','DESC')->paginate(20);
    	return $this->respondWithPagination($posts, $this->postTransformer );
    }
}
