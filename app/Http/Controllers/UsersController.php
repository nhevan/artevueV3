<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Like;
use App\Post;
use App\User;
use App\Comment;
use App\Message;
use App\Follower;
use App\UserType;
use Carbon\Carbon;
use App\BlockedUser;
use App\UserArtType;
use App\ReportedUser;
use App\UserMetadata;
use App\ArtPreference;
use App\Mail\WelcomeEmail;
use App\UserArtPreference;
use App\MessageParticipant;
use App\UserArtInteraction;
use Illuminate\Http\Request;
use App\Mail\NewPasswordEmail;
use App\Traits\UserSwissKnife;
use App\Traits\CounterSwissKnife;
use App\Traits\FileUploadSwissKnife;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Acme\Transformers\PostTransformer;
use Acme\Transformers\UserTransformer;
use App\Notifications\QueuedJobFailed;
use App\Traits\NotificationSwissKnife;
use Acme\Transformers\ActivityTransformer;
use Acme\Transformers\FollowerTransformer;
use Acme\Transformers\UserSearchTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response as IlluminateResponse;

class UsersController extends ApiController
{
    use CounterSwissKnife, NotificationSwissKnife, UserSwissKnife, FileUploadSwissKnife;
    protected $user;
    
    /**
     * Acme/Transformers/userTransformer
     * @var userTransformer
     */
    protected $userTransformer;
    protected $request;

    public function __construct(User $user, UserTransformer $userTransformer, Request $request)
    {
        $this->user = $user;
        $this->userTransformer = $userTransformer;
        $this->request = $request;
    }

    /**
     * lists all users
     * @return response users array
     */
    public function index(Request $request, $type = 'all')
    {
        $limit = 18;
        if((int)$request->limit <= 20) $limit = (int)$request->limit ?: 18;
        $users = $this->user->sortable()->latest()->with(['metadata', 'userType'])->paginate($limit);

        if(!request()->wantsJson()){
            if ($user_type_id = $this->foundMatchingUserType($type)) {
                $users = $this->user->sortable()->latest()->with(['metadata', 'userType'])->where('user_type_id', $user_type_id)->paginate($limit);
            }
            $user_types = $this->getUserTypesArray();

            return view('users.index', compact(['users', 'user_types']));
        }

        return $this->respondWithPagination($users, $this->userTransformer);
    }

    /**
     * returns the id of the user type if a match is found, otherwise false
     * @param  [type] $user_type [description]
     * @return [type]            [description]
     */
    private function foundMatchingUserType($user_type)
    {
        $available_usertypes = $this->getUserTypesArray();
        $available_usertypes = array_map('strtolower', $available_usertypes);

        if (in_array(strtolower($user_type), $available_usertypes)) {
            $user_type_id = array_search(strtolower($user_type),$available_usertypes);
            
            return $user_type_id;
        }
    }

    /**
     * returns an array of user types
     * @return [type] [description]
     */
    private function getUserTypesArray()
    {
        return UserType::where('id', '>', 1)->where('id', '<', 11)->get()->pluck('title', 'id')->toArray();
    }

    /**
     * returns a single user detail for web
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function show($id)
    {
        $user = $this->user->find($id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        $user->load(['userType', 'metadata', 'artPreferences', 'artTypes']);

        $post_ids = Post::where('owner_id', $user->id)->pluck('id');
        $comments_received = Comment::whereIn('post_id', $post_ids)->count();
        $likes_received = Like::whereIn('post_id', $post_ids)->count();

        return view('users.show', compact(['user', 'comments_received', 'likes_received']));
    }

    public function viewUserPosts($user_id)
    {
        $user = $this->user->find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        $user_posts =  Post::where('owner_id', $user_id)->latest()->with(['owner'])->paginate(20);

        return view('posts.index', ['posts' => $user_posts]);
    }

    /**
     * returns the profile of the logged-in user
     * @return [type] [description]
     */
    public function myProfile()
    {
        return $this->fetch(Auth::user()->id);
    }

