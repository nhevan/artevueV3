<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Searchability\PostCrawler;

class SearchPostsController extends BaseSearchController
{
	protected $request;
	protected $postCrawler;
	protected $rules = [
		            'price' => 'digits_between:0,*'
		        ];

	public function __construct(Request $request,PostCrawler $postCrawler)
	{
		$this->request = $request;
		$this->postCrawler = $postCrawler;
	}

    public function search()
    {
    	if (!$this->setRequest($this->request)->isValidated($this->rules)) {
            return $this->responseValidationError();
        }

    	$posts = $this->postCrawler->search();

    	return $this->respond($posts);
    }
}
