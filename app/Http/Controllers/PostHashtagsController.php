<?php

namespace App\Http\Controllers;

use App\Post;
use App\Hashtag;
use App\PostHashtag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Acme\Transformers\PostTransformer;

class PostHashtagsController extends ApiController
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

    	$posts = $hashtag->posts()->select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))->orderBy('total_count', 'DESC')->with('artist', 'owner')->limit(9)->get()->toArray();

    	return $this->respondAsTransformattedArray($posts, New PostTransformer);
    }

    /**
     * returns a paginated list of all the latest posts for a given hashtag
     * @param  [type] $hashtag_title [description]
     * @return [type]                [description]
     */
    public function latestPosts($hashtag_title)
    {
    	$hashtag_title = '#'.$hashtag_title;
    	$hashtag = Hashtag::where('hashtag', $hashtag_title)->first();

    	if (!$hashtag) {
    		return $this->responseNotFound('Hashtag does not exist.');
    	}
    	$top_posts = $hashtag->posts()->select(DB::raw("*, (`like_count`+`pin_count`+`comment_count`) as total_count"))->orderBy('total_count', 'DESC')->limit(9)->get()->pluck('post_id')->toArray();

    	$posts = $hashtag->posts()->whereNotIn('post_id', $top_posts)->with('artist', 'owner')->latest()->paginate(5);

    	return $this->respondWithPagination($posts, New PostTransformer);
    }
}
