<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentHashtag extends Model
{
	protected $table = 'comment_hashtag';
	
    protected $fillable = [
    	'comment_id', 'hashtag_id'
    ];
}
