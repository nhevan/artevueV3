<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, Sortable;

    protected $casts = ['user_type_id' => 'integer'];

    /**
     * defines an array of fields that are sortable
     * @var [type]
     */
    public $sortable = [
        'id', 'created_at', 'updated_at', 'name', 'username'
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password', 'username', 'profile_picture', 'name', 'gcm_registration_key', 'sex', 'website', 'biography', 'email', 'user_type_id', 'phone', 'location', 'latitude', 'longitude'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($user){
            $user->posts->each->delete();
        });
    }

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

    public function galleries()
    {
        return $this->hasMany('App\Gallery')->orderBy('sequence');
    }

    public function pins()
    {
        return $this->hasMany('App\Pin');
    }

    public function tags()
    {
        return $this->hasMany('App\Tag');
    }

    public function scopeTop($query)
    {
        return $query->join('users_metadata', 'users.id', '=', 'users_metadata.user_id')
                     ->select('users.*', DB::raw('`users_metadata`.`like_count`+`users_metadata`.`pin_count`+`users_metadata`.`comment_count`+`users_metadata`.`message_count`+`users_metadata`.`follower_count`+`users_metadata`.`following_count`+`users_metadata`.`post_count`+`users_metadata`.`tagged_count` as total_count'))
                     ->orderBy('total_count', 'desc');
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

    public function routeNotificationForSlack()
    {
        return "https://hooks.slack.com/services/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";
    }

    public function isPrivate()
    {
        return !! $this->metadata->is_account_private;
    }

    public function isAdmin()
    {
        if($this->user_type_id <= 2) return true;
    }
}
