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

Route::middleware('auth:api')->get('/feed-top-bar', function(){
	$data['news_image'] = '/news.jpg';
	$data['events_image'] = '/event.jpg';

	return response()->json(['data' => $data], 200);
});

Route::middleware('api')->post('/user', 'UsersController@store');
Route::middleware('api')->get('/facebook-login', 'UsersController@facebookLogin');
Route::middleware('auth:api')->patch('/password', 'UsersController@changePassword');
Route::middleware('api')->get('/forgot-password', 'UsersController@sendNewPasswordEmail');
Route::middleware('api')->post('/auth/{provider}/login', 'UsersController@login');
Route::middleware('api')->post('/auth/{provider}/signup', 'UsersController@signup');

Route::middleware('auth.optional:api')->get('/user/{user}', 'UsersController@fetch');
Route::middleware('auth:api')->put('/user', 'UsersController@edit');
Route::middleware('auth:api')->get('/current-user', 'UsersController@currentUser');
Route::middleware('api')->get('/user-by-username', 'UsersController@fetchUserByUsername');
Route::middleware('api')->get('/check-username', 'UsersController@checkUsername');
Route::middleware('api')->get('/search-user', 'UsersController@searchUser');
Route::middleware('auth:api')->post('/facebook-signup', 'UsersController@facebookSignup');
Route::middleware('auth:api')->patch('/update-gallery-info', 'UsersController@updateGalleryInfo');
Route::middleware('auth:api')->patch('/update-settings', 'UsersController@updateSettings');
Route::middleware('api')->get('/check-email', 'UsersController@checkEmail');
Route::middleware('auth:api')->post('/update-profile-picture', 'UsersController@updateProfilePicture');
Route::middleware('auth.optional:api')->get('/discover-users', 'DiscoverController@discoverUsers');
Route::middleware('auth:api')->get('/user-activities', 'UsersController@userActivities');
Route::middleware('auth:api')->get('/follower-activities', 'UsersController@followerActivities');
Route::middleware('auth:api')->delete('/user/{user}', 'UsersController@destroy');

Route::middleware('auth:api')->get('/followers', 'FollowersController@getMyFollowers');
Route::middleware('auth.optional:api')->get('/followers/{user_id}', 'FollowersController@getUserFollowers');
Route::middleware('auth:api')->post('/follower/{user_id}', 'FollowersController@store');
Route::middleware('auth:api')->get('/following', 'FollowersController@getFollowingUsers');
Route::middleware('auth.optional:api')->get('/following/{user_id}', 'FollowersController@getFollowingUsersByUser');

Route::middleware('auth:api')->post('/block/{user_id}', 'BlockedUsersController@store');

Route::middleware('auth:api')->post('/report/{user_id}', 'ReportedUsersController@store');

Route::middleware('api')->get('/usertypes', 'UserTypesController@index');

Route::middleware('auth:api')->post('/message', 'MessagesController@store');
Route::middleware('auth:api')->get('/message-participants', 'MessageParticipantsController@index');
Route::middleware('auth:api')->get('/message/{friend_id}', 'MessagesController@index');
Route::middleware('auth:api')->delete('/conversation/{friend_id}', 'MessagesController@delete');

