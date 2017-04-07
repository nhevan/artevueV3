<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
    	'image', 'description', 'hashtags', 'aspect_ratio', 'price', 'has_buy_btn', 'google_place_id', 'address', 'address_title', 'is_public', 'is_gallery', 'artist_id'
    ];

    public function owner()
    {
        return $this->belongsTo('App\User');
    }
    public function artist()
    {
        return $this->belongsTo('App\Artist');
    }
    public function tags()
    {
        return $this->hasMany('App\Tag');
    }
    public function likes()
    {
        return $this->hasMany('App\Like');
    }
    public function comments()
    {
        return $this->hasMany('app\Comment');
    }
}
