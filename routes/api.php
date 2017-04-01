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

Route::middleware('auth:api')->get('/followers', 'FollowersController@getMyFollowers');
Route::middleware('auth:api')->get('/followers/{user_id}', 'FollowersController@getUserFollowers');
Route::middleware('auth:api')->post('/follower/{user_id}', 'FollowersController@store');

Route::middleware('auth:api')->get('/usertypes', 'UserTypesController@index');

