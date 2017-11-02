<?php

namespace App\Http\Controllers;

use App\User;
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

    /**
     * Returns an array of profile picture of all user types
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function avatars(Request $request)
    {
        $artist = User::where('user_type_id', 6)->latest()->first();
        $gallery = User::where('user_type_id', 4)->latest()->first();
        $collector = User::where('user_type_id', 3)->latest()->first();
        $enthusiast = User::where('user_type_id', 5)->latest()->first();
        $professional = User::whereIn('user_type_id', [7, 8, 9])->latest()->first();

        return [
            "artist" => $artist->profile_picture,
            "gallery" => $gallery->profile_picture,
            "collector" => $collector->profile_picture,
            "enthusiast" => $enthusiast->profile_picture,
            "professional" => $professional->profile_picture
        ];
    }
}
