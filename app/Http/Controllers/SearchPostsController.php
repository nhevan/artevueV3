<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class SearchPostsController extends ApiController
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

    public function search()
    {
    	// $this->validateSearchRequest($this->request, 'Post');

    	// $posts = $this->search($this->request);

    	// return $this->respond($posts);
    	$posts = new Post();
    	if ($this->request->minimum_price) {
    		$posts = $posts->where('price', '>=', $this->request->minimum_price);
    	}

    	if ($this->request->description) {
    		$posts = $posts->where('description', 'like', '%'.$this->request->description.'%');
    	}

    	$posts = $posts->get();

    	return $this->respond($posts);
    }
}
