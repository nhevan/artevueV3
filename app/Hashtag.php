<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $fillable = [
    	'hashtag', 'use_count'
    ];

    public function posts()
    {
    	return $this->belongsToMany('App\Post');
    }
}