    /**
     * returns a single user detail for api
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function fetch($id)
    {
        $user = $this->user->find($id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        if (!$this->userIsGuest()) {
            $this->trackAction(Auth::user(), "View Profile", ['User ID' => $id]);
        }

        return $this->respondTransformattedModel($user, $this->userTransformer);
    }

    /**
     * signs up a user via social media (facebook and Instagram)
     * @param  [type]  $provider [description]
     * @param  Request $request  [description]
     * @return [type]            [description]
     */
    public function signup($provider, Request $request)
    {
        if (!$this->isAllowed($provider)) {
            return $this->setStatusCode(422)->respondWithError("{$provider} is not a known social media integrated with Artevue yet.");
        }

        if ($provider == "artevue") {
            return $this->signupViaArtevue($request);
        }

        if ($provider == "instagram") {
            return $this->signupViaInstagram($request);
        }

        if ($provider == "facebook") {
            return $this->signupViaFacebook($request);
        }
    }

    /**
     * allow users to signup via artevue
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function signupViaArtevue(Request $request)
    {
        return $this->store($request);
    }

    protected function processSocialSignup($media, Request $request)
    {
        $rules = [
            'name' => 'required|max:50',
            'social_media_uid' => 'bail|required',
            'social_media_access_token' => 'bail|required',
            'username' => 'required|min:4|max:20|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'user_type_id' => 'bail|required|numeric|min:3|max:10|',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $request->merge(array( 'profile_picture' => 'img/profile-holder.png' ));

        $user = $this->user->create($request->all());
        
        $metadata = New UserMetadata;
        $user->metadata()->save($metadata);

        $this->startAutoFollowingUsers($user->id);

        $this->trackAction($user, "New Signup", ['media' => $media]);
        $this->sendWelcomeEmail($user);

        $this->updateUsersSocialMediaInfo($user, $media, $request->social_media_uid, $request->social_media_access_token);
        return $this->respondWithAccessToken($user);
    }

    /**
     * signup via facebook
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function signupViaFacebook(Request $request)
    {
        return $this->processSocialSignup('facebook', $request);
    }

    /**
     * signup via facebook
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function signupViaInstagram(Request $request)
    {
        return $this->processSocialSignup('instagram', $request);
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
            'user_type_id' => 'bail|required|numeric|min:3|max:10|'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $request->merge(array( 'password' => bcrypt($request->password) ));
        $request->merge(array( 'profile_picture' => 'img/profile-holder.png' ));

        $user = $this->user->create($request->all());
        
        $metadata = New UserMetadata;
        $user->metadata()->save($metadata);

        $this->startAutoFollowingUsers($user->id);

        $this->trackAction($user, "New Signup", ['media' => 'App']);
        $this->sendWelcomeEmail($user);

        return $this->respond(['message' => 'User successfully signed up.']);
    }

    /**
     * returns the details of the loggedin user, in other words return the user with whom the access token is associated
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function currentUser(Request $request)
    {
        // $user = $request->user();
        // $user->load('reportedUsers');
        // return $user;
        return $this->respondTransformattedModel( $request->user(), $this->userTransformer);
    }

    /**
     * return a generated access token for the given user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    protected function respondWithAccessToken(User $user)
    {
        $access_token = $user->createToken('signup-token')->accessToken;
        return $this->respond([
            'token_type' => 'Bearer',
            'expires_in' => 31536000,
            'access_token' => $access_token
        ]);
    }

    /**
     * allows a user to login via social media (Facebook and Instagram)
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login($provider, Request $request)
    {
        if (!$this->isAllowed($provider)) {
            return $this->setStatusCode(422)->respondWithError("{$provider} is not a known social media integrated with Artevue yet.");
        }

        if ($provider == "artevue") {
            return $this->loginViaArtevue($request);
        }

        if ($provider == "instagram") {
            return $this->loginViaInstagram($request);
        }

        if ($provider == "facebook") {
            return $this->loginViaFacebook($request);
        }
    }

    /**
     * attempt login via artevue
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function loginViaArtevue(Request $request)
    {
        $rules = [
            'username' => 'required|min:4|max:20',
            'password' => 'required|min:6',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            return $this->respondWithAccessToken($user);
        }
        
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError('The user credentials were incorrect.');
    }

    /**
     * attempt login via facebook
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function loginViaFacebook(Request $request)
    {
        $rules = [
            'access_token' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $fb_response = $this->fetchUserEmailFromFBAccessToken($request->access_token);

        if ($fb_response->getStatusCode() == 400) { //invalid access token
            return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError('Invalid access token.');
        }

        $fb_response_object = json_decode($fb_response->getBody());

        $email_address = $fb_response_object->email;
        $user = $this->user->where('email', $email_address)->first();
        if ($user) {
            $this->updateUsersSocialMediaInfo($user, 'facebook', $fb_response_object->id, $request->access_token);
            return $this->respondWithAccessToken($user);
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError('This email address is not associated with any user. Try signing up first.');
    }

    /**
     * attempt login via instagram
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    protected function loginViaInstagram(Request $request)
    {
        $rules = [
            'access_token' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $instagram_response = $this->fetchUserInstagramId($request->access_token);
        if ($instagram_response->getStatusCode() == 400) { //invalid access token
            return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError('Invalid access token.');
        }

        $instagram_response_object = json_decode($instagram_response->getBody(), true);

        $instagram_uid = $instagram_response_object['data']['id'];
        $user = $this->user->where('social_media_uid', $instagram_uid)->where('social_media', 'instagram')->first();
        if ($user) {
            $this->updateUsersSocialMediaInfo($user, 'instagram', $instagram_response_object['data']['id'], $request->access_token);
            return $this->respondWithAccessToken($user);
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError('This user needs to sign up with instagram first.');
    }

    /**
     * updates social media info for a given user
     * @param  User    $user     [description]
     * @param  [type]  $provider [description]
     * @param  Request $request  [description]
     * @return [type]            [description]
     */
    protected function updateUsersSocialMediaInfo(User $user, $provider, $social_media_uid, $social_media_access_token)
    {
        $user->social_media = $provider;
        $user->social_media_uid = $social_media_uid;
        $user->social_media_access_token = $social_media_access_token;

        $user->save();
    }

