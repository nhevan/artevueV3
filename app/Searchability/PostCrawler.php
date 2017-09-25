<?php

namespace App\Searchability;

use App\Post;
use Illuminate\Http\Request;
use App\Searchability\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PostCrawler extends Crawler
{
	protected $field_mapping = [
    	'minimum_price' => [
    		'original_name' => 'price',
    		'condition' => '>='
    	],
    	'maximum_price' => [
    		'original_name' => 'price'
    	]
    ];
    public $rules = [
		            'price' => 'digits_between:0,99999999'
		        ];

	public function setUp()
	{
		$this->models = new Post();
	}

	public function whereMaximumPrice($value)
	{
		return $this->models = $this->models->where('price', '<=', $value);
	}
}
