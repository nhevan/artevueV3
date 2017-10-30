<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['name', 'description', 'email', 'website', 'is_private'];

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

    /**
     * represents the owner of the gallery
     * @return [type] [description]
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * returns all the pins associated with the gallery
     * @return [type] [description]
     */
    public function pins()
    {
        return $this->hasMany('App\Pin');
    }

    /**
     * Scope a query to only include public galleries only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', 0);
    }

}
