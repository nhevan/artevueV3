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

Route::middleware('auth:api')->get('/feed-top-bar', function(){
	$data['news_image'] = '/news.jpg';
	$data['events_image'] = '/event.jpg';

	return response()->json(['data' => $data], 200);
});

//User APIs
// Route::middleware('auth:api')->get('/user', 'UsersController@index');
Route::middleware('auth:api')->get('/user/{user}', 'UsersController@show');
Route::middleware('auth:api')->post('/user', 'UsersController@store');
Route::middleware('auth:api')->put('/user', 'UsersController@edit');
Route::middleware('auth:api')->get('/current-user', 'UsersController@currentUser');
Route::middleware('auth:api')->get('/user-by-username', 'UsersController@fetchUserByUsername');
Route::middleware('auth:api')->get('/check-username', 'UsersController@checkUsername');
Route::middleware('auth:api')->get('/search-user', 'UsersController@searchUser');
Route::middleware('auth:api')->get('/facebook-login', 'UsersController@facebookLogin');
Route::middleware('auth:api')->post('/facebook-signup', 'UsersController@facebookSignup');
Route::middleware('auth:api')->patch('/update-gallery-info', 'UsersController@updateGalleryInfo');
Route::middleware('auth:api')->patch('/update-settings', 'UsersController@updateSettings');
Route::middleware('auth:api')->get('/check-email', 'UsersController@checkEmail');
Route::middleware('auth:api')->post('/update-profile-picture', 'UsersController@updateProfilePicture');
Route::middleware('auth:api')->get('/discover-users', 'DiscoverController@discoverUsers');
Route::middleware('auth:api')->get('/user-activities', 'UsersController@userActivities');
Route::middleware('auth:api')->get('/follower-activities', 'UsersController@followerActivities');
Route::middleware('auth:api')->patch('/password', 'UsersController@changePassword');
Route::middleware('auth:api')->get('/forgot-password', 'UsersController@sendNewPasswordEmail');
Route::middleware('auth:api')->get('/test-slack', 'UsersController@testSlack');

Route::middleware('auth:api')->get('/followers', 'FollowersController@getMyFollowers');
Route::middleware('auth:api')->get('/followers/{user_id}', 'FollowersController@getUserFollowers');
Route::middleware('auth:api')->post('/follower/{user_id}', 'FollowersController@store');
Route::middleware('auth:api')->get('/following', 'FollowersController@getFollowingUsers');
Route::middleware('auth:api')->get('/following/{user_id}', 'FollowersController@getFollowingUsersByUser');

Route::middleware('auth:api')->post('/block/{user_id}', 'BlockedUsersController@store');

Route::middleware('auth:api')->post('/report/{user_id}', 'ReportedUsersController@store');

Route::middleware('auth:api')->get('/usertypes', 'UserTypesController@index');

Route::middleware('auth:api')->post('/message', 'MessagesController@store');
Route::middleware('auth:api')->get('/message-participants', 'MessageParticipantsController@index');
Route::middleware('auth:api')->get('/message/{friend_id}', 'MessagesController@index');
Route::middleware('auth:api')->delete('/conversation/{friend_id}', 'MessagesController@delete');

Route::middleware('auth:api')->get('/post', 'PostsController@index');
Route::middleware('auth:api')->get('/post/{owner_id}', 'PostsController@index');
Route::middleware('auth:api')->get('/post/detail/{post}', 'PostsController@show');
Route::middleware('auth:api')->post('/post', 'PostsController@store');
Route::middleware('auth:api')->put('/post/{post}', 'PostsController@edit');
Route::middleware('auth:api')->patch('/post/{post_id}', 'PostsController@swapGalleryAndLockStatus');
Route::middleware('auth:api')->delete('/post/{post_id}', 'PostsController@delete');
Route::middleware('auth:api')->get('/post/tagged/{user_id}', 'PostsController@taggedPosts');
Route::middleware('auth:api')->get('/post/likes/{post_id}', 'PostsController@postLikes');
Route::middleware('auth:api')->get('/feed', 'PostsController@feed');
Route::middleware('auth:api')->get('/discover-posts', 'DiscoverController@discoverPosts');
Route::middleware('auth:api')->get('/advance-search', 'PostsController@advanceSearch');
Route::middleware('auth:api')->post('/email-gallery-pdf', 'PostsController@emailGalleryPdf');
Route::middleware('auth:api')->get('/gallery/{user_id}', 'PostsController@getGallery');
Route::middleware('auth:api')->post('/arrange-gallery', 'PostsController@arrangeGalleryPosts');

Route::middleware('auth:api')->post('/pin/{post_id}', 'PinsController@store');
Route::middleware('auth:api')->delete('/pin/{post_id}', 'PinsController@delete');
Route::middleware('auth:api')->get('/pin/posts/{user_id}', 'PinsController@pinnedPosts');

Route::middleware('auth:api')->post('/like/{post_id}', 'LikesController@store');
Route::middleware('auth:api')->delete('/like/{post_id}', 'LikesController@delete');

Route::middleware('auth:api')->get('/hashtag/top-posts/{hashtag}', 'HashtagsController@topPosts');
Route::middleware('auth:api')->get('/hashtag/latest-posts/{hashtag}', 'HashtagsController@latestPosts');
Route::middleware('auth:api')->get('/search-hashtag', 'HashtagsController@searchHashtag');
Route::middleware('auth:api')->get('/hashtag-by-name', 'HashtagsController@getHashtagByName');

Route::middleware('auth:api')->get('/artist', 'ArtistsController@index');
Route::middleware('auth:api')->get('/search-artist', 'ArtistsController@searchArtist');
Route::middleware('auth:api')->get('/artist/posts/{artist_id}', 'ArtistsController@posts');
Route::middleware('auth:api')->get('/artist-by-name', 'ArtistsController@getPostsByArtistName');

Route::middleware('auth:api')->post('/comment/{post_id}', 'CommentsController@store');
Route::middleware('auth:api')->get('/comment/{post_id}', 'CommentsController@index');
Route::middleware('auth:api')->delete('/comment/{comment_id}', 'CommentsController@delete');

Route::middleware('auth:api')->get('/news', 'NewsController@index');
Route::middleware('auth:api')->get('/events', 'EventsController@index');

Route::middleware('auth:api')->get('/test-email-queue/{user}', 'UsersController@sendWelcomeEmail');

Route::middleware('auth:api')->get('/art-preferences', 'ArtPreferencesController@index');
Route::middleware('auth:api')->get('/art-types', 'ArtTypesController@index');
