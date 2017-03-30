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

Route::middleware('auth:api')->get('/current-user', 'UsersController@currentUser');
Route::middleware('auth:api')->get('/user', 'UsersController@index');
Route::middleware('auth:api')->get('/user/{user}', 'UsersController@show');
Route::middleware('auth:api')->post('/user', 'UsersController@store');
Route::middleware('auth:api')->get('/facebook-login', 'UsersController@facebookLogin');
