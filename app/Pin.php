<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $fillable = [
    	'post_id', 'user_id', 'sequence', 'gallery_id'
    ];
    protected $with = ['post'];

    public function post()
    {
        return $this->belongsTo('App\Post');
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
