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
        return [
            "artist" => $this->getLatest4AvatarsByUserType(6),
            "gallery" => $this->getLatest4AvatarsByUserType(4),
            "collector" => $this->getLatest4AvatarsByUserType(3),
            "enthusiast" => $this->getLatest4AvatarsByUserType(5),
            "professional" => $this->getLatest4AvatarsByUserType([7, 8, 9])
        ];
    }

    /**
     * returns the latest 4 users profile picture for a given user type
     * @param  [type] $user_type_id [description]
     * @return [type]               [description]
     */
    private function getLatest4AvatarsByUserType($user_type_id)
    {
        if (gettype($user_type_id) == "array") {
            $users = User::whereIn('user_type_id', $user_type_id)->select('profile_picture')->latest()->limit(4)->get();
        }
        else {
            $users = User::where('user_type_id', $user_type_id)->select('profile_picture')->latest()->limit(4)->get();
        }
        $avatars = [];
        foreach ($users as $user) {
            array_push($avatars, $user->profile_picture);
        }

        return $avatars;
    }
}
