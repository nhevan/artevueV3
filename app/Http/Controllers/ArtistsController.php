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

        $posts = $this->artist->posts()->with('artist', 'owner', 'tags')->latest()->paginate(20);

        return $this->respondWithPagination($posts, new PostTransformer);
	}

	/**
	 * returns posts by a given artist name
	 * @return Response [description]
	 */
	public function getPostsByArtistName()
	{
		$rules = [
            'name' => 'required',
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $artist = $this->artist->where('title', $this->request->name)->first();
        if ($artist) {
            return $this->posts($artist->id);
        }
        return $this->responseNotFound('No such artist exists.');
	}
    
    /**
     * searches users with a possible given search string
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function searchArtist(Request $request)
    {
    	$rules = [
            'search_string' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $this->request = $request;
        $search_string = $this->request->search_string;

        $matching_artists = $this->artist->where('title', 'like', '%'.$search_string.'%')->get()->toArray();
        return $this->respondAsTransformattedArray($matching_artists, new ArtistTransformer);
    }
}