    /**
     * checks if the provider is a valid provider
     * @param  [type]  $provider [description]
     * @return boolean           [description]
     */
    protected function isAllowed($provider)
    {
        return in_array($provider, ['artevue', 'facebook', 'instagram']);
    }

    /**
     * allows a user to login via facebook email address {THIS IS NOT THE RIGHT APPROACH - Apparently this is the right method as per Ben}
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
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondWithError('This email address is not associated with any user. Try signing up first.');
        
    }

    /**
     * allows a user to signup via facebook {THIS IS NOT THE RIGHT APPROACH - Apparently this is the right method as per Ben}
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function facebookSignup(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|max:20|unique:users,username',
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

        $metadata = New UserMetadata;
        $user->metadata()->save($metadata);

        $this->startAutoFollowingUsers($user->id);

        $this->trackAction($user, "New Signup", ['media' => 'facebook']);
        $this->sendWelcomeEmail($user);
        return $this->respondWithAccessToken($user);
    }

    protected function startAutoFollowingUsers($user_id)
    {
        $this->startFollowingArtevue($user_id);
        $this->startFollowingHarpersBazaar($user_id);
        $this->startFollowingMestaria($user_id);
        $this->startFollowingThirdline($user_id);
        $this->startFollowingAfficheGallery($user_id);
        $this->startFollowingShoChoudhury($user_id);
        $this->startFollowingDelfinaFoundation($user_id);
        $this->startFollowingEmergeast($user_id);
        $this->startFollowingAndakulova($user_id);
    }

    /**
     * starts following Sho Choudhury
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingShoChoudhury($follower_id)
    {
        $this->startFollowing(74, $follower_id);
    }

    /**
     * starts following Unit Gallery account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingUnitGallery($follower_id)
    {
        $this->startFollowing(601, $follower_id);
    }

    /**
     * starts following Thirdline account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingThirdline($follower_id)
    {
        $this->startFollowing(663, $follower_id);
    }

    /**
     * starts following Affiche Gallery account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingAfficheGallery($follower_id)
    {
        $this->startFollowing(306, $follower_id);
    }

    /**
     * starts following Delfina Foundation
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingDelfinaFoundation($follower_id)
    {
        $this->startFollowing(1637, $follower_id);
    }

    /**
     * starts following Emergeast aka Fidan Huseyni
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingEmergeast($follower_id)
    {
        $this->startFollowing(567, $follower_id);
    }

    /**
     * starts following Emergeast aka Natalya Andakulova
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingAndakulova($follower_id)
    {
        $this->startFollowing(2523, $follower_id);
    }

    /**
     * starts following Mestaria account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingMestaria($follower_id)
    {
        $this->startFollowing(128, $follower_id);
    }

    /**
     * starts following artevue account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function startFollowingArtevue($follower_id)
    {
        $this->startFollowing(33, $follower_id);
    }

    /**
     * starts following Harpers Bazaar account
     * @param  [type] $follower_id [description]
     * @return [type]              [description]
     */
    protected function startFollowingHarpersBazaar($follower_id)
    {
        $this->startFollowing(204, $follower_id);
    }

