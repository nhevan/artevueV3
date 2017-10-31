<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BlockUserApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * a logged in user can block another user
     */
    public function a_logged_in_user_can_block_another_user()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\User')->create();
    
        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}");

        //assert
        $response->assertSuccessful();
        $response->assertJson([
        		'message' => $this->user->name.' blocked '.$target_user->name
        	]);
        $this->assertDatabaseHas('blocked_users', [
        		'user_id' => $this->user->id,
        		'blocked_user_id' => $target_user->id
        	]);
    }

    /**
     * @test
     * blocking a user removes the user from complainants follower list
     */
    public function blocking_a_user_removes_the_user_from_complainants_follower_list()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\User')->create();
    	factory('App\Follower')->create([
    			'user_id' => $target_user->id,
    			'follower_id' => $this->user->id,
    		]);

        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}");
    
        //assert
        $this->assertDatabaseMissing('followers', [
        		'user_id' => $target_user->id,
        		'follower_id' => $this->user->id,
        		'is_still_following' => 1
        	]);
    }

    /**
     * @test
     * blocking a user removes the complainant from his followers list
     */
    public function blocking_a_user_removes_the_complainant_from_his_followers_list()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\User')->create();
    	factory('App\Follower')->create([
    			'user_id' => $this->user->id,
    			'follower_id' => $target_user->id,
    		]);

        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}");
    
        //assert
        $this->assertDatabaseMissing('followers', [
        		'user_id' => $this->user->id,
    			'follower_id' => $target_user->id,
        		'is_still_following' => 1
        	]);
    }

    /**
     * @test
     * blocking a user removes all complainant posts from his galleries in otherwords removes pins
     */
    public function blocking_a_user_removes_all_complainant_posts_from_his_galleries_in_otherwords_removes_pins()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\UserMetadata')->create()->user;
        $complainants_post1 = factory('App\Post')->create(['owner_id' => $this->user->id]);
        $complainants_post2 = factory('App\Post')->create(['owner_id' => $this->user->id]);
        $pin1 = factory('App\Pin')->create([
        		'user_id' => $target_user->id,
        		'post_id' => $complainants_post1->id
        	]);
        $pin2 = factory('App\Pin')->create([
        		'user_id' => $target_user->id,
        		'post_id' => $complainants_post2->id
        	]);
    
        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}");

        //assert
        $this->assertDatabaseMissing('pins', [
        		'user_id' => $target_user->id,
    			'post_id' => $complainants_post1->id
        	]);

        $this->assertDatabaseMissing('pins', [
        		'user_id' => $target_user->id,
    			'post_id' => $complainants_post2->id
        	]);
    }

    /**
     * @test
     * blocking a user removes all his posts from complainants galleries in otherwords removes pins
     */
    public function blocking_a_user_removes_all_his_posts_from_complainants_galleries_in_otherwords_removes_pins()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\UserMetadata')->create()->user;
        $target_users_post1 = factory('App\Post')->create(['owner_id' => $target_user->id]);
        $target_users_post2 = factory('App\Post')->create(['owner_id' => $target_user->id]);
        $pin1 = factory('App\Pin')->create([
        		'user_id' => $this->user->id,
        		'post_id' => $target_users_post1->id
        	]);
        $pin2 = factory('App\Pin')->create([
        		'user_id' => $this->user->id,
        		'post_id' => $target_users_post2->id
        	]);
    
        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}");

        //assert
        $this->assertDatabaseMissing('pins', [
        		'user_id' => $this->user->id,
    			'post_id' => $target_users_post1->id
        	]);

        $this->assertDatabaseMissing('pins', [
        		'user_id' => $this->user->id,
    			'post_id' => $target_users_post2->id
        	]);
    }

    /**
     * @test
     * unblocking a user just removes the entry from the blocked users table
     */
    public function unblocking_a_user_just_removes_the_entry_from_the_blocked_users_table()
    {
    	//arrange
        $this->signIn();
        $target_user = factory('App\User')->create();

        //act
    	$response = $this->json('POST',"api/block/{$target_user->id}"); //blocking user
    	$response = $this->json('POST',"api/block/{$target_user->id}"); //unblocking user
    
        //assert
        $response->assertSuccessful();
        $response->assertJson([
        		'message' => $this->user->name.' unblocked a user.'
        	]);
        $this->assertDatabaseMissing('blocked_users', [
        		'user_id' => $this->user->id,
        		'blocked_user_id' => $target_user->id
        	]);
    }
}
