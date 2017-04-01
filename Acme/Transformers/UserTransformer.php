<?php

namespace Acme\Transformers;

use App\User;

/**
*
*/
class UserTransformer extends Transformer
{
    public function transform($user)
    {
    	$user['is_following'] = 0;
    	$user['is_blocked'] = 0;

    	$artPreferences = $this->transformArtPreferences($user);
    	$artInteractions = $this->transformArtInteractions($user);
        $artTypes = $this->transformArtTypes($user);

        return [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'sex' => $user['sex'],
                'dob' => $user['dob'],
                'phone' => $user['phone'],
                'biography' => $user['biography'],
                'website' => $user['website'],

                'device_token' => $user['device_token'],
                'gcm_registration_key' => $user['gcm_registration_key'],
                'social_media' => $user['social_media'],
                'social_media_uid' => $user['social_media_uid'],
                'social_media_access_token' => $user['social_media_access_token'],
                'profile_picture' => $user['profile_picture'],
                
                'user_type' => $user['usertype']['title'],

                'gallery_name' => $user['metadata']['gallery_name'],
                'gallery_description' => $user['metadata']['gallery_description'],
                'museum_name' => $user['metadata']['museum_name'],
                'foundation_name' => $user['metadata']['foundation_name'],
                'is_notification_enabled' => $user['metadata']['is_notification_enabled'],
            	'is_account_private' => $user['metadata']['is_account_private'],
            	'is_save_to_phone' => $user['metadata']['is_save_to_phone'],

                'post_count' => $user['metadata']['post_count'],
                'comment_count' => $user['metadata']['comment_count'],
                'like_count' => $user['metadata']['like_count'],
                'pin_count' => $user['metadata']['pin_count'],
                'message_count' => $user['metadata']['message_count'],
                'follower_count' => $user['metadata']['follower_count'],
                'following_count' => $user['metadata']['following_count'],
                'tagged_count' => $user['metadata']['tagged_count'],

                'art_preferences' => $artPreferences,
                'art_interactions' => $artInteractions,
                'art_types' => $artTypes,

            	'is_following' => $user['is_following'],
            	'is_blocked' => $user['is_blocked'],
            ];
    }

    /**
     * transforms the Art Preferences associated with the User
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function transformArtPreferences($user)
	{
		$preferences = $user['artPreferences'];
    	$pref_arrays = [];
    	foreach ($preferences->toArray() as $pref) {
    		$tmp = [
    			'id' => $pref['id'],
    			'title' => $pref['title'],
    		];
    		array_push($pref_arrays, $tmp);
    	}
    	return $pref_arrays;
	}

	/**
     * transforms the Art Interactions associated with the User
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function transformArtInteractions($user)
	{
		$interactions = $user['artInteractions'];
    	$interactions_array = [];
    	foreach ($interactions->toArray() as $interaction) {
    		$tmp = [
    			'id' => $interaction['id'],
    			'title' => $interaction['title'],
    		];
    		array_push($interactions_array, $tmp);
    	}
    	return $interactions_array;
	}

    /**
     * transforms the Art Types associated with the User
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function transformArtTypes($user)
    {
        $art_types = $user['artTypes'];
        $art_type_array = [];
        foreach ($art_types->toArray() as $art_type) {
            $tmp = [
                'id' => $art_type['id'],
                'title' => $art_type['title'],
            ];
            array_push($art_type_array, $tmp);
        }
        return $art_type_array;
    }
	
}