    /**
     * starts following a specific user
     * @param  [type] $user_id     [description]
     * @param  [type] $follower_id [description]
     * @return [type]              [description]
     */
    protected function startFollowing($user_id, $follower_id)
    {
        Follower::create(['user_id'=> $user_id, 'follower_id' => $follower_id]);

        $this->incrementFollowerCount($user_id);
        $this->incrementFollowingCount($follower_id);
    }

    /**
     * sends welcome email to a user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function sendWelcomeEmail(User $user)
    {
        return Mail::to($user->email)->queue(new WelcomeEmail($user));
    }

    /**
     * returns a user if a user is found with the provided username
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function fetchUserByUsername(Request $request)
    {
        $rules = [
            'username' => 'required|min:4|max:20',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $user = $this->user->where('username', $request->username)->first();
        if ($user) {
            return $this->fetch($user->id);
        }
        return $this->responseNotFound('No such user exists.');
    }

    /**
     * check whether a username is available
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function checkUsername(Request $request)
    {
        $rules = [
            'username' => 'required|min:4|max:20',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $user = $this->user->where('username', $request->username)->first();
        if ($user) {
            return $this->respond(['message' => 'Username taken.', 'is_taken' => 1]);
        }
        return $this->respond(['message' => 'Username available.', 'is_taken' => 0]);
    }

    /**
     * search users by name or username
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function searchUser(Request $request)
    {
        $limit = 100;
        $rules = [
            'search_string' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $search_string = $request->search_string;

        $users = $this->user->where('username', 'like', '%'.$search_string.'%')->orWhere('name', 'like', '%'.$search_string.'%')->orWhere('email', 'like', '%'.$search_string.'%')->with('usertype', 'metadata', 'artPreferences', 'arttypes')->paginate($limit);

        if(!request()->wantsJson()){
            return view('users.index', compact('users'));
        }

        $userSearchTransformer = new UserSearchTransformer;
        return $this->respondWithPagination($users, $userSearchTransformer );
    }

    /**
     * updates user specific info like name, website, biography, user_type_id, email, phone, sex [art_preferences and art_types]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function edit(Request $request)
    {
        $rules = [
            'name' => 'max:50',
            'username' => 'min:4|max:20',
            'email' => 'email',
            'user_type_id' => 'numeric|min:3|max:10',
            'sex' => 'numeric|in:1,2',
            'website' =>'nullable',
            'biography' => 'nullable|max:250',
            'phone' => 'nullable',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        if ($request->username) {
            $duplicate_username = $this->user->where('id', '<>', $request->user()->id)->where('username', $request->username)->first();

            if ($duplicate_username) {
                return $this->setValidationErrors(['username' => 'This username is already taken.'])->responseValidationError();
            }
        }
        if(!$this->emailIsUnique($request)){
            return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError([ 'message' => [ 'email' => 'The email has already been taken.' ] ] );
        }

        $user = $request->user();

        $user->fill($request->all());

        $user->save();

        return $this->respond( [ 'message' => 'The user has been updated.' ] );
    }

    /**
     * allows a user to update their instagram uid 
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateInstagramId(Request $request)
    {
        $rules = [
            'instagram_uid' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $user = $request->user();
        $user->instagram_uid = $request->input('instagram_uid');
        $user->save();

        return $this->respond( [ 'message' => 'The user has successfully updated their instagram_uid.' ] );
    }

    /**
     * swaps the privacy status of a users account
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function swapPrivacy(Request $request)
    {
        if ($request->user()->isPrivate()) {
            $user = $request->user();
            $user->metadata->is_account_private = 0;
            $user->metadata->save();

            return $this->respond( [ 'message' => 'This users account is now public.' ] );
        }

        $user = $request->user();
        $user->metadata->is_account_private = 1;
        $user->metadata->save();
        return $this->respond( [ 'message' => 'This users account is now private.' ] );
    }

    /**
     * updates the authenticated users location
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateLocation(Request $request)
    {
        $rules = [
            'location' => 'max:100',
            'latitude' => 'max:100',
            'longitude' => 'max:100'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $user = $request->user();
        $user->fill($request->all());
        $user->save();

        return $this->respond( [ 'message' => 'The users location has been updated.' ] );
    }


    /**
     * allows admins to edit username of any user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function editUsername(User $user)
    {
        if ($this->request->isMethod('GET')) {
            return view('users.edit-username', compact('user'));
        }

        $this->validate($this->request, [
                'username' => 'min:4|max:20'
            ]);

        if ($this->request->username) {
            $duplicate_username = $this->user->where('id', '<>', $user->id)->where('username', $this->request->username)->first();

            if ($duplicate_username) {
                return back()->withErrors("The username '{$this->request->username}' is already taken.");
            }
        }

        $user->username = $this->request->username;

        $user->save();

        request()->session()->flash('status', 'Username successfully changed !');
        return redirect()->route('users.show', ['user' => $user->id]);
    }

    /**
     * updates selected art preferences of the current user
     * @return [type] [description]
     */
    public function updateArtPreferences(User $user)
    {
        if($this->request->art_preferences){
            UserArtPreference::where('user_id', $user->id)->delete();

            foreach ($this->request->art_preferences as $art_pref) {
                UserArtPreference::create(['user_id' => $user->id, 'art_preference_id' => $art_pref['id'] ]);
            }
        }
    }

