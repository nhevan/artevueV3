<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMetadata extends Model
{
    protected $table = 'users_metadata';

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function latest3Posts()
    {
    	return $this->hasMany('App\Post', 'owner_id', 'user_id')->latest()->take(3);
    }
}
