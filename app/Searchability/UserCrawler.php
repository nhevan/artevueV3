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
		$this->model = $this->model->whereNotIn('user_type_id', [1, 2])
								   ->join('users_metadata', 'users.id', '=', 'users_metadata.user_id')
								   ->select('users.*', DB::raw('`users_metadata`.`like_count`+`users_metadata`.`pin_count`+`users_metadata`.`comment_count`+`users_metadata`.`message_count`+`users_metadata`.`follower_count`+`users_metadata`.`following_count`+`users_metadata`.`post_count`+`users_metadata`.`tagged_count` as total_count'))
								   ->orderBy('total_count', 'desc');
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
