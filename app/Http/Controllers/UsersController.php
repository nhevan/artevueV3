<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Like;
use App\Post;
use App\User;
use App\Comment;
use App\Follower;
use App\UserArtType;
use App\UserMetadata;
use App\ArtPreference;
use App\Mail\WelcomeEmail;
use App\UserArtPreference;
use Illuminate\Http\Request;
use App\Mail\NewPasswordEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Acme\Transformers\UserTransformer;
use App\Notifications\QueuedJobFailed;
use Acme\Transformers\ActivityTransformer;
use Acme\Transformers\FollowerTransformer;
use Acme\Transformers\UserSearchTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response as IlluminateResponse;

class UsersController extends ApiController
{
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
        $this->startFollowingArtevue($user->id);
        $metadata = New UserMetadata;
        $user->metadata()->save($metadata);

        //start following ArteVue
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
        $this->startFollowingArtevue($user->id);
        $metadata = New UserMetadata;
        $user->metadata()->save($metadata);

        //start following ArteVue
        $this->sendWelcomeEmail($user);
        return $this->respondWithAccessToken($user);
    }

    /**
     * starts following artevue account
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function startFollowingArtevue($user_id)
    {
        Follower::create(['user_id'=> 33, 'follower_id' => $user_id]);
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
     * returns a user id if a user is found with the provided username
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
            return $this->show($user->id);
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
        $rules = [
            'search_string' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $search_string = $request->search_string;

        $limit = 5;
        if((int)$request->limit <= 20) $limit = (int)$request->limit ?: 5;
        $users = $this->user->where('username', 'like', '%'.$search_string.'%')->orWhere('name', 'like', '%'.$search_string.'%')->orWhere('email', 'like', '%'.$search_string.'%')->with('usertype', 'metadata', 'artPreferences', 'arttypes')->paginate($limit);

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
            'name' => 'required|max:50',
            'email' => 'required|email',
            'user_type_id' => 'required|not_in:1,2',
            'website' =>'nullable|url',
            'biography' => 'nullable|max:250',
            'phone' => 'nullable',
            'sex' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        if(!$this->emailIsUnique($request)){
            return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError([ 'message' => [ 'email' => 'The email has already been taken.' ] ] );
        }
        // return $request->all();
        $user = $request->user();

        $user->email = $request->email;
        $user->name = $request->name;
        $user->user_type_id = $request->user_type_id;
        if ($request->website) {
            $user->website = $request->website;
        }
        if ($request->biography) {
            $user->biography = $request->biography;
        }
        if ($request->phone) {
            $user->phone = $request->phone;
        }
        $user->sex = $request->sex;
        $user->gcm_registration_key = $request->gcm_registration_key;
         
        $this->request = $request;
        $this->updateArtPreferences($user);
        $this->updateArtTypes($user);
        
        $user->save();

        return $this->respond( [ 'message' => 'The user has been updated.' ] );
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
        $auth_user = $request->user()->toArray();
        $email_owner = $this->user->where('email', $request->email)->first();
        
        if (!$email_owner) {
            return true;
        }
        if ($auth_user == $email_owner->toArray()) {
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
            'profile_picture' => 'required|file',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        
        $path = $request->file('profile_picture')->store(
            'img/profile_pic', 's3'
        );

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
     * sends a new generated password to users email address
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

        Mail::to($user->email)->queue(new NewPasswordEmail($user));

        return $this->respond(['message' => 'An email has been sent to your email with the new password']);
    }

    /**
     * returns true if old password matches
     * @return [type] [description]
     */
    public function oldPasswordMatches()
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
     * test method to check if slack notifications are working
     * @return [type] [description]
     */
    public function testSlack()
    {
        $user = new User;
        $user->notify(new QueuedJobFailed());

        return $this->respond(['message' => 'Message successfully posted to slack webhook.']);
    }
}
