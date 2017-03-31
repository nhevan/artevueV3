<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtInteraction extends Model
{
    protected $table = 'art_interactions';

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
