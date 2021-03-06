<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $casts = [ 'is_gallery_item' => 'integer', 'price' => 'float' ];

    protected $fillable = [
    	'image', 'description', 'hashtags', 'aspect_ratio', 'price', 'has_buy_btn', 'google_place_id', 'address', 'address_title', 'is_public', 'is_gallery_item', 'artist_id', 'post_art_type_id'
    ];

    public function owner()
    {
        return $this->belongsTo('App\User');
    }
    public function artist()
    {
        return $this->belongsTo('App\Artist');
    }
    public function tags()
    {
        return $this->hasMany('App\Tag');
    }
    public function likes()
    {
        return $this->hasMany('App\Like');
    }
    public function comments()
    {
        return $this->hasMany('App\Comment');
    }
    public function type()
    {
        return $this->belongsTo('App\PostArtType', 'post_art_type_id');
    }

    /**
     * Scope a query to only include arteprize posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArteprizePosts($query)
    {
        $artePrizeHashtag = '#arteprize2017';
        return $query->where('description', 'LIKE', '%'.$artePrizeHashtag.'%')->where('is_undiscoverable', 0);
    }

    /**
     * Scope a query to only include artevue selected posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArtevueSelectedPosts($query)
    {
        return $query->where('is_selected_by_artevue', 1)->where('is_undiscoverable', 0);
    }

    /**
     * Scope a query to only include posts selected for sale by Artevue Team.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelectedSalePosts($query)
    {
        return $query->where('is_selected_for_sale', 1)->where('is_undiscoverable', 0);
    }

    /**
     * Scope a query to only include posts with a buy button.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSalePosts($query)
    {
        return $query->where('has_buy_btn', 1)->where('is_undiscoverable', 0);
    }

    /**
     * Scope a query to only include trending posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTrending($query)
    {
        return $query->select(DB::raw("*, (`like_count`+`comment_count`) as trending_count"))
            ->where('is_undiscoverable', false)
            ->orderByDesc('trending_count');
    }


    /**
     * swaps the discoverability of a post
     * @return [type] [description]
     */
    public function swapDiscoverability()
    {
        if ($this->is_undiscoverable) {
            return $this->makeDiscoverable();
        }
        
        return $this->makeUndiscoverable();
    }

    /**
     * makes a post discoverable
     * @return [type] [description]
     */
    public function makeDiscoverable()
    {
        $this->is_undiscoverable = false;
        return $this->save();
    }

    /**
     * makes a post undiscoverable
     * @return [type] [description]
     */
    public function makeUndiscoverable()
    {
        $this->is_undiscoverable = true;
        return $this->save();
    }

    /**
     * swaps the sale status of a post
     * @return [type] [description]
     */
    public function swapSaleStatus()
    {
        if ($this->is_selected_for_sale) {
            return $this->putOffSale();
        }
        
        return $this->putOnSale();
    }

    /**
     * takes off a post from sale
     * @return [type] [description]
     */
    public function putOffSale()
    {
        $this->is_selected_for_sale = false;
        return $this->save();
    }

    /**
     * sets a post for sale
     * @return [type] [description]
     */
    public function putOnSale()
    {
        $this->is_selected_for_sale = true;
        return $this->save();
    }

    /**
     * swaps the curators selection status of a post
     * @return [type] [description]
     */
    public function swapCuratorSelectionStatus()
    {
        if ($this->is_selected_by_artevue) {
            return $this->putOffCuratorsSelection();
        }
        
        return $this->putOnCuratorsSelection();
    }

    /**
     * takes off a post from Curtor's selection
     * @return [type] [description]
     */
    public function putOffCuratorsSelection()
    {
        $this->is_selected_by_artevue = false;
        return $this->save();
    }

    /**
     * marks a post as Curtor's selection
     * @return [type] [description]
     */
    public function putOnCuratorsSelection()
    {
        $this->is_selected_by_artevue = true;
        return $this->save();
    }
}
