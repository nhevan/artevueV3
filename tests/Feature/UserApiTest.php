<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * a user can fetch his own profile
     */
    public function a_user_can_fetch_his_own_profile()
    {
    	//arrange
        $this->signIn();
    
        //act
    	$response = $this->get('/api/me');
    
        //assert
        $response->assertJsonFragment([
        	'id' => $this->user->id
    	]);
    }

    /**
     * @test
     * a user can update the privacy status of his account
     */
    public function a_user_can_update_the_privacy_status_of_his_account()
    {
        //arrange
        $this->signIn();

        // act and assert
        $response = $this->patch('/api/swap-privacy');
        $this->assertDatabaseHas('users_metadata',[
            'user_id' => $this->user->id,
            'is_account_private' => 1
        ]);
        $response = $this->patch('/api/swap-privacy');
        $this->assertDatabaseHas('users_metadata',[
            'user_id' => $this->user->id,
            'is_account_private' => 0
        ]);
    }

    /**
     * @test
     * while a user is returned it contains the instagram uid field
     */
    public function while_a_user_is_returned_it_contains_the_instagram_uid_field()
    {
    	//arrange
    	$user = factory('App\User')->create(['instagram_uid' => '1234567890']);
        $this->signIn($user);
    
        //act
    	$response = $this->get('/api/me');
    
        //assert
        $response->assertJsonFragment([
        	'id' => $this->user->id,
        	'instagram_uid' => $this->user->instagram_uid
    	]);
    }

    /**
     * @test
     * a logged in user can set his instagram uid
     */
    public function a_logged_in_user_can_set_his_instagram_uid()
    {
    	//arrange
        $this->signIn();
    
        //act
    	$response = $this->put('/api/instagram-uid', ['instagram_uid' => '1234567890']);
    
        //assert
        $response->assertJsonFragment([
        	'message' => 'The user has successfully updated their instagram_uid.'
    	]);
    	$this->assertDatabaseHas('users', [
    		'id' => $this->user->id,
    		'instagram_uid' => '1234567890'
		]);
    }
}
