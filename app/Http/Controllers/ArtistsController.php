<?php

namespace App\Http\Controllers;

use App\Artist;
use Illuminate\Http\Request;
use Acme\Transformers\PostTransformer;
use Acme\Transformers\ArtistTransformer;

class ArtistsController extends ApiController
{
	protected $artist;
	protected $request;

	public function __construct(Artist $artist, Request $request)
	{
		$this->artist = $artist;
		$this->request = $request;
	}

	/**
	 * fetches paginated list of all the artists
	 * @return [type] [description]
	 */
	public function index()
	{
		$artists = $this->artist->all()->toArray();

		return $this->respondAsTransformattedArray($artists, new ArtistTransformer);
	}

	/**
	 * returns all the posts by a given artist
	 * @param  [type] $artist_id [description]
	 * @return [type]            [description]
	 */
	public function posts($artist_id)
	{
		$artist = $this->artist->find($artist_id);
        if (!$artist) {
            return $this->responseNotFound('Artist does not exist.');
        }
        $this->artist = $artist;

        $posts = $this->artist->posts()->with('artist', 'owner')->latest()->paginate(20);

        return $this->respondWithPagination($posts, new PostTransformer);
	}
    
}
