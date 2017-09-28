<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Searchability\UserCrawler;
use Acme\Transformers\UserSearchTransformer;

class SearchUsersController extends BaseSearchController
{
    protected $request;
	protected $userCrawler;
	

	public function __construct(Request $request,UserCrawler $userCrawler)
	{
		$this->request = $request;
		$this->userCrawler = $userCrawler;
	}

    public function search()
    {
    	if (!$this->setRequest($this->request)->isValidated($this->userCrawler->rules)) {
            return $this->responseValidationError();
        }

    	$users = $this->userCrawler->search();

    	$userSearchTransformer = new UserSearchTransformer;
    	return $this->respondWithPagination($users, $userSearchTransformer );
    }
}
