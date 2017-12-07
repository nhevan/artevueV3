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

Route::get('/manage-tokens', function() {
    return view('tokens');
})->middleware('auth');

Route::middleware(['auth','admin'])->get('/dashboard', 'DashboardController@dashboard')->name('dashboard');

Route::middleware(['auth', 'admin'])->get('/settings', 'SettingsController@index')->name('settings.index');
Route::middleware(['auth', 'admin'])->get('/settings/edit/app', 'SettingsController@editAppSettings')->name('settings.edit-app-settings');
Route::middleware(['auth', 'admin'])->post('/settings/edit/app', 'SettingsController@editAppSettings')->name('settings.edit-app-settings');
Route::middleware(['auth', 'admin'])->get('/settings/edit/weight', 'SettingsController@editWeightSettings')->name('settings.edit-weight-settings');
Route::middleware(['auth', 'admin'])->post('/settings/edit/weight', 'SettingsController@editWeightSettings')->name('settings.edit-weight-settings');

Route::middleware(['auth', 'admin'])->get('/users/{type?}', 'UsersController@index')->name('users.index');
Route::middleware(['auth', 'admin'])->get('/user/{user}', 'UsersController@show')->name('users.show');
Route::middleware(['auth', 'admin'])->delete('/users/{user}', 'UsersController@destroy')->name('users.destroy');
Route::middleware(['auth', 'admin'])->get('/user/{user}/edit-username', 'UsersController@editUsername')->name('user.edit-username-form');
Route::middleware(['auth', 'admin'])->patch('/user/{user}/edit-username', 'UsersController@editUsername')->name('user.edit-username');
Route::middleware(['auth', 'admin'])->post('/search-users', 'UsersController@searchUser')->name('users.search');
Route::middleware(['auth', 'admin'])->get('/user-posts/{user_id}', 'UsersController@viewUserPosts')->name('users.posts');
Route::middleware(['auth', 'admin'])->get('/send-reset-password-email/{user}', 'UsersController@resetPassword')->name('user.reset-password');
Route::middleware(['auth', 'admin'])->get('/change-password-form/{user}', 'UsersController@showSetPasswordForm')->name('user.change-password-form');
Route::middleware(['auth', 'admin'])->post('/change-password/{user}', 'UsersController@setPassword')->name('user.change-password');
Route::middleware(['auth', 'admin'])->get('/send-notification-form/{user?}', 'UsersController@sendNotification')->name('user.send-notification-form');
Route::middleware(['auth', 'admin'])->post('/send-notification/{user?}', 'UsersController@sendNotification')->name('user.send-notification');

Route::middleware(['auth', 'admin'])->get('/posts', 'PostsController@indexWeb')->name('posts.index');
Route::middleware(['auth', 'admin'])->get('/posts/trending', 'DiscoverPostsController@discoverPosts')->name('posts.trending');
Route::middleware(['auth', 'admin'])->get('/posts/arteprize', 'PostsController@artePrizePosts')->name('posts.arteprize');
Route::middleware(['auth', 'admin'])->get('/posts/buy', 'PostsController@onSalePosts')->name('posts.buy');
Route::middleware(['auth', 'admin'])->get('/posts/curators-choice', 'PostsController@artevueSelectedPosts')->name('posts.curators-choice');
Route::middleware(['auth', 'admin'])->get('/post/{post}', 'PostsController@showWeb')->name('posts.show');
Route::middleware(['auth', 'admin'])->get('/post/edit/{post}', 'PostsController@showEditForm')->name('posts.edit-form');
Route::middleware(['auth', 'admin'])->post('/post/{post}', 'PostsController@editWeb')->name('posts.edit');
Route::middleware(['auth', 'admin'])->delete('/post/{post}', 'PostsController@delete')->name('posts.destroy');
Route::middleware(['auth', 'admin'])->patch('/post/{post}/swap-discoverability', 'PostsController@swapDiscoverability')->name('posts.swapDiscoverability');
Route::middleware(['auth', 'admin'])->patch('/post/{post}/swap-sale-status', 'PostsController@swapSaleStatus')->name('posts.swapSaleStatus');
Route::middleware(['auth', 'admin'])->patch('/post/{post}/swap-curators-selection-status', 'PostsController@swapCuratorSelectionStatus')->name('posts.swapCuratorsSelectionStatus');

Route::middleware(['auth', 'admin'])->get('/events', 'EventsController@all');
Route::middleware(['auth', 'admin'])->get('/events/show-create-form', 'EventsController@showAddForm');
Route::middleware(['auth', 'admin'])->post('/events', 'EventsController@store');
Route::middleware(['auth', 'admin'])->get('/events/{event}', 'EventsController@show');
Route::middleware(['auth', 'admin'])->get('/events/edit/{event}', 'EventsController@showEditForm');
Route::middleware(['auth', 'admin'])->post('/events/edit/{event}', 'EventsController@edit');
Route::middleware(['auth', 'admin'])->get('/events/delete/{event}', 'EventsController@destroy');

Route::middleware(['auth', 'admin'])->get('/news', 'NewsController@all');
Route::middleware(['auth', 'admin'])->get('/news/show-create-form', 'NewsController@showAddForm');
Route::middleware(['auth', 'admin'])->post('/news', 'NewsController@store');
Route::middleware(['auth', 'admin'])->get('/news/{news}', 'NewsController@show');
Route::middleware(['auth', 'admin'])->get('/news/edit/{news}', 'NewsController@showEditForm');
Route::middleware(['auth', 'admin'])->post('/news/edit/{news}', 'NewsController@edit');
Route::middleware(['auth', 'admin'])->get('/news/delete/{news}', 'NewsController@destroy');

Route::middleware(['auth', 'admin'])->get('/mails/templates', 'MailsController@templates')->name('mail.templates');
Route::middleware(['auth', 'admin'])->get('/mails/{template}/preview', 'MailsController@preview')->name('mail.preview');
Route::middleware(['auth', 'admin'])->get('/mails/{template}/test', 'MailsController@test')->name('mail.test');
Route::middleware(['auth', 'admin'])->get('/mails/{template}/edit', 'MailsController@edit')->name('mail.edit');
Route::middleware(['auth', 'admin'])->post('/mails/{template}/edit', 'MailsController@update')->name('mail.update');
Route::middleware(['auth', 'admin'])->get('/mails/dispatch-announcement', 'MailsController@dispatchAnnouncement')->name('mail.dispatch-announcement');