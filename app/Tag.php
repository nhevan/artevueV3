<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
    	'user_id', 'post_id', 'username', 'x', 'y'
    ];
}