Route::middleware('auth.optional:api')->get('/post', 'PostsController@index');
Route::middleware('auth.optional:api')->get('/post/{owner_id}', 'PostsController@index');
Route::middleware('auth.optional:api')->get('/post/detail/{post}', 'PostsController@show');
Route::middleware('auth:api')->post('/post', 'PostsController@store'); //v2-3
Route::middleware('auth:api')->post('/fetch-suggested-hashtags', 'PostsController@fetchSuggestedHashtags'); //v3
Route::middleware('auth:api')->put('/post/{post}', 'PostsController@edit');
Route::middleware('auth:api')->patch('/post/{post_id}', 'PostsController@swapLockStatus');
Route::middleware('auth:api')->delete('/post/{post_id}', 'PostsController@delete');
Route::middleware('auth:api')->get('/post/tagged/{user_id}', 'PostsController@taggedPosts');
Route::middleware('auth.optional:api')->get('/post/likes/{post_id}', 'PostsController@postLikes');
Route::middleware('auth.optional:api')->get('/feed', 'PostsController@feed');
Route::middleware('auth.optional:api')->get('/discover-posts', 'DiscoverPostsController@discoverPosts');
Route::middleware('auth.optional:api')->get('/advance-search', 'PostsController@advanceSearch');
Route::middleware('auth:api')->post('/email-gallery-pdf', 'PostsController@emailGalleryPdf');
Route::middleware('auth.optional:api')->get('/gallery/{user_id}', 'PostsController@getGallery');
Route::middleware('auth:api')->post('/arrange-gallery', 'PostsController@arrangeGalleryPosts');
Route::middleware('auth:api')->post('/gallery', 'GalleriesController@store');
Route::middleware('api')->get('/user/{user_id}/galleries', 'GalleriesController@index');
Route::middleware('auth:api')->patch('/user/{user_id}/galleries', 'GalleriesController@arrangeGallery');
Route::middleware('auth:api')->patch('/gallery/{gallery_id}/arrange-pins', 'GalleriesController@arrangePins');
Route::middleware('api')->get('/user/{user_id}/gallery/{gallery_id}', 'GalleriesController@show');
Route::middleware('auth:api')->delete('/gallery/{gallery_id}', 'GalleriesController@destroy');
Route::middleware('auth:api')->patch('/gallery/{gallery}', 'GalleriesController@update');

Route::middleware('auth:api')->post('/pin/{post_id}', 'PinsController@storeOld'); //v2
Route::middleware('auth:api')->delete('/pin/{post_id}', 'PinsController@deleteOld'); //v2
Route::middleware('auth:api')->post('/gallery/{gallery_id}/pin/{post_id}', 'PinsController@store'); //v3
Route::middleware('auth:api')->delete('/gallery/{gallery_id}/pin/{post_id}', 'PinsController@delete'); //v3
Route::middleware('auth.optional:api')->get('/pin/posts/{user_id}', 'PinsController@pinnedPosts');

Route::middleware('auth:api')->post('/like/{post_id}', 'LikesController@store');
Route::middleware('auth:api')->delete('/like/{post_id}', 'LikesController@delete');

Route::middleware('auth.optional:api')->get('/hashtag/top-posts/{hashtag}', 'HashtagsController@topPosts');
Route::middleware('auth.optional:api')->get('/hashtag/latest-posts/{hashtag}', 'HashtagsController@latestPosts');
Route::middleware('api')->get('/search-hashtag', 'HashtagsController@searchHashtag');
Route::middleware('auth.optional:api')->get('/hashtag-by-name', 'HashtagsController@getHashtagByName');

Route::middleware('api')->get('/artist', 'ArtistsController@index');
Route::middleware('api')->get('/search-artist', 'ArtistsController@searchArtist');
Route::middleware('auth.optional:api')->get('/artist/posts/{artist_id}', 'ArtistsController@posts');
Route::middleware('auth.optional:api')->get('/artist-by-name', 'ArtistsController@getPostsByArtistName');

Route::middleware('auth:api')->post('/comment/{post_id}', 'CommentsController@store');
Route::middleware('api')->get('/comment/{post_id}', 'CommentsController@index');
Route::middleware('auth:api')->delete('/comment/{comment_id}', 'CommentsController@delete');

Route::middleware('api')->get('/news', 'NewsController@index');
Route::middleware('api')->get('/events', 'EventsController@index');

Route::middleware('api')->get('/art-preferences', 'ArtPreferencesController@index');
Route::middleware('api')->get('/art-types', 'ArtTypesController@index');

Route::middleware('auth:api')->get('/test-slack', 'UsersController@testSlack');
Route::middleware('auth:api')->get('/test-email-queue/{user}', 'UsersController@sendWelcomeEmail');
Route::middleware('auth:api')->get('/test-mixpanel', 'UsersController@testMixpanel');
Route::middleware('api')->get('/status', 'SettingsController@index');

Route::middleware('api')->get('/search-posts', 'SearchPostsController@search');
Route::middleware('api')->get('/search-users', 'SearchUsersController@search');