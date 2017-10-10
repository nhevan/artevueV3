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

Route::get('/send-email-to-all', 'UsersController@sendEmailRegardingIssue');

Auth::routes();

Route::get('/home', 'HomeController@index');
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

Route::get('/users', 'UsersController@index')->middleware('auth')->name('users.index');
Route::get('/users/{user}', 'UsersController@show')->middleware('auth')->name('users.show');
Route::delete('/users/{user}', 'UsersController@destroy')->middleware('auth')->name('users.destroy');
Route::post('/search-users', 'UsersController@searchUser')->middleware('auth')->name('users.search');
Route::get('/user-posts/{user_id}', 'UsersController@viewUserPosts')->middleware('auth')->name('users.posts');
Route::middleware('auth')->get('/send-reset-password-email/{user}', 'UsersController@resetPassword')->name('user.reset-password');
Route::middleware('auth')->get('/change-password-form/{user}', 'UsersController@showSetPasswordForm')->name('user.change-password-form');
Route::middleware('auth')->post('/change-password/{user}', 'UsersController@setPassword')->name('user.change-password');

Route::get('/posts', 'PostsController@indexWeb')->middleware('auth')->name('posts.index');
Route::get('/posts/{post}', 'PostsController@showWeb')->middleware('auth')->name('posts.show');
Route::get('/post/edit/{post}', 'PostsController@showEditForm')->middleware('auth')->name('posts.edit-form');
Route::post('/post/{post}', 'PostsController@editWeb')->middleware('auth')->name('posts.edit');
Route::delete('/posts/{post}', 'PostsController@delete')->middleware('auth')->name('posts.destroy');
Route::patch('/posts/{post}/swap-discoverability', 'PostsController@swapDiscoverability')->middleware('auth')->name('posts.swapDiscoverability');

Route::get('/events', 'EventsController@all')->middleware('auth');
Route::get('/events/show-create-form', 'EventsController@showAddForm')->middleware('auth');
Route::post('/events', 'EventsController@store')->middleware('auth');
Route::get('/events/{event}', 'EventsController@show')->middleware('auth');
Route::get('/events/edit/{event}', 'EventsController@showEditForm')->middleware('auth');
Route::post('/events/edit/{event}', 'EventsController@edit')->middleware('auth');
Route::get('/events/delete/{event}', 'EventsController@destroy')->middleware('auth');

Route::get('/news', 'NewsController@all')->middleware('auth');
Route::get('/news/show-create-form', 'NewsController@showAddForm')->middleware('auth');
Route::post('/news', 'NewsController@store')->middleware('auth');
Route::get('/news/{news}', 'NewsController@show')->middleware('auth');
Route::get('/news/edit/{news}', 'NewsController@showEditForm')->middleware('auth');
Route::post('/news/edit/{news}', 'NewsController@edit')->middleware('auth');
Route::get('/news/delete/{news}', 'NewsController@destroy')->middleware('auth');
