<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Acme\Transformers\UserTransformer;

class UsersController extends ApiController
{
    protected $user;
    
    /**
     * Acme/Transformers/userTransformer
     * @var userTransformer
     */
    protected $userTransformer;

    public function __construct(User $user, UserTransformer $userTransformer)
    {
        $this->user = $user;
        $this->userTransformer = $userTransformer;
    }

    /**
     * lists all users
     * @return response users array
     */
    public function index(Request $request)
    {
        $limit = 5;
        if((int)$request->limit <= 20) $limit = (int)$request->limit ?: 5;
        $users = $this->user->with('metadata')->paginate($limit);

        return $this->respondWithPagination($users, $this->userTransformer);
    }
}
