<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
	protected $table = 'user_types';

	public function users()
    {
        return $this->hasMany('App\User');
    }
}
