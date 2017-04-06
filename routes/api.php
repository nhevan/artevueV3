<?php

use App\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/test-user/{user}', function (User $user) {
    return $user->load('metadata', 'userType');
});

//User APIs
// Route::middleware('auth:api')->get('/user', 'UsersController@index');
Route::middleware('auth:api')->get('/user/{user}', 'UsersController@show');
Route::middleware('auth:api')->post('/user', 'UsersController@store');
Route::middleware('auth:api')->put('/user', 'UsersController@edit');
Route::middleware('auth:api')->get('/current-user', 'UsersController@currentUser');
Route::middleware('auth:api')->get('/fetch-user-id', 'UsersController@fetchUserIdByUsername');
Route::middleware('auth:api')->get('/check-username', 'UsersController@checkUsername');
Route::middleware('auth:api')->get('/search-user', 'UsersController@searchUser');
Route::middleware('auth:api')->get('/facebook-login', 'UsersController@facebookLogin');
Route::middleware('auth:api')->post('/facebook-signup', 'UsersController@facebookSignup');
Route::middleware('auth:api')->patch('/update-gallery-info', 'UsersController@updateGalleryInfo');
Route::middleware('auth:api')->patch('/update-settings', 'UsersController@updateSettings');
Route::middleware('auth:api')->get('/check-email', 'UsersController@checkEmail');
Route::middleware('auth:api')->post('/update-profile-picture', 'UsersController@updateProfilePicture');

Route::middleware('auth:api')->get('/followers', 'FollowersController@getMyFollowers');
Route::middleware('auth:api')->get('/followers/{user_id}', 'FollowersController@getUserFollowers');
Route::middleware('auth:api')->post('/follower/{user_id}', 'FollowersController@store');

Route::middleware('auth:api')->post('/block/{user_id}', 'BlockedUsersController@store');

Route::middleware('auth:api')->post('/report/{user_id}', 'ReportedUsersController@store');

Route::middleware('auth:api')->get('/usertypes', 'UserTypesController@index');

Route::middleware('auth:api')->post('/message', 'MessagesController@store');
Route::middleware('auth:api')->get('/message-participants', 'MessageParticipantsController@index');
Route::middleware('auth:api')->get('/message/{friend_id}', 'MessagesController@index');
Route::middleware('auth:api')->delete('/conversation/{friend_id}', 'MessagesController@delete');

Route::middleware('auth:api')->get('/post', 'PostsController@index');
Route::middleware('auth:api')->get('/post/{owner_id}', 'PostsController@index');
Route::middleware('auth:api')->post('/post', 'PostsController@store');
Route::middleware('auth:api')->put('/post/{post}', 'PostsController@edit');
Route::middleware('auth:api')->patch('/post/{post_id}', 'PostsController@swapGalleryAndLockStatus');
Route::middleware('auth:api')->delete('/post/{post_id}', 'PostsController@delete');

Route::middleware('auth:api')->post('/pin/{post_id}', 'PinsController@store');
Route::middleware('auth:api')->delete('/pin/{post_id}', 'PinsController@delete');

Route::middleware('auth:api')->get('/hashtag/top-posts/{hashtag}', 'HashtagsController@topPosts');
Route::middleware('auth:api')->get('/hashtag/latest-posts/{hashtag}', 'HashtagsController@latestPosts');
Route::middleware('auth:api')->get('/search-hashtag', 'HashtagsController@searchHashtag');
