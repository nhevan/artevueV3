<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostHashtag extends Model
{
    protected $table = 'hashtag_post';

    protected $fillable = [
    	'post_id', 'hashtag_id'
    ];
}
