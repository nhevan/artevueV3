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
}
