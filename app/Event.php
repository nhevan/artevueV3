<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'headline', 'description', 'location', 'image', 'url', 'start_date', 'end_date', 'publish_date', 'city'
    ];
}