    /**
     * updates selected art types of the given user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function updateArtTypes(User $user)
    {
        if($this->request->art_types){
            UserArtType::where('user_id', $user->id)->delete();

            foreach ($this->request->art_types as $art_type) {
                UserArtType::create(['user_id' => $user->id, 'art_type_id' => $art_type['id'] ]);
            }
        }
    }

    /**
     * returns TRUE ONlY IF the provided email is not taken by anyone else OR the user himself is the owner of the email
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function emailIsUnique(Request $request)
    {
        $auth_user = $request->user();
        $email_owner = $this->user->where('email', $request->email)->first();
        
        if (!$email_owner) {
            return true;
        }
        
        if ($auth_user->id == $email_owner->id) {
            return true;
        }
    }

    /**
     * updates users gallery name and description
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateGalleryInfo(Request $request)
    {
        $rules = [
            'gallery_name' => 'nullable|max:50',
            'gallery_description' => 'nullable',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $user_metadata = UserMetadata::where('user_id', Auth::user()->id)->first();
        $user_metadata->gallery_name = $request->gallery_name;
        $user_metadata->gallery_description = $request->gallery_description;
        $user_metadata->save();
        
        return $this->respond(['message' => 'Users gallery info successfuly updated.']);
    }

    /**
     * updates users is_notification_enabled, is_account_private and is_save_to_phone
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateSettings(Request $request)
    {
        $rules = [
            'is_notification_enabled' => 'required|numeric',
            'is_account_private' => 'required|numeric',
            'is_save_to_phone' => 'required|numeric',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $user_metadata = UserMetadata::where('user_id', Auth::user()->id)->first();

        $user_metadata->is_notification_enabled = $request->is_notification_enabled;
        $user_metadata->is_account_private = $request->is_account_private;
        $user_metadata->is_save_to_phone = $request->is_save_to_phone;

        $user_metadata->save();
        
        return $this->respond(['message' => 'User settings successfuly updated.']);
    }

    /**
     * check whether a email is available
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function checkEmail(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $user = $this->user->where('email', $request->email)->first();
        if ($user) {
            return $this->respond(['message' => 'Email taken.', 'is_taken' => 1]);
        }
        return $this->respond(['message' => 'Email available.', 'is_taken' => 0]);
    }

    /**
     * updates a users profile picture
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updateProfilePicture(Request $request)
    {
        $rules = [
            'profile_picture' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        
        // $path = $request->file('profile_picture')->store(
        //     'img/profile_pic', 's3'
        // );
        $path = $this->uploadPostImageTos3('profile_picture', 'img/profile_pic');

        $user = Auth::user();
        $user->profile_picture = $path;
        $user->save();

        return $this->respond(['message'=>'Profile picture has been successfully updated.']);
    }

    /**
     * returns a list of user activities
     * @return [type] [description]
     */
    public function userActivities()
    {
        $limit = 10;
        $user = $this->request->user();

        $my_post_ids = Post::where('owner_id', $user->id)->pluck('id')->toArray();

        $like_activities = $this->getLikeActivities($my_post_ids);
        $comment_activities = $this->getCommentActivities($my_post_ids);
        $pin_activities = $this->getPinActivities($my_post_ids);
        $following_activities = $this->getFollowingActivities();

        $all_activities = $like_activities->merge($comment_activities);
        $all_activities = $all_activities->merge($pin_activities);
        $all_activities = $all_activities->merge($following_activities);

        $all_activities = $all_activities->sortByDesc('created_at')->values()->all();

        $paginated_result = $this->getPaginated($all_activities, $limit);

        return $this->respondWithPagination($paginated_result, new ActivityTransformer);
    }

