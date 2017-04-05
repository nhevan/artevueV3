<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $fillable = [
    	'post_id', 'user_id', 'sequence'
    ];
}
