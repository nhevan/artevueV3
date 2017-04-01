<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtType extends Model
{
    protected $table = 'art_types';

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