    /**
     * returns a list of user's followers activities
     * @return [type] [description]
     */
    public function followerActivities()
    {
        $limit = 10;
        $user = $this->request->user();

        $following_user_ids = Follower::where('follower_id', $user->id)->pluck('user_id')->toArray();

        $like_activities = $this->getLikeActivitiesForFollowingUsers($following_user_ids);
        $comment_activities = $this->getCommentActivitiesForFollowingUsers($following_user_ids);
        $pin_activities = $this->getPinActivitiesForFollowingUsers($following_user_ids);
        $following_activities = $this->getFollowingActivitiesForFollowingUsers($following_user_ids);

        $all_activities = $like_activities->merge($comment_activities);
        $all_activities = $all_activities->merge($pin_activities);
        $all_activities = $all_activities->merge($following_activities);

        $all_activities = $all_activities->sortByDesc('created_at')->values()->all();

        $paginated_result = $this->getPaginated($all_activities, $limit);

        return $this->respondWithPagination($paginated_result, new ActivityTransformer);
    }

    /**
     * get all like activities
     * @param  [type] $user_ids [description]
     * @return [type]           [description]
     */
    public function getLikeActivitiesForFollowingUsers($user_ids)
    {
        $likes = Like::whereIn('user_id', $user_ids)->where('user_id', '<>', $this->request->user()->id)->with('user', 'post')->select(['id', 'user_id', 'post_id', 'created_at'])->get();
        $likes->map(function ($like) {
            $like['type'] = 'like';
            return $like;
        });

        return $likes;
    }

