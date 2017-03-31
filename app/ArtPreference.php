<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtPreference extends Model
{
    protected $table = 'art_preferences';

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
