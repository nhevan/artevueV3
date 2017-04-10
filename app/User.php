<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public function metadata()
    {
        return $this->hasOne('App\UserMetadata');
    }

    public function userType()
    {
        return $this->belongsTo('App\UserType');
    }

    public function artPreferences()
    {
        return $this->belongsToMany('App\ArtPreference');
    }

    public function artInteractions()
    {
        return $this->belongsToMany('App\ArtInteraction');
    }

    public function artTypes()
    {
        return $this->belongsToMany('App\ArtType');
    }

    public function followers()
    {
        return $this->hasMany('App\Follower');
    }

    public function following()
    {
        return $this->hasMany('App\Follower', 'follower_id')->where('is_still_following', 1);
    }

    public function blockedUsers()
    {
        return $this->hasMany('App\BlockedUser');
    }

    public function reportedUsers()
    {
        return $this->hasMany('App\ReportedUser');
    }

    public function sentMessages()
    {
        return $this->hasMany('App\Message', 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany('App\Message', 'receiver_id');
    }

    public function posts()
    {
        return $this->hasMany('App\Post', 'owner_id');
    }

    public function pins()
    {
        return $this->hasMany('App\Pin');
    }

    public function tags()
    {
        return $this->hasMany('App\Tag');
    }

    /**
     * allows users to login using username
     * @param  [type] $username [description]
     * @return [bool]           [description]
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'user_type_id', 'profile_picture', 'gcm_registration_key'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
