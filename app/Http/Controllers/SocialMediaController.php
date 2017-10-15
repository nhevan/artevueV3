<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Traits\UserSwissKnife;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\UserSearchTransformer;

class SocialMediaController extends ApiController
{
	use UserSwissKnife;

	/**
	 * returns a list of users that are using our system and is also a frind of the currently logged in user
	 * @return [type] [description]
	 */
    public function findFriends()
    {
    	if ($this->socialMediaIsFacebook()) {
    		return $this->getFacebookFriends();
    	}
    	if ($this->socialMediaIsInstagram()) {
    		return $this->getInstagramFriends();
    	}

		return $this->responseNotFound('This user has not signed up using any social media.');
    }

    /**
     * returns true if the current user is logged in using facebook, otherwise false
     * @return [type] [description]
     */
    public function socialMediaIsFacebook()
    {
    	if (Auth::user()->social_media === 'facebook') {
    		return true;
    	}
    }

    /**
     * returns all common friends of the current user from facebook
     * @return [type] [description]
     */
    public function getFacebookFriends()
    {
    	$user_social_uids = $this->fetchUserFriendsSocialIdsFromFacebook(Auth::user()->social_media_access_token);

    	$users = User::where('social_media', 'facebook')->whereIn('social_media_uid', $user_social_uids )->paginate(30);

    	$userSearchTransformer = new UserSearchTransformer;
    	return $this->respondWithPagination($users, $userSearchTransformer );
    }

    /**
     * returns true if the current user is logged in using instagram, otherwise false
     * @return [type] [description]
     */
    public function socialMediaIsInstagram()
    {
    	if (Auth::user()->social_media === 'instagram') {
    		return true;
    	}
    }
}
