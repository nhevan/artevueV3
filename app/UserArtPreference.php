<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserArtPreference extends Model
{
    protected $table = 'art_preference_user';

    protected $fillable = [
    	'user_id', 'art_preference_id'
    ];
}
