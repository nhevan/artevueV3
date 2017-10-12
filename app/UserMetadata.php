<?php

namespace App;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;

class UserMetadata extends Model
{
	use Sortable;
    protected $table = 'users_metadata';

    /**
     * defines an array of fields that are sortable
     * @var [type]
     */
    public $sortable = [
    	'post_count', 'like_count', 'follower_count'
    ];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function latest3Posts()
    {
    	return $this->hasMany('App\Post', 'owner_id', 'user_id')->latest()->take(3);
    }
}
