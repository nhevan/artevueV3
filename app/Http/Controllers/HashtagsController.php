<?php

namespace App\Http\Controllers;

use App\Post;
use App\Hashtag;
use App\PostHashtag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Acme\Transformers\PostTransformer;
use Acme\Transformers\HashtagTransformer;

class HashtagsController extends ApiController
{
	/**
	 * fetches the top 9 posts for a given hashtag
	 * @param  [type] $hashtag [description]
	 * @return [type]          [description]
	 */
    public function topPosts($hashtag_title)
    {
    	$hashtag_title = '#'.$hashtag_title;
    	$hashtag = Hashtag::where('hashtag', $hashtag_title)->first();

    	if (!$hashtag) {
    		return $this->responseNotFound('Hashtag does not exist.');
    	}
        $posts = $hashtag->posts()->select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))->orderBy('total_count', 'DESC')->with('artist', 'owner', 'tags')->limit(9)->paginate(9);

        return $this->respondWithPagination($posts, New PostTransformer);
    }

    /**
     * returns a paginated list of all the latest posts for a given hashtag
     * @param  [type] $hashtag_title [description]
     * @return [type]                [description]
     */
    public function latestPosts($hashtag_title)
    {
        $limit = 20;
    	$hashtag_title = '#'.$hashtag_title;
    	$hashtag = Hashtag::where('hashtag', $hashtag_title)->first();

    	if (!$hashtag) {
    		return $this->responseNotFound('Hashtag does not exist.');
    	}
    	$top_posts = $hashtag->posts()->select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))->orderBy('total_count', 'DESC')->limit(9)->get()->pluck('post_id')->toArray();

    	$posts = $hashtag->posts()->whereNotIn('post_id', $top_posts)->with('artist', 'owner', 'tags')->latest()->paginate($limit);

    	return $this->respondWithPagination($posts, New PostTransformer);
    }

    /**
     * searches for a hashtag using a given search string
     * @param  Request $request [description]
     * @return [type]                [description]
     */
    public function searchHashtag(Request $request)
    {
        $limit = 100;
    	$rules = [
            'search_string' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $search_string = $request->search_string;

        $hashtags = Hashtag::where('hashtag', 'like', '%'.$search_string.'%')->orderBy('use_count', 'DESC')->paginate($limit);

        return $this->respondWithPagination($hashtags, new HashtagTransformer);
    }

    /**
     * returns a list of post that contains the given hashtag (should be depcrecated, instead use top post method)
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getHashtagByName(Request $request)
    {
        $rules = [
            'hashtag' => 'required',
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        $hashtag = Hashtag::where('hashtag', 'like', '%'.$request->hashtag.'%')->first();
        if ($hashtag) {
            $hashtag_title = ltrim($hashtag->hashtag, '#');

            return $this->topPosts($hashtag_title);
        }
        return $this->responseNotFound('No such hashtag exists.');
    }
}
