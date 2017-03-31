<?php

namespace App\Http\Controllers;

use App\User;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

    /**
     * returns a single user detail
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function show($id)
    {
        $user = $this->user->find($id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }
        return $this->respondTransformattedModel($user, $this->userTransformer);
    }

    /**
     * signup a new user
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:50',
            'username' => 'required|min:4|max:20|unique:users,username',
            'password' => 'required|min:6',
            'email' => 'required|email|unique:users,email',
            'user_type_id' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $request->merge(array( 'password' => bcrypt($request->password) ));
        $request->merge(array( 'profile_picture' => 'img/profile-holder.png' ));

        $user = $this->user->create($request->all());
        //start following ArteVue
        $this->sendWelcomeEmail($user);
        return $this->respondWithAccessToken($user);
    }

    /**
     * returns the details of the loggedin user, in other words return the user with whom the access token is associated
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function currentUser(Request $request)
    {
        return $this->respondTransformattedModel( $request->user(), $this->userTransformer);
    }

    /**
     * return a generated access token for the given user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function respondWithAccessToken(User $user)
    {
        $access_token = $user->createToken('signup-token')->accessToken;
        return $this->respond([
            'token_type' => 'Bearer',
            'expires_in' => 31536000,
            'access_token' => $access_token
        ]);
    }

    /**
     * allows a user to login via facebook email address {THIS IS NOT THE RIGHT APPROACH}
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function facebookLogin(Request $request)
    {
        $email_address = $request->email;
        $user = $this->user->where('email', $email_address)->first();
        if ($user) {
            return $this->respondWithAccessToken($user);
        }
        return $this->responseNotFound('This email address is not associated with any user. Try signing up first.');
        
    }

    /**
     * allows a user to signup via facebook {THIS IS NOT THE RIGHT APPROACH}
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function facebookSignup(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|min:4|max:20|unique:users,username',
            'email' => 'required|email',
            'user_type_id' => 'required',
            'social_id' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $user_exists = $this->user->where('email', $request->email)->first();
        if ($user_exists) {
            return $this->respondWithAccessToken($user_exists);
        }

        $request->merge(array( 'name' => $request->first_name.' '.$request->last_name ));
        $request->merge(array( 'profile_picture' => 'img/profile-holder.png' ));

        $user = $this->user->create($request->all());
        //start following ArteVue
        $this->sendWelcomeEmail($user);
        return $this->respondWithAccessToken($user);
    }

    /**
     * sends welcome email to a user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function sendWelcomeEmail(User $user)
    {
        return Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}
