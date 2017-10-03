<?php

namespace App\Http\Controllers;

use App\User;
use App\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends ApiController
{
    protected $user;
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * get a collection of ids of all my followers
     * @return object eloquent collection object
     */
    public function getMyFollowersIds()
    {
        if ($this->user) {
        	return  $this->user->following->pluck('user_id');
        }

        return collect([]);
    }

    /**
     * exclude myself from a colleciton of users ids
     * @param  collection $users_collection [description]
     * @return array        [description]
     */
    public function excludeMyself($users_collection)
    {
    	$users_collection = $users_collection->reject(function ($id) {
		    return $id == $this->user->id;
		});

        return $users_collection->all();
    }

    /**
     * include myself from a colleciton of users ids
     * @param  collection $users_collection [description]
     * @return array        [description]
     */
    public function includeMyself($users_collection)
    {
        if ($users_collection && $this->user) {
            $users_collection = $users_collection->push($this->user->id);

            return $users_collection->all();
        }
        
        if ($users_collection) {
            return $users_collection->all();
        }

        if ($this->user) {
            return [ $this->user->id ];
        }

        return [];
    }
}
