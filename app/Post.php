<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    public function owner()
    {
        return $this->belongsTo('App\User');
    }
    public function artist()
    {
        return $this->belongsTo('App\Artist');
    }
}