    /**
     * get all like activities
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getLikeActivities($post_ids)
    {
        $likes = Like::whereIn('post_id', $post_ids)->where('user_id', '<>', $this->request->user()->id)->with('user', 'post')->select(['id', 'user_id', 'post_id', 'created_at'])->get();
        $likes->map(function ($like) {
            $like['type'] = 'like';
            return $like;
        });

        return $likes;
    }

    /**
     * get all comment activities
     * @param  [type] $user_ids [description]
     * @return [type]           [description]
     */
    public function getCommentActivitiesForFollowingUsers($user_ids)
    {
        $comments = Comment::whereIn('user_id', $user_ids)->where('user_id', '<>', $this->request->user()->id)->with('commentor', 'post')->get();
        $comments->map(function ($comment) {
            $comment['type'] = 'comment';
            return $comment;
        });

        return $comments;
    }

    /**
     * get all comment activities
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getCommentActivities($post_ids)
    {
        $comments = Comment::whereIn('post_id', $post_ids)->where('user_id', '<>', $this->request->user()->id)->with('commentor', 'post')->get();
        $comments->map(function ($comment) {
            $comment['type'] = 'comment';
            return $comment;
        });

        return $comments;
    }

    /**
     * get all pin activities
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getPinActivitiesForFollowingUsers($user_ids)
    {
        $pins = Pin::whereIn('user_id', $user_ids)->where('user_id', '<>', $this->request->user()->id)->with('user', 'post')->get();
        $pins->map(function ($pin) {
            $pin['type'] = 'pin';
            return $pin;
        });

        return $pins;
    }

    /**
     * get all pin activities
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getPinActivities($post_ids)
    {
        $pins = Pin::whereIn('post_id', $post_ids)->where('user_id', '<>', $this->request->user()->id)->with('user', 'post')->get();
        $pins->map(function ($pin) {
            $pin['type'] = 'pin';
            return $pin;
        });

        return $pins;
    }

    /**
     * get all following type activity
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getFollowingActivitiesForFollowingUsers($user_ids)
    {
        $following = Follower::whereIn('follower_id', $user_ids)->where('is_still_following', 1)->where('user_id', '<>', $this->request->user()->id)->with('followerDetail', 'user')->get();
        $following->map(function ($following) {
            $following['type'] = 'following';
            return $following;
        });

        return $following;
    }

    /**
     * get all following type activity
     * @param  [type] $post_ids [description]
     * @return [type]           [description]
     */
    public function getFollowingActivities()
    {
        $following = Follower::where('user_id', $this->request->user()->id)->where('is_still_following', 1)->with('followerDetail', 'user')->get();
        $following->map(function ($following) {
            $following['type'] = 'following';
            return $following;
        });

        return $following;
    }

