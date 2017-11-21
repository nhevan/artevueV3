<?php

use App\Post;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::get('/manage-tokens', function() {
    return view('tokens');
})->middleware('auth');

Route::get('/test-pdf', function () {
	$posts = Post::whereIn('id', [1,2,3])->get()->toArray();
	$data['gallery_name'] = 'ArteVue';
	$data['gallery_description'] = 'ArteVue is the first art ecosystem for artists, art lovers, collectors, art professionals and art institutions to discover, buy, collect, curate, and catalogue art. 
www.artevue.co.uk
Info@artevue.co.uk';
	$data['posts'] = $posts;

	// return view('pdf.gallery', compact('data'));

	// var_dump($data);
	// exit();

	$pdf = App::make('snappy.pdf.wrapper');
	$pdf->loadView('pdf.gallery', compact('data'));
	$pdf->setPaper('a4')->setOption('margin-bottom', '0mm');
	return $pdf->inline();
});

Route::middleware('auth')->get('/settings', 'SettingsController@index')->name('settings.index');
Route::middleware('auth')->get('/settings/edit/app', 'SettingsController@editAppSettings')->name('settings.edit-app-settings');
Route::middleware('auth')->post('/settings/edit/app', 'SettingsController@editAppSettings')->name('settings.edit-app-settings');
Route::middleware('auth')->get('/settings/edit/weight', 'SettingsController@editWeightSettings')->name('settings.edit-weight-settings');
Route::middleware('auth')->post('/settings/edit/weight', 'SettingsController@editWeightSettings')->name('settings.edit-weight-settings');

Route::middleware('auth')->get('/users/{type?}', 'UsersController@index')->name('users.index');
Route::middleware('auth')->get('/user/{user}', 'UsersController@show')->name('users.show');
Route::middleware('auth')->delete('/users/{user}', 'UsersController@destroy')->name('users.destroy');
Route::middleware('auth')->get('/user/{user}/edit-username', 'UsersController@editUsername')->name('user.edit-username-form');
Route::middleware('auth')->patch('/user/{user}/edit-username', 'UsersController@editUsername')->name('user.edit-username');
Route::middleware('auth')->post('/search-users', 'UsersController@searchUser')->name('users.search');
Route::middleware('auth')->get('/user-posts/{user_id}', 'UsersController@viewUserPosts')->name('users.posts');
Route::middleware('auth')->get('/send-reset-password-email/{user}', 'UsersController@resetPassword')->name('user.reset-password');
Route::middleware('auth')->get('/change-password-form/{user}', 'UsersController@showSetPasswordForm')->name('user.change-password-form');
Route::middleware('auth')->post('/change-password/{user}', 'UsersController@setPassword')->name('user.change-password');
Route::middleware('auth')->get('/send-notification-form/{user?}', 'UsersController@sendNotification')->name('user.send-notification-form');
Route::middleware('auth')->post('/send-notification/{user?}', 'UsersController@sendNotification')->name('user.send-notification');

Route::middleware('auth')->get('/posts', 'PostsController@indexWeb')->name('posts.index');
Route::middleware('auth')->get('/posts/trending', 'DiscoverPostsController@discoverPosts')->name('posts.trending');
Route::middleware('auth')->get('/posts/arteprize', 'PostsController@artePrizePosts')->name('posts.arteprize');
Route::middleware('auth')->get('/posts/buy', 'PostsController@onSalePosts')->name('posts.buy');
Route::middleware('auth')->get('/posts/curators-choice', 'PostsController@artevueSelectedPosts')->name('posts.curators-choice');
Route::middleware('auth')->get('/post/{post}', 'PostsController@showWeb')->name('posts.show');
Route::middleware('auth')->get('/post/edit/{post}', 'PostsController@showEditForm')->name('posts.edit-form');
Route::middleware('auth')->post('/post/{post}', 'PostsController@editWeb')->name('posts.edit');
Route::middleware('auth')->delete('/post/{post}', 'PostsController@delete')->name('posts.destroy');
Route::middleware('auth')->patch('/post/{post}/swap-discoverability', 'PostsController@swapDiscoverability')->name('posts.swapDiscoverability');
Route::middleware('auth')->patch('/post/{post}/swap-sale-status', 'PostsController@swapSaleStatus')->name('posts.swapSaleStatus');
Route::middleware('auth')->patch('/post/{post}/swap-curators-selection-status', 'PostsController@swapCuratorSelectionStatus')->name('posts.swapCuratorsSelectionStatus');

Route::middleware('auth')->get('/events', 'EventsController@all');
Route::middleware('auth')->get('/events/show-create-form', 'EventsController@showAddForm');
Route::middleware('auth')->post('/events', 'EventsController@store');
Route::middleware('auth')->get('/events/{event}', 'EventsController@show');
Route::middleware('auth')->get('/events/edit/{event}', 'EventsController@showEditForm');
Route::middleware('auth')->post('/events/edit/{event}', 'EventsController@edit');
Route::middleware('auth')->get('/events/delete/{event}', 'EventsController@destroy');

Route::middleware('auth')->get('/news', 'NewsController@all');
Route::middleware('auth')->get('/news/show-create-form', 'NewsController@showAddForm');
Route::middleware('auth')->post('/news', 'NewsController@store');
Route::middleware('auth')->get('/news/{news}', 'NewsController@show');
Route::middleware('auth')->get('/news/edit/{news}', 'NewsController@showEditForm');
Route::middleware('auth')->post('/news/edit/{news}', 'NewsController@edit');
Route::middleware('auth')->get('/news/delete/{news}', 'NewsController@destroy');

Route::middleware('auth')->get('/mails/templates', 'MailsController@templates')->name('mail.templates');
Route::middleware('auth')->get('/mails/{template}/preview', 'MailsController@preview')->name('mail.preview');
Route::middleware('auth')->get('/mails/{template}/test', 'MailsController@test')->name('mail.test');
Route::middleware('auth')->get('/mails/{template}/edit', 'MailsController@edit')->name('mail.edit');
Route::middleware('auth')->post('/mails/{template}/edit', 'MailsController@update')->name('mail.update');
Route::middleware('auth')->get('/mails/dispatch-announcement', 'MailsController@dispatchAnnouncement')->name('mail.dispatch-announcement');