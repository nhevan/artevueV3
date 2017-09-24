<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class SearchUsersController extends ApiController
{
    protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function search()
    {
    	// $this->validateSearchRequest($this->request, 'User');

    	// $posts = $this->search($this->request);

    	// return $this->respond($posts);
    	$users = new User();

    	if ($this->request->username) {
    		$users = $users->where('username', 'like', '%'.$this->request->username.'%');
    	}

    	if ($this->request->name) {
    		$users = $users->where('name', 'like', '%'.$this->request->name.'%');
    	}

    	$users = $users->get();

    	return $this->respond($users);
    }
}
