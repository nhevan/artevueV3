<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SocialMediaFunctionalitiesTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * @test
     * users can search their common fb friends that are using our system
     */
    public function users_can_search_their_common_fb_friends_that_are_using_our_system()
    {
        //arrange
        $user = factory('App\User')->create([
        	'social_media' => 'facebook',
        	'social_media_uid' => '1555685954491783',
        	'social_media_access_token' => 'EAAEKXpBRuKwBAKvk8dmNNG3PTPQJUb7ghbJh9ie68w1wtPWjrW63F5xxqIZAfWY6JZBTjfrdIHTfbJjvHRrCj8VaIoOuwUvMeZBuGzjdYOZBznsoIgTt4tHwkZAVZBMqZB8j9V0ox1MfibnMsMGQMYkoioKMRjwr1aOG0i4rqSDrQZDZD'
    	]);

    	$users_friend = factory('App\User')->create([
    		'social_media' => 'facebook',
        	'social_media_uid' => '10214331734277272',
        	'social_media_access_token' => 'whatever'
		]);

		$this->signIn($user);
    	
        //act
        $response = $this->json( 'GET', "/api/find-friends");
    
        //assert
    	$response->assertStatus(200);
    	$this->assertEquals(1, sizeof($response->json()['data']));
    }
}
