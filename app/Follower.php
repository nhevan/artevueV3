<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $table = 'followers';

    public function followerDetail()
    {
        return $this->belongsTo('App\User', 'follower_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'follower_id'
    ];
}
