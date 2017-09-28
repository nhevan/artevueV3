<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Searchability\PostCrawler;
use Acme\Transformers\PostTransformer;

class SearchPostsController extends BaseSearchController
{
	protected $request;
	protected $postCrawler;
	

	public function __construct(Request $request, PostCrawler $postCrawler)
	{
		$this->request = $request;
		$this->postCrawler = $postCrawler;
	}

    public function search()
    {
    	if (!$this->setRequest($this->request)->isValidated($this->postCrawler->rules)) {
            return $this->responseValidationError();
        }

    	$posts = $this->postCrawler->search();
    	$posts->load('owner', 'artist', 'tags');

    	$postTransformer = new PostTransformer;
    	return $this->respondWithPagination($posts, $postTransformer );
    }
}
