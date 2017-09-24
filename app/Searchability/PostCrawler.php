<?php

namespace App\Searchability;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PostCrawler
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

    protected $field_mapping = [
    	'google_place_id' => 'place_id'
    ];

    public function search()
    {
    	$posts = new Post();
    	if ($this->request->minimum_price) {
    		$posts = $posts->where('price', '>=', $this->request->minimum_price);
    	}

    	if ($this->request->description) {
    		$posts = $posts->where('description', 'like', '%'.$this->request->description.'%');
    	}

    	$posts = $posts->get();

    	return $posts;
    }


}
