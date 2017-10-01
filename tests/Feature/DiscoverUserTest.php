<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DiscoverUserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * discover user api returns paginated data
     */
    public function discover_user_api_returns_paginated_data()
    {
		$response = $this->getJson('/api/discover-users')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);  
    }

    /**
     * @test
     * both logged in and guest users can access discover users api
     */
    public function both_logged_in_and_guest_users_can_access_discover_users_api()
    {
    	$response = $this->getJson('/api/discover-users');
    	$response->assertStatus(200);
    	$this->assertArrayHasKey('data', $response->json());
    	$this->assertArrayHasKey('pagination', $response->json());

    	$this->signIn();
    	$response = $this->getJson('/api/discover-users');
    	$response->assertStatus(200);
    	$this->assertArrayHasKey('data', $response->json());
    	$this->assertArrayHasKey('pagination', $response->json());
    }

    /**
     * @test
	 * guests can discover any user
     */
    public function guests_can_discover_any_user()
    {
    	//arrange
     	factory('App\UserMetadata', 2 )->create(); //this create 2 users associated with the metadata
    
        //act
    	$response = $this->getJson('/api/discover-users')->json();
    
        //assert
    	$this->assertEquals(2, $response['pagination']['total']);
    }

    /**
     * @test
     * logged in user can discover users that his followers followed
     */
    public function logged_in_user_can_discover_users_that_his_followers_followed()
    {
    	//arrange
    	$this->signIn();
        factory('App\UserMetadata', 2 )->create(); //this create 2 users associated with the metadata
    
        //act
    	$response = $this->getJson('/api/discover-users')->json();
    
        //assert
        $this->assertEquals(2, $response['pagination']['total']);
    }

    /**
     * @test
     * logged in users dont see users that they are following
     */
    public function logged_in_users_dont_see_users_that_they_are_following()
    {
    	//arrange
        $this->signIn();
        $usersFollower = factory('App\Follower')->create(['follower_id' => $this->user->id]);
    
        //act
    	$response = $this->getJson('/api/discover-users')->json();
    
        //assert
        $this->assertEquals(0, $response['pagination']['total']);
    }

    /**
     * @test
     * logged in users sees users that their followers are following
     */
    public function logged_in_users_sees_users_that_their_followers_are_following()
    {
    	//arrange
        $this->signIn();
        $usersFollower = factory('App\Follower')->create(['follower_id' => $this->user->id]);
        $followersFollower = factory('App\Follower')->create(['follower_id' => $usersFollower->user_id]);
    
        //act
    	$response = $this->getJson('/api/discover-users')->json();
    
        //assert
        $this->assertEquals(1, $response['pagination']['total']);
    }
}
