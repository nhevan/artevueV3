<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
    	'title'
    ];

    public function posts()
    {
    	return $this->hasMany('App\Post');
    }
}
