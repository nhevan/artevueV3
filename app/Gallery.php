<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['name', 'description', 'email', 'website'];

    /**
	 * the booting method of the model
	 */
	protected static function boot()
	{
		parent::boot();

		static::deleting(function($gallery){
			$gallery->pins->each->delete();
		});
	}

    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function pins()
    {
        return $this->hasMany('App\Pin');
    }

}
