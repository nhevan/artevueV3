<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserArtType extends Model
{
    protected $table = 'art_type_user';

    protected $fillable = [
    	'user_id', 'art_type_id'
    ];

}