    /**
     * changes the password of a user
     * @return [type] [description]
     */
    public function changePassword()
    {
        $rules = [
            'old_password' => 'required|min:6',
            'new_password' => 'required|min:6',
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        if ($this->oldPasswordMatches()) {
            $this->updatePassword();
            return $this->respond(['message' => 'Password changed successfully.']);
        }
        
        return $this->responseUnauthorized('Your old password did not match our record.');
    }

    /**
     * displays a form to admins to set password for a given user
     * @return [type] [description]
     */
    public function showSetPasswordForm(User $user)
    {
        return view('users.set-password', compact('user'));
    }

    /**
     * set password for a given user
     * @param User $user [description]
     */
    public function setPassword(User $user)
    {
        if($this->request->new_password !== $this->request->confirm_password){
            return back()->withErrors(['The confirm password field did not match with the new password field.']);;
        }
        $rules = [
            'new_password' => 'required|min:6'
        ];
        $this->validate($this->request, $rules);

        $user->password = bcrypt($this->request->new_password);
        $user->save();

        request()->session()->flash('status', 'Password successfully changed !');
        return redirect()->route('users.show', ['user' => $user->id]);
    }

    /**
     * sends a new generated password to a given user email address
     * @return [type] [description]
     */
    public function sendNewPasswordEmail()
    {
        $rules = [
            'email' => 'required|email',
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $user = $this->user->where('email', $this->request->email)->first();
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        return $this->resetPassword($user);
    }

    /**
     * sends reset password email to a given user
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function resetPassword(User $user)
    {
        Mail::to($user->email)->queue(new NewPasswordEmail($user));

        if(request()->wantsJson()){
            return $this->respond(['message' => 'An email has been sent to your email with the new password']);
        }

        request()->session()->flash('status', 'Password reset email successfully sent !');
        return back();
    }

    /**
     * returns true if old password matches
     * @return [type] [description]
     */
    protected function oldPasswordMatches()
    {
        return Hash::check($this->request->old_password, $this->request->user()->password);
    }

    /**
     * updates a users password to a given new one
     * @return [type] [description]
     */
    public function updatePassword()
    {
        $this->request->user()->password = bcrypt($this->request->new_password);

        $this->request->user()->save();
    }

    /**
     * deletes a user object
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function destroy(User $user)
    {
        $followers = Follower::where('user_id', $user->id)->orWhere('follower_id', $user->id)->get();
        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $participants = MessageParticipant::where('participant_one', $user->id)->orWhere('participant_two', $user->id)->get();
        $reported_users = ReportedUser::where('user_id', $user->id)->orWhere('suspect_id', $user->id)->get();
        $blocks = BlockedUser::where('user_id', $user->id)->orWhere('blocked_user_id', $user->id)->get();
        $art_interactions = UserArtInteraction::where('user_id', $user->id)->get();
        $art_preferences = UserArtPreference::where('user_id', $user->id)->get();
        $art_types = UserArtType::where('user_id', $user->id)->get();

        foreach ($followers as $follower) {
            $follower->delete();
        }
        foreach ($participants as $participant) {
            $participant->delete();
        }
        foreach ($messages as $message) {
            $message->delete();
        }
        foreach ($reported_users as $report) {
            $report->delete();
        }
        foreach ($blocks as $block) {
            $block->delete();
        }
        foreach ($art_interactions as $art_interaction) {
            $art_interaction->delete();
        }
        foreach ($art_preferences as $art_preference) {
            $art_preference->delete();
        }
        foreach ($art_types as $art_type) {
            $art_type->delete();
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted!');
    }

    /**
     * sends notification to a single or all user
     * @param  User|null $user [description]
     * @return [type]          [description]
     */
    public function sendNotification(User $user = null)
    {
        if ($this->request->isMethod('GET')) {
            return view('users.send-notification-form', compact('user'));
        }

        if ($user->id) {
            $this->sendGenericNotification($this->request->notification, $user->id);

            request()->session()->flash('status', 'Notification successfully sent!');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        $this->sendGenericNotification($this->request->notification);

        request()->session()->flash('status', 'Notification successfully sent!');
        return redirect()->route('users.index');
    }

    /**
     * test method to check if slack notifications are working
     * @return [type] [description]
     */
    public function testSlack()
    {
        $user = new User;
        $user->notify(new QueuedJobFailed());

        return $this->respond(['message' => 'Message successfully posted to slack webhook.']);
    }

    /**
     * test mix panel integration
     * @return [type] [description]
     */
    public function testMixpanel()
    {
        dispatch( new SendMixpanelAction(Auth::user(), "New Action Dispatched.", ['test' => 'properties']));

        return $this->respond(['message' => 'Test Mix Panel action successfully sent.']);
    }
}
