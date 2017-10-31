<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowerApiTest extends TestCase
{
	use DatabaseTransactions;
 	
 	/**
    * @test
    * a logged in user can start following another user
    */
   public function a_logged_in_user_can_start_following_another_user()
   {
	   //arrange
        $another_user = factory('App\User')->create();
        $this->signIn();

        //act
    	$response = $this->json('POST',"api/follow/{$another_user->id}");

        //assert
        $this->assertDatabaseHas('followers',[
        	'user_id' => $another_user->id,
        	'follower_id' => $this->user->id,
        	'is_still_following' => 1
    	]);
   }

   /**
    * @test
    * a logged in user can unfollow a already following user
    */
   public function a_logged_in_user_can_unfollow_a_already_following_user()
   {
		//arrange
		$another_user = factory('App\User')->create();
		$this->signIn();
		factory('App\Follower')->create([
				'user_id' => $another_user,
				'follower_id' => $this->user->id
			]);

		//act
		$response = $this->json('POST',"api/follow/{$another_user->id}");

		//assert
       $this->assertDatabaseHas('followers',[
        	'user_id' => $another_user->id,
        	'follower_id' => $this->user->id,
        	'is_still_following' => 0
    	]);
   }

   /**
    * @test
    * when a user is followed the related counts increases by one
    */
   public function when_a_user_is_followed_the_related_counts_increases_by_one()
   {
		//arrange
   		$another_user = factory('App\User')->create();
   		factory('App\UserMetadata')->create(['user_id' => $another_user->id]);
		$this->signIn();

		//act
		$response = $this->json('POST',"api/follow/{$another_user->id}");

		//assert
       	$this->assertDatabaseHas('users_metadata',[
        	'user_id' => $another_user->id,
        	'follower_count' => 1
    	]);
    	$this->assertDatabaseHas('users_metadata',[
        	'user_id' => $this->user->id,
        	'following_count' => 1
    	]);
   }

   /**
    * @test
    * when a user is unfollowed the related counts decreases by one
    */
   public function when_a_user_is_unfollowed_the_related_counts_decreases_by_one()
   {
		//arrange
   		$another_user = factory('App\User')->create();
   		factory('App\UserMetadata')->create(['user_id' => $another_user->id]);
		$this->signIn();

		//act
		$response = $this->json('POST',"api/follow/{$another_user->id}"); //follow user
		$response = $this->json('POST',"api/follow/{$another_user->id}"); //unfollow user

		//assert
       $this->assertDatabaseHas('users_metadata',[
        	'user_id' => $another_user->id,
        	'follower_count' => 0
    	]);
    	$this->assertDatabaseHas('users_metadata',[
        	'user_id' => $this->user->id,
        	'following_count' => 0
    	]);
   }
}
