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
		$this->model = new User();
	}
	public function defaultConditions()
	{
		$this->model = $this->model->whereNotIn('user_type_id', [1, 2]);

		return $this;
	}

	public function whereUserTypeId($query_string)
	{
		$is_multiple = strpos($query_string, ',');

		if (!$is_multiple) {
			$user_types = [ $query_string ];
		}
		if ($is_multiple) {
			$user_types = explode(',', $query_string);
		}

		return $this->model = $this->model->whereIn('user_type_id', $user_types);
	}
}
