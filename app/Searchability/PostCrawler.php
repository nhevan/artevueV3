<?php

namespace App\Searchability;

use App\Post;
use App\User;
use App\Artist;
use Illuminate\Http\Request;
use App\Searchability\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PostCrawler extends Crawler
{
    public $rules = [
		            'price' => 'digits_between:0,99999999'
		        ];

	public function setUp()
	{
		$this->model = new Post();
	}

	public function defaultConditions()
	{
		$this->model = $this->model->where('is_public', 1);

		return $this;
	}

	public function whereOwnerUsername($query_string)
	{
		$owner = User::where('username', 'LIKE', '%'.$query_string.'%')->get()->pluck('id')->toArray();

		return $this->model = $this->model->whereIn('owner_id', $owner);
	}

	public function whereOwnerName($query_string)
	{
		$owner = User::where('name', 'LIKE', '%'.$query_string.'%')->get()->pluck('id')->toArray();

		return $this->model = $this->model->whereIn('owner_id', $owner);
	}

	public function whereArtist($query_string)
	{
		$artists = Artist::where('title', 'LIKE', '%'.$query_string.'%')->get()->pluck('id')->toArray();

		return $this->model = $this->model->whereIn('artist_id', $artists);
	}

	public function whereHashtag($query_string)
	{
		$hashtag = urldecode($query_string);

		if($hashtag[0] == "#"){
			return $this->model = $this->model->where('description', 'LIKE', '%'.$hashtag.'%');
		}

		return $this->model = $this->model->where('description', 'LIKE', '%#'.$hashtag.'%');

	}
}
