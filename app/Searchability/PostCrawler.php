<?php

namespace App\Searchability;

use App\Post;
use App\User;
use Illuminate\Http\Request;
use App\Searchability\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PostCrawler extends Crawler
{
	protected $field_mapping = [
    	'minimum_price' => [
    		'field' => 'price',
    		'condition' => '>='
    	],
    	'maximum_price' => [
    		'field' => 'price',
    		'condition' => '<='	
    	],
    	'owner_username'
    ];
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

	public function whereOwnerUsername($value)
	{
		$owner = User::where('username', 'LIKE', $value)->get()->pluck('id')->toArray();

		return $this->model = $this->model->whereIn('owner_id', $owner);
	}
}
