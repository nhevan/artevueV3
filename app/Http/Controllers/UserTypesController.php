<?php

namespace App\Http\Controllers;

use App\UserType;
use Illuminate\Http\Request;
use Acme\Transformers\UserTypeTransformer;

class UserTypesController extends ApiController
{
	protected $userType;
    
    /**
     * Acme/Transformers/userTypeTransformer
     * @var userTypeTransformer
     */
    protected $userTypeTransformer;

    public function __construct(UserType $userType, UserTypeTransformer $userTypeTransformer)
    {
        $this->userType = $userType;
        $this->userTypeTransformer = $userTypeTransformer;
    }

    /**
     * lists all user types
     * @return response user types array
     */
    public function index(Request $request)
    {
        $user_types = $this->userType->where('title', '<>', 'Super Admin')->where('title', '<>', 'Admin')->get()->toArray();

        return $this->respond([
            'data' => $this->userTypeTransformer->transformCollection($user_types)
        ]);
    }
}
