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
