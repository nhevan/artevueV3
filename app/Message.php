<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    public function receiver()
    {
    	return $this->belongsTo('App\User', 'receiver_id');
    }
}
