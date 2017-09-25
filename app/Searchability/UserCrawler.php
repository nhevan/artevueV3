<?php

namespace App\Searchability;

use App\User;
use Illuminate\Http\Request;
use App\Searchability\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UserCrawler extends Crawler
{
	public function setUp()
	{
		$this->models = new User();
	}	
}
