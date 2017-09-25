<?php

namespace App\Searchability;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PostCrawler
{
    protected $field_mapping = [
    	'minimum_price' => [
    		'original_name' => 'price',
    		'condition' => '>='
    	]
    ];

	protected $request;
	protected $posts;


	public function __construct(Request $request, Post $posts)
	{
		$this->request = $request;

		$this->posts = $posts;
	}


    public function search()
    {
    	$posts = new Post();

    	$posts = $this->searchByAllGivenParameters();

    	$posts = $posts->get();

    	return $posts;
    }

    public function searchByAllGivenParameters()
    {
    	$posts = new Post();

    	// $fields = DB::getSchemaBuilder()->getColumnListing('posts');

    	$search_paramaters = $this->request->all();

    	foreach ($search_paramaters as $column_name => $value) {

    		if($this->isAlias($column_name)){
    			// dd($this->getCondition($column_name));
    			$posts = $posts->where($this->getOriginalColumn($column_name), $this->getCondition($column_name), $value);
    		}else{
	    		$posts = $posts->where($column_name, 'like', '%'.$value.'%');
    		}
    	}

    	return $posts;
    }

    public function isAlias($column_name)
    {
    	if (isset($this->field_mapping[$column_name]['original_name'])) {
    		return true;
    	}

    	return false;
    }

    public function getOriginalColumn($column_name)
    {
    	return $this->field_mapping[$column_name]['original_name'];
    }

    public function getCondition($column_name)
    {
    	return $this->field_mapping[$column_name]['condition'];
    }

}
