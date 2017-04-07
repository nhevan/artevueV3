<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
    	'user_id', 'post_id', 'comment'
    ];

    public function commentor()
    {
    	return $this->belongsTo('App\User', 'user_id');
    }
}